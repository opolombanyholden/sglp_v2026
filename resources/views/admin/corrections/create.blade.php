@extends('layouts.admin')

@section('title', 'Correction — ' . $organisation->nom)

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1">
                    <i class="fas fa-pen-fancy me-2 text-warning"></i>
                    Correction administrative
                </h2>
                <p class="text-muted mb-0">Corriger les erreurs du dossier <code>{{ $dossier->numero_dossier }}</code></p>
            </div>
            <a href="{{ route('admin.corrections.select-organisation') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>

        <!-- Carte organisation -->
        <div class="card mb-4 border-warning">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar-circle bg-warning text-white">
                            {{ strtoupper(substr($organisation->sigle ?? $organisation->nom, 0, 2)) }}
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-0">{{ $organisation->nom }}</h5>
                        <small class="text-muted">
                            {{ $organisation->organisationType->libelle ?? $organisation->type ?? 'N/A' }} |
                            {{ $organisation->numero_recepisse ?? 'N/A' }}
                        </small>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-success fs-6">Approuvé</span>
                    </div>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
        @endif

        <!-- Formulaire -->
        <form id="correctionForm" action="{{ route('admin.corrections.store', $organisation) }}" method="POST">
            @csrf
            <input type="hidden" name="action" id="formAction" value="brouillon">

            <!-- Justification globale -->
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i> Justification de la correction</h5>
                </div>
                <div class="card-body">
                    <label for="motif_global" class="form-label">Motif global de la correction <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('motif_global') is-invalid @enderror" id="motif_global"
                              name="motif_global" rows="3" required
                              placeholder="Décrivez la raison de cette correction administrative...">{{ old('motif_global') }}</textarea>
                    @error('motif_global')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Section : Identité de l'organisation -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i> Identité de l'organisation</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="corr_nom" class="form-label">Nom de l'organisation</label>
                            <input type="text" class="form-control" id="corr_nom" name="champs[nom]"
                                   value="{{ old('champs.nom', $organisation->nom) }}">
                            <small class="form-text text-muted">Actuel : {{ $organisation->nom }}</small>
                        </div>
                        <div class="col-md-4">
                            <label for="corr_sigle" class="form-label">Sigle / Acronyme</label>
                            <input type="text" class="form-control" id="corr_sigle" name="champs[sigle]"
                                   value="{{ old('champs.sigle', $organisation->sigle) }}">
                            <small class="form-text text-muted">Actuel : {{ $organisation->sigle ?? '—' }}</small>
                        </div>
                        <div class="col-12">
                            <label for="corr_objet" class="form-label">Objet / But de l'organisation</label>
                            <textarea class="form-control" id="corr_objet" name="champs[objet]"
                                      rows="3">{{ old('champs.objet', $organisation->objet) }}</textarea>
                            @if($organisation->objet)
                                <small class="form-text text-muted">Actuel : {{ Str::limit($organisation->objet, 100) }}</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="corr_devise" class="form-label">Devise</label>
                            <input type="text" class="form-control" id="corr_devise" name="champs[devise]"
                                   value="{{ old('champs.devise', $organisation->devise) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="corr_date_creation" class="form-label">Date de création</label>
                            <input type="date" class="form-control" id="corr_date_creation" name="champs[date_creation]"
                                   value="{{ old('champs.date_creation', $organisation->date_creation ? \Carbon\Carbon::parse($organisation->date_creation)->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section : Localisation -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Localisation</h5>
                </div>
                <div class="card-body">
                    <!-- Hidden fields pour stocker les noms texte -->
                    <input type="hidden" name="champs[province]" id="province_nom" value="{{ $organisation->province }}">
                    <input type="hidden" name="champs[departement]" id="departement_nom" value="{{ $organisation->departement }}">
                    <input type="hidden" name="champs[ville_commune]" id="commune_nom" value="{{ $organisation->ville_commune }}">
                    <input type="hidden" name="champs[arrondissement]" id="arrondissement_nom" value="{{ $organisation->arrondissement }}">
                    <input type="hidden" name="champs[quartier]" id="quartier_nom" value="{{ $organisation->quartier }}">
                    <input type="hidden" name="champs[canton]" id="canton_nom" value="{{ $organisation->canton }}">
                    <input type="hidden" name="champs[regroupement]" id="regroupement_nom" value="{{ $organisation->regroupement }}">
                    <input type="hidden" name="champs[village]" id="village_nom" value="{{ $organisation->village }}">

                    <div class="row g-3">
                        <!-- Siège social -->
                        <div class="col-12">
                            <label class="form-label">Siège social (adresse complète)</label>
                            <input type="text" class="form-control" name="champs[siege_social]"
                                   value="{{ old('champs.siege_social', $organisation->siege_social) }}">
                            @if($organisation->siege_social)
                                <small class="form-text text-muted">Actuel : {{ $organisation->siege_social }}</small>
                            @endif
                        </div>

                        <!-- Type de zone -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Type de zone</label>
                            <div class="d-flex gap-4 mt-1">
                                <label class="zone-option d-flex align-items-center gap-2 p-2 border rounded {{ ($organisation->zone_type ?? 'urbaine') == 'urbaine' ? 'border-primary' : '' }}" style="cursor:pointer">
                                    <input type="radio" name="champs[zone_type]" value="urbaine" class="form-check-input zone-type-radio"
                                        {{ old('champs.zone_type', $organisation->zone_type ?? 'urbaine') == 'urbaine' ? 'checked' : '' }}>
                                    <i class="fas fa-city text-primary"></i>
                                    <div>
                                        <strong>Zone Urbaine</strong>
                                        <small class="d-block text-muted">Commune &rarr; Arrondissement &rarr; Quartier</small>
                                    </div>
                                </label>
                                <label class="zone-option d-flex align-items-center gap-2 p-2 border rounded {{ ($organisation->zone_type) == 'rurale' ? 'border-success' : '' }}" style="cursor:pointer">
                                    <input type="radio" name="champs[zone_type]" value="rurale" class="form-check-input zone-type-radio"
                                        {{ old('champs.zone_type', $organisation->zone_type) == 'rurale' ? 'checked' : '' }}>
                                    <i class="fas fa-tree text-success"></i>
                                    <div>
                                        <strong>Zone Rurale</strong>
                                        <small class="d-block text-muted">Canton &rarr; Regroupement &rarr; Village</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Province -->
                        <div class="col-md-6">
                            <label class="form-label">Province <span class="text-danger">*</span></label>
                            <select class="form-select" id="corr_province_id">
                                <option value="">Sélectionner une province...</option>
                                @foreach($provinces as $prov)
                                    <option value="{{ $prov->id }}" data-nom="{{ $prov->nom }}"
                                        {{ $organisation->province_ref_id == $prov->id ? 'selected' : '' }}>
                                        {{ $prov->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @if($organisation->province)
                                <small class="form-text text-muted">Actuel : {{ $organisation->province }}</small>
                            @endif
                        </div>

                        <!-- Département -->
                        <div class="col-md-6">
                            <label class="form-label">Département <span class="text-danger">*</span></label>
                            <select class="form-select" id="corr_departement_id" {{ $organisation->province_ref_id ? '' : 'disabled' }}>
                                <option value="">Sélectionner province d'abord...</option>
                            </select>
                            @if($organisation->departement)
                                <small class="form-text text-muted">Actuel : {{ $organisation->departement }}</small>
                            @endif
                        </div>

                        <!-- ========== ZONE URBAINE ========== -->
                        <div class="col-md-4 zone-urbaine-field">
                            <label class="form-label">Commune / Ville</label>
                            <select class="form-select" id="corr_commune_id" disabled>
                                <option value="">Sélectionner département...</option>
                            </select>
                            @if($organisation->ville_commune)
                                <small class="form-text text-muted">Actuel : {{ $organisation->ville_commune }}</small>
                            @endif
                        </div>
                        <div class="col-md-4 zone-urbaine-field">
                            <label class="form-label">Arrondissement</label>
                            <select class="form-select" id="corr_arrondissement_id" disabled>
                                <option value="">Sélectionner commune...</option>
                            </select>
                            @if($organisation->arrondissement)
                                <small class="form-text text-muted">Actuel : {{ $organisation->arrondissement }}</small>
                            @endif
                        </div>
                        <div class="col-md-4 zone-urbaine-field">
                            <label class="form-label">Quartier</label>
                            <select class="form-select" id="corr_quartier_id" disabled>
                                <option value="">Sélectionner arrondissement...</option>
                            </select>
                            @if($organisation->quartier)
                                <small class="form-text text-muted">Actuel : {{ $organisation->quartier }}</small>
                            @endif
                        </div>

                        <!-- ========== ZONE RURALE ========== -->
                        <div class="col-md-4 zone-rurale-field" style="display:none">
                            <label class="form-label">Canton</label>
                            <select class="form-select" id="corr_canton_id" disabled>
                                <option value="">Sélectionner département...</option>
                            </select>
                            @if($organisation->canton)
                                <small class="form-text text-muted">Actuel : {{ $organisation->canton }}</small>
                            @endif
                        </div>
                        <div class="col-md-4 zone-rurale-field" style="display:none">
                            <label class="form-label">Regroupement</label>
                            <select class="form-select" id="corr_regroupement_id" disabled>
                                <option value="">Sélectionner canton...</option>
                            </select>
                        </div>
                        <div class="col-md-4 zone-rurale-field" style="display:none">
                            <label class="form-label">Village</label>
                            <select class="form-select" id="corr_village_id" disabled>
                                <option value="">Sélectionner regroupement...</option>
                            </select>
                        </div>

                        <!-- Champs complémentaires -->
                        <div class="col-md-4">
                            <label class="form-label">Préfecture</label>
                            <input type="text" class="form-control" name="champs[prefecture]"
                                   value="{{ old('champs.prefecture', $organisation->prefecture) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sous-préfecture</label>
                            <input type="text" class="form-control" name="champs[sous_prefecture]"
                                   value="{{ old('champs.sous_prefecture', $organisation->sous_prefecture) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lieu-dit</label>
                            <input type="text" class="form-control" name="champs[lieu_dit]"
                                   value="{{ old('champs.lieu_dit', $organisation->lieu_dit) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section : Contacts -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-address-book me-2"></i> Coordonnées de contact</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="corr_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="corr_email" name="champs[email]"
                                   value="{{ old('champs.email', $organisation->email) }}">
                            @if($organisation->email)
                                <small class="form-text text-muted">Actuel : {{ $organisation->email }}</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="corr_telephone" class="form-label">Téléphone principal</label>
                            <input type="tel" class="form-control" id="corr_telephone" name="champs[telephone]"
                                   value="{{ old('champs.telephone', $organisation->telephone) }}">
                            @if($organisation->telephone)
                                <small class="form-text text-muted">Actuel : {{ $organisation->telephone }}</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="corr_telephone_secondaire" class="form-label">Téléphone secondaire</label>
                            <input type="tel" class="form-control" id="corr_telephone_secondaire" name="champs[telephone_secondaire]"
                                   value="{{ old('champs.telephone_secondaire', $organisation->telephone_secondaire) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="corr_site_web" class="form-label">Site web</label>
                            <input type="url" class="form-control" id="corr_site_web" name="champs[site_web]"
                                   value="{{ old('champs.site_web', $organisation->site_web) }}" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section : Membres du bureau -->
            <div class="card mb-4">
                <div class="card-header bg-warning bg-opacity-25">
                    <h5 class="mb-0"><i class="fas fa-users-cog me-2"></i> Membres du bureau</h5>
                </div>
                <div class="card-body">
                    @if($organisation->membresBureau && $organisation->membresBureau->count() > 0)
                        <h6 class="text-secondary mb-3"><i class="fas fa-users me-2"></i>Composition actuelle</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fonction</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Contact</th>
                                        <th>NIP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($organisation->membresBureau as $membre)
                                        <tr>
                                            <td><strong>{{ $membre->fonction }}</strong></td>
                                            <td>{{ $membre->nom }}</td>
                                            <td>{{ $membre->prenom }}</td>
                                            <td>{{ $membre->contact ?? '—' }}</td>
                                            <td>{{ $membre->nip ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h6 class="text-warning mb-3"><i class="fas fa-edit me-2"></i>Corrections des membres</h6>
                        @foreach($organisation->membresBureau as $index => $membre)
                            <div class="border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 text-warning">
                                        <i class="fas fa-user-tie me-2"></i>
                                        {{ $membre->fonction }} — {{ $membre->prenom }} {{ $membre->nom }}
                                    </h6>
                                    <div class="form-check">
                                        <input class="form-check-input toggle-correction" type="checkbox"
                                               id="toggle_mb_{{ $membre->id }}" data-target="mb_fields_{{ $membre->id }}">
                                        <label class="form-check-label text-primary" for="toggle_mb_{{ $membre->id }}">
                                            Corriger
                                        </label>
                                    </div>
                                </div>
                                <div class="row g-2 correction-fields d-none" id="mb_fields_{{ $membre->id }}">
                                    <div class="col-md-4">
                                        <label class="form-label small">NIP <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm nip-input"
                                                   name="membres_bureau[{{ $membre->id }}][nip]"
                                                   value="{{ $membre->nip }}"
                                                   placeholder="A1-2345-19901225"
                                                   pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}" maxlength="16" disabled>
                                            <span class="input-group-text nip-status"></span>
                                        </div>
                                        <small class="form-text text-muted">Format : XX-0000-AAAAMMJJ</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="membres_bureau[{{ $membre->id }}][nom]"
                                               value="{{ $membre->nom }}" disabled>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="membres_bureau[{{ $membre->id }}][prenom]"
                                               value="{{ $membre->prenom }}" disabled>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Fonction <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm corr-fonction-select"
                                                name="membres_bureau[{{ $membre->id }}][fonction]" disabled>
                                            <option value="{{ $membre->fonction }}" selected>{{ $membre->fonction }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Contact</label>
                                        <input type="tel" class="form-control form-control-sm"
                                               name="membres_bureau[{{ $membre->id }}][contact]"
                                               value="{{ $membre->contact }}"
                                               placeholder="+241..." disabled>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Domicile</label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="membres_bureau[{{ $membre->id }}][domicile]"
                                               value="{{ $membre->domicile }}" disabled>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small text-danger">Motif de correction <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="membres_bureau[{{ $membre->id }}][motif]"
                                               placeholder="Raison de cette correction..." disabled>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>Aucun membre du bureau enregistré.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section : Fondateurs -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i> Fondateurs</h5>
                </div>
                <div class="card-body">
                    @if($organisation->fondateurs && $organisation->fondateurs->count() > 0)
                        <h6 class="text-secondary mb-3"><i class="fas fa-users me-2"></i>Liste actuelle</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Nationalité</th>
                                        <th>NIP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($organisation->fondateurs as $fondateur)
                                        <tr>
                                            <td>{{ $fondateur->nom }}</td>
                                            <td>{{ $fondateur->prenom }}</td>
                                            <td>{{ $fondateur->nationalite ?? '—' }}</td>
                                            <td>{{ $fondateur->nip ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h6 class="text-primary mb-3"><i class="fas fa-edit me-2"></i>Corrections des fondateurs</h6>
                        @foreach($organisation->fondateurs as $fondateur)
                            <div class="border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        {{ $fondateur->prenom }} {{ $fondateur->nom }}
                                    </h6>
                                    <div class="form-check">
                                        <input class="form-check-input toggle-correction" type="checkbox"
                                               id="toggle_f_{{ $fondateur->id }}" data-target="f_fields_{{ $fondateur->id }}">
                                        <label class="form-check-label text-primary" for="toggle_f_{{ $fondateur->id }}">
                                            Corriger
                                        </label>
                                    </div>
                                </div>
                                <div class="row g-2 correction-fields d-none" id="f_fields_{{ $fondateur->id }}">
                                    <div class="col-md-2">
                                        <label class="form-label small">Civilité</label>
                                        <select class="form-select form-select-sm"
                                                name="fondateurs[{{ $fondateur->id }}][civilite]" disabled>
                                            <option value="M" {{ ($fondateur->civilite ?? '') == 'M' ? 'selected' : '' }}>M.</option>
                                            <option value="Mme" {{ ($fondateur->civilite ?? '') == 'Mme' ? 'selected' : '' }}>Mme</option>
                                            <option value="Mlle" {{ ($fondateur->civilite ?? '') == 'Mlle' ? 'selected' : '' }}>Mlle</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">NIP <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm nip-input"
                                                   name="fondateurs[{{ $fondateur->id }}][nip]"
                                                   value="{{ $fondateur->nip }}"
                                                   placeholder="A1-2345-19901225"
                                                   pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}" maxlength="16" disabled>
                                            <span class="input-group-text nip-status"></span>
                                        </div>
                                        <small class="form-text text-muted">Format : XX-0000-AAAAMMJJ</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="fondateurs[{{ $fondateur->id }}][nom]"
                                               value="{{ $fondateur->nom }}" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="fondateurs[{{ $fondateur->id }}][prenom]"
                                               value="{{ $fondateur->prenom }}" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Fonction</label>
                                        <select class="form-select form-select-sm corr-fonction-select"
                                                name="fondateurs[{{ $fondateur->id }}][fonction]" disabled>
                                            <option value="{{ $fondateur->fonction }}" selected>{{ $fondateur->fonction }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Date de naissance</label>
                                        <input type="date" class="form-control form-control-sm"
                                               name="fondateurs[{{ $fondateur->id }}][date_naissance]"
                                               value="{{ $fondateur->date_naissance ? \Carbon\Carbon::parse($fondateur->date_naissance)->format('Y-m-d') : '' }}" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Lieu de naissance</label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="fondateurs[{{ $fondateur->id }}][lieu_naissance]"
                                               value="{{ $fondateur->lieu_naissance }}" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Nationalité</label>
                                        <select class="form-select form-select-sm"
                                                name="fondateurs[{{ $fondateur->id }}][nationalite]" disabled>
                                            @php
                                                $nationalites = ['Gabonaise','Camerounaise','Congolaise','Equato-Guinéenne','Tchadienne','Centrafricaine','Sénégalaise','Malienne','Béninoise','Togolaise','Ivoirienne','Burkinabè','Nigérienne','Nigériane','Française','Autre'];
                                            @endphp
                                            @foreach($nationalites as $nat)
                                                <option value="{{ $nat }}" {{ ($fondateur->nationalite ?? 'Gabonaise') == $nat ? 'selected' : '' }}>{{ $nat }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small text-danger">Motif de correction <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                               name="fondateurs[{{ $fondateur->id }}][motif]"
                                               placeholder="Raison de cette correction..." disabled>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>Aucun fondateur enregistré.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Container pour les corrections JSON -->
            <div id="corrections-container"></div>

            <!-- Boutons d'action -->
            <div class="card mb-4">
                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('admin.corrections.select-organisation') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Annuler
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-warning" onclick="submitCorrection('brouillon')">
                            <i class="fas fa-save me-1"></i> Enregistrer Brouillon
                        </button>
                        <button type="button" class="btn btn-success" onclick="submitCorrection('soumettre')">
                            <i class="fas fa-paper-plane me-1"></i> Soumettre pour validation
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
    </style>

    <script>
    var geoBaseUrl = '{{ url("admin/api/geo") }}';

    document.addEventListener('DOMContentLoaded', function() {
        // =====================================================
        // Toggle corrections pour membres bureau et fondateurs
        // =====================================================
        document.querySelectorAll('.toggle-correction').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                var target = document.getElementById(this.dataset.target);
                if (!target) return;
                var inputs = target.querySelectorAll('input, select, textarea');
                if (this.checked) {
                    target.classList.remove('d-none');
                    inputs.forEach(function(i) { i.disabled = false; });
                } else {
                    target.classList.add('d-none');
                    inputs.forEach(function(i) { i.disabled = true; });
                }
            });
        });

        // =====================================================
        // GÉOLOCALISATION — Menus déroulants en cascade
        // =====================================================

        function resetSelect(id, placeholder) {
            var select = document.getElementById(id);
            if (select) {
                select.innerHTML = '<option value="">' + placeholder + '</option>';
                select.disabled = true;
            }
        }

        // --- Zone type toggle ---
        document.querySelectorAll('.zone-type-radio').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var isUrbaine = this.value === 'urbaine';
                document.querySelectorAll('.zone-urbaine-field').forEach(function(el) {
                    el.style.display = isUrbaine ? '' : 'none';
                });
                document.querySelectorAll('.zone-rurale-field').forEach(function(el) {
                    el.style.display = isUrbaine ? 'none' : '';
                });

                if (isUrbaine) {
                    resetSelect('corr_canton_id', 'Sélectionner département...');
                    resetSelect('corr_regroupement_id', 'Sélectionner canton...');
                    resetSelect('corr_village_id', 'Sélectionner regroupement...');
                    document.getElementById('canton_nom').value = '';
                    document.getElementById('regroupement_nom').value = '';
                    document.getElementById('village_nom').value = '';
                } else {
                    resetSelect('corr_commune_id', 'Sélectionner département...');
                    resetSelect('corr_arrondissement_id', 'Sélectionner commune...');
                    resetSelect('corr_quartier_id', 'Sélectionner arrondissement...');
                    document.getElementById('commune_nom').value = '';
                    document.getElementById('arrondissement_nom').value = '';
                    document.getElementById('quartier_nom').value = '';
                }

                var deptId = document.getElementById('corr_departement_id').value;
                if (deptId) {
                    if (isUrbaine) { loadCommunes(deptId); } else { loadCantons(deptId); }
                }
            });
        });

        // --- Province → Département ---
        document.getElementById('corr_province_id').addEventListener('change', function() {
            var id = this.value;
            var opt = this.options[this.selectedIndex];
            document.getElementById('province_nom').value = opt.getAttribute('data-nom') || opt.text || '';

            resetSelect('corr_departement_id', 'Chargement...');
            resetSelect('corr_commune_id', 'Sélectionner département...');
            resetSelect('corr_arrondissement_id', 'Sélectionner commune...');
            resetSelect('corr_quartier_id', 'Sélectionner arrondissement...');
            resetSelect('corr_canton_id', 'Sélectionner département...');
            resetSelect('corr_regroupement_id', 'Sélectionner canton...');
            resetSelect('corr_village_id', 'Sélectionner regroupement...');
            document.getElementById('departement_nom').value = '';
            document.getElementById('commune_nom').value = '';
            document.getElementById('arrondissement_nom').value = '';
            document.getElementById('quartier_nom').value = '';
            document.getElementById('canton_nom').value = '';
            document.getElementById('regroupement_nom').value = '';
            document.getElementById('village_nom').value = '';

            if (id) {
                fetch(geoBaseUrl + '/departements/' + id)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var dept = document.getElementById('corr_departement_id');
                            var html = '<option value="">Sélectionner...</option>';
                            data.data.forEach(function(d) {
                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                            });
                            dept.innerHTML = html;
                            dept.disabled = false;
                        }
                    });
            }
        });

        // --- Département → Commune ou Canton ---
        document.getElementById('corr_departement_id').addEventListener('change', function() {
            var id = this.value;
            var opt = this.options[this.selectedIndex];
            document.getElementById('departement_nom').value = opt.getAttribute('data-nom') || opt.text || '';
            var isUrbaine = document.querySelector('.zone-type-radio:checked').value === 'urbaine';
            if (id) {
                if (isUrbaine) { loadCommunes(id); } else { loadCantons(id); }
            }
        });

        // --- URBAIN : Commune → Arrondissement → Quartier ---
        function loadCommunes(deptId) {
            resetSelect('corr_commune_id', 'Chargement...');
            resetSelect('corr_arrondissement_id', 'Sélectionner commune...');
            resetSelect('corr_quartier_id', 'Sélectionner arrondissement...');
            document.getElementById('commune_nom').value = '';
            document.getElementById('arrondissement_nom').value = '';
            document.getElementById('quartier_nom').value = '';

            fetch(geoBaseUrl + '/communes/' + deptId)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        var sel = document.getElementById('corr_commune_id');
                        var html = '<option value="">(Optionnel)</option>';
                        data.data.forEach(function(d) {
                            html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                        });
                        sel.innerHTML = html;
                        sel.disabled = false;
                    }
                });
        }

        document.getElementById('corr_commune_id').addEventListener('change', function() {
            var id = this.value;
            var opt = this.options[this.selectedIndex];
            document.getElementById('commune_nom').value = opt.getAttribute('data-nom') || opt.text || '';
            resetSelect('corr_arrondissement_id', 'Chargement...');
            resetSelect('corr_quartier_id', 'Sélectionner arrondissement...');
            document.getElementById('arrondissement_nom').value = '';
            document.getElementById('quartier_nom').value = '';

            if (id) {
                fetch(geoBaseUrl + '/arrondissements/' + id)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var sel = document.getElementById('corr_arrondissement_id');
                            var html = '<option value="">(Optionnel)</option>';
                            data.data.forEach(function(d) {
                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                            });
                            sel.innerHTML = html;
                            sel.disabled = false;
                        }
                    });
            }
        });

        document.getElementById('corr_arrondissement_id').addEventListener('change', function() {
            var id = this.value;
            var opt = this.options[this.selectedIndex];
            document.getElementById('arrondissement_nom').value = opt.getAttribute('data-nom') || opt.text || '';
            resetSelect('corr_quartier_id', 'Chargement...');
            document.getElementById('quartier_nom').value = '';

            if (id) {
                fetch(geoBaseUrl + '/quartiers/' + id)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var sel = document.getElementById('corr_quartier_id');
                            var html = '<option value="">(Optionnel)</option>';
                            data.data.forEach(function(d) {
                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                            });
                            sel.innerHTML = html;
                            sel.disabled = false;
                        }
                    });
            }
        });

        document.getElementById('corr_quartier_id').addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('quartier_nom').value = opt.getAttribute('data-nom') || opt.text || '';
        });

        // --- RURAL : Canton → Regroupement → Village ---
        function loadCantons(deptId) {
            resetSelect('corr_canton_id', 'Chargement...');
            resetSelect('corr_regroupement_id', 'Sélectionner canton...');
            resetSelect('corr_village_id', 'Sélectionner regroupement...');
            document.getElementById('canton_nom').value = '';
            document.getElementById('regroupement_nom').value = '';
            document.getElementById('village_nom').value = '';

            fetch(geoBaseUrl + '/cantons/' + deptId)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        var sel = document.getElementById('corr_canton_id');
                        var html = '<option value="">(Optionnel)</option>';
                        data.data.forEach(function(d) {
                            html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                        });
                        sel.innerHTML = html;
                        sel.disabled = false;
                    }
                });
        }

        document.getElementById('corr_canton_id').addEventListener('change', function() {
            var id = this.value;
            var opt = this.options[this.selectedIndex];
            document.getElementById('canton_nom').value = opt.getAttribute('data-nom') || opt.text || '';
            resetSelect('corr_regroupement_id', 'Chargement...');
            resetSelect('corr_village_id', 'Sélectionner regroupement...');
            document.getElementById('regroupement_nom').value = '';
            document.getElementById('village_nom').value = '';

            if (id) {
                fetch(geoBaseUrl + '/regroupements/' + id)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var sel = document.getElementById('corr_regroupement_id');
                            var html = '<option value="">(Optionnel)</option>';
                            data.data.forEach(function(d) {
                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                            });
                            sel.innerHTML = html;
                            sel.disabled = false;
                        }
                    });
            }
        });

        document.getElementById('corr_regroupement_id').addEventListener('change', function() {
            var id = this.value;
            var opt = this.options[this.selectedIndex];
            document.getElementById('regroupement_nom').value = opt.getAttribute('data-nom') || opt.text || '';
            resetSelect('corr_village_id', 'Chargement...');
            document.getElementById('village_nom').value = '';

            if (id) {
                fetch(geoBaseUrl + '/villages/' + id)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var sel = document.getElementById('corr_village_id');
                            var html = '<option value="">(Optionnel)</option>';
                            data.data.forEach(function(d) {
                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                            });
                            sel.innerHTML = html;
                            sel.disabled = false;
                        }
                    });
            }
        });

        document.getElementById('corr_village_id').addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('village_nom').value = opt.getAttribute('data-nom') || opt.text || '';
        });

        // --- Initialisation zone urbaine/rurale ---
        (function() {
            var zoneType = document.querySelector('.zone-type-radio:checked');
            if (zoneType) {
                var isUrbaine = zoneType.value === 'urbaine';
                document.querySelectorAll('.zone-urbaine-field').forEach(function(el) {
                    el.style.display = isUrbaine ? '' : 'none';
                });
                document.querySelectorAll('.zone-rurale-field').forEach(function(el) {
                    el.style.display = isUrbaine ? 'none' : '';
                });
            }

            // Charger automatiquement les départements si province déjà sélectionnée
            var provinceSelect = document.getElementById('corr_province_id');
            if (provinceSelect.value) {
                provinceSelect.dispatchEvent(new Event('change'));
            }
        })();

        // =====================================================
        // Validation NIP en temps réel (format XX-0000-AAAAMMJJ)
        // =====================================================
        document.addEventListener('input', function(e) {
            if (!e.target.classList.contains('nip-input')) return;
            var val = e.target.value.toUpperCase();
            // Auto-formatter : insérer les tirets
            val = val.replace(/[^A-Z0-9\-]/g, '');
            if (val.length === 2 && val.indexOf('-') === -1) val += '-';
            if (val.length === 7 && val.charAt(6) !== '-') val = val.substring(0, 7) + '-' + val.substring(7);
            e.target.value = val;

            var statusSpan = e.target.parentElement.querySelector('.nip-status');
            if (!statusSpan) return;
            var nipRegex = /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/;

            if (val.length === 0) {
                statusSpan.innerHTML = '';
                e.target.classList.remove('is-valid', 'is-invalid');
            } else if (nipRegex.test(val)) {
                statusSpan.innerHTML = '<i class="fas fa-check text-success"></i>';
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else {
                statusSpan.innerHTML = '<i class="fas fa-times text-danger"></i>';
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            }
        });

        // =====================================================
        // Chargement dynamique des fonctions
        // =====================================================
        loadCorrectionFonctions();

        function loadCorrectionFonctions() {
            var apiUrl = geoBaseUrl.replace('/api/geo', '/api/fonctions') + '?grouped=1';

            fetch(apiUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(response) {
                var html = '<option value="">Sélectionner une fonction...</option>';
                if (response.success && response.data) {
                    var categories = [
                        { key: 'bureau', label: 'Bureau Exécutif' },
                        { key: 'commission', label: 'Commission' },
                        { key: 'membre', label: 'Membres' }
                    ];
                    categories.forEach(function(cat) {
                        var items = response.data[cat.key];
                        if (items && items.length > 0) {
                            html += '<optgroup label="' + cat.label + '">';
                            items.forEach(function(f) {
                                var nom = f.nom || f.name || f;
                                html += '<option value="' + nom + '">' + nom + '</option>';
                            });
                            html += '</optgroup>';
                        }
                    });
                }

                // Appliquer à tous les selects de fonction
                document.querySelectorAll('.corr-fonction-select').forEach(function(sel) {
                    var currentVal = sel.value;
                    sel.innerHTML = html;
                    // Restaurer la valeur actuelle
                    if (currentVal) {
                        var found = false;
                        for (var i = 0; i < sel.options.length; i++) {
                            if (sel.options[i].value === currentVal) {
                                sel.options[i].selected = true;
                                found = true;
                                break;
                            }
                        }
                        // Si la fonction n'est pas dans la liste, l'ajouter
                        if (!found) {
                            var opt = document.createElement('option');
                            opt.value = currentVal;
                            opt.text = currentVal;
                            opt.selected = true;
                            sel.insertBefore(opt, sel.options[1]);
                        }
                    }
                });
            })
            .catch(function(err) {
                console.warn('Fonctions API indisponible, fallback statique:', err.message);
                var html = '<option value="">Sélectionner...</option>';
                html += '<optgroup label="Bureau Exécutif">';
                var fonctions = ['Président','Vice-Président','Secrétaire Général','Secrétaire Général Adjoint','Trésorier Général','Trésorier Adjoint'];
                fonctions.forEach(function(f) { html += '<option value="' + f + '">' + f + '</option>'; });
                html += '</optgroup>';
                html += '<optgroup label="Commission">';
                html += '<option value="Commissaire aux Comptes">Commissaire aux Comptes</option>';
                html += '</optgroup>';
                html += '<optgroup label="Membres">';
                html += '<option value="Membre Fondateur">Membre Fondateur</option>';
                html += '<option value="Membre">Membre</option>';
                html += '</optgroup>';

                document.querySelectorAll('.corr-fonction-select').forEach(function(sel) {
                    var currentVal = sel.value;
                    sel.innerHTML = html;
                    if (currentVal) {
                        for (var i = 0; i < sel.options.length; i++) {
                            if (sel.options[i].value === currentVal) { sel.options[i].selected = true; break; }
                        }
                    }
                });
            });
        }
    });

    // =====================================================
    // Valeurs originales pour détection des modifications
    // =====================================================
    var originalValues = {!! json_encode([
        'nom' => $organisation->nom,
        'sigle' => $organisation->sigle,
        'objet' => $organisation->objet,
        'devise' => $organisation->devise,
        'date_creation' => $organisation->date_creation ? \Carbon\Carbon::parse($organisation->date_creation)->format('Y-m-d') : '',
        'siege_social' => $organisation->siege_social,
        'province' => $organisation->province,
        'departement' => $organisation->departement,
        'ville_commune' => $organisation->ville_commune,
        'arrondissement' => $organisation->arrondissement,
        'quartier' => $organisation->quartier,
        'canton' => $organisation->canton,
        'regroupement' => $organisation->regroupement,
        'village' => $organisation->village,
        'zone_type' => $organisation->zone_type,
        'prefecture' => $organisation->prefecture,
        'sous_prefecture' => $organisation->sous_prefecture,
        'lieu_dit' => $organisation->lieu_dit,
        'email' => $organisation->email,
        'telephone' => $organisation->telephone,
        'telephone_secondaire' => $organisation->telephone_secondaire,
        'site_web' => $organisation->site_web,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

    // =====================================================
    // Soumission du formulaire
    // =====================================================
    function submitCorrection(action) {
        var container = document.getElementById('corrections-container');
        container.innerHTML = '';
        var index = 0;
        var hasCorrection = false;

        // 1. Champs organisation modifiés (inputs texte, selects, textareas, hidden, radio)
        var orgFields = document.querySelectorAll('input[name^="champs["], select[name^="champs["], textarea[name^="champs["]');
        var processedFields = {};

        orgFields.forEach(function(input) {
            var match = input.name.match(/champs\[(.+)\]/);
            if (!match) return;
            var fieldName = match[1];

            // Pour les radios, ne prendre que le checked
            if (input.type === 'radio' && !input.checked) return;
            // Éviter les doublons
            if (processedFields[fieldName]) return;
            processedFields[fieldName] = true;

            var newVal = input.value ? input.value.trim() : '';
            var oldVal = (originalValues[fieldName] || '').toString().trim();

            if (newVal !== oldVal) {
                addHidden('corrections[' + index + '][champ]', fieldName);
                addHidden('corrections[' + index + '][categorie]', 'organisation');
                addHidden('corrections[' + index + '][nouvelle_valeur]', newVal);
                addHidden('corrections[' + index + '][motif]', 'Correction du champ ' + fieldName);
                index++;
                hasCorrection = true;
            }
        });

        // 2. Membres bureau cochés
        document.querySelectorAll('[id^="toggle_mb_"]:checked').forEach(function(toggle) {
            var membreId = toggle.id.replace('toggle_mb_', '');
            var fieldsDiv = document.getElementById('mb_fields_' + membreId);
            var motifInput = fieldsDiv.querySelector('[name$="[motif]"]');
            var motif = motifInput ? motifInput.value.trim() : '';

            if (!motif) {
                motifInput.classList.add('is-invalid');
                return;
            }

            // Collecter inputs et selects (sauf motif), n'envoyer que les non-vides
            fieldsDiv.querySelectorAll('input:not([name$="[motif]"]), select').forEach(function(field) {
                var champ = field.name.match(/\[(\w+)\]$/);
                if (!champ) return;
                var val = field.value.trim();
                if (!val) return; // Ignorer les champs vides
                addHidden('corrections[' + index + '][champ]', champ[1]);
                addHidden('corrections[' + index + '][categorie]', 'membre_bureau');
                addHidden('corrections[' + index + '][nouvelle_valeur]', val);
                addHidden('corrections[' + index + '][motif]', motif);
                addHidden('corrections[' + index + '][entity_id]', membreId);
                index++;
                hasCorrection = true;
            });
        });

        // 3. Fondateurs cochés
        document.querySelectorAll('[id^="toggle_f_"]:checked').forEach(function(toggle) {
            var fondateurId = toggle.id.replace('toggle_f_', '');
            var fieldsDiv = document.getElementById('f_fields_' + fondateurId);
            var motifInput = fieldsDiv.querySelector('[name$="[motif]"]');
            var motif = motifInput ? motifInput.value.trim() : '';

            if (!motif) {
                motifInput.classList.add('is-invalid');
                return;
            }

            // Collecter inputs et selects (sauf motif), n'envoyer que les non-vides
            fieldsDiv.querySelectorAll('input:not([name$="[motif]"]), select').forEach(function(field) {
                var champ = field.name.match(/\[(\w+)\]$/);
                if (!champ) return;
                var val = field.value.trim();
                if (!val) return; // Ignorer les champs vides
                addHidden('corrections[' + index + '][champ]', champ[1]);
                addHidden('corrections[' + index + '][categorie]', 'fondateur');
                addHidden('corrections[' + index + '][nouvelle_valeur]', val);
                addHidden('corrections[' + index + '][motif]', motif);
                addHidden('corrections[' + index + '][entity_id]', fondateurId);
                index++;
                hasCorrection = true;
            });
        });

        if (!hasCorrection) {
            alert('Aucune modification détectée. Modifiez au moins un champ pour créer une correction.');
            return;
        }

        document.getElementById('formAction').value = action;
        document.getElementById('correctionForm').submit();

        function addHidden(name, value) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            container.appendChild(input);
        }
    }
    </script>
@endsection
