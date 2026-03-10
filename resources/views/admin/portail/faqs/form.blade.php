@extends('layouts.admin')
@section('title', isset($faq->id) ? 'Modifier la FAQ' : 'Nouvelle question FAQ')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
            <i class="fas fa-question-circle mr-2" style="color:#009e3f;"></i>
            {{ isset($faq->id) ? 'Modifier la question' : 'Nouvelle question FAQ' }}
        </h1>
        <a href="{{ route('admin.portail.faqs.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form method="POST"
                  action="{{ isset($faq->id) ? route('admin.portail.faqs.update', $faq) : route('admin.portail.faqs.store') }}">
                @csrf
                @if(isset($faq->id)) @method('PUT') @endif

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Question <span class="text-danger">*</span></label>
                            <input type="text" name="question" class="form-control @error('question') is-invalid @enderror"
                                   value="{{ old('question', $faq->question ?? '') }}" required>
                            @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Réponse <span class="text-danger">*</span></label>
                            <textarea name="reponse" class="form-control @error('reponse') is-invalid @enderror" rows="6" required>{{ old('reponse', $faq->reponse ?? '') }}</textarea>
                            @error('reponse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Catégorie <span class="text-danger">*</span></label>
                                    <input type="text" name="categorie" class="form-control" required
                                           value="{{ old('categorie', $faq->categorie ?? '') }}"
                                           list="cat-list">
                                    <datalist id="cat-list">
                                        @foreach($categories as $cat)<option>{{ $cat }}</option>@endforeach
                                        <option>Général</option><option>Création</option>
                                        <option>Technique</option><option>Gestion</option>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Ordre</label>
                                    <input type="number" name="ordre" class="form-control" min="0" value="{{ old('ordre', $faq->ordre ?? 0) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Actif</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="est_actif" name="est_actif" value="1"
                                               {{ old('est_actif', $faq->est_actif ?? true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="est_actif">Activé</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i>{{ isset($faq->id) ? 'Enregistrer' : 'Créer' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
