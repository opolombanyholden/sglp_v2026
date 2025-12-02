@extends('layouts.admin')

@section('title', 'Gestion des Fonctions')
@section('page-title', 'Fonctions des Membres')

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

.stats-card:hover {
    transform: translateY(-3px);
}

.stats-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.table-fonctions {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.table-fonctions thead th {
    background: linear-gradient(135deg, var(--gabon-blue) 0%, #0056b3 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 1rem;
}

.table-fonctions tbody td {
    vertical-align: middle;
    padding: 0.875rem 1rem;
}

.table-fonctions tbody tr:hover {
    background-color: rgba(0, 158, 63, 0.05);
}

.fonction-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
}

.badge-categorie {
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-action {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

.filter-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- ALERTES --}}
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
                <i class="fas fa-user-tag mr-2" style="color: var(--gabon-green);"></i>
                Fonctions des Membres
            </h4>
            <p class="text-muted mb-0">Gérer les fonctions attribuables aux membres des organisations</p>
        </div>
        <a href="{{ route('admin.referentiels.fonctions.create') }}" class="btn btn-success btn-lg">
            <i class="fas fa-plus mr-2"></i>Nouvelle Fonction
        </a>
    </div>

    {{-- STATISTIQUES --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon mr-3" style="background: linear-gradient(135deg, var(--gabon-green), #00b347);">
                        <i class="fas fa-list text-white"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 font-weight-bold">{{ $stats['total'] }}</h3>
                        <small class="text-muted">Total fonctions</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon mr-3" style="background: linear-gradient(135deg, var(--gabon-blue), #0056b3);">
                        <i class="fas fa-crown text-white"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 font-weight-bold">{{ $stats['bureau'] }}</h3>
                        <small class="text-muted">Bureau exécutif</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon mr-3" style="background: linear-gradient(135deg, var(--gabon-yellow), #e6b800);">
                        <i class="fas fa-star text-white"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 font-weight-bold">{{ $stats['obligatoires'] }}</h3>
                        <small class="text-muted">Obligatoires</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon mr-3" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 font-weight-bold">{{ $stats['actives'] }}</h3>
                        <small class="text-muted">Actives</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTRES --}}
    <div class="card filter-card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.referentiels.fonctions.index') }}" class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="small text-muted mb-1">Rechercher</label>
                    <input type="text" name="search" class="form-control" placeholder="Nom, code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small text-muted mb-1">Catégorie</label>
                    <select name="categorie" class="form-control">
                        <option value="">Toutes</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('categorie') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small text-muted mb-1">Bureau</label>
                    <select name="bureau" class="form-control">
                        <option value="">Tous</option>
                        <option value="oui" {{ request('bureau') == 'oui' ? 'selected' : '' }}>Bureau uniquement</option>
                        <option value="non" {{ request('bureau') == 'non' ? 'selected' : '' }}>Hors bureau</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small text-muted mb-1">Statut</label>
                    <select name="statut" class="form-control">
                        <option value="">Tous</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search mr-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.referentiels.fonctions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo mr-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLEAU --}}
    <div class="card table-fonctions">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Fonction</th>
                        <th>Code</th>
                        <th>Catégorie</th>
                        <th class="text-center">Bureau</th>
                        <th class="text-center">Obligatoire</th>
                        <th class="text-center">Max</th>
                        <th class="text-center">Statut</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fonctions as $fonction)
                    <tr>
                        <td>
                            <span class="text-muted">{{ $fonction->ordre }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="fonction-icon mr-2" style="background-color: {{ $fonction->couleur ?? '#6c757d' }};">
                                    <i class="fas {{ $fonction->icone ?? 'fa-user' }}"></i>
                                </div>
                                <div>
                                    <strong>{{ $fonction->nom }}</strong>
                                    @if($fonction->nom_feminin && $fonction->nom_feminin != $fonction->nom)
                                        <small class="text-muted d-block">{{ $fonction->nom_feminin }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $fonction->code }}</code></td>
                        <td>
                            @php
                                $catColors = ['bureau' => 'primary', 'commission' => 'info', 'membre' => 'secondary'];
                            @endphp
                            <span class="badge badge-{{ $catColors[$fonction->categorie] ?? 'secondary' }} badge-categorie">
                                {{ $categories[$fonction->categorie] ?? $fonction->categorie }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($fonction->is_bureau)
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-minus text-muted"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($fonction->is_obligatoire)
                                <i class="fas fa-asterisk text-danger"></i>
                            @else
                                <i class="fas fa-minus text-muted"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light">{{ $fonction->nb_max }}</span>
                        </td>
                        <td class="text-center">
                            @if($fonction->is_active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-danger">Inactif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.referentiels.fonctions.edit', $fonction) }}" 
                               class="btn btn-sm btn-outline-primary btn-action" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.referentiels.fonctions.toggle-status', $fonction) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-{{ $fonction->is_active ? 'warning' : 'success' }} btn-action" 
                                        title="{{ $fonction->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-{{ $fonction->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.referentiels.fonctions.destroy', $fonction) }}" 
                                  method="POST" class="d-inline" 
                                  onsubmit="return confirm('Supprimer cette fonction ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                            Aucune fonction trouvée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($fonctions->hasPages())
        <div class="card-footer bg-white">
            {{ $fonctions->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection