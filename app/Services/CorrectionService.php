<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\DossierCorrection;
use App\Models\DossierOperation;
use App\Models\DocumentGeneration;
use App\Models\Organisation;
use App\Models\Adherent;
use App\Models\Fondateur;
use App\Models\MembreBureau;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CorrectionService
{
    protected DocumentGenerationService $documentGenerationService;

    public function __construct(DocumentGenerationService $documentGenerationService)
    {
        $this->documentGenerationService = $documentGenerationService;
    }

    /**
     * Initialiser une correction sur un dossier approuvé
     */
    public function initializeCorrection(Dossier $approvedDossier, array $corrections, string $globalMotif): Dossier
    {
        if (!$approvedDossier->canBeCorrected()) {
            throw new \Exception('Ce dossier ne peut pas faire l\'objet d\'une correction.');
        }

        return DB::transaction(function () use ($approvedDossier, $corrections, $globalMotif) {
            // Créer un snapshot de l'état actuel
            $snapshot = $this->createSnapshot($approvedDossier);

            // Créer le dossier de correction (nouvelle version)
            $correctionDossier = Dossier::create([
                'organisation_id' => $approvedDossier->organisation_id,
                'type_operation' => Dossier::TYPE_CORRECTION,
                'statut' => Dossier::STATUT_BROUILLON,
                'parent_dossier_id' => $approvedDossier->id,
                'version' => $approvedDossier->version + 1,
                'is_current_version' => false, // Reste false jusqu'à approbation
                'champs_modifies' => array_column($corrections, 'champ'),
                'donnees_avant_modification' => $snapshot,
                'is_active' => true,
                'donnees_supplementaires' => ['motif_global' => $globalMotif],
            ]);

            // Créer les enregistrements de correction détaillés
            foreach ($corrections as $correction) {
                DossierCorrection::create([
                    'dossier_id' => $correctionDossier->id,
                    'original_dossier_id' => $approvedDossier->id,
                    'champ_corrige' => $correction['champ'],
                    'categorie' => $correction['categorie'],
                    'ancienne_valeur' => $correction['ancienne_valeur'] ?? null,
                    'nouvelle_valeur' => $correction['nouvelle_valeur'],
                    'motif_correction' => $correction['motif'],
                    'entity_id' => $correction['entity_id'] ?? null,
                    'corrected_by' => Auth::id(),
                ]);
            }

            // Enregistrer l'opération dans l'audit trail
            DossierOperation::create([
                'dossier_id' => $correctionDossier->id,
                'user_id' => Auth::id(),
                'type_operation' => DossierOperation::TYPE_CORRECTION,
                'ancien_statut' => $approvedDossier->statut,
                'nouveau_statut' => Dossier::STATUT_BROUILLON,
                'description' => "Correction administrative initiée : {$globalMotif}",
                'donnees_avant' => $snapshot,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('Correction initiée', [
                'original_dossier' => $approvedDossier->numero_dossier,
                'correction_dossier' => $correctionDossier->numero_dossier,
                'nb_corrections' => count($corrections),
            ]);

            return $correctionDossier;
        });
    }

    /**
     * Soumettre un dossier de correction pour validation
     */
    public function submitCorrection(Dossier $correctionDossier): Dossier
    {
        if ($correctionDossier->type_operation !== Dossier::TYPE_CORRECTION) {
            throw new \Exception('Ce dossier n\'est pas un dossier de correction.');
        }

        if ($correctionDossier->statut !== Dossier::STATUT_BROUILLON) {
            throw new \Exception('Seul un brouillon peut être soumis.');
        }

        return DB::transaction(function () use ($correctionDossier) {
            $correctionDossier->update([
                'statut' => Dossier::STATUT_SOUMIS,
                'date_soumission' => now(),
                'submitted_at' => now(),
            ]);

            DossierOperation::create([
                'dossier_id' => $correctionDossier->id,
                'user_id' => Auth::id(),
                'type_operation' => DossierOperation::TYPE_SOUMISSION,
                'ancien_statut' => Dossier::STATUT_BROUILLON,
                'nouveau_statut' => Dossier::STATUT_SOUMIS,
                'description' => 'Correction soumise pour validation',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $correctionDossier->fresh();
        });
    }

    /**
     * Approuver une correction et appliquer les modifications
     */
    public function approveCorrection(Dossier $correctionDossier, string $commentaire = ''): Dossier
    {
        if (!in_array($correctionDossier->statut, [Dossier::STATUT_SOUMIS, Dossier::STATUT_EN_COURS])) {
            throw new \Exception('Ce dossier ne peut pas être approuvé dans son état actuel.');
        }

        return DB::transaction(function () use ($correctionDossier, $commentaire) {
            $organisation = $correctionDossier->organisation;

            // 1. Appliquer les corrections sur l'organisation et entités liées
            $this->applyFieldCorrections($correctionDossier);

            // 2. Invalider les anciens documents générés
            $invalidatedDocs = $this->invalidateDocuments($organisation, $correctionDossier);

            // 3. Marquer toutes les corrections comme approuvées
            $correctionDossier->corrections()->update([
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // 4. Mettre à jour le dossier de correction
            $correctionDossier->update([
                'statut' => Dossier::STATUT_ACCEPTE,
                'validated_at' => now(),
                'is_current_version' => true,
            ]);

            // 5. L'ancien dossier n'est plus la version courante
            if ($correctionDossier->parent_dossier_id) {
                Dossier::where('id', $correctionDossier->parent_dossier_id)
                    ->update(['is_current_version' => false]);
            }

            // 6. Audit trail
            DossierOperation::create([
                'dossier_id' => $correctionDossier->id,
                'user_id' => Auth::id(),
                'type_operation' => DossierOperation::TYPE_VALIDATION,
                'ancien_statut' => Dossier::STATUT_SOUMIS,
                'nouveau_statut' => Dossier::STATUT_ACCEPTE,
                'description' => "Correction approuvée" . ($commentaire ? " : {$commentaire}" : ''),
                'donnees_apres' => $this->createSnapshot($correctionDossier),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('Correction approuvée', [
                'dossier' => $correctionDossier->numero_dossier,
                'docs_invalides' => $invalidatedDocs,
                'approuve_par' => Auth::id(),
            ]);

            return $correctionDossier->fresh();
        });
    }

    /**
     * Rejeter une correction
     */
    public function rejectCorrection(Dossier $correctionDossier, string $motif): Dossier
    {
        return DB::transaction(function () use ($correctionDossier, $motif) {
            $correctionDossier->update([
                'statut' => Dossier::STATUT_REJETE,
                'motif_rejet' => $motif,
            ]);

            DossierOperation::create([
                'dossier_id' => $correctionDossier->id,
                'user_id' => Auth::id(),
                'type_operation' => DossierOperation::TYPE_REJET,
                'ancien_statut' => $correctionDossier->statut,
                'nouveau_statut' => Dossier::STATUT_REJETE,
                'description' => "Correction rejetée : {$motif}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $correctionDossier->fresh();
        });
    }

    /**
     * Appliquer les corrections champ par champ
     */
    private function applyFieldCorrections(Dossier $correctionDossier): void
    {
        $corrections = $correctionDossier->corrections;
        $organisation = $correctionDossier->organisation;

        foreach ($corrections as $correction) {
            switch ($correction->categorie) {
                case DossierCorrection::CATEGORIE_ORGANISATION:
                    $organisation->{$correction->champ_corrige} = $correction->nouvelle_valeur;
                    break;

                case DossierCorrection::CATEGORIE_ADHERENT:
                    if ($correction->entity_id) {
                        Adherent::where('id', $correction->entity_id)
                            ->update([$correction->champ_corrige => $correction->nouvelle_valeur]);
                    }
                    break;

                case DossierCorrection::CATEGORIE_FONDATEUR:
                    if ($correction->entity_id) {
                        Fondateur::where('id', $correction->entity_id)
                            ->update([$correction->champ_corrige => $correction->nouvelle_valeur]);
                    }
                    break;

                case DossierCorrection::CATEGORIE_MEMBRE_BUREAU:
                    if ($correction->entity_id) {
                        MembreBureau::where('id', $correction->entity_id)
                            ->update([$correction->champ_corrige => $correction->nouvelle_valeur]);
                    }
                    break;
            }
        }

        // Sauvegarder les modifications de l'organisation
        $organisation->save();
    }

    /**
     * Invalider les documents existants de l'organisation
     */
    private function invalidateDocuments(Organisation $organisation, Dossier $correctionDossier): int
    {
        $documents = DocumentGeneration::where('organisation_id', $organisation->id)
            ->where('is_valid', true)
            ->get();

        $count = 0;
        foreach ($documents as $doc) {
            $doc->invalidate("Correction administrative - Dossier {$correctionDossier->numero_dossier}");
            $count++;
        }

        return $count;
    }

    /**
     * Créer un snapshot complet de l'état actuel du dossier
     */
    private function createSnapshot(Dossier $dossier): array
    {
        $organisation = $dossier->organisation;

        return [
            'dossier' => $dossier->toArray(),
            'organisation' => $organisation ? $organisation->toArray() : [],
            'fondateurs' => $organisation ? $organisation->fondateurs->toArray() : [],
            'membres_bureau' => $organisation ? $organisation->membresBureau->toArray() : [],
            'date_snapshot' => now()->toISOString(),
        ];
    }
}
