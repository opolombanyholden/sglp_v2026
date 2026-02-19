@extends('layouts.admin')

@section('title', 'Inscriptions en attente - ' . ($organisation->nom ?? ''))

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-clock mr-2 text-warning"></i>Inscriptions en attente</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.adherents.index', $organisation) }}">Adhérents</a></li>
                    <li class="breadcrumb-item active">En attente</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.adherents.index', $organisation) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Retour
        </a>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En attente</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['en_attente'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Validées</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['validees'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-danger shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejetées</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['rejetees'] ?? 0 }}</div>
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
            <h5 class="mb-0"><i class="fas fa-list mr-2"></i>{{ $organisation->nom }} - Inscriptions en ligne en attente</h5>
        </div>
        <div class="card-body p-0">
            @if($pendingAdherents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>NIP</th>
                            <th>Nom & Prénom</th>
                            <th>Téléphone</th>
                            <th>Profession</th>
                            <th>Date inscription</th>
                            <th>Anomalies</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingAdherents as $adherent)
                        <tr>
                            <td><code>{{ $adherent->nip ?: 'N/A' }}</code></td>
                            <td>
                                <strong>{{ $adherent->nom }}</strong> {{ $adherent->prenom }}
                                @if($adherent->email)<br><small class="text-muted">{{ $adherent->email }}</small>@endif
                            </td>
                            <td>{{ $adherent->telephone ?? '-' }}</td>
                            <td>{{ $adherent->profession ?? '-' }}</td>
                            <td>{{ $adherent->created_at ? $adherent->created_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                @if($adherent->has_anomalies)
                                    <span class="badge badge-{{ $adherent->anomalies_severity === 'critique' ? 'danger' : ($adherent->anomalies_severity === 'majeure' ? 'warning' : 'info') }}">
                                        {{ ucfirst($adherent->anomalies_severity) }}
                                    </span>
                                @else
                                    <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.adherents.show', [$organisation, $adherent]) }}" class="btn btn-outline-primary" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.adherents.validate', [$organisation, $adherent]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Valider" onclick="return confirm('Valider l\'inscription de {{ $adherent->nom }} {{ $adherent->prenom }} ?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-outline-danger" title="Rejeter" data-toggle="modal" data-target="#rejectModal{{ $adherent->id }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                {{-- Modal rejet --}}
                                <div class="modal fade" id="rejectModal{{ $adherent->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.adherents.reject', [$organisation, $adherent]) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Rejeter l'inscription</h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body text-left">
                                                    <p>Rejeter l'inscription de <strong>{{ $adherent->nom }} {{ $adherent->prenom }}</strong> ?</p>
                                                    <div class="form-group">
                                                        <label for="motif{{ $adherent->id }}">Motif du rejet <span class="text-danger">*</span></label>
                                                        <textarea class="form-control" id="motif{{ $adherent->id }}" name="motif" rows="3" required placeholder="Indiquez le motif du rejet..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-danger">Rejeter</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $pendingAdherents->links() }}</div>
            @else
            <div class="p-4 text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="text-muted">Aucune inscription en attente</h5>
                <p class="text-muted">Toutes les inscriptions en ligne ont été traitées.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
