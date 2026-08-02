<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Création de comptes administrateurs et présence dans les listes d'assignation.
 *
 * ⚠️ Ces tests tournent sur la base de développement configurée dans .env :
 * DatabaseTransactions annule chaque test, rien n'est persisté.
 */
class AdminUserCreationTest extends TestCase
{
    use DatabaseTransactions;

    private function actingAsSuperAdmin(): User
    {
        $admin = User::where('role', 'admin')->whereNotNull('email_verified_at')->firstOrFail();
        $this->actingAs($admin);

        return $admin;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nom' => 'TESTUSER',
            'prenom' => 'Automatise',
            'email' => 'test.creation.' . uniqid() . '@sglp.test',
            'phone' => '062000000',
            'role' => 'admin',
            'role_id' => Role::where('name', 'super_admin')->value('id'),
            'password' => 'MotDePasse123',
            'password_confirmation' => 'MotDePasse123',
            'is_active' => '1',
        ], $overrides);
    }

    /** @test */
    public function un_administrateur_cree_apparait_dans_les_listes_dassignation(): void
    {
        $this->actingAsSuperAdmin();
        $data = $this->payload();

        $response = $this->post(route('admin.users.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.users.index'));

        $cree = User::where('email', $data['email'])->first();
        $this->assertNotNull($cree, 'Le compte devrait exister en base');
        $this->assertSame('admin', $cree->role);
        $this->assertTrue($cree->is_active, 'Le compte doit être actif');
        $this->assertTrue($cree->isAdministrateur());

        $this->assertTrue(
            User::assignables()->where('id', $cree->id)->exists(),
            'Le nouvel administrateur doit apparaître dans les listes d\'assignation'
        );
    }

    /** @test */
    public function un_admin_metier_role_agent_avec_role_avance_est_assignable(): void
    {
        $this->actingAsSuperAdmin();
        $data = $this->payload([
            'role' => 'agent',
            'role_id' => Role::where('name', 'admin_associations')->value('id'),
        ]);

        $response = $this->post(route('admin.users.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.users.agents'));

        $cree = User::where('email', $data['email'])->firstOrFail();
        $this->assertTrue($cree->isAdministrateur());
        $this->assertTrue(User::assignables()->where('id', $cree->id)->exists());
    }

    /** @test */
    public function un_compte_back_office_sans_role_avance_est_refuse(): void
    {
        $this->actingAsSuperAdmin();
        $data = $this->payload(['role_id' => null]);

        $response = $this->post(route('admin.users.store'), $data);

        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => $data['email']]);
    }

    /** @test */
    public function un_operateur_ne_peut_pas_porter_un_role_avance_dadministration(): void
    {
        $this->actingAsSuperAdmin();
        $data = $this->payload([
            'role' => 'operator',
            'role_id' => Role::where('name', 'admin_associations')->value('id'),
        ]);

        $response = $this->post(route('admin.users.store'), $data);

        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => $data['email']]);
    }

    /** @test */
    public function un_operateur_est_cree_mais_nest_pas_assignable(): void
    {
        $this->actingAsSuperAdmin();
        $data = $this->payload([
            'role' => 'operator',
            'role_id' => Role::where('name', 'operateur')->value('id'),
        ]);

        $response = $this->post(route('admin.users.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.users.operators'));

        $cree = User::where('email', $data['email'])->firstOrFail();
        $this->assertFalse($cree->isAdministrateur());
        $this->assertFalse(
            User::assignables()->where('id', $cree->id)->exists(),
            'Un opérateur ne doit jamais apparaître dans les listes d\'assignation'
        );
    }

    /** @test */
    public function le_prenom_est_facultatif(): void
    {
        $this->actingAsSuperAdmin();
        $data = $this->payload(['prenom' => '']);

        $response = $this->post(route('admin.users.store'), $data);

        $response->assertSessionHasNoErrors();

        $cree = User::where('email', $data['email'])->firstOrFail();
        $this->assertSame('TESTUSER', $cree->name, 'Le nom complet ne doit pas garder d\'espace en trop');
        $this->assertTrue(User::assignables()->where('id', $cree->id)->exists());
    }

    /** @test */
    public function la_case_compte_actif_decochee_cree_un_compte_inactif(): void
    {
        $this->actingAsSuperAdmin();
        // Le formulaire envoie l'input caché "0" seul quand la case est décochée
        $data = $this->payload(['is_active' => '0']);

        $this->post(route('admin.users.store'), $data)->assertSessionHasNoErrors();

        $cree = User::where('email', $data['email'])->firstOrFail();
        $this->assertFalse((bool) $cree->is_active);
        $this->assertFalse(
            User::assignables()->where('id', $cree->id)->exists(),
            'Un compte inactif ne doit pas être assignable'
        );
    }
}
