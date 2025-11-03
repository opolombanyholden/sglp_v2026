@extends('layouts.admin')

@section('title', 'Timeline Workflow - Réorganisation des Étapes')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-stream text-primary"></i> Timeline du Workflow
            </h1>
            <p class="text-muted mb-0">Réorganisation visuelle des étapes par glisser-déposer</p>
        </div>
        <div>
            <a href="{{ route('admin.workflow-steps.configure', ['type_organisation' => $typeOrganisation, 'type_operation' => $typeOperation]) }}" 
               class="btn btn-success me-2">
                <i class="fas fa-th"></i> Matrice
            </a>
            <a href="{{ route('admin.workflow-steps.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-filter"></i> Filtres
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.workflow-steps.timeline') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-5">
                        <label for="type_organisation" class="form-label">Type d'Organisation</label>
                        <select class="form-select" id="type_organisation" name="type_organisation" onchange="document.getElementById('filterForm').submit()">
                            @foreach($typesOrganisation as $key => $label)
                            <option value="{{ $key }}" {{ $typeOrganisation == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="type_operation" class="form-label">Type d'Opération</label>
                        <select class="form-select" id="type_operation" name="type_operation" onchange="document.getElementById('filterForm').submit()">
                            @foreach($typesOperation as $key => $label)
                            <option value="{{ $key }}" {{ $typeOperation == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-success w-100" onclick="saveOrder()">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages -->
    <div id="messageContainer"></div>

    @if(count($steps) == 0)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        Aucune étape de workflow n'est configurée pour ce type d'organisation et d'opération.
        <a href="{{ route('admin.workflow-steps.create') }}" class="alert-link">Créer une étape</a>
    </div>
    @else

    <!-- Instructions -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Instructions :</strong> Glissez-déposez les étapes pour modifier leur ordre. 
        Cliquez sur <strong>"Enregistrer"</strong> pour sauvegarder les modifications.
    </div>

    <!-- Timeline horizontale -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-success text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-project-diagram"></i> Timeline du Workflow
                <span class="badge bg-light text-dark ms-2">{{ count($steps) }} étapes</span>
            </h6>
        </div>
        <div class="card-body">
            <div id="timeline-container" class="timeline-horizontal">
                <div id="workflow-steps" class="workflow-steps-container">
                    @foreach($steps as $index => $step)
                    <div class="workflow-step-card" 
                         data-step-id="{{ $step->id }}" 
                         data-original-order="{{ $index + 1 }}">
                        
                        <!-- Numéro de l'étape -->
                        <div class="step-number">
                            <span class="badge bg-primary badge-lg">{{ $index + 1 }}</span>
                        </div>

                        <!-- Contenu de la carte -->
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-grip-vertical drag-handle"></i>
                                        <strong>Étape {{ $step->numero_passage }}</strong>
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        {{ $step->delai_traitement }}h
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $step->libelle }}</h5>
                                <p class="card-text text-muted small">
                                    <code>{{ $step->code }}</code>
                                </p>
                                
                                @if($step->description)
                                <p class="card-text small">{{ Str::limit($step->description, 80) }}</p>
                                @endif

                                <!-- Entités assignées -->
                                @if(count($step->entities) > 0)
                                <div class="mt-3">
                                    <strong class="small">Entités assignées:</strong>
                                    <div class="mt-2">
                                        @foreach($step->entities as $entity)
                                        @php
                                        $typeColors = [
                                            'direction' => 'primary',
                                            'service' => 'info',
                                            'departement' => 'success',
                                            'commission' => 'warning',
                                            'externe' => 'secondary'
                                        ];
                                        @endphp
                                        <span class="badge bg-{{ $typeColors[$entity->type] ?? 'secondary' }} me-1 mb-1">
                                            {{ $entity->nom }}
                                        </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Actions -->
                                <div class="mt-3 d-flex justify-content-between">
                                    <a href="{{ route('admin.workflow-steps.show', $step->id) }}" 
                                       class="btn btn-sm btn-info"
                                       target="_blank">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    <a href="{{ route('admin.workflow-steps.edit', $step->id) }}" 
                                       class="btn btn-sm btn-warning"
                                       target="_blank">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Flèche vers l'étape suivante -->
                        @if(!$loop->last)
                        <div class="step-arrow">
                            <i class="fas fa-arrow-right text-primary fa-2x"></i>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-chart-bar"></i> Statistiques
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3 class="text-primary">{{ count($steps) }}</h3>
                        <p class="text-muted mb-0">Étapes totales</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        @php
                        $totalEntities = collect($steps)->sum(function($step) {
                            return count($step->entities);
                        });
                        @endphp
                        <h3 class="text-success">{{ $totalEntities }}</h3>
                        <p class="text-muted mb-0">Entités assignées</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        @php
                        $totalDelai = collect($steps)->sum('delai_traitement');
                        @endphp
                        <h3 class="text-info">{{ $totalDelai }}h</h3>
                        <p class="text-muted mb-0">Délai total</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        @php
                        $avgDelai = count($steps) > 0 ? round($totalDelai / count($steps), 1) : 0;
                        @endphp
                        <h3 class="text-warning">{{ $avgDelai }}h</h3>
                        <p class="text-muted mb-0">Délai moyen/étape</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.timeline-horizontal {
    overflow-x: auto;
    overflow-y: hidden;
    padding: 20px 0;
}

.workflow-steps-container {
    display: flex;
    gap: 30px;
    min-width: min-content;
    padding: 10px;
}

.workflow-step-card {
    display: flex;
    align-items: center;
    position: relative;
}

.workflow-step-card .card {
    width: 320px;
    min-height: 300px;
    cursor: move;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.workflow-step-card .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.workflow-step-card.sortable-drag {
    opacity: 0.5;
}

.workflow-step-card.sortable-ghost {
    opacity: 0.3;
}

.step-number {
    position: absolute;
    top: -10px;
    left: 10px;
    z-index: 10;
}

.step-number .badge-lg {
    font-size: 1.2rem;
    padding: 0.5rem 0.8rem;
}

.step-arrow {
    display: flex;
    align-items: center;
    margin: 0 10px;
}

.drag-handle {
    cursor: grab;
    margin-right: 10px;
}

.drag-handle:active {
    cursor: grabbing;
}

.stat-card {
    text-align: center;
    padding: 20px;
    border-radius: 8px;
    background: #f8f9fa;
}

.stat-card h3 {
    margin-bottom: 5px;
    font-weight: bold;
}

/* Animation pour le drag & drop */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.workflow-step-card.sortable-chosen {
    animation: pulse 1s infinite;
}
</style>
@endpush

@push('scripts')
<!-- SortableJS pour le drag & drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
let sortable;
let hasChanges = false;

$(document).ready(function() {
    initSortable();
});

// Initialiser Sortable.js
function initSortable() {
    const container = document.getElementById('workflow-steps');
    
    if (!container) return;
    
    sortable = Sortable.create(container, {
        animation: 150,
        handle: '.card',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
            hasChanges = true;
            updateStepNumbers();
        }
    });
}

// Mettre à jour les numéros des étapes après réorganisation
function updateStepNumbers() {
    $('.workflow-step-card').each(function(index) {
        $(this).find('.step-number .badge').text(index + 1);
        $(this).find('.card-header strong').text('Étape ' + (index + 1));
    });
}

// Sauvegarder le nouvel ordre
function saveOrder() {
    if (!hasChanges) {
        showMessage('info', '<i class="fas fa-info-circle"></i> Aucune modification à enregistrer');
        return;
    }
    
    // Récupérer l'ordre actuel des IDs
    const orderedIds = [];
    $('.workflow-step-card').each(function() {
        orderedIds.push($(this).data('step-id'));
    });
    
    const typeOrganisation = $('#type_organisation').val();
    const typeOperation = $('#type_operation').val();
    
    // Afficher un loader
    showMessage('info', '<i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...');
    
    // Envoyer via AJAX
    $.ajax({
        url: '{{ route("admin.workflow-steps.reorder") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            type_organisation: typeOrganisation,
            type_operation: typeOperation,
            step_ids: orderedIds
        },
        success: function(response) {
            if (response.success) {
                showMessage('success', '<i class="fas fa-check-circle"></i> ' + response.message);
                hasChanges = false;
                
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showMessage('danger', '<i class="fas fa-exclamation-circle"></i> ' + response.message);
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Erreur lors de la sauvegarde';
            showMessage('danger', '<i class="fas fa-times-circle"></i> ' + message);
        }
    });
}

// Afficher un message
function showMessage(type, message) {
    const alertClass = type === 'info' ? 'alert-info' : 
                       type === 'success' ? 'alert-success' : 
                       'alert-danger';
    
    $('#messageContainer').html(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    // Auto-dismiss après 5 secondes sauf pour info
    if (type !== 'info') {
        setTimeout(() => {
            $('#messageContainer .alert').fadeOut();
        }, 5000);
    }
}

// Avertir si l'utilisateur quitte sans sauvegarder
window.addEventListener('beforeunload', function (e) {
    if (hasChanges) {
        e.preventDefault();
        e.returnValue = 'Vous avez des modifications non enregistrées. Voulez-vous vraiment quitter ?';
        return e.returnValue;
    }
});
</script>
@endpush