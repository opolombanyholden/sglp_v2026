@extends('layouts.admin')

@section('title', 'Modifier un domaine d\'activité')
@section('page-title', 'Modifier un domaine d\'activité')

@push('styles')
<style>
:root { --gabon-green: #009e3f; --gabon-blue: #003f7f; }
.form-card { border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
.form-header {
    background: linear-gradient(135deg, var(--gabon-blue) 0%, #0056b3 100%);
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
                            <h4 class="mb-1"><i class="fas fa-edit mr-2"></i>Modifier le domaine</h4>
                            <small class="opacity-75">{{ $domaineActivite->nom }}</small>
                        </div>
                        <a href="{{ route('admin.referentiels.domaines-activite.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left mr-2"></i>Retour
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.referentiels.domaines-activite.update', $domaineActivite) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="card-body p-4">

                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-info-circle mr-2"></i>Informations</h6>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label font-weight-bold">Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" id="nom"
                                           class="form-control @error('nom') is-invalid @enderror"
                                           value="{{ old('nom', $domaineActivite->nom) }}" required>
                                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Ordre <span class="text-danger">*</span></label>
                                    <input type="number" name="ordre"
                                           class="form-control @error('ordre') is-invalid @enderror"
                                           value="{{ old('ordre', $domaineActivite->ordre) }}" min="0" required>
                                    @error('ordre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Code unique</label>
                                <input type="text" name="code" id="code"
                                       class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code', $domaineActivite->code) }}">
                                <small class="text-muted">Laisser vide pour régénérer automatiquement</small>
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Description</label>
                                <textarea name="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="3">{{ old('description', $domaineActivite->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $domaineActivite->is_active) ? 'checked' : '' }}>
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
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                            </button>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
