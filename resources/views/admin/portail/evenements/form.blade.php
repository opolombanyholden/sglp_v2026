@extends('layouts.admin')
@section('title', isset($evenement->id) ? 'Modifier l\'événement' : 'Nouvel événement')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
            <i class="fas fa-calendar-alt mr-2" style="color:#009e3f;"></i>
            {{ isset($evenement->id) ? 'Modifier l\'événement' : 'Nouvel événement' }}
        </h1>
        <a href="{{ route('admin.portail.evenements.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form method="POST"
                  action="{{ isset($evenement->id) ? route('admin.portail.evenements.update', $evenement) : route('admin.portail.evenements.store') }}">
                @csrf
                @if(isset($evenement->id)) @method('PUT') @endif

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Titre <span class="text-danger">*</span></label>
                            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                                   value="{{ old('titre', $evenement->titre ?? '') }}" required>
                            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description', $evenement->description ?? '') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                                        @foreach(['echeance'=>'Échéance','formation'=>'Formation','maintenance'=>'Maintenance','evenement'=>'Événement'] as $val => $label)
                                            <option value="{{ $val }}" {{ old('type', $evenement->type ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date début <span class="text-danger">*</span></label>
                                    <input type="date" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror"
                                           value="{{ old('date_debut', isset($evenement->date_debut) ? $evenement->date_debut->format('Y-m-d') : '') }}" required>
                                    @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date fin</label>
                                    <input type="date" name="date_fin" class="form-control"
                                           value="{{ old('date_fin', isset($evenement->date_fin) ? $evenement->date_fin->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Lieu</label>
                            <input type="text" name="lieu" class="form-control" value="{{ old('lieu', $evenement->lieu ?? '') }}" placeholder="Ville, adresse...">
                        </div>
                        <div class="form-group">
                            <label>URL (lien externe)</label>
                            <input type="url" name="url" class="form-control" value="{{ old('url', $evenement->url ?? '') }}" placeholder="https://...">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="est_important" name="est_important" value="1"
                                           {{ old('est_important', $evenement->est_important ?? false) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="est_important">Marquer comme important</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="est_actif" name="est_actif" value="1"
                                           {{ old('est_actif', $evenement->est_actif ?? true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="est_actif">Actif</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i>{{ isset($evenement->id) ? 'Enregistrer' : 'Créer l\'événement' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
