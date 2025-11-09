@extends('layouts.admin')

@section('title', 'Détails du document ' . $generation->numero_document)

@section('content')
<div class="container-fluid px-4 py-4">
    
    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home"></i>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.documents.index') }}">Documents générés</a>
            </li>
            <li class="breadcrumb-item active">{{ $generation->numero_document }}</li>
        </ol>
    </nav>

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-2" style="color: #003f7f; font-weight: 600;">
                <i class="fas fa-file-pdf mr-2"></i>{{ $generation->numero_document }}
            </h1>
            <p class="text-muted mb-0">
                <small>Généré le {{ $generation->generated_at->format('d/m/Y à H:i') }}</small>
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.documents.download', $generation) }}" 
               class="btn btn-success">
                <i class="fas fa-download mr-1"></i>Télécharger PDF
            </a>
            @if($generation->is_valid)
                <button type="button" 
                        class="btn btn-danger"
                        onclick="invalidateDocument()">
                    <i class="fas fa-ban mr-1"></i>Invalider
                </button>
            @else
                <button type="button" 
                        class="btn btn-primary"
                        onclick="reactivateDocument()">
                    <i class="fas fa-check-circle mr-1"></i>Réactiver
                </button>
            @endif
        </div>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <div class="row">
        {{-- Informations principales --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #003f7f 0%, #005fa3 100%); color: white;">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle mr-2"></i>Informations du document
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small mb-1">N° Document</label>
                            <div class="font-weight-bold" style="color: #003f7f;">
                                {{ $generation->numero_document }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small mb-1">Type de document</label>
                            <div class="font-weight-bold">
                                {{ $generation->type_document }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small mb-1">Template utilisé</label>
                            <div>
                                <i class="fas fa-file-alt mr-1" style="color: #009e3f;"></i>
                                {{ $generation->template->nom ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small mb-1">Statut</label>
                            <div>
                                @if($generation->is_valid)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle mr-1"></i>Valide
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fas fa-ban mr-1"></i>Invalidé
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Organisation --}}
            @if($generation->organisation)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); color: white;">
                    <h5 class="mb-0">
                        <i class="fas fa-building mr-2"></i>Organisation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="text-muted small mb-1">Nom de l'organisation</label>
                            <div class="font-weight-bold">
                                {{ $generation->organisation->nom }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small mb-1">Type</label>
                            <div>
                                {{ $generation->organisation->organisationType->nom ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small mb-1">Sigle</label>
                            <div>
                                {{ $generation->organisation->sigle ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Vérifications --}}
            @if($generation->verifications && $generation->verifications->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #ffcd00 0%, #ffd633 100%); color: #1e3a8a;">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode mr-2"></i>Historique des vérifications
                        <span class="badge badge-dark ml-2">{{ $generation->verifications->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>IP</th>
                                    <th>Résultat</th>
                                    <th>Méthode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($generation->verifications as $verif)
                                <tr>
                                    <td>{{ $verif->verified_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <small class="text-muted">{{ $verif->ip_address }}</small>
                                    </td>
                                    <td>
                                        @if($verif->verification_reussie)
                                            <span class="badge badge-success badge-sm">
                                                <i class="fas fa-check"></i> Succès
                                            </span>
                                        @else
                                            <span class="badge badge-danger badge-sm">
                                                <i class="fas fa-times"></i> Échec
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $verif->methode_verification }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4 mb-4">
            {{-- QR Code --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <h6 class="mb-3" style="color: #003f7f;">Code QR de vérification</h6>
                    @if($generation->qr_code_svg)
                        <div class="mb-3">
                            {!! $generation->qr_code_svg !!}
                        </div>
                    @else
                        <div class="mb-3">
                            <i class="fas fa-qrcode fa-5x text-muted"></i>
                        </div>
                    @endif
                    <small class="text-muted d-block">
                        {{ $generation->qr_code_token }}
                    </small>
                </div>
            </div>

            {{-- Statistiques --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line mr-2"></i>Statistiques
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Téléchargements</label>
                        <div class="font-weight-bold" style="font-size: 1.5rem; color: #009e3f;">
                            <i class="fas fa-download mr-2"></i>{{ $generation->download_count }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Vérifications</label>
                        <div class="font-weight-bold" style="font-size: 1.5rem; color: #003f7f;">
                            <i class="fas fa-search mr-2"></i>{{ $generation->verifications->count() ?? 0 }}
                        </div>
                    </div>
                    @if($generation->last_downloaded_at)
                    <div>
                        <label class="text-muted small mb-1">Dernier téléchargement</label>
                        <div class="small">
                            {{ $generation->last_downloaded_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Métadonnées --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle mr-2"></i>Métadonnées
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Généré par</label>
                        <div>
                            <i class="fas fa-user mr-1"></i>
                            {{ $generation->generatedBy->nom ?? 'Système' }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Date de génération</label>
                        <div>
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $generation->generated_at->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                    @if($generation->invalidated_at)
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Invalidé le</label>
                        <div class="text-danger">
                            <i class="fas fa-ban mr-1"></i>
                            {{ $generation->invalidated_at->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Invalidé par</label>
                        <div class="text-danger">
                            {{ $generation->invalidatedBy->nom ?? 'N/A' }}
                        </div>
                    </div>
                    @if($generation->invalidation_reason)
                    <div>
                        <label class="text-muted small mb-1">Raison</label>
                        <div class="small">
                            {{ $generation->invalidation_reason }}
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function invalidateDocument() {
    const reason = prompt('Raison de l\'invalidation (optionnel):');
    
    if (reason === null) return; // Annulé
    
    fetch('{{ route("admin.documents.invalidate", $generation) }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors de l\'invalidation');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur système');
    });
}

function reactivateDocument() {
    if (!confirm('Confirmer la réactivation de ce document ?')) {
        return;
    }
    
    fetch('{{ route("admin.documents.reactivate", $generation) }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors de la réactivation');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur système');
    });
}
</script>
@endpush
@endsection