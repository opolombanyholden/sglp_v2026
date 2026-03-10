@extends('layouts.admin')
@section('title', 'Nouveau paramètre')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
            <i class="fas fa-sliders-h mr-2" style="color:#009e3f;"></i>Nouveau paramètre
        </h1>
        <a href="{{ route('admin.portail.parametres.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('admin.portail.parametres.store') }}">
                @csrf
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Clé <span class="text-danger">*</span> <small class="text-muted">(unique, snake_case)</small></label>
                            <input type="text" name="cle" class="form-control @error('cle') is-invalid @enderror"
                                   value="{{ old('cle') }}" placeholder="ex: hero_titre" required>
                            @error('cle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Valeur</label>
                            <textarea name="valeur" class="form-control" rows="4">{{ old('valeur') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control" required>
                                        @foreach(['text','html','json','image','url','email','phone'] as $t)
                                            <option value="{{ $t }}" {{ old('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Groupe <span class="text-danger">*</span></label>
                                    <input type="text" name="groupe" class="form-control" value="{{ old('groupe', 'general') }}"
                                           list="groupe-list" required>
                                    <datalist id="groupe-list">
                                        <option>general</option><option>hero</option><option>stats</option>
                                        <option>about</option><option>contact</option><option>footer</option>
                                    </datalist>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i>Créer le paramètre</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
