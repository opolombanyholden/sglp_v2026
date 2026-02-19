@extends('layouts.operator')

@section('title', 'Inscriptions en attente - ' . $organisation->nom)

@section('page-title', 'Inscriptions en attente de validation')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-2">
                                    <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}" class="text-white opacity-75">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('operator.dossiers.index') }}" class="text-white opacity-75">Dossiers</a></li>
                                    <li class="breadcrumb-item active text-white">{{ $organisation->nom }}</li>
                                </ol>
                            </nav>
                            <h2 class="mb-1">
                                <i class="fas fa-user-clock mr-2"></i>
                                Inscriptions en attente
                            </h2>
                            <p class="mb-0 opacity-90">{{ $organisation->nom }} ({{ $organisation->sigle ?? '' }})</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('operator.dossiers.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left mr-1"></i>Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['en_attente'] }}</h4>
                            <small class="text-muted">En attente</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #28a745 !important;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['validees'] }}</h4>
                            <small class="text-muted">Validées</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['rejetees'] }}</h4>
                            <small class="text-muted">Rejetées</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Table des inscriptions --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($pendingAdherents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th class="border-0">NIP</th>
                                <th class="border-0">Nom & Prénom</th>
                                <th class="border-0">Date soumission</th>
                                <th class="border-0">Pièce d'identité</th>
                                <th class="border-0">Anomalies</th>
                                <th class="border-0 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingAdherents as $adherent)
                                <tr>
                                    <td class="align-middle">
                                        <code>{{ $adherent->nip ?? 'N/A' }}</code>
                                    </td>
                                    <td class="align-middle">
                                        <strong>{{ $adherent->nom }}</strong> {{ $adherent->prenom }}
                                        @if($adherent->telephone)
                                            <br><small class="text-muted"><i class="fas fa-phone mr-1"></i>{{ $adherent->telephone }}</small>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <small>{{ $adherent->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="align-middle">
                                        @if($adherent->piece_identite)
                                            <a href="{{ Storage::disk('public')->url($adherent->piece_identite) }}"
                                               target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-file-image mr-1"></i>Voir
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($adherent->has_anomalies)
                                            @php $severity = $adherent->anomalies_severity; @endphp
                                            @if($severity === 'critique')
                                                <span class="badge badge-danger" title="{{ json_encode($adherent->anomalies_data) }}">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>Critique
                                                </span>
                                            @elseif($severity === 'majeure')
                                                <span class="badge badge-warning text-dark" title="{{ json_encode($adherent->anomalies_data) }}">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>Majeure
                                                </span>
                                            @else
                                                <span class="badge badge-info" title="{{ json_encode($adherent->anomalies_data) }}">
                                                    <i class="fas fa-info-circle mr-1"></i>Mineure
                                                </span>
                                            @endif

                                            {{-- Détail anomalies --}}
                                            @if(is_array($adherent->anomalies_data))
                                                <div class="mt-1">
                                                    @foreach($adherent->anomalies_data as $anomalie)
                                                        <small class="d-block text-muted">
                                                            @if(is_array($anomalie))
                                                                {{ $anomalie['message'] ?? $anomalie['code'] ?? '' }}
                                                            @else
                                                                {{ $anomalie }}
                                                            @endif
                                                        </small>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>OK</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <form method="POST"
                                              action="{{ route('operator.inscription.validate', [$organisation->id, $adherent->id]) }}"
                                              class="d-inline" onsubmit="return confirm('Confirmer la validation de {{ $adherent->nom }} {{ $adherent->prenom }} ?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-sm btn-danger" title="Rejeter"
                                                onclick="showRejectModal({{ $adherent->id }}, '{{ addslashes($adherent->nom . ' ' . $adherent->prenom) }}')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="card-footer bg-white">
                    {{ $pendingAdherents->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune inscription en attente</h5>
                    <p class="text-muted">Les nouvelles demandes d'adhésion apparaîtront ici.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal de rejet --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="reject-form">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle mr-2"></i>Rejeter l'inscription
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Rejeter l'inscription de <strong id="reject-name"></strong> ?</p>
                    <div class="form-group">
                        <label for="motif">Motif du rejet <span class="text-danger">*</span></label>
                        <textarea name="motif" id="motif" class="form-control" rows="3" required
                                  placeholder="Indiquez le motif du rejet..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i>Confirmer le rejet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal(adherentId, adherentName) {
    document.getElementById('reject-name').textContent = adherentName;
    var form = document.getElementById('reject-form');
    form.action = '/operator/organisations/{{ $organisation->id }}/inscription/' + adherentId + '/reject';
    document.getElementById('motif').value = '';
    $('#rejectModal').modal('show');
}
</script>
@endsection
