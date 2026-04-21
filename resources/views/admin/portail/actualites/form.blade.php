@extends('layouts.admin')

@section('title', isset($actualite->id) ? 'Modifier l\'actualité' : 'Nouvelle actualité')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
                <i class="fas fa-newspaper mr-2" style="color:#009e3f;"></i>
                {{ isset($actualite->id) ? 'Modifier l\'actualité' : 'Nouvelle actualité' }}
            </h1>
        </div>
        <a href="{{ route('admin.portail.actualites.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Retour
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST"
          action="{{ isset($actualite->id) ? route('admin.portail.actualites.update', $actualite) : route('admin.portail.actualites.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if(isset($actualite->id)) @method('PUT') @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><strong>Contenu</strong></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Titre <span class="text-danger">*</span></label>
                            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                                   value="{{ old('titre', $actualite->titre ?? '') }}" required>
                            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Slug (URL) <small class="text-muted">Laissez vide pour générer automatiquement</small></label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $actualite->slug ?? '') }}" placeholder="ex: titre-de-l-article">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Extrait <small class="text-muted">(résumé court, max 500 caractères)</small></label>
                            <textarea name="extrait" class="form-control @error('extrait') is-invalid @enderror" rows="2"
                                      maxlength="500">{{ old('extrait', $actualite->extrait ?? '') }}</textarea>
                            @error('extrait')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Contenu complet <span class="text-danger">*</span></label>
                            <textarea name="contenu" id="contenu" class="form-control @error('contenu') is-invalid @enderror"
                                      rows="12">{{ old('contenu', $actualite->contenu ?? '') }}</textarea>
                            @error('contenu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><strong>Publication</strong></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Statut <span class="text-danger">*</span></label>
                            <select name="statut" class="form-control @error('statut') is-invalid @enderror">
                                <option value="brouillon" {{ old('statut', $actualite->statut ?? '') == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                                <option value="publie"    {{ old('statut', $actualite->statut ?? '') == 'publie'    ? 'selected' : '' }}>Publié</option>
                                <option value="archive"   {{ old('statut', $actualite->statut ?? '') == 'archive'   ? 'selected' : '' }}>Archivé</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date de publication</label>
                            <input type="date" name="date_publication" class="form-control"
                                   value="{{ old('date_publication', isset($actualite->date_publication) ? $actualite->date_publication->format('Y-m-d') : date('Y-m-d')) }}">
                        </div>
                        <div class="form-group">
                            <label>Catégorie <span class="text-danger">*</span></label>
                            <input type="text" name="categorie" class="form-control @error('categorie') is-invalid @enderror"
                                   value="{{ old('categorie', $actualite->categorie ?? '') }}"
                                   list="categories-list" required>
                            <datalist id="categories-list">
                                <option>Réglementation</option><option>Événement</option><option>Documentation</option>
                                <option>Annonce</option><option>Guide</option><option>Statistiques</option>
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label>Auteur</label>
                            <input type="text" name="auteur" class="form-control"
                                   value="{{ old('auteur', $actualite->auteur ?? 'Administration DGELP') }}">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="en_une" name="en_une" value="1"
                                       {{ old('en_une', $actualite->en_une ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="en_une">Mettre à la une</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header"><strong>Image</strong></div>
                    <div class="card-body">
                        @if(!empty($actualite->image))
                            <img src="{{ asset('storage/' . $actualite->image) }}" class="img-fluid rounded mb-2" alt="">
                        @endif
                        <input type="file" name="image" class="form-control-file" accept="image/*">
                        <small class="text-muted">JPEG, PNG. Max 2 MB.</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-save mr-1"></i>
                    {{ isset($actualite->id) ? 'Enregistrer les modifications' : 'Créer l\'actualité' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
