<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\QrCode;
use Illuminate\Http\Request;

class AnnuaireController extends Controller
{
    /**
     * Statuts visibles dans l'annuaire public.
     * Inclut récépissé provisoire (soumis/en_validation) et définitif (approuve).
     * Les organisations suspendues restent visibles avec avertissement.
     */
    private const STATUTS_PUBLICS = [
        Organisation::STATUT_SOUMIS,
        Organisation::STATUT_EN_VALIDATION,
        Organisation::STATUT_APPROUVE,
        Organisation::STATUT_SUSPENDU,
    ];

    /** Types autorisés pour le filtre (whitelist) */
    private const TYPES_AUTORISES = [
        Organisation::TYPE_ASSOCIATION,
        Organisation::TYPE_ONG,
        Organisation::TYPE_PARTI,
        Organisation::TYPE_CONFESSION,
    ];

    public function index(Request $request)
    {
        $query = Organisation::whereNotNull('numero_recepisse')
            ->whereIn('statut', self::STATUTS_PUBLICS)
            ->with('organisationType');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], substr($request->search, 0, 255));
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('sigle', 'like', "%{$search}%")
                  ->orWhere('objet', 'like', "%{$search}%")
                  ->orWhere('ville_commune', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            if (in_array($request->type, self::TYPES_AUTORISES, true)) {
                $query->where('type', $request->type);
            }
        }

        if ($request->filled('province') && $request->province !== 'all') {
            $query->where('province', substr($request->province, 0, 100));
        }

        $organisations = $query->orderBy('nom')->paginate(12)->withQueryString();

        // Stats (requêtes séparées pour éviter d'interférer avec la pagination)
        $baseStats = Organisation::whereNotNull('numero_recepisse')
            ->whereIn('statut', self::STATUTS_PUBLICS);

        $stats = [
            'total'       => (clone $baseStats)->count(),
            'associations'=> (clone $baseStats)->where('type', Organisation::TYPE_ASSOCIATION)->count(),
            'ong'         => (clone $baseStats)->where('type', Organisation::TYPE_ONG)->count(),
            'partis'      => (clone $baseStats)->where('type', Organisation::TYPE_PARTI)->count(),
            'confessions' => (clone $baseStats)->where('type', Organisation::TYPE_CONFESSION)->count(),
        ];

        $provinces = Organisation::whereNotNull('numero_recepisse')
            ->whereIn('statut', self::STATUTS_PUBLICS)
            ->distinct()->pluck('province')->filter()->sort()->values();

        return view('public.annuaire.index', compact('organisations', 'stats', 'provinces'));
    }

    public function associations()
    {
        return redirect()->route('annuaire.index', ['type' => Organisation::TYPE_ASSOCIATION]);
    }

    public function ong()
    {
        return redirect()->route('annuaire.index', ['type' => Organisation::TYPE_ONG]);
    }

    public function partisPolitiques()
    {
        return redirect()->route('annuaire.index', ['type' => Organisation::TYPE_PARTI]);
    }

    public function confessionsReligieuses()
    {
        return redirect()->route('annuaire.index', ['type' => Organisation::TYPE_CONFESSION]);
    }

    public function show(int $id)
    {
        $organisation = Organisation::whereNotNull('numero_recepisse')
            ->whereIn('statut', self::STATUTS_PUBLICS)
            ->with(['organisationType', 'membresBureauPourRecepisse'])
            ->findOrFail($id);

        $similaires = Organisation::whereNotNull('numero_recepisse')
            ->whereIn('statut', [Organisation::STATUT_APPROUVE, Organisation::STATUT_SUSPENDU])
            ->where('type', $organisation->type)
            ->where('province', $organisation->province)
            ->where('id', '!=', $id)
            ->with('organisationType')
            ->limit(3)
            ->get();

        return view('public.annuaire.show', compact('organisation', 'similaires'));
    }

    /**
     * Vérifie l'authenticité d'un récépissé via QR code, numéro de récépissé ou ID.
     * URL : /annuaire/verify/{code}
     * Throttle : 20 requêtes/minute/IP (défini dans les routes).
     */
    public function verify(string $code)
    {
        // Sanitisation : seulement les caractères alphanumériques, tirets, slashes
        $code = substr(preg_replace('/[^a-zA-Z0-9\-\_\/]/', '', $code), 0, 100);

        if (empty($code)) {
            return view('public.annuaire.verify-error', [
                'raison' => 'invalid_code',
                'code'   => '',
            ]);
        }

        $organisation = null;
        $verifiedViaQr = false;

        // 1. Recherche par code QR (colonne `code`)
        $qr = QrCode::active()
            ->notExpired()
            ->where('code', $code)
            ->first();

        if ($qr) {
            // Si le QR a un verifiable_id, charger directement
            if ($qr->verifiable instanceof Organisation) {
                $organisation = $qr->verifiable->load(['organisationType', 'membresBureauPourRecepisse']);
            }
            // Sinon, extraire l'organisation_id depuis donnees_verification
            if (!$organisation && $qr->donnees_verification) {
                $data = is_array($qr->donnees_verification)
                    ? $qr->donnees_verification
                    : json_decode($qr->donnees_verification, true);
                if (!empty($data['organisation_id'])) {
                    $organisation = Organisation::with(['organisationType', 'membresBureauPourRecepisse'])
                        ->find($data['organisation_id']);
                }
            }
            if ($organisation) {
                $qr->markAsVerified();
                $verifiedViaQr = true;
            }
        }

        // 2. Recherche par document_numero du QR code (ex: RECEP-PROV-2026-00134)
        if (!$organisation) {
            // Recherche exacte d'abord
            $qr = QrCode::active()
                ->notExpired()
                ->where('document_numero', $code)
                ->first();

            // Si pas trouvé et que le code ressemble à un numéro de récépissé,
            // normaliser sur 5 chiffres (ex: RECEP-DEF-2026-0019 → RECEP-DEF-2026-00019)
            if (!$qr && preg_match('/^(RECEP-(?:PROV|DEF)-\d{4}-)(\d+)$/i', $code, $m)) {
                $normalized = $m[1] . str_pad($m[2], 5, '0', STR_PAD_LEFT);
                if ($normalized !== $code) {
                    $qr = QrCode::active()
                        ->notExpired()
                        ->where('document_numero', $normalized)
                        ->first();
                }
            }

            if ($qr && $qr->donnees_verification) {
                $data = $qr->donnees_verification;
                if (!empty($data['organisation_id'])) {
                    $organisation = Organisation::with(['organisationType', 'membresBureauPourRecepisse'])
                        ->find($data['organisation_id']);
                }
                if ($organisation) {
                    $qr->markAsVerified();
                    $verifiedViaQr = true;
                }
            }
        }

        // 3. Recherche par numéro de récépissé (ex: ASS/2026/00002)
        if (!$organisation) {
            $organisation = Organisation::where('numero_recepisse', $code)
                ->with(['organisationType', 'membresBureauPourRecepisse'])
                ->first();
        }

        // 4. Recherche par ID numérique
        if (!$organisation && ctype_digit($code)) {
            $organisation = Organisation::whereNotNull('numero_recepisse')
                ->with(['organisationType', 'membresBureauPourRecepisse'])
                ->find((int) $code);
        }

        // Aucune correspondance trouvée → document frauduleux ou inexistant
        if (!$organisation) {
            return view('public.annuaire.verify-error', [
                'raison' => 'not_found',
                'code'   => $code,
            ]);
        }

        // Trouvé mais état invalide (radié, rejeté, brouillon)
        if (in_array($organisation->statut, [
            Organisation::STATUT_RADIE,
            Organisation::STATUT_REJETE,
            Organisation::STATUT_BROUILLON,
        ], true)) {
            return view('public.annuaire.verify-error', [
                'raison'       => 'document_invalide',
                'code'         => $code,
                'organisation' => $organisation,
            ]);
        }

        // Valide → afficher la fiche avec bannière de vérification
        $similaires        = collect();
        $verificationMode  = true;
        $verificationResult = ($organisation->statut === Organisation::STATUT_SUSPENDU)
            ? 'suspendu'
            : 'valid';

        return view('public.annuaire.show', compact(
            'organisation',
            'similaires',
            'verificationMode',
            'verificationResult'
        ));
    }
}
