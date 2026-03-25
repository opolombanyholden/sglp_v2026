<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\QrCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API V1 — Organisations / Annuaire
 *
 * Tous les endpoints retournent uniquement les données publiques.
 * Aucune donnée sensible (NIP, données personnelles des membres) n'est exposée.
 */
class OrganisationApiController extends Controller
{
    private const STATUTS_PUBLICS = [
        Organisation::STATUT_SOUMIS,
        Organisation::STATUT_EN_VALIDATION,
        Organisation::STATUT_APPROUVE,
        Organisation::STATUT_SUSPENDU,
    ];

    private const TYPES_AUTORISES = [
        Organisation::TYPE_ASSOCIATION,
        Organisation::TYPE_ONG,
        Organisation::TYPE_PARTI,
        Organisation::TYPE_CONFESSION,
    ];

    /**
     * GET /api/v1/organisations
     * Liste paginée des organisations publiques.
     *
     * Paramètres de requête :
     *  - search   : recherche textuelle (nom, sigle, ville)
     *  - type     : association | ong | parti_politique | confession_religieuse
     *  - statut   : soumis | en_validation | approuve | suspendu
     *  - province : filtre province
     *  - per_page : 10 à 100 (défaut 20)
     *  - page     : numéro de page (défaut 1)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search'   => 'nullable|string|max:255',
            'type'     => 'nullable|string|in:' . implode(',', self::TYPES_AUTORISES),
            'statut'   => 'nullable|string|in:' . implode(',', self::STATUTS_PUBLICS),
            'province' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = Organisation::whereNotNull('numero_recepisse')
            ->whereIn('statut', self::STATUTS_PUBLICS)
            ->with('organisationType');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], substr($request->search, 0, 255));
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('sigle', 'like', "%{$search}%")
                  ->orWhere('ville_commune', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && in_array($request->type, self::TYPES_AUTORISES, true)) {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut') && in_array($request->statut, self::STATUTS_PUBLICS, true)) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }

        $perPage = min((int) ($request->per_page ?? 20), 100);
        $results = $query->orderBy('nom')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $results->map(fn ($org) => $this->formatSummary($org)),
            'meta'    => [
                'total'        => $results->total(),
                'per_page'     => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page'    => $results->lastPage(),
                'from'         => $results->firstItem(),
                'to'           => $results->lastItem(),
            ],
            'links' => [
                'first' => $results->url(1),
                'last'  => $results->url($results->lastPage()),
                'prev'  => $results->previousPageUrl(),
                'next'  => $results->nextPageUrl(),
            ],
        ]);
    }

    /**
     * GET /api/v1/organisations/{id}
     * Détail d'une organisation.
     */
    public function show(int $id): JsonResponse
    {
        $org = Organisation::whereNotNull('numero_recepisse')
            ->whereIn('statut', self::STATUTS_PUBLICS)
            ->with(['organisationType', 'membresBureauPourRecepisse'])
            ->find($id);

        if (!$org) {
            return response()->json([
                'success' => false,
                'error'   => 'NOT_FOUND',
                'message' => 'Organisation introuvable ou non publique.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatDetail($org),
        ]);
    }

    /**
     * GET /api/v1/organisations/verify/{code}
     * Vérifie l'authenticité d'un récépissé (numéro, QR code ou ID).
     */
    public function verify(string $code): JsonResponse
    {
        // Sanitisation
        $code = substr(preg_replace('/[^a-zA-Z0-9\-\_\/]/', '', $code), 0, 100);

        if (empty($code)) {
            return response()->json([
                'success'  => false,
                'error'    => 'INVALID_CODE',
                'message'  => 'Le code fourni est vide ou invalide.',
                'verified' => false,
            ], 422);
        }

        $org = null;

        // 1. Recherche QR code
        $qr = QrCode::active()
            ->notExpired()
            ->where('code', $code)
            ->where('type', QrCode::TYPE_ORGANISATION)
            ->with('verifiable')
            ->first();

        if ($qr && $qr->verifiable instanceof Organisation) {
            $org = $qr->verifiable->load(['organisationType', 'membresBureauPourRecepisse']);
            $qr->markAsVerified();
        }

        // 2. Recherche par numéro de récépissé
        if (!$org) {
            $org = Organisation::where('numero_recepisse', $code)
                ->with(['organisationType', 'membresBureauPourRecepisse'])
                ->first();
        }

        // 3. Recherche par ID numérique
        if (!$org && ctype_digit($code)) {
            $org = Organisation::whereNotNull('numero_recepisse')
                ->with(['organisationType', 'membresBureauPourRecepisse'])
                ->find((int) $code);
        }

        if (!$org) {
            return response()->json([
                'success'  => true,
                'verified' => false,
                'status'   => 'NOT_FOUND',
                'message'  => 'Aucun récépissé correspondant à ce code dans la base officielle SGLP.',
                'code'     => $code,
            ]);
        }

        // Trouvé mais invalide
        if (in_array($org->statut, [
            Organisation::STATUT_RADIE,
            Organisation::STATUT_REJETE,
            Organisation::STATUT_BROUILLON,
        ], true)) {
            return response()->json([
                'success'      => true,
                'verified'     => false,
                'status'       => 'INVALID',
                'reason'       => $org->statut,
                'message'      => 'Ce récépissé existe mais n\'est plus valide.',
                'organisation' => $this->formatSummary($org),
            ]);
        }

        $verificationStatus = $org->statut === Organisation::STATUT_SUSPENDU ? 'SUSPENDED' : 'VALID';

        return response()->json([
            'success'      => true,
            'verified'     => true,
            'status'       => $verificationStatus,
            'message'      => $verificationStatus === 'VALID'
                ? 'Récépissé authentique et valide.'
                : 'Récépissé authentique mais organisation suspendue.',
            'organisation' => $this->formatDetail($org),
            'verified_at'  => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /api/v1/stats
     * Statistiques agrégées publiques.
     */
    public function stats(): JsonResponse
    {
        $base = Organisation::whereNotNull('numero_recepisse')
            ->whereIn('statut', self::STATUTS_PUBLICS);

        return response()->json([
            'success' => true,
            'data'    => [
                'total'          => (clone $base)->count(),
                'par_type'       => [
                    'associations'       => (clone $base)->where('type', Organisation::TYPE_ASSOCIATION)->count(),
                    'ong'                => (clone $base)->where('type', Organisation::TYPE_ONG)->count(),
                    'partis_politiques'  => (clone $base)->where('type', Organisation::TYPE_PARTI)->count(),
                    'confessions_religieuses' => (clone $base)->where('type', Organisation::TYPE_CONFESSION)->count(),
                ],
                'par_statut'     => [
                    'approuve'      => (clone $base)->where('statut', Organisation::STATUT_APPROUVE)->count(),
                    'en_validation' => (clone $base)->where('statut', Organisation::STATUT_EN_VALIDATION)->count(),
                    'soumis'        => (clone $base)->where('statut', Organisation::STATUT_SOUMIS)->count(),
                    'suspendu'      => (clone $base)->where('statut', Organisation::STATUT_SUSPENDU)->count(),
                ],
                'generated_at'   => now()->toIso8601String(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers de formatage — données publiques uniquement, jamais de NIP
    // ──────────────────────────────────────────────────────────────────────────

    private function formatSummary(Organisation $org): array
    {
        return [
            'id'               => $org->id,
            'nom'              => $org->nom,
            'sigle'            => $org->sigle,
            'type'             => $org->type,
            'type_libelle'     => $org->organisationType?->libelle ?? $org->type,
            'statut'           => $org->statut,
            'numero_recepisse' => $org->numero_recepisse,
            'province'         => $org->province,
            'ville_commune'    => $org->ville_commune,
        ];
    }

    private function formatDetail(Organisation $org): array
    {
        $membres = [];
        if ($org->relationLoaded('membresBureauPourRecepisse')) {
            foreach ($org->membresBureauPourRecepisse as $m) {
                $membres[] = [
                    'nom'      => $m->nom,
                    'prenom'   => $m->prenom,
                    'fonction' => $m->fonction,
                    // NIP délibérément exclu
                ];
            }
        }

        return array_merge($this->formatSummary($org), [
            'objet'            => $org->objet,
            'adresse'          => $org->adresse,
            'telephone'        => $org->telephone,
            'email'            => $org->email,
            'date_creation'    => $org->date_creation?->toDateString(),
            'date_recepisse'   => $org->date_recepisse?->toDateString(),
            'membres_bureau'   => $membres,
        ]);
    }
}
