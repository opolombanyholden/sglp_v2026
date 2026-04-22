<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use App\Models\Adherent;
use App\Models\InscriptionLink;
use App\Models\Organisation;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * CONTROLLER PUBLIC D'AUTO-INSCRIPTION DES ADHÉRENTS
 *
 * Permet aux citoyens de s'inscrire comme adhérents d'une organisation
 * via un lien public unique, sans nécessiter de connexion.
 *
 * Projet : SGLP
 * IMPORTANT : Ce controller est PUBLIC (pas d'authentification)
 */
class PublicRegistrationController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Afficher le formulaire d'inscription publique
     *
     * GET /inscription/{token}
     */
    public function showRegistrationForm(string $token)
    {
        $inscriptionLink = InscriptionLink::where('token', $token)
            ->with(['organisation.organisationType'])
            ->first();

        if (!$inscriptionLink) {
            return view('public.inscription.invalid', [
                'message' => 'Ce lien d\'inscription n\'existe pas ou a été supprimé.'
            ]);
        }

        if (!$inscriptionLink->isValid()) {
            $reason = $this->getInvalidReason($inscriptionLink);
            return view('public.inscription.invalid', [
                'message' => $reason,
                'organisation' => $inscriptionLink->organisation
            ]);
        }

        $organisation = $inscriptionLink->organisation;

        return view('public.inscription.form', compact('inscriptionLink', 'organisation'));
    }

    /**
     * Traiter la soumission du formulaire d'inscription
     *
     * POST /inscription/{token}
     */
    public function submitRegistration(Request $request, string $token)
    {
        $inscriptionLink = InscriptionLink::where('token', $token)
            ->with(['organisation.organisationType'])
            ->first();

        if (!$inscriptionLink || !$inscriptionLink->isValid()) {
            return redirect()->back()->with('error', 'Ce lien d\'inscription n\'est plus valide.');
        }

        $organisation = $inscriptionLink->organisation;

        // Validation des données (mêmes règles que côté opérateur)
        $validated = $request->validate([
            'civilite'        => 'nullable|in:M,F',
            'nom'             => 'required|string|max:100',
            'prenom'          => 'required|string|max:100',
            'string|max:255',
            'date_naissance'  => 'nullable|date|before:today',
            'lieu_naissance'  => 'nullable|string|max:255',
            'sexe'            => 'nullable|in:M,F',
            'nationalite'     => 'nullable|string|max:100',
            'profession'      => 'nullable|string|max:255',
            'fonction'        => 'nullable|string|max:100',
            'telephone'       => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'adresse_complete' => 'nullable|string|max:255',
            'province'        => 'nullable|string|max:100',
            'departement'     => 'nullable|string|max:100',
            'piece_identite'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'nom.required'             => 'Le nom est obligatoire.',
            'prenom.required'          => 'Le prénom est obligatoire.',
            'piece_identite.required'  => 'La pièce d\'identité est obligatoire.',
            'piece_identite.mimes'     => 'La pièce d\'identité doit être au format PDF, JPG ou PNG.',
            'piece_identite.max'       => 'La pièce d\'identité ne doit pas dépasser 5 Mo.',
            'date_naissance.before'    => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'email.email'              => 'L\'adresse email n\'est pas valide.',
        ]);

        // Vérifier doublon NIP + organisation (contrainte unique)
        if (!empty($validated['nip'])) {
            $existingAdherent = Adherent::where('nip', $validated['nip'])
                ->where('organisation_id', $organisation->id)
                ->first();

            if ($existingAdherent) {
                throw ValidationException::withMessages([
                    'nip' => 'Ce numéro NIP est déjà enregistré pour cette organisation.'
                ]);
            }
        }

        try {
            DB::beginTransaction();

            // Upload de la pièce d'identité
            $uploadResult = $this->fileUploadService->upload(
                $request->file('piece_identite'),
                'adherents/pieces_identite'
            );

            // Créer l'adhérent
            // Le boot() du modèle appellera automatiquement detectAndManageAllAnomalies()
            $adherent = Adherent::create([
                'organisation_id'     => $organisation->id,
                'inscription_link_id' => $inscriptionLink->id,
                'source_inscription'  => 'auto_inscription',
                'statut_inscription'  => $inscriptionLink->requiert_validation
                    ? 'en_attente_validation'
                    : 'validee',
                'is_active'           => !$inscriptionLink->requiert_validation,

                // Identification
                'civilite'       => $validated['civilite'] ?? 'M',
                'nom'            => strtoupper(trim($validated['nom'])),
                'prenom'         => ucwords(strtolower(trim($validated['prenom']))),
                'nip'            => !empty($validated['nip']) ? trim($validated['nip']) : null,
                'date_naissance' => $validated['date_naissance'] ?? null,
                'lieu_naissance' => $validated['lieu_naissance'] ?? null,
                'sexe'           => $validated['sexe'] ?? null,
                'nationalite'    => $validated['nationalite'] ?? 'Gabonaise',

                // Contact
                'telephone'       => $validated['telephone'] ?? null,
                'email'           => $validated['email'] ?? null,
                'adresse_complete' => $validated['adresse_complete'] ?? null,
                'province'        => $validated['province'] ?? null,
                'departement'     => $validated['departement'] ?? null,

                // Professionnel
                'profession' => $validated['profession'] ?? null,
                'fonction'   => $validated['fonction'] ?? Adherent::FONCTION_MEMBRE,

                // Document
                'piece_identite' => $uploadResult['file_path'],

                // Dates
                'date_adhesion' => now(),
            ]);

            // Incrémenter le compteur d'inscriptions
            $inscriptionLink->incrementInscriptions();

            DB::commit();

            Log::info('Auto-inscription réussie', [
                'adherent_id'        => $adherent->id,
                'organisation_id'    => $organisation->id,
                'inscription_link'   => $inscriptionLink->id,
                'has_anomalies'      => $adherent->has_anomalies,
                'anomalies_severity' => $adherent->anomalies_severity,
            ]);

            return redirect()->route('public.inscription.confirmation', $token)
                ->with('success', true)
                ->with('adherent_nom', $adherent->nom . ' ' . $adherent->prenom);

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur auto-inscription', [
                'token'   => $token,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
        }
    }

    /**
     * Page de confirmation après inscription réussie
     *
     * GET /inscription/{token}/confirmation
     */
    public function showConfirmation(string $token)
    {
        $inscriptionLink = InscriptionLink::where('token', $token)
            ->with('organisation')
            ->first();

        if (!$inscriptionLink) {
            return redirect('/');
        }

        return view('public.inscription.confirmation', [
            'organisation'   => $inscriptionLink->organisation,
            'adherent_nom'   => session('adherent_nom', ''),
            'requiert_validation' => $inscriptionLink->requiert_validation,
        ]);
    }

    /**
     * Déterminer la raison d'invalidité du lien
     */
    private function getInvalidReason(InscriptionLink $link): string
    {
        if (!$link->is_active) {
            return 'Ce lien d\'inscription a été désactivé par l\'administrateur de l\'organisation.';
        }

        if ($link->isExpired()) {
            return 'Ce lien d\'inscription a expiré le ' . $link->date_fin->format('d/m/Y') . '.';
        }

        if ($link->isLimitReached()) {
            return 'Le nombre maximum d\'inscriptions pour ce lien a été atteint.';
        }

        if ($link->organisation && !$link->organisation->isApprouvee()) {
            return 'L\'organisation associée à ce lien n\'est plus active.';
        }

        return 'Ce lien d\'inscription n\'est plus valide.';
    }
}
