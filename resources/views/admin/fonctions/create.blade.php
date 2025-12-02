@extends('layouts.admin')

@section('title', 'Nouvelle Fonction')
@section('page-title', 'Créer une Fonction')

@push('styles')
<style>
:root {
    --gabon-green: #009e3f;
    --gabon-yellow: #ffcd00;
    --gabon-blue: #003f7f;
}

.form-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.form-header {
    background: linear-gradient(135deg, var(--gabon-green) 0%, #00b347 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 16px 16px 0 0;
}

.form-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-section-title {
    font-weight: 600;
    color: var(--gabon-blue);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--gabon-green);
}

.color-option {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.2s;
}

.color-option:hover {
    transform: scale(1.1);
}

.color-option.selected {
    border-color: #333;
    box-shadow: 0 0 0 2px white, 0 0 0 4px #333;
}

.icon-option {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid #e2e8f0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    background: white;
}

.icon-option:hover {
    border-color: var(--gabon-green);
    background: rgba(0, 158, 63, 0.1);
}

.icon-option.selected {
    border-color: var(--gabon-green);
    background: var(--gabon-green);
    color: white;
}

.preview-badge {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="card form-card">
                <div class="form-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="fas fa-plus-circle mr-2"></i>Nouvelle Fonction</h4>
                            <small class="opacity-75">Définir une fonction attribuable aux membres</small>
                        </div>
                        <a href="{{ route('admin.referentiels.fonctions.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left mr-2"></i>Retour
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.referentiels.fonctions.store') }}" method="POST">
                    @csrf
                    <div class="card-body p-4">

                        {{-- INFORMATIONS DE BASE --}}
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-info-circle mr-2"></i>Informations de base</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Nom de la fonction <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror" 
                                           value="{{ old('nom') }}" placeholder="Ex: Président" required>
                                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Forme féminine</label>
                                    <input type="text" name="nom_feminin" id="nom_feminin" class="form-control @error('nom_feminin') is-invalid @enderror" 
                                           value="{{ old('nom_feminin') }}" placeholder="Ex: Présidente">
                                    @error('nom_feminin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Code unique <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code') }}" placeholder="Ex: president">
                                    <small class="text-muted">Généré automatiquement si vide</small>
                                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Catégorie <span class="text-danger">*</span></label>
                                    <select name="categorie" class="form-control @error('categorie') is-invalid @enderror" required>
                                        @foreach($categories as $key => $label)
                                            <option value="{{ $key }}" {{ old('categorie') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="2" placeholder="Décrivez les responsabilités...">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- CONFIGURATION --}}
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-cogs mr-2"></i>Configuration</h6>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Ordre d'affichage <span class="text-danger">*</span></label>
                                    <input type="number" name="ordre" class="form-control @error('ordre') is-invalid @enderror" 
                                           value="{{ old('ordre', $maxOrdre + 1) }}" min="0" required>
                                    @error('ordre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Nombre max <span class="text-danger">*</span></label>
                                    <input type="number" name="nb_max" class="form-control @error('nb_max') is-invalid @enderror" 
                                           value="{{ old('nb_max', 1) }}" min="1" max="999" required>
                                    <small class="text-muted">Personnes pouvant occuper cette fonction</small>
                                    @error('nb_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_bureau" name="is_bureau" value="1" {{ old('is_bureau') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_bureau">
                                            <strong>Bureau exécutif</strong>
                                            <small class="d-block text-muted">Fait partie du bureau</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_obligatoire" name="is_obligatoire" value="1" {{ old('is_obligatoire') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_obligatoire">
                                            <strong>Obligatoire</strong>
                                            <small class="d-block text-muted">Requise pour chaque organisation</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_unique" name="is_unique" value="1" {{ old('is_unique', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_unique">
                                            <strong>Unique</strong>
                                            <small class="d-block text-muted">Une seule personne max</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- APPARENCE --}}
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-palette mr-2"></i>Apparence</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Icône</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($icones as $icon => $label)
                                            <div class="icon-option {{ old('icone') == $icon ? 'selected' : '' }}" 
                                                 data-icon="{{ $icon }}" title="{{ $label }}">
                                                <i class="fas {{ $icon }}"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="icone" id="icone" value="{{ old('icone', 'fa-user') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Couleur</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($couleurs as $hex => $label)
                                            <div class="color-option {{ old('couleur') == $hex ? 'selected' : '' }}" 
                                                 style="background-color: {{ $hex }};" 
                                                 data-color="{{ $hex }}" title="{{ $label }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="couleur" id="couleur" value="{{ old('couleur', '#009e3f') }}">
                                </div>
                            </div>

                            {{-- Prévisualisation --}}
                            <div class="mt-3">
                                <label class="form-label font-weight-bold">Prévisualisation</label>
                                <div>
                                    <span class="preview-badge" id="previewBadge" style="background-color: {{ old('couleur', '#009e3f') }}; color: white;">
                                        <i class="fas {{ old('icone', 'fa-user') }} mr-2" id="previewIcon"></i>
                                        <span id="previewNom">{{ old('nom', 'Nom de la fonction') }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- STATUT --}}
                        <div class="form-section">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    <strong>Fonction active</strong>
                                    <small class="d-block text-muted">Disponible pour attribution</small>
                                </label>
                            </div>
                        </div>

                        {{-- BOUTONS --}}
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="{{ route('admin.referentiels.fonctions.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times mr-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save mr-2"></i>Enregistrer
                            </button>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Générer code automatiquement
    $('#nom').on('blur', function() {
        if (!$('#code').val()) {
            var code = $(this).val()
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_|_$/g, '');
            $('#code').val(code);
        }
    });

    // Sélection icône
    $('.icon-option').on('click', function() {
        $('.icon-option').removeClass('selected');
        $(this).addClass('selected');
        var icon = $(this).data('icon');
        $('#icone').val(icon);
        $('#previewIcon').attr('class', 'fas ' + icon + ' mr-2');
    });

    // Sélection couleur
    $('.color-option').on('click', function() {
        $('.color-option').removeClass('selected');
        $(this).addClass('selected');
        var color = $(this).data('color');
        $('#couleur').val(color);
        $('#previewBadge').css('background-color', color);
    });

    // Mise à jour prévisualisation nom
    $('#nom').on('input', function() {
        $('#previewNom').text($(this).val() || 'Nom de la fonction');
    });

    // Initialiser prévisualisation
    if ($('#icone').val()) {
        $('.icon-option[data-icon="' + $('#icone').val() + '"]').addClass('selected');
    }
    if ($('#couleur').val()) {
        $('.color-option[data-color="' + $('#couleur').val() + '"]').addClass('selected');
    }
});
</script>
@endpush