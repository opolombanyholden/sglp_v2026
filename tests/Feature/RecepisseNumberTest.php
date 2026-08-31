<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DossierController;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Régression : unicité du numéro de récépissé.
 *
 * Le numéro était dérivé d'un COUNT d'organisations créées dans l'année, alors
 * que la colonne `numero_recepisse` porte un index UNIQUE. Dès qu'une
 * organisation est supprimée, ou qu'une organisation existe sans récépissé, le
 * compteur repart en arrière et propose un numéro déjà attribué — erreur 1062
 * « Duplicate entry » et échec de la création côté admin.
 *
 * ⚠️ Ces tests tournent sur la base configurée dans .env ;
 * DatabaseTransactions annule chaque test.
 */
class RecepisseNumberTest extends TestCase
{
    use DatabaseTransactions;

    private function genererNumero(string $type): string
    {
        $methode = new ReflectionMethod(DossierController::class, 'generateRecepisseNumberAdmin');
        $methode->setAccessible(true);

        return $methode->invoke(app(DossierController::class), $type);
    }

    /** @test */
    public function le_numero_genere_n_est_jamais_deja_attribue(): void
    {
        $numero = $this->genererNumero('association');

        $this->assertFalse(
            DB::table('organisations')->where('numero_recepisse', $numero)->exists(),
            "Le générateur a proposé « {$numero} », déjà attribué à une organisation"
        );
    }

    /** @test */
    public function le_numero_suit_le_dernier_emis_et_non_le_nombre_d_organisations(): void
    {
        $annee = date('Y');

        // Plus haute séquence émise pour ce type, TOUS FORMATS confondus :
        // la série unifiée doit repartir au-dessus de l'existant.
        $maxEmis = (int) DB::table('organisations')
            ->where('type', 'association')
            ->whereNotNull('numero_recepisse')
            ->where('numero_recepisse', 'like', "%{$annee}%")
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(REPLACE(numero_recepisse, '/', '-'), '-', -1) AS UNSIGNED)) m")
            ->value('m');

        $numero = $this->genererNumero('association');
        $sequence = (int) substr($numero, strrpos($numero, '-') + 1);

        $this->assertGreaterThan(
            $maxEmis,
            $sequence,
            'La séquence doit repartir du dernier numéro émis'
        );
    }

    /** @test */
    public function une_organisation_sans_recepisse_ne_consomme_pas_de_numero(): void
    {
        $premier = $this->genererNumero('association');

        // Une organisation créée sans récépissé (brouillon) ne doit pas décaler
        // la numérotation : elle gonflait le COUNT dans l'ancienne version.
        // On clone une ligne existante pour satisfaire toutes les colonnes
        // obligatoires, puis on la vide de son récépissé.
        $modele = (array) DB::table('organisations')->where('type', 'association')->first();
        unset($modele['id']);
        $modele['nom'] = 'TEST NUMEROTATION ' . uniqid();
        $modele['sigle'] = null;
        $modele['numero_recepisse'] = null;
        $modele['statut'] = 'brouillon';
        $modele['created_at'] = now();
        $modele['updated_at'] = now();
        DB::table('organisations')->insert($modele);

        $this->assertSame(
            $premier,
            $this->genererNumero('association'),
            'Une organisation sans récépissé ne doit pas décaler la numérotation'
        );
    }
    /** @test */
    public function le_generateur_operateur_ne_propose_pas_non_plus_un_numero_pris(): void
    {
        $methode = new ReflectionMethod(
            \App\Http\Controllers\Operator\OrganisationController::class,
            'generateRecepisseNumber'
        );
        $methode->setAccessible(true);

        $numero = $methode->invoke(
            app(\App\Http\Controllers\Operator\OrganisationController::class),
            'association'
        );

        $this->assertFalse(
            DB::table('organisations')->where('numero_recepisse', $numero)->exists(),
            "Le générateur opérateur a proposé « {$numero} », déjà attribué"
        );
    }
    /** @test */
    public function les_deux_flux_produisent_le_format_retenu(): void
    {
        $admin = $this->genererNumero('association');

        $methode = new ReflectionMethod(
            \App\Http\Controllers\Operator\OrganisationController::class,
            'generateRecepisseNumber'
        );
        $methode->setAccessible(true);
        $operateur = $methode->invoke(
            app(\App\Http\Controllers\Operator\OrganisationController::class),
            'association'
        );

        $motif = '/^REC-AS-\d{4}-\d{5}$/';
        $this->assertMatchesRegularExpression($motif, $admin, "Flux admin : {$admin}");
        $this->assertMatchesRegularExpression($motif, $operateur, "Flux opérateur : {$operateur}");
        $this->assertSame($admin, $operateur, 'Les deux flux doivent proposer le même numéro');
    }
}