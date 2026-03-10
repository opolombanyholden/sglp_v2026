@extends('layouts.admin')

@section('title', 'Nouveau domaine d\'activité')
@section('page-title', 'Créer un domaine d\'activité')

@push('styles')
<style>
:root { --gabon-green: #009e3f; --gabon-blue: #003f7f; }
.form-card { border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
.form-header {
    background: linear-gradient(135deg, var(--gabon-green) 0%, #00b347 100%);
    color: white; padding: 1.5rem 2rem; border-radius: 16px 16px 0 0;
}
.form-section { background: #f8f9fa; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
.form-section-title {
    font-weight: 600; color: var(--gabon-blue); margin-bottom: 1rem;
    padding-bottom: 0.5rem; border-bottom: 2px solid var(--gabon-green);
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="card form-card">
                <div class="form-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="fas fa-plus-circle mr-2"></i>Nouveau domaine d'activité</h4>
                            <small class="opacity-75">Définir un domaine d'activité pour les organisations</small>
                        </div>
                        <a href="{{ route('admin.referentiels.domaines-activite.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left mr-2"></i>Retour
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.referentiels.domaines-activite.store') }}" method="POST">
                    @csrf
                    <div class="card-body p-4">

                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-info-circle mr-2"></i>Informations</h6>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label font-weight-bold">Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" id="nom"
                                           class="form-control @error('nom') is-invalid @enderror"
                                           value="{{ old('nom') }}" placeholder="Ex: Agriculture" required>
                                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Ordre d'affichage <span class="text-danger">*</span></label>
                                    <input type="number" name="ordre"
                                           class="form-control @error('ordre') is-invalid @enderror"
                                           value="{{ old('ordre', $maxOrdre + 1) }}" min="0" required>
                                    @error('ordre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Code unique</label>
                                <input type="text" name="code" id="code"
                                       class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code') }}" placeholder="Ex: agriculture">
                                <small class="text-muted">Généré automatiquement si vide (lettres minuscules et underscores)</small>
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Description</label>
                                <textarea name="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="3" placeholder="Décrivez ce domaine d'activité...">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    <strong>Domaine actif</strong>
                                    <small class="d-block text-muted">Disponible lors de la création d'organisations</small>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="{{ route('admin.referentiels.domaines-activite.index') }}" class="btn btn-outline-secondary btn-lg">
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
});
</script>
@endpush
