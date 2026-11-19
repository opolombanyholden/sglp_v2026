<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Sous MySQL (utf8mb4_unicode_ci), `like` ignorait la casse et les accents.
 * Sous PostgreSQL, App\Database\PostgresInsensibleCasseGrammar compile `like`
 * en `unaccent(...) ilike unaccent(...)` pour garder ce comportement.
 *
 * ⚠️ Tourne sur la base configurée dans .env ; DatabaseTransactions annule tout.
 */
class RechercheSansAccentTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Comportement propre à PostgreSQL.');
        }

        // Table temporaire : disparaît avec la transaction du test
        Schema::create('test_recherche', function ($table) {
            $table->id();
            $table->string('nom');
        });
        DB::table('test_recherche')->insert([
            ['nom' => 'Église Évangélique du Gabon'],
            ['nom' => 'ASSOCIATION DES ÉLÈVES'],
            ['nom' => 'Club de karaté'],
        ]);
    }

    private function trouves(string $operateur, string $motif): array
    {
        return DB::table('test_recherche')->where('nom', $operateur, $motif)->orderBy('id')->pluck('nom')->all();
    }

    /** @test */
    public function une_recherche_sans_accent_trouve_le_mot_accentue(): void
    {
        $this->assertSame(['Église Évangélique du Gabon'], $this->trouves('like', '%Eglise%'));
        $this->assertSame(['Club de karaté'], $this->trouves('like', '%karate%'));
    }

    /** @test */
    public function une_recherche_accentuee_trouve_le_mot_sans_accent_et_ignore_la_casse(): void
    {
        $this->assertSame(['ASSOCIATION DES ÉLÈVES'], $this->trouves('like', '%élèves%'));
        $this->assertSame(['ASSOCIATION DES ÉLÈVES'], $this->trouves('LIKE', '%eleves%'));
    }

    /** @test */
    public function not_like_ignore_aussi_les_accents(): void
    {
        $this->assertSame(['ASSOCIATION DES ÉLÈVES', 'Club de karaté'], $this->trouves('not like', '%eglise%'));
    }

    /** @test */
    public function l_egalite_stricte_n_est_pas_modifiee(): void
    {
        $this->assertSame([], $this->trouves('=', 'Eglise Evangelique du Gabon'));
    }
}
