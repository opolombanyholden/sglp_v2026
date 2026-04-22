@extends('layouts.admin')

@section('title', 'Modification - ' . $organisation->nom)

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1">
                    <i class="fas fa-edit me-2 text-info"></i>
                    Modification d'organisation
                </h2>
                <p class="text-muted mb-0">Modifier les informations de l'organisation</p>
            </div>
            <a href="{{ route('admin.operations.select-operation', $organisation->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>

        <!-- Carte organisation -->
        <div class="card mb-4 border-info">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar-circle bg-info text-white">
                            {{ strtoupper(substr($organisation->sigle ?? $organisation->nom, 0, 2)) }}
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-0">{{ $organisation->nom }}</h5>
                        <small class="text-muted">{{ $organisation->organisationType->nom ?? 'N/A' }} |
                            {{ $organisation->numero_recepisse ?? 'N/A' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <form id="operationForm" action="{{ route('admin.operations.store', [$organisation->id, $operationType->code]) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" id="formAction" value="brouillon">

            <!-- Type de modification -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i> Type de modification</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Que souhaitez-vous modifier ? <span
                                    class="text-danger">*</span></label>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input modification-type" type="radio"
                                            name="type_modification" id="type_informations" value="informations" checked>
                                        <label class="form-check-label" for="type_informations">
                                            <i class="fas fa-info-circle text-info me-2"></i>
                                            <strong>Informations générales</strong>
                                            <small class="d-block text-muted">Nom, sigle, siège social, contacts,
                                                objet...</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input modification-type" type="radio"
                                            name="type_modification" id="type_statutaire" value="changement_statutaire">
                                        <label class="form-check-label" for="type_statutaire">
                                            <i class="fas fa-gavel text-purple me-2"></i>
                                            <strong>Statuts / Règlement intérieur</strong>
                                            <small class="d-block text-muted">Modification d'articles des statuts ou du
                                                R.I.</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input modification-type" type="radio"
                                            name="type_modification" id="type_bureau" value="bureau">
                                        <label class="form-check-label" for="type_bureau">
                                            <i class="fas fa-users-cog text-warning me-2"></i>
                                            <strong>Bureau exécutif</strong>
                                            <small class="d-block text-muted">Changement des membres du bureau</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input modification-type" type="radio"
                                            name="type_modification" id="type_mixte" value="mixte">
                                        <label class="form-check-label" for="type_mixte">
                                            <i class="fas fa-layer-group text-success me-2"></i>
                                            <strong>Modifications multiples</strong>
                                            <small class="d-block text-muted">Plusieurs types de modifications à la
                                                fois</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section : Informations générales (visible par défaut) -->
            <div id="section-informations" class="modification-section">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i> Identité de l'organisation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="nom" class="form-label">Nom de l'organisation</label>
                                <input type="text" class="form-control" id="nom" name="modifications[nom]"
                                    value="{{ old('modifications.nom', $organisation->nom) }}">
                                <small class="form-text text-muted">Actuel : {{ $organisation->nom }}</small>
                            </div>
                            <div class="col-md-4">
                                <label for="sigle" class="form-label">Sigle / Acronyme</label>
                                <input type="text" class="form-control" id="sigle" name="modifications[sigle]"
                                    value="{{ old('modifications.sigle', $organisation->sigle) }}">
                            </div>
                            <div class="col-12">
                                <label for="objet" class="form-label">Objet / But de l'organisation</label>
                                <textarea class="form-control" id="objet" name="modifications[objet]"
                                    rows="3">{{ old('modifications.objet', $organisation->objet) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Localisation -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Localisation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="siege_social" class="form-label">Siège social (adresse complète)</label>
                                <input type="text" class="form-control" id="siege_social" name="modifications[siege_social]"
                                    value="{{ old('modifications.siege_social', $organisation->siege_social) }}">
                                @if($organisation->siege_social)
                                    <small class="form-text text-muted">Actuel : {{ $organisation->siege_social }}</small>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="zone_type" class="form-label">Type de zone</label>
                                <select class="form-select" id="zone_type" name="modifications[zone_type]">
                                    <option value="">Sélectionnez...</option>
                                    <option value="urbaine" {{ old('modifications.zone_type', $organisation->zone_type) == 'urbaine' ? 'selected' : '' }}>Urbaine</option>
                                    <option value="rurale" {{ old('modifications.zone_type', $organisation->zone_type) == 'rurale' ? 'selected' : '' }}>Rurale</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="province_id" class="form-label">Province</label>
                                <select class="form-select" id="province_id" name="modifications[province_ref_id]" data-current-text="{{ $organisation->province }}">
                                    <option value="">Sélectionnez une province</option>
                                    @foreach($provinces ?? [] as $prov)
                                        <option value="{{ $prov->id }}"
                                            {{ old('modifications.province_ref_id', $organisation->province_ref_id) == $prov->id ? 'selected' : '' }}>
                                            {{ $prov->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($organisation->province)
                                    <small class="form-text text-muted">Actuelle : {{ $organisation->province }}</small>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="departement_id" class="form-label">Département</label>
                                <select class="form-select" id="departement_id" name="modifications[departement_ref_id]"
                                    data-current-id="{{ $organisation->departement_ref_id }}"
                                    data-current-text="{{ $organisation->departement }}" disabled>
                                    <option value="">Sélectionnez d'abord une province</option>
                                </select>
                                @if($organisation->departement)
                                    <small class="form-text text-muted">Actuel : {{ $organisation->departement }}</small>
                                @endif
                            </div>

                            {{-- Zone URBAINE --}}
                            <div class="col-md-4 zone-urbaine-field" style="display:none;">
                                <label for="commune_id" class="form-label">Commune / Ville</label>
                                <select class="form-select" id="commune_id" name="modifications[commune_ville_ref_id]"
                                    data-current-id="{{ $organisation->commune_ville_ref_id }}"
                                    data-current-text="{{ $organisation->ville_commune }}" disabled>
                                    <option value="">Sélectionnez d'abord un département</option>
                                </select>
                                @if($organisation->ville_commune)
                                    <small class="form-text text-muted">Actuelle : {{ $organisation->ville_commune }}</small>
                                @endif
                            </div>

                            <div class="col-md-4 zone-urbaine-field" style="display:none;">
                                <label for="arrondissement_id" class="form-label">Arrondissement</label>
                                <select class="form-select" id="arrondissement_id" name="modifications[arrondissement_ref_id]"
                                    data-current-id="{{ $organisation->arrondissement_ref_id }}"
                                    data-current-text="{{ $organisation->arrondissement }}" disabled>
                                    <option value="">Sélectionnez d'abord une commune</option>
                                </select>
                                @if($organisation->arrondissement)
                                    <small class="form-text text-muted">Actuel : {{ $organisation->arrondissement }}</small>
                                @endif
                            </div>

                            <div class="col-md-4 zone-urbaine-field" style="display:none;">
                                <label for="quartier_id" class="form-label">Quartier</label>
                                <select class="form-select" id="quartier_id" name="modifications[quartier]"
                                    data-current-text="{{ $organisation->quartier }}" disabled>
                                    <option value="">Sélectionnez d'abord un arrondissement</option>
                                </select>
                                @if($organisation->quartier)
                                    <small class="form-text text-muted">Actuel : {{ $organisation->quartier }}</small>
                                @endif
                            </div>

                            {{-- Zone RURALE --}}
                            <div class="col-md-4 zone-rurale-field" style="display:none;">
                                <label for="canton_id" class="form-label">Canton</label>
                                <select class="form-select" id="canton_id" name="modifications[canton_ref_id]"
                                    data-current-id="{{ $organisation->canton_ref_id }}"
                                    data-current-text="{{ $organisation->canton }}" disabled>
                                    <option value="">Sélectionnez d'abord un département</option>
                                </select>
                                @if($organisation->canton)
                                    <small class="form-text text-muted">Actuel : {{ $organisation->canton }}</small>
                                @endif
                            </div>

                            <div class="col-md-4 zone-rurale-field" style="display:none;">
                                <label for="regroupement_id" class="form-label">Regroupement</label>
                                <select class="form-select" id="regroupement_id" name="modifications[regroupement_ref_id]"
                                    data-current-id="{{ $organisation->regroupement_ref_id }}"
                                    data-current-text="{{ $organisation->regroupement }}" disabled>
                                    <option value="">Sélectionnez d'abord un canton</option>
                                </select>
                                @if($organisation->regroupement)
                                    <small class="form-text text-muted">Actuel : {{ $organisation->regroupement }}</small>
                                @endif
                            </div>

                            <div class="col-md-4 zone-rurale-field" style="display:none;">
                                <label for="village_id" class="form-label">Village / Lieu-dit</label>
                                <select class="form-select" id="village_id" name="modifications[village]"
                                    data-current-text="{{ $organisation->village }}" disabled>
                                    <option value="">Sélectionnez d'abord un regroupement</option>
                                </select>
                                @if($organisation->village)
                                    <small class="form-text text-muted">Actuel : {{ $organisation->village }}</small>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="sous_prefecture" class="form-label">Sous-préfecture</label>
                                <input type="text" class="form-control" id="sous_prefecture"
                                    name="modifications[sous_prefecture]"
                                    value="{{ old('modifications.sous_prefecture', $organisation->sous_prefecture) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contacts -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-address-book me-2"></i> Coordonnées de contact</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="modifications[email]"
                                    value="{{ old('modifications.email', $organisation->email) }}">
                                @if($organisation->email)
                                    <small class="form-text text-muted">Actuel : {{ $organisation->email }}</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="telephone" class="form-label">Téléphone principal</label>
                                <input type="text" class="form-control" id="telephone" name="modifications[telephone]"
                                    value="{{ old('modifications.telephone', $organisation->telephone) }}">
                                @if($organisation->telephone)
                                    <small class="form-text text-muted">Actuel : {{ $organisation->telephone }}</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="telephone_secondaire" class="form-label">Téléphone secondaire</label>
                                <input type="text" class="form-control" id="telephone_secondaire"
                                    name="modifications[telephone_secondaire]"
                                    value="{{ old('modifications.telephone_secondaire', $organisation->telephone_secondaire) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="site_web" class="form-label">Site web</label>
                                <input type="url" class="form-control" id="site_web" name="modifications[site_web]"
                                    value="{{ old('modifications.site_web', $organisation->site_web) }}"
                                    placeholder="https://...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section : Changement statutaire (caché par défaut) -->
            <div id="section-statutaire" class="modification-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-gavel me-2"></i> Modification des statuts / règlement intérieur
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Important :</strong> Précisez chaque article modifié avec son contenu avant et après
                            modification.
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="date_ag" class="form-label">Date de l'Assemblée Générale <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_ag" name="date_ag">
                                <small class="form-text text-muted">Date à laquelle les modifications ont été votées</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Document(s) concerné(s) <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="doc_statuts"
                                            name="documents_concernes[]" value="statuts" checked>
                                        <label class="form-check-label" for="doc_statuts">
                                            <i class="fas fa-book me-1"></i> Statuts
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="doc_reglement"
                                            name="documents_concernes[]" value="reglement_interieur">
                                        <label class="form-check-label" for="doc_reglement">
                                            <i class="fas fa-list-alt me-1"></i> Règlement intérieur
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conteneur des articles modifiés -->
                        <h6 class="text-primary mb-3"><i class="fas fa-file-contract me-2"></i>Articles modifiés</h6>
                        <div id="articles-container">
                            <!-- Premier article -->
                            <div class="article-modification-item border rounded p-3 mb-3 bg-light" data-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-paragraph me-2"></i>
                                        <span class="article-number">Article #1</span>
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-article"
                                        style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Document</label>
                                        <select class="form-select form-select-sm" name="articles[0][document]">
                                            <option value="statuts">Statuts</option>
                                            <option value="reglement_interieur">Règlement intérieur</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Numéro/Référence <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="articles[0][numero]"
                                            placeholder="Ex: Article 5">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Titre/Intitulé</label>
                                        <input type="text" class="form-control form-control-sm" name="articles[0][titre]"
                                            placeholder="Ex: Conditions d'adhésion">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            <i class="fas fa-arrow-left text-danger me-1"></i>
                                            Ancienne rédaction <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" name="articles[0][ancien_contenu]" rows="4"
                                            placeholder="Texte AVANT modification..."></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            <i class="fas fa-arrow-right text-success me-1"></i>
                                            Nouvelle rédaction <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" name="articles[0][nouveau_contenu]" rows="4"
                                            placeholder="Texte APRÈS modification..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Motif de cette modification</label>
                                        <input type="text" class="form-control form-control-sm" name="articles[0][motif]"
                                            placeholder="Pourquoi cet article a été modifié...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-outline-primary" id="add-article-btn">
                                <i class="fas fa-plus me-2"></i> Ajouter un autre article modifié
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section : Bureau exécutif (caché par défaut) -->
            <div id="section-bureau" class="modification-section" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-users-cog me-2"></i> Modification du bureau exécutif</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            La modification du bureau nécessite un procès-verbal d'AG ou de réunion du bureau.
                        </div>
                        <!-- Bureau actuel -->
                        @if($organisation->membresBureau && $organisation->membresBureau->count() > 0)
                            <h6 class="text-secondary mb-3"><i class="fas fa-users me-2"></i>Composition actuelle du bureau</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fonction</th>
                                            <th>Nom complet</th>
                                            <th>Téléphone</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($organisation->membresBureau as $index => $membre)
                                            <tr>
                                                <td><strong>{{ $membre->fonction }}</strong></td>
                                                <td>{{ $membre->prenom }} {{ $membre->nom }}</td>
                                                <td>{{ $membre->telephone ?? '-' }}</td>
                                                <td class="text-center">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="bureau_modifications[retirer][]" value="{{ $membre->id }}"
                                                            id="retirer_{{ $membre->id }}">
                                                        <label class="form-check-label text-danger" for="retirer_{{ $membre->id }}">
                                                            Retirer
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucun membre du bureau enregistré actuellement.
                            </div>
                        @endif

                        <!-- Nouveaux membres / Modifications -->
                        <h6 class="text-primary mb-3"><i class="fas fa-user-plus me-2"></i>Ajouter ou modifier des membres
                        </h6>
                        <div id="bureau-members-container">
                            <!-- Premier membre -->
                            <div class="bureau-member-item border rounded p-3 mb-3 bg-light" data-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 text-warning">
                                        <i class="fas fa-user-tie me-2"></i>
                                        <span class="member-number">Membre #1</span>
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-bureau-member"
                                        style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Type de changement</label>
                                        <select class="form-select form-select-sm"
                                            name="bureau_membres[0][type_changement]">
                                            <option value="ajout">Nouveau membre</option>
                                            <option value="modification">Modification fonction</option>
                                            <option value="remplacement">Remplacement</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Fonction <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                            name="bureau_membres[0][fonction]"
                                            placeholder="Ex: Président, Secrétaire général...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Civilité</label>
                                        <select class="form-select form-select-sm" name="bureau_membres[0][civilite]">
                                            <option value="">Sélectionnez...</option>
                                            <option value="M.">M.</option>
                                            <option value="Mme">Mme</option>
                                            <option value="Mlle">Mlle</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                            name="bureau_membres[0][nom]" placeholder="Nom de famille">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                            name="bureau_membres[0][prenom]" placeholder="Prénom(s)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Date de naissance</label>
                                        <input type="date" class="form-control form-control-sm"
                                            name="bureau_membres[0][date_naissance]">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Téléphone</label>
                                        <input type="text" class="form-control form-control-sm"
                                            name="bureau_membres[0][telephone]" placeholder="+241...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control form-control-sm"
                                            name="bureau_membres[0][email]" placeholder="email@exemple.com">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-outline-warning" id="add-bureau-member-btn">
                                <i class="fas fa-plus me-2"></i> Ajouter un autre membre
                            </button>
                        </div>

                        <hr class="my-4">

                        <!-- Description générale des changements -->
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="description_bureau" class="form-label">Commentaires supplémentaires sur les
                                    changements du bureau</label>
                                <textarea class="form-control" id="description_bureau"
                                    name="modifications[bureau_description]" rows="3"
                                    placeholder="Précisions sur les changements, date effective, etc."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Justification (toujours visible) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comment me-2"></i> Justification</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="justification" class="form-label">Raisons des modifications <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="justification" name="modifications[justification]" rows="3"
                                required
                                placeholder="Expliquez les raisons de ces modifications...">{{ old('modifications.justification') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents à fournir -->
            @if($documentTypes->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i> Documents à fournir</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($documentTypes as $docType)
                                <div class="col-md-6">
                                    <label for="doc_{{ $docType->id }}" class="form-label">
                                        {{ $docType->nom }}
                                        @if($docType->pivot->is_obligatoire)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="file" class="form-control" id="doc_{{ $docType->id }}"
                                        name="documents[{{ $docType->id }}]">
                                    @if($docType->description)
                                        <small class="form-text text-muted">{{ $docType->description }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Boutons d'action -->
            <div class="card">
                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('admin.operations.select-operation', $organisation->id) }}"
                        class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Annuler
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-warning" onclick="submitFormWithAction('brouillon')">
                            <i class="fas fa-save me-1"></i> Enregistrer Brouillon
                        </button>
                        <button type="button" class="btn btn-success" onclick="submitFormWithAction('soumettre')">
                            <i class="fas fa-paper-plane me-1"></i> Soumettre
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .avatar-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }

        .text-purple {
            color: #7c3aed;
        }

        .form-check-label strong {
            display: block;
        }

        .modification-type:checked+.form-check-label {
            color: #0d6efd;
        }

        .article-modification-item {
            transition: all 0.3s ease;
        }

        .article-modification-item:hover {
            border-color: #7c3aed !important;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.15);
        }

        .article-modification-item:hover .remove-article {
            opacity: 1;
        }

        .article-modification-item .remove-article {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        #add-article-btn {
            border-style: dashed;
            border-width: 2px;
        }
    </style>

    <script>
        let articleIndex = 1;

        function submitFormWithAction(action) {
            document.getElementById('formAction').value = action;
            document.getElementById('operationForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const typeRadios = document.querySelectorAll('.modification-type');
            const sectionInfo = document.getElementById('section-informations');
            const sectionStatutaire = document.getElementById('section-statutaire');
            const sectionBureau = document.getElementById('section-bureau');
            const container = document.getElementById('articles-container');
            const addBtn = document.getElementById('add-article-btn');


            // Gestion de l'affichage des sections selon le type
            typeRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    // Cacher toutes les sections
                    if (sectionInfo) sectionInfo.style.display = 'none';
                    if (sectionStatutaire) sectionStatutaire.style.display = 'none';
                    if (sectionBureau) sectionBureau.style.display = 'none';

                    // Afficher selon le type sélectionné
                    switch (this.value) {
                        case 'informations':
                            if (sectionInfo) sectionInfo.style.display = 'block';
                            break;
                        case 'changement_statutaire':
                            if (sectionStatutaire) sectionStatutaire.style.display = 'block';
                            break;
                        case 'bureau':
                            if (sectionBureau) sectionBureau.style.display = 'block';
                            break;
                        case 'mixte':
                            if (sectionInfo) sectionInfo.style.display = 'block';
                            if (sectionStatutaire) sectionStatutaire.style.display = 'block';
                            if (sectionBureau) sectionBureau.style.display = 'block';
                            break;
                    }
                });
            });

            // Ajout d'article (section statutaire)
            if (addBtn && container) {
                addBtn.addEventListener('click', function () {
                    const newArticle = document.createElement('div');
                    newArticle.className = 'article-modification-item border rounded p-3 mb-3 bg-light';
                    newArticle.setAttribute('data-index', articleIndex);

                    newArticle.innerHTML = `
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0 text-primary">
                                                <i class="fas fa-paragraph me-2"></i>
                                                <span class="article-number">Article #${articleIndex + 1}</span>
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-article">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Document</label>
                                                <select class="form-select form-select-sm" name="articles[${articleIndex}][document]">
                                                    <option value="statuts">Statuts</option>
                                                    <option value="reglement_interieur">Règlement intérieur</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Numéro/Référence <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" name="articles[${articleIndex}][numero]" 
                                                       placeholder="Ex: Article 5">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Titre/Intitulé</label>
                                                <input type="text" class="form-control form-control-sm" name="articles[${articleIndex}][titre]" 
                                                       placeholder="Ex: Conditions d'adhésion">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <i class="fas fa-arrow-left text-danger me-1"></i>
                                                    Ancienne rédaction <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control" name="articles[${articleIndex}][ancien_contenu]" rows="4"
                                                          placeholder="Texte AVANT modification..."></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    <i class="fas fa-arrow-right text-success me-1"></i>
                                                    Nouvelle rédaction <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control" name="articles[${articleIndex}][nouveau_contenu]" rows="4"
                                                          placeholder="Texte APRÈS modification..."></textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Motif de cette modification</label>
                                                <input type="text" class="form-control form-control-sm" name="articles[${articleIndex}][motif]" 
                                                       placeholder="Pourquoi cet article a été modifié...">
                                            </div>
                                        </div>
                                    `;

                    container.appendChild(newArticle);
                    articleIndex++;
                    updateArticleNumbers();
                    updateRemoveButtons();
                });

                // Suppression d'article
                container.addEventListener('click', function (e) {
                    if (e.target.closest('.remove-article')) {
                        const item = e.target.closest('.article-modification-item');
                        if (item) {
                            item.remove();
                            updateArticleNumbers();
                            updateRemoveButtons();
                        }
                    }
                });

                function updateArticleNumbers() {
                    const items = container.querySelectorAll('.article-modification-item');
                    items.forEach((item, index) => {
                        const numberSpan = item.querySelector('.article-number');
                        if (numberSpan) {
                            numberSpan.textContent = `Article #${index + 1}`;
                        }
                    });
                }

                function updateRemoveButtons() {
                    const items = container.querySelectorAll('.article-modification-item');
                    items.forEach((item, index) => {
                        const removeBtn = item.querySelector('.remove-article');
                        if (removeBtn) {
                            removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
                        }
                    });
                }

                updateRemoveButtons();
            }

            // =====================================================
            // Gestion dynamique des membres du bureau
            // =====================================================
            const bureauContainer = document.getElementById('bureau-members-container');
            const addBureauBtn = document.getElementById('add-bureau-member-btn');
            let bureauMemberIndex = 1;

            if (addBureauBtn && bureauContainer) {
                addBureauBtn.addEventListener('click', function () {
                    const newMember = document.createElement('div');
                    newMember.className = 'bureau-member-item border rounded p-3 mb-3 bg-light';
                    newMember.setAttribute('data-index', bureauMemberIndex);

                    newMember.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 text-warning">
                                        <i class="fas fa-user-tie me-2"></i>
                                        <span class="member-number">Membre #${bureauMemberIndex + 1}</span>
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-bureau-member">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Type de changement</label>
                                        <select class="form-select form-select-sm" name="bureau_membres[${bureauMemberIndex}][type_changement]">
                                            <option value="ajout">Nouveau membre</option>
                                            <option value="modification">Modification fonction</option>
                                            <option value="remplacement">Remplacement</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Fonction <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="bureau_membres[${bureauMemberIndex}][fonction]"
                                               placeholder="Ex: Président, Secrétaire général...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Civilité</label>
                                        <select class="form-select form-select-sm" name="bureau_membres[${bureauMemberIndex}][civilite]">
                                            <option value="">Sélectionnez...</option>
                                            <option value="M.">M.</option>
                                            <option value="Mme">Mme</option>
                                            <option value="Mlle">Mlle</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="bureau_membres[${bureauMemberIndex}][nom]" placeholder="Nom de famille">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="bureau_membres[${bureauMemberIndex}][prenom]" placeholder="Prénom(s)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Date de naissance</label>
                                        <input type="date" class="form-control form-control-sm" 
                                               name="bureau_membres[${bureauMemberIndex}][date_naissance]">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Téléphone</label>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="bureau_membres[${bureauMemberIndex}][telephone]" placeholder="+241...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control form-control-sm" 
                                               name="bureau_membres[${bureauMemberIndex}][email]" placeholder="email@exemple.com">
                                    </div>
                                </div>
                            `;

                    bureauContainer.appendChild(newMember);
                    bureauMemberIndex++;
                    updateBureauMemberNumbers();
                    updateBureauRemoveButtons();
                });

                // Suppression de membre
                bureauContainer.addEventListener('click', function (e) {
                    if (e.target.closest('.remove-bureau-member')) {
                        const item = e.target.closest('.bureau-member-item');
                        if (item) {
                            item.remove();
                            updateBureauMemberNumbers();
                            updateBureauRemoveButtons();
                        }
                    }
                });

                function updateBureauMemberNumbers() {
                    const items = bureauContainer.querySelectorAll('.bureau-member-item');
                    items.forEach((item, index) => {
                        const numberSpan = item.querySelector('.member-number');
                        if (numberSpan) {
                            numberSpan.textContent = `Membre #${index + 1}`;
                        }
                    });
                }

                function updateBureauRemoveButtons() {
                    const items = bureauContainer.querySelectorAll('.bureau-member-item');
                    items.forEach((item, index) => {
                        const removeBtn = item.querySelector('.remove-bureau-member');
                        if (removeBtn) {
                            removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
                        }
                    });
                }

                updateBureauRemoveButtons();
            }
        });
    </script>

    {{-- Cascades géolocalisation --}}
    <script>
        (function () {
            var provinceSel = document.getElementById('province_id');
            var departementSel = document.getElementById('departement_id');
            var communeSel = document.getElementById('commune_id');
            var arrondissementSel = document.getElementById('arrondissement_id');
            var quartierSel = document.getElementById('quartier_id');
            var cantonSel = document.getElementById('canton_id');
            var regroupementSel = document.getElementById('regroupement_id');
            var villageSel = document.getElementById('village_id');
            var zoneTypeSel = document.getElementById('zone_type');

            if (!provinceSel) return;

            function toggleZoneFields() {
                var zone = zoneTypeSel ? zoneTypeSel.value : '';
                document.querySelectorAll('.zone-urbaine-field').forEach(function (el) {
                    el.style.display = (zone === 'urbaine') ? '' : 'none';
                });
                document.querySelectorAll('.zone-rurale-field').forEach(function (el) {
                    el.style.display = (zone === 'rurale') ? '' : 'none';
                });
            }

            function fetchAndFill(select, url, currentId, currentText) {
                if (!select) return Promise.resolve();
                select.innerHTML = '<option value="">Chargement...</option>';
                select.disabled = true;
                return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        var items = res.data || res.items || res || [];
                        select.innerHTML = '<option value="">Sélectionnez...</option>';
                        items.forEach(function (item) {
                            var opt = document.createElement('option');
                            opt.value = item.id;
                            opt.textContent = item.nom || item.name || item.libelle || '';
                            if (currentId && String(currentId) === String(item.id)) opt.selected = true;
                            else if (!currentId && currentText && opt.textContent.toLowerCase() === String(currentText).toLowerCase()) opt.selected = true;
                            select.appendChild(opt);
                        });
                        select.disabled = false;
                    })
                    .catch(function () {
                        select.innerHTML = '<option value="">Erreur de chargement</option>';
                        select.disabled = false;
                    });
            }

            if (provinceSel) {
                provinceSel.addEventListener('change', function () {
                    [departementSel, communeSel, arrondissementSel, quartierSel, cantonSel, regroupementSel, villageSel].forEach(function (s) {
                        if (s) { s.innerHTML = '<option value="">--</option>'; s.disabled = true; }
                    });
                    if (this.value) {
                        fetchAndFill(departementSel, '/admin/api/geo/departements/' + this.value,
                            departementSel.dataset.currentId, departementSel.dataset.currentText);
                    }
                });
            }

            if (departementSel) {
                departementSel.addEventListener('change', function () {
                    [communeSel, arrondissementSel, quartierSel, cantonSel, regroupementSel, villageSel].forEach(function (s) {
                        if (s) { s.innerHTML = '<option value="">--</option>'; s.disabled = true; }
                    });
                    if (this.value) {
                        if (communeSel) fetchAndFill(communeSel, '/admin/api/geo/communes/' + this.value,
                            communeSel.dataset.currentId, communeSel.dataset.currentText);
                        if (cantonSel) fetchAndFill(cantonSel, '/admin/api/geo/cantons/' + this.value,
                            cantonSel.dataset.currentId, cantonSel.dataset.currentText);
                    }
                });
            }

            if (communeSel) {
                communeSel.addEventListener('change', function () {
                    [arrondissementSel, quartierSel].forEach(function (s) {
                        if (s) { s.innerHTML = '<option value="">--</option>'; s.disabled = true; }
                    });
                    if (this.value && arrondissementSel) {
                        fetchAndFill(arrondissementSel, '/admin/api/geo/arrondissements/' + this.value,
                            arrondissementSel.dataset.currentId, arrondissementSel.dataset.currentText);
                    }
                });
            }

            if (arrondissementSel) {
                arrondissementSel.addEventListener('change', function () {
                    if (quartierSel) { quartierSel.innerHTML = '<option value="">--</option>'; quartierSel.disabled = true; }
                    if (this.value && quartierSel) {
                        fetchAndFill(quartierSel, '/admin/api/geo/quartiers/' + this.value, null, quartierSel.dataset.currentText);
                    }
                });
            }

            if (cantonSel) {
                cantonSel.addEventListener('change', function () {
                    [regroupementSel, villageSel].forEach(function (s) {
                        if (s) { s.innerHTML = '<option value="">--</option>'; s.disabled = true; }
                    });
                    if (this.value && regroupementSel) {
                        fetchAndFill(regroupementSel, '/admin/api/geo/regroupements/' + this.value,
                            regroupementSel.dataset.currentId, regroupementSel.dataset.currentText);
                    }
                });
            }

            if (regroupementSel) {
                regroupementSel.addEventListener('change', function () {
                    if (villageSel) { villageSel.innerHTML = '<option value="">--</option>'; villageSel.disabled = true; }
                    if (this.value && villageSel) {
                        fetchAndFill(villageSel, '/admin/api/geo/villages/' + this.value, null, villageSel.dataset.currentText);
                    }
                });
            }

            if (zoneTypeSel) zoneTypeSel.addEventListener('change', toggleZoneFields);

            // Init : pré-remplir en cascade si déjà valeurs
            toggleZoneFields();
            if (provinceSel.value) {
                fetchAndFill(departementSel, '/admin/api/geo/departements/' + provinceSel.value,
                    departementSel.dataset.currentId, departementSel.dataset.currentText).then(function () {
                    if (departementSel.value) {
                        if (communeSel) fetchAndFill(communeSel, '/admin/api/geo/communes/' + departementSel.value,
                            communeSel.dataset.currentId, communeSel.dataset.currentText).then(function () {
                            if (communeSel && communeSel.value && arrondissementSel) {
                                fetchAndFill(arrondissementSel, '/admin/api/geo/arrondissements/' + communeSel.value,
                                    arrondissementSel.dataset.currentId, arrondissementSel.dataset.currentText).then(function () {
                                    if (arrondissementSel.value && quartierSel) {
                                        fetchAndFill(quartierSel, '/admin/api/geo/quartiers/' + arrondissementSel.value, null, quartierSel.dataset.currentText);
                                    }
                                });
                            }
                        });
                        if (cantonSel) fetchAndFill(cantonSel, '/admin/api/geo/cantons/' + departementSel.value,
                            cantonSel.dataset.currentId, cantonSel.dataset.currentText).then(function () {
                            if (cantonSel && cantonSel.value && regroupementSel) {
                                fetchAndFill(regroupementSel, '/admin/api/geo/regroupements/' + cantonSel.value,
                                    regroupementSel.dataset.currentId, regroupementSel.dataset.currentText).then(function () {
                                    if (regroupementSel.value && villageSel) {
                                        fetchAndFill(villageSel, '/admin/api/geo/villages/' + regroupementSel.value, null, villageSel.dataset.currentText);
                                    }
                                });
                            }
                        });
                    }
                });
            }
        })();
    </script>
@endsection