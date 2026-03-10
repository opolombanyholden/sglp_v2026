@extends('layouts.admin')
@section('title', isset($guide->id) ? 'Modifier le guide' : 'Nouveau guide')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
            <i class="fas fa-book mr-2" style="color:#009e3f;"></i>
            {{ isset($guide->id) ? 'Modifier le guide' : 'Nouveau guide' }}
        </h1>
        <a href="{{ route('admin.portail.guides.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST"
          action="{{ isset($guide->id) ? route('admin.portail.guides.update', $guide) : route('admin.portail.guides.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if(isset($guide->id)) @method('PUT') @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Titre <span class="text-danger">*</span></label>
                            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                                   value="{{ old('titre', $guide->titre ?? '') }}" required>
                            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $guide->description ?? '') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Catégorie <span class="text-danger">*</span></label>
                                    <input type="text" name="categorie" class="form-control" required
                                           value="{{ old('categorie', $guide->categorie ?? '') }}" list="cat-list">
                                    <datalist id="cat-list">
                                        <option>Général</option><option>Association</option>
                                        <option>ONG</option><option>Parti politique</option>
                                        <option>Confession religieuse</option>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Nombre de pages</label>
                                    <input type="number" name="nombre_pages" class="form-control" min="0"
                                           value="{{ old('nombre_pages', $guide->nombre_pages ?? 0) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Ordre</label>
                                    <input type="number" name="ordre" class="form-control" min="0"
                                           value="{{ old('ordre', $guide->ordre ?? 0) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Fichier PDF</label>
                            @if(!empty($guide->chemin_fichier))
                                <p class="mb-1"><small class="text-muted">Fichier actuel : {{ basename($guide->chemin_fichier) }}</small></p>
                            @endif
                            <input type="file" name="fichier" class="form-control-file" accept=".pdf">
                            <small class="text-muted">Max 30 MB. Laisser vide pour conserver le fichier actuel.</small>
                        </div>
                        <div class="form-group">
                            <label>OU URL externe</label>
                            <input type="url" name="url_externe" class="form-control"
                                   value="{{ old('url_externe', $guide->url_externe ?? '') }}" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><strong>Options</strong></div>
                    <div class="card-body">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="est_actif" name="est_actif" value="1"
                                   {{ old('est_actif', $guide->est_actif ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="est_actif">Guide actif</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-save mr-1"></i>{{ isset($guide->id) ? 'Enregistrer' : 'Créer le guide' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
