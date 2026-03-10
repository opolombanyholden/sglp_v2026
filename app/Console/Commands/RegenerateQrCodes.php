<?php

namespace App\Console\Commands;

use App\Models\QrCode;
use App\Services\QRCodeService;
use Illuminate\Console\Command;

class RegenerateQrCodes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'qr:regenerate 
                            {--missing-png : Regénérer seulement les QR codes sans PNG}
                            {--missing-svg : Regénérer seulement les QR codes sans SVG}
                            {--all : Regénérer tous les QR codes}
                            {--code= : Regénérer un QR code spécifique par son code}';

    /**
     * The console command description.
     */
    protected $description = 'Regénérer les QR codes avec SVG et PNG manquants';

    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        parent::__construct();
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Début de la regénération des QR Codes...');

        // Déterminer quels QR codes traiter
        $query = QrCode::where('is_active', true);

        if ($this->option('code')) {
            $query->where('code', $this->option('code'));
            $this->info("Traitement du QR Code: " . $this->option('code'));
        } elseif ($this->option('missing-png')) {
            $query->where(function($q) {
                $q->whereNull('png_base64')->orWhere('png_base64', '');
            });
            $this->info("Traitement des QR Codes sans PNG...");
        } elseif ($this->option('missing-svg')) {
            $query->where(function($q) {
                $q->whereNull('svg_content')->orWhere('svg_content', '');
            });
            $this->info("Traitement des QR Codes sans SVG...");
        } elseif ($this->option('all')) {
            $this->info("Traitement de TOUS les QR Codes...");
        } else {
            // Par défaut : regénérer ceux sans PNG ou SVG
            $query->where(function($q) {
                $q->whereNull('png_base64')
                  ->orWhere('png_base64', '')
                  ->orWhereNull('svg_content')
                  ->orWhere('svg_content', '');
            });
            $this->info("Traitement des QR Codes avec PNG ou SVG manquants...");
        }

        $qrCodes = $query->get();
        $total = $qrCodes->count();

        if ($total === 0) {
            $this->warn('Aucun QR Code à traiter trouvé.');
            return 0;
        }

        $this->info("QR Codes trouvés: {$total}");
        
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $success = 0;
        $errors = 0;

        foreach ($qrCodes as $qrCode) {
            try {
                $this->processQrCode($qrCode);
                $success++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("\n❌ Erreur QR Code {$qrCode->code}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        
        $this->newLine(2);
        $this->info("✅ Regénération terminée!");
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['Succès', $success],
                ['Erreurs', $errors],
                ['Total', $total]
            ]
        );

        return 0;
    }

    /**
     * Traiter un QR Code individuel
     */
    private function processQrCode(QrCode $qrCode)
    {
        $updated = false;
        
        // Vérifier et regénérer l'URL si manquante
        if (empty($qrCode->verification_url)) {
            $baseUrl = rtrim(config('app.qr_verification_base_url', 'https://www.sglp.ga'), '/');
            if ($qrCode->verifiable_type === 'App\\Models\\Organisation') {
                $qrCode->verification_url = $baseUrl . "/annuaire/verify/{$qrCode->verifiable_id}";
            } else {
                $qrCode->verification_url = $baseUrl . "/annuaire/verify/{$qrCode->code}";
            }
            $updated = true;
        }

        // Regénérer SVG si manquant
        if (empty($qrCode->svg_content)) {
            $svgContent = $this->generateQrCodeSvg($qrCode->verification_url, $qrCode->code);
            if ($svgContent) {
                $qrCode->svg_content = $svgContent;
                $updated = true;
            }
        }

        // Regénérer PNG si manquant
        if (empty($qrCode->png_base64)) {
            $pngBase64 = $this->generateQrCodePng($qrCode->verification_url, $qrCode->code);
            if ($pngBase64) {
                $qrCode->png_base64 = $pngBase64;
                $updated = true;
            }
        }

        // Sauvegarder si des changements ont été faits
        if ($updated) {
            $qrCode->save();
            
            if ($this->option('verbose')) {
                $this->line("✅ QR Code {$qrCode->code} mis à jour");
            }
        }
    }

    /**
     * Générer SVG du QR Code
     */
    private function generateQrCodeSvg($url, $code)
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                throw new \Exception('Bibliothèque QR Code non installée');
            }

            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(150)
                ->margin(2)
                ->color(0, 62, 127)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('M')
                ->generate($url);

            return trim(str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $svg));

        } catch (\Exception $e) {
            $this->error("Erreur génération SVG pour {$code}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Générer PNG base64 du QR Code
     */
    private function generateQrCodePng($url, $code)
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                throw new \Exception('Bibliothèque QR Code non installée');
            }

            $pngData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(100)
                ->margin(1)
                ->color(0, 62, 127)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($url);

            return base64_encode($pngData);

        } catch (\Exception $e) {
            $this->error("Erreur génération PNG pour {$code}: " . $e->getMessage());
            return null;
        }
    }
}