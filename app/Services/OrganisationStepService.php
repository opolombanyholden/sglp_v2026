<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\OrganizationDraft;
use App\Models\Organisation;
use App\Models\OrganisationType;
use App\Models\Fondateur;
use App\Models\Adherent;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrganisationStepService
{
    /**
     * Configuration des étapes et leurs exigences
     */
    const STEPS_CONFIG = [
        1 => [
            'name' => 'Type d\'organisation',
            'required_fields' => ['type_organisation'],
            'validation_rules' => [
                'type_organisation' => 'required|in:association,ong,parti_politique,confession_religieuse'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        2 => [
            'name' => 'Guide et exigences',
            'required_fields' => ['guide_read_confirm'],
            'validation_rules' => [
                'guide_read_confirm' => 'required'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        3 => [
            'name' => 'Informations demandeur',
            'required_fields' => ['demandeur_nip', 'demandeur_nom', 'demandeur_prenom', 'demandeur_email', 'demandeur_telephone'],
            'validation_rules' => [
                'demandeur_nip' => 'required|digits:13',
                'demandeur_nom' => 'required|string|max:255',
                'demandeur_prenom' => 'required|string|max:255',
                'demandeur_email' => 'required|email|max:255',
                'demandeur_telephone' => 'required|string|max:255',
                'demandeur_role' => 'required|string'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        4 => [
            'name' => 'Informations organisation',
            'required_fields' => ['org_nom', 'org_objet', 'org_telephone'],
            'validation_rules' => [
                'org_nom' => 'required|string|max:255',
                'org_objet' => 'required|string|min:50',
                'org_telephone' => 'required|string|max:255',
                'org_date_creation' => 'required|date'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        5 => [
            'name' => 'Coordonnées et localisation',
            'required_fields' => ['org_adresse_complete', 'org_province', 'org_prefecture'],
            'validation_rules' => [
                'org_adresse_complete' => 'required|string|max:255',
                'org_province' => 'required|string|max:255',
                'org_prefecture' => 'required|string|max:255',
                'org_zone_type' => 'required|in:urbaine,rurale'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        6 => [
            'name' => 'Fondateurs',
            'required_fields' => ['fondateurs'],
            'validation_rules' => [],
            'custom_validation' => 'validateFondateurs',
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        7 => [
            'name' => 'Adhérents',
            'required_fields' => ['adherents'],
            'validation_rules' => [],
            'custom_validation' => 'validateAdherents',
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        8 => [
            'name' => 'Documents',
            'required_fields' => ['documents'],
            'validation_rules' => [],
            'custom_validation' => 'validateDocuments',
            'can_generate_accuse' => false,
            'auto_save_enabled' => true
        ],
        9 => [
            'name' => 'Validation finale',
            'required_fields' => ['declaration_veracite', 'declaration_conformite', 'declaration_autorisation'],
            'validation_rules' => [
                'declaration_veracite' => 'required|accepted',
                'declaration_conformite' => 'required|accepted',
                'declaration_autorisation' => 'required|accepted'
            ],
            'can_generate_accuse' => false,
            'auto_save_enabled' => false
        ]
    ];

    /**
     * Sauvegarder une étape spécifique
     */
    public function saveStep(int $stepNumber, array $data, int $userId, string $sessionId = null): array
    {
        try {
            Log::info("Sauvegarde étape {$stepNumber}", [
                'user_id' => $userId,
                'data_keys' => array_keys($data),
                'session_id' => $sessionId
            ]);

            // Validation des données de l'étape
            $validationResult = $this->validateStep($stepNumber, $data);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'message' => 'Erreurs de validation détectées',
                    'errors' => $validationResult['errors'],
                    'step' => $stepNumber
                ];
            }

            // Récupérer ou créer le brouillon
            $draft = $this->getOrCreateDraft($userId, $sessionId);

            // Mettre à jour les données de l'étape
            $stepData = $this->updateStepData($draft, $stepNumber, $data);

            // Sauvegarder en base
            $draft->save();

            // Persister parallèlement un Dossier brouillon (exigence "chaque
            // étape persistée en BDD comme dossier brouillon"). Le dossier_id
            // est conservé dans le draft pour réutilisation aux étapes suivantes.
            $dossier = $this->upsertDossierBrouillon($draft, $userId);

            // Générer accusé si possible
            $accuseGenerated = false;
            if ($this->canGenerateAccuse($stepNumber)) {
                $accuseGenerated = $this->generateStepAccuse($stepNumber, $draft);
            }

            Log::info("Étape {$stepNumber} sauvegardée avec succès", [
                'draft_id' => $draft->id,
                'dossier_id' => $dossier?->id,
                'accuse_generated' => $accuseGenerated
            ]);

            return [
                'success' => true,
                'message' => "Étape {$stepNumber} sauvegardée avec succès",
                'draft_id' => $draft->id,
                'dossier_id' => $dossier?->id,
                'numero_dossier' => $dossier?->numero_dossier,
                'step_status' => $stepData['status'],
                'accuse_generated' => $accuseGenerated,
                'can_proceed_next' => $this->canProceedToStep($stepNumber + 1, $draft->id),
                'completion_percentage' => $this->calculateCompletionPercentage($draft)
            ];

        } catch (\Exception $e) {
            Log::error("Erreur sauvegarde étape {$stepNumber}", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde',
                'error' => $e->getMessage(),
                'step' => $stepNumber
            ];
        }
    }

    /**
     * Valider une étape spécifique
     */
    public function validateStep(int $stepNumber, array $data): array
    {
        $config = self::STEPS_CONFIG[$stepNumber] ?? null;
        if (!$config) {
            return [
                'valid' => false,
                'errors' => ['step' => 'Étape non reconnue']
            ];
        }

        $errors = [];

        // Validation des règles standard
        if (!empty($config['validation_rules'])) {
            $validator = \Validator::make($data, $config['validation_rules']);
            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->toArray());
            }
        }

        // Validation personnalisée
        if (!empty($config['custom_validation'])) {
            $customMethod = $config['custom_validation'];
            if (method_exists($this, $customMethod)) {
                $customResult = $this->$customMethod($data);
                if (!$customResult['valid']) {
                    $errors = array_merge($errors, $customResult['errors']);
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Récupérer les données d'une étape
     */
    public function getStepData(int $stepNumber, int $draftId): array
    {
        $draft = OrganizationDraft::find($draftId);
        if (!$draft) {
            return [
                'success' => false,
                'message' => 'Brouillon non trouvé'
            ];
        }

        $formData = $draft->form_data ?? [];
        $stepKey = "step_{$stepNumber}";
        $stepData = $formData[$stepKey] ?? [];

        return [
            'success' => true,
            'data' => $stepData['data'] ?? [],
            'status' => $stepData['status'] ?? 'pending',
            'validated_at' => $stepData['validated_at'] ?? null,
            'errors' => $stepData['errors'] ?? []
        ];
    }

    /**
     * Vérifier si on peut passer à l'étape suivante
     */
    public function canProceedToStep(int $stepNumber, int $draftId): bool
    {
        if ($stepNumber <= 1) {
            return true; // Première étape toujours accessible
        }

        $previousStepNumber = $stepNumber - 1;
        $previousStepData = $this->getStepData($previousStepNumber, $draftId);

        return $previousStepData['success'] && 
               $previousStepData['status'] === 'completed';
    }

    /**
     * Générer un accusé de réception pour une étape
     */
    public function generateStepAccuse(int $stepNumber, OrganizationDraft $draft): bool
    {
        try {
            $config = self::STEPS_CONFIG[$stepNumber];
            if (!$config['can_generate_accuse']) {
                return false;
            }

            $accuseData = [
                'draft_id' => $draft->id,
                'step_number' => $stepNumber,
                'step_name' => $config['name'],
                'completed_at' => now(),
                'user_id' => $draft->user_id,
                'organization_type' => $draft->organization_type
            ];

            // Ici on pourrait sauvegarder l'accusé en base ou générer un PDF
            Log::info("Accusé de réception généré pour étape {$stepNumber}", $accuseData);

            return true;

        } catch (\Exception $e) {
            Log::error("Erreur génération accusé étape {$stepNumber}", [
                'draft_id' => $draft->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Finaliser le processus et créer l'organisation
     */
    public function finalizeOrganisation(int $draftId): array
    {
        try {
            $draft = OrganizationDraft::find($draftId);
            if (!$draft) {
                return [
                    'success' => false,
                    'message' => 'Brouillon non trouvé'
                ];
            }

            // Vérifier que toutes les étapes sont complétées
            for ($step = 1; $step <= 9; $step++) {
                if (!$this->canProceedToStep($step + 1, $draftId) && $step < 9) {
                    return [
                        'success' => false,
                        'message' => "L'étape {$step} n'est pas complétée",
                        'missing_step' => $step
                    ];
                }
            }

            DB::beginTransaction();

            // S'assurer qu'un Dossier brouillon existe (idempotent)
            $this->upsertDossierBrouillon($draft, $draft->user_id);

            $meta = ($draft->form_data ?? [])['_meta'] ?? [];

            // Réutiliser l'Organisation brouillon créée pendant le wizard plutôt
            // que d'en créer une nouvelle (évite doublons + préserve les FK).
            $organisation = !empty($meta['organisation_id'])
                ? Organisation::find($meta['organisation_id'])
                : null;

            $organisationData = $this->consolidateOrganisationData($draft);
            // À la finalisation : statut = soumis, is_active = true
            $organisationData['statut'] = 'soumis';
            $organisationData['is_active'] = true;

            if ($organisation) {
                $organisation->fill($organisationData)->save();
            } else {
                $organisation = Organisation::create($organisationData);
            }

            // Créer les entités liées (fondateurs/adhérents) — idempotent : on vide
            // d'abord pour éviter les doublons en cas de re-finalisation.
            // ORDRE : adhérents avant fondateurs (FK adherents.fondateur_id → fondateurs.id)
            $organisation->adherents()->delete();
            $organisation->fondateurs()->delete();
            $this->createRelatedEntities($organisation, $draft);

            // Réutiliser le Dossier brouillon : passage à statut=soumis
            $dossier = !empty($meta['dossier_id']) ? Dossier::find($meta['dossier_id']) : null;
            if (!$dossier) {
                // Cas exceptionnel : aucun upsert n'a réussi (FK manquantes en
                // cours de wizard) → on en crée un maintenant.
                $dossier = Dossier::create([
                    'organisation_id' => $organisation->id,
                    'numero_dossier'  => $this->generateNumeroDossierBrouillon($organisation->type ?? 'ORG'),
                    'type_operation'  => 'creation',
                    'statut'          => 'soumis',
                    'is_active'       => true,
                    'date_soumission' => now(),
                ]);
            } else {
                $dossier->update([
                    'organisation_id' => $organisation->id,
                    'statut'          => 'soumis',
                    'date_soumission' => now(),
                ]);
            }

            // Marquer le brouillon comme finalisé
            $draft->update([
                'completion_percentage' => 100,
                'current_step' => 9,
                'expires_at' => now()->addDays(30)
            ]);

            DB::commit();

            Log::info("Organisation finalisée avec succès", [
                'organisation_id' => $organisation->id,
                'dossier_id' => $dossier->id,
                'draft_id' => $draft->id
            ]);

            return [
                'success' => true,
                'message' => 'Organisation créée avec succès',
                'organisation_id' => $organisation->id,
                'dossier_id' => $dossier->id,
                'organisation' => $organisation
            ];

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error("Erreur finalisation organisation", [
                'draft_id' => $draftId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'organisation',
                'error' => $e->getMessage()
            ];
        }
    }

    // =============================================
    // MÉTHODES PRIVÉES
    // =============================================

    /**
     * Récupérer ou créer un brouillon
     */
    private function getOrCreateDraft(int $userId, string $sessionId = null): OrganizationDraft
    {
        $query = OrganizationDraft::where('user_id', $userId);
        
        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        $draft = $query->latest()->first();

        if (!$draft) {
            $draft = OrganizationDraft::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'form_data' => [],
                'current_step' => 1,
                'completion_percentage' => 0,
                'last_saved_at' => now(),
                'expires_at' => now()->addDays(7)
            ]);
        }

        return $draft;
    }

    /**
     * Mettre à jour les données d'une étape
     */
    private function updateStepData(OrganizationDraft $draft, int $stepNumber, array $data): array
    {
        $formData = $draft->form_data ?? [];
        $stepKey = "step_{$stepNumber}";

        $stepData = [
            'status' => 'completed',
            'data' => $data,
            'validated_at' => now()->toISOString(),
            'errors' => []
        ];

        $formData[$stepKey] = $stepData;

        // Mettre à jour le brouillon
        $draft->form_data = $formData;
        $draft->current_step = max($draft->current_step, $stepNumber);
        $draft->completion_percentage = $this->calculateCompletionPercentage($draft);
        $draft->last_saved_at = now();

        // Stocker le type d'organisation dans la colonne dédiée (étape 1)
        if ($stepNumber === 1 && !empty($data['type_organisation'])) {
            $draft->organization_type = $data['type_organisation'];
        }

        return $stepData;
    }

    /**
     * Calculer le pourcentage de completion
     */
    private function calculateCompletionPercentage(OrganizationDraft $draft): int
    {
        $formData = $draft->form_data ?? [];
        $completedSteps = 0;

        for ($i = 1; $i <= 9; $i++) {
            $stepKey = "step_{$i}";
            if (isset($formData[$stepKey]) && $formData[$stepKey]['status'] === 'completed') {
                $completedSteps++;
            }
        }

        return round(($completedSteps / 9) * 100);
    }

    /**
     * Vérifier si on peut générer un accusé pour cette étape
     */
    private function canGenerateAccuse(int $stepNumber): bool
    {
        $config = self::STEPS_CONFIG[$stepNumber] ?? null;
        return $config ? $config['can_generate_accuse'] : false;
    }

    /**
     * Validation personnalisée pour les fondateurs
     */
    private function validateFondateurs(array $data): array
    {
        $fondateurs = $data['fondateurs'] ?? [];
        
        if (empty($fondateurs)) {
            return [
                'valid' => false,
                'errors' => ['fondateurs' => 'Au moins un fondateur est requis']
            ];
        }

        // Validation selon le type d'organisation
        $organizationType = $data['organization_type'] ?? 'association';
        $minRequired = $this->getMinFondateurs($organizationType);

        if (count($fondateurs) < $minRequired) {
            return [
                'valid' => false,
                'errors' => ['fondateurs' => "Minimum {$minRequired} fondateurs requis"]
            ];
        }

        return ['valid' => true, 'errors' => []];
    }

    /**
     * Validation personnalisée pour les adhérents
     */
    private function validateAdherents(array $data): array
    {
        $adherents = $data['adherents'] ?? [];
        
        if (empty($adherents)) {
            return [
                'valid' => false,
                'errors' => ['adherents' => 'Au moins un adhérent est requis']
            ];
        }

        // Validation selon le type d'organisation
        $organizationType = $data['organization_type'] ?? 'association';
        $minRequired = $this->getMinAdherents($organizationType);

        if (count($adherents) < $minRequired) {
            return [
                'valid' => false,
                'errors' => ['adherents' => "Minimum {$minRequired} adhérents requis"]
            ];
        }

        return ['valid' => true, 'errors' => []];
    }

    /**
     * Validation personnalisée pour les documents
     */
    private function validateDocuments(array $data): array
    {
        // Pour l'instant, accepter même sans documents
        // La validation complète sera faite à la soumission finale
        return ['valid' => true, 'errors' => []];
    }

    /**
     * Obtenir le minimum de fondateurs requis
     */
    private function getMinFondateurs(string $type): int
    {
        $orgType = \App\Models\OrganisationType::where('code', $type)->first();
        if ($orgType) {
            return $orgType->nb_min_fondateurs_majeurs;
        }
        return 2;
    }

    /**
     * Obtenir le minimum d'adhérents requis — depuis la BD (organisation_types)
     */
    private function getMinAdherents(string $type): int
    {
        $orgType = \App\Models\OrganisationType::where('code', $type)->first();
        if ($orgType) {
            return $orgType->nb_min_adherents_creation;
        }
        return 10;
    }

    /**
     * Consolider les données pour créer l'organisation finale
     */
    /**
     * Crée ou met à jour un Dossier brouillon associé au draft.
     *
     * À la première étape : crée une Organisation provisoire (statut brouillon)
     * + un Dossier (statut brouillon, type_operation=creation).
     * Aux étapes suivantes : met à jour l'Organisation et le Dossier existants.
     *
     * Le dossier_id et organisation_id sont persistés dans form_data['_meta']
     * pour pouvoir y accéder aux étapes suivantes et à la finalisation.
     */
    private function upsertDossierBrouillon(OrganizationDraft $draft, int $userId): ?Dossier
    {
        try {
            $formData = $draft->form_data ?? [];
            $meta = $formData['_meta'] ?? [];
            $consolidated = [];
            foreach ($formData as $stepKey => $stepData) {
                if (is_string($stepKey) && str_starts_with($stepKey, 'step_') && isset($stepData['data'])) {
                    $consolidated = array_merge($consolidated, (array) $stepData['data']);
                }
            }

            // Tant qu'on n'a pas de type d'organisation, on ne peut pas créer
            // proprement le couple Organisation+Dossier (FK obligatoires).
            $typeCode = $consolidated['type_organisation'] ?? null;
            if (!$typeCode) {
                return null;
            }

            $orgType = OrganisationType::where('code', $typeCode)->first();
            if (!$orgType) {
                return null;
            }

            // 1) Organisation brouillon
            $organisation = !empty($meta['organisation_id'])
                ? Organisation::find($meta['organisation_id'])
                : null;

            $orgFields = [
                'user_id'              => $userId,
                'organisation_type_id' => $orgType->id,
                'type'                 => $orgType->code,
                'nom'                  => $consolidated['org_nom'] ?? ($organisation->nom ?? ('Brouillon #' . now()->timestamp)),
                'sigle'                => $consolidated['org_sigle'] ?? ($organisation->sigle ?? null),
                'objet'                => $consolidated['org_objet'] ?? ($organisation->objet ?? ''),
                'siege_social'         => $consolidated['org_adresse_complete'] ?? ($organisation->siege_social ?? ''),
                'province'             => $consolidated['org_province'] ?? ($organisation->province ?? ''),
                'departement'          => $consolidated['org_departement'] ?? ($organisation->departement ?? null),
                'prefecture'           => $consolidated['org_prefecture'] ?? ($organisation->prefecture ?? 'Non défini'),
                'zone_type'            => $consolidated['org_zone_type'] ?? ($organisation->zone_type ?? 'urbaine'),
                'latitude'             => $consolidated['org_latitude'] ?? ($organisation->latitude ?? null),
                'longitude'            => $consolidated['org_longitude'] ?? ($organisation->longitude ?? null),
                'email'                => $consolidated['org_email'] ?? ($organisation->email ?? null),
                'telephone'            => $consolidated['org_telephone'] ?? ($organisation->telephone ?? ''),
                'site_web'             => $consolidated['org_site_web'] ?? ($organisation->site_web ?? null),
                'date_creation'        => $consolidated['org_date_creation'] ?? ($organisation->date_creation ?? null),
                'statut'               => 'brouillon',
                'is_active'            => false,
            ];

            if ($organisation) {
                $organisation->fill(array_filter($orgFields, fn($v) => $v !== null && $v !== ''))->save();
            } else {
                $organisation = Organisation::create($orgFields);
            }

            // 2) Dossier brouillon
            $dossier = !empty($meta['dossier_id'])
                ? Dossier::find($meta['dossier_id'])
                : null;

            $donneesSupp = is_array($dossier?->donnees_supplementaires)
                ? $dossier->donnees_supplementaires
                : [];
            $donneesSupp['demandeur'] = [
                'nip'       => $consolidated['demandeur_nip']      ?? ($donneesSupp['demandeur']['nip']      ?? null),
                'nom'       => $consolidated['demandeur_nom']      ?? ($donneesSupp['demandeur']['nom']      ?? null),
                'prenom'    => $consolidated['demandeur_prenom']   ?? ($donneesSupp['demandeur']['prenom']   ?? null),
                'email'     => $consolidated['demandeur_email']    ?? ($donneesSupp['demandeur']['email']    ?? null),
                'telephone' => $consolidated['demandeur_telephone']?? ($donneesSupp['demandeur']['telephone']?? null),
                'role'      => $consolidated['demandeur_role']     ?? ($donneesSupp['demandeur']['role']     ?? 'Déclarant'),
            ];
            $donneesSupp['draft_id'] = $draft->id;
            $donneesSupp['last_completed_step'] = max(
                (int) ($donneesSupp['last_completed_step'] ?? 0),
                (int) $draft->current_step
            );

            if ($dossier) {
                $dossier->update([
                    'organisation_id'         => $organisation->id,
                    'donnees_supplementaires' => $donneesSupp,
                ]);
            } else {
                $dossier = Dossier::create([
                    'organisation_id'         => $organisation->id,
                    'numero_dossier'          => $this->generateNumeroDossierBrouillon($orgType->code),
                    'type_operation'          => 'creation',
                    'statut'                  => 'brouillon',
                    'is_active'               => true,
                    'donnees_supplementaires' => $donneesSupp,
                ]);
            }

            // 3) Mémoriser les ids dans le draft
            $meta['organisation_id'] = $organisation->id;
            $meta['dossier_id']      = $dossier->id;
            $formData['_meta']       = $meta;
            $draft->form_data        = $formData;
            $draft->save();

            return $dossier;
        } catch (\Throwable $e) {
            Log::warning('upsertDossierBrouillon échoué (non bloquant)', [
                'draft_id' => $draft->id,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Numéro de dossier provisoire pour les brouillons. Le numéro définitif
     * est attribué à la soumission (méthode `soumettre`).
     */
    private function generateNumeroDossierBrouillon(string $orgCode): string
    {
        $prefix = strtoupper(substr($orgCode, 0, 3));
        return 'BR-' . $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
    }

    private function consolidateOrganisationData(OrganizationDraft $draft): array
    {
        $formData = $draft->form_data ?? [];
        $consolidatedData = [];

        // Extraire et consolider les données de chaque étape
        foreach ($formData as $stepKey => $stepData) {
            if (isset($stepData['data'])) {
                $consolidatedData = array_merge($consolidatedData, $stepData['data']);
            }
        }

        // Mapper vers les colonnes de la table organisations
        return [
            'user_id' => $draft->user_id,
            'type' => $consolidatedData['type_organisation'] ?? 'association',
            'nom' => $consolidatedData['org_nom'] ?? '',
            'sigle' => $consolidatedData['org_sigle'] ?? null,
            'objet' => $consolidatedData['org_objet'] ?? '',
            'siege_social' => $consolidatedData['org_adresse_complete'] ?? '',
            'province' => $consolidatedData['org_province'] ?? '',
            'departement' => $consolidatedData['org_departement'] ?? null,
            'prefecture' => $consolidatedData['org_prefecture'] ?? '',
            'zone_type' => $consolidatedData['org_zone_type'] ?? 'urbaine',
            'latitude' => $consolidatedData['org_latitude'] ?? null,
            'longitude' => $consolidatedData['org_longitude'] ?? null,
            'email' => $consolidatedData['org_email'] ?? null,
            'telephone' => $consolidatedData['org_telephone'] ?? '',
            'site_web' => $consolidatedData['org_site_web'] ?? null,
            'date_creation' => $consolidatedData['org_date_creation'] ?? now(),
            'statut' => 'soumis',
            'is_active' => true,
            'nombre_adherents_min' => $this->getMinAdherents($consolidatedData['type_organisation'] ?? 'association')
        ];
    }

    /**
     * Créer les entités liées (fondateurs, adhérents, etc.)
     */
    private function createRelatedEntities(Organisation $organisation, OrganizationDraft $draft): void
    {
        $formData = $draft->form_data ?? [];

        // Créer les fondateurs
        if (isset($formData['step_6']['data']['fondateurs'])) {
            foreach ($formData['step_6']['data']['fondateurs'] as $fondateurData) {
                Fondateur::create([
                    'organisation_id' => $organisation->id,
                    'nip' => $fondateurData['nip'],
                    'nom' => $fondateurData['nom'],
                    'prenom' => $fondateurData['prenom'],
                    'fonction' => $fondateurData['fonction'],
                    'telephone' => $fondateurData['telephone'],
                    'email' => $fondateurData['email'] ?? null
                ]);
            }
        }

        // Créer les adhérents
        if (isset($formData['step_7']['data']['adherents'])) {
            foreach ($formData['step_7']['data']['adherents'] as $adherentData) {
                Adherent::create([
                    'organisation_id' => $organisation->id,
                    'nip' => $adherentData['nip'],
                    'nom' => $adherentData['nom'],
                    'prenom' => $adherentData['prenom'],
                    'profession' => $adherentData['profession'] ?? null,
                    'fonction' => $adherentData['fonction'] ?? 'Membre',
                    'telephone' => $adherentData['telephone'] ?? null,
                    'date_adhesion' => now(),
                    'is_active' => true
                ]);
            }
        }
    }
}