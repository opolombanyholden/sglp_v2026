@extends('layouts.admin')

@section('title', 'Tokens API — Interopérabilité')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Tokens API</h1>
            <p class="text-muted mb-0">Gestion des accès interopérabilité pour systèmes tiers</p>
        </div>
        <a href="{{ route('admin.api.tokens.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau token
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('new_token'))
    <div class="alert alert-warning border border-warning">
        <h5><i class="fas fa-key me-2"></i>Nouveau token généré — à copier maintenant</h5>
        <p class="mb-2">Ce token ne sera plus affiché après cette page. Transmettez-le de façon sécurisée au système client.</p>
        <div class="input-group">
            <input type="text" class="form-control font-monospace" id="newTokenValue"
                   value="{{ session('new_token') }}" readonly>
            <button class="btn btn-outline-secondary" onclick="copyToken()">
                <i class="fas fa-copy me-1"></i>Copier
            </button>
        </div>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom / Application</th>
                        <th>Préfixe</th>
                        <th>Permissions</th>
                        <th>Rate limit</th>
                        <th>Dernière utilisation</th>
                        <th>Requêtes totales</th>
                        <th>Expiration</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tokens as $token)
                    <tr class="{{ !$token->est_actif ? 'table-secondary text-muted' : '' }}">
                        <td>
                            <strong>{{ $token->nom }}</strong>
                            @if($token->organisation_cliente)
                                <br><small class="text-muted">{{ $token->organisation_cliente }}</small>
                            @endif
                        </td>
                        <td><code>{{ $token->prefix }}…</code></td>
                        <td>
                            @foreach($token->permissions ?? ['*'] as $perm)
                                <span class="badge bg-secondary">{{ $perm }}</span>
                            @endforeach
                        </td>
                        <td>{{ $token->rate_limit }} req/min</td>
                        <td>
                            @if($token->last_used_at)
                                {{ $token->last_used_at->diffForHumans() }}<br>
                                <small class="text-muted">{{ $token->last_used_ip }}</small>
                            @else
                                <span class="text-muted">Jamais</span>
                            @endif
                        </td>
                        <td>{{ number_format($token->total_requests) }}</td>
                        <td>
                            @if($token->expires_at)
                                <span class="{{ $token->isExpired() ? 'text-danger' : '' }}">
                                    {{ $token->expires_at->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted">Permanent</span>
                            @endif
                        </td>
                        <td>
                            @if(!$token->est_actif)
                                <span class="badge bg-danger">Révoqué</span>
                            @elseif($token->isExpired())
                                <span class="badge bg-warning text-dark">Expiré</span>
                            @else
                                <span class="badge bg-success">Actif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($token->est_actif)
                                <form method="POST" action="{{ route('admin.api.tokens.destroy', $token) }}"
                                      onsubmit="return confirm('Révoquer ce token ? Les systèmes clients utilisant ce token perdront l\'accès.')"
                                      class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-ban me-1"></i>Révoquer
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.api.tokens.activate', $token) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-check me-1"></i>Réactiver
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-key fa-2x mb-2 d-block"></i>
                            Aucun token API créé. <a href="{{ route('admin.api.tokens.create') }}">Créer le premier token</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ url('/api/v1/documentation') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-book me-1"></i>Voir la documentation API
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToken() {
    const input = document.getElementById('newTokenValue');
    input.select();
    document.execCommand('copy');
    alert('Token copié dans le presse-papier.');
}
</script>
@endpush
