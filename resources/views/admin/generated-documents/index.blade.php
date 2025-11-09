@extends('layouts.admin')

@section('title', 'Documents Générés')

@section('content')
<div class="container-fluid px-4 py-4">
    
    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" style="color: #003f7f; font-weight: 600;">
                <i class="fas fa-file-pdf mr-2"></i>Documents Générés
            </h1>
            <p class="text-muted mb-0">
                <small>Gestion des documents officiels générés par le système</small>
            </p>
        </div>
        <a href="{{ route('admin.documents.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-1"></i>Générer un document
        </a>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    {{-- Filtres --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.documents.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label small">
                            <i class="fas fa-search mr-1"></i>Recherche
                        </label>
                        <input type="text" 
                               name="search" 
                               class="form-control form-control-sm" 
                               placeholder="N° document, QR code..."
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label small">
                            <i class="fas fa-file-alt mr-1"></i>Template
                        </label>
                        <select name="template_id" class="form-control form-control-sm">
                            <option value="">Tous les templates</option>
                            @foreach(\App\Models\DocumentTemplate::where('is_active', true)->get() as $template)
                                <option value="{{ $template->id }}" {{ request('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label small">
                            <i class="fas fa-building mr-1"></i>Organisation
                        </label>
                        <select name="organisation_id" class="form-control form-control-sm">
                            <option value="">Toutes les organisations</option>
                            @foreach(\App\Models\Organisation::where('statut', 'approuve')->take(100)->get() as $org)
                                <option value="{{ $org->id }}" {{ request('organisation_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label small">
                            <i class="fas fa-check-circle mr-1"></i>Validité
                        </label>
                        <select name="is_valid" class="form-control form-control-sm">
                            <option value="">Tous</option>
                            <option value="true" {{ request('is_valid') === 'true' ? 'selected' : '' }}>Valides uniquement</option>
                            <option value="false" {{ request('is_valid') === 'false' ? 'selected' : '' }}>Invalidés uniquement</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter mr-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times mr-1"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau des documents --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background: linear-gradient(135deg, #003f7f 0%, #005fa3 100%); color: white;">
                            <tr>
                                <th class="border-0">N° Document</th>
                                <th class="border-0">Template</th>
                                <th class="border-0">Organisation</th>
                                <th class="border-0">Date génération</th>
                                <th class="border-0">Généré par</th>
                                <th class="border-0">Statut</th>
                                <th class="border-0 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                            <tr>
                                <td>
                                    <span class="badge badge-dark font-weight-normal" style="font-size: 0.85rem;">
                                        {{ $doc->numero_document }}
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-file-alt mr-1" style="color: #003f7f;"></i>
                                    {{ $doc->template->nom ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $doc->type_document }}</small>
                                </td>
                                <td>
                                    <strong>{{ $doc->organisation->nom ?? 'N/A' }}</strong>
                                    @if($doc->organisation)
                                        <br>
                                        <small class="text-muted">
                                            {{ $doc->organisation->organisationType->nom ?? '' }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <i class="fas fa-calendar mr-1" style="color: #009e3f;"></i>
                                    {{ $doc->generated_at->format('d/m/Y') }}
                                    <br>
                                    <small class="text-muted">{{ $doc->generated_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <i class="fas fa-user mr-1" style="color: #8b1538;"></i>
                                    {{ $doc->generatedBy->nom ?? 'Système' }}
                                </td>
                                <td>
                                    @if($doc->is_valid)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle mr-1"></i>Valide
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-ban mr-1"></i>Invalidé
                                        </span>
                                    @endif
                                    @if($doc->download_count > 0)
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-download"></i> {{ $doc->download_count }}x
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.documents.show', $doc) }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.documents.download', $doc) }}" 
                                           class="btn btn-sm btn-outline-success"
                                           title="Télécharger">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($doc->is_valid)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="invalidateDocument({{ $doc->id }})"
                                                    title="Invalider">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                <div class="p-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Affichage de {{ $documents->firstItem() }} à {{ $documents->lastItem() }} 
                            sur {{ $documents->total() }} documents
                        </div>
                        <div>
                            {{ $documents->links() }}
                        </div>
                    </div>
                </div>
            @else
                {{-- État vide --}}
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-file-pdf fa-5x text-muted" style="opacity: 0.3;"></i>
                    </div>
                    <h5 class="text-muted mb-3">Aucun document généré</h5>
                    <p class="text-muted mb-4">
                        Commencez par générer votre premier document officiel.
                    </p>
                    <a href="{{ route('admin.documents.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle mr-2"></i>Générer un document
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .table thead th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 158, 63, 0.05);
        transform: translateX(2px);
    }
    
    .badge {
        padding: 0.4em 0.65em;
        font-size: 0.75rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
</style>
@endpush

@push('scripts')
<script>
function invalidateDocument(documentId) {
    if (!confirm('Êtes-vous sûr de vouloir invalider ce document ?')) {
        return;
    }
    
    fetch(`/admin/documents/${documentId}/invalidate`, {
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
            alert('Erreur lors de l\'invalidation du document');
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