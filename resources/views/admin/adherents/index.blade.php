@extends('layouts.admin')

@section('title', 'Adhérents - ' . ($organisation->nom ?? ''))

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-users mr-2 text-primary"></i>Adhérents</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.organisations.show', $organisation) }}">{{ $organisation->sigle ?? $organisation->nom }}</a></li>
                    <li class="breadcrumb-item active">Adhérents</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(($stats['en_attente'] ?? 0) > 0)
            <a href="{{ route('admin.adherents.pending', $organisation) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-clock mr-1"></i>{{ $stats['en_attente'] }} en attente
            </a>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Actifs</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['actifs'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-danger shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Inactifs</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['inactifs'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Fondateurs</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['fondateurs'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Anomalies</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['avec_anomalies'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-secondary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">En attente</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['en_attente'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Liste des adhérents - {{ $organisation->nom }}</h5>
        </div>
        <div class="card-body p-0">
            @if($adherents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>NIP</th>
                            <th>Nom & Prénom</th>
                            <th>Profession</th>
                            <th>Statut</th>
                            <th>Anomalies</th>
                            <th>Date adhésion</th>
                            <th>Source</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adherents as $adherent)
                        <tr>
                            <td><code>{{ $adherent->nip ?: 'N/A' }}</code></td>
                            <td>
                                <strong>{{ $adherent->nom }}</strong> {{ $adherent->prenom }}
                                @if($adherent->is_fondateur)
                                    <span class="badge badge-warning ml-1"><i class="fas fa-star"></i></span>
                                @endif
                            </td>
                            <td>{{ $adherent->profession ?: '-' }}</td>
                            <td>
                                @if($adherent->is_active)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                @if($adherent->has_anomalies)
                                    @if($adherent->anomalies_severity === 'critique')
                                        <span class="badge badge-danger">Critique</span>
                                    @elseif($adherent->anomalies_severity === 'majeure')
                                        <span class="badge badge-warning">Majeure</span>
                                    @else
                                        <span class="badge badge-info">Mineure</span>
                                    @endif
                                @else
                                    <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                @endif
                            </td>
                            <td>{{ $adherent->date_adhesion ? $adherent->date_adhesion->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($adherent->source_inscription === 'auto_inscription')
                                    <span class="badge badge-info">En ligne</span>
                                @else
                                    <span class="badge badge-secondary">Manuelle</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.adherents.show', [$organisation, $adherent]) }}" class="btn btn-outline-primary btn-sm" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $adherents->links() }}</div>
            @else
            <div class="p-4 text-center">
                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                <p class="text-muted">Aucun adhérent enregistré pour cette organisation.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
