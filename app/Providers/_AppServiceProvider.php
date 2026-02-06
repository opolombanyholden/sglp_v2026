<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Services
use App\Services\OrganisationStepService;
use App\Services\PDFService;
use App\Services\DocumentGenerationService;
use App\Services\QRCodeService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * Service: OrganisationStepService
         */
        $this->app->singleton(OrganisationStepService::class, function () {
            return new OrganisationStepService();
        });

        /**
         * Service: QRCodeService
         * (Ajouté pour éviter l'erreur "Target class [App\Services\QRCodeService] does not exist")
         */
        $this->app->singleton(QRCodeService::class, function () {
            return new QRCodeService();
        });

        /**
         * Service: PDFService
         * Injection de DocumentGenerationService
         */
        $this->app->singleton(PDFService::class, function ($app) {
            return new PDFService(
                $app->make(DocumentGenerationService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
