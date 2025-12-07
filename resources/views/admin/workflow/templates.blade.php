@extends('layouts.admin')

@section('title', 'Templates de Workflow')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Templates de Workflow</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Cette fonctionnalité permet de gérer les templates de workflow.
                            L'implémentation complète sera ajoutée prochainement.
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="text-center py-5">
                            <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                            <h4>Configuration des Templates</h4>
                            <p class="text-muted">
                                Ici vous pourrez créer et gérer des templates de workflow personnalisés
                                pour automatiser le traitement des dossiers.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection