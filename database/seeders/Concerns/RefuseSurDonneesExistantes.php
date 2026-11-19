<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Empêche un seeder de « repartir de zéro » sur une base déjà remplie.
 *
 * Sous MySQL, ces seeders vidaient leurs tables avec FOREIGN_KEY_CHECKS=0 :
 * les dossiers et organisations gardaient des identifiants devenus orphelins.
 * Sous PostgreSQL, c'est pire : truncate() devient TRUNCATE … CASCADE (vide
 * aussi dossiers, validations, etc.) et delete() déclenche les ON DELETE
 * CASCADE / SET NULL. Ces seeders ne s'exécutent donc que sur des tables vides.
 */
trait RefuseSurDonneesExistantes
{
    /**
     * Vrai si toutes les tables sont vides ; sinon affiche un avertissement
     * et renvoie faux (le seeder doit alors s'arrêter).
     */
    protected function tablesVides(array $tables): bool
    {
        $remplies = array_filter($tables, fn ($t) => DB::table($t)->exists());
        if ($remplies === []) {
            return true;
        }

        $this->command->warn(sprintf(
            '%s ignoré : %s contient déjà des données. Il ne s\'exécute que sur une base vide.',
            class_basename(static::class),
            implode(', ', $remplies)
        ));

        return false;
    }
}
