<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\DocumentGeneration;
use App\Models\Organisation;
use App\Models\Dossier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * SERVICE DE GÉNÉRATION DE DOCUMENTS
 * 
 * ⭐ VERSION AMÉLIORÉE - ÉTAPE 1.3 COMPLÈTE
 * Service principal pour générer les documents PDF à la volée
 * à partir des templates Blade configurés
 * 
 * Améliorations Étape 1.1 :
 * - Variables dynamiques avancées (dirigeants, fondateurs, adhérents)
 * - Constantes ministérielles et républicaines
 * - Statistiques enrichies
 * - Formatage automatique des adresses
 * 
 * Améliorations Étape 1.2 :
 * - Watermark texte CSS automatique
 * - Détection automatique du texte watermark
 * - Configuration personnalisable
 * 
 * Améliorations Étape 1.3 :
 * - Intégration ImageHelperService
 * - Logos officiels en base64 (Gabon, Ministère, Drapeau)
 * - Backgrounds avec armoiries
 * - Watermark image
 * - Placeholders SVG automatiques
 * 
 * Projet : SGLP
 * Date : 06 Novembre 2025
 */
class DocumentGenerationService
{
    protected QRCodeService $qrCodeService;
    protected DocumentNumberingService $numberingService;
    protected ImageHelperService $imageHelper;  // ⭐ NOUVEAU

    public function __construct(
        QRCodeService $qrCodeService,
        DocumentNumberingService $numberingService,
        ImageHelperService $imageHelper  // ⭐ NOUVEAU
    ) {
        $this->qrCodeService = $qrCodeService;
        $this->numberingService = $numberingService;
        $this->imageHelper = $imageHelper;  // ⭐ NOUVEAU
    }

