@extends('layouts.admin')

@section('title', 'Gestion des Étapes de Workflow')

@section('content')
<div class="container-fluid">
    
    <!-- En-tête de page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="fas fa-tasks text-primary"></i> Gestion des Étapes de Workflow
            </h1>
            <p class="text-muted mb-0">Configurez les étapes du processus de validation des dossiers</p>
        </div>
        <div>
            <a href="{{ route('admin.workflow-steps.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nouvelle Étape
            </a>
            <a href="{{ route('admin.workflow-steps.export') }}" class="btn btn-outline-secondary">
                <i class="fas fa-download"></i> Export JSON
            </a>
        </div>
    </div>

    <!-- Messages de feedback -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Étapes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_steps'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Étapes Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_steps'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Étapes Inactives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['inactive_steps'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Délai Moyen
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['avg_step_duration'] ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtres
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.workflow-steps.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Recherche</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   placeholder="Code, libellé..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type_organisation">Type Organisation</label>
                            <select class="form-control" id="type_organisation" name="type_organisation">
                                <option value="all" {{ request('type_organisation') == 'all' ? 'selected' : '' }}>
                                    Tous les types
                                </option>
                                @foreach($typesOrganisations as $key => $label)
                                    <option value="{{ $key }}" {{ request('type_organisation') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type_operation">Type Opération</label>
                            <select class="form-control" id="type_operation" name="type_operation">
                                <option value="all" {{ request('type_operation') == 'all' ? 'selected' : '' }}>
                                    Toutes les opérations
                                </option>
                                @foreach($typesOperations as $key => $label)
                                    <option value="{{ $key }}" {{ request('type_operation') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <select class="form-control" id="statut" name="statut">
                                <option value="">Tous les statuts</option>
                                <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Actives</option>
                                <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Inactives</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.workflow-steps.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table des étapes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Liste des Étapes de Workflow
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="workflowStepsTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Code</th>
                            <th width="20%">Libellé</th>
                            <th width="12%">Type Org.</th>
                            <th width="12%">Type Op.</th>
                            <th width="8%" class="text-center">Ordre</th>
                            <th width="10%" class="text-center">Délai</th>
                            <th width="10%" class="text-center">Entités</th>
                            <th width="8%" class="text-center">Statut</th>
                            <th width="15%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($steps as $step)
                            <tr>
                                <td>{{ $step->id }}</td>
                                <td>
                                    <code class="badge badge-secondary">{{ $step->code }}</code>
                                </td>
                                <td>
                                    <strong>{{ $step->libelle }}</strong>
                                    @if($step->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($step->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $orgColors = [
                                            'association' => 'primary',
                                            'ong' => 'success',
                                            'parti_politique' => 'danger',
                                            'confession_religieuse' => 'info'
                                        ];
                                        $color = $orgColors[$step->type_organisation] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $color }}">
                                        {{ ucfirst(str_replace('_', ' ', $step->type_organisation)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-light border">
                                        {{ ucfirst(str_replace('_', ' ', $step->type_operation)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-dark">
                                        {{ $step->numero_passage }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <i class="fas fa-clock text-warning"></i> 
                                    {{ $step->delai_traitement }}h
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info badge-pill">
                                        {{ $step->entities_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                               class="custom-control-input toggle-status" 
                                               id="status{{ $step->id }}" 
                                               data-id="{{ $step->id }}"
                                               {{ $step->is_active ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="status{{ $step->id }}"></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.workflow-steps.show', $step->id) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.workflow-steps.edit', $step->id) }}" 
                                           class="btn btn-sm btn-warning" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-secondary btn-duplicate" 
                                                data-id="{{ $step->id }}"
                                                title="Dupliquer">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger btn-delete" 
                                                data-id="{{ $step->id }}"
                                                data-libelle="{{ $step->libelle }}"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Aucune étape de workflow trouvée.</p>
                                    <a href="{{ route('admin.workflow-steps.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Créer la première étape
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($steps->hasPages())
                <div class="mt-3">
                    {{ $steps->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirmation de suppression
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer l'étape <strong id="stepLibelle"></strong> ?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Attention :</strong> Cette action est irréversible. Assurez-vous qu'aucun dossier n'utilise cette étape.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Confirmer la suppression
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-left-primary {
        border-left: 0.25rem solid #009e3f !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #009e3f !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #ffcd00 !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #003f7f !important;
    }
    .text-primary {
        color: #009e3f !important;
    }
    .btn-primary {
        background-color: #009e3f;
        border-color: #009e3f;
    }
    .btn-primary:hover {
        background-color: #007a31;
        border-color: #007a31;
    }
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #009e3f;
        border-color: #009e3f;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 158, 63, 0.05);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    
    // Toggle status AJAX
    $('.toggle-status').on('change', function() {
        const stepId = $(this).data('id');
        const isChecked = $(this).is(':checked');
        const switchElement = $(this);
        
        $.ajax({
            url: `/admin/workflow-steps/${stepId}/toggle-status`,
            type: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Erreur lors de la modification du statut');
                    switchElement.prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                toastr.error('Erreur lors de la modification du statut');
                switchElement.prop('checked', !isChecked);
            }
        });
    });
    
    // Bouton dupliquer
    $('.btn-duplicate').on('click', function() {
        const stepId = $(this).data('id');
        
        if (confirm('Voulez-vous dupliquer cette étape ?')) {
            $.ajax({
                url: `/admin/workflow-steps/${stepId}/duplicate`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Étape dupliquée avec succès');
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
                        toastr.error(response.message || 'Erreur lors de la duplication');
                    }
                },
                error: function(xhr) {
                    toastr.error('Erreur lors de la duplication');
                }
            });
        }
    });
    
    // Bouton supprimer
    $('.btn-delete').on('click', function() {
        const stepId = $(this).data('id');
        const libelle = $(this).data('libelle');
        
        $('#stepLibelle').text(libelle);
        $('#deleteForm').attr('action', `/admin/workflow-steps/${stepId}`);
        $('#deleteModal').modal('show');
    });
    
    // Configuration toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };
});
</script>
@endpush