@extends('layouts.admin')

@section('title', 'Corrections administratives')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Corrections administratives</h1>
            <p class="text-muted mb-0">Correction d'erreurs sur les dossiers approuvés</p>
        </div>
        <a href="{{ route('admin.corrections.select-organisation') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouvelle correction
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif

    {{-- Filtres --}}
    <div class="card mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Rechercher (n dossier, organisation...)"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="brouillon" {{ request('statut') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                        <option value="soumis" {{ request('statut') === 'soumis' ? 'selected' : '' }}>Soumis</option>
                        <option value="accepte" {{ request('statut') === 'accepte' ? 'selected' : '' }}>Approuvé</option>
                        <option value="rejete" {{ request('statut') === 'rejete' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N Dossier</th>
                        <th>Organisation</th>
                        <th>Dossier original</th>
                        <th>Corrections</th>
                        <th>Initiée par</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dossiers as $dossier)
                    <tr>
                        <td><code>{{ $dossier->numero_dossier }}</code></td>
                        <td>
                            @if($dossier->organisation)
                                <strong>{{ $dossier->organisation->nom }}</strong>
                                @if($dossier->organisation->sigle)
                                    <span class="text-muted">({{ $dossier->organisation->sigle }})</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($dossier->parentDossier)
                                <code>{{ $dossier->parentDossier->numero_dossier }}</code>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $dossier->corrections->count() }} champ(s)</span>
                        </td>
                        <td>
                            @if($dossier->corrections->first() && $dossier->corrections->first()->correctedByUser)
                                {{ $dossier->corrections->first()->correctedByUser->name }}
                            @endif
                        </td>
                        <td>{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @switch($dossier->statut)
                                @case('brouillon')
                                    <span class="badge bg-secondary">Brouillon</span>
                                    @break
                                @case('soumis')
                                    <span class="badge bg-warning text-dark">En attente</span>
                                    @break
                                @case('en_cours')
                                    <span class="badge bg-primary">En cours</span>
                                    @break
                                @case('accepte')
                                    <span class="badge bg-success">Approuvé</span>
                                    @break
                                @case('rejete')
                                    <span class="badge bg-danger">Rejeté</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $dossier->statut }}</span>
                            @endswitch
                        </td>
                        <td class="text-end">
                            @if(in_array($dossier->statut, ['soumis', 'en_cours']))
                                <a href="{{ route('admin.corrections.review', $dossier) }}"
                                   class="btn btn-sm btn-outline-primary" title="Examiner">
                                    <i class="fas fa-eye"></i> Examiner
                                </a>
                            @elseif($dossier->statut === 'accepte')
                                <a href="{{ route('admin.corrections.review', $dossier) }}"
                                   class="btn btn-sm btn-outline-success" title="Voir">
                                    <i class="fas fa-check-circle"></i> Voir
                                </a>
                            @elseif($dossier->statut === 'rejete')
                                <a href="{{ route('admin.corrections.review', $dossier) }}"
                                   class="btn btn-sm btn-outline-danger" title="Voir">
                                    <i class="fas fa-times-circle"></i> Voir
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Aucune correction enregistrée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dossiers->hasPages())
            <div class="card-footer">{{ $dossiers->links() }}</div>
        @endif
    </div>
</div>
@endsection