    /**
     * Générer un document à la volée
     * 
     * @param DocumentTemplate $template Template à utiliser
     * @param array $data Données du document
     * @return array ['pdf' => stream, 'metadata' => DocumentGeneration, 'filename' => string]
     * @throws \Exception
     */
    public function generate(DocumentTemplate $template, array $data): array
    {
        try {
            // 1. Vérifier que le template existe
            if (!$template->templateExists()) {
                throw new \Exception("Le template Blade '{$template->template_path}' n'existe pas.");
            }

            // 2. Générer numéro unique
            $numeroDocument = $this->numberingService->generate(
                $template->type_document,
                $data['organisation_id']
            );

            // 3. Générer token QR code
            $qrToken = $this->qrCodeService->generateToken();
            $qrUrl = $this->qrCodeService->getVerificationUrl($qrToken);

            // 4. Préparer les variables (VERSION AMÉLIORÉE)
            $variables = $this->prepareVariables($data, $numeroDocument, $qrUrl);

            // 5. Valider les variables requises
            $this->validateRequiredVariables($template, $variables);

            // 6. Générer hash de vérification
            $hash = $this->generateHash($numeroDocument, $variables);

            // 7. Enregistrer les métadonnées (LOG uniquement)
            $generation = DocumentGeneration::create([
                'document_template_id' => $template->id,
                'dossier_id' => $data['dossier_id'] ?? null,
                'dossier_validation_id' => $data['dossier_validation_id'] ?? null,
                'organisation_id' => $data['organisation_id'],
                'numero_document' => $numeroDocument,
                'type_document' => $template->type_document,
                'qr_code_token' => $qrToken,
                'qr_code_url' => $qrUrl,
                'hash_verification' => $hash,
                'variables_data' => $variables,
                'generated_by' => Auth::id(),
                'generated_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // 8. Générer le HTML avec variables
            $html = $this->renderTemplate($template, $variables);

            // 9. Générer le PDF en mémoire
            $pdf = $this->generatePDF($html, $template);

            Log::info('Document généré avec succès', [
                'template_id' => $template->id,
                'numero_document' => $numeroDocument,
                'organisation_id' => $data['organisation_id'],
            ]);

            return [
                'pdf' => $pdf,
                'metadata' => $generation,
                'filename' => $this->generateFilename($template, $numeroDocument),
            ];

        } catch (\Exception $e) {
            Log::error('Erreur génération document', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Régénérer un document existant
     * 
     * @param DocumentGeneration $generation Document à régénérer
     * @return array
     * @throws \Exception
     */
    public function regenerate(DocumentGeneration $generation): array
    {
        if (!$generation->is_valid) {
            throw new \Exception('Ce document a été invalidé et ne peut être régénéré.');
        }

        // Incrémenter le compteur de téléchargement
        $generation->incrementDownloadCount();

        $template = $generation->template;

        // Vérifier que le template existe toujours
        if (!$template->templateExists()) {
            throw new \Exception("Le template Blade '{$template->template_path}' n'existe plus.");
        }

        // Générer le HTML avec les variables sauvegardées
        $html = $this->renderTemplate($template, $generation->variables_data);

        // Générer le PDF
        $pdf = $this->generatePDF($html, $template);

        Log::info('Document régénéré', [
            'generation_id' => $generation->id,
            'numero_document' => $generation->numero_document,
        ]);

        return [
            'pdf' => $pdf,
            'metadata' => $generation,
            'filename' => $this->generateFilename($template, $generation->numero_document),
        ];
    }

    /**
     * Préparer les variables pour le template
     * 
     * ⭐ VERSION AMÉLIORÉE avec variables dynamiques avancées
     * 
     * @param array $data Données brutes
     * @param string $numeroDocument Numéro du document
     * @param string $qrUrl URL du QR code
     * @return array Variables préparées
     */
    protected function prepareVariables(array $data, string $numeroDocument, string $qrUrl): array
    {
        // Charger l'organisation avec toutes ses relations
        $organisation = Organisation::with([
            'organisationType',
            'fondateurs' => function($query) {
                $query->orderBy('ordre')->limit(10);
            },
            'adherentsActifs',
            'etablissements',
            'dossiers'
        ])->findOrFail($data['organisation_id']);
        
        $dossier = isset($data['dossier_id']) ? Dossier::find($data['dossier_id']) : null;

        // ========================================
        // VARIABLES DE BASE
        // ========================================
        $variables = [
            // Organisation (enrichie)
            'organisation' => [
                'id' => $organisation->id,
                'nom' => $organisation->nom,
                'sigle' => $organisation->sigle ?? '',
                'type' => $organisation->organisationType->nom ?? 'Organisation',
                'type_code' => $organisation->organisationType->code ?? 'organisation',
                'numero_recepisse' => $organisation->numero_recepisse ?? 'En cours',
                'date_creation' => $organisation->date_creation ? $organisation->date_creation->format('d/m/Y') : 'N/A',
                'objet' => $organisation->objet ?? 'Non spécifié',
                
                // Adresse complète
                'siege_social' => $organisation->siege_social ?? '',
                'adresse_complete' => $this->formatAdresseComplete($organisation),
                'province' => $organisation->province ?? '',
                'departement' => $organisation->departement ?? '',
                'commune' => $organisation->commune ?? '',
                'quartier' => $organisation->quartier ?? '',
                'boite_postale' => $organisation->boite_postale ?? '',
                
                // Contacts
                'telephone' => $organisation->telephone ?? 'Non renseigné',
                'email' => $organisation->email ?? '',
                'site_web' => $organisation->site_web ?? '',
                
                // Statuts
                'statut' => $organisation->statut ?? '',
                'is_active' => $organisation->is_active ?? false,
            ],

            // ========================================
            // DIRIGEANTS (Bureau Exécutif)
            // ========================================
            'dirigeants' => [
                'president' => $this->getPersonneByRole($organisation, 'president'),
                'vice_president' => $this->getPersonneByRole($organisation, 'vice_president'),
                'secretaire_general' => $this->getPersonneByRole($organisation, 'secretaire_general'),
                'tresorier' => $this->getPersonneByRole($organisation, 'tresorier'),
                'liste_complete' => $this->getDirigeantsComplets($organisation),
            ],

            // ========================================
            // FONDATEURS
            // ========================================
            'fondateurs' => [
                'nombre' => $organisation->fondateurs->count(),
                'liste' => $organisation->fondateurs->map(function($fondateur) {
                    return [
                        'nom' => $fondateur->nom,
                        'prenom' => $fondateur->prenom,
                        'nip' => $fondateur->nip ?? '',
                        'nom_complet' => trim($fondateur->prenom . ' ' . $fondateur->nom),
                    ];
                })->toArray(),
                'premier' => $organisation->fondateurs->first() ? [
                    'nom_complet' => trim($organisation->fondateurs->first()->prenom . ' ' . $organisation->fondateurs->first()->nom),
                    'nip' => $organisation->fondateurs->first()->nip ?? '',
                ] : null,
            ],

            // ========================================
            // ADHÉRENTS & STATISTIQUES
            // ========================================
            'adherents' => [
                'total' => $organisation->adherentsActifs->count(),
                'hommes' => $organisation->adherentsActifs->where('sexe', 'M')->count(),
                'femmes' => $organisation->adherentsActifs->where('sexe', 'F')->count(),
                'pourcentage_femmes' => $organisation->adherentsActifs->count() > 0 
                    ? round(($organisation->adherentsActifs->where('sexe', 'F')->count() / $organisation->adherentsActifs->count()) * 100, 1)
                    : 0,
            ],

            // ========================================
            // ÉTABLISSEMENTS (Antennes)
            // ========================================
            'etablissements' => [
                'nombre' => $organisation->etablissements->count(),
                'provinces' => $organisation->etablissements->pluck('province')->unique()->values()->toArray(),
            ],

            // ========================================
            // MANDATAIRE (Représentant légal)
            // ========================================
            'mandataire' => $this->getMandataire($organisation),

            // ========================================
            // DOCUMENT (Métadonnées)
            // ========================================
            'document' => [
                'numero_document' => $numeroDocument,
                'date_generation' => now()->format('d/m/Y'),
                'date_generation_longue' => $this->formatDateLongue(now()),
                'heure_generation' => now()->format('H:i'),
                'annee' => now()->year,
                'qr_code_url' => $qrUrl,
            ],

            // ========================================
            // DOSSIER (si fourni)
            // ========================================
            'dossier' => $dossier ? [
                'id' => $dossier->id,
                'numero_dossier' => $dossier->numero_dossier,
                'date_depot' => $dossier->date_depot ? $dossier->date_depot->format('d/m/Y') : 'N/A',
                'statut' => $dossier->statut_label ?? 'En cours',
                'operation_type' => $dossier->operationType->nom ?? 'Opération',
            ] : null,

            // ========================================
            // CONSTANTES MINISTÉRIELLES
            // ========================================
            'ministere' => [
                'nom_complet' => 'MINISTÈRE DE L\'INTÉRIEUR ET DE LA SÉCURITÉ',
                'nom_court' => 'Ministère de l\'Intérieur',
                'sigle' => 'MISD',
                'direction' => 'Direction Générale des Affaires Politiques et Associatives',
                'direction_sigle' => 'DGAPA',
            ],

            // ========================================
            // CONSTANTES RÉPUBLICAINES
            // ========================================
            'republique' => [
                'nom' => 'RÉPUBLIQUE GABONAISE',
                'devise' => 'Union - Travail - Justice',
                'capitale' => 'Libreville',
            ],

            // ========================================
            // INFORMATIONS GÉOGRAPHIQUES
            // ========================================
            'geographie' => [
                'lieu_edition' => 'Libreville',
                'pays' => 'Gabon',
            ],

            // ========================================
            // COULEURS NATIONALES (pour styling)
            // ========================================
            'couleurs' => [
                'vert' => '#009e3f',
                'jaune' => '#ffcd00',
                'bleu' => '#003f7f',
                'rouge' => '#8b1538',
            ],
        ];

        // ========================================
        // ⭐ NOUVEAU : LOGOS OFFICIELS EN BASE64
        // ========================================
        $variables['logos'] = [
            'gabon' => $this->imageHelper->getLogoGabonBase64(),
            'ministere' => $this->imageHelper->getLogoMinistereBase64(),
            'drapeau' => $this->imageHelper->getDrapeauGabonBase64(),
        ];

        // ========================================
        // ⭐ NOUVEAU : BACKGROUNDS DISPONIBLES
        // ========================================
        $variables['backgrounds'] = [
            'armoiries' => $this->imageHelper->getBackgroundArmoiriesGabon(),
        ];

        return $variables;
    }

    /**
     * Obtenir une personne par son rôle dans l'organisation
     */
    protected function getPersonneByRole(Organisation $organisation, string $role): ?array
    {
        $personne = $organisation->personnes()
            ->where('role', $role)
            ->where('is_active', true)
            ->first();

        if (!$personne) {
            return null;
        }

        return [
            'nom' => $personne->nom,
            'prenom' => $personne->prenom,
            'nom_complet' => trim($personne->prenom . ' ' . $personne->nom),
            'nip' => $personne->nip ?? '',
            'email' => $personne->email ?? '',
            'telephone' => $personne->telephone ?? '',
            'role' => $personne->role_label ?? ucfirst(str_replace('_', ' ', $role)),
        ];
    }

    /**
     * Obtenir la liste complète des dirigeants
     */
    protected function getDirigeantsComplets(Organisation $organisation): array
    {
        return $organisation->personnes()
            ->where('is_active', true)
            ->whereIn('role', ['president', 'vice_president', 'secretaire_general', 'tresorier', 'membre_bureau'])
            ->orderByRaw("FIELD(role, 'president', 'vice_president', 'secretaire_general', 'tresorier', 'membre_bureau')")
            ->get()
            ->map(function($personne) {
                return [
                    'nom_complet' => trim($personne->prenom . ' ' . $personne->nom),
                    'role' => $personne->role_label ?? 'Membre',
                    'nip' => $personne->nip ?? '',
                ];
            })
            ->toArray();
    }

    /**
     * Obtenir le mandataire (représentant légal)
     */
    protected function getMandataire(Organisation $organisation): ?array
    {
        // Le mandataire est généralement le Président ou le Secrétaire Général
        $mandataire = $organisation->personnes()
            ->where('is_mandataire', true)
            ->where('is_active', true)
            ->first();

        if (!$mandataire) {
            // Fallback sur le président
            $mandataire = $organisation->personnes()
                ->where('role', 'president')
                ->where('is_active', true)
                ->first();
        }

        if (!$mandataire) {
            return null;
        }

        return [
            'nom' => $mandataire->nom,
            'prenom' => $mandataire->prenom,
            'nom_complet' => trim($mandataire->prenom . ' ' . $mandataire->nom),
            'nip' => $mandataire->nip ?? '',
            'email' => $mandataire->email ?? '',
            'telephone' => $mandataire->telephone ?? '',
            'role' => $mandataire->role_label ?? 'Mandataire',
            'qualite' => $mandataire->qualite ?? 'Représentant légal',
        ];
    }

    /**
     * Formater l'adresse complète de l'organisation
     */
    protected function formatAdresseComplete(Organisation $organisation): string
    {
        $parts = array_filter([
            $organisation->siege_social,
            $organisation->quartier,
            $organisation->commune,
            $organisation->province,
        ]);

        $adresse = implode(', ', $parts);

        if ($organisation->boite_postale) {
            $adresse .= ' - BP ' . $organisation->boite_postale;
        }

        return $adresse ?: 'Adresse non renseignée';
    }

    /**
     * Formater une date en format long français
     */
    protected function formatDateLongue(\DateTime $date): string
    {
        $mois = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];

        $jour = $date->format('j');
        $moisNum = (int) $date->format('n');
        $annee = $date->format('Y');

        return "le {$jour} {$mois[$moisNum]} {$annee}";
    }

    /**
     * Valider que toutes les variables requises sont présentes
     * 
     * @param DocumentTemplate $template
     * @param array $variables
     * @throws \Exception
     */
    protected function validateRequiredVariables(DocumentTemplate $template, array $variables): void
    {
        if (empty($template->required_variables)) {
            return;
        }

        $flatVariables = $this->flattenArray($variables);
        $missingVars = [];

        foreach ($template->required_variables as $requiredVar) {
            if (!isset($flatVariables[$requiredVar])) {
                $missingVars[] = $requiredVar;
            }
        }

        if (!empty($missingVars)) {
            throw new \Exception('Variables requises manquantes : ' . 
                implode(', ', $missingVars));
        }
    }

    /**
     * Rendre le template HTML avec variables
     * 
     * @param DocumentTemplate $template Template
     * @param array $variables Variables
     * @return string HTML généré
     */
    protected function renderTemplate(DocumentTemplate $template, array $variables): string
    {
        // Générer le QR code SVG
        $qrCodeSvg = $template->has_qr_code 
            ? $this->qrCodeService->generateSVG($variables['document']['qr_code_url'])
            : '';

        // ========================================
        // ÉTAPE 1.2 : Générer le CSS du watermark
        // ========================================
        $watermarkCss = '';
        if ($template->has_watermark) {
            $watermarkCss = $this->generateWatermarkCss($template, $variables);
        }

        // ========================================
        // ⭐ ÉTAPE 1.3 : Générer background CSS si activé
        // ========================================
        $backgroundCss = '';
        if (!empty($template->metadata['background']['enabled'])) {
            $backgroundType = $template->metadata['background']['type'] ?? 'armoiries';
            
            if ($backgroundType === 'armoiries') {
                $opacity = $template->metadata['background']['opacity'] ?? 0.05;
                $backgroundCss = $this->imageHelper->getBackgroundArmoiriesGabon($opacity);
            } elseif ($backgroundType === 'custom' && !empty($template->metadata['background']['image_path'])) {
                $backgroundCss = $this->imageHelper->generateImageWatermark(
                    $template->metadata['background']['image_path'],
                    $template->metadata['background']['options'] ?? []
                );
            }
        }

        // Rendre le template Blade
        $html = View::make($template->template_path, [
            ...$variables,
            'qr_code_svg' => $qrCodeSvg,
            'has_qr_code' => $template->has_qr_code,
            'has_signature' => $template->has_signature,
            'has_watermark' => $template->has_watermark,
            'watermark_css' => $watermarkCss,
            'background_css' => $backgroundCss,  // ⭐ NOUVEAU
            'has_background' => !empty($backgroundCss),  // ⭐ NOUVEAU
            'signature_path' => $template->getSignatureFullPath(),
        ])->render();

        // ========================================
        // ÉTAPE 1.2 : Injecter le CSS watermark dans le HTML
        // ========================================
        if ($template->has_watermark && !empty($watermarkCss)) {
            $html = $this->injectWatermarkCss($html, $watermarkCss);
        }

        // ========================================
        // ⭐ ÉTAPE 1.3 : Injecter le background CSS dans le HTML
        // ========================================
        if (!empty($backgroundCss)) {
            $html = $this->injectBackgroundCss($html, $backgroundCss);
        }

        return $html;
    }

    /**
     * Générer le PDF en mémoire
     * 
     * @param string $html HTML du document
     * @param DocumentTemplate $template Template
     * @return \Barryvdh\DomPDF\PDF
     */
    protected function generatePDF(string $html, DocumentTemplate $template)
    {
        $margins = $template->getPdfMargins();

        $pdf = Pdf::loadHTML($html)
            ->setPaper($template->getPdfFormat(), $template->getPdfOrientation())
            ->setOption('margin_top', $margins['top'])
            ->setOption('margin_bottom', $margins['bottom'])
            ->setOption('margin_left', $margins['left'])
            ->setOption('margin_right', $margins['right'])
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf;
    }

    /**
     * Générer le nom du fichier PDF
     * 
     * @param DocumentTemplate $template Template
     * @param string $numeroDocument Numéro du document
     * @return string Nom du fichier
     */
    protected function generateFilename(DocumentTemplate $template, string $numeroDocument): string
    {
        $slug = \Str::slug($template->nom);
        $cleanNumber = str_replace(['/', '\\', '-'], '_', $numeroDocument);
        
        return "{$slug}_{$cleanNumber}.pdf";
    }

    /**
     * Générer hash de vérification
     * 
     * @param string $numeroDocument Numéro du document
     * @param array $variables Variables utilisées
     * @return string Hash SHA-256
     */
    protected function generateHash(string $numeroDocument, array $variables): string
    {
        return hash('sha256', $numeroDocument . json_encode($variables) . config('app.key'));
    }

    /**
     * Aplatir un tableau multidimensionnel avec notation point
     * 
     * @param array $array Tableau à aplatir
     * @param string $prefix Préfixe pour les clés
     * @return array Tableau aplati
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
            
            if (is_array($value) && !empty($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }

    /**
     * ÉTAPE 1.2 : Générer le CSS pour le watermark
     * 
     * @param DocumentTemplate $template
     * @param array $variables
     * @return string CSS du watermark
     */
    protected function generateWatermarkCss(DocumentTemplate $template, array $variables): string
    {
        // Configuration par défaut du watermark
        $config = [
            'text' => $this->getWatermarkText($template, $variables),
            'opacity' => 0.1,
            'rotation' => -45,
            'font_size' => '120px',
            'color' => '#009e3f', // Vert Gabon par défaut
            'font_weight' => 'bold',
            'z_index' => -1,
        ];

        // Permettre la personnalisation via metadata du template
        if (!empty($template->metadata['watermark'])) {
            $config = array_merge($config, $template->metadata['watermark']);
        }

        // Générer le CSS
        return "
            <style>
                body::before {
                    content: '{$config['text']}';
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate({$config['rotation']}deg);
                    font-size: {$config['font_size']};
                    font-weight: {$config['font_weight']};
                    color: {$config['color']};
                    opacity: {$config['opacity']};
                    z-index: {$config['z_index']};
                    white-space: nowrap;
                    pointer-events: none;
                    user-select: none;
                }
            </style>
        ";
    }

    /**
     * ÉTAPE 1.2 : Déterminer le texte du watermark
     * 
     * @param DocumentTemplate $template
     * @param array $variables
     * @return string Texte du watermark
     */
    protected function getWatermarkText(DocumentTemplate $template, array $variables): string
    {
        // Si le texte est défini dans les metadata du template
        if (!empty($template->metadata['watermark']['text'])) {
            return $template->metadata['watermark']['text'];
        }

        // Texte par défaut selon le type de document
        $defaults = [
            'recepisse_provisoire' => 'PROVISOIRE',
            'recepisse_definitif' => 'DOCUMENT OFFICIEL',
            'accuse_reception' => 'ACCUSÉ DE RÉCEPTION',
            'certificat' => 'CONFIDENTIEL',
            'attestation' => 'ORIGINAL',
        ];

        $typeDocument = $template->type_document;
        
        // Chercher une correspondance
        foreach ($defaults as $key => $text) {
            if (stripos($typeDocument, $key) !== false) {
                return $text;
            }
        }

        // Par défaut
        return 'DOCUMENT OFFICIEL';
    }

    /**
     * ÉTAPE 1.2 : Injecter le CSS watermark dans le HTML
     * 
     * @param string $html HTML original
     * @param string $watermarkCss CSS à injecter
     * @return string HTML modifié
     */
    protected function injectWatermarkCss(string $html, string $watermarkCss): string
    {
        // Chercher la balise </head> pour injecter le CSS avant
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $watermarkCss . "\n</head>", $html);
        } 
        // Si pas de </head>, chercher <style>
        elseif (stripos($html, '<style>') !== false) {
            $html = str_ireplace('<style>', '<style>' . "\n" . $watermarkCss, $html);
        }
        // Sinon, ajouter au début du body
        else {
            $html = str_ireplace('<body>', '<body>' . "\n" . $watermarkCss, $html);
        }

        return $html;
    }

    /**
     * ⭐ ÉTAPE 1.3 : Générer un watermark image (utilise ImageHelperService)
     * 
     * Utilise une image de logo en filigrane au lieu de texte CSS
     * 
     * @param DocumentTemplate $template
     * @param string $imagePath Chemin vers l'image du watermark
     * @return string CSS du watermark image
     */
    protected function generateImageWatermarkCss(DocumentTemplate $template, string $imagePath): string
    {
        // Utiliser ImageHelperService pour générer le watermark
        $options = [
            'opacity' => $template->metadata['watermark']['opacity'] ?? 0.1,
            'size' => $template->metadata['watermark']['size'] ?? '400px',
            'rotation' => $template->metadata['watermark']['rotation'] ?? -45,
        ];
        
        return $this->imageHelper->generateImageWatermark($imagePath, $options);
    }

    /**
     * ⭐ ÉTAPE 1.3 : Injecter le CSS background dans le HTML
     * 
     * @param string $html HTML original
     * @param string $backgroundCss CSS à injecter
     * @return string HTML modifié
     */
    protected function injectBackgroundCss(string $html, string $backgroundCss): string
    {
        // Chercher la balise </head> pour injecter le CSS avant
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $backgroundCss . "\n</head>", $html);
        } 
        // Si pas de </head>, chercher <style>
        elseif (stripos($html, '<style>') !== false) {
            $html = str_ireplace('<style>', '<style>' . "\n" . $backgroundCss, $html);
        }
        // Sinon, ajouter après le watermark ou au début du body
        elseif (stripos($html, '<body>') !== false) {
            $html = str_ireplace('<body>', '<body>' . "\n" . $backgroundCss, $html);
        }

        return $html;
    }
}