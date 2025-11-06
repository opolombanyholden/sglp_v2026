<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * SERVICE HELPER POUR GESTION D'IMAGES DANS LES PDF
 * 
 * ⭐ VERSION COMPLÈTE - ÉTAPE 1.3
 * Ce service centralise la conversion d'images en base64
 * pour une utilisation optimale avec DomPDF
 * 
 * Fonctionnalités :
 * - Conversion images en base64 (Storage, public/, resources/)
 * - Logos officiels gabonais (République, Ministère, Drapeau)
 * - Backgrounds pour PDF avec armoiries
 * - QR codes en base64
 * - Validation images pour PDF
 * - Placeholders SVG automatiques
 * 
 * Projet : SGLP
 * Date : 06 Novembre 2025
 */
class ImageHelperService
{
    /**
     * SECTION : CONVERSION IMAGES EN BASE64
     */
    
    /**
     * Convertit une image en base64 pour les PDF
     * 
     * @param string $path Chemin vers l'image
     * @param string $disk Disque de stockage (par défaut 'public')
     * @return string|null Base64 data URI ou null si échec
     */
    public function getImageAsBase64(string $path, string $disk = 'public'): ?string
    {
        try {
            // Méthode 1: Via Storage facade (recommandé)
            if (Storage::disk($disk)->exists($path)) {
                $imageContent = Storage::disk($disk)->get($path);
                $fullPath = Storage::disk($disk)->path($path);
                $mimeType = $this->getMimeType($fullPath);
                
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
            }
            
            // Méthode 2: Chemin absolu si Storage échoue
            $absolutePath = storage_path("app/{$disk}/" . ltrim($path, '/'));
            if (file_exists($absolutePath)) {
                $imageContent = file_get_contents($absolutePath);
                $mimeType = $this->getMimeType($absolutePath);
                
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
            }
            
            Log::warning('Image non trouvée pour PDF', ['path' => $path, 'disk' => $disk]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Erreur conversion image base64', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * SECTION : QR CODES
     */
    
    /**
     * Convertit un QR code en base64 pour PDF
     * 
     * @param \App\Models\QrCode $qrCode
     * @return string|null
     */
    public function getQrCodeAsBase64($qrCode): ?string
    {
        // Priorité 1: PNG base64 déjà en base
        if (!empty($qrCode->png_base64)) {
            return 'data:image/png;base64,' . $qrCode->png_base64;
        }
        
        // Priorité 2: Fichier PNG
        if (!empty($qrCode->file_path)) {
            $base64 = $this->getImageAsBase64($qrCode->file_path);
            if ($base64) {
                return $base64;
            }
        }
        
        // Priorité 3: Générer depuis URL si possible
        if (!empty($qrCode->verification_url)) {
            return $this->generateQrCodeBase64($qrCode->verification_url);
        }
        
        return null;
    }
    
    /**
     * Génère un QR code en base64 à la volée
     * 
     * @param string $url URL à encoder
     * @return string|null
     */
    private function generateQrCodeBase64(string $url): ?string
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                return null;
            }
            
            $qrData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(120)
                ->margin(2)
                ->color(0, 62, 127)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($url);
            
            return 'data:image/png;base64,' . base64_encode($qrData);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération QR code base64', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * SECTION : UTILITAIRES
     */
    
    /**
     * Détermine le MIME type d'un fichier
     * 
     * @param string $filePath
     * @return string
     */
    private function getMimeType(string $filePath): string
    {
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath);
            if ($mimeType) {
                return $mimeType;
            }
        }
        
        // Fallback basé sur l'extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp'
        ];
        
