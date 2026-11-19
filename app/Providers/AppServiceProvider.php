<?php

namespace App\Providers;

use App\Database\PostgresInsensibleCasseGrammar;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Services\OrganisationStepService;
use App\Services\PDFService;
use App\Services\DocumentGenerationService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrganisationStepService::class, function () {
            return new OrganisationStepService();
        });

        $this->app->singleton(PDFService::class, function ($app) {
            return new PDFService(
                $app->make(DocumentGenerationService::class)
            );
        });
    }

    public function boot(): void
    {
        // PostgreSQL : LIKE insensible à la casse, comme sous MySQL
        foreach (config('database.connections') as $nom => $config) {
            if (($config['driver'] ?? null) === 'pgsql') {
                DB::extend($nom, function ($config, $name) {
                    $config['name'] = $name;
                    $connexion = app('db.factory')->make($config, $name);
                    $connexion->setQueryGrammar(
                        $connexion->withTablePrefix(new PostgresInsensibleCasseGrammar)
                    );
                    return $connexion;
                });
            }
        }

        // V02 — Rapport ANINF : toutes les URLs générées doivent être HTTPS en production
        if (config('app.env') === 'production' || str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}
