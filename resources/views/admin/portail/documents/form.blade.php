@extends('layouts.admin')
@section('title', isset($document->id) ? 'Modifier le document' : 'Nouveau document')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
            <i class="fas fa-file-download mr-2" style="color:#009e3f;"></i>
            {{ isset($document->id) ? 'Modifier le document' : 'Nouveau document' }}
        </h1>
        <a href="{{ route('admin.portail.documents.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST"
          action="{{ isset($document->id) ? route('admin.portail.documents.update', $document) : route('admin.portail.documents.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if(isset($document->id)) @method('PUT') @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><strong>Informations du document</strong></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Titre <span class="text-danger">*</span></label>
                            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                                   value="{{ old('titre', $document->titre ?? '') }}" required>
                            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $document->description ?? '') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Catégorie <span class="text-danger">*</span></label>
                                    <input type="text" name="categorie" class="form-control @error('categorie') is-invalid @enderror"
                                           value="{{ old('categorie', $document->categorie ?? '') }}"
                                           list="cat-list" required>
                                    <datalist id="cat-list">
                                        <option>Guides</option><option>Formulaires</option>
                                        <option>Modèles</option><option>Réglementation</option>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type d'organisation</label>
                                    <select name="type_organisation" class="form-control">
                                        <option value="">— Tous types —</option>
                                        @foreach(['association','ong','parti','confession','tous'] as $t)
                                            <option value="{{ $t }}" {{ old('type_organisation', $document->type_organisation ?? '') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Fichier PDF / DOCX</label>
                            @if(!empty($document->chemin_fichier))
                                <p class="mb-1"><small class="text-muted">Fichier actuel : {{ basename($document->chemin_fichier) }}</small></p>
                            @endif
                            <input type="file" name="fichier" class="form-control-file" accept=".pdf,.doc,.docx,.xls,.xlsx">
                            <small class="text-muted">Max 20 MB. Laisser vide pour conserver le fichier actuel.</small>
                        </div>
                        <div class="form-group">
                            <label>OU URL externe</label>
                            <input type="url" name="url_externe" class="form-control @error('url_externe') is-invalid @enderror"
                                   value="{{ old('url_externe', $document->url_externe ?? '') }}" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><strong>Options</strong></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Ordre d'affichage</label>
                            <input type="number" name="ordre" class="form-control" min="0" value="{{ old('ordre', $document->ordre ?? 0) }}">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="est_actif" name="est_actif" value="1"
                                       {{ old('est_actif', $document->est_actif ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="est_actif">Document actif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-save mr-1"></i>
                    {{ isset($document->id) ? 'Enregistrer' : 'Créer le document' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