        return $mimeTypes[$extension] ?? 'image/png';
    }
    
    /**
     * Valide qu'une image peut être utilisée dans un PDF
     * 
     * @param string $path
     * @param string $disk
     * @return array ['valid' => bool, 'error' => string|null, 'size' => int]
     */
    public function validateImageForPdf(string $path, string $disk = 'public'): array
    {
        try {
            if (!Storage::disk($disk)->exists($path)) {
                return [
                    'valid' => false,
                    'error' => 'Fichier non trouvé',
                    'size' => 0
                ];
            }
            
            $size = Storage::disk($disk)->size($path);
            $maxSize = 2 * 1024 * 1024; // 2MB max pour PDF
            
            if ($size > $maxSize) {
                return [
                    'valid' => false,
                    'error' => 'Fichier trop volumineux (max 2MB)',
                    'size' => $size
                ];
            }
            
            $fullPath = Storage::disk($disk)->path($path);
            $mimeType = $this->getMimeType($fullPath);
            
            $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
            if (!in_array($mimeType, $allowedTypes)) {
                return [
                    'valid' => false,
                    'error' => 'Type de fichier non supporté: ' . $mimeType,
                    'size' => $size
                ];
            }
            
            return [
                'valid' => true,
                'error' => null,
                'size' => $size
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => 'Erreur validation: ' . $e->getMessage(),
                'size' => 0
            ];
        }
    }
    
    /**
     * Crée un placeholder SVG pour les images manquantes
     * 
     * @param string $text Texte à afficher
     * @param int $width Largeur
     * @param int $height Hauteur
     * @return string SVG en base64
     */
    public function createPlaceholderSvg(string $text = 'Image', int $width = 100, int $height = 100): string
    {
        $svg = "<?xml version='1.0' encoding='UTF-8'?>
        <svg width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg'>
            <rect width='{$width}' height='{$height}' fill='#f8f9fa' stroke='#003f7f' stroke-width='2' stroke-dasharray='4,4'/>
            <text x='50%' y='50%' font-family='Arial' font-size='12' text-anchor='middle' 
                  dominant-baseline='middle' fill='#666'>{$text}</text>
        </svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * SECTION : LOGOS OFFICIELS GABONAIS
     */
    
    /**
     * Obtenir le logo République Gabonaise (Armoiries) en base64
     * 
     * Chemins recherchés (dans l'ordre) :
     * - storage/app/public/images/logo-gabon.png
     * - storage/app/public/images/armoiries-gabon.png
     * - public/images/logo-gabon.png
     * - public/logos/gabon.png
     * 
     * Si aucun logo trouvé : génère un placeholder SVG automatiquement
     * 
     * @return string Base64 data URI ou placeholder SVG
     */
    public function getLogoGabonBase64(): string
    {
        // Chemins possibles pour le logo
        $possiblePaths = [
            'images/logo-gabon.png',
            'images/armoiries-gabon.png',
            'images/republique-gabon.png',
            'logos/gabon.png',
        ];
        
        // Essayer chaque chemin
        foreach ($possiblePaths as $path) {
            // Essayer dans storage/app/public
            $base64 = $this->getImageAsBase64($path, 'public');
            if ($base64) {
                Log::info('Logo Gabon trouvé', ['path' => $path]);
                return $base64;
            }
            
            // Essayer dans public/
            $publicPath = public_path($path);
            if (file_exists($publicPath)) {
                try {
                    $imageContent = file_get_contents($publicPath);
                    $mimeType = $this->getMimeType($publicPath);
                    Log::info('Logo Gabon trouvé dans public/', ['path' => $path]);
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        // Si aucun logo trouvé, retourner placeholder SVG
        Log::info('Logo Gabon non trouvé, génération placeholder SVG');
        return $this->createLogoGabonPlaceholder();
    }
    
    /**
     * Obtenir le logo Ministère de l'Intérieur en base64
     * 
     * Chemins recherchés (dans l'ordre) :
     * - storage/app/public/images/logo-ministere.png
     * - storage/app/public/images/misd.png
     * - public/images/logo-ministere.png
     * 
     * Si aucun logo trouvé : génère un placeholder SVG automatiquement
     * 
     * @return string Base64 data URI ou placeholder SVG
     */
    public function getLogoMinistereBase64(): string
    {
        $possiblePaths = [
            'images/logo-ministere.png',
            'images/logo-interieur.png',
            'images/misd.png',
            'logos/ministere.png',
        ];
        
        foreach ($possiblePaths as $path) {
            $base64 = $this->getImageAsBase64($path, 'public');
            if ($base64) {
                Log::info('Logo Ministère trouvé', ['path' => $path]);
                return $base64;
            }
            
            $publicPath = public_path($path);
            if (file_exists($publicPath)) {
                try {
                    $imageContent = file_get_contents($publicPath);
                    $mimeType = $this->getMimeType($publicPath);
                    Log::info('Logo Ministère trouvé dans public/', ['path' => $path]);
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        Log::info('Logo Ministère non trouvé, génération placeholder SVG');
        return $this->createLogoMinisterePlaceholder();
    }
    
    /**
     * Obtenir le drapeau du Gabon en base64
     * 
     * Chemins recherchés (dans l'ordre) :
     * - storage/app/public/images/drapeau-gabon.png
     * - public/images/drapeau-gabon.png
     * 
     * Si aucun drapeau trouvé : génère un SVG du drapeau gabonais
     * 
     * @return string Base64 data URI ou SVG drapeau
     */
    public function getDrapeauGabonBase64(): string
    {
        $possiblePaths = [
            'images/drapeau-gabon.png',
            'images/flag-gabon.png',
            'flags/gabon.png',
        ];
        
        foreach ($possiblePaths as $path) {
            $base64 = $this->getImageAsBase64($path, 'public');
            if ($base64) {
                Log::info('Drapeau Gabon trouvé', ['path' => $path]);
                return $base64;
            }
            
            $publicPath = public_path($path);
            if (file_exists($publicPath)) {
                try {
                    $imageContent = file_get_contents($publicPath);
                    $mimeType = $this->getMimeType($publicPath);
                    Log::info('Drapeau Gabon trouvé dans public/', ['path' => $path]);
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        Log::info('Drapeau Gabon non trouvé, génération placeholder SVG');
        return $this->createDrapeauGabonPlaceholder();
    }
    
    /**
     * SECTION : PLACEHOLDERS LOGOS OFFICIELS
     */
    
    /**
     * Créer placeholder SVG pour logo République Gabonaise
     * 
     * Génère un logo stylisé avec les couleurs nationales :
     * - Vert (#009e3f), Jaune (#ffcd00), Bleu (#003f7f)
     * 
     * @return string SVG en base64
     */
    protected function createLogoGabonPlaceholder(): string
    {
        $svg = "<?xml version='1.0' encoding='UTF-8'?>
        <svg width='100' height='100' xmlns='http://www.w3.org/2000/svg'>
            <rect width='100' height='100' fill='#009e3f' rx='10'/>
            <circle cx='50' cy='40' r='25' fill='#ffcd00' stroke='#003f7f' stroke-width='2'/>
            <text x='50' y='75' font-family='Arial' font-size='10' font-weight='bold' 
                  text-anchor='middle' fill='#ffffff'>GABON</text>
            <text x='50' y='88' font-family='Arial' font-size='8' 
                  text-anchor='middle' fill='#ffffff'>République</text>
        </svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Créer placeholder SVG pour logo Ministère
     * 
     * Génère un logo stylisé pour le Ministère de l'Intérieur
     * 
     * @return string SVG en base64
     */
    protected function createLogoMinisterePlaceholder(): string
    {
        $svg = "<?xml version='1.0' encoding='UTF-8'?>
        <svg width='100' height='100' xmlns='http://www.w3.org/2000/svg'>
            <rect width='100' height='100' fill='#003f7f' rx='10'/>
            <rect x='25' y='25' width='50' height='50' fill='#ffcd00' rx='5'/>
            <text x='50' y='55' font-family='Arial' font-size='18' font-weight='bold' 
                  text-anchor='middle' fill='#003f7f'>MI</text>
            <text x='50' y='88' font-family='Arial' font-size='8' 
                  text-anchor='middle' fill='#ffffff'>Ministère Intérieur</text>
        </svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Créer placeholder SVG pour drapeau Gabon
     * 
     * Génère le drapeau gabonais officiel en SVG :
     * - Bande verte (haut) : #009e3f
     * - Bande jaune (milieu) : #ffcd00
     * - Bande bleue (bas) : #003f7f
     * 
     * @return string SVG en base64
     */
    protected function createDrapeauGabonPlaceholder(): string
    {
        // Couleurs du drapeau gabonais : Vert, Jaune, Bleu
        $svg = "<?xml version='1.0' encoding='UTF-8'?>
        <svg width='150' height='100' xmlns='http://www.w3.org/2000/svg'>
            <rect width='150' height='33.33' fill='#009e3f'/>
            <rect y='33.33' width='150' height='33.34' fill='#ffcd00'/>
            <rect y='66.67' width='150' height='33.33' fill='#003f7f'/>
        </svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * SECTION : BACKGROUNDS POUR PDF
     */
    
    /**
     * Obtenir le background avec armoiries du Gabon en filigrane
     * 
     * Génère un CSS pour afficher les armoiries en arrière-plan
     * avec une faible opacité pour effet filigrane.
     * 
     * @param float $opacity Opacité du background (0.0 à 1.0), défaut 0.05
     * @return string CSS du background ou chaîne vide si logo indisponible
     */
    public function getBackgroundArmoiriesGabon(float $opacity = 0.05): string
    {
        // Essayer de charger l'image des armoiries
        $logoBase64 = $this->getLogoGabonBase64();
        
        // Si c'est un placeholder SVG avec texte, ne pas l'utiliser en background
        // (car le texte serait répété en filigrane)
        if (strpos($logoBase64, 'GABON') !== false || strpos($logoBase64, 'République') !== false) {
            Log::info('Background armoiries : placeholder SVG non utilisé (contient du texte)');
            return '';
        }
        
        return $this->generateBackgroundCss($logoBase64, [
            'opacity' => $opacity,
            'size' => '400px 400px',
            'position' => 'center center',
            'repeat' => 'no-repeat',
        ]);
    }
    
    /**
     * Générer le CSS pour un background image
     * 
     * Crée un élément pseudo ::after sur body avec l'image en arrière-plan.
     * Utilisé pour les filigranes, logos en transparence, etc.
     * 
     * @param string $imageBase64 Image en base64 (data URI)
     * @param array $options Options de configuration
     *   - opacity: float (0.0 à 1.0)
     *   - size: string CSS (ex: 'contain', '400px', '50% auto')
     *   - position: string CSS (ex: 'center', 'top left')
     *   - repeat: string CSS (ex: 'no-repeat', 'repeat')
     *   - z_index: int (position dans le z-index)
     * @return string CSS du background
     */
    public function generateBackgroundCss(string $imageBase64, array $options = []): string
    {
        // Configuration par défaut
        $config = array_merge([
            'opacity' => 0.1,
            'size' => 'contain',
            'position' => 'center',
            'repeat' => 'no-repeat',
            'z_index' => -1,
        ], $options);
        
        return "
        <style>
            body::after {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: url('{$imageBase64}');
                background-size: {$config['size']};
                background-position: {$config['position']};
                background-repeat: {$config['repeat']};
                opacity: {$config['opacity']};
                z-index: {$config['z_index']};
                pointer-events: none;
            }
        </style>
        ";
    }
    
    /**
     * Générer un watermark image pour PDF
     * 
     * Alternative au watermark texte CSS.
     * Affiche une image en filigrane au centre du document.
     * 
     * @param string $imagePath Chemin vers l'image (relatif à storage ou public)
     * @param array $options Options de configuration
     * @return string CSS du watermark ou chaîne vide si erreur
     */
    public function generateImageWatermark(string $imagePath, array $options = []): string
    {
        // Essayer de charger l'image
        $imageBase64 = $this->getImageAsBase64($imagePath, 'public');
        
        if (!$imageBase64) {
            // Essayer dans public/
            $publicPath = public_path($imagePath);
            if (file_exists($publicPath)) {
                try {
                    $imageContent = file_get_contents($publicPath);
                    $mimeType = $this->getMimeType($publicPath);
                    $imageBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                } catch (\Exception $e) {
                    Log::error('Erreur chargement watermark image', [
                        'path' => $imagePath,
                        'error' => $e->getMessage()
                    ]);
                    return '';
                }
            } else {
                Log::warning('Image watermark introuvable', ['path' => $imagePath]);
                return '';
            }
        }
        
        // Configuration par défaut
        $config = array_merge([
            'opacity' => 0.1,
            'size' => '400px',
            'rotation' => -45,
        ], $options);
        
        return "
        <style>
            body::before {
                content: '';
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate({$config['rotation']}deg);
                width: {$config['size']};
                height: {$config['size']};
                background-image: url('{$imageBase64}');
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
                opacity: {$config['opacity']};
                z-index: -1;
                pointer-events: none;
                user-select: none;
            }
        </style>
        ";
    }
}