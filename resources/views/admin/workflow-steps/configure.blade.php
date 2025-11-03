@extends('layouts.admin')

@section('title', 'Configuration Workflow - Matrice Étapes × Entités')

@section('content')
@php
// Pas de conversion de dates nécessaire ici
@endphp
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-th text-primary"></i> Configuration du Workflow
            </h1>
            <p class="text-muted mb-0">Matrice de configuration Étapes × Entités de validation</p>
        </div>
        <div>
            <a href="{{ route('admin.workflow-steps.timeline', ['type_organisation' => $typeOrganisation, 'type_operation' => $typeOperation]) }}" 
               class="btn btn-info me-2">
                <i class="fas fa-stream"></i> Timeline
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
            <form method="GET" action="{{ route('admin.workflow-steps.configure') }}" id="filterForm">
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
                        <button type="button" class="btn btn-success w-100" onclick="saveConfiguration()">
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

    <!-- Matrice de configuration -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-success text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-th-large"></i> Matrice de Configuration
                <span class="badge bg-light text-dark ms-2">{{ count($steps) }} étapes × {{ count($entities) }} entités</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="configMatrix">
                    <thead class="table-light">
                        <tr>
                            <th width="3%" class="text-center">#</th>
                            <th width="25%">Étape de Workflow</th>
                            @foreach($entities as $entity)
                            <th width="{{ 72 / count($entities) }}%" class="text-center entity-column">
                                <div class="entity-header">
                                    <small class="text-muted d-block">{{ $entity->code }}</small>
                                    <strong>{{ $entity->nom }}</strong>
                                    <br>
                                    @php
                                    $typeColors = [
                                        'direction' => 'primary',
                                        'service' => 'info',
                                        'departement' => 'success',
                                        'commission' => 'warning',
                                        'externe' => 'secondary'
                                    ];
                                    @endphp
                                    <span class="badge bg-{{ $typeColors[$entity->type] ?? 'secondary' }} mt-1">
                                        {{ ucfirst($entity->type) }}
                                    </span>
                                </div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($steps as $step)
                        <tr data-step-id="{{ $step->id }}">
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $step->numero_passage }}</span>
                            </td>
                            <td>
                                <strong>{{ $step->libelle }}</strong>
                                <br>
                                <small class="text-muted">{{ $step->code }}</small>
                                <br>
                                <span class="badge bg-info mt-1">
                                    <i class="fas fa-clock"></i> {{ $step->delai_traitement }}h
                                </span>
                            </td>
                            @foreach($entities as $entity)
                            <td class="text-center align-middle entity-cell" 
                                data-step-id="{{ $step->id }}" 
                                data-entity-id="{{ $entity->id }}">
                                
                                @php
                                $isAssigned = in_array($entity->id, $matrix[$step->id]['entities']);
                                $details = $isAssigned ? $matrix[$step->id]['details'][$entity->id] : null;
                                @endphp
                                
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input entity-checkbox" 
                                           type="checkbox" 
                                           id="assign_{{ $step->id }}_{{ $entity->id }}"
                                           data-step-id="{{ $step->id }}"
                                           data-entity-id="{{ $entity->id }}"
                                           {{ $isAssigned ? 'checked' : '' }}>
                                </div>
                                
                                @if($isAssigned)
                                <div class="assignment-details mt-2">
                                    <span class="badge bg-secondary">Ordre: {{ $details->ordre }}</span>
                                    @if($details->is_optional)
                                    <br><span class="badge bg-warning text-dark mt-1">Optionnel</span>
                                    @else
                                    <br><span class="badge bg-success mt-1">Obligatoire</span>
                                    @endif
                                </div>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Légende -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-info-circle"></i> Légende
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Types d'entités</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge bg-primary">Direction</span> - Entité de direction</li>
                        <li><span class="badge bg-info">Service</span> - Service opérationnel</li>
                        <li><span class="badge bg-success">Département</span> - Département</li>
                        <li><span class="badge bg-warning text-dark">Commission</span> - Commission de validation</li>
                        <li><span class="badge bg-secondary">Externe</span> - Partenaire externe</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Statuts d'assignation</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-square text-success"></i> Coché = Entité assignée à l'étape</li>
                        <li><span class="badge bg-success">Obligatoire</span> - Validation obligatoire</li>
                        <li><span class="badge bg-warning text-dark">Optionnel</span> - Validation optionnelle</li>
                        <li><span class="badge bg-secondary">Ordre</span> - Ordre de passage des entités</li>
                    </ul>
                </div>
            </div>
            <div class="alert alert-info mb-0 mt-3">
                <i class="fas fa-lightbulb"></i> 
                <strong>Astuce :</strong> Cochez/décochez les cases pour assigner ou retirer les entités des étapes. 
                N'oubliez pas de cliquer sur <strong>"Enregistrer"</strong> pour sauvegarder vos modifications.
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.entity-column {
    min-width: 180px;
    vertical-align: middle;
}

