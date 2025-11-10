<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\Dossier;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * SERVICE - GESTION DES TEMPLATES DE DOCUMENTS
 * 
 * Service pour la génération et manipulation des templates de documents
 * 
 * Projet : SGLP
 */
class DocumentTemplateService
{
    /**
     * Générer des données de test pour un template
     * 
     * @param DocumentTemplate $template
     * @return array
     */
    public function generateTestData(DocumentTemplate $template): array
{
    $now = now();
    
    // ✅ Charger un dossier réel correspondant au type d'organisation
    $dossier = Dossier::with(['organisation'])
        ->whereHas('organisation', function($q) use ($template) {
            $q->where('organisation_type_id', $template->organisation_type_id);
        })
        ->latest()
        ->first();
    
    // Si aucun dossier trouvé, utiliser des données samples
    if (!$dossier || !$dossier->organisation) {
        return $this->generateSampleData($now);
    }
    
    $organisation = $dossier->organisation;
    
    // ✅ Retourner les vraies données du dossier
    return [
        'organisation' => [
            'nom' => $organisation->nom ?? 'Organisation Test',
            'sigle' => $organisation->sigle ?? 'ORG-TEST',
            'denomination' => $organisation->nom ?? 'Organisation Test',
            'objet' => $organisation->objet ?? 'Objet de l\'organisation',
            'siege_social' => $organisation->siege_social ?? 'Siège social',
            'adresse' => $organisation->siege_social ?? 'Adresse',
            'province' => $organisation->province->nom ?? 'Province',
            'departement' => $organisation->departement->nom ?? 'Département',
            'email' => $organisation->email ?? 'contact@organisation.ga',
            'telephone' => $organisation->telephone ?? '066119001',
            'type' => $organisation->organisationType->libelle ?? 'Type',
            'fondateurs_count' => $organisation->fondateurs()->count() ?? 0,
            'date_creation' => $organisation->created_at ? $organisation->created_at->format('d/m/Y') : $now->format('d/m/Y'),
        ],
        'dossier' => [
            'numero_dossier' => $dossier->numero_dossier,
            'numero' => $dossier->numero_dossier,
            'date_depot' => $dossier->date_soumission ? $dossier->date_soumission->format('d/m/Y') : $now->format('d/m/Y'),
            'date_soumission' => $dossier->date_soumission ? $dossier->date_soumission->format('d/m/Y') : $now->format('d/m/Y'),
            'date_creation' => $dossier->created_at ? $dossier->created_at->format('d/m/Y') : $now->format('d/m/Y'),
            'statut' => $dossier->statut ?? 'En cours',
        ],
        'document' => [
            'numero_document' => 'DOC-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'numero' => 'DOC-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'date_generation' => $now->format('d/m/Y H:i'),
            'date_creation' => $now->format('d/m/Y'),
            'qr_code_token' => 'QR-' . md5(uniqid()),
            'qr_code_url' => 'https://sglp.ga/verify/QR-' . md5(uniqid()),
        ],
        'agent' => [
            'nom' => auth()->user()->nom ?? 'AGENT',
            'prenom' => auth()->user()->prenom ?? 'Prénom',
            'fonction' => 'Chargé de dossier',
        ],
        'qrCode' => null,
        'signature' => null,
        'signataire' => 'LE DIRECTEUR GÉNÉRAL DES LIBERTÉS PUBLIQUES',
    ];
}

/**
 * Générer des données samples (fallback)
 * 
 * @param Carbon $now
 * @return array
 */
private function generateSampleData($now): array
{
    return [
        'organisation' => [
            'nom' => 'Association Test SGLP',
            'sigle' => 'AT-SGLP',
            'denomination' => 'Association Test SGLP',
            'objet' => 'Promouvoir le développement social et culturel au Gabon',
            'siege_social' => '123 Avenue Bouet, Quartier Plaine Orety, Libreville',
            'adresse' => '123 Avenue Bouet, Quartier Plaine Orety, Libreville',
            'province' => 'Estuaire',
            'departement' => 'Libreville',
            'email' => 'contact@association-test.ga',
            'telephone' => '066119001',
            'type' => 'Association',
            'fondateurs_count' => 15,
            'date_creation' => $now->format('d/m/Y'),
        ],
        'dossier' => [
            'numero_dossier' => 'SGLP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'numero' => 'DOSS-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'date_depot' => $now->format('d/m/Y'),
            'date_soumission' => $now->format('d/m/Y'),
            'date_creation' => $now->format('d/m/Y'),
            'statut' => 'En cours d\'instruction',
        ],
        'document' => [
            'numero_document' => 'DOC-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'numero' => 'DOC-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'date_generation' => $now->format('d/m/Y H:i'),
            'date_creation' => $now->format('d/m/Y'),
            'qr_code_token' => 'QR-' . md5(uniqid()),
            'qr_code_url' => 'https://sglp.ga/verify/QR-' . md5(uniqid()),
        ],
        'agent' => [
            'nom' => 'AGENT TEST',
            'prenom' => 'Prénom',
            'fonction' => 'Chargé de dossier',
        ],
        'qrCode' => null,
        'signature' => null,
        'signataire' => 'LE DIRECTEUR GÉNÉRAL DES LIBERTÉS PUBLIQUES',
    ];
}

    /**
     * Générer un PDF de prévisualisation
     * 
     * @param DocumentTemplate $template
     * @param array $data
     * @return string
     */
    public function generatePreviewPdf(DocumentTemplate $template, array $data)
{
    try {
        // 1. Générer le HTML à partir du template Blade
        $html = view($template->template_path, $data)->render();
        
        // 2. ✅ Générer un vrai PDF avec DomPDF
        $pdf = Pdf::loadHTML($html);
        
        // 3. Configuration du PDF selon les paramètres du template
        $pdfConfig = $template->pdf_config ?? [];
        
        $format = $pdfConfig['format'] ?? 'a4';
        $orientation = $pdfConfig['orientation'] ?? 'portrait';
        
        $pdf->setPaper($format, $orientation);
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);
        
        return $pdf;  // ✅ Retourne objet Pdf (pas string HTML)
        
    } catch (\Exception $e) {
        Log::error('Erreur génération PDF preview: ' . $e->getMessage());
        throw $e;
    }
}

    /**
     * Valider un template
     * 
     * @param string $templatePath
     * @return bool
     */
    public function validateTemplate(string $templatePath): bool
    {
        try {
            // Vérifier si le template existe
            if (!View::exists($templatePath)) {
                return false;
            }
            
            // Essayer de compiler le template
            $html = view($templatePath, $this->generateTestData(new DocumentTemplate()))->render();
            
            return !empty($html);
            
        } catch (\Exception $e) {
            Log::error('Erreur validation template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir les variables disponibles pour un template
     * 
     * @return array
     */
    public function getAvailableVariables(): array
    {
        return [
            'organisation' => [
                'nom' => 'Nom de l\'organisation',
                'sigle' => 'Sigle',
                'type' => 'Type d\'organisation',
                'adresse' => 'Adresse complète',
                'telephone' => 'Numéro de téléphone',
                'email' => 'Email de contact',
            ],
            'dossier' => [
                'numero' => 'Numéro du dossier',
                'date_soumission' => 'Date de soumission',
                'statut' => 'Statut du dossier',
            ],
            'document' => [
                'numero' => 'Numéro du document',
                'date_generation' => 'Date de génération',
                'qr_code_token' => 'Token QR Code',
            ],
        ];
    }
}