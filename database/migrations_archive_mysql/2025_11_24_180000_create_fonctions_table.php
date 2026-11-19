<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table des fonctions des membres dans une organisation
     */
    public function up(): void
    {
        Schema::create('fonctions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code unique: president, vice_president, etc.');
            $table->string('nom', 100)->comment('Nom de la fonction');
            $table->string('nom_feminin', 100)->nullable()->comment('Forme féminine: Présidente, Secrétaire...');
            $table->text('description')->nullable()->comment('Description des responsabilités');
            $table->string('categorie', 50)->default('bureau')->comment('bureau, commission, membre');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage');
            $table->boolean('is_bureau')->default(false)->comment('Fait partie du bureau exécutif');
            $table->boolean('is_obligatoire')->default(false)->comment('Fonction obligatoire');
            $table->boolean('is_unique')->default(true)->comment('Une seule personne peut occuper cette fonction');
            $table->integer('nb_max')->default(1)->comment('Nombre max de personnes pour cette fonction');
            $table->string('icone', 50)->nullable()->comment('Icône FontAwesome');
            $table->string('couleur', 20)->nullable()->comment('Couleur badge');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['categorie', 'ordre']);
            $table->index('is_active');
        });

        // Insérer les fonctions par défaut
        $fonctions = [
            // Bureau exécutif
            ['code' => 'president', 'nom' => 'Président', 'nom_feminin' => 'Présidente', 'description' => 'Représente l\'organisation et préside les réunions', 'categorie' => 'bureau', 'ordre' => 1, 'is_bureau' => true, 'is_obligatoire' => true, 'is_unique' => true, 'icone' => 'fa-crown', 'couleur' => '#009e3f'],
            ['code' => 'vice_president', 'nom' => 'Vice-Président', 'nom_feminin' => 'Vice-Présidente', 'description' => 'Assiste le président et le remplace en cas d\'absence', 'categorie' => 'bureau', 'ordre' => 2, 'is_bureau' => true, 'is_obligatoire' => false, 'is_unique' => false, 'nb_max' => 3, 'icone' => 'fa-user-tie', 'couleur' => '#00b347'],
            ['code' => 'secretaire_general', 'nom' => 'Secrétaire Général', 'nom_feminin' => 'Secrétaire Générale', 'description' => 'Gère l\'administration et les procès-verbaux', 'categorie' => 'bureau', 'ordre' => 3, 'is_bureau' => true, 'is_obligatoire' => true, 'is_unique' => true, 'icone' => 'fa-file-alt', 'couleur' => '#003f7f'],
            ['code' => 'secretaire_adjoint', 'nom' => 'Secrétaire Adjoint', 'nom_feminin' => 'Secrétaire Adjointe', 'description' => 'Assiste le secrétaire général', 'categorie' => 'bureau', 'ordre' => 4, 'is_bureau' => true, 'is_obligatoire' => false, 'is_unique' => false, 'nb_max' => 2, 'icone' => 'fa-file', 'couleur' => '#0056b3'],
            ['code' => 'tresorier', 'nom' => 'Trésorier', 'nom_feminin' => 'Trésorière', 'description' => 'Gère les finances et la comptabilité', 'categorie' => 'bureau', 'ordre' => 5, 'is_bureau' => true, 'is_obligatoire' => true, 'is_unique' => true, 'icone' => 'fa-coins', 'couleur' => '#ffcd00'],
            ['code' => 'tresorier_adjoint', 'nom' => 'Trésorier Adjoint', 'nom_feminin' => 'Trésorière Adjointe', 'description' => 'Assiste le trésorier', 'categorie' => 'bureau', 'ordre' => 6, 'is_bureau' => true, 'is_obligatoire' => false, 'is_unique' => false, 'nb_max' => 2, 'icone' => 'fa-wallet', 'couleur' => '#e6b800'],
            
            // Commissaires
            ['code' => 'commissaire_comptes', 'nom' => 'Commissaire aux Comptes', 'nom_feminin' => 'Commissaire aux Comptes', 'description' => 'Contrôle la gestion financière', 'categorie' => 'commission', 'ordre' => 7, 'is_bureau' => false, 'is_obligatoire' => true, 'is_unique' => false, 'nb_max' => 3, 'icone' => 'fa-search-dollar', 'couleur' => '#6c757d'],
            ['code' => 'commissaire_conflits', 'nom' => 'Commissaire aux Conflits', 'nom_feminin' => 'Commissaire aux Conflits', 'description' => 'Gère les différends internes', 'categorie' => 'commission', 'ordre' => 8, 'is_bureau' => false, 'is_obligatoire' => false, 'is_unique' => false, 'nb_max' => 3, 'icone' => 'fa-balance-scale', 'couleur' => '#dc3545'],
            
            // Conseillers
            ['code' => 'conseiller', 'nom' => 'Conseiller', 'nom_feminin' => 'Conseillère', 'description' => 'Conseille le bureau sur des sujets spécifiques', 'categorie' => 'commission', 'ordre' => 9, 'is_bureau' => false, 'is_obligatoire' => false, 'is_unique' => false, 'nb_max' => 10, 'icone' => 'fa-lightbulb', 'couleur' => '#17a2b8'],
            
            // Membres
            ['code' => 'membre_fondateur', 'nom' => 'Membre Fondateur', 'nom_feminin' => 'Membre Fondatrice', 'description' => 'Membre ayant participé à la création', 'categorie' => 'membre', 'ordre' => 10, 'is_bureau' => false, 'is_obligatoire' => false, 'is_unique' => false, 'nb_max' => 999, 'icone' => 'fa-star', 'couleur' => '#ffc107'],
            ['code' => 'membre_honneur', 'nom' => 'Membre d\'Honneur', 'nom_feminin' => 'Membre d\'Honneur', 'description' => 'Membre honorifique pour services rendus', 'categorie' => 'membre', 'ordre' => 11, 'is_bureau' => false, 'is_obligatoire' => false, 'is_unique' => false, 'nb_max' => 999, 'icone' => 'fa-medal', 'couleur' => '#fd7e14'],
            ['code' => 'membre_actif', 'nom' => 'Membre Actif', 'nom_feminin' => 'Membre Active', 'description' => 'Membre participant activement', 'categorie' => 'membre', 'ordre' => 12, 'is_bureau' => false, 'is_obligatoire' => false, 'is_unique' => false, 'nb_max' => 999, 'icone' => 'fa-user', 'couleur' => '#28a745'],
            ['code' => 'membre', 'nom' => 'Membre', 'nom_feminin' => 'Membre', 'description' => 'Membre simple de l\'organisation', 'categorie' => 'membre', 'ordre' => 13, 'is_bureau' => false, 'is_obligatoire' => false, 'is_unique' => false, 'nb_max' => 999, 'icone' => 'fa-user', 'couleur' => '#6c757d'],
        ];

        foreach ($fonctions as $fonction) {
            DB::table('fonctions')->insert(array_merge($fonction, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fonctions');
    }
};