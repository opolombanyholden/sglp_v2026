@extends('layouts.operator')

@section('title', 'Adhérents - ' . ($organisation->nom ?? ''))

@section('page-title', 'Gestion des adhérents')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background-color: #009e3f;">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-2">
                                    <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}" class="text-white" style="opacity:.75">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('operator.dossiers.index') }}" class="text-white" style="opacity:.75">Organisations</a></li>
                                    <li class="breadcrumb-item active text-white">{{ $organisation->sigle ?? $organisation->nom }}</li>
                                </ol>
                            </nav>
                            <h2 class="mb-1"><i class="fas fa-users mr-2"></i>Adhérents</h2>
                            <p class="mb-0" style="opacity:.9">{{ $organisation->nom }}@if($organisation->sigle) ({{ $organisation->sigle }})@endif</p>
                        </div>
                        <div class="col-md-5 text-right">
                            <a href="{{ route('operator.adherents.create', $organisation) }}" class="btn btn-light btn-sm mb-1">
                                <i class="fas fa-plus mr-1"></i>Ajouter
                            </a>
                            <a href="{{ route('operator.adherents.import', $organisation) }}" class="btn btn-outline-light btn-sm mb-1">
                                <i class="fas fa-file-import mr-1"></i>Importer CSV
                            </a>
                            <a href="{{ route('operator.adherents.export', $organisation) }}" class="btn btn-outline-light btn-sm mb-1">
                                <i class="fas fa-file-export mr-1"></i>Exporter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Actifs</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['actifs'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Inactifs</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['inactifs'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-times fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Fondateurs</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['fondateurs'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-star fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    <div class="row">
        <div class="col-lg-9">
            {{-- Table --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list mr-2 text-primary"></i>Liste des adhérents</h5>
                </div>
                <div class="card-body p-0">
                    @if($adherents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>NIP</th>
                                    <th>Nom & Prénom</th>
                                    <th>Téléphone</th>
                                    <th>Fonction</th>
                                    <th>Statut</th>
                                    <th>Anomalies</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adherents as $adherent)
                                <tr>
                                    <td><code>{{ $adherent->nip ?? 'N/A' }}</code></td>
                                    <td>
                                        <strong>{{ $adherent->nom }}</strong> {{ $adherent->prenom }}
                                        @if($adherent->is_fondateur)
                                            <span class="badge badge-warning ml-1" title="Fondateur"><i class="fas fa-star"></i></span>
                                        @endif
                                        @if($adherent->email)
                                            <br><small class="text-muted">{{ $adherent->email }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $adherent->telephone ?? '-' }}</td>
                                    <td>{{ $adherent->fonction ?? 'Membre' }}</td>
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
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('operator.adherents.show', [$organisation, $adherent]) }}" class="btn btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('operator.adherents.edit', [$organisation, $adherent]) }}" class="btn btn-outline-secondary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($adherent->is_active && !$adherent->is_fondateur)
                                            <form action="{{ route('operator.adherents.destroy', [$organisation, $adherent]) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la démission de {{ $adherent->nom }} {{ $adherent->prenom }} ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Démission">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
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
                        <p class="text-muted">Aucun adhérent enregistré.</p>
                        <a href="{{ route('operator.adherents.create', $organisation) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i>Ajouter le premier adhérent
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            {{-- Liens rapides --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-link mr-2 text-primary"></i>Accès rapide</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('operator.adherents.fondateurs', $organisation) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-star text-warning mr-2"></i>Fondateurs
                        <span class="badge badge-secondary float-right">{{ $stats['fondateurs'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('operator.adherents.duplicates', $organisation) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-copy text-danger mr-2"></i>Doublons
                    </a>
                    @if($organisation->adherentsEnAttenteValidation && $organisation->adherentsEnAttenteValidation->count() > 0)
                    <a href="{{ route('operator.inscription.pending', $organisation) }}" class="list-group-item list-group-item-action list-group-item-warning">
                        <i class="fas fa-clock mr-2"></i>Inscriptions en attente
                        <span class="badge badge-warning float-right">{{ $organisation->adherentsEnAttenteValidation->count() }}</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Lien inscription publique --}}
            @include('operator.partials.inscription-link-card', ['organisation' => $organisation])
        </div>
    </div>
</div>
@endsection
