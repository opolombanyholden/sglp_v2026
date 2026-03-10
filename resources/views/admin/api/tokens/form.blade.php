@extends('layouts.admin')

@section('title', 'Créer un token API')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.api.tokens.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="h3 mb-0">Nouveau token API</h1>
            <p class="text-muted mb-0">Créer un accès pour un système tiers</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.api.tokens.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nom de l'application cliente <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom') }}" placeholder="ex: Ministère des Finances - Système RH" required>
                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Organisation / Institution cliente</label>
                            <input type="text" name="organisation_cliente" class="form-control"
                                   value="{{ old('organisation_cliente') }}"
                                   placeholder="ex: Ministère de l'Économie">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Permissions (scopes)</label>
                            <div class="d-flex flex-wrap gap-3 mt-1">
                                @foreach(['organisations' => 'Lister/consulter les organisations', 'verify' => 'Vérifier les récépissés', 'stats' => 'Statistiques agrégées', '*' => 'Accès complet'] as $scope => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                           value="{{ $scope }}" id="perm_{{ $scope }}"
                                           {{ in_array($scope, old('permissions', ['organisations', 'stats', 'verify'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $scope }}">
                                        <strong>{{ $scope }}</strong><br>
                                        <small class="text-muted">{{ $label }}</small>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Rate limit (requêtes / minute)</label>
                            <input type="number" name="rate_limit" class="form-control @error('rate_limit') is-invalid @enderror"
                                   value="{{ old('rate_limit', 60) }}" min="10" max="600">
                            <div class="form-text">10 à 600 req/min. Défaut recommandé : 60.</div>
                            @error('rate_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Date d'expiration</label>
                            <input type="date" name="expires_at" class="form-control"
                                   value="{{ old('expires_at') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <div class="form-text">Laisser vide pour un token permanent.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Notes internes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Contexte, contact technique, cas d'usage...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Le token sera généré et affiché <strong>une seule fois</strong> après la création.
                            Transmettez-le immédiatement au système client par canal sécurisé.
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Générer le token
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-book me-2"></i>Comment utiliser ce token</h6>
                    <p class="small">Le système client doit inclure le token dans chaque requête via le header HTTP :</p>
                    <pre class="bg-dark text-white rounded p-3 small">Authorization: Bearer &lt;token&gt;</pre>
                    <p class="small mt-3">Exemple de requête :</p>
                    <pre class="bg-dark text-white rounded p-3 small" style="font-size:0.75rem">GET /api/v1/public/organisations
Host: {{ parse_url(config('app.url'), PHP_URL_HOST) }}
Authorization: Bearer abc123...
Accept: application/json</pre>
                    <p class="small mt-2">
                        <a href="{{ url('/api/v1/documentation') }}" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i>Documentation complète
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
