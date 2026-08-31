<?php

namespace Tests\Feature;

use App\Models\Organisation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Régression : suppression des fondateurs d'une organisation.
 *
 * Plusieurs flux (soumission opérateur, reprise d'un brouillon, mise à jour
 * admin, résolution de conflit de dénomination) suppriment les fondateurs d'une
 * organisation pour les recréer. La contrainte `adherents.fondateur_id` était
 * en ON DELETE RESTRICT : dès qu'un adhérent référençait un fondateur, MySQL
 * rejetait la suppression avec l'erreur 1451 et la soumission échouait.
 *
 * ⚠️ Ces tests tournent sur la base configurée dans .env ;
 * DatabaseTransactions annule chaque test.
 */
class FondateurDeletionTest extends TestCase
{
    use DatabaseTransactions;

    private function creerFondateurAvecAdherent(Organisation $org): array
    {
        $fondateurId = DB::table('fondateurs')->insertGetId([
            'organisation_id' => $org->id,
            'nom' => 'TEST-FK',
            'prenom' => 'Regression',
            'nip' => 'TESTFK-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adherentId = DB::table('adherents')->insertGetId([
            'organisation_id' => $org->id,
            'fondateur_id' => $fondateurId,
            'nip' => 'TESTFK-A-' . uniqid(),
            'nom' => 'TEST-FK',
            'prenom' => 'Adherent',
            'is_fondateur' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$fondateurId, $adherentId];
    }

    /** @test */
    public function supprimer_les_fondateurs_ne_bloque_pas_quand_un_adherent_les_reference(): void
    {
        $org = Organisation::first();
        $this->assertNotNull($org, 'Aucune organisation en base pour le test');

        [$fondateurId, $adherentId] = $this->creerFondateurAvecAdherent($org);

        // C'est exactement l'appel effectué par les flux de soumission/reprise.
        $org->fondateurs()->delete();

        $this->assertDatabaseMissing('fondateurs', ['id' => $fondateurId]);
    }

    /** @test */
    public function l_adherent_survit_a_la_suppression_de_son_fondateur(): void
    {
        $org = Organisation::first();
        $this->assertNotNull($org, 'Aucune organisation en base pour le test');

        [, $adherentId] = $this->creerFondateurAvecAdherent($org);

        $org->fondateurs()->delete();

        // L'adhérent ne doit pas être supprimé en cascade : seule la référence
        // devenue obsolète est effacée.
        $this->assertDatabaseHas('adherents', ['id' => $adherentId]);
        $this->assertNull(
            DB::table('adherents')->where('id', $adherentId)->value('fondateur_id'),
            'La référence vers le fondateur supprimé doit être remise à NULL'
        );
    }
}
