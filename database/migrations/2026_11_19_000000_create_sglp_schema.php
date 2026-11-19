<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schéma de base SGLP (regroupement des migrations antérieures, archivées dans
 * database/migrations_archive_mysql/). Généré le 2026-11-19 à partir de la base MySQL
 * de production, écrit avec le Schema Builder pour fonctionner sous PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adherent_anomalies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('adherent_id');
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedInteger('ligne_import')->nullable()->comment('Numéro de ligne dans le fichier importé');
            $table->enum('type_anomalie', ['critique', 'majeure', 'mineure']);
            $table->string('champ_concerne', 255)->comment('Champ où l\'anomalie a été détectée');
            $table->text('message_anomalie')->nullable()->comment('Description de l\'anomalie');
            $table->text('description')->nullable();
            $table->timestamp('detectee_le')->nullable();
            $table->longText('valeur_erronee')->nullable()->comment('Valeur incorrecte détectée (format JSON)');
            $table->longText('valeur_incorrecte')->nullable();
            $table->string('impact_metier', 255)->nullable();
            $table->integer('priorite')->nullable()->default(3);
            $table->enum('statut', ['en_attente', 'detectee', 'resolu', 'ignore'])->default('en_attente');
            $table->text('valeur_corrigee')->nullable()->comment('Valeur corrigée');
            $table->text('commentaire_correction')->nullable()->comment('Commentaire de la correction');
            $table->unsignedBigInteger('corrige_par')->nullable();
            $table->timestamp('date_correction')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['adherent_id', 'statut'], 'adherent_anomalies_adherent_id_statut_index');
            $table->index(['organisation_id', 'type_anomalie'], 'adherent_anomalies_organisation_id_type_anomalie_index');
            $table->index(['statut', 'type_anomalie'], 'adherent_anomalies_statut_type_anomalie_index');
            $table->index(['corrige_par'], 'idx_adherent_anomalies_corrige_par');
        });

        Schema::create('adherent_detection_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nip', 255);
            $table->unsignedBigInteger('nouvelle_organisation_id');
            $table->longText('organisations_existantes');
            $table->longText('anomalies_detectees');
            $table->enum('action_prise', ['creation_avec_validation', 'creation_directe', 'rejet']);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['action_prise'], 'adherent_detection_logs_action_prise_index');
            $table->index(['created_at'], 'adherent_detection_logs_created_at_index');
            $table->index(['nip'], 'adherent_detection_logs_nip_index');
            $table->index(['nouvelle_organisation_id'], 'adherent_detection_logs_nouvelle_organisation_id_index');
        });

        Schema::create('adherent_exclusions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('adherent_id');
            $table->unsignedBigInteger('organisation_id');
            $table->enum('type_exclusion', ['demission_volontaire', 'exclusion_disciplinaire', 'non_paiement_cotisation', 'incompatibilite', 'deces', 'autre']);
            $table->text('motif_detaille');
            $table->date('date_decision');
            $table->string('numero_decision', 255)->nullable();
            $table->string('document_decision', 255)->comment('Scan de la décision');
            $table->string('lettre_notification', 255)->nullable();
            $table->boolean('conteste')->default(false);
            $table->text('detail_contestation')->nullable();
            $table->unsignedBigInteger('validated_by');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['adherent_id', 'organisation_id'], 'adherent_exclusions_adherent_id_organisation_id_index');
            $table->index(['date_decision'], 'adherent_exclusions_date_decision_index');
            $table->index(['type_exclusion'], 'adherent_exclusions_type_exclusion_index');
            $table->index(['organisation_id'], 'idx_adherent_exclusions_organisation_id');
            $table->index(['validated_by'], 'idx_adherent_exclusions_validated_by');
        });

        Schema::create('adherent_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('adherent_id');
            $table->unsignedBigInteger('organisation_id');
            $table->enum('type_mouvement', ['adhesion', 'exclusion', 'demission', 'transfert', 'reintegration', 'suspension', 'deces', 'radiation', 'correction', 'validation_anomalie', 'creation_avec_anomalie'])->nullable();
            $table->unsignedBigInteger('ancienne_organisation_id')->nullable();
            $table->unsignedBigInteger('nouvelle_organisation_id')->nullable();
            $table->text('motif');
            $table->string('document_justificatif', 255)->nullable();
            $table->date('date_effet');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->text('commentaire_validation')->nullable();
            $table->text('commentaires')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['adherent_id', 'type_mouvement'], 'adherent_histories_adherent_id_type_mouvement_index');
            $table->index(['organisation_id', 'date_effet'], 'adherent_histories_organisation_id_date_effet_index');
            $table->index(['statut'], 'adherent_histories_statut_index');
            $table->index(['ancienne_organisation_id'], 'idx_adherent_histories_ancienne_organisation_id');
            $table->index(['created_by'], 'idx_adherent_histories_created_by');
            $table->index(['nouvelle_organisation_id'], 'idx_adherent_histories_nouvelle_organisation_id');
            $table->index(['validated_by'], 'idx_adherent_histories_validated_by');
        });

        Schema::create('adherent_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('imported_by');
            $table->string('fichier_original', 255);
            $table->string('fichier_traite', 255)->nullable();
            $table->integer('total_lignes');
            $table->integer('lignes_importees')->default(0);
            $table->integer('lignes_rejetees')->default(0);
            $table->integer('doublons_detectes')->default(0);
            $table->enum('statut', ['en_cours', 'complete', 'echoue', 'partiel'])->default('en_cours');
            $table->longText('erreurs')->nullable()->comment('Détail des erreurs par ligne');
            $table->longText('doublons')->nullable()->comment('Liste des NIP en doublon');
            $table->longText('statistiques')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['imported_by'], 'adherent_imports_imported_by_index');
            $table->index(['organisation_id', 'statut'], 'adherent_imports_organisation_id_statut_index');
        });

        Schema::create('adherents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_id');
            $table->string('nip', 255)->comment('Numéro d\'Identification Personnel');
            $table->string('nom', 255);
            $table->string('prenom', 255)->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance', 255)->nullable();
            $table->string('sexe', 10)->nullable();
            $table->string('nationalite', 255)->nullable()->default('Gabonaise');
            $table->string('telephone', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('profession', 255)->nullable()->comment('Profession de l\'adhérent');
            $table->string('fonction', 255)->nullable()->default('Membre')->comment('Fonction de l\'adhérent dans l\'organisation');
            $table->string('motif_adhesion', 500)->nullable();
            $table->string('civilite', 10)->nullable()->default('M')->comment('Civilité : M, Mme, Mlle');
            $table->string('adresse_complete', 255)->nullable();
            $table->string('province', 255)->nullable();
            $table->string('departement', 255)->nullable();
            $table->string('canton', 255)->nullable();
            $table->string('prefecture', 255)->nullable();
            $table->string('sous_prefecture', 255)->nullable();
            $table->string('regroupement', 255)->nullable();
            $table->enum('zone_type', ['urbaine', 'rurale'])->nullable();
            $table->string('ville_commune', 255)->nullable();
            $table->string('arrondissement', 255)->nullable();
            $table->string('quartier', 255)->nullable();
            $table->string('village', 255)->nullable();
            $table->string('lieu_dit', 255)->nullable();
            $table->unsignedBigInteger('province_ref_id')->nullable();
            $table->unsignedBigInteger('departement_ref_id')->nullable();
            $table->unsignedBigInteger('commune_ville_ref_id')->nullable();
            $table->unsignedBigInteger('arrondissement_ref_id')->nullable();
            $table->unsignedBigInteger('canton_ref_id')->nullable();
            $table->unsignedBigInteger('regroupement_ref_id')->nullable();
            $table->unsignedBigInteger('localite_ref_id')->nullable();
            $table->string('photo', 255)->nullable();
            $table->string('piece_identite', 255)->nullable()->comment('Scan de la pièce');
            $table->date('date_adhesion')->nullable();
            $table->date('date_exclusion')->nullable();
            $table->text('motif_exclusion')->nullable();
            $table->boolean('is_fondateur')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_anomalies')->default(false);
            $table->enum('anomalies_severity', ['critique', 'majeure', 'mineure'])->nullable();
            $table->longText('anomalies_data')->nullable();
            $table->enum('statut_validation', ['en_attente', 'valide', 'rejete'])->default('valide');
            $table->text('justificatif_depart')->nullable();
            $table->string('document_justificatif', 255)->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->unsignedBigInteger('validateur_id')->nullable();
            $table->text('commentaire_validation')->nullable();
            $table->boolean('appartenance_multiple')->default(false);
            $table->longText('organisations_precedentes')->nullable();
            $table->unsignedBigInteger('fondateur_id')->nullable();
            $table->unsignedBigInteger('inscription_link_id')->nullable();
            $table->string('source_inscription', 30)->default('operateur');
            $table->string('statut_inscription', 30)->nullable();
            $table->unsignedBigInteger('validee_par')->nullable();
            $table->timestamp('validee_le')->nullable();
            $table->text('motif_rejet_inscription')->nullable();
            $table->longText('historique')->nullable()->comment('Historique des modifications');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['organisation_id', 'province_ref_id'], 'adh_org_prov_idx');
            $table->index(['province_ref_id', 'departement_ref_id'], 'adh_prov_dept_idx');
            $table->index(['zone_type', 'province_ref_id'], 'adh_zone_prov_idx');
            $table->index(['appartenance_multiple'], 'adherents_appartenance_multiple_index');
            $table->index(['fondateur_id'], 'adherents_fondateur_id_index');
            $table->index(['nip'], 'adherents_nip_index');
            $table->index(['nip', 'statut_validation'], 'adherents_nip_statut_validation_index');
            $table->index(['organisation_id', 'is_active'], 'adherents_organisation_id_is_active_index');
            $table->index(['statut_validation'], 'adherents_statut_validation_index');
            $table->index(['arrondissement_ref_id'], 'idx_adherents_arrondissement_ref_id');
            $table->index(['canton_ref_id'], 'idx_adherents_canton_ref_id');
            $table->index(['commune_ville_ref_id'], 'idx_adherents_commune_ville_ref_id');
            $table->index(['departement_ref_id'], 'idx_adherents_departement_ref_id');
            $table->index(['fonction'], 'idx_adherents_fonction');
            $table->index(['inscription_link_id'], 'idx_adherents_inscription_link');
            $table->index(['source_inscription', 'statut_inscription'], 'idx_adherents_inscription_status');
            $table->index(['localite_ref_id'], 'idx_adherents_localite_ref_id');
            $table->index(['profession'], 'idx_adherents_profession');
            $table->index(['regroupement_ref_id'], 'idx_adherents_regroupement_ref_id');
            $table->index(['validateur_id'], 'idx_adherents_validateur_id');
            $table->index(['validee_par'], 'idx_adherents_validee_par');
        });

        Schema::create('api_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 255);
            $table->string('organisation_cliente', 255)->nullable();
            $table->string('token', 64);
            $table->string('prefix', 8);
            $table->longText('permissions')->nullable();
            $table->integer('rate_limit')->default(60);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->integer('total_requests')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['token'], 'api_tokens_token_unique');
        });

        Schema::create('arrondissements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('commune_ville_id');
            $table->string('nom', 255)->comment('Nom de l\'arrondissement');
            $table->string('code', 25)->comment('Code unique de l\'arrondissement');
            $table->integer('numero_arrondissement')->nullable()->comment('Numéro d\'ordre de l\'arrondissement (1er, 2ème, etc.)');
            $table->text('description')->nullable()->comment('Description de l\'arrondissement');
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            $table->string('delegue', 255)->nullable()->comment('Nom du délégué d\'arrondissement');
            $table->string('telephone', 255)->nullable()->comment('Téléphone de la délégation');
            $table->string('email', 255)->nullable()->comment('Email de la délégation');
            $table->text('limites_geographiques')->nullable()->comment('Description des limites géographiques');
            $table->longText('services_publics')->nullable()->comment('Liste des services publics disponibles');
            $table->longText('equipements')->nullable()->comment('Équipements disponibles (écoles, hôpitaux, etc.)');
            $table->longText('metadata')->nullable()->comment('Données supplémentaires (transport, commerce, etc.)');
            $table->boolean('is_active')->default(true)->comment('Arrondissement actif ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index(['code'], 'arrondissements_code_index');
            $table->index(['commune_ville_id', 'is_active', 'ordre_affichage'], 'arrondissements_arrondissements_commune_ville_id_is_active_ordr');
            $table->index(['nom'], 'arrondissements_nom_index');
            $table->index(['numero_arrondissement', 'is_active'], 'arrondissements_numero_arrondissement_is_active_index');
            $table->unique(['commune_ville_id', 'nom'], 'unique_arrond_par_commune');
            $table->unique(['commune_ville_id', 'code'], 'unique_code_arrond_par_commune');
            $table->unique(['commune_ville_id', 'numero_arrondissement'], 'unique_numero_arrond_par_commune');
            $table->comment('Table des arrondissements urbains du Gabon - Niveau 4 URBAIN, rattachés aux communes/villes');
        });

        Schema::create('cantons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('departement_id');
            $table->string('nom', 255)->comment('Nom du canton');
            $table->string('code', 25)->comment('Code unique du canton');
            $table->string('chef_lieu', 255)->nullable()->comment('Chef-lieu du canton');
            $table->text('description')->nullable()->comment('Description du canton');
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            $table->string('chef_canton', 255)->nullable()->comment('Nom du chef de canton');
            $table->string('telephone_chef', 255)->nullable()->comment('Téléphone du chef de canton');
            $table->string('telephone_administration', 255)->nullable()->comment('Téléphone de l\'administration cantonale');
            $table->text('limites_geographiques')->nullable()->comment('Description des limites géographiques');
            $table->longText('ethnies_principales')->nullable()->comment('Ethnies principales présentes');
            $table->longText('langues_parlees')->nullable()->comment('Langues parlées dans le canton');
            $table->longText('activites_economiques')->nullable()->comment('Principales activités économiques');
            $table->longText('ressources_naturelles')->nullable()->comment('Ressources naturelles disponibles');
            $table->longText('infrastructures')->nullable()->comment('Infrastructures disponibles (routes, ponts, etc.)');
            $table->longText('services_publics')->nullable()->comment('Services publics disponibles');
            $table->boolean('acces_electricite')->default(false)->comment('Accès à l\'électricité');
            $table->boolean('acces_eau_potable')->default(false)->comment('Accès à l\'eau potable');
            $table->boolean('reseau_telephonique')->default(false)->comment('Couverture réseau téléphonique');
            $table->longText('metadata')->nullable()->comment('Données supplémentaires (climat, géologie, etc.)');
            $table->boolean('is_active')->default(true)->comment('Canton actif ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index(['acces_electricite', 'acces_eau_potable'], 'cantons_acces_electricite_acces_eau_potable_index');
            $table->index(['code'], 'cantons_code_index');
            $table->index(['departement_id', 'is_active', 'ordre_affichage'], 'cantons_departement_id_is_active_ordre_affichage_index');
            $table->index(['nom'], 'cantons_nom_index');
            $table->unique(['departement_id', 'nom'], 'unique_canton_par_dept');
            $table->unique(['departement_id', 'code'], 'unique_code_canton_par_dept');
            $table->comment('Table des cantons du Gabon - Niveau 3 RURAL, rattachés aux départements');
        });

        Schema::create('communes_villes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('departement_id');
            $table->string('nom', 255)->comment('Nom de la commune ou ville');
            $table->string('code', 20)->comment('Code unique de la commune/ville');
            $table->enum('type', ['commune', 'ville'])->default('commune')->comment('Type: commune ou ville');
            $table->string('statut', 255)->nullable()->comment('Statut administratif (commune urbaine, ville, etc.)');
            $table->text('description')->nullable()->comment('Description de la commune/ville');
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            $table->string('maire', 255)->nullable()->comment('Nom du maire en exercice');
            $table->date('date_creation')->nullable()->comment('Date de création de la commune/ville');
            $table->string('telephone', 255)->nullable()->comment('Téléphone de la mairie');
            $table->string('email', 255)->nullable()->comment('Email de la mairie');
            $table->string('site_web', 255)->nullable()->comment('Site web officiel');
            $table->longText('metadata')->nullable()->comment('Données supplémentaires (services, équipements, etc.)');
            $table->boolean('is_active')->default(true)->comment('Commune/ville active ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index(['code'], 'communes_villes_code_index');
            $table->index(['departement_id', 'is_active', 'ordre_affichage'], 'communes_villes_departement_id_is_active_ordre_affichage_index');
            $table->index(['nom'], 'communes_villes_nom_index');
            $table->index(['type', 'is_active'], 'communes_villes_type_is_active_index');
            $table->unique(['departement_id', 'code'], 'unique_code_commune_par_dept');
            $table->unique(['departement_id', 'nom'], 'unique_commune_par_dept');
            $table->comment('Table des communes et villes du Gabon - Niveau 3 URBAIN, rattachées aux départements');
        });

        Schema::create('declaration_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('declaration_id');
            $table->string('type_document', 255);
            $table->string('nom_fichier', 255);
            $table->string('chemin_fichier', 255);
            $table->string('type_mime', 255);
            $table->integer('taille');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['declaration_id'], 'declaration_documents_declaration_id_index');
        });

        Schema::create('declaration_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->enum('categorie', ['activite', 'evenement', 'publication', 'changement_statutaire', 'changement_bureau', 'rapport_annuel', 'autre']);
            $table->longText('types_organisation')->comment('Types d\'organisation concernés');
            $table->boolean('is_periodique')->default(false);
            $table->enum('periodicite', ['mensuelle', 'trimestrielle', 'semestrielle', 'annuelle'])->nullable();
            $table->integer('delai_declaration')->nullable()->comment('Délai en jours après l\'événement');
            $table->longText('documents_requis')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['categorie', 'is_active'], 'declaration_types_categorie_is_active_index');
            $table->unique(['code'], 'declaration_types_code_unique');
            $table->index(['is_periodique'], 'declaration_types_is_periodique_index');
        });

        Schema::create('declarations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('declaration_type_id');
            $table->string('numero_declaration', 255);
            $table->string('titre', 255);
            $table->text('description');
            $table->date('date_evenement')->nullable();
            $table->date('date_fin_evenement')->nullable();
            $table->string('lieu', 255)->nullable();
            $table->integer('nombre_participants')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->enum('statut', ['brouillon', 'soumise', 'validee', 'rejetee', 'archivee'])->default('brouillon');
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->longText('donnees_specifiques')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['date_evenement'], 'declarations_date_evenement_index');
            $table->index(['numero_declaration'], 'declarations_numero_declaration_index');
            $table->unique(['numero_declaration'], 'declarations_numero_declaration_unique');
            $table->index(['organisation_id', 'declaration_type_id'], 'declarations_organisation_id_declaration_type_id_index');
            $table->index(['statut', 'created_at'], 'declarations_statut_created_at_index');
            $table->index(['declaration_type_id'], 'idx_declarations_declaration_type_id');
            $table->index(['submitted_by'], 'idx_declarations_submitted_by');
            $table->index(['validated_by'], 'idx_declarations_validated_by');
        });

        Schema::create('departements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('province_id');
            $table->string('nom', 255)->comment('Nom du département');
            $table->string('code', 15)->comment('Code unique du département');
            $table->string('chef_lieu', 255)->nullable()->comment('Chef-lieu du département');
            $table->text('description')->nullable()->comment('Description du département');
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            $table->longText('metadata')->nullable()->comment('Données supplémentaires (frontières, limites, etc.)');
            $table->boolean('is_active')->default(true)->comment('Département actif ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['code'], 'departements_code_index');
            $table->index(['nom'], 'departements_nom_index');
            $table->index(['province_id', 'is_active', 'ordre_affichage'], 'departements_province_id_is_active_ordre_affichage_index');
            $table->unique(['province_id', 'code'], 'unique_code_dept_par_province');
            $table->unique(['province_id', 'nom'], 'unique_dept_par_province');
            $table->comment('Table des départements du Gabon - Niveau 2 de la hiérarchie géographique, rattachés aux provinces');
        });

        Schema::create('document_generation_customizations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dossier_id');
            $table->unsignedBigInteger('document_template_id');
            $table->longText('header_text')->nullable()->comment('Texte d\'en-tête personnalisé (HTML)');
            $table->longText('signature_text')->nullable()->comment('Texte de signature personnalisé (HTML)');
            $table->unsignedBigInteger('customized_by')->nullable();
            $table->timestamp('customized_at')->nullable()->comment('Date de personnalisation');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['customized_by'], 'idx_document_generation_customizations_customized_by');
            $table->index(['document_template_id'], 'idx_document_generation_customizations_document_template_id');
            $table->unique(['dossier_id', 'document_template_id'], 'unique_dossier_template');
        });

        Schema::create('document_generations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('document_template_id');
            $table->unsignedBigInteger('dossier_id')->nullable();
            $table->unsignedBigInteger('dossier_validation_id')->nullable();
            $table->unsignedBigInteger('organisation_id');
            $table->string('numero_document', 100)->comment('Numéro unique du document généré');
            $table->string('type_document', 100)->comment('Type de document (recepisse_provisoire, etc.)');
            $table->string('qr_code_token', 255)->comment('Token pour vérification publique');
            $table->string('qr_code_url', 255)->comment('URL de vérification complète');
            $table->string('hash_verification', 255)->comment('Hash SHA-256 des données du document');
            $table->longText('variables_data')->comment('Snapshot des données utilisées pour la génération');
            $table->unsignedBigInteger('generated_by');
            $table->timestamp('generated_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('download_count')->default(0)->comment('Nombre de téléchargements');
            $table->timestamp('last_downloaded_at')->nullable();
            $table->unsignedBigInteger('last_downloaded_by')->nullable();
            $table->boolean('is_valid')->default(true)->comment('Document valide ou invalidé');
            $table->timestamp('invalidated_at')->nullable();
            $table->unsignedBigInteger('invalidated_by')->nullable();
            $table->text('invalidation_reason')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['numero_document'], 'document_generations_numero_document_unique');
            $table->unique(['qr_code_token'], 'document_generations_qr_code_token_unique');
            $table->index(['document_template_id'], 'idx_document_generations_document_template_id');
            $table->index(['dossier_validation_id'], 'idx_document_generations_dossier_validation_id');
            $table->index(['generated_by'], 'idx_document_generations_generated_by');
            $table->index(['invalidated_by'], 'idx_document_generations_invalidated_by');
            $table->index(['last_downloaded_by'], 'idx_document_generations_last_downloaded_by');
            $table->index(['dossier_id'], 'idx_dossier');
            $table->index(['generated_at'], 'idx_generated_date');
            $table->index(['numero_document'], 'idx_numero');
            $table->index(['organisation_id', 'type_document'], 'idx_org_type');
            $table->index(['qr_code_token'], 'idx_qr_token');
            $table->index(['is_valid', 'type_document'], 'idx_valid_type');
        });

        Schema::create('document_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_type_id')->nullable();
            $table->unsignedBigInteger('operation_type_id')->nullable();
            $table->unsignedBigInteger('workflow_step_id')->nullable();
            $table->string('code', 255);
            $table->string('nom', 255);
            $table->text('description')->nullable();
            $table->string('template_path', 255)->comment('Chemin Blade (ex: documents.templates.association.recepisse)');
            $table->string('layout_path', 255)->nullable()->default('documents.layouts.official')->comment('Layout parent Blade');
            $table->longText('body_content')->nullable()->comment('Contenu HTML configuré via le designer (publipostage)');
            $table->boolean('use_designer')->default(false)->comment('Si true, utilise body_content au lieu du fichier Blade');
            $table->enum('type_document', ['accuse_reception', 'recepisse_provisoire', 'recepisse_definitif', 'recepisse_enregistrement', 'certificat_enregistrement', 'certificat_conformite', 'attestation', 'autorisation', 'agrement', 'notification_rejet', 'demande_complement', 'convocation', 'pv_commission', 'decision', 'courrier_officiel', 'autre'])->comment('Type de document généré');
            $table->longText('variables')->nullable();
            $table->longText('required_variables')->nullable()->comment('Variables obligatoires pour générer le document');
            $table->longText('pdf_config')->nullable()->comment('Config PDF : format, orientation, marges');
            $table->json('page_config')->nullable()->comment('Format page, orientation, marges');
            $table->boolean('has_qr_code')->default(true);
            $table->boolean('has_watermark')->default(true);
            $table->boolean('has_signature')->default(true);
            $table->string('signature_image', 255)->nullable();
            $table->longText('header_text')->nullable()->comment('Texte d\'en-tête par défaut (HTML WYSIWYG)');
            $table->longText('signature_text')->nullable()->comment('Texte de signature par défaut (HTML WYSIWYG)');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_generate')->default(false)->comment('Générer automatiquement à l\'étape du workflow');
            $table->integer('generation_delay_hours')->nullable()->default(0)->comment('Délai en heures après l\'étape avant génération auto');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['code'], 'document_templates_code_unique');
            $table->index(['is_active'], 'document_templates_is_active_index');
            $table->index(['type_document'], 'document_templates_type_document_type_organisation_index');
            $table->index(['auto_generate', 'workflow_step_id'], 'idx_auto_workflow');
            $table->index(['operation_type_id'], 'idx_document_templates_operation_type_id');
            $table->index(['workflow_step_id'], 'idx_document_templates_workflow_step_id');
            $table->index(['organisation_type_id', 'is_active'], 'idx_org_type_active');
            $table->index(['organisation_type_id', 'type_document'], 'idx_org_type_doc');
            $table->index(['organisation_type_id', 'operation_type_id', 'workflow_step_id'], 'idx_template_triplet');
            $table->index(['type_document', 'is_active'], 'idx_type_active');
        });

        Schema::create('document_type_operation_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('operation_type_id');
            $table->boolean('is_obligatoire')->default(false)->comment('Document obligatoire pour ce type d\'opération');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage pour ce type d\'opération');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['document_type_id', 'operation_type_id'], 'doc_op_unique');
            $table->index(['is_obligatoire'], 'document_type_operation_type_is_obligatoire_index');
            $table->index(['ordre'], 'document_type_operation_type_ordre_index');
            $table->index(['operation_type_id'], 'idx_document_type_operation_type_operation_type_id');
        });

        Schema::create('document_type_organisation_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('organisation_type_id');
            $table->boolean('is_obligatoire')->default(false)->comment('Document obligatoire pour ce type d\'organisation');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage pour ce type d\'organisation');
            $table->text('aide_texte')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['document_type_id', 'organisation_type_id'], 'doc_org_unique');
            $table->index(['is_obligatoire'], 'document_type_organisation_type_is_obligatoire_index');
            $table->index(['ordre'], 'document_type_organisation_type_ordre_index');
            $table->index(['organisation_type_id'], 'idx_document_type_organisation_type_organisation_type_id');
        });

        Schema::create('document_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('ordre')->default(0);
            $table->string('format_accepte', 255)->default('pdf,jpg,png')->comment('Extensions acceptées');
            $table->integer('taille_max')->default(5)->comment('Taille max en MB');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['code'], 'document_types_code_unique');
            $table->index(['ordre'], 'document_types_ordre_index');
            $table->index(['is_active'], 'document_types_type_organisation_type_operation_is_active_index');
        });

        Schema::create('document_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('document_generation_id');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->longText('geolocation')->nullable()->comment('Données de géolocalisation si disponibles');
            $table->boolean('verification_reussie')->default(true);
            $table->text('motif_echec')->nullable();
            $table->timestamp('verified_at')->useCurrent();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['document_generation_id', 'verified_at'], 'idx_doc_date');
            $table->index(['ip_address'], 'idx_ip');
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dossier_id');
            $table->unsignedBigInteger('document_type_id');
            $table->string('nom_fichier', 255);
            $table->string('nom_original', 255)->nullable()->comment('Nom original du fichier uploadé par l\'utilisateur');
            $table->string('chemin_fichier', 255);
            $table->string('type_mime', 255);
            $table->integer('taille')->comment('Taille en octets');
            $table->string('hash_fichier', 255)->comment('Pour vérifier l\'intégrité');
            $table->unsignedBigInteger('uploaded_by')->nullable()->comment('ID de l\'utilisateur qui a uploadé le document');
            $table->boolean('is_validated')->default(false);
            $table->boolean('is_system_generated')->default(false)->comment('Document généré automatiquement par le système (accusés, certificats, etc.)');
            $table->boolean('has_anomalies_info')->default(false);
            $table->text('commentaire')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['dossier_id', 'document_type_id'], 'documents_dossier_id_document_type_id_index');
            $table->index(['is_validated'], 'documents_is_validated_index');
            $table->index(['document_type_id'], 'idx_documents_document_type_id');
            $table->index(['is_system_generated'], 'idx_documents_system_generated');
            $table->index(['uploaded_by'], 'idx_documents_uploaded_by');
        });

        Schema::create('domaines_activite', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 100);
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('suggested_by_user_id')->nullable();
            $table->enum('suggestion_status', ['approved', 'pending', 'rejected'])->default('approved');
            $table->integer('ordre')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['code'], 'domaines_activite_code_unique');
        });

        Schema::create('dossier_archives', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dossier_id');
            $table->unsignedBigInteger('archived_by');
            $table->text('motif_archivage');
            $table->longText('snapshot_data');
            $table->timestamp('archived_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['archived_at'], 'dossier_archives_archived_at_index');
            $table->index(['dossier_id'], 'dossier_archives_dossier_id_index');
            $table->index(['archived_by'], 'idx_dossier_archives_archived_by');
        });

        Schema::create('dossier_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dossier_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('workflow_step_id')->nullable();
            $table->enum('type', ['interne', 'operateur', 'systeme'])->default('interne');
            $table->text('commentaire');
            $table->boolean('is_visible_operateur')->default(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->longText('fichiers_joints')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['dossier_id', 'created_at'], 'dossier_comments_dossier_id_created_at_index');
            $table->index(['type', 'is_visible_operateur'], 'dossier_comments_type_is_visible_operateur_index');
            $table->index(['parent_id'], 'idx_dossier_comments_parent_id');
            $table->index(['user_id'], 'idx_dossier_comments_user_id');
            $table->index(['workflow_step_id'], 'idx_dossier_comments_workflow_step_id');
        });

        Schema::create('dossier_corrections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dossier_id')->comment('Dossier de correction (nouvelle version)');
            $table->unsignedBigInteger('original_dossier_id')->comment('Dossier approuvé corrigé');
            $table->string('champ_corrige', 255)->comment('Nom du champ corrigé');
            $table->enum('categorie', ['organisation', 'adherent', 'fondateur', 'membre_bureau', 'document', 'autre'])->default('organisation');
            $table->text('ancienne_valeur')->nullable();
            $table->text('nouvelle_valeur')->nullable();
            $table->text('motif_correction')->comment('Justification obligatoire');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('ID adhérent/fondateur/membre si applicable');
            $table->unsignedBigInteger('corrected_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['approved_by'], 'dossier_corrections_approved_by_foreign');
            $table->index(['corrected_by'], 'dossier_corrections_corrected_by_foreign');
            $table->index(['dossier_id', 'categorie'], 'dossier_corrections_dossier_id_categorie_index');
            $table->index(['original_dossier_id'], 'dossier_corrections_original_dossier_id_index');
        });

        Schema::create('dossier_locks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dossier_id');
            $table->unsignedBigInteger('locked_by');
            $table->unsignedBigInteger('workflow_step_id');
            $table->string('session_id', 255)->comment('ID de session pour éviter les conflits');
            $table->timestamp('locked_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('ip_address', 255)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['dossier_id', 'is_active'], 'dossier_locks_dossier_id_is_active_unique');
            $table->index(['expires_at'], 'dossier_locks_expires_at_index');
            $table->index(['locked_by', 'is_active'], 'dossier_locks_locked_by_is_active_index');
            $table->index(['workflow_step_id'], 'idx_dossier_locks_workflow_step_id');
        });

        Schema::create('dossier_operations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dossier_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type_operation', 50);
            $table->string('ancien_statut', 255)->nullable();
            $table->string('nouveau_statut', 255)->nullable();
            $table->unsignedBigInteger('workflow_step_id')->nullable();
            $table->text('description');
            $table->longText('donnees_avant')->nullable()->comment('Snapshot des données avant l\'opération');
            $table->longText('donnees_apres')->nullable()->comment('Snapshot des données après l\'opération');
            $table->string('ip_address', 255);
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['dossier_id', 'created_at'], 'dossier_operations_dossier_id_created_at_index');
            $table->index(['type_operation'], 'dossier_operations_type_operation_index');
            $table->index(['user_id', 'type_operation'], 'dossier_operations_user_id_type_operation_index');
            $table->index(['workflow_step_id'], 'idx_dossier_operations_workflow_step_id');
        });

        Schema::create('dossier_priority_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dossier_id');
            $table->enum('ancien_niveau', ['normale', 'moyenne', 'haute', 'urgente']);
            $table->enum('nouveau_niveau', ['normale', 'moyenne', 'haute', 'urgente']);
            $table->text('justification')->nullable();
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at')->useCurrent();
            $table->integer('ordre_avant')->nullable();
            $table->integer('ordre_apres')->nullable();
            $table->index(['changed_by'], 'dossier_priority_history_changed_by_index');
            $table->index(['dossier_id', 'changed_at'], 'dossier_priority_history_dossier_id_changed_at_index');
        });

        Schema::create('dossier_validations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dossier_id');
            $table->unsignedBigInteger('workflow_step_id');
            $table->unsignedBigInteger('validation_entity_id');
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->enum('decision', ['approuve', 'rejete', 'en_attente'])->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->string('visa', 255)->nullable();
            $table->string('reference', 255)->nullable();
            $table->string('numero_enregistrement', 255)->nullable();
            $table->string('document_genere', 255)->nullable()->comment('Chemin du document généré');
            $table->unsignedBigInteger('document_generation_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->integer('duree_traitement')->nullable()->comment('Durée en minutes');
            $table->longText('donnees_supplementaires')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['decision'], 'dossier_validations_decision_index');
            $table->index(['dossier_id', 'workflow_step_id'], 'dossier_validations_dossier_id_workflow_step_id_index');
            $table->index(['validated_by'], 'dossier_validations_validated_by_index');
            $table->index(['validation_entity_id', 'decision'], 'dossier_validations_validation_entity_id_decision_index');
            $table->index(['document_generation_id'], 'idx_dossier_validations_document_generation_id');
            $table->index(['workflow_step_id'], 'idx_dossier_validations_workflow_step_id');
        });

        Schema::create('dossiers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('parent_dossier_id')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_current_version')->default(true);
            $table->enum('type_operation', ['creation', 'modification', 'cessation', 'ajout_adherent', 'retrait_adherent', 'declaration_activite', 'changement_statutaire', 'correction']);
            $table->string('numero_dossier', 255);
            $table->enum('statut', ['brouillon', 'soumis', 'en_cours', 'approuve', 'rejete', 'accepte', 'archive', 'annule'])->default('brouillon');
            $table->timestamp('date_soumission')->nullable();
            $table->enum('priorite_niveau', ['normale', 'moyenne', 'haute', 'urgente'])->default('normale');
            $table->boolean('priorite_urgente')->default(false);
            $table->integer('ordre_traitement')->nullable();
            $table->text('priorite_justification')->nullable();
            $table->unsignedBigInteger('priorite_assignee_par')->nullable();
            $table->timestamp('priorite_assignee_at')->nullable();
            $table->text('instructions_agent')->nullable();
            $table->boolean('has_anomalies_majeures')->default(false);
            $table->unsignedBigInteger('current_step_id')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->longText('donnees_supplementaires')->nullable();
            $table->longText('champs_modifies')->nullable()->comment('Liste des champs sélectionnés pour modification');
            $table->longText('donnees_avant_modification')->nullable()->comment('Snapshot des données avant opération pour historique');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('deleted_at')->nullable();
            $table->index(['assigned_to'], 'dossiers_assigned_to_index');
            $table->index(['numero_dossier'], 'dossiers_numero_dossier_index');
            $table->unique(['numero_dossier'], 'dossiers_numero_dossier_unique');
            $table->index(['organisation_id', 'statut'], 'dossiers_organisation_id_statut_index');
            $table->index(['statut', 'created_at'], 'dossiers_statut_created_at_index');
            $table->index(['current_step_id'], 'idx_dossiers_current_step_id');
            $table->index(['locked_by'], 'idx_dossiers_locked_by');
            $table->index(['priorite_assignee_par'], 'idx_dossiers_priorite_assignee_par');
            $table->index(['statut'], 'idx_fifo_queue');
            $table->index(['organisation_id', 'is_current_version'], 'idx_org_current_version');
            $table->index(['parent_dossier_id'], 'idx_parent_dossier');
            $table->index(['created_at'], 'idx_priorite_niveau');
        });

        Schema::create('draft_accuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('draft_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('step_number');
            $table->string('step_name', 255);
            $table->string('accuse_type', 255)->default('step_completion');
            $table->string('numero_accuse', 255);
            $table->text('contenu_html')->nullable();
            $table->string('fichier_pdf', 255)->nullable();
            $table->longText('donnees_etape')->nullable();
            $table->string('hash_verification', 255);
            $table->string('qr_code', 255)->nullable();
            $table->boolean('is_valide')->default(true);
            $table->timestamp('generated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('expires_at')->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['draft_id', 'step_number'], 'draft_accuses_draft_id_step_number_index');
            $table->index(['numero_accuse'], 'draft_accuses_numero_accuse_index');
            $table->unique(['numero_accuse'], 'draft_accuses_numero_accuse_unique');
            $table->index(['user_id', 'generated_at'], 'draft_accuses_user_id_generated_at_index');
        });

        Schema::create('entity_agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('validation_entity_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['responsable', 'agent', 'superviseur'])->default('agent');
            $table->boolean('peut_valider')->default(true);
            $table->boolean('peut_rejeter')->default(true);
            $table->boolean('peut_assigner')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('charge_actuelle')->default(0)->comment('Nombre de dossiers en cours');
            $table->integer('capacite_max')->default(5)->comment('Nombre max de dossiers simultanés');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['is_active', 'charge_actuelle'], 'entity_agents_is_active_charge_actuelle_index');
            $table->unique(['validation_entity_id', 'user_id'], 'entity_agents_validation_entity_id_user_id_unique');
            $table->index(['user_id'], 'idx_entity_agents_user_id');
        });

        Schema::create('etablissements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_id');
            $table->string('nom', 255);
            $table->string('type', 255)->comment('Siège, antenne, bureau, etc.');
            $table->string('adresse', 255);
            $table->string('province', 255);
            $table->string('departement', 255)->nullable();
            $table->string('canton', 255)->nullable();
            $table->string('prefecture', 255);
            $table->string('sous_prefecture', 255)->nullable();
            $table->string('regroupement', 255)->nullable();
            $table->enum('zone_type', ['urbaine', 'rurale'])->default('urbaine');
            $table->string('ville_commune', 255)->nullable();
            $table->string('arrondissement', 255)->nullable();
            $table->string('quartier', 255)->nullable();
            $table->string('village', 255)->nullable();
            $table->string('lieu_dit', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedBigInteger('province_ref_id')->nullable();
            $table->unsignedBigInteger('departement_ref_id')->nullable();
            $table->unsignedBigInteger('commune_ville_ref_id')->nullable();
            $table->unsignedBigInteger('arrondissement_ref_id')->nullable();
            $table->unsignedBigInteger('canton_ref_id')->nullable();
            $table->unsignedBigInteger('regroupement_ref_id')->nullable();
            $table->unsignedBigInteger('localite_ref_id')->nullable();
            $table->string('telephone', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('responsable_nom', 255)->nullable();
            $table->string('responsable_telephone', 255)->nullable();
            $table->string('responsable_email', 255)->nullable();
            $table->boolean('is_siege_principal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['organisation_id', 'province_ref_id'], 'etab_org_prov_idx');
            $table->index(['zone_type', 'province_ref_id'], 'etab_zone_prov_idx');
            $table->index(['latitude', 'longitude'], 'etablissements_latitude_longitude_index');
            $table->index(['organisation_id'], 'etablissements_organisation_id_index');
            $table->index(['province', 'prefecture'], 'etablissements_province_prefecture_index');
            $table->index(['arrondissement_ref_id'], 'idx_etablissements_arrondissement_ref_id');
            $table->index(['canton_ref_id'], 'idx_etablissements_canton_ref_id');
            $table->index(['commune_ville_ref_id'], 'idx_etablissements_commune_ville_ref_id');
            $table->index(['departement_ref_id'], 'idx_etablissements_departement_ref_id');
            $table->index(['localite_ref_id'], 'idx_etablissements_localite_ref_id');
            $table->index(['province_ref_id'], 'idx_etablissements_province_ref_id');
            $table->index(['regroupement_ref_id'], 'idx_etablissements_regroupement_ref_id');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 255);
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
            $table->unique(['uuid'], 'failed_jobs_uuid_unique');
        });

        Schema::create('fonctions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 50)->comment('Code unique: president, vice_president, etc.');
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
            $table->unsignedBigInteger('suggested_by_user_id')->nullable();
            $table->enum('suggestion_status', ['approved', 'pending', 'rejected'])->default('approved');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['categorie', 'ordre'], 'fonctions_categorie_ordre_index');
            $table->unique(['code'], 'fonctions_code_unique');
            $table->index(['is_active'], 'fonctions_is_active_index');
        });

        Schema::create('fondateurs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_id');
            $table->string('nip', 255)->comment('Numéro d\'Identification Personnel');
            $table->string('nom', 255);
            $table->string('prenom', 255)->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance', 255)->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
            $table->string('nationalite', 255)->nullable()->default('Gabonaise');
            $table->string('telephone', 255)->nullable();
            $table->string('telephone_secondaire', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('adresse_complete', 255)->nullable();
            $table->string('province', 255)->nullable();
            $table->string('departement', 255)->nullable();
            $table->string('canton', 255)->nullable();
            $table->string('prefecture', 255)->nullable();
            $table->string('sous_prefecture', 255)->nullable();
            $table->string('regroupement', 255)->nullable();
            $table->enum('zone_type', ['urbaine', 'rurale'])->nullable()->default('urbaine');
            $table->string('ville_commune', 255)->nullable();
            $table->string('arrondissement', 255)->nullable();
            $table->string('quartier', 255)->nullable();
            $table->string('village', 255)->nullable();
            $table->string('lieu_dit', 255)->nullable();
            $table->unsignedBigInteger('province_ref_id')->nullable();
            $table->unsignedBigInteger('departement_ref_id')->nullable();
            $table->unsignedBigInteger('commune_ville_ref_id')->nullable();
            $table->unsignedBigInteger('arrondissement_ref_id')->nullable();
            $table->unsignedBigInteger('canton_ref_id')->nullable();
            $table->unsignedBigInteger('regroupement_ref_id')->nullable();
            $table->unsignedBigInteger('localite_ref_id')->nullable();
            $table->string('photo', 255)->nullable();
            $table->string('piece_identite', 255)->nullable()->comment('Scan de la pièce d\'identité');
            $table->string('type_piece', 255)->nullable()->comment('CNI, Passeport, etc.');
            $table->string('numero_piece', 255)->nullable();
            $table->string('fonction', 255)->nullable()->comment('Président fondateur, Secrétaire général, etc.');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['organisation_id', 'province_ref_id'], 'fond_org_prov_idx');
            $table->index(['zone_type', 'province_ref_id'], 'fond_zone_prov_idx');
            $table->index(['nip'], 'fondateurs_nip_index');
            $table->index(['organisation_id'], 'fondateurs_organisation_id_index');
            $table->unique(['organisation_id', 'nip'], 'fondateurs_organisation_id_nip_unique');
            $table->index(['arrondissement_ref_id'], 'idx_fondateurs_arrondissement_ref_id');
            $table->index(['canton_ref_id'], 'idx_fondateurs_canton_ref_id');
            $table->index(['commune_ville_ref_id'], 'idx_fondateurs_commune_ville_ref_id');
            $table->index(['departement_ref_id'], 'idx_fondateurs_departement_ref_id');
            $table->index(['localite_ref_id'], 'idx_fondateurs_localite_ref_id');
            $table->index(['province_ref_id'], 'idx_fondateurs_province_ref_id');
            $table->index(['regroupement_ref_id'], 'idx_fondateurs_regroupement_ref_id');
        });

        Schema::create('guide_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->enum('type_operation', ['creation', 'modification', 'cessation', 'ajout_adherent', 'retrait_adherent', 'declaration_activite', 'changement_statutaire']);
            $table->enum('type_organisation', ['association', 'ong', 'parti_politique', 'confession_religieuse'])->nullable();
            $table->string('titre', 255);
            $table->text('contenu_intro');
            $table->longText('etapes');
            $table->longText('documents_requis');
            $table->longText('liens_utiles')->nullable();
            $table->string('video_tutoriel', 255)->nullable();
            $table->integer('temps_estime')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['code'], 'guide_contents_code_unique');
            $table->index(['type_operation', 'type_organisation'], 'guide_contents_type_operation_type_organisation_index');
        });

        Schema::create('inscription_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_id');
            $table->string('token', 255);
            $table->string('url_courte', 255)->nullable();
            $table->string('nom_campagne', 255);
            $table->text('description')->nullable();
            $table->integer('limite_inscriptions')->nullable();
            $table->integer('inscriptions_actuelles')->default(0);
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->boolean('requiert_validation')->default(true);
            $table->longText('champs_supplementaires')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['created_by'], 'idx_inscription_links_created_by');
            $table->index(['date_debut', 'date_fin'], 'inscription_links_date_debut_date_fin_index');
            $table->index(['is_active'], 'inscription_links_is_active_index');
            $table->index(['organisation_id', 'is_active'], 'inscription_links_organisation_id_is_active_index');
            $table->index(['token'], 'inscription_links_token_index');
            $table->unique(['token'], 'inscription_links_token_unique');
            $table->unique(['url_courte'], 'inscription_links_url_courte_unique');
        });

        Schema::create('localites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('arrondissement_id')->nullable();
            $table->unsignedBigInteger('regroupement_id')->nullable();
            $table->enum('type', ['quartier', 'village'])->comment('Type: quartier (urbain) ou village (rural)');
            $table->string('nom', 255)->comment('Nom du quartier ou village');
            $table->string('code', 35)->comment('Code unique de la localité');
            $table->text('description')->nullable()->comment('Description de la localité');
            $table->decimal('superficie_km2', 8, 4)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude');
            $table->decimal('altitude_m', 8, 2)->nullable()->comment('Altitude en mètres');
            $table->string('responsable_nom', 255)->nullable()->comment('Nom du chef de quartier/village');
            $table->string('responsable_telephone', 255)->nullable()->comment('Téléphone du responsable');
            $table->string('responsable_email', 255)->nullable()->comment('Email du responsable');
            $table->longText('ethnies_presentes')->nullable()->comment('Ethnies présentes dans la localité');
            $table->longText('langues_parlees')->nullable()->comment('Langues parlées');
            $table->longText('religions_pratiquees')->nullable()->comment('Religions pratiquées');
            $table->longText('activites_economiques')->nullable()->comment('Activités économiques principales');
            $table->longText('commerces_presents')->nullable()->comment('Types de commerces présents');
            $table->longText('services_artisanaux')->nullable()->comment('Services artisanaux disponibles');
            $table->boolean('ecole_maternelle')->default(false)->comment('École maternelle');
            $table->boolean('ecole_primaire')->default(false)->comment('École primaire');
            $table->boolean('college')->default(false)->comment('Collège');
            $table->boolean('lycee')->default(false)->comment('Lycée');
            $table->boolean('centre_sante')->default(false)->comment('Centre de santé');
            $table->boolean('pharmacie')->default(false)->comment('Pharmacie');
            $table->boolean('marche')->default(false)->comment('Marché');
            $table->boolean('eglise')->default(false)->comment('Église');
            $table->boolean('mosquee')->default(false)->comment('Mosquée');
            $table->boolean('terrain_sport')->default(false)->comment('Terrain de sport');
            $table->enum('type_route', ['bitumee', 'laterite', 'piste', 'sentier', 'aucune'])->nullable()->comment('Type de route d\'accès');
            $table->boolean('transport_public')->default(false)->comment('Transport public disponible');
            $table->enum('acces_eau', ['robinet', 'borne_fontaine', 'puits', 'forage', 'riviere', 'source', 'aucun'])->nullable()->comment('Type d\'accès à l\'eau');
            $table->boolean('electricite')->default(false)->comment('Électricité disponible');
            $table->enum('assainissement', ['tout_a_legout', 'fosse_septique', 'latrine', 'aucun'])->nullable()->comment('Type d\'assainissement');
            $table->enum('gestion_dechets', ['collecte_municipale', 'enfouissement', 'incineration', 'aucune'])->nullable()->comment('Gestion des déchets');
            $table->enum('couverture_mobile', ['aucune', 'faible', 'moyenne', 'bonne', 'excellente'])->default('aucune')->comment('Couverture réseau mobile');
            $table->boolean('internet_disponible')->default(false)->comment('Internet disponible');
            $table->boolean('radio_communautaire')->default(false)->comment('Radio communautaire');
            $table->boolean('poste_police')->default(false)->comment('Poste de police/gendarmerie');
            $table->boolean('eclairage_public')->default(false)->comment('Éclairage public');
            $table->enum('niveau_securite', ['tres_faible', 'faible', 'moyen', 'bon', 'excellent'])->default('moyen')->comment('Niveau de sécurité perçu');
            $table->longText('evenements_culturels')->nullable()->comment('Événements culturels annuels');
            $table->longText('problemes_recurrents')->nullable()->comment('Problèmes récurrents identifiés');
            $table->longText('projets_developpement')->nullable()->comment('Projets de développement en cours/prévus');
            $table->longText('metadata')->nullable()->comment('Autres données supplémentaires');
            $table->boolean('is_active')->default(true)->comment('Localité active ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index(['arrondissement_id', 'type', 'is_active'], 'localites_arrondissement_id_type_is_active_index');
            $table->index(['code'], 'localites_code_index');
            $table->index(['ecole_primaire', 'centre_sante'], 'localites_ecole_primaire_centre_sante_index');
            $table->index(['electricite', 'transport_public'], 'localites_electricite_transport_public_index');
            $table->index(['nom'], 'localites_nom_index');
            $table->index(['regroupement_id', 'type', 'is_active'], 'localites_regroupement_id_type_is_active_index');
            $table->index(['type', 'is_active', 'ordre_affichage'], 'localites_type_is_active_ordre_affichage_index');
            $table->unique(['arrondissement_id', 'code'], 'unique_code_quartier_par_arrond');
            $table->unique(['regroupement_id', 'code'], 'unique_code_village_par_regroupement');
            $table->unique(['arrondissement_id', 'nom'], 'unique_quartier_par_arrond');
            $table->unique(['regroupement_id', 'nom'], 'unique_village_par_regroupement');
            $table->comment('Table unifiée des quartiers (urbain) et villages (rural) du Gabon - Niveau 5 FINAL de la hiérarchie géographique');
        });

        Schema::create('membres_bureau', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_id');
            $table->string('nip', 50);
            $table->string('nom', 100);
            $table->string('prenom', 100)->nullable();
            $table->string('fonction', 150)->comment('Fonction dans l\'organisation');
            $table->string('contact', 255)->nullable()->comment('Téléphone ou email');
            $table->string('domicile', 255)->nullable()->comment('Adresse de résidence');
            $table->boolean('afficher_recepisse')->default(false)->comment('Afficher sur le récépissé définitif');
            $table->unsignedSmallInteger('ordre')->default(0)->comment('Ordre d\'affichage');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['organisation_id', 'afficher_recepisse'], 'membres_bureau_organisation_id_afficher_recepisse_index');
        });

        Schema::create('nip_database', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 255)->comment('Nom de famille');
            $table->string('prenom', 255)->nullable()->comment('Prénom');
            $table->date('date_naissance')->nullable()->comment('Date de naissance extraite du NIP');
            $table->string('lieu_naissance', 255)->nullable()->comment('Lieu de naissance');
            $table->string('nip', 20)->comment('Numéro d\'Identification Personnel unique');
            $table->string('sexe', 10)->nullable()->comment('Sexe extrait du NIP');
            $table->enum('statut', ['actif', 'inactif', 'decede', 'suspendu'])->nullable()->default('actif')->comment('Statut du NIP');
            $table->string('telephone', 255)->nullable()->comment('Numéro de téléphone');
            $table->string('email', 255)->nullable()->comment('Adresse email');
            $table->text('remarques')->nullable()->comment('Remarques ou notes administratives');
            $table->string('source_import', 100)->nullable()->comment('Source du fichier d\'import');
            $table->timestamp('date_import')->nullable()->comment('Date d\'import dans la base');
            $table->unsignedBigInteger('imported_by')->nullable()->comment('Utilisateur ayant effectué l\'import');
            $table->timestamp('last_verified_at')->nullable()->comment('Dernière vérification');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['imported_by'], 'idx_nip_database_imported_by');
            $table->index(['date_import'], 'nip_database_date_import_index');
            $table->index(['date_naissance'], 'nip_database_date_naissance_index');
            $table->index(['nip', 'statut'], 'nip_database_nip_statut_index');
            $table->unique(['nip'], 'nip_database_nip_unique');
            $table->index(['nom', 'prenom'], 'nip_database_nom_prenom_index');
            $table->index(['statut'], 'nip_database_statut_index');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->char('id', 36);
            $table->string('type', 255);
            $table->string('notifiable_type', 255);
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('operation_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 50)->comment('Code unique de l\'opération');
            $table->string('libelle', 100)->comment('Libellé de l\'opération');
            $table->text('description')->nullable()->comment('Description détaillée');
            $table->boolean('is_active')->default(true)->comment('Opération active');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['code'], 'operation_types_code_unique');
            $table->index(['is_active'], 'operation_types_is_active_index');
            $table->index(['ordre'], 'operation_types_ordre_index');
        });

        Schema::create('organe_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('organe_type_id');
            $table->string('nip', 255);
            $table->string('nom', 255);
            $table->string('prenom', 255)->nullable();
            $table->string('poste', 255);
            $table->date('date_nomination');
            $table->date('date_fin_mandat')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('telephone', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['organe_type_id'], 'idx_organe_members_organe_type_id');
            $table->index(['nip'], 'organe_members_nip_index');
            $table->index(['organisation_id', 'organe_type_id'], 'organe_members_organisation_id_organe_type_id_index');
            $table->unique(['organisation_id', 'organe_type_id', 'poste', 'is_active'], 'unique_organe_member');
        });

        Schema::create('organe_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->string('libelle', 255);
            $table->enum('type_organisation', ['association', 'ong', 'parti_politique', 'confession_religieuse']);
            $table->longText('postes_disponibles');
            $table->integer('membres_min')->default(3);
            $table->integer('membres_max')->nullable();
            $table->boolean('is_obligatoire')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['code'], 'organe_types_code_unique');
            $table->index(['type_organisation', 'is_active'], 'organe_types_type_organisation_is_active_index');
        });

        Schema::create('organisation_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type_organisation', ['association', 'ong', 'parti_politique', 'confession_religieuse']);
            $table->string('parametre', 255);
            $table->string('valeur', 255);
            $table->text('description')->nullable();
            $table->enum('type_valeur', ['string', 'integer', 'boolean', 'json'])->default('string');
            $table->boolean('is_editable')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['parametre'], 'organisation_settings_parametre_index');
            $table->unique(['type_organisation', 'parametre'], 'organisation_settings_type_organisation_parametre_unique');
        });

        Schema::create('organisation_type_document_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organisation_type_id');
            $table->unsignedBigInteger('document_type_id');
            $table->boolean('is_obligatoire')->default(true)->comment('Document obligatoire (true) ou facultatif (false)');
            $table->unsignedInteger('ordre')->default(0)->comment('Ordre d\'affichage du document dans la liste (0 = premier)');
            $table->text('aide_texte')->nullable()->comment('Instructions ou aide spécifique pour ce document et ce type d\'organisation');
            $table->text('modele_texte')->nullable()->comment('Texte modèle ou exemple pour aider à remplir le document');
            $table->string('exemple_fichier', 255)->nullable()->comment('Chemin vers un fichier exemple (PDF, image)');
            $table->longText('regles_validation')->nullable()->comment('Règles de validation spécifiques en JSON (taille max, formats, etc.)');
            $table->longText('metadata')->nullable()->comment('Données supplémentaires en JSON');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['document_type_id', 'is_obligatoire'], 'idx_doc_type_obligatoire');
            $table->index(['ordre'], 'idx_ordre');
            $table->index(['organisation_type_id', 'is_obligatoire'], 'idx_org_type_obligatoire');
            $table->unique(['organisation_type_id', 'document_type_id'], 'unique_org_type_doc_type');
        });

        Schema::create('organisation_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 50)->comment('Code unique : association, ong, parti_politique, confession_religieuse');
            $table->string('nom', 150)->comment('Nom complet affiché (ex: Association)');
            $table->text('description')->nullable()->comment('Description détaillée du type d\'organisation');
            $table->string('couleur', 7)->default('#007bff')->comment('Couleur hex pour l\'interface (#007bff, #28a745, etc.)');
            $table->string('icone', 50)->nullable()->comment('Classe d\'icône Font Awesome (ex: fa-users, fa-building)');
            $table->boolean('is_lucratif')->default(false)->comment('But lucratif (true) ou non lucratif (false)');
            $table->unsignedInteger('nb_min_fondateurs_majeurs')->default(2)->comment('Nombre minimum de fondateurs majeurs requis à la création');
            $table->unsignedInteger('nb_min_adherents_creation')->default(10)->comment('Nombre minimum d\'adhérents requis à la création');
            $table->text('guide_creation')->nullable()->comment('Guide explicatif de création (format Markdown ou HTML)');
            $table->text('texte_legislatif')->nullable()->comment('Texte de loi ou référence législative applicable');
            $table->string('loi_reference', 100)->nullable()->comment('Référence de la loi (ex: Loi 016/2025 du 15 octobre)');
            $table->boolean('is_active')->default(true)->comment('Type actif ou désactivé');
            $table->unsignedInteger('ordre')->default(0)->comment('Ordre d\'affichage dans les listes (0 = premier)');
            $table->longText('metadata')->nullable()->comment('Données supplémentaires en JSON (config avancée)');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable()->comment('Suppression douce (soft delete)');
            $table->index(['is_active', 'ordre'], 'idx_active_ordre');
            $table->index(['code'], 'idx_code');
            $table->unique(['code'], 'organisation_types_code_unique');
        });

        Schema::create('organisations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organisation_type_id')->comment('FK vers organisation_types (remplace ENUM type)');
            $table->unsignedBigInteger('domaine_activite_id')->nullable();
            $table->enum('type', ['association', 'ong', 'parti_politique', 'confession_religieuse']);
            $table->string('nom', 255);
            $table->string('sigle', 255)->nullable();
            $table->text('objet')->comment('Objet social');
            $table->string('siege_social', 255)->comment('Adresse complète');
            $table->string('province', 255);
            $table->string('departement', 255)->nullable();
            $table->string('canton', 255)->nullable();
            $table->string('prefecture', 255)->nullable();
            $table->string('sous_prefecture', 255)->nullable();
            $table->string('regroupement', 255)->nullable();
            $table->enum('zone_type', ['urbaine', 'rurale'])->default('urbaine');
            $table->string('ville_commune', 255)->nullable()->comment('Pour zone urbaine');
            $table->string('arrondissement', 255)->nullable()->comment('Pour zone urbaine');
            $table->string('quartier', 255)->nullable();
            $table->string('village', 255)->nullable()->comment('Pour zone rurale');
            $table->string('lieu_dit', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedBigInteger('province_ref_id')->nullable();
            $table->unsignedBigInteger('departement_ref_id')->nullable();
            $table->unsignedBigInteger('commune_ville_ref_id')->nullable();
            $table->unsignedBigInteger('arrondissement_ref_id')->nullable();
            $table->unsignedBigInteger('canton_ref_id')->nullable();
            $table->unsignedBigInteger('regroupement_ref_id')->nullable();
            $table->unsignedBigInteger('localite_ref_id')->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telephone', 255);
            $table->string('telephone_secondaire', 255)->nullable();
            $table->string('site_web', 255)->nullable();
            $table->string('numero_recepisse', 255)->nullable();
            $table->date('date_creation');
            $table->enum('statut', ['brouillon', 'soumis', 'en_validation', 'approuve', 'rejete', 'suspendu', 'radie'])->default('brouillon');
            $table->boolean('is_active')->default(true);
            $table->integer('nombre_adherents_min')->default(10);
            $table->boolean('has_anomalies_majeures')->default(false);
            $table->longText('organes_gestion')->nullable()->comment('Membres des organes de gestion avec NIP');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['arrondissement_ref_id'], 'idx_organisations_arrondissement_ref_id');
            $table->index(['canton_ref_id'], 'idx_organisations_canton_ref_id');
            $table->index(['commune_ville_ref_id'], 'idx_organisations_commune_ville_ref_id');
            $table->index(['departement_ref_id'], 'idx_organisations_departement_ref_id');
            $table->index(['domaine_activite_id'], 'idx_organisations_domaine_activite_id');
            $table->index(['localite_ref_id'], 'idx_organisations_localite_ref_id');
            $table->index(['regroupement_ref_id'], 'idx_organisations_regroupement_ref_id');
            $table->index(['organisation_type_id'], 'idx_organisations_type_id');
            $table->index(['province_ref_id', 'departement_ref_id'], 'org_prov_dept_idx');
            $table->index(['zone_type', 'canton_ref_id'], 'org_zone_cant_idx');
            $table->index(['zone_type', 'commune_ville_ref_id'], 'org_zone_comm_idx');
            $table->index(['zone_type', 'province_ref_id'], 'org_zone_prov_idx');
            $table->unique(['nom'], 'organisations_nom_unique');
            $table->index(['numero_recepisse'], 'organisations_numero_recepisse_index');
            $table->unique(['numero_recepisse'], 'organisations_numero_recepisse_unique');
            $table->index(['province', 'prefecture'], 'organisations_province_prefecture_index');
            $table->index(['province_ref_id', 'departement_ref_id'], 'organisations_province_ref_id_departement_ref_id_index');
            $table->unique(['sigle'], 'organisations_sigle_unique');
            $table->index(['statut'], 'organisations_statut_index');
            $table->index(['user_id', 'type'], 'organisations_user_id_type_index');
            $table->index(['zone_type'], 'organisations_zone_type_index');
            $table->index(['zone_type', 'province_ref_id'], 'organisations_zone_type_province_ref_id_index');
        });

        Schema::create('organization_drafts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->enum('organization_type', ['association', 'ong', 'parti_politique', 'confession_religieuse'])->nullable();
            $table->longText('form_data');
            $table->integer('current_step')->default(1);
            $table->integer('completion_percentage')->default(0);
            $table->longText('validation_errors')->nullable();
            $table->string('session_id', 255)->nullable();
            $table->timestamp('last_saved_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['current_step'], 'organization_drafts_current_step_index');
            $table->index(['expires_at'], 'organization_drafts_expires_at_index');
            $table->index(['last_saved_at'], 'organization_drafts_last_saved_at_index');
            $table->index(['user_id', 'organization_type'], 'organization_drafts_user_id_organization_type_index');
            $table->unique(['user_id', 'organization_type'], 'unique_user_org_type_draft');
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email', 255);
            $table->string('token', 255);
            $table->timestamp('created_at')->nullable();
            $table->index(['email'], 'password_resets_email_index');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->comment('Nom unique de la permission');
            $table->string('display_name', 150)->comment('Nom affiché de la permission');
            $table->string('category', 50)->comment('Catégorie: users, organizations, workflow, system, content');
            $table->text('description')->nullable()->comment('Description détaillée');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['category'], 'permissions_category_index');
            $table->index(['name'], 'permissions_name_index');
            $table->unique(['name'], 'permissions_name_unique');
        });

        Schema::create('portail_actualites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('titre', 255);
            $table->string('slug', 255);
            $table->text('extrait')->nullable();
            $table->longText('contenu');
            $table->string('image', 255)->nullable();
            $table->string('categorie', 255)->default('Général');
            $table->string('auteur', 255)->default('Administration');
            $table->enum('statut', ['brouillon', 'publie', 'archive'])->default('brouillon');
            $table->unsignedBigInteger('vues')->default(0);
            $table->boolean('en_une')->default(false);
            $table->timestamp('date_publication')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['categorie'], 'portail_actualites_categorie_index');
            $table->unique(['slug'], 'portail_actualites_slug_unique');
            $table->index(['statut', 'date_publication'], 'portail_actualites_statut_date_publication_index');
        });

        Schema::create('portail_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('titre', 255);
            $table->text('description')->nullable();
            $table->string('categorie', 255)->default('Guides');
            $table->string('type_organisation', 255)->default('tous');
            $table->string('format', 255)->default('PDF');
            $table->string('taille', 255)->nullable();
            $table->string('chemin_fichier', 255)->nullable();
            $table->string('url_externe', 255)->nullable();
            $table->unsignedBigInteger('nombre_telechargements')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['est_actif', 'categorie'], 'portail_documents_est_actif_categorie_index');
        });

        Schema::create('portail_evenements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('titre', 255);
            $table->text('description')->nullable();
            $table->enum('type', ['echeance', 'formation', 'maintenance', 'evenement'])->default('evenement');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('lieu', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->boolean('est_important')->default(false);
            $table->boolean('est_actif')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['est_actif', 'date_debut'], 'portail_evenements_est_actif_date_debut_index');
        });

        Schema::create('portail_faqs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('question', 255);
            $table->text('reponse');
            $table->string('categorie', 255)->default('Général');
            $table->unsignedInteger('ordre')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['est_actif', 'categorie', 'ordre'], 'portail_faqs_est_actif_categorie_ordre_index');
        });

        Schema::create('portail_guides', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('titre', 255);
            $table->text('description')->nullable();
            $table->string('categorie', 255)->default('Général');
            $table->string('chemin_fichier', 255)->nullable();
            $table->string('url_externe', 255)->nullable();
            $table->unsignedInteger('nombre_pages')->default(0);
            $table->unsignedBigInteger('nombre_telechargements')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['est_actif', 'ordre'], 'portail_guides_est_actif_ordre_index');
        });

        Schema::create('portail_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 255);
            $table->string('email', 255);
            $table->string('sujet', 255);
            $table->text('message');
            $table->enum('statut', ['non_lu', 'lu', 'traite', 'archive'])->default('non_lu');
            $table->text('reponse')->nullable();
            $table->timestamp('date_reponse')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['statut', 'created_at'], 'portail_messages_statut_created_at_index');
        });

        Schema::create('portail_parametres', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cle', 255);
            $table->longText('valeur')->nullable();
            $table->enum('type', ['text', 'html', 'json', 'image', 'url', 'email', 'phone'])->default('text');
            $table->string('description', 255)->nullable();
            $table->string('groupe', 255)->default('general');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['cle'], 'portail_parametres_cle_unique');
            $table->index(['groupe'], 'portail_parametres_groupe_index');
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 255)->comment('Nom de la province');
            $table->string('code', 10)->comment('Code unique de la province (ex: EST, LIT, etc.)');
            $table->string('chef_lieu', 255)->nullable()->comment('Chef-lieu de la province');
            $table->text('description')->nullable()->comment('Description de la province');
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            $table->longText('metadata')->nullable()->comment('Données supplémentaires (frontières, limites, etc.)');
            $table->boolean('is_active')->default(true)->comment('Province active ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['code'], 'provinces_code_index');
            $table->unique(['code'], 'provinces_code_unique');
            $table->index(['is_active', 'ordre_affichage'], 'provinces_is_active_ordre_affichage_index');
            $table->index(['nom'], 'provinces_nom_index');
            $table->unique(['nom'], 'provinces_nom_unique');
            $table->comment('Table des provinces du Gabon - Niveau 1 de la hiérarchie géographique');
        });

        Schema::create('qr_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->string('type', 255)->comment('Type de document');
            $table->string('verifiable_type', 255)->nullable()->comment('Type du modèle vérifiable');
            $table->unsignedBigInteger('verifiable_id')->nullable()->comment('ID du modèle vérifiable');
            $table->string('document_numero', 255)->nullable();
            $table->longText('donnees_verification')->nullable()->comment('Données à afficher lors de la vérification');
            $table->longText('svg_content')->nullable()->comment('Contenu SVG du QR Code généré');
            $table->longText('png_base64')->nullable()->comment('Image PNG du QR Code en base64 pour PDF');
            $table->string('file_path', 255)->nullable();
            $table->string('verification_url', 255)->nullable()->comment('URL complète de vérification du QR Code');
            $table->string('hash_verification', 255)->comment('Hash pour vérifier l\'intégrité');
            $table->integer('nombre_verifications')->default(0);
            $table->timestamp('derniere_verification')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['code'], 'qr_codes_code_index');
            $table->unique(['code'], 'qr_codes_code_unique');
            $table->index(['expire_at'], 'qr_codes_expire_at_index');
            $table->index(['is_active'], 'qr_codes_is_active_index');
            $table->index(['verifiable_type', 'verifiable_id'], 'qr_codes_verifiable_type_verifiable_id_index');
        });

        Schema::create('regroupements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('canton_id');
            $table->string('nom', 255)->comment('Nom du regroupement');
            $table->string('code', 30)->comment('Code unique du regroupement');
            $table->string('village_centre', 255)->nullable()->comment('Village centre du regroupement');
            $table->text('description')->nullable()->comment('Description du regroupement');
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            $table->string('chef_regroupement', 255)->nullable()->comment('Nom du chef de regroupement');
            $table->string('telephone_chef', 255)->nullable()->comment('Téléphone du chef de regroupement');
            $table->integer('nombre_villages')->default(0)->comment('Nombre de villages dans le regroupement');
            $table->text('villages_composants')->nullable()->comment('Liste des villages composant le regroupement');
            $table->longText('ethnies_dominantes')->nullable()->comment('Ethnies dominantes dans le regroupement');
            $table->longText('langues_courantes')->nullable()->comment('Langues couramment parlées');
            $table->longText('activites_principales')->nullable()->comment('Activités économiques principales');
            $table->longText('cultures_pratiquees')->nullable()->comment('Cultures agricoles pratiquées');
            $table->longText('elevage_pratique')->nullable()->comment('Types d\'élevage pratiqués');
            $table->boolean('ecole_primaire')->default(false)->comment('Présence d\'école primaire');
            $table->boolean('ecole_secondaire')->default(false)->comment('Présence d\'école secondaire');
            $table->boolean('centre_sante')->default(false)->comment('Présence de centre de santé');
            $table->boolean('marche')->default(false)->comment('Présence de marché');
            $table->boolean('route_praticable')->default(false)->comment('Route praticable toute l\'année');
            $table->boolean('transport_commun')->default(false)->comment('Transport en commun disponible');
            $table->enum('acces_eau', ['puits', 'forage', 'riviere', 'source', 'adduction', 'aucun'])->nullable()->comment('Type d\'accès à l\'eau');
            $table->boolean('electricite_disponible')->default(false)->comment('Électricité disponible');
            $table->enum('couverture_reseau', ['aucune', 'faible', 'moyenne', 'bonne'])->default('aucune')->comment('Couverture réseau téléphonique');
            $table->longText('metadata')->nullable()->comment('Données supplémentaires (traditions, événements, etc.)');
            $table->boolean('is_active')->default(true)->comment('Regroupement actif ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index(['canton_id', 'is_active', 'ordre_affichage'], 'regroupements_canton_id_is_active_ordre_affichage_index');
            $table->index(['code'], 'regroupements_code_index');
            $table->index(['ecole_primaire', 'centre_sante'], 'regroupements_ecole_primaire_centre_sante_index');
            $table->index(['nom'], 'regroupements_nom_index');
            $table->index(['route_praticable', 'transport_commun'], 'regroupements_route_praticable_transport_commun_index');
            $table->unique(['canton_id', 'code'], 'unique_code_regroupement_par_canton');
            $table->unique(['canton_id', 'nom'], 'unique_regroupement_par_canton');
            $table->comment('Table des regroupements ruraux du Gabon - Niveau 4 RURAL, rattachés aux cantons');
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->comment('ID du rôle');
            $table->unsignedBigInteger('permission_id')->comment('ID de la permission');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->primary(['role_id', 'permission_id']);
            $table->index(['permission_id'], 'role_permissions_permission_id_index');
            $table->index(['role_id'], 'role_permissions_role_id_index');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50)->comment('Nom unique du rôle');
            $table->string('display_name', 100)->comment('Nom affiché du rôle');
            $table->text('description')->nullable()->comment('Description du rôle');
            $table->string('color', 7)->default('#007bff')->comment('Couleur hex pour UI');
            $table->integer('level')->default(1)->comment('Niveau hiérarchique');
            $table->boolean('is_active')->default(true)->comment('Rôle actif ou non');
            $table->boolean('is_system')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['is_active', 'level'], 'roles_is_active_level_index');
            $table->index(['name'], 'roles_name_index');
            $table->unique(['name'], 'roles_name_unique');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 255);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
            $table->primary(['id']);
            $table->index(['last_activity'], 'sessions_last_activity_index');
            $table->index(['user_id'], 'sessions_user_id_index');
        });

        Schema::create('two_factor_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('code', 6);
            $table->timestamp('expires_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['expires_at', 'used'], 'two_factor_codes_expires_at_used_index');
            $table->index(['user_id', 'code'], 'two_factor_codes_user_id_code_index');
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('ID de l\'utilisateur');
            $table->string('session_id', 255)->comment('ID de session Laravel');
            $table->string('ip_address', 45)->comment('Adresse IP (IPv4/IPv6)');
            $table->text('user_agent')->comment('Navigateur et OS');
            $table->timestamp('login_at')->useCurrent()->useCurrentOnUpdate()->comment('Date/heure de connexion');
            $table->timestamp('logout_at')->nullable()->comment('Date/heure de déconnexion');
            $table->boolean('is_active')->default(true)->comment('Session active ou fermée');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['ip_address'], 'user_sessions_ip_address_index');
            $table->index(['login_at'], 'user_sessions_login_at_index');
            $table->index(['session_id'], 'user_sessions_session_id_index');
            $table->index(['user_id', 'is_active'], 'user_sessions_user_id_is_active_index');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('nom', 255)->nullable()->comment('Nom de famille de l\'utilisateur');
            $table->string('prenom', 255)->nullable()->comment('Prénom de l\'utilisateur');
            $table->string('email', 255);
            $table->string('nip', 255)->nullable()->comment('Numéro d\'Identification Personnel');
            $table->string('phone', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->unsignedBigInteger('role_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending');
            $table->enum('role', ['admin', 'agent', 'operator', 'visitor'])->default('visitor');
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('country', 255)->default('Gabon');
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance', 255)->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret', 255)->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->integer('login_attempts')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->string('verification_token', 255)->nullable();
            $table->string('last_login_ip', 255)->nullable();
            $table->longText('preferences')->nullable()->comment('Préférences utilisateur (notifications, langue, etc.)');
            $table->longText('metadata')->nullable()->comment('Métadonnées additionnelles');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->index(['created_by'], 'idx_users_created_by');
            $table->index(['nom', 'prenom'], 'idx_users_nom_prenom');
            $table->index(['updated_by'], 'idx_users_updated_by');
            $table->index(['email', 'is_active'], 'users_email_is_active_index');
            $table->unique(['email'], 'users_email_unique');
            $table->index(['is_active'], 'users_is_active_index');
            $table->index(['is_active', 'status'], 'users_is_active_status_index');
            $table->index(['last_login_at'], 'users_last_login_at_index');
            $table->index(['locked_until'], 'users_locked_until_index');
            $table->index(['role_id', 'status'], 'users_role_id_status_index');
            $table->index(['role'], 'users_role_index');
            $table->index(['role', 'is_active'], 'users_role_is_active_index');
            $table->index(['role', 'status'], 'users_role_status_index');
            $table->index(['verification_token'], 'users_verification_token_index');
        });

        Schema::create('validation_entities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->string('nom', 255);
            $table->text('description')->nullable();
            $table->enum('type', ['direction', 'service', 'departement', 'commission', 'externe']);
            $table->string('email_notification', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('capacite_traitement')->default(10)->comment('Nombre de dossiers par jour');
            $table->longText('horaires_travail')->nullable()->comment('Horaires de travail');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['code'], 'validation_entities_code_unique');
            $table->index(['is_active'], 'validation_entities_is_active_index');
            $table->index(['type'], 'validation_entities_type_index');
        });

        Schema::create('workflow_step_entities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('workflow_step_id');
            $table->unsignedBigInteger('validation_entity_id');
            $table->integer('ordre')->default(1)->comment('Ordre si plusieurs entités pour une étape');
            $table->boolean('is_optional')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['validation_entity_id'], 'idx_workflow_step_entities_validation_entity_id');
            $table->unique(['workflow_step_id', 'validation_entity_id'], 'unique_step_entity');
            $table->index(['ordre'], 'workflow_step_entities_ordre_index');
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->string('libelle', 255);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('organisation_type_id')->comment('FK vers organisation_types (remplace ENUM type_organisation)');
            $table->unsignedBigInteger('operation_type_id')->comment('FK vers operation_types (remplace ENUM type_operation)');
            $table->enum('type_organisation_old', ['association', 'ong', 'parti_politique', 'confession_religieuse'])->nullable()->comment('DEPRECATED - Utiliser organisation_type_id');
            $table->enum('type_operation_old', ['creation', 'modification', 'cessation', 'ajout_adherent', 'retrait_adherent', 'declaration_activite', 'changement_statutaire'])->nullable()->comment('DEPRECATED - Utiliser operation_type_id');
            $table->integer('numero_passage')->comment('Ordre de passage dans le workflow');
            $table->boolean('is_active')->default(true);
            $table->boolean('permet_rejet')->default(true);
            $table->boolean('permet_commentaire')->default(true);
            $table->boolean('genere_document')->default(false);
            $table->string('template_document', 255)->nullable()->comment('Template du document à générer');
            $table->longText('champs_requis')->nullable()->comment('Champs à remplir à cette étape');
            $table->integer('delai_traitement')->default(48)->comment('Délai en heures');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['operation_type_id'], 'fk_workflow_steps_op_type');
            $table->index(['organisation_type_id', 'operation_type_id', 'is_active'], 'idx_workflow_org_op_active');
            $table->unique(['code'], 'workflow_steps_code_unique');
            $table->index(['numero_passage'], 'workflow_steps_numero_passage_index');
            $table->index(['type_organisation_old', 'type_operation_old', 'is_active'], 'workflow_steps_type_organisation_type_operation_is_active_index');
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom', 255);
            $table->string('code', 10);
            $table->enum('type', ['province', 'departement', 'prefecture', 'sous_prefecture', 'district', 'canton', 'commune', 'arrondissement', 'quartier', 'village', 'regroupement', 'lieu_dit']);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('chef_lieu', 255)->nullable();
            $table->integer('population_estimee')->nullable();
            $table->decimal('superficie_km2', 10, 2)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->longText('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['code'], 'zones_code_unique');
            $table->index(['nom'], 'zones_nom_index');
            $table->index(['nom', 'type'], 'zones_nom_type_index');
            $table->index(['parent_id', 'type'], 'zones_parent_id_type_index');
            $table->index(['type'], 'zones_type_index');
            $table->index(['type', 'is_active'], 'zones_type_is_active_index');
        });

        // Clés étrangères, une fois toutes les tables créées
        Schema::table('adherent_anomalies', function (Blueprint $table) {
            $table->foreign(['adherent_id'], 'adherent_anomalies_adherent_id_fk')->references(['id'])->on('adherents')->onDelete('cascade');
            $table->foreign(['corrige_par'], 'adherent_anomalies_corrige_par_fk')->references(['id'])->on('users')->onDelete('set null');
            $table->foreign(['organisation_id'], 'adherent_anomalies_organisation_id_fk')->references(['id'])->on('organisations')->onDelete('cascade');
        });

        Schema::table('adherent_detection_logs', function (Blueprint $table) {
            $table->foreign(['nouvelle_organisation_id'], 'adherent_detection_logs_nouvelle_organisation_id_fk')->references(['id'])->on('organisations')->onDelete('cascade');
        });

        Schema::table('adherent_exclusions', function (Blueprint $table) {
            $table->foreign(['adherent_id'], 'adherent_exclusions_adherent_id_fk')->references(['id'])->on('adherents')->onDelete('cascade');
            $table->foreign(['organisation_id'], 'adherent_exclusions_organisation_id_fk')->references(['id'])->on('organisations');
            $table->foreign(['validated_by'], 'adherent_exclusions_validated_by_fk')->references(['id'])->on('users');
        });

        Schema::table('adherent_histories', function (Blueprint $table) {
            $table->foreign(['adherent_id'], 'adherent_histories_adherent_id_fk')->references(['id'])->on('adherents')->onDelete('cascade');
            $table->foreign(['ancienne_organisation_id'], 'adherent_histories_ancienne_organisation_id_fk')->references(['id'])->on('organisations');
            $table->foreign(['created_by'], 'adherent_histories_created_by_fk')->references(['id'])->on('users');
            $table->foreign(['nouvelle_organisation_id'], 'adherent_histories_nouvelle_organisation_id_fk')->references(['id'])->on('organisations');
            $table->foreign(['organisation_id'], 'adherent_histories_organisation_id_fk')->references(['id'])->on('organisations');
            $table->foreign(['validated_by'], 'adherent_histories_validated_by_fk')->references(['id'])->on('users');
        });

        Schema::table('adherent_imports', function (Blueprint $table) {
            $table->foreign(['imported_by'], 'adherent_imports_imported_by_fk')->references(['id'])->on('users');
            $table->foreign(['organisation_id'], 'adherent_imports_organisation_id_fk')->references(['id'])->on('organisations');
        });

        Schema::table('adherents', function (Blueprint $table) {
            $table->foreign(['arrondissement_ref_id'], 'adherents_arrondissement_ref_id_fk')->references(['id'])->on('arrondissements')->onDelete('set null');
            $table->foreign(['canton_ref_id'], 'adherents_canton_ref_id_fk')->references(['id'])->on('cantons')->onDelete('set null');
            $table->foreign(['commune_ville_ref_id'], 'adherents_commune_ville_ref_id_fk')->references(['id'])->on('communes_villes')->onDelete('set null');
            $table->foreign(['departement_ref_id'], 'adherents_departement_ref_id_fk')->references(['id'])->on('departements')->onDelete('set null');
            $table->foreign(['fondateur_id'], 'adherents_fondateur_id_fk')->references(['id'])->on('fondateurs')->onDelete('set null');
            $table->foreign(['inscription_link_id'], 'adherents_inscription_link_id_fk')->references(['id'])->on('inscription_links')->onDelete('set null');
            $table->foreign(['localite_ref_id'], 'adherents_localite_ref_id_fk')->references(['id'])->on('localites')->onDelete('set null');
            $table->foreign(['organisation_id'], 'adherents_organisation_id_fk')->references(['id'])->on('organisations')->onDelete('cascade');
            $table->foreign(['province_ref_id'], 'adherents_province_ref_id_fk')->references(['id'])->on('provinces')->onDelete('set null');
            $table->foreign(['regroupement_ref_id'], 'adherents_regroupement_ref_id_fk')->references(['id'])->on('regroupements')->onDelete('set null');
            $table->foreign(['validateur_id'], 'adherents_validateur_id_fk')->references(['id'])->on('users')->onDelete('set null');
            $table->foreign(['validee_par'], 'adherents_validee_par_fk')->references(['id'])->on('users')->onDelete('set null');
        });

        Schema::table('arrondissements', function (Blueprint $table) {
            $table->foreign(['commune_ville_id'], 'arrondissements_commune_ville_id_fk')->references(['id'])->on('communes_villes')->onDelete('cascade');
        });

        Schema::table('cantons', function (Blueprint $table) {
            $table->foreign(['departement_id'], 'cantons_departement_id_fk')->references(['id'])->on('departements')->onDelete('cascade');
        });

        Schema::table('communes_villes', function (Blueprint $table) {
            $table->foreign(['departement_id'], 'communes_villes_departement_id_fk')->references(['id'])->on('departements')->onDelete('cascade');
        });

        Schema::table('declaration_documents', function (Blueprint $table) {
            $table->foreign(['declaration_id'], 'declaration_documents_declaration_id_fk')->references(['id'])->on('declarations')->onDelete('cascade');
        });

        Schema::table('declarations', function (Blueprint $table) {
            $table->foreign(['declaration_type_id'], 'declarations_declaration_type_id_fk')->references(['id'])->on('declaration_types');
            $table->foreign(['organisation_id'], 'declarations_organisation_id_fk')->references(['id'])->on('organisations');
            $table->foreign(['submitted_by'], 'declarations_submitted_by_fk')->references(['id'])->on('users');
            $table->foreign(['validated_by'], 'declarations_validated_by_fk')->references(['id'])->on('users');
        });

        Schema::table('departements', function (Blueprint $table) {
            $table->foreign(['province_id'], 'departements_province_id_fk')->references(['id'])->on('provinces')->onDelete('cascade');
        });

        Schema::table('document_generation_customizations', function (Blueprint $table) {
            $table->foreign(['customized_by'], 'document_generation_customizations_customized_by_fk')->references(['id'])->on('users')->onDelete('set null');
            $table->foreign(['document_template_id'], 'document_generation_customizations_document_template_id_fk')->references(['id'])->on('document_templates')->onDelete('cascade');
            $table->foreign(['dossier_id'], 'document_generation_customizations_dossier_id_fk')->references(['id'])->on('dossiers')->onDelete('cascade');
        });

        Schema::table('document_generations', function (Blueprint $table) {
            $table->foreign(['document_template_id'], 'document_generations_document_template_id_fk')->references(['id'])->on('document_templates')->onDelete('cascade');
            $table->foreign(['dossier_id'], 'document_generations_dossier_id_fk')->references(['id'])->on('dossiers')->onDelete('cascade');
            $table->foreign(['dossier_validation_id'], 'document_generations_dossier_validation_id_fk')->references(['id'])->on('dossier_validations')->onDelete('cascade');
            $table->foreign(['generated_by'], 'document_generations_generated_by_fk')->references(['id'])->on('users')->onDelete('cascade');
            $table->foreign(['invalidated_by'], 'document_generations_invalidated_by_fk')->references(['id'])->on('users')->onDelete('set null');
            $table->foreign(['last_downloaded_by'], 'document_generations_last_downloaded_by_fk')->references(['id'])->on('users')->onDelete('set null');
            $table->foreign(['organisation_id'], 'document_generations_organisation_id_fk')->references(['id'])->on('organisations')->onDelete('cascade');
        });

        Schema::table('document_templates', function (Blueprint $table) {
            $table->foreign(['operation_type_id'], 'document_templates_operation_type_id_fk')->references(['id'])->on('operation_types')->onDelete('cascade');
            $table->foreign(['organisation_type_id'], 'document_templates_organisation_type_id_fk')->references(['id'])->on('organisation_types')->onDelete('cascade');
            $table->foreign(['workflow_step_id'], 'document_templates_workflow_step_id_fk')->references(['id'])->on('workflow_steps')->onDelete('cascade');
        });

        Schema::table('document_type_operation_type', function (Blueprint $table) {
            $table->foreign(['document_type_id'], 'document_type_operation_type_document_type_id_fk')->references(['id'])->on('document_types')->onDelete('cascade');
            $table->foreign(['operation_type_id'], 'document_type_operation_type_operation_type_id_fk')->references(['id'])->on('operation_types')->onDelete('cascade');
        });

        Schema::table('document_type_organisation_type', function (Blueprint $table) {
            $table->foreign(['document_type_id'], 'document_type_organisation_type_document_type_id_fk')->references(['id'])->on('document_types')->onDelete('cascade');
            $table->foreign(['organisation_type_id'], 'document_type_organisation_type_organisation_type_id_fk')->references(['id'])->on('organisation_types')->onDelete('cascade');
        });

        Schema::table('document_verifications', function (Blueprint $table) {
            $table->foreign(['document_generation_id'], 'document_verifications_document_generation_id_fk')->references(['id'])->on('document_generations')->onDelete('cascade');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreign(['document_type_id'], 'documents_document_type_id_fk')->references(['id'])->on('document_types');
            $table->foreign(['dossier_id'], 'documents_dossier_id_fk')->references(['id'])->on('dossiers')->onDelete('cascade');
            $table->foreign(['uploaded_by'], 'fk_documents_uploaded_by')->references(['id'])->on('users')->onDelete('set null')->onUpdate('cascade');
        });

        Schema::table('dossier_archives', function (Blueprint $table) {
            $table->foreign(['archived_by'], 'dossier_archives_archived_by_fk')->references(['id'])->on('users');
            $table->foreign(['dossier_id'], 'dossier_archives_dossier_id_fk')->references(['id'])->on('dossiers');
        });

        Schema::table('dossier_comments', function (Blueprint $table) {
            $table->foreign(['dossier_id'], 'dossier_comments_dossier_id_fk')->references(['id'])->on('dossiers')->onDelete('cascade');
            $table->foreign(['parent_id'], 'dossier_comments_parent_id_fk')->references(['id'])->on('dossier_comments');
            $table->foreign(['user_id'], 'dossier_comments_user_id_fk')->references(['id'])->on('users');
            $table->foreign(['workflow_step_id'], 'dossier_comments_workflow_step_id_fk')->references(['id'])->on('workflow_steps');
        });

        Schema::table('dossier_corrections', function (Blueprint $table) {
            $table->foreign(['approved_by'], 'dossier_corrections_approved_by_foreign')->references(['id'])->on('users')->onDelete('set null');
            $table->foreign(['corrected_by'], 'dossier_corrections_corrected_by_foreign')->references(['id'])->on('users')->onDelete('cascade');
            $table->foreign(['dossier_id'], 'dossier_corrections_dossier_id_foreign')->references(['id'])->on('dossiers')->onDelete('cascade');
            $table->foreign(['original_dossier_id'], 'dossier_corrections_original_dossier_id_foreign')->references(['id'])->on('dossiers')->onDelete('cascade');
        });

        Schema::table('dossier_locks', function (Blueprint $table) {
            $table->foreign(['dossier_id'], 'dossier_locks_dossier_id_fk')->references(['id'])->on('dossiers')->onDelete('cascade');
            $table->foreign(['locked_by'], 'dossier_locks_locked_by_fk')->references(['id'])->on('users');
            $table->foreign(['workflow_step_id'], 'dossier_locks_workflow_step_id_fk')->references(['id'])->on('workflow_steps');
        });

        Schema::table('dossier_operations', function (Blueprint $table) {
            $table->foreign(['dossier_id'], 'dossier_operations_dossier_id_fk')->references(['id'])->on('dossiers')->onDelete('cascade');
            $table->foreign(['user_id'], 'dossier_operations_user_id_fk')->references(['id'])->on('users');
            $table->foreign(['workflow_step_id'], 'dossier_operations_workflow_step_id_fk')->references(['id'])->on('workflow_steps');
        });

        Schema::table('dossier_priority_history', function (Blueprint $table) {
            $table->foreign(['changed_by'], 'dossier_priority_history_changed_by_fk')->references(['id'])->on('users')->onDelete('cascade');
            $table->foreign(['dossier_id'], 'dossier_priority_history_dossier_id_fk')->references(['id'])->on('dossiers')->onDelete('cascade');
        });

        Schema::table('dossier_validations', function (Blueprint $table) {
            $table->foreign(['document_generation_id'], 'dossier_validations_document_generation_id_fk')->references(['id'])->on('document_generations')->onDelete('set null');
            $table->foreign(['dossier_id'], 'dossier_validations_dossier_id_fk')->references(['id'])->on('dossiers')->onDelete('cascade');
            $table->foreign(['validated_by'], 'dossier_validations_validated_by_fk')->references(['id'])->on('users');
            $table->foreign(['validation_entity_id'], 'dossier_validations_validation_entity_id_fk')->references(['id'])->on('validation_entities');
            $table->foreign(['workflow_step_id'], 'dossier_validations_workflow_step_id_fk')->references(['id'])->on('workflow_steps');
        });

        Schema::table('dossiers', function (Blueprint $table) {
            $table->foreign(['assigned_to'], 'dossiers_assigned_to_fk')->references(['id'])->on('users');
            $table->foreign(['current_step_id'], 'dossiers_current_step_id_fk')->references(['id'])->on('workflow_steps');
            $table->foreign(['locked_by'], 'dossiers_locked_by_fk')->references(['id'])->on('users');
            $table->foreign(['organisation_id'], 'dossiers_organisation_id_fk')->references(['id'])->on('organisations')->onDelete('cascade');
            $table->foreign(['parent_dossier_id'], 'dossiers_parent_dossier_id_fk')->references(['id'])->on('dossiers')->onDelete('set null');
            $table->foreign(['priorite_assignee_par'], 'dossiers_priorite_assignee_par_fk')->references(['id'])->on('users')->onDelete('set null');
        });

        Schema::table('draft_accuses', function (Blueprint $table) {
            $table->foreign(['draft_id'], 'draft_accuses_draft_id_fk')->references(['id'])->on('organization_drafts')->onDelete('cascade');
            $table->foreign(['user_id'], 'draft_accuses_user_id_fk')->references(['id'])->on('users')->onDelete('cascade');
        });

        Schema::table('entity_agents', function (Blueprint $table) {
            $table->foreign(['user_id'], 'entity_agents_user_id_fk')->references(['id'])->on('users')->onDelete('cascade');
            $table->foreign(['validation_entity_id'], 'entity_agents_validation_entity_id_fk')->references(['id'])->on('validation_entities')->onDelete('cascade');
        });

        Schema::table('etablissements', function (Blueprint $table) {
            $table->foreign(['arrondissement_ref_id'], 'etablissements_arrondissement_ref_id_fk')->references(['id'])->on('arrondissements')->onDelete('set null');
            $table->foreign(['canton_ref_id'], 'etablissements_canton_ref_id_fk')->references(['id'])->on('cantons')->onDelete('set null');
            $table->foreign(['commune_ville_ref_id'], 'etablissements_commune_ville_ref_id_fk')->references(['id'])->on('communes_villes')->onDelete('set null');
            $table->foreign(['departement_ref_id'], 'etablissements_departement_ref_id_fk')->references(['id'])->on('departements')->onDelete('set null');
            $table->foreign(['localite_ref_id'], 'etablissements_localite_ref_id_fk')->references(['id'])->on('localites')->onDelete('set null');
            $table->foreign(['organisation_id'], 'etablissements_organisation_id_fk')->references(['id'])->on('organisations')->onDelete('cascade');
            $table->foreign(['province_ref_id'], 'etablissements_province_ref_id_fk')->references(['id'])->on('provinces')->onDelete('set null');
            $table->foreign(['regroupement_ref_id'], 'etablissements_regroupement_ref_id_fk')->references(['id'])->on('regroupements')->onDelete('set null');
        });

        Schema::table('fondateurs', function (Blueprint $table) {
            $table->foreign(['arrondissement_ref_id'], 'fondateurs_arrondissement_ref_id_fk')->references(['id'])->on('arrondissements')->onDelete('set null');
            $table->foreign(['canton_ref_id'], 'fondateurs_canton_ref_id_fk')->references(['id'])->on('cantons')->onDelete('set null');
            $table->foreign(['commune_ville_ref_id'], 'fondateurs_commune_ville_ref_id_fk')->references(['id'])->on('communes_villes')->onDelete('set null');
            $table->foreign(['departement_ref_id'], 'fondateurs_departement_ref_id_fk')->references(['id'])->on('departements')->onDelete('set null');
            $table->foreign(['localite_ref_id'], 'fondateurs_localite_ref_id_fk')->references(['id'])->on('localites')->onDelete('set null');
            $table->foreign(['organisation_id'], 'fondateurs_organisation_id_fk')->references(['id'])->on('organisations')->onDelete('cascade');
            $table->foreign(['province_ref_id'], 'fondateurs_province_ref_id_fk')->references(['id'])->on('provinces')->onDelete('set null');
            $table->foreign(['regroupement_ref_id'], 'fondateurs_regroupement_ref_id_fk')->references(['id'])->on('regroupements')->onDelete('set null');
        });

        Schema::table('inscription_links', function (Blueprint $table) {
            $table->foreign(['created_by'], 'inscription_links_created_by_fk')->references(['id'])->on('users');
            $table->foreign(['organisation_id'], 'inscription_links_organisation_id_fk')->references(['id'])->on('organisations');
        });

        Schema::table('localites', function (Blueprint $table) {
            $table->foreign(['arrondissement_id'], 'localites_arrondissement_id_fk')->references(['id'])->on('arrondissements')->onDelete('cascade');
            $table->foreign(['regroupement_id'], 'localites_regroupement_id_fk')->references(['id'])->on('regroupements')->onDelete('cascade');
        });

        Schema::table('membres_bureau', function (Blueprint $table) {
            $table->foreign(['organisation_id'], 'membres_bureau_organisation_id_fk')->references(['id'])->on('organisations')->onDelete('cascade');
        });

        Schema::table('nip_database', function (Blueprint $table) {
            $table->foreign(['imported_by'], 'nip_database_imported_by_fk')->references(['id'])->on('users')->onDelete('set null');
        });

        Schema::table('organe_members', function (Blueprint $table) {
            $table->foreign(['organe_type_id'], 'organe_members_organe_type_id_fk')->references(['id'])->on('organe_types');
            $table->foreign(['organisation_id'], 'organe_members_organisation_id_fk')->references(['id'])->on('organisations');
        });

        Schema::table('organisation_type_document_type', function (Blueprint $table) {
            $table->foreign(['document_type_id'], 'organisation_type_document_type_document_type_id_fk')->references(['id'])->on('document_types')->onDelete('cascade');
            $table->foreign(['organisation_type_id'], 'organisation_type_document_type_organisation_type_id_fk')->references(['id'])->on('organisation_types')->onDelete('cascade');
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->foreign(['organisation_type_id'], 'fk_organisations_type')->references(['id'])->on('organisation_types')->onUpdate('cascade');
            $table->foreign(['arrondissement_ref_id'], 'organisations_arrondissement_ref_id_fk')->references(['id'])->on('arrondissements')->onDelete('set null');
            $table->foreign(['canton_ref_id'], 'organisations_canton_ref_id_fk')->references(['id'])->on('cantons')->onDelete('set null');
            $table->foreign(['commune_ville_ref_id'], 'organisations_commune_ville_ref_id_fk')->references(['id'])->on('communes_villes')->onDelete('set null');
            $table->foreign(['departement_ref_id'], 'organisations_departement_ref_id_fk')->references(['id'])->on('departements')->onDelete('set null');
            $table->foreign(['domaine_activite_id'], 'organisations_domaine_activite_id_fk')->references(['id'])->on('domaines_activite')->onDelete('set null');
            $table->foreign(['localite_ref_id'], 'organisations_localite_ref_id_fk')->references(['id'])->on('localites')->onDelete('set null');
            $table->foreign(['province_ref_id'], 'organisations_province_ref_id_fk')->references(['id'])->on('provinces')->onDelete('set null');
            $table->foreign(['regroupement_ref_id'], 'organisations_regroupement_ref_id_fk')->references(['id'])->on('regroupements')->onDelete('set null');
            $table->foreign(['user_id'], 'organisations_user_id_fk')->references(['id'])->on('users');
        });

        Schema::table('organization_drafts', function (Blueprint $table) {
            $table->foreign(['user_id'], 'organization_drafts_user_id_fk')->references(['id'])->on('users')->onDelete('cascade');
        });

        Schema::table('regroupements', function (Blueprint $table) {
            $table->foreign(['canton_id'], 'regroupements_canton_id_fk')->references(['id'])->on('cantons')->onDelete('cascade');
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'role_permissions_permission_id_fk')->references(['id'])->on('permissions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(['role_id'], 'role_permissions_role_id_fk')->references(['id'])->on('roles')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('two_factor_codes', function (Blueprint $table) {
            $table->foreign(['user_id'], 'two_factor_codes_user_id_fk')->references(['id'])->on('users')->onDelete('cascade');
        });

        Schema::table('user_sessions', function (Blueprint $table) {
            $table->foreign(['user_id'], 'user_sessions_user_id_fk')->references(['id'])->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign(['created_by'], 'users_created_by_fk')->references(['id'])->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign(['role_id'], 'users_role_id_fk')->references(['id'])->on('roles')->onDelete('set null')->onUpdate('cascade');
            $table->foreign(['updated_by'], 'users_updated_by_fk')->references(['id'])->on('users')->onDelete('set null')->onUpdate('cascade');
        });

        Schema::table('workflow_step_entities', function (Blueprint $table) {
            $table->foreign(['validation_entity_id'], 'workflow_step_entities_validation_entity_id_fk')->references(['id'])->on('validation_entities')->onDelete('cascade');
            $table->foreign(['workflow_step_id'], 'workflow_step_entities_workflow_step_id_fk')->references(['id'])->on('workflow_steps')->onDelete('cascade');
        });

        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->foreign(['operation_type_id'], 'fk_workflow_steps_op_type')->references(['id'])->on('operation_types')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(['organisation_type_id'], 'fk_workflow_steps_org_type')->references(['id'])->on('organisation_types')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->foreign(['parent_id'], 'zones_parent_id_fk')->references(['id'])->on('zones')->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('zones');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_step_entities');
        Schema::dropIfExists('validation_entities');
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('two_factor_codes');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('regroupements');
        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('portail_parametres');
        Schema::dropIfExists('portail_messages');
        Schema::dropIfExists('portail_guides');
        Schema::dropIfExists('portail_faqs');
        Schema::dropIfExists('portail_evenements');
        Schema::dropIfExists('portail_documents');
        Schema::dropIfExists('portail_actualites');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('organization_drafts');
        Schema::dropIfExists('organisations');
        Schema::dropIfExists('organisation_types');
        Schema::dropIfExists('organisation_type_document_type');
        Schema::dropIfExists('organisation_settings');
        Schema::dropIfExists('organe_types');
        Schema::dropIfExists('organe_members');
        Schema::dropIfExists('operation_types');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('nip_database');
        Schema::dropIfExists('membres_bureau');
        Schema::dropIfExists('localites');
        Schema::dropIfExists('inscription_links');
        Schema::dropIfExists('guide_contents');
        Schema::dropIfExists('fondateurs');
        Schema::dropIfExists('fonctions');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('etablissements');
        Schema::dropIfExists('entity_agents');
        Schema::dropIfExists('draft_accuses');
        Schema::dropIfExists('dossiers');
        Schema::dropIfExists('dossier_validations');
        Schema::dropIfExists('dossier_priority_history');
        Schema::dropIfExists('dossier_operations');
        Schema::dropIfExists('dossier_locks');
        Schema::dropIfExists('dossier_corrections');
        Schema::dropIfExists('dossier_comments');
        Schema::dropIfExists('dossier_archives');
        Schema::dropIfExists('domaines_activite');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_verifications');
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('document_type_organisation_type');
        Schema::dropIfExists('document_type_operation_type');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_generations');
        Schema::dropIfExists('document_generation_customizations');
        Schema::dropIfExists('departements');
        Schema::dropIfExists('declarations');
        Schema::dropIfExists('declaration_types');
        Schema::dropIfExists('declaration_documents');
        Schema::dropIfExists('communes_villes');
        Schema::dropIfExists('cantons');
        Schema::dropIfExists('arrondissements');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('adherents');
        Schema::dropIfExists('adherent_imports');
        Schema::dropIfExists('adherent_histories');
        Schema::dropIfExists('adherent_exclusions');
        Schema::dropIfExists('adherent_detection_logs');
        Schema::dropIfExists('adherent_anomalies');
        Schema::enableForeignKeyConstraints();
    }
};