.entity-header {
    padding: 10px 5px;
}

.entity-cell {
    background-color: #f8f9fa;
    transition: background-color 0.3s;
}

.entity-cell:hover {
    background-color: #e9ecef;
}

.entity-checkbox {
    width: 2.5rem;
    height: 2.5rem;
    cursor: pointer;
}

.assignment-details {
    font-size: 0.75rem;
}

#configMatrix tbody tr:hover {
    background-color: #f1f3f5;
}

.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}
</style>
@endpush

@push('scripts')
<script>
let matrixChanges = {};

$(document).ready(function() {
    // Gestion des checkboxes
    $('.entity-checkbox').on('change', function() {
        const stepId = $(this).data('step-id');
        const entityId = $(this).data('entity-id');
        const isChecked = $(this).is(':checked');
        
        // Enregistrer le changement
        if (!matrixChanges[stepId]) {
            matrixChanges[stepId] = [];
        }
        
        const index = matrixChanges[stepId].findIndex(e => e.entity_id === entityId);
        
        if (isChecked) {
            if (index === -1) {
                matrixChanges[stepId].push({
                    entity_id: entityId,
                    is_optional: 0
                });
            }
        } else {
            if (index !== -1) {
                matrixChanges[stepId].splice(index, 1);
            }
        }
        
        // Mettre à jour l'affichage
        const cell = $(this).closest('.entity-cell');
        const detailsDiv = cell.find('.assignment-details');
        
        if (isChecked && detailsDiv.length === 0) {
            const ordre = matrixChanges[stepId].length;
            cell.append(`
                <div class="assignment-details mt-2">
                    <span class="badge bg-secondary">Ordre: ${ordre}</span>
                    <br><span class="badge bg-success mt-1">Obligatoire</span>
                </div>
            `);
        } else if (!isChecked && detailsDiv.length > 0) {
            detailsDiv.remove();
        }
    });
});

// Sauvegarder la configuration
function saveConfiguration() {
    const typeOrganisation = $('#type_organisation').val();
    const typeOperation = $('#type_operation').val();
    
    // Construire les assignations finales
    const assignments = {};
    
    $('.entity-checkbox:checked').each(function() {
        const stepId = $(this).data('step-id');
        const entityId = $(this).data('entity-id');
        
        if (!assignments[stepId]) {
            assignments[stepId] = [];
        }
        
        assignments[stepId].push({
            entity_id: entityId,
            is_optional: 0
        });
    });
    
    // Afficher un loader
    showMessage('info', '<i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...');
    
    // Envoyer via AJAX
    $.ajax({
        url: '{{ route("admin.workflow-steps.configure.save") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            type_organisation: typeOrganisation,
            type_operation: typeOperation,
            assignments: assignments
        },
        success: function(response) {
            if (response.success) {
                showMessage('success', '<i class="fas fa-check-circle"></i> ' + response.message);
                matrixChanges = {};
                
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

// Configuration toastr
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000"
};
</script>
@endpush