@extends('layouts.admin')

@section('title', 'Détails de l\'Étape : ' . $step->libelle)

@section('content')
@php
// Conversion des dates string en objets Carbon
$createdAt = \Carbon\Carbon::parse($step->created_at);
$updatedAt = \Carbon\Carbon::parse($step->updated_at);
@endphp
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-project-diagram text-primary"></i> Détails de l'Étape
            </h1>
            <p class="text-muted mb-0">
                <code>{{ $step->code }}</code> - {{ $step->libelle }}
            </p>
        </div>
        <div>
            <a href="{{ route('admin.workflow-steps.edit', $step->id) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#duplicateModal">
                <i class="fas fa-copy"></i> Dupliquer
            </button>
            <a href="{{ route('admin.workflow-steps.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Colonne gauche -->
        <div class="col-lg-8">
            <!-- Informations générales -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Informations Générales
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Code</label>
                            <p class="mb-0"><strong><code>{{ $step->code }}</code></strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Numéro de Passage</label>
                            <p class="mb-0">
                                <span class="badge bg-secondary" style="font-size: 1rem;">
                                    Étape {{ $step->numero_passage }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Libellé</label>
                        <p class="mb-0"><strong>{{ $step->libelle }}</strong></p>
                    </div>

                    @if($step->description)
                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <p class="mb-0 text-justify">{{ $step->description }}</p>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Type d'Organisation</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">
                                    {{ ucfirst(str_replace('_', ' ', $step->type_organisation)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Type d'Opération</label>
                            <p class="mb-0">
                                <span class="badge bg-info">
                                    {{ ucfirst(str_replace('_', ' ', $step->type_operation)) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Statut</label>
                            <p class="mb-0">
                                @if($step->is_active)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Active
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle"></i> Inactive
                                </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Délai de Traitement</label>
                            <p class="mb-0">
                                <i class="fas fa-clock text-warning"></i> 
                                <strong>{{ $step->delai_traitement }} heures</strong>
                                <small class="text-muted">({{ round($step->delai_traitement / 24, 1) }} jours)</small>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Dates</label>
                            <p class="mb-0 small">
                                <i class="fas fa-calendar-plus text-success"></i> {{ $createdAt->format('d/m/Y') }}
                                @if($updatedAt->ne($createdAt))
                                <br><i class="fas fa-calendar-edit text-warning"></i> {{ $updatedAt->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paramètres de l'étape -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-cogs"></i> Paramètres de l'Étape
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                @if($step->permet_rejet)
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                @else
                                <i class="fas fa-times-circle text-danger fa-2x me-3"></i>
                                @endif
                                <div>
                                    <strong>Permet le Rejet</strong>
                                    <p class="mb-0 small text-muted">
                                        {{ $step->permet_rejet ? 'Oui' : 'Non' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                @if($step->permet_commentaire)
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                @else
                                <i class="fas fa-times-circle text-danger fa-2x me-3"></i>
                                @endif
                                <div>
                                    <strong>Permet Commentaires</strong>
                                    <p class="mb-0 small text-muted">
                                        {{ $step->permet_commentaire ? 'Oui' : 'Non' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                @if($step->genere_document)
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                @else
                                <i class="fas fa-times-circle text-danger fa-2x me-3"></i>
                                @endif
                                <div>
                                    <strong>Génère Document</strong>
                                    <p class="mb-0 small text-muted">
                                        {{ $step->genere_document ? 'Oui' : 'Non' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($step->genere_document && $step->template_document)
                    <div class="mt-3">
                        <label class="text-muted small">Template de Document</label>
                        <p class="mb-0">
                            <code>{{ $step->template_document }}</code>
                        </p>
                    </div>
                    @endif

                    @if($step->champs_requis)
                    <div class="mt-3">
                        <label class="text-muted small">Champs Requis</label>
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0 small">{{ json_encode(json_decode($step->champs_requis), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Entités de validation assignées -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-building"></i> Entités de Validation Assignées
                        @php
                        $entitiesCount = DB::table('workflow_step_entities')
                            ->where('workflow_step_id', $step->id)
                            ->count();
                        @endphp
                        <span class="badge bg-light text-dark ms-2">{{ $entitiesCount }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($entitiesCount > 0)
                    @php
                    $assignedEntities = DB::table('workflow_step_entities as wse')
                        ->join('validation_entities as ve', 'wse.validation_entity_id', '=', 've.id')
                        ->where('wse.workflow_step_id', $step->id)
                        ->select('ve.id', 've.code', 've.nom', 've.type', 'wse.ordre', 'wse.is_optional')
                        ->orderBy('wse.ordre')
                        ->get();
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">Ordre</th>
                                    <th width="30%">Entité</th>
                                    <th width="25%">Type</th>
                                    <th width="20%">Statut</th>
                                    <th width="15%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedEntities as $entity)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $entity->ordre }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $entity->nom }}</strong>
                                        <br><small class="text-muted">{{ $entity->code }}</small>
                                    </td>
                                    <td>
                                        @php
                                        $typeColors = [
                                            'direction' => 'primary',
                                            'service' => 'info',
                                            'departement' => 'success',
                                            'commission' => 'warning',
                                            'externe' => 'secondary'
                                        ];
                                        @endphp
                                        <span class="badge bg-{{ $typeColors[$entity->type] ?? 'secondary' }}">
                                            {{ ucfirst($entity->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($entity->is_optional)
                                        <span class="badge bg-warning">Optionnelle</span>
                                        @else
                                        <span class="badge bg-success">Obligatoire</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.validation-entities.show', $entity->id) }}" 
                                           class="btn btn-sm btn-info"
                                           data-bs-toggle="tooltip"
                                           title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Aucune entité de validation assignée à cette étape
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colonne droite - Statistiques -->
        <div class="col-lg-4">
            <!-- Statistiques de traitement -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar"></i> Statistiques de Traitement
                    </h6>
                </div>
                <div class="card-body">
                    @php
                    $stats = DB::table('dossier_validations')
                        ->where('workflow_step_id', $step->id)
                        ->selectRaw('
                            COUNT(*) as total,
                            SUM(CASE WHEN decision = "approuve" THEN 1 ELSE 0 END) as approuves,
                            SUM(CASE WHEN decision = "rejete" THEN 1 ELSE 0 END) as rejetes,
                            SUM(CASE WHEN decision = "en_attente" THEN 1 ELSE 0 END) as en_attente,
                            AVG(TIMESTAMPDIFF(HOUR, created_at, decided_at)) as delai_moyen,
                            MIN(TIMESTAMPDIFF(HOUR, created_at, decided_at)) as delai_min,
                            MAX(TIMESTAMPDIFF(HOUR, created_at, decided_at)) as delai_max
                        ')
                        ->first();
                    
                    $tauxApprobation = $stats->total > 0 ? round(($stats->approuves / $stats->total) * 100, 1) : 0;
                    $tauxRejet = $stats->total > 0 ? round(($stats->rejetes / $stats->total) * 100, 1) : 0;
                    @endphp

                    <!-- Total dossiers -->
                    <div class="mb-3 p-3 bg-light rounded">
                        <h3 class="mb-0 text-primary">{{ $stats->total ?? 0 }}</h3>
                        <small class="text-muted">Dossiers Traités</small>
                    </div>

                    <!-- Répartition des décisions -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-check text-success"></i> Approuvés</span>
                            <strong>{{ $stats->approuves ?? 0 }} ({{ $tauxApprobation }}%)</strong>
                        </div>
                        <div class="progress mb-3" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $tauxApprobation }}%" 
                                 aria-valuenow="{{ $tauxApprobation }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $tauxApprobation }}%
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-times text-danger"></i> Rejetés</span>
                            <strong>{{ $stats->rejetes ?? 0 }} ({{ $tauxRejet }}%)</strong>
                        </div>
                        <div class="progress mb-3" style="height: 20px;">
                            <div class="progress-bar bg-danger" role="progressbar" 
                                 style="width: {{ $tauxRejet }}%" 
                                 aria-valuenow="{{ $tauxRejet }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $tauxRejet }}%
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-clock text-warning"></i> En attente</span>
                            <strong>{{ $stats->en_attente ?? 0 }}</strong>
                        </div>
                    </div>

                    <!-- Délais de traitement -->
                    @if($stats->total > 0)
                    <hr>
                    <div class="mt-3">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-stopwatch"></i> Délais de Traitement
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <strong>Moyen :</strong> 
                                <span class="text-{{ $stats->delai_moyen <= $step->delai_traitement ? 'success' : 'danger' }}">
                                    {{ $stats->delai_moyen ? round($stats->delai_moyen, 1) . 'h' : 'N/A' }}
                                </span>
                            </li>
                            <li class="mb-2">
                                <strong>Minimum :</strong> 
                                <span class="text-success">
                                    {{ $stats->delai_min ? round($stats->delai_min, 1) . 'h' : 'N/A' }}
                                </span>
                            </li>
                            <li class="mb-2">
                                <strong>Maximum :</strong> 
                                <span class="text-warning">
                                    {{ $stats->delai_max ? round($stats->delai_max, 1) . 'h' : 'N/A' }}
                                </span>
                            </li>
                            <li>
                                <strong>Objectif :</strong> 
                                <span class="text-info">{{ $step->delai_traitement }}h</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Performance -->
                    @php
                    $performance = $stats->delai_moyen <= $step->delai_traitement ? 'Excellente' : 
                                   ($stats->delai_moyen <= $step->delai_traitement * 1.5 ? 'Bonne' : 'À améliorer');
                    $performanceClass = $stats->delai_moyen <= $step->delai_traitement ? 'success' : 
                                        ($stats->delai_moyen <= $step->delai_traitement * 1.5 ? 'warning' : 'danger');
                    @endphp
                    <div class="alert alert-{{ $performanceClass }} mt-3 mb-0">
                        <i class="fas fa-trophy"></i> <strong>Performance :</strong> {{ $performance }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt"></i> Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.workflow-steps.edit', $step->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifier l'Étape
                        </a>
                        
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#duplicateModal">
                            <i class="fas fa-copy"></i> Dupliquer l'Étape
                        </button>

                        @if($stats->total > 0)
                        <button type="button" class="btn btn-primary" onclick="loadStatistics()">
                            <i class="fas fa-chart-line"></i> Statistiques Détaillées
                        </button>
                        @endif

                        <form action="{{ route('admin.workflow-steps.toggle-status', $step->id) }}" 
                              method="POST" 
                              class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="btn btn-{{ $step->is_active ? 'danger' : 'success' }} w-100">
                                <i class="fas fa-{{ $step->is_active ? 'power-off' : 'check' }}"></i> 
                                {{ $step->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>

                        <button type="button" 
                                class="btn btn-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Informations système -->
            <div class="card shadow border-left-info">
                <div class="card-body">
                    <h6 class="text-info">
                        <i class="fas fa-database"></i> Informations Système
                    </h6>
                    <ul class="small mb-0">
                        <li class="mb-2"><strong>ID :</strong> {{ $step->id }}</li>
                        <li class="mb-2"><strong>Créé le :</strong> {{ $createdAt->format('d/m/Y à H:i') }}</li>
                        <li class="mb-2"><strong>Modifié le :</strong> {{ $updatedAt->format('d/m/Y à H:i') }}</li>
                        <li><strong>Dernière modification :</strong> {{ $updatedAt->diffForHumans() }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Duplication -->
<div class="modal fade" id="duplicateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-copy"></i> Dupliquer l'Étape
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.workflow-steps.duplicate', $step->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Voulez-vous dupliquer cette étape ?</p>
                    <p class="text-muted small">
                        <i class="fas fa-info-circle"></i> 
                        Une copie sera créée avec un nouveau code généré automatiquement.
                    </p>
                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Vous devrez modifier le code et le numéro de passage de la copie.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-copy"></i> Dupliquer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmer la Suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.workflow-steps.destroy', $step->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Voulez-vous vraiment supprimer cette étape ?</p>
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-circle"></i> Attention !
                        </h6>
                        <p class="mb-0 small">
                            Cette action est <strong>irréversible</strong>. 
                            @if($stats->total > 0)
                            <br>Cette étape a déjà traité <strong>{{ $stats->total }} dossiers</strong>.
                            @endif
                        </p>
                    </div>
                    <p class="text-muted small mb-0">
                        Étape : <strong>{{ $step->libelle }}</strong> ({{ $step->code }})
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Supprimer Définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialiser les tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Configuration toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };
});

// Charger les statistiques détaillées (AJAX)
function loadStatistics() {
    $.ajax({
        url: "{{ route('admin.workflow-steps.statistics', $step->id) }}",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Afficher les statistiques dans une modale ou page dédiée
                alert('Fonctionnalité de statistiques détaillées à venir !');
            }
        },
        error: function() {
            toastr.error('Erreur lors du chargement des statistiques');
        }
    });
}
</script>
@endpush