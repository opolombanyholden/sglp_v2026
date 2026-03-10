@extends('layouts.admin')

@section('title', 'Domaines d\'activité')
@section('page-title', 'Domaines d\'activité')

@push('styles')
<style>
:root {
    --gabon-green: #009e3f;
    --gabon-yellow: #ffcd00;
    --gabon-blue: #003f7f;
}
.stats-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: transform 0.2s;
}
.stats-card:hover { transform: translateY(-3px); }
.stats-icon {
    width: 50px; height: 50px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
}
.table-domaines {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
.table-domaines thead th {
    background: linear-gradient(135deg, var(--gabon-blue) 0%, #0056b3 100%);
    color: white; font-weight: 600; border: none; padding: 1rem;
}
.table-domaines tbody td { vertical-align: middle; padding: 0.875rem 1rem; }
.table-domaines tbody tr:hover { background-color: rgba(0,158,63,0.05); }
.btn-action {
    width: 32px; height: 32px; padding: 0;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 6px;
}
.filter-card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 font-weight-bold text-dark">
                <i class="fas fa-industry mr-2" style="color: var(--gabon-green);"></i>
                Domaines d'activité
            </h4>
            <p class="text-muted mb-0">Gérer les domaines d'activité des organisations</p>
        </div>
        <a href="{{ route('admin.referentiels.domaines-activite.create') }}" class="btn btn-success btn-lg">
            <i class="fas fa-plus mr-2"></i>Nouveau domaine
        </a>
    </div>

    {{-- STATISTIQUES --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon mr-3" style="background: linear-gradient(135deg, var(--gabon-green), #00b347);">
                        <i class="fas fa-list text-white"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 font-weight-bold">{{ $stats['total'] }}</h3>
                        <small class="text-muted">Total domaines</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon mr-3" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 font-weight-bold">{{ $stats['actifs'] }}</h3>
                        <small class="text-muted">Actifs</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon mr-3" style="background: linear-gradient(135deg, #6c757d, #adb5bd);">
                        <i class="fas fa-times-circle text-white"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 font-weight-bold">{{ $stats['inactifs'] }}</h3>
                        <small class="text-muted">Inactifs</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTRES --}}
    <div class="card filter-card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.referentiels.domaines-activite.index') }}" class="row align-items-end">
                <div class="col-md-5 mb-2">
                    <label class="small text-muted mb-1">Rechercher</label>
                    <input type="text" name="search" class="form-control" placeholder="Nom, code, description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small text-muted mb-1">Statut</label>
                    <select name="statut" class="form-control">
                        <option value="">Tous</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search mr-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.referentiels.domaines-activite.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo mr-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLEAU --}}
    <div class="card table-domaines">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">Ordre</th>
                        <th>Nom</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-center">Organisations</th>
                        <th class="text-center">Statut</th>
                        <th width="120" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($domaines as $domaine)
                    <tr>
                        <td><span class="text-muted">{{ $domaine->ordre }}</span></td>
                        <td><strong>{{ $domaine->nom }}</strong></td>
                        <td><code>{{ $domaine->code }}</code></td>
                        <td>
                            @if($domaine->description)
                                <span class="text-muted" title="{{ $domaine->description }}">
                                    {{ Str::limit($domaine->description, 60) }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $domaine->organisations()->count() }}</span>
                        </td>
                        <td class="text-center">
                            @if($domaine->is_active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-danger">Inactif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.referentiels.domaines-activite.edit', $domaine) }}"
                               class="btn btn-sm btn-outline-primary btn-action" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.referentiels.domaines-activite.toggle-status', $domaine) }}"
                                  method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="btn btn-sm btn-outline-{{ $domaine->is_active ? 'warning' : 'success' }} btn-action"
                                        title="{{ $domaine->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-{{ $domaine->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.referentiels.domaines-activite.destroy', $domaine) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer ce domaine d\'activité ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                            Aucun domaine d'activité trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($domaines->hasPages())
        <div class="card-footer bg-white">
            {{ $domaines->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
