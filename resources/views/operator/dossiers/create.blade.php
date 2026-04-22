@extends('layouts.operator')

@section('title', 'Créer une Organisation')
@section('page-title', 'Nouvelle Organisation')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/organisation-create.css') }}">

    <!-- CSS Styles intégrés pour l'interface modernisée -->
    <style>
        /* ===== STYLES POUR L'INTERFACE MODERNISÉE ===== */

        .alert-gradient {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
            border-left: 4px solid #0d6efd;
        }

        .alert-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: rgba(13, 110, 253, 0.1);
            border-radius: 50%;
            flex-shrink: 0;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%) !important;
        }

        /* ===== STYLES POUR LES CARTES D'ORGANISATION ===== */
        .organization-type-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e9ecef !important;
        }

        .organization-type-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-color: #007bff !important;
        }

        .organization-type-card.selected,
        .organization-type-card:has(input:checked) {
            border-color: #007bff !important;
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.05) 0%, rgba(0, 123, 255, 0.1) 100%);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.2);
        }

        .org-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            transition: all 0.3s ease;
        }

        .organization-type-card:hover .org-icon {
            transform: scale(1.1);
        }

        .organization-type-card .form-check-input {
            display: none;
        }

        .organization-type-card .form-check-label {
            cursor: pointer;
            width: 100%;
        }

        /* ===== STYLES POUR LES INDICATEURS D'ÉTAPES ===== */
        .step-indicator {
            text-align: center;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .step-indicator .step-icon {
            display: block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            margin: 0 auto 0.5rem;
            transition: all 0.3s ease;
        }

        .step-indicator.active .step-icon {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .step-indicator.completed .step-icon {
            background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
            color: white;
        }

        .step-indicator.completed .step-icon::before {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
        }

        .step-indicator small {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 500;
        }

        .step-indicator.active small {
            color: #28a745;
            font-weight: 600;
        }

        .step-indicator.completed small {
            color: #007bff;
            font-weight: 600;
        }

        /* ===== ICÔNE D'ÉTAPE LARGE ===== */
        .step-icon-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* ===== ANIMATIONS ===== */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Card hover effects */
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Button loading states */
        .btn.loading {
            pointer-events: none;
        }

        .btn.loading::after {
            content: '';
            display: inline-block;
            width: 1rem;
            height: 1rem;
            margin-left: 0.5rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Progress Modal Styles */
        .modal-content {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .progress {
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            transition: width 0.3s ease;
        }

        /* Badge styles */
        .badge {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .d-flex.gap-3 {
                gap: 0.5rem !important;
                flex-wrap: wrap;
            }

            .step-indicator {
                margin-bottom: 0.5rem;
            }

            .step-indicator .step-icon {
                width: 30px;
                height: 30px;
                line-height: 30px;
            }

            .organization-type-card {
                margin-bottom: 1rem;
            }
        }

        /* Dark theme support */
        @media (prefers-color-scheme: dark) {
            .step-indicator small {
                color: #adb5bd;
            }
        }

        /* Custom checkbox styles */
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        /* Tooltip styles */
        [title] {
            cursor: help;
        }

        /* Loading states */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        /* ===== VALIDATION STATES ===== */
        .is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .is-valid {
            border-color: #198754 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.64 1.64 3.62-3.62.94.94-4.56 4.56z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        /* ===== REQUIRED FIELD INDICATOR ===== */
        .required::after {
            content: " *";
            color: #dc3545;
        }

        /* ===== STEP CONTENT DISPLAY ===== */
        .step-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .step-content:first-child {
            display: block;
        }


        /* =====<!-- Styles workflow 2 phases intégrés -->  WORKFLOW 2 PHASES - STYLES INTÉGRÉS ===== */

        .phase-banner {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .phase-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="gabon-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23gabon-pattern)"/></svg>');
            opacity: 0.3;
        }

        .phase-content {
            position: relative;
            z-index: 2;
        }

        .workflow-progress-mini {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 0.5rem 1rem;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .next-phase-info {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
            border-left: 4px solid #ffc107;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
        }

        .adherents-preview {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            border: 2px dashed #dee2e6;
        }

        .phase-indicator-mini {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .phase-step-mini {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .phase-step-mini.completed {
            background: #28a745;
            color: white;
        }

        .phase-step-mini.current {
            background: #ffc107;
            color: #333;
        }

        .phase-step-mini.pending {
            background: #6c757d;
            color: white;
        }

        .submission-choice {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .submission-choice:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .submission-choice.selected {
            border-color: #007bff;
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.05) 0%, rgba(0, 123, 255, 0.1) 100%);
        }

        .submission-choice input[type="radio"] {
            display: none;
        }

        /* Cartes radio de soumission */
        .submission-radio-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border-color: #dee2e6 !important;
        }

        .submission-radio-card:hover {
            border-color: #007bff !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .submission-radio-card.selected-green {
            border-color: #28a745 !important;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(32, 201, 151, 0.08));
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.25);
        }

        .submission-radio-card.selected-blue {
            border-color: #007bff !important;
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.05), rgba(102, 16, 242, 0.08));
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .cursor-pointer { cursor: pointer; }

        /* Recap table */
        .recap-table th {
            background: #f8f9fa;
            width: 35%;
            font-weight: 600;
            color: #002B7F;
        }

        .recap-section-title {
            background: #002B7F;
            color: white;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .choice-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .btn-phase-primary {
            background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-phase-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 158, 63, 0.4);
            color: white;
        }

        .btn-phase-secondary {
            background: white;
            border: 2px solid #009e3f;
            color: #009e3f;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-phase-secondary:hover {
            background: #009e3f;
            color: white;
            transform: translateY(-2px);
        }

        /* Animation pour modal workflow */
        .modal-workflow .modal-content {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .modal-workflow .modal-header {
            background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);
            color: white;
            border: none;
            padding: 1.5rem;
        }

        .modal-workflow .modal-body {
            padding: 2rem;
        }

        .workflow-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .step-connector {
            width: 50px;
            height: 3px;
            background: #dee2e6;
            border-radius: 2px;
        }

        .step-connector.active {
            background: #28a745;
        }

        @media (max-width: 768px) {
            .workflow-steps {
                flex-direction: column;
                gap: 0.5rem;
            }

            .step-connector {
                width: 3px;
                height: 30px;
            }

            .submission-choice {
                margin-bottom: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <!-- ========== SECTION A - HEADER ET NAVIGATION ========== -->
    <div class="container-fluid">
        <!-- Header avec navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                    <div class="card-body text-white">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <nav aria-label="breadcrumb" class="mb-2">
                                    <ol class="breadcrumb text-white-50 mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('operator.dashboard') }}" class="text-white opacity-75">
                                                <i class="fas fa-home me-1"></i>Dashboard
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('operator.organisations.index') }}"
                                                class="text-white opacity-75">Organisations</a>
                                        </li>
                                        <li class="breadcrumb-item active text-white">Nouvelle Organisation</li>
                                    </ol>
                                </nav>
                                <h2 class="mb-2">
                                    <i class="fas fa-building me-2"></i>
                                    Création d'une Nouvelle Organisation
                                </h2>
                                <p class="mb-0 opacity-90">Assistant de création guidée en <span id="totalSteps">8</span>
                                    étapes</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('operator.organisations.index') }}" class="btn btn-light">
                                        <i class="fas fa-arrow-left me-2"></i>Retour
                                    </a>
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#helpModal">
                                        <i class="fas fa-question-circle me-2"></i>Aide
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicateur de sauvegarde amélioré -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-end">
                    <small id="save-indicator" class="text-muted me-2">
                        <i class="fas fa-circle text-muted"></i> En attente
                    </small>
                </div>
            </div>
        </div>

        <!-- Barre de progression globale -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Progression</h6>
                            <small class="text-muted">Étape <span id="currentStepNumber">1</span> sur <span
                                    id="totalStepsDisplay">8</span></small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: 12.5%" id="globalProgress"></div>
                        </div>
                        <div class="row mt-3 step-indicators">
                            <div class="col step-indicator active" data-step="1">
                                <i class="fas fa-list-ul step-icon"></i>
                                <small class="d-block mt-1">Type</small>
                            </div>
                            <div class="col step-indicator" data-step="2">
                                <i class="fas fa-book-open step-icon"></i>
                                <small class="d-block mt-1">Guide</small>
                            </div>
                            <div class="col step-indicator" data-step="3">
                                <i class="fas fa-user step-icon"></i>
                                <small class="d-block mt-1">Demandeur</small>
                            </div>
                            <div class="col step-indicator" data-step="4">
                                <i class="fas fa-building step-icon"></i>
                                <small class="d-block mt-1">Organisation</small>
                            </div>
                            <div class="col step-indicator" data-step="5">
                                <i class="fas fa-map-marker-alt step-icon"></i>
                                <small class="d-block mt-1">Coordonnées</small>
                            </div>
                            <div class="col step-indicator" data-step="6">
                                <i class="fas fa-users step-icon"></i>
                                <small class="d-block mt-1">Fondateurs</small>
                            </div>
                            <div class="col step-indicator" data-step="7">
                                <i class="fas fa-file-alt step-icon"></i>
                                <small class="d-block mt-1">Documents</small>
                            </div>
                            <div class="col step-indicator" data-step="8">
                                <i class="fas fa-check-circle step-icon"></i>
                                <small class="d-block mt-1">Soumission</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal du formulaire -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form id="organisationForm" action="/operator/organisations" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <!-- ========== ÉTAPE 1 : CHOIX DU TYPE D'ORGANISATION ========== -->
                            <div class="step-content" id="step1" style="display: block;">
                                <div class="text-center mb-4">
                                    <div class="step-icon-large mx-auto mb-3"
                                        style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                                        <i class="fas fa-list-ul fa-3x text-white"></i>
                                    </div>
                                    <h3 class="text-success">Choisissez le type d'organisation</h3>
                                    <p class="text-muted">Sélectionnez le statut juridique qui correspond à vos objectifs au
                                        Gabon</p>
                                </div>

                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <div class="row">
                                            @forelse($typesOrganisation ?? [] as $orgType)
                                                <div class="col-md-6 mb-4">
                                                    <div class="card h-100 border-2 organization-type-card"
                                                        data-type="{{ $orgType->code }}">
                                                        <div class="card-body text-center p-4">
                                                            <div class="org-icon mb-3"
                                                                style="background: linear-gradient(135deg, {{ $orgType->couleur ?? '#009e3f' }} 0%, {{ $orgType->couleur ?? '#00b347' }}dd 100%);">
                                                                <i
                                                                    class="fas {{ $orgType->icone ?? 'fa-building' }} fa-3x text-white"></i>
                                                            </div>
                                                            <h5 class="card-title"
                                                                style="color: {{ $orgType->couleur ?? '#009e3f' }};">
                                                                {{ $orgType->nom }}</h5>
                                                            <p class="card-text text-muted">
                                                                {{ $orgType->description ?? 'Type d\'organisation' }}
                                                            </p>
                                                            <div class="features mb-3">
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <small class="d-block text-muted">
                                                                            <i
                                                                                class="fas fa-check text-success me-1"></i>{{ $orgType->is_lucratif ? 'But lucratif' : 'But non lucratif' }}
                                                                        </small>
                                                                        <small class="d-block text-muted">
                                                                            <i class="fas fa-check text-success me-1"></i>Min.
                                                                            {{ $orgType->nb_min_fondateurs_majeurs }} fondateurs
                                                                        </small>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <small class="d-block text-muted">
                                                                            <i
                                                                                class="fas {{ $orgType->nb_min_adherents_creation >= 50 ? 'fa-exclamation text-warning' : 'fa-check text-success' }} me-1"></i>Min.
                                                                            {{ $orgType->nb_min_adherents_creation }} adhérents
                                                                        </small>
                                                                        @if($orgType->loi_reference)
                                                                            <small class="d-block text-muted">
                                                                                <i
                                                                                    class="fas fa-gavel text-info me-1"></i>{{ Str::limit($orgType->loi_reference, 25) }}
                                                                            </small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-center mt-2 p-2 rounded" style="background: #f8f9fa;">
                                                                <input type="radio"
                                                                    name="type_organisation" value="{{ $orgType->code }}"
                                                                    id="type{{ Str::studly($orgType->code) }}"
                                                                    style="width: 22px; height: 22px; accent-color: {{ $orgType->couleur ?? '#009e3f' }}; cursor: pointer;"
                                                                    class="me-2">
                                                                <label class="fw-bold mb-0" style="cursor: pointer; color: {{ $orgType->couleur ?? '#009e3f' }};"
                                                                    for="type{{ Str::studly($orgType->code) }}">
                                                                    Choisir {{ $orgType->nom }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <div class="alert alert-warning text-center">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        Aucun type d'organisation n'est disponible pour le moment. Veuillez
                                                        contacter l'administrateur.
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>

                                        <!-- Type sélectionné -->
                                        <div class="row justify-content-center mt-4">
                                            <div class="col-md-8">
                                                <div class="d-none" id="selectedTypeInfo">
                                                    <div
                                                        class="alert alert-success d-flex align-items-center border-0 shadow-sm">
                                                        <i class="fas fa-check-circle me-3 fa-2x text-success"></i>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">Type sélectionné avec succès !</h6>
                                                            <strong id="selectedTypeName"></strong>
                                                            <br>
                                                            <small class="text-muted">Vous pouvez maintenant passer à
                                                                l'étape suivante pour consulter le guide spécifique.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Input caché pour stocker le type -->
                                        <input type="hidden" name="organization_type" id="organizationType" required>
                                    </div>
                                </div>
                            </div>

                            <!-- ========== ÉTAPE 2 : GUIDE SPÉCIFIQUE ========== -->
                            <div class="step-content" id="step2" style="display: none;">
                                <div class="text-center mb-4">
                                    <div class="step-icon-large mx-auto mb-3"
                                        style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                                        <i class="fas fa-book-open fa-3x text-dark"></i>
                                    </div>
                                    <h3 class="text-warning">Guide pour <span id="selectedTypeTitle">votre
                                            organisation</span></h3>
                                    <p class="text-muted">Informations légales et procédures spécifiques au Gabon</p>
                                </div>

                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <!-- Guide dynamique selon le type sélectionné -->
                                        <div id="guide-content">
                                            <div class="alert alert-info border-0 mb-4 shadow-sm">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-info-circle fa-3x me-3 text-info"></i>
                                                    <div>
                                                        <h5 class="alert-heading mb-1">Guide spécifique à votre type
                                                            d'organisation</h5>
                                                        <p class="mb-0">Le contenu s'affichera selon votre sélection à
                                                            l'étape précédente</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Confirmation de lecture obligatoire -->
                                        <div class="mt-4 p-4 bg-light rounded shadow-sm">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="guideReadConfirm"
                                                    name="guide_read_confirm" required>
                                                <label class="form-check-label fw-bold" for="guideReadConfirm">
                                                    <i class="fas fa-check-circle me-2 text-success"></i>
                                                    J'ai lu et compris les exigences légales spécifiques à mon type
                                                    d'organisation au Gabon
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                                Cette confirmation est obligatoire pour passer à l'étape suivante.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ========== ÉTAPE 3 : INFORMATIONS DEMANDEUR - FORMULAIRE COMPLET ========== -->
                            <div class="step-content" id="step3" style="display: none;">
                                <div class="text-center mb-4">
                                    <div class="step-icon-large mx-auto mb-3"
                                        style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                                        <i class="fas fa-user fa-3x text-white"></i>
                                    </div>
                                    <h3 class="text-info">Informations du demandeur</h3>
                                    <p class="text-muted">Renseignez vos informations personnelles en tant que demandeur
                                        principal</p>
                                </div>

                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <div class="alert alert-info border-0 mb-4">
                                            <h6 class="alert-heading">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Information importante
                                            </h6>
                                            <p class="mb-0">
                                                Vous serez le contact privilégié pour le suivi de ce dossier. Assurez-vous
                                                que toutes vos informations
                                                sont exactes et correspondent à vos documents officiels gabonais.
                                            </p>
                                        </div>

                                        <!-- Section Identification -->
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-id-card me-2"></i>
                                                    Identification personnelle
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- NIP - VALIDATION CORRIGÉE -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_nip" class="form-label fw-bold required">
                                                            <i class="fas fa-hashtag me-2 text-primary"></i>
                                                            Numéro d'Identification Personnelle (NIP)
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control form-control-lg"
                                                                id="demandeur_nip" name="demandeur_nip" data-validate="nip"
                                                                placeholder="A1-2345-19901225" maxlength="16"
                                                                pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}" required>
                                                            <span class="input-group-text">
                                                                <i class="fas fa-spinner fa-spin d-none"
                                                                    id="nip-loading"></i>
                                                                <i class="fas fa-check text-success d-none"
                                                                    id="nip-valid"></i>
                                                                <i class="fas fa-times text-danger d-none"
                                                                    id="nip-invalid"></i>
                                                            </span>
                                                        </div>
                                                        <div class="form-text d-none">
                                                            <i class="fas fa-info me-1"></i>
                                                            Format nouveau: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)
                                                        </div>
                                                        <div class="invalid-feedback" id="demandeur_nip_error"></div>
                                                    </div>

                                                    <!-- Civilité -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_civilite" class="form-label fw-bold required">
                                                            <i class="fas fa-user-tag me-2 text-primary"></i>
                                                            Civilité
                                                        </label>
                                                        <select class="form-select form-select-lg" id="demandeur_civilite"
                                                            name="demandeur_civilite" required>
                                                            <option value="">Sélectionnez votre civilité</option>
                                                            <option value="M">Monsieur</option>
                                                            <option value="Mme">Madame</option>
                                                            <option value="Mlle">Mademoiselle</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Nom -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_nom" class="form-label fw-bold required">
                                                            <i class="fas fa-user me-2 text-primary"></i>
                                                            Nom de famille
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="demandeur_nom" name="demandeur_nom"
                                                            placeholder="Votre nom de famille" required>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Prénom -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_prenom" class="form-label fw-bold required">
                                                            <i class="fas fa-user me-2 text-primary"></i>
                                                            Prénom(s)
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="demandeur_prenom" name="demandeur_prenom"
                                                            placeholder="Vos prénoms" required>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Date de naissance -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_date_naissance"
                                                            class="form-label fw-bold required">
                                                            <i class="fas fa-calendar me-2 text-primary"></i>
                                                            Date de naissance
                                                        </label>
                                                        <input type="date" class="form-control form-control-lg"
                                                            id="demandeur_date_naissance" name="demandeur_date_naissance"
                                                            max="{{ date('Y-m-d', strtotime('-18 years')) }}" required>
                                                        <div class="form-text">
                                                            <i class="fas fa-info me-1"></i>
                                                            Vous devez être majeur (18 ans minimum)
                                                        </div>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Nationalité -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_nationalite"
                                                            class="form-label fw-bold required">
                                                            <i class="fas fa-flag me-2 text-primary"></i>
                                                            Nationalité
                                                        </label>
                                                        <select class="form-select form-select-lg"
                                                            id="demandeur_nationalite" name="demandeur_nationalite"
                                                            required>
                                                            <option value="">Sélectionnez votre nationalité</option>
                                                            <option value="Gabonaise" selected>Gabonaise</option>
                                                            <option value="Camerounaise">Camerounaise</option>
                                                            <option value="Congolaise">Congolaise</option>
                                                            <option value="Équato-guinéenne">Équato-guinéenne</option>
                                                            <option value="Française">Française</option>
                                                            <option value="Autre">Autre</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section Contact -->
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-address-book me-2"></i>
                                                    Informations de contact
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- Téléphone -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_telephone"
                                                            class="form-label fw-bold required">
                                                            <i class="fas fa-phone me-2 text-success"></i>
                                                            Téléphone
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">+241</span>
                                                            <input type="text" class="form-control form-control-lg"
                                                                id="demandeur_telephone" name="demandeur_telephone"
                                                                placeholder="01 23 45 67" pattern="[0-9]{8,9}" required>
                                                        </div>
                                                        <div class="form-text">
                                                            <i class="fas fa-info me-1"></i>
                                                            Format gabonais : 8 ou 9 chiffres (ex: 01234567)
                                                        </div>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Email -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_email" class="form-label fw-bold required">
                                                            <i class="fas fa-envelope me-2 text-success"></i>
                                                            Adresse email
                                                        </label>
                                                        <input type="email" class="form-control form-control-lg"
                                                            id="demandeur_email" name="demandeur_email"
                                                            placeholder="votre.email@exemple.com" required>
                                                        <div class="form-text">
                                                            <i class="fas fa-info me-1"></i>
                                                            Adresse email valide pour les communications officielles
                                                        </div>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Adresse -->
                                                    <div class="col-12 mb-4">
                                                        <label for="demandeur_adresse" class="form-label fw-bold required">
                                                            <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                                            Adresse complète de résidence
                                                        </label>
                                                        <textarea class="form-control form-control-lg"
                                                            id="demandeur_adresse" name="demandeur_adresse" rows="3"
                                                            placeholder="Adresse complète (quartier, commune, ville, province)"
                                                            required></textarea>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Profession -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_profession" class="form-label fw-bold">
                                                            <i class="fas fa-briefcase me-2 text-success"></i>
                                                            Profession
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="demandeur_profession" name="demandeur_profession"
                                                            placeholder="Votre profession actuelle">
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Statut dans l'organisation -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="demandeur_role" class="form-label fw-bold required">
                                                            <i class="fas fa-user-tie me-2 text-success"></i>
                                                            Rôle dans l'organisation
                                                        </label>
                                                        <select class="form-select form-select-lg" id="demandeur_role"
                                                            name="demandeur_role" required>
                                                            <option value="">Sélectionnez votre rôle</option>
                                                            @foreach($fonctions ?? [] as $fonction)
                                                                @if($fonction->categorie === 'bureau')
                                                                    <option value="{{ $fonction->code }}">{{ $fonction->nom }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                            <optgroup label="Autres fonctions">
                                                                @foreach($fonctions ?? [] as $fonction)
                                                                    @if($fonction->categorie !== 'bureau')
                                                                        <option value="{{ $fonction->code }}">{{ $fonction->nom }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </optgroup>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Informations complémentaires -->
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-clipboard-check me-2"></i>
                                                    Déclaration et engagement
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-check mb-3">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="demandeur_engagement" name="demandeur_engagement"
                                                                required>
                                                            <label class="form-check-label fw-bold"
                                                                for="demandeur_engagement">
                                                                <i class="fas fa-check-circle me-2 text-warning"></i>
                                                                Je certifie sur l'honneur que les informations fournies sont
                                                                exactes et complètes
                                                            </label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="demandeur_responsabilite"
                                                                name="demandeur_responsabilite" required>
                                                            <label class="form-check-label fw-bold"
                                                                for="demandeur_responsabilite">
                                                                <i class="fas fa-gavel me-2 text-warning"></i>
                                                                J'accepte d'être le responsable légal de cette demande et de
                                                                recevoir toutes les communications officielles
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ========== ÉTAPE 4 : INFORMATIONS ORGANISATION ========== -->
                            <div class="step-content" id="step4" style="display: none;">
                                <div class="text-center mb-4">
                                    <div class="step-icon-large mx-auto mb-3"
                                        style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                                        <i class="fas fa-building fa-3x text-white"></i>
                                    </div>
                                    <h3 class="text-primary">Informations de l'organisation</h3>
                                    <p class="text-muted">Renseignez les détails de votre organisation</p>
                                </div>

                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <!-- Section Identité -->
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-id-badge me-2"></i>
                                                    Identité de l'organisation
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- Nom organisation -->
                                                    <div class="col-md-8 mb-4">
                                                        <label for="org_nom" class="form-label fw-bold required">
                                                            <i class="fas fa-building me-2 text-primary"></i>
                                                            Nom de l'organisation
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg" id="org_nom"
                                                            name="org_nom" placeholder="Nom complet de votre organisation"
                                                            maxlength="255" required>
                                                        <div class="form-text">
                                                            <i class="fas fa-info me-1"></i>
                                                            Le nom exact tel qu'il apparaîtra sur les documents officiels (max. 255 caractères)
                                                        </div>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Sigle -->
                                                    <div class="col-md-4 mb-4">
                                                        <label for="org_sigle" class="form-label fw-bold">
                                                            <i class="fas fa-tag me-2 text-primary"></i>
                                                            Sigle (optionnel)
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="org_sigle" name="org_sigle" placeholder="Ex: AJSD, ONG-DEV"
                                                            maxlength="10">
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Objet social -->
                                                    <div class="col-12 mb-4">
                                                        <label for="org_objet" class="form-label fw-bold required">
                                                            <i class="fas fa-bullseye me-2 text-primary"></i>
                                                            Objet social / Mission
                                                        </label>
                                                        <textarea class="form-control form-control-lg" id="org_objet"
                                                            name="org_objet" rows="4" minlength="50"
                                                            placeholder="Décrivez l'objet social et la mission principale de votre organisation..."
                                                            required oninput="updateCharCount(this, 'org_objet_count', 50)"></textarea>
                                                        <div class="d-flex justify-content-between">
                                                            <div class="form-text">
                                                                <i class="fas fa-info me-1"></i>
                                                                Minimum <strong>50 caractères</strong> requis
                                                            </div>
                                                            <small id="org_objet_count" class="form-text text-danger">0 / 50 min</small>
                                                        </div>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Domaine d'activité (selon le type) -->
                                                    <div class="col-md-6 mb-4" id="org_domaine_container">
                                                        <label for="org_domaine" class="form-label fw-bold">
                                                            <i class="fas fa-industry me-2 text-primary"></i>
                                                            Domaine d'activité
                                                        </label>
                                                        <select class="form-select form-select-lg" id="org_domaine"
                                                            name="org_domaine_activite_id" data-allow-other="domaine">
                                                            <option value="">Sélectionnez un domaine</option>
                                                            @foreach($domainesActivite ?? [] as $domaine)
                                                                <option value="{{ $domaine->id }}">{{ $domaine->nom }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Date de création prévue -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_date_creation" class="form-label fw-bold required">
                                                            <i class="fas fa-calendar me-2 text-primary"></i>
                                                            Date de création prévue
                                                        </label>
                                                        <input type="date" class="form-control form-control-lg"
                                                            id="org_date_creation" name="org_date_creation"
                                                            min="{{ date('Y-m-d') }}" required>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section Contact -->
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-phone me-2"></i>
                                                    Informations de contact
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- Téléphone principal -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_telephone" class="form-label fw-bold required">
                                                            <i class="fas fa-phone me-2 text-success"></i>
                                                            Téléphone principal
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">+241</span>
                                                            <input type="text" class="form-control form-control-lg"
                                                                id="org_telephone" name="org_telephone"
                                                                placeholder="01 23 45 67" pattern="[0-9]{8,9}" required>
                                                        </div>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Email -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_email" class="form-label fw-bold">
                                                            <i class="fas fa-envelope me-2 text-success"></i>
                                                            Adresse email
                                                        </label>
                                                        <input type="email" class="form-control form-control-lg"
                                                            id="org_email" name="org_email"
                                                            placeholder="contact@organisation.ga">
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Site web -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_site_web" class="form-label fw-bold">
                                                            <i class="fas fa-globe me-2 text-success"></i>
                                                            Site web (optionnel)
                                                        </label>
                                                        <input type="url" class="form-control form-control-lg"
                                                            id="org_site_web" name="org_site_web"
                                                            placeholder="https://www.organisation.ga">
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Réseaux sociaux -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_reseaux_sociaux" class="form-label fw-bold">
                                                            <i class="fab fa-facebook me-2 text-success"></i>
                                                            Réseaux sociaux (optionnel)
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="org_reseaux_sociaux" name="org_reseaux_sociaux"
                                                            placeholder="Facebook, Twitter, Instagram...">
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section spécifique selon le type d'organisation -->
                                        <div id="type_specific_section" class="d-none">
                                            <!-- Contenu dynamique selon le type -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ========== ÉTAPE 5 : COORDONNÉES ET GÉOLOCALISATION ========== -->
                            <div class="step-content" id="step5" style="display: none;">
                                <div class="text-center mb-4">
                                    <div class="step-icon-large mx-auto mb-3"
                                        style="background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);">
                                        <i class="fas fa-map-marker-alt fa-3x text-white"></i>
                                    </div>
                                    <h3 class="text-info">Localisation et coordonnées</h3>
                                    <p class="text-muted">Indiquez l'adresse du siège social de votre organisation</p>
                                </div>

                                <div class="row justify-content-center">
                                    <div class="col-md-10">

                                        <!-- Sélection du type de zone -->
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-map-signs me-2"></i>
                                                    Type de zone
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row justify-content-center">
                                                    <div class="col-md-5">
                                                        <div class="card text-center p-3 border-2 zone-type-btn"
                                                            id="zoneCardUrbaine" data-zone="urbaine"
                                                            style="cursor:pointer; border-color: #17a2b8; background-color: #17a2b810;">
                                                            <div class="form-check d-flex justify-content-end mb-2">
                                                                <input class="form-check-input zone-radio" type="radio"
                                                                    name="zone_type_radio" id="radioUrbaine" value="urbaine"
                                                                    checked style="pointer-events:none;">
                                                            </div>
                                                            <i class="fas fa-city fa-2x text-info mb-2"></i>
                                                            <h6 class="mb-1">Zone Urbaine</h6>
                                                            <small class="text-muted">Commune &rarr; Arrondissement &rarr;
                                                                Quartier</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="card text-center p-3 border-2 zone-type-btn"
                                                            id="zoneCardRurale" data-zone="rurale"
                                                            style="cursor:pointer; border-color: #dee2e6;">
                                                            <div class="form-check d-flex justify-content-end mb-2">
                                                                <input class="form-check-input zone-radio" type="radio"
                                                                    name="zone_type_radio" id="radioRurale" value="rurale"
                                                                    style="pointer-events:none;">
                                                            </div>
                                                            <i class="fas fa-tree fa-2x text-success mb-2"></i>
                                                            <h6 class="mb-1">Zone Rurale</h6>
                                                            <small class="text-muted">Canton &rarr; Regroupement &rarr;
                                                                Village</small>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="zone_type" id="zone_type_input"
                                                        value="urbaine">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Localisation administrative -->
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-home me-2"></i>
                                                    Localisation du siège social
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- Province (commun) -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_province" class="form-label fw-bold required">
                                                            <i class="fas fa-map me-2 text-primary"></i>
                                                            Province
                                                        </label>
                                                        <select class="form-select form-select-lg" id="org_province"
                                                            name="org_province_id" required>
                                                            <option value="">Sélectionnez une province</option>
                                                            @foreach($provinces ?? [] as $province)
                                                                <option value="{{ $province->id }}"
                                                                    data-nom="{{ $province->nom }}">{{ $province->nom }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Département (commun) -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_departement" class="form-label fw-bold required">
                                                            <i class="fas fa-map-pin me-2 text-primary"></i>
                                                            Département
                                                        </label>
                                                        <select class="form-select form-select-lg" id="org_departement"
                                                            name="org_departement_id" required disabled>
                                                            <option value="">Sélectionnez d'abord une province</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- ===== ZONE URBAINE : Commune → Arrondissement → Quartier ===== -->
                                                    <div class="col-md-6 mb-4 zone-urbaine-field">
                                                        <label for="org_commune_id" class="form-label fw-bold">
                                                            <i class="fas fa-city me-2 text-info"></i>
                                                            Commune / Ville
                                                        </label>
                                                        <select class="form-select form-select-lg" id="org_commune_id"
                                                            name="org_commune_id" disabled>
                                                            <option value="">Sélectionnez un département...</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <div class="col-md-6 mb-4 zone-urbaine-field">
                                                        <label for="org_arrondissement_id" class="form-label fw-bold">
                                                            <i class="fas fa-map-marked me-2 text-info"></i>
                                                            Arrondissement
                                                        </label>
                                                        <select class="form-select form-select-lg"
                                                            id="org_arrondissement_id" name="org_arrondissement_id"
                                                            disabled>
                                                            <option value="">Sélectionnez une commune...</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <div class="col-md-6 mb-4 zone-urbaine-field">
                                                        <label for="org_quartier_id" class="form-label fw-bold">
                                                            <i class="fas fa-street-view me-2 text-info"></i>
                                                            Quartier
                                                        </label>
                                                        <select class="form-select form-select-lg" id="org_quartier_id"
                                                            name="org_quartier_id" disabled>
                                                            <option value="">Sélectionnez un arrondissement...</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- ===== ZONE RURALE : Canton → Regroupement → Village ===== -->
                                                    <div class="col-md-6 mb-4 zone-rurale-field" style="display: none;">
                                                        <label for="org_canton_id" class="form-label fw-bold">
                                                            <i class="fas fa-tree me-2 text-success"></i>
                                                            Canton
                                                        </label>
                                                        <select class="form-select form-select-lg" id="org_canton_id"
                                                            name="org_canton_id" disabled>
                                                            <option value="">Sélectionnez un département...</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <div class="col-md-6 mb-4 zone-rurale-field" style="display: none;">
                                                        <label for="org_regroupement_id" class="form-label fw-bold">
                                                            <i class="fas fa-layer-group me-2 text-success"></i>
                                                            Regroupement
                                                        </label>
                                                        <select class="form-select form-select-lg" id="org_regroupement_id"
                                                            name="org_regroupement_id" disabled>
                                                            <option value="">Sélectionnez un canton...</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <div class="col-md-6 mb-4 zone-rurale-field" style="display: none;">
                                                        <label for="org_village_id" class="form-label fw-bold">
                                                            <i class="fas fa-home me-2 text-success"></i>
                                                            Village
                                                        </label>
                                                        <select class="form-select form-select-lg" id="org_village_id"
                                                            name="org_village_id" disabled>
                                                            <option value="">Sélectionnez un regroupement...</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- ===== CHAMPS COMMUNS ===== -->
                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_prefecture" class="form-label fw-bold">
                                                            <i class="fas fa-building me-2 text-muted"></i>
                                                            Préfecture
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="org_prefecture" name="org_prefecture"
                                                            placeholder="Préfecture (optionnel)">
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_sous_prefecture" class="form-label fw-bold">
                                                            <i class="fas fa-building me-2 text-muted"></i>
                                                            Sous-Préfecture
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="org_sous_prefecture" name="org_sous_prefecture"
                                                            placeholder="Sous-préfecture (optionnel)">
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <div class="col-md-6 mb-4">
                                                        <label for="org_lieu_dit" class="form-label fw-bold">
                                                            <i class="fas fa-thumbtack me-2 text-muted"></i>
                                                            Lieu-dit
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="org_lieu_dit" name="org_lieu_dit"
                                                            placeholder="Lieu-dit (optionnel)">
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <!-- Adresse complète -->
                                                    <div class="col-12 mb-4">
                                                        <label for="org_adresse_complete"
                                                            class="form-label fw-bold required">
                                                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                                            Adresse complète du siège social
                                                        </label>
                                                        <textarea class="form-control form-control-lg"
                                                            id="org_adresse_complete" name="org_adresse" rows="3"
                                                            placeholder="Numéro, rue, bâtiment..." required></textarea>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>

                                                <!-- Champs cachés pour stocker les noms -->
                                                <input type="hidden" name="province" id="province_nom">
                                                <input type="hidden" name="departement" id="departement_nom">
                                                <input type="hidden" name="commune" id="commune_nom">
                                                <input type="hidden" name="arrondissement" id="arrondissement_text">
                                                <input type="hidden" name="quartier" id="quartier_nom">
                                                <input type="hidden" name="canton" id="canton_nom">
                                                <input type="hidden" name="regroupement" id="regroupement_nom">
                                                <input type="hidden" name="village" id="village_nom">
                                            </div>
                                        </div>

                                        <!-- Géolocalisation optionnelle -->
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-satellite me-2"></i>
                                                    Géolocalisation (optionnel)
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="org_latitude" class="form-label fw-bold">
                                                            <i class="fas fa-compass me-2 text-warning"></i>
                                                            Latitude
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="org_latitude" name="org_latitude" placeholder="Ex: 0.4162">
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label for="org_longitude" class="form-label fw-bold">
                                                            <i class="fas fa-globe-americas me-2 text-warning"></i>
                                                            Longitude
                                                        </label>
                                                        <input type="text" class="form-control form-control-lg"
                                                            id="org_longitude" name="org_longitude"
                                                            placeholder="Ex: 9.4673">
                                                        <div class="invalid-feedback"></div>
                                                    </div>

                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-outline-warning"
                                                            id="getLocationBtn">
                                                            <i class="fas fa-map-marker-alt me-2"></i>
                                                            Obtenir ma position actuelle
                                                        </button>
                                                        <div class="form-text mt-2">
                                                            <i class="fas fa-info me-1"></i>
                                                            La géolocalisation aide à localiser précisément votre
                                                            organisation sur la carte du Gabon
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ========== ÉTAPE 6 : FONDATEURS ========== -->
                            <div class="step-content" id="step6" style="display: none;">
                                <div class="text-center mb-4">
                                    <div class="step-icon-large mx-auto mb-3"
                                        style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                                        <i class="fas fa-users fa-3x text-white"></i>
                                    </div>
                                    <h3 class="text-warning">Membres fondateurs</h3>
                                    <p class="text-muted">Ajoutez les membres fondateurs de votre organisation</p>
                                </div>

                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <div class="alert alert-info border-0 mb-4">
                                            <h6 class="alert-heading">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Exigences selon le type d'organisation
                                            </h6>
                                            <div id="fondateurs_requirements">
                                                <p class="mb-0">Minimum requis : <span id="min_fondateurs">3</span>
                                                    fondateurs majeurs</p>
                                            </div>
                                        </div>

                                        <!-- Formulaire ajout fondateur -->
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user-plus me-2"></i>
                                                    Ajouter un fondateur
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3 mb-3">
                                                        <label for="fondateur_civilite"
                                                            class="form-label fw-bold">Civilité</label>
                                                        <select class="form-select" id="fondateur_civilite">
                                                            <option value="M">Monsieur</option>
                                                            <option value="Mme">Madame</option>
                                                            <option value="Mlle">Mademoiselle</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label for="fondateur_nom" class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="fondateur_nom"
                                                            placeholder="Nom de famille" maxlength="255">
                                                        <div class="invalid-feedback">Le nom est obligatoire</div>
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label for="fondateur_prenom"
                                                            class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="fondateur_prenom"
                                                            placeholder="Prénom(s)" maxlength="255">
                                                        <div class="invalid-feedback">Le prénom est obligatoire</div>
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label for="fondateur_nip" class="form-label fw-bold">NIP <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="fondateur_nip"
                                                            data-validate="nip" placeholder="A1-2345-19901225"
                                                            maxlength="16" pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}">
                                                        <small class="form-text text-muted">Format obligatoire : XX-0000-AAAAMMJJ (ex: A1-2345-19901225)</small>
                                                        <div class="invalid-feedback">NIP obligatoire au format XX-0000-AAAAMMJJ</div>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label for="fondateur_fonction"
                                                            class="form-label fw-bold">Fonction <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="fondateur_fonction" data-allow-other="fonction">
                                                            <option value="">Sélectionnez</option>
                                                            @if(isset($fonctions) && $fonctions->where('categorie', 'bureau')->count())
                                                                <optgroup label="Bureau">
                                                                    @foreach($fonctions->where('categorie', 'bureau') as $fonction)
                                                                        <option value="{{ $fonction->code }}">{{ $fonction->nom }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endif
                                                            @if(isset($fonctions))
                                                                <optgroup label="Autres fonctions">
                                                                    @foreach($fonctions->where('categorie', '!=', 'bureau') as $fonction)
                                                                        <option value="{{ $fonction->code }}">{{ $fonction->nom }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endif
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label for="fondateur_telephone"
                                                            class="form-label fw-bold">Téléphone <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">+241</span>
                                                            <input type="text" class="form-control" id="fondateur_telephone"
                                                                placeholder="01234567" pattern="[0-9]{8,9}">
                                                        </div>
                                                        <small class="form-text text-muted">8 ou 9 chiffres</small>
                                                        <div class="invalid-feedback">Téléphone obligatoire (8-9 chiffres)</div>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label for="fondateur_email"
                                                            class="form-label fw-bold">Email</label>
                                                        <input type="email" class="form-control" id="fondateur_email"
                                                            placeholder="email@exemple.com">
                                                        <small class="form-text text-muted">Optionnel</small>
                                                    </div>

                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-warning" id="addFondateurBtn">
                                                            <i class="fas fa-plus me-2"></i>Ajouter ce fondateur
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Liste des fondateurs -->
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-success text-white">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-list me-2"></i>
                                                        Liste des fondateurs
                                                    </h6>
                                                    <span class="badge bg-light text-dark" id="fondateurs_count">0
                                                        fondateur(s)</span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div id="fondateurs_list">
                                                    <div class="text-center py-4 text-muted">
                                                        <i class="fas fa-users fa-3x mb-3"></i>
                                                        <p>Aucun fondateur ajouté</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ========== SECTION MEMBRES DU BUREAU ========== -->
                                        <div class="card border-0 shadow-sm mt-4">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user-tie me-2"></i>
                                                    Membres du Bureau Exécutif
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info border-0 mb-4">
                                                    <div class="d-flex">
                                                        <i class="fas fa-info-circle me-3 mt-1"></i>
                                                        <div>
                                                            <h6 class="alert-heading mb-1">Information importante</h6>
                                                            <p class="mb-0">
                                                                Déclarez ici les membres du bureau de votre organisation.
                                                                Vous pouvez sélectionner jusqu'à <strong>3 membres
                                                                    maximum</strong>
                                                                pour qu'ils apparaissent sur le récépissé définitif.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Formulaire ajout membre bureau -->
                                                <div class="card border mb-4">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-plus-circle me-2"></i>
                                                            Ajouter un membre du bureau
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-4 mb-3">
                                                                <label for="membre_nip" class="form-label fw-bold">NIP
                                                                    *</label>
                                                                <input type="text" class="form-control" id="membre_nip"
                                                                    data-validate="nip" placeholder="A1-2345-19901225"
                                                                    maxlength="16" pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}">
                                                                <small class="form-text text-muted">Format:
                                                                    XX-QQQQ-YYYYMMDD</small>
                                                            </div>

                                                            <div class="col-md-4 mb-3">
                                                                <label for="membre_nom" class="form-label fw-bold">Nom
                                                                    *</label>
                                                                <input type="text" class="form-control" id="membre_nom"
                                                                    placeholder="Nom de famille">
                                                            </div>

                                                            <div class="col-md-4 mb-3">
                                                                <label for="membre_prenom" class="form-label fw-bold">Prénom
                                                                    *</label>
                                                                <input type="text" class="form-control" id="membre_prenom"
                                                                    placeholder="Prénom(s)">
                                                            </div>

                                                            <div class="col-md-4 mb-3">
                                                                <label for="membre_fonction"
                                                                    class="form-label fw-bold">Fonction *</label>
                                                                <select class="form-select" id="membre_fonction" data-allow-other="fonction">
                                                                    <option value="">Sélectionnez</option>
                                                                    @if(isset($fonctions) && $fonctions->where('categorie', 'bureau')->count())
                                                                        <optgroup label="Bureau">
                                                                            @foreach($fonctions->where('categorie', 'bureau') as $fonction)
                                                                                <option value="{{ $fonction->nom }}">
                                                                                    {{ $fonction->nom }}</option>
                                                                            @endforeach
                                                                        </optgroup>
                                                                    @endif
                                                                    @if(isset($fonctions))
                                                                        <optgroup label="Autres fonctions">
                                                                            @foreach($fonctions->where('categorie', '!=', 'bureau') as $fonction)
                                                                                <option value="{{ $fonction->nom }}">
                                                                                    {{ $fonction->nom }}</option>
                                                                            @endforeach
                                                                        </optgroup>
                                                                    @endif
                                                                </select>
                                                            </div>

                                                            <div class="col-md-4 mb-3">
                                                                <label for="membre_contact"
                                                                    class="form-label fw-bold">Contact</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">+241</span>
                                                                    <input type="text" class="form-control"
                                                                        id="membre_contact" placeholder="01234567">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4 mb-3">
                                                                <label for="membre_domicile"
                                                                    class="form-label fw-bold">Domicile</label>
                                                                <input type="text" class="form-control" id="membre_domicile"
                                                                    placeholder="Quartier, Ville...">
                                                            </div>

                                                            <div class="col-12 mb-3">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="membre_afficher_recepisse">
                                                                    <label class="form-check-label fw-bold"
                                                                        for="membre_afficher_recepisse">
                                                                        <i class="fas fa-file-alt me-2 text-primary"></i>
                                                                        Afficher ce membre sur le récépissé définitif
                                                                        <small class="text-muted d-block">Maximum 3 membres
                                                                            peuvent être affichés sur le récépissé</small>
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <button type="button" class="btn btn-primary"
                                                                    id="addMembreBureauBtn">
                                                                    <i class="fas fa-plus me-2"></i>Ajouter ce membre
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Liste des membres du bureau -->
                                                <div class="card border">
                                                    <div class="card-header bg-light">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0">
                                                                <i class="fas fa-users-cog me-2"></i>
                                                                Membres du bureau déclarés
                                                            </h6>
                                                            <div>
                                                                <span class="badge bg-primary me-2"
                                                                    id="membres_bureau_count">0 membre(s)</span>
                                                                <span class="badge bg-success"
                                                                    id="membres_recepisse_count">0/3 sur récépissé</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="membres_bureau_list">
                                                            <div class="text-center py-4 text-muted">
                                                                <i class="fas fa-user-tie fa-3x mb-3"></i>
                                                                <p>Aucun membre du bureau ajouté</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FIN SECTION MEMBRES DU BUREAU -->

                                    </div>
                                </div>
                            </div>

                            <!-- ========== ÉTAPE 7 : DOCUMENTS ========== -->
                            <div class="step-content" id="step7" style="display: none;">
                                <div class="text-center mb-4">
                                    <div class="step-icon-large mx-auto mb-3"
                                        style="background: linear-gradient(135deg, #17a2b8 0%, #6610f2 100%);">
                                        <i class="fas fa-file-alt fa-3x text-white"></i>
                                    </div>
                                    <h3 class="text-info">Documents justificatifs</h3>
                                    <p class="text-muted">Uploadez les documents requis pour votre organisation</p>
                                </div>

                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <!-- Documents requis (contenu généré dynamiquement) -->
                                        <div id="documents_container">
                                            <div class="alert alert-info border-0 mb-4">
                                                <p class="mb-0"><i class="fas fa-info-circle me-2"></i>Les documents requis seront affichés après la sélection du type d'organisation.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ========== ÉTAPE 8 : SOUMISSION FINALE ========== -->
                            <!-- ========== ÉTAPE 8 : WORKFLOW 2 PHASES - SOUMISSION INTELLIGENTE ========== -->
                            <div class="step-content" id="step8" style="display: none;">
                                <div class="text-center mb-4">
                                    <div class="step-icon-large mx-auto mb-3"
                                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                        <i class="fas fa-check-circle fa-3x text-white"></i>
                                    </div>
                                    <h3 class="text-success">Soumission de votre dossier</h3>
                                    <p class="text-muted">Choisissez le mode de soumission adapté à votre organisation</p>
                                </div>

                                <div class="row justify-content-center">
                                    <div class="col-md-10">

                                        <!-- RÉCAPITULATIF COMPLET -->
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-clipboard-list me-2"></i>
                                                    Récapitulatif complet de votre organisation
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div id="recap_content">
                                                    <!-- Généré dynamiquement par generateFullRecap() -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- CHOIX DU MODE DE SOUMISSION -->
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-paper-plane me-2"></i>
                                                    Choisissez votre mode de soumission
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <!-- Option 1 : Soumission Directe -->
                                                <label for="submissionPhase1Only" class="d-block mb-3 cursor-pointer">
                                                    <div class="border rounded p-3 submission-radio-card" id="card_phase1">
                                                        <div class="d-flex align-items-start">
                                                            <input type="radio" name="submission_mode" value="phase1_only"
                                                                   id="submissionPhase1Only" class="mt-1 me-3"
                                                                   style="width: 20px; height: 20px; accent-color: #28a745;"
                                                                   onchange="toggleSubmissionMode()">
                                                            <div class="flex-grow-1">
                                                                <h5 class="mb-1 text-success">
                                                                    <i class="fas fa-rocket me-2"></i>
                                                                    Soumission Directe (Recommandé)
                                                                </h5>
                                                                <p class="text-muted mb-2">
                                                                    Créer l'organisation maintenant et ajouter les adhérents en Phase 2
                                                                </p>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <small class="text-success d-block"><i class="fas fa-check me-1"></i> Organisation créée immédiatement</small>
                                                                        <small class="text-success d-block"><i class="fas fa-check me-1"></i> Numéro de récépissé généré</small>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <small class="text-success d-block"><i class="fas fa-check me-1"></i> Aucun risque de timeout</small>
                                                                        <small class="text-info d-block"><i class="fas fa-arrow-right me-1"></i> Import adhérents ensuite</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>

                                                <!-- Option 2 : Soumission Traditionnelle -->
                                                <label for="submissionTraditional" class="d-block mb-3 cursor-pointer">
                                                    <div class="border rounded p-3 submission-radio-card" id="card_traditional">
                                                        <div class="d-flex align-items-start">
                                                            <input type="radio" name="submission_mode" value="traditional"
                                                                   id="submissionTraditional" class="mt-1 me-3"
                                                                   style="width: 20px; height: 20px; accent-color: #007bff;"
                                                                   onchange="toggleSubmissionMode()">
                                                            <div class="flex-grow-1">
                                                                <h5 class="mb-1 text-primary">
                                                                    <i class="fas fa-file-upload me-2"></i>
                                                                    Soumission Traditionnelle
                                                                </h5>
                                                                <p class="text-muted mb-2">
                                                                    Ajouter manuellement quelques adhérents maintenant (max 50)
                                                                </p>
                                                                <small class="text-warning d-block"><i class="fas fa-exclamation-triangle me-1"></i> Limité à 50 adhérents</small>
                                                                <small class="text-warning d-block"><i class="fas fa-clock me-1"></i> Risque de timeout si nombreux</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Section adhérents traditionnels (masquée par défaut) -->
                                        <div class="card border-0 shadow-sm mb-4 d-none" id="traditional-adherents-section">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0"><i class="fas fa-users me-2"></i>Ajout d'adhérents (Mode Traditionnel)</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Nom *</label>
                                                        <input type="text" class="form-control" id="adherent-nom" placeholder="Nom de famille">
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Prénom *</label>
                                                        <input type="text" class="form-control" id="adherent-prenom" placeholder="Prénom(s)">
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">NIP *</label>
                                                        <input type="text" class="form-control" id="adherent-nip" placeholder="A1-2345-19901225">
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="button" class="btn btn-warning w-100" onclick="addAdherentTraditional()">
                                                            <i class="fas fa-plus me-2"></i>Ajouter
                                                        </button>
                                                    </div>
                                                </div>
                                                <div id="traditional-adherents-list">
                                                    <div class="text-center text-muted py-3">
                                                        <i class="fas fa-users fa-2x mb-2"></i>
                                                        <p>Aucun adhérent ajouté</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Déclaration finale -->
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-signature me-2"></i>
                                                    Déclaration sur l'honneur
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="declaration_veracite" name="declaration_veracite" required>
                                                    <label class="form-check-label fw-bold" for="declaration_veracite">
                                                        Je certifie sur l'honneur que toutes les informations fournies sont
                                                        exactes et complètes
                                                    </label>
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="declaration_conformite" name="declaration_conformite" required>
                                                    <label class="form-check-label fw-bold" for="declaration_conformite">
                                                        Je déclare que l'organisation respecte la législation gabonaise en
                                                        vigueur
                                                    </label>
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="declaration_autorisation" name="declaration_autorisation"
                                                        required>
                                                    <label class="form-check-label fw-bold" for="declaration_autorisation">
                                                        J'autorise l'administration à vérifier les informations fournies
                                                    </label>
                                                </div>

                                                <!-- Déclaration spécifique parti politique -->
                                                <div id="declaration_parti_politique" class="d-none">
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="declaration_exclusivite_parti"
                                                            name="declaration_exclusivite_parti">
                                                        <label class="form-check-label fw-bold"
                                                            for="declaration_exclusivite_parti">
                                                            <i class="fas fa-vote-yea me-2 text-warning"></i>
                                                            Je déclare que les adhérents de ce parti politique ne sont
                                                            membres d'aucun autre parti politique au Gabon
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Déclaration workflow 2 phases -->
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="declaration_workflow" name="declaration_workflow" required>
                                                    <label class="form-check-label fw-bold" for="declaration_workflow">
                                                        <i class="fas fa-route me-2 text-success"></i>
                                                        Je comprends et accepte le processus de création en 2 phases pour
                                                        garantir la sécurité de mes données
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- Boutons de navigation avec workflow 2 phases -->
                    <!-- Boutons de navigation avec workflow 2 phases -->
                    <div class="card-footer bg-light border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)"
                                style="display: none;">
                                <i class="fas fa-arrow-left me-2"></i>Précédent
                            </button>

                            <span id="save-indicator" class="align-self-center mr-3 small text-muted"></span>

                            <div class="ms-auto d-flex gap-2">
                                <!-- Bouton suivant (étapes 1-7) -->
                                <button type="button" class="btn btn-success" id="nextBtn" onclick="changeStep(1)">
                                    Suivant <i class="fas fa-arrow-right ms-2"></i>
                                </button>

                                <!-- Bouton "Garder en brouillon" visible partout à partir de l'étape 2 -->
                                <button type="button" class="btn btn-outline-warning" id="saveDraftBtn"
                                    onclick="saveDraftAndExit()" style="display: none;">
                                    <i class="fas fa-save me-2"></i>Garder en brouillon
                                </button>

                                <!-- Boutons de soumission (étape 8) -->
                                <button type="button" class="btn btn-primary" id="submitPhase1Btn" onclick="submitPhase1()"
                                    style="display: none;">
                                    <i class="fas fa-rocket me-2"></i>Créer l'Organisation (Phase 1)
                                </button>

                                <button type="button" class="btn btn-success" id="submitTraditionalBtn"
                                    onclick="submitTraditional()" style="display: none;">
                                    <i class="fas fa-paper-plane me-2"></i>Soumettre Maintenant
                                </button>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <!-- Loader global -->
            <div id="global-loader" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none"
                style="z-index: 9999;">
                <div class="d-flex align-items-center justify-content-center h-100">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>

            <!-- Modal d'aide -->
            <div class="modal fade" id="helpModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-question-circle me-2"></i>
                                Aide - Création d'organisation
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <p>Ce guide vous aidera à créer votre organisation étape par étape selon la législation
                                gabonaise.</p>
                            <ul>
                                <li><strong>Étape 1-2 :</strong> Choisissez le type et consultez le guide légal</li>
                                <li><strong>Étape 3 :</strong> Saisissez vos informations personnelles</li>
                                <li><strong>Étapes 4-8 :</strong> Complétez les informations de l'organisation</li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal d'aide NIP -->
            <div class="modal fade" id="nipHelpModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-hashtag me-2"></i>
                                Aide - Format NIP Gabonais
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <h6>Nouveau format NIP : XX-QQQQ-YYYYMMDD</h6>
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Structure du NIP :</h6>
                                <ul class="mb-0">
                                    <li><strong>XX</strong> : 2 caractères alphanumériques (A-Z, 0-9)</li>
                                    <li><strong>QQQQ</strong> : 4 chiffres de séquence</li>
                                    <li><strong>YYYYMMDD</strong> : Date de naissance (Année-Mois-Jour)</li>
                                </ul>
                            </div>
                            <h6>Exemples valides :</h6>
                            <div class="bg-light p-3 rounded">
                                <code>A1-2345-19901225</code> → Né le 25/12/1990<br>
                                <code>B2-0001-20000115</code> → Né le 15/01/2000<br>
                                <code>C3-9999-19850630</code> → Né le 30/06/1985
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-info" onclick="showNipExample()">
                                    <i class="fas fa-magic me-2"></i>Générer un exemple
                                </button>
                            </div>
                            <div id="nipExampleResult" class="mt-2"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>




            <!-- Modal de confirmation workflow 2 phases -->
            <div class="modal fade modal-workflow" id="workflowConfirmModal" tabindex="-1" data-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-rocket me-2"></i>
                                Confirmation - Workflow 2 Phases
                            </h5>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <div class="workflow-steps">
                                    <div class="phase-step-mini current">1</div>
                                    <div class="step-connector active"></div>
                                    <div class="phase-step-mini pending">2</div>
                                </div>
                            </div>

                            <div class="alert alert-success border-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-2x me-3 text-success"></i>
                                    <div>
                                        <h6 class="alert-heading mb-2">Prêt pour la soumission Phase 1</h6>
                                        <p class="mb-2">
                                            Votre organisation va être créée et enregistrée dans la base de données DGELP.
                                            Vous recevrez immédiatement un numéro de récépissé.
                                        </p>
                                        <p class="mb-0">
                                            <strong>Étape suivante :</strong> Vous serez automatiquement redirigé vers
                                            l'interface
                                            d'import des adhérents (Phase 2) pour compléter votre dossier.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-success">
                                        <i class="fas fa-check me-2"></i>Phase 1 - Organisation
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li>✓ Informations organisation validées</li>
                                        <li>✓ Fondateurs enregistrés</li>
                                        <li>✓ Documents uploadés</li>
                                        <li>✓ Numéro de récépissé généré</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-warning">
                                        <i class="fas fa-arrow-right me-2"></i>Phase 2 - Adhérents
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li>⏳ Import intelligent par lots</li>
                                        <li>⏳ Traitement sécurisé</li>
                                        <li>⏳ Validation automatique</li>
                                        <li>⏳ Soumission finale à l'administration</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <strong>Organisation :</strong>
                                        <span id="confirm-org-name">---</span>
                                    </div>
                                    <div>
                                        <strong>Type :</strong>
                                        <span id="confirm-org-type">---</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- ═══ DIAGNOSTIC ═══ --}}
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Annuler
                            </button>
                            <button type="button" class="btn btn-success btn-lg" id="btnConfirmCreate"
                                onclick="handleConfirmSubmitPhase1();">
                                <i class="fas fa-rocket me-2"></i>Confirmer - Créer l'Organisation
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Laravel pour JavaScript -->
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <meta name="app-url" content="{{ config('app.url') }}">
            <meta name="user-id" content="{{ auth()->id() }}">

            @if(isset($dossier) && $dossier)
                <meta name="dossier-id" content="{{ $dossier->id }}">
            @endif

            @if(isset($organisation) && $organisation)
                <meta name="organisation-id" content="{{ $organisation->id }}">
            @endif

@endsection

        @push('scripts')
                <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js" integrity="sha384-EtqfExzDvAOmLLdnOsa5Dy174/rTmPzv9OnQXw8NQOXnTypob284TIsp6Gt3yEyL" crossorigin="anonymous"></script>

                <!-- Configuration JavaScript pour 8 étapes -->
                <script>
                    // Données des types d'organisation chargées depuis la base de données
                    window.typesOrganisationData = @json($typesOrganisationJson ?? []);

                    // Configuration globale mise à jour pour 8 étapes
                    window.OrganisationApp = {
                        currentStep: 1,
                        totalSteps: 8,
                        formData: {},
                        isSubmitting: false,
                        organisationType: null,
                        foundateurs: [],
                        membresBureau: [],
                        validationErrors: {},
                        uploadedDocuments: {},

                        // Sauvegarde par étape (brouillon serveur)
                        draftId: null,
                        isSavingStep: false,
                        stepSaveErrors: {},
                        frontendToBackendStep: {
                            1: 1,  // Type d'organisation
                            2: 2,  // Guide et exigences
                            3: 3,  // Informations demandeur
                            4: 4,  // Informations organisation
                            5: 5,  // Coordonnées et localisation
                            6: 6,  // Fondateurs
                            7: 8,  // Documents (backend step 8, step 7 = adhérents Phase 2)
                            8: 9   // Validation finale
                        },

                        // Configuration cache/localStorage
                        cacheConfig: {
                            enabled: true,
                            keyPrefix: 'organisationForm_',
                            expirationHours: 24,
                            autoSaveInterval: 5000,
                            maxCacheSize: 5 * 1024 * 1024
                        },

                        // Configuration mise à jour des étapes
                        stepConfig: {
                            1: { name: 'Type', icon: 'fa-list-ul', required: true },
                            2: { name: 'Guide', icon: 'fa-book-open', required: true },
                            3: { name: 'Demandeur', icon: 'fa-user', required: true },
                            4: { name: 'Organisation', icon: 'fa-building', required: true },
                            5: { name: 'Coordonnées', icon: 'fa-map-marker-alt', required: true },
                            6: { name: 'Fondateurs', icon: 'fa-users', required: true },
                            7: { name: 'Documents', icon: 'fa-file-alt', required: true },
                            8: { name: 'Soumission', icon: 'fa-check-circle', required: true }
                        },

                        // Documents par type - dynamique depuis la DB
                        documentRequirements: (function () {
                            const reqs = {};
                            Object.keys(window.typesOrganisationData).forEach(code => {
                                const typeData = window.typesOrganisationData[code];
                                reqs[code] = {
                                    required: typeData.documents.filter(d => d.is_obligatoire).map(d => d.nom),
                                    optional: typeData.documents.filter(d => !d.is_obligatoire).map(d => d.nom)
                                };
                            });
                            return reqs;
                        })()
                    };

                    console.log('Configuration chargée depuis DB:', window.typesOrganisationData);
                    console.log('Configuration app:', window.OrganisationApp);
                </script>

                <script>
                    // ========================================
                    // WORKFLOW 2 PHASES - JAVASCRIPT INTÉGRÉ
                    // ========================================

                    // Variables globales pour le workflow
                    window.WorkflowData = {
                        submissionMode: 'phase1_only',
                        traditionalAdherents: [],
                        isPhase1Submitted: false
                    };

                    /**
                     * Mise à jour des boutons de navigation pour l'étape 8
                     */
                    function updateNavigationButtonsStep8() {
                        const nextBtn = document.getElementById('nextBtn');
                        const submitPhase1Btn = document.getElementById('submitPhase1Btn');
                        const submitTraditionalBtn = document.getElementById('submitTraditionalBtn');
                        const submissionInfo = document.getElementById('submission-info');

                        if (OrganisationApp.currentStep === 8) {
                            // Masquer le bouton suivant
                            if (nextBtn) nextBtn.classList.add('d-none');

                            // Afficher les informations de soumission
                            if (submissionInfo) submissionInfo.classList.remove('d-none');

                            // Afficher le bon bouton selon le mode
                            if (WorkflowData.submissionMode === 'phase1_only') {
                                if (submitPhase1Btn) submitPhase1Btn.classList.remove('d-none');
                                if (submitTraditionalBtn) submitTraditionalBtn.classList.add('d-none');
                            } else {
                                if (submitPhase1Btn) submitPhase1Btn.classList.add('d-none');
                                if (submitTraditionalBtn) submitTraditionalBtn.classList.remove('d-none');
                            }
                        } else {
                            // Masquer tous les boutons de soumission
                            if (submitPhase1Btn) submitPhase1Btn.classList.add('d-none');
                            if (submitTraditionalBtn) submitTraditionalBtn.classList.add('d-none');
                            if (submissionInfo) submissionInfo.classList.add('d-none');

                            // Afficher le bouton suivant
                            if (nextBtn) nextBtn.classList.remove('d-none');
                        }
                    }

                    /**
                     * Gestion du changement de mode de soumission
                     */
                    function setupSubmissionModeHandlers() {
                        const submissionChoices = document.querySelectorAll('.submission-choice');
                        const traditionalSection = document.getElementById('traditional-adherents-section');
                        const submissionHelp = document.getElementById('submission-help');

                        submissionChoices.forEach(choice => {
                            choice.addEventListener('click', function () {
                                const radio = this.querySelector('input[type="radio"]');
                                if (radio) {
                                    // Mettre à jour la sélection visuelle
                                    submissionChoices.forEach(c => c.classList.remove('selected'));
                                    this.classList.add('selected');

                                    // Mettre à jour le mode global
                                    WorkflowData.submissionMode = radio.value;

                                    // Afficher/masquer la section traditionnelle
                                    if (radio.value === 'traditional') {
                                        if (traditionalSection) traditionalSection.classList.remove('d-none');
                                        if (submissionHelp) {
                                            submissionHelp.className = 'alert alert-warning border-0 mt-3';
                                            submissionHelp.innerHTML = `
                                                                                <div class="d-flex align-items-center">
                                                                                    <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                                                                                    <div>
                                                                                        <h6 class="alert-heading mb-1">Mode Traditionnel</h6>
                                                                                        <p class="mb-0">
                                                                                            Attention : Ce mode est limité à 50 adhérents maximum et peut causer des timeouts 
                                                                                            avec de gros volumes. La Phase 2 est recommandée pour plus de sécurité.
                                                                                        </p>
                                                                                    </div>
                                                                                                                                       </div>
                                                                            `;
                                        }
                                    } else {
                                        if (traditionalSection) traditionalSection.classList.add('d-none');
                                        if (submissionHelp) {
                                            submissionHelp.className = 'alert alert-info border-0 mt-3';
                                            submissionHelp.innerHTML = `
                                                                                <div class="d-flex align-items-center">
                                                                                    <i class="fas fa-lightbulb fa-2x me-3 text-info"></i>
                                                                                    <div>
                                                                                        <h6 class="alert-heading mb-1">Recommandation</h6>
                                                                                        <p class="mb-0">
                                                                                            Excellent choix ! La soumission en 2 phases garantit la sécurité de vos données 
                                                                                            et permet de traiter un nombre illimité d'adhérents.
                                                                                        </p>
                                                                                    </div>
                                                                                </div>
                                                                            `;
                                        }
                                    }

                                    // Mettre à jour les boutons
                                    updateNavigationButtonsStep8();
                                }
                            });
                        });
                    }

                    /**
                     * Ajouter un adhérent en mode traditionnel
                     */
                    function addAdherentTraditional() {
                        const nom = document.getElementById('adherent-nom')?.value?.trim();
                        const prenom = document.getElementById('adherent-prenom')?.value?.trim();
                        const nip = document.getElementById('adherent-nip')?.value?.trim();
                        const telephone = document.getElementById('adherent-telephone')?.value?.trim();

                        // Validation
                        if (!nom || !prenom || !nip) {
                            showNotification('Nom, prénom et NIP sont obligatoires', 'warning');
                            return;
                        }

                        // Vérifier le limite
                        if (WorkflowData.traditionalAdherents.length >= 50) {
                            showNotification('Maximum 50 adhérents en mode traditionnel', 'warning');
                            return;
                        }

                        // Vérifier les doublons
                        if (WorkflowData.traditionalAdherents.some(a => a.nip === nip)) {
                            showNotification('Ce NIP existe déjà', 'warning');
                            return;
                        }

                        // Ajouter l'adhérent
                        const adherent = {
                            id: Date.now(),
                            nom: nom,
                            prenom: prenom,
                            nip: nip,
                            telephone: telephone || '',
                            civilite: 'M'
                        };

                        WorkflowData.traditionalAdherents.push(adherent);
                        updateTraditionalAdherentsList();
                        clearTraditionalForm();

                        showNotification(`Adhérent ${prenom} ${nom} ajouté (${WorkflowData.traditionalAdherents.length}/50)`, 'success');
                    }

                    /**
                     * Mettre à jour la liste des adhérents traditionnels
                     */
                    function updateTraditionalAdherentsList() {
                        const listContainer = document.getElementById('traditional-adherents-list');
                        if (!listContainer) return;

                        if (WorkflowData.traditionalAdherents.length === 0) {
                            listContainer.innerHTML = `
                                                                <div class="text-center text-muted py-3">
                                                                    <i class="fas fa-users fa-2x mb-2"></i>
                                                                    <p>Aucun adhérent ajouté</p>
                                                                </div>
                                                            `;
                            return;
                        }

                        let html = `
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h6 class="mb-0">
                                                                    <i class="fas fa-list me-2"></i>
                                                                    Adhérents ajoutés (${WorkflowData.traditionalAdherents.length}/50)
                                                                </h6>
                                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearAllTraditionalAdherents()">
                                                                    <i class="fas fa-trash"></i> Vider
                                                                </button>
                                                            </div>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Nom</th>
                                                                            <th>Prénom</th>
                                                                            <th>NIP</th>
                                                                            <th>Téléphone</th>
                                                                            <th>Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                        `;

                        WorkflowData.traditionalAdherents.forEach((adherent, index) => {
                            html += `
                                                                <tr>
                                                                    <td><strong>${adherent.nom}</strong></td>
                                                                    <td>${adherent.prenom}</td>
                                                                    <td><code>${adherent.nip}</code></td>
                                                                    <td>${adherent.telephone || '-'}</td>
                                                                    <td>
                                                                        <button class="btn btn-outline-danger btn-sm" onclick="removeTraditionalAdherent(${index})">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            `;
                        });

                        html += '</tbody></table></div>';
                        listContainer.innerHTML = html;
                    }

                    /**
                     * Supprimer un adhérent traditionnel
                     */
                    function removeTraditionalAdherent(index) {
                        if (confirm('Supprimer cet adhérent ?')) {
                            const adherent = WorkflowData.traditionalAdherents[index];
                            WorkflowData.traditionalAdherents.splice(index, 1);
                            updateTraditionalAdherentsList();
                            showNotification(`Adhérent ${adherent.prenom} ${adherent.nom} supprimé`, 'info');
                        }
                    }

                    /**
                     * Vider tous les adhérents traditionnels
                     */
                    function clearAllTraditionalAdherents() {
                        if (confirm('Supprimer tous les adhérents ajoutés ?')) {
                            WorkflowData.traditionalAdherents = [];
                            updateTraditionalAdherentsList();
                            showNotification('Tous les adhérents supprimés', 'info');
                        }
                    }

                    /**
                     * Vider le formulaire traditionnel
                     */
                    function clearTraditionalForm() {
                        const fields = ['adherent-nom', 'adherent-prenom', 'adherent-nip', 'adherent-telephone'];
                        fields.forEach(fieldId => {
                            const field = document.getElementById(fieldId);
                            if (field) field.value = '';
                        });
                    }

                    /**
                     * Soumission Phase 1
                     */
                    function submitPhase1() {
                        console.log('🚀 Début soumission Phase 1');

                        // Validation finale
                        if (!validateStep8()) {
                            showNotification('Veuillez compléter toutes les déclarations obligatoires', 'warning');
                            return;
                        }

                        // Préparer les données de confirmation
                        const orgNom = document.getElementById('org_nom')?.value || 'Organisation';
                        const orgType = getOrganizationTypeLabel(OrganisationApp.organisationType);

                        document.getElementById('confirm-org-name').textContent = orgNom;
                        document.getElementById('confirm-org-type').textContent = orgType;

                        // Afficher le modal de confirmation (Bootstrap 4 + jQuery)
                        $('#workflowConfirmModal').modal('show');
                    }

                    /**
                     * Confirmation finale de la soumission Phase 1
                     */
                    async function handleConfirmSubmitPhase1() {
                        console.log('Confirmation soumission Phase 1');

                        // Masquer le modal
                        $('#workflowConfirmModal').modal('hide');
                        showGlobalLoader('Creation de votre organisation en cours...');

                        try {
                            // Sauvegarder la derniere etape (declarations) au serveur
                            await saveStepToServer(8);

                            // Verifier qu'un brouillon existe
                            if (!OrganisationApp.draftId) {
                                throw new Error('Aucun brouillon en cours. Veuillez recommencer le formulaire.');
                            }

                            // Token CSRF
                            let csrfToken;
                            try {
                                csrfToken = await window.UnifiedCSRFManager.getCurrentToken();
                            } catch (e) {
                                csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                            }

                            if (!csrfToken) {
                                throw new Error('Token CSRF introuvable. Veuillez rafraichir la page.');
                            }

                            // Upload des documents avant finalisation (via FormData)
                            if (Object.keys(OrganisationApp.uploadedDocuments).length > 0) {
                                const docFormData = new FormData();
                                docFormData.append('_token', csrfToken);
                                docFormData.append('draft_id', OrganisationApp.draftId);
                                let hasFiles = false;
                                Object.keys(OrganisationApp.uploadedDocuments).forEach(docType => {
                                    const doc = OrganisationApp.uploadedDocuments[docType];
                                    if (doc && doc.file) {
                                        docFormData.append('documents[' + docType + ']', doc.file);
                                        hasFiles = true;
                                    }
                                });

                                if (hasFiles) {
                                    // Envoyer les documents au endpoint existant
                                    const submitUrl = document.getElementById('organisationForm').action;
                                    collectAllFormData();

                                    // Construire un FormData complet pour le store existant
                                    const fullFormData = new FormData();
                                    fullFormData.append('_token', csrfToken);

                                    Object.keys(OrganisationApp.formData).forEach(key => {
                                        if (key === '_token') return;
                                        const val = OrganisationApp.formData[key];
                                        if (val === null || val === undefined || val === false) return;
                                        if (typeof val === 'object' && val.hasFile) return;
                                        fullFormData.append(key, val === true ? '1' : val);
                                    });

                                    OrganisationApp.foundateurs.forEach((fondateur, index) => {
                                        Object.keys(fondateur).forEach(key => {
                                            if (key !== 'id' && key !== 'dateAjout') {
                                                fullFormData.append('fondateurs[' + index + '][' + key + ']', fondateur[key]);
                                            }
                                        });
                                    });

                                    OrganisationApp.membresBureau.forEach((membre, index) => {
                                        Object.keys(membre).forEach(key => {
                                            if (key !== 'id' && key !== 'dateAjout') {
                                                fullFormData.append('membresBureau[' + index + '][' + key + ']', membre[key]);
                                            }
                                        });
                                    });

                                    Object.keys(OrganisationApp.uploadedDocuments).forEach(docType => {
                                        const doc = OrganisationApp.uploadedDocuments[docType];
                                        if (doc && doc.file) {
                                            fullFormData.append('documents[' + docType + ']', doc.file);
                                        }
                                    });

                                    fullFormData.append('_workflow', '2_phases');
                                    fullFormData.append('_phase', '1');
                                    fullFormData.append('submission_mode', 'phase1_only');
                                    fullFormData.append('draft_id', OrganisationApp.draftId);

                                    const response = await fetch(submitUrl, {
                                        method: 'POST',
                                        body: fullFormData,
                                        credentials: 'same-origin',
                                        headers: {
                                            'X-CSRF-TOKEN': csrfToken,
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json'
                                        }
                                    });

                                    if (response.status === 419) {
                                        try {
                                            csrfToken = await window.UnifiedCSRFManager.refreshFromServer();
                                        } catch (e) {}
                                        fullFormData.set('_token', csrfToken);
                                        const retryResp = await fetch(submitUrl, {
                                            method: 'POST',
                                            body: fullFormData,
                                            credentials: 'same-origin',
                                            headers: {
                                                'X-CSRF-TOKEN': csrfToken,
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'Accept': 'application/json'
                                            }
                                        });
                                        return await handleFinalResponse(retryResp);
                                    }

                                    return await handleFinalResponse(response);
                                }
                            }

                            // Finalisation via le draft (sans documents)
                            const finalizeUrl = '/operator/organisations/draft/' + OrganisationApp.draftId + '/finalize';
                            const response = await fetch(finalizeUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({ _token: csrfToken })
                            });

                            return await handleFinalResponse(response);

                        } catch (error) {
                            hideGlobalLoader();
                            console.error('Erreur Phase 1:', error);
                            let errorMessage = error.message || 'Erreur lors de la creation.';
                            if (error.message && error.message.includes('413')) {
                                errorMessage = 'Fichiers trop volumineux. Reduisez la taille des documents.';
                            }
                            showNotification(errorMessage, 'error');
                        }
                    }

                    /**
                     * Traiter la reponse finale (creation/finalisation)
                     */
                    async function handleFinalResponse(response) {
                        if (!response.ok) {
                            const errorData = await response.json().catch(() => ({ message: 'HTTP ' + response.status }));
                            let errorMsg = errorData.message || 'HTTP ' + response.status;
                            if (errorData.errors) {
                                const details = Object.values(errorData.errors).flat().join('\n');
                                errorMsg += '\n' + details;
                            }
                            if (errorData.missing_step) {
                                errorMsg = 'Etape ' + errorData.missing_step + ' non completee. Veuillez la completer.';
                            }
                            hideGlobalLoader();
                            showNotification(errorMsg, 'error');
                            return;
                        }

                        const data = await response.json();
                        hideGlobalLoader();
                        console.log('Phase 1 reussie:', data);

                        if (data.success) {
                            if (typeof WorkflowData !== 'undefined') WorkflowData.isPhase1Submitted = true;
                            clearCache();

                            const orgId = data.organisation_id || (data.data && data.data.organisation_id);
                            const recepisse = (data.data && data.data.numero_recepisse) || '';
                            showNotification('Organisation creee avec succes !' + (recepisse ? ' N ' + recepisse : ''), 'success');

                            setTimeout(() => {
                                if (data.redirect_url) {
                                    window.location.href = data.redirect_url;
                                } else if (data.data && data.data.next_phase_url) {
                                    window.location.href = data.data.next_phase_url;
                                } else if (data.data && data.data.dossier_id) {
                                    window.location.href = '/operator/dossiers/' + data.data.dossier_id + '/adherents-import';
                                } else if (orgId) {
                                    window.location.href = '/operator/organisations/' + orgId;
                                } else {
                                    window.location.href = '/operator/organisations';
                                }
                            }, 2000);
                        } else {
                            throw new Error(data.message || 'Reponse inattendue du serveur');
                        }
                    }

                    /**
                     * Soumission Traditionnelle (complète avec adhérents)
                     * Même logique que confirmSubmitPhase1() mais avec submission_mode='traditional'
                     */
                    async function submitTraditional() {
                        console.log('🚀 Début soumission Traditionnelle');

                        // Validation finale
                        if (!validateStep8()) {
                            showNotification('Veuillez compléter toutes les déclarations obligatoires', 'warning');
                            return;
                        }

                        // Afficher le loader
                        showGlobalLoader('Soumission complète en cours...');

                        try {
                            // ══════ 1. Rafraîchir le token CSRF AVANT soumission ══════
                            let csrfToken;
                            try {
                                csrfToken = await window.UnifiedCSRFManager.getCurrentToken();
                            } catch (e) {
                                csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                            }

                            if (!csrfToken) {
                                throw new Error('Token CSRF introuvable. Veuillez rafraîchir la page.');
                            }

                            // ══════ 2. Collecter toutes les données ══════
                            collectAllFormData();

                            // ══════ 3. Construire le FormData ══════
                            const formData = new FormData();
                            formData.append('_token', csrfToken);

                            Object.keys(OrganisationApp.formData).forEach(key => {
                                if (key === '_token') return;
                                const val = OrganisationApp.formData[key];
                                if (val === null || val === undefined) return;

                                if (val === true) {
                                    formData.append(key, '1');
                                } else if (val === false) {
                                    return;
                                } else if (typeof val === 'object' && val.hasFile) {
                                    return;
                                } else {
                                    formData.append(key, val);
                                }
                            });

                            // Fondateurs
                            console.log('📋 Fondateurs à envoyer:', OrganisationApp.foundateurs.length);
                            OrganisationApp.foundateurs.forEach((fondateur, index) => {
                                Object.keys(fondateur).forEach(key => {
                                    if (key !== 'id' && key !== 'dateAjout') {
                                        formData.append(`fondateurs[${index}][${key}]`, fondateur[key]);
                                    }
                                });
                            });

                            // Membres du bureau
                            OrganisationApp.membresBureau.forEach((membre, index) => {
                                Object.keys(membre).forEach(key => {
                                    if (key !== 'id' && key !== 'dateAjout') {
                                        formData.append(`membresBureau[${index}][${key}]`, membre[key]);
                                    }
                                });
                            });

                            // Documents
                            Object.keys(OrganisationApp.uploadedDocuments).forEach(docType => {
                                const doc = OrganisationApp.uploadedDocuments[docType];
                                if (doc.file) {
                                    formData.append(`documents[${docType}]`, doc.file);
                                }
                            });

                            formData.append('submission_mode', 'traditional');

                            // ══════ 4. Envoyer avec retry CSRF ══════
                            const submitUrl = document.getElementById('organisationForm').action;
                            console.log('🔍 submitTraditional - URL:', submitUrl);

                            const maxRetries = 2;
                            for (let attempt = 1; attempt <= maxRetries; attempt++) {
                                if (attempt > 1) {
                                    try {
                                        csrfToken = await window.UnifiedCSRFManager.refreshFromServer();
                                        formData.set('_token', csrfToken);
                                    } catch (e) {
                                        console.warn('⚠️ Échec refresh token:', e.message);
                                    }
                                }

                                const response = await fetch(submitUrl, {
                                    method: 'POST',
                                    body: formData,
                                    credentials: 'same-origin',
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken,
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    }
                                });

                                console.log('🔍 submitTraditional - Response:', response.status);

                                // 419 CSRF → retry
                                if (response.status === 419 && attempt < maxRetries) {
                                    const body = await response.json().catch(() => ({}));
                                    if (body.new_token) {
                                        csrfToken = body.new_token;
                                        formData.set('_token', csrfToken);
                                        window.UnifiedCSRFManager.updateAllLocations(csrfToken);
                                    }
                                    continue;
                                }

                                if (response.type === 'opaqueredirect' || response.status === 0) {
                                    throw new Error('Session expirée. Veuillez rafraîchir la page.');
                                }

                                if (!response.ok) {
                                    const errorData = await response.json().catch(() => ({ message: `HTTP ${response.status}` }));
                                    let errorMsg = errorData.message || `HTTP ${response.status}`;
                                    if (errorData.errors) {
                                        errorMsg += '\n\n• ' + Object.values(errorData.errors).flat().join('\n• ');
                                    }
                                    throw new Error(errorMsg);
                                }

                                // ══════ 5. Succès ══════
                                const data = await response.json();
                                hideGlobalLoader();
                                console.log('✅ Soumission traditionnelle réussie:', data);

                                if (data.success) {
                                    clearCache();
                                    showNotification('Organisation créée avec succès !', 'success');
                                    setTimeout(() => {
                                        if (data.redirect_url) {
                                            window.location.href = data.redirect_url;
                                        } else if (data.data && data.data.dossier_id) {
                                            window.location.href = `/operator/dossiers/${data.data.dossier_id}/confirmation`;
                                        } else {
                                            window.location.href = '/operator/dossiers';
                                        }
                                    }, 2000);
                                } else {
                                    throw new Error(data.message || 'Réponse inattendue du serveur');
                                }
                                return;
                            }

                            throw new Error('Échec après plusieurs tentatives. Token CSRF invalide.');

                        } catch (error) {
                            hideGlobalLoader();
                            console.error('❌ Erreur soumission traditionnelle:', error);
                            showNotification('Erreur: ' + error.message, 'error');
                        }
                    }

                    /**
                     * Afficher le loader global
                     */
                    function showGlobalLoader(message = 'Chargement...') {
                        const loader = document.getElementById('global-loader');
                        if (loader) {
                            const loadingText = loader.querySelector('.visually-hidden');
                            if (loadingText) loadingText.textContent = message;
                            loader.classList.remove('d-none');
                        }
                    }

                    /**
                     * Masquer le loader global
                     */
                    function hideGlobalLoader() {
                        const loader = document.getElementById('global-loader');
                        if (loader) {
                            loader.classList.add('d-none');
                        }
                    }

                    /**
                     * Vérifier la session des adhérents
                     */
                    function checkSessionAdherents() {
                        // Cette fonction devrait appeler une API pour vérifier s'il y a des adhérents en session
                        // Pour l'instant, simulation
                        const sessionCount = 0; // À remplacer par vraie vérification

                        const previewSection = document.getElementById('adherents-preview-section');
                        const countSpan = document.getElementById('session-adherents-count');

                        if (sessionCount > 0) {
                            if (previewSection) previewSection.style.display = 'block';
                            if (countSpan) countSpan.textContent = sessionCount;
                        }
                    }

                    /**
                     * Initialiser le workflow lors de l'arrivée à l'étape 8
                     */
                    function initWorkflowStep8() {
                        console.log('🔄 Initialisation workflow étape 8');

                        // Sélectionner le mode par défaut
                        const defaultMode = document.getElementById('submissionPhase1Only');
                        if (defaultMode && !document.querySelector('input[name="submission_mode"]:checked')) {
                            defaultMode.checked = true;
                            defaultMode.closest('.submission-choice').classList.add('selected');
                            WorkflowData.submissionMode = 'phase1_only';
                        }

                        // Configurer les gestionnaires d'événements
                        setupSubmissionModeHandlers();

                        // Vérifier les adhérents en session
                        checkSessionAdherents();

                        // Mettre à jour les boutons
                        updateNavigationButtonsStep8();

                        // Configurer le bouton de confirmation
                        // Note: le onclick est déjà défini en inline sur le bouton HTML
                        // (onclick="handleConfirmSubmitPhase1()")
                        // Ce code est un fallback de sécurité
                        const confirmBtn = document.getElementById('btnConfirmCreate');
                        if (confirmBtn && !confirmBtn.getAttribute('onclick')) {
                            confirmBtn.onclick = handleConfirmSubmitPhase1;
                        }
                    }

                    // Note: la logique étape 8 est gérée dans handleStepSpecificActions()

                    // Note: updateNavigationButtons gère déjà l'étape 8 nativement

                    // Exposer les fonctions nécessaires globalement
                    window.submitPhase1 = submitPhase1;
                    window.addAdherentTraditional = addAdherentTraditional;
                    window.removeTraditionalAdherent = removeTraditionalAdherent;
                    window.clearAllTraditionalAdherents = clearAllTraditionalAdherents;

                    console.log('🚀 Workflow 2 Phases intégré avec succès dans create.blade.php');
                </script>

                <!-- Validation NIP format XX-QQQQ-YYYYMMDD -->
                <script src="{{ asset('js/nip-validation.js') }}"></script>



                <!-- Script principal adapté pour 8 étapes -->
                <script>
                    // ========================================
                    // FONCTIONS DE NAVIGATION CORRIGÉES
                    // ========================================

                    /**
                     * Navigation entre les étapes (avec sauvegarde automatique)
                     */
                    // ══════════════════════════════════════════════
                    // SAUVEGARDE PAR ÉTAPE (BROUILLON SERVEUR)
                    // ══════════════════════════════════════════════

                    /**
                     * Collecter les données spécifiques à une étape frontend
                     */
                    function getStepDataPayload(frontendStep) {
                        const payload = {};

                        switch (frontendStep) {
                            case 1:
                                payload.type_organisation = OrganisationApp.organisationType ||
                                    document.querySelector('input[name="type_organisation"]:checked')?.value || '';
                                break;

                            case 2:
                                var guideCheck = document.getElementById('guideReadConfirm') || document.querySelector('[name="guide_read_confirm"]');
                                payload.guide_read_confirm = (guideCheck && guideCheck.checked) ? '1' : '';
                                break;

                            case 3:
                                ['demandeur_nip', 'demandeur_civilite', 'demandeur_nom', 'demandeur_prenom',
                                 'demandeur_date_naissance', 'demandeur_nationalite', 'demandeur_telephone',
                                 'demandeur_email', 'demandeur_adresse', 'demandeur_profession',
                                 'demandeur_role', 'demandeur_responsabilite'].forEach(field => {
                                    const el = document.getElementById(field) || document.querySelector('[name="' + field + '"]');
                                    if (el) payload[field] = el.type === 'checkbox' ? el.checked : el.value;
                                });
                                break;

                            case 4:
                                ['org_nom', 'org_sigle', 'org_objet', 'org_domaine_activite_id',
                                 'org_date_creation', 'org_telephone', 'org_email', 'org_site_web',
                                 'org_reseaux_sociaux'].forEach(field => {
                                    const el = document.getElementById(field) || document.querySelector('[name="' + field + '"]');
                                    if (el) payload[field] = el.value;
                                });
                                break;

                            case 5:
                                ['org_province_id', 'org_departement_id', 'org_commune_id',
                                 'org_arrondissement_id', 'org_quartier_id', 'org_canton_id',
                                 'org_regroupement_id', 'org_village_id', 'org_prefecture',
                                 'org_sous_prefecture', 'org_lieu_dit', 'org_latitude', 'org_longitude'].forEach(field => {
                                    const el = document.getElementById(field) || document.querySelector('[name="' + field + '"]');
                                    if (el) payload[field] = el.value;
                                });
                                // Champs mappés pour le backend
                                const adresseEl = document.querySelector('[name="org_adresse"]') || document.getElementById('org_adresse_complete');
                                if (adresseEl) payload.org_adresse_complete = adresseEl.value;
                                payload.org_province = payload.org_province_id || '';
                                payload.org_zone_type = document.getElementById('zone_type_input')?.value || 'urbaine';
                                break;

                            case 6:
                                payload.fondateurs = OrganisationApp.foundateurs || [];
                                payload.organization_type = OrganisationApp.organisationType || '';
                                payload.membresBureau = OrganisationApp.membresBureau || [];
                                break;

                            case 7: // Documents (backend step 8)
                                payload.documents = {};
                                Object.keys(OrganisationApp.uploadedDocuments).forEach(docType => {
                                    const doc = OrganisationApp.uploadedDocuments[docType];
                                    if (doc && doc.file) {
                                        payload.documents[docType] = { name: doc.file.name, size: doc.file.size };
                                    }
                                });
                                break;

                            case 8: // Validation finale (backend step 9)
                                ['declaration_veracite', 'declaration_conformite', 'declaration_autorisation'].forEach(field => {
                                    const el = document.getElementById(field);
                                    payload[field] = el ? el.checked : false;
                                });
                                break;
                        }

                        return payload;
                    }

                    /**
                     * Sauvegarder une étape sur le serveur (brouillon)
                     */
                    async function saveStepToServer(frontendStep) {
                        if (OrganisationApp.isSavingStep) return { success: true };

                        const backendStep = OrganisationApp.frontendToBackendStep[frontendStep];
                        if (!backendStep) return { success: true };

                        const data = getStepDataPayload(frontendStep);
                        OrganisationApp.isSavingStep = true;

                        // Indicateur visuel
                        const saveIndicator = document.getElementById('save-indicator');
                        if (saveIndicator) saveIndicator.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sauvegarde...';

                        try {
                            let csrfToken;
                            try {
                                csrfToken = await window.UnifiedCSRFManager.getCurrentToken();
                            } catch (e) {
                                csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                            }

                            const url = '/operator/organisations/step/' + backendStep + '/save';

                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({ data: data, session_id: null })
                            });

                            // Retry sur 419 (CSRF)
                            if (response.status === 419) {
                                try {
                                    csrfToken = await window.UnifiedCSRFManager.refreshFromServer();
                                } catch (e) {
                                    csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                                }
                                const retryResponse = await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({ data: data, session_id: null })
                                });
                                return await handleStepSaveResponse(retryResponse, frontendStep);
                            }

                            return await handleStepSaveResponse(response, frontendStep);

                        } catch (error) {
                            console.error('Erreur sauvegarde etape ' + frontendStep + ':', error);
                            if (saveIndicator) saveIndicator.innerHTML = '<i class="fas fa-exclamation-triangle text-warning mr-1"></i> Erreur sauvegarde';
                            return { success: false, error: error.message };
                        } finally {
                            OrganisationApp.isSavingStep = false;
                        }
                    }

                    /**
                     * Traiter la réponse de sauvegarde d'étape
                     */
                    async function handleStepSaveResponse(response, frontendStep) {
                        const saveIndicator = document.getElementById('save-indicator');

                        try {
                            const result = await response.json();

                            if (result.success) {
                                if (result.draft_id) {
                                    OrganisationApp.draftId = result.draft_id;
                                }
                                OrganisationApp.stepSaveErrors[frontendStep] = null;
                                if (saveIndicator) saveIndicator.innerHTML = '<i class="fas fa-check text-success mr-1"></i> Brouillon sauvegarde';
                                console.log('Etape ' + frontendStep + ' sauvegardee. Draft ID: ' + result.draft_id + ', completion: ' + result.completion_percentage + '%');

                                // Effacer le message après 3s
                                setTimeout(() => {
                                    if (saveIndicator) saveIndicator.innerHTML = '';
                                }, 3000);

                                return result;
                            } else {
                                OrganisationApp.stepSaveErrors[frontendStep] = result.errors || {};
                                if (saveIndicator) saveIndicator.innerHTML = '<i class="fas fa-exclamation-triangle text-warning mr-1"></i> Erreurs de validation';
                                console.warn('Erreurs sauvegarde etape ' + frontendStep + ':', result.errors);
                                return result;
                            }
                        } catch (e) {
                            if (saveIndicator) saveIndicator.innerHTML = '<i class="fas fa-times text-danger mr-1"></i> Erreur serveur';
                            return { success: false, error: 'Erreur parsing response' };
                        }
                    }

                    // ══════════════════════════════════════════════

                    async function changeStep(direction) {
                        console.log('Changement etape: direction ' + direction + ', etape actuelle: ' + OrganisationApp.currentStep);

                        // Sauvegarder les données de l'étape actuelle avant de changer
                        saveCurrentStepData();

                        // Validation avant d'avancer
                        if (direction === 1 && !validateCurrentStep()) {
                            console.log('Validation echouee pour etape', OrganisationApp.currentStep);
                            return false;
                        }

                        // Sauvegarde synchrone sur le serveur à chaque clic sur "Suivant"
                        if (direction === 1) {
                            var saveIndicator = document.getElementById('save-indicator');
                            if (saveIndicator) saveIndicator.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sauvegarde...';

                            try {
                                var result = await saveStepToServer(OrganisationApp.currentStep);
                                if (result && result.success) {
                                    console.log('Etape ' + OrganisationApp.currentStep + ' sauvegardee (draft_id=' + (result.draft_id || '?') + ')');
                                } else {
                                    console.warn('Sauvegarde serveur echouee, navigation autorisee', result);
                                }
                            } catch (e) {
                                console.warn('Erreur sauvegarde serveur:', e);
                            }
                        }

                        // Calculer la nouvelle étape
                        var newStep = OrganisationApp.currentStep + direction;

                        if (newStep >= 1 && newStep <= OrganisationApp.totalSteps) {
                            OrganisationApp.currentStep = newStep;
                            updateStepDisplay();
                            updateNavigationButtons();

                            // Actions spécifiques selon l'étape
                            handleStepSpecificActions(newStep);

                            // Sauvegarde cache local (localStorage)
                            saveToCache();

                            scrollToTop();
                            return true;
                        }

                        return false;
                    }

                    /**
                     * Actions spécifiques selon l'étape (CORRIGÉ pour 8 étapes)
                     */
                    function handleStepSpecificActions(stepNumber) {
                        switch (stepNumber) {
                            case 2:
                                updateGuideContent();
                                break;
                            case 4:
                                updateOrganizationRequirements();
                                break;
                            case 6:
                                updateFoundersRequirements();
                                break;
                            case 7: // Ex-étape 8 : Documents
                                updateDocumentsRequirements();
                                break;
                            case 8: // Soumission
                                generateFullRecap();
                                break;
                        }
                    }

                    /**
                     * Générer le récapitulatif complet de l'organisation
                     */
                    function generateFullRecap() {
                        var fd = OrganisationApp.formData || {};
                        var recapEl = document.getElementById('recap_content');
                        if (!recapEl) return;

                        // Sauvegarder les données actuelles
                        if (typeof saveCurrentStepData === 'function') saveCurrentStepData();
                        fd = OrganisationApp.formData || {};

                        var typeLabel = getOrganizationTypeLabel(OrganisationApp.organisationType) || OrganisationApp.organisationType || '-';

                        var getValue = function(key) { return fd[key] || '-'; };

                        var html = '';

                        // Section 1 : Type
                        html += '<div class="recap-section-title"><i class="fas fa-list-ul me-2"></i>Type d\'organisation</div>';
                        html += '<table class="table table-sm mb-0 recap-table"><tbody>';
                        html += '<tr><th>Type</th><td>' + typeLabel + '</td></tr>';
                        html += '</tbody></table>';

                        // Section 2 : Demandeur
                        html += '<div class="recap-section-title"><i class="fas fa-user me-2"></i>Informations du demandeur</div>';
                        html += '<table class="table table-sm mb-0 recap-table"><tbody>';
                        html += '<tr><th>NIP</th><td>' + getValue('demandeur_nip') + '</td></tr>';
                        html += '<tr><th>Nom</th><td>' + getValue('demandeur_nom') + '</td></tr>';
                        html += '<tr><th>Prenom</th><td>' + getValue('demandeur_prenom') + '</td></tr>';
                        html += '<tr><th>Email</th><td>' + getValue('demandeur_email') + '</td></tr>';
                        html += '<tr><th>Telephone</th><td>' + getValue('demandeur_telephone') + '</td></tr>';
                        html += '<tr><th>Role</th><td>' + getValue('demandeur_role') + '</td></tr>';
                        html += '</tbody></table>';

                        // Section 3 : Organisation
                        html += '<div class="recap-section-title"><i class="fas fa-building me-2"></i>Informations de l\'organisation</div>';
                        html += '<table class="table table-sm mb-0 recap-table"><tbody>';
                        html += '<tr><th>Nom</th><td>' + getValue('org_nom') + '</td></tr>';
                        html += '<tr><th>Sigle</th><td>' + getValue('org_sigle') + '</td></tr>';
                        html += '<tr><th>Objet</th><td><small>' + getValue('org_objet') + '</small></td></tr>';
                        html += '<tr><th>Date de creation</th><td>' + getValue('org_date_creation') + '</td></tr>';
                        html += '<tr><th>Telephone</th><td>' + getValue('org_telephone') + '</td></tr>';
                        html += '<tr><th>Email</th><td>' + getValue('org_email') + '</td></tr>';
                        html += '<tr><th>Site web</th><td>' + getValue('org_site_web') + '</td></tr>';
                        html += '</tbody></table>';

                        // Section 4 : Localisation
                        html += '<div class="recap-section-title"><i class="fas fa-map-marker-alt me-2"></i>Coordonnees et localisation</div>';
                        html += '<table class="table table-sm mb-0 recap-table"><tbody>';
                        var provinceSelect = document.querySelector('[name="org_province_id"]');
                        var provinceName = provinceSelect && provinceSelect.selectedIndex > 0 ? provinceSelect.options[provinceSelect.selectedIndex].text : getValue('org_province_id');
                        html += '<tr><th>Province</th><td>' + provinceName + '</td></tr>';
                        html += '<tr><th>Prefecture</th><td>' + getValue('org_prefecture') + '</td></tr>';
                        html += '<tr><th>Adresse</th><td>' + (getValue('org_adresse') !== '-' ? getValue('org_adresse') : getValue('org_adresse_complete')) + '</td></tr>';
                        var zoneType = document.getElementById('zone_type_input');
                        html += '<tr><th>Zone</th><td>' + (zoneType ? zoneType.value : '-') + '</td></tr>';
                        html += '</tbody></table>';

                        // Section 5 : Fondateurs
                        html += '<div class="recap-section-title"><i class="fas fa-users me-2"></i>Fondateurs (' + OrganisationApp.foundateurs.length + ')</div>';
                        if (OrganisationApp.foundateurs.length > 0) {
                            html += '<table class="table table-sm mb-0"><thead><tr><th>#</th><th>Nom</th><th>Prenom</th><th>NIP</th><th>Fonction</th></tr></thead><tbody>';
                            OrganisationApp.foundateurs.forEach(function(f, i) {
                                html += '<tr><td>' + (i + 1) + '</td><td>' + (f.nom || '-') + '</td><td>' + (f.prenom || '-') + '</td><td>' + (f.nip || '-') + '</td><td>' + (f.fonction || '-') + '</td></tr>';
                            });
                            html += '</tbody></table>';
                        } else {
                            html += '<div class="p-3 text-muted text-center"><i class="fas fa-exclamation-circle me-1"></i>Aucun fondateur ajouté</div>';
                        }

                        // Section 6 : Documents
                        var docs = OrganisationApp.uploadedDocuments || {};
                        var docKeys = Object.keys(docs);
                        html += '<div class="recap-section-title"><i class="fas fa-file-alt me-2"></i>Documents (' + docKeys.length + ')</div>';
                        if (docKeys.length > 0) {
                            html += '<table class="table table-sm mb-0"><tbody>';
                            docKeys.forEach(function(key) {
                                var d = docs[key];
                                var icon = d.type && d.type.startsWith('image/') ? 'fa-file-image text-info' : 'fa-file-pdf text-danger';
                                html += '<tr><td><i class="fas ' + icon + ' me-2"></i>' + getDocumentLabel(key) + '</td><td>' + (d.name || '-') + '</td><td class="text-muted">' + (d.size ? (d.size / 1024).toFixed(1) + ' Ko' : '') + '</td></tr>';
                            });
                            html += '</tbody></table>';
                        } else {
                            html += '<div class="p-3 text-muted text-center"><i class="fas fa-exclamation-circle me-1"></i>Aucun document charge</div>';
                        }

                        recapEl.innerHTML = html;
                    }

                    /**
                     * Basculer le style des cartes radio de soumission
                     */
                    function toggleSubmissionMode() {
                        var card1 = document.getElementById('card_phase1');
                        var card2 = document.getElementById('card_traditional');
                        var radio1 = document.getElementById('submissionPhase1Only');
                        var radio2 = document.getElementById('submissionTraditional');
                        var traditionalSection = document.getElementById('traditional-adherents-section');

                        card1.classList.remove('selected-green');
                        card2.classList.remove('selected-blue');

                        if (radio1 && radio1.checked) {
                            card1.classList.add('selected-green');
                            if (traditionalSection) traditionalSection.classList.add('d-none');
                        }
                        if (radio2 && radio2.checked) {
                            card2.classList.add('selected-blue');
                            if (traditionalSection) traditionalSection.classList.remove('d-none');
                        }
                    }

                    /**
                     * Compteur de caractères en temps réel
                     */
                    function updateCharCount(textarea, counterId, minLength) {
                        const counter = document.getElementById(counterId);
                        if (!counter) return;
                        const len = (textarea.value || '').length;
                        if (len >= minLength) {
                            counter.textContent = `${len} caractères ✓`;
                            counter.className = 'form-text text-success';
                            textarea.classList.remove('is-invalid');
                        } else {
                            counter.textContent = `${len} / ${minLength} min`;
                            counter.className = 'form-text text-danger';
                        }
                    }

                    /**
                     * Validation de l'étape actuelle
                     */
                    function validateCurrentStep() {
                        return validateStep(OrganisationApp.currentStep);
                    }

                    /**
                     * Validation d'une étape spécifique (CORRIGÉ pour 8 étapes)
                     */
                    function validateStep(stepNumber) {
                        switch (stepNumber) {
                            case 1: return validateStep1();
                            case 2: return validateStep2();
                            case 3: return validateStep3();
                            case 4: return validateStep4();
                            case 5: return validateStep5();
                            case 6: return validateStep6();
                            case 7: return validateStep7(); // Ex-étape 8 : Documents
                            case 8: return validateStep8(); // Ex-étape 9 : Soumission
                            default: return true;
                        }
                    }

                    /**
                     * Validation étape 1 : Type d'organisation
                     */
                    function validateStep1() {
                        const selectedType = document.querySelector('input[name="type_organisation"]:checked');
                        if (!selectedType) {
                            showNotification('Veuillez sélectionner un type d\'organisation', 'warning');
                            return false;
                        }

                        // Stocker le type sélectionné
                        OrganisationApp.organisationType = selectedType.value;
                        document.getElementById('organizationType').value = selectedType.value;

                        return true;
                    }

                    /**
                     * Validation étape 2 : Guide lu
                     */
                    function validateStep2() {
                        const guideConfirm = document.getElementById('guideReadConfirm');
                        if (!guideConfirm || !guideConfirm.checked) {
                            showNotification('Veuillez confirmer avoir lu et compris le guide', 'warning');
                            if (guideConfirm) guideConfirm.focus();
                            return false;
                        }
                        return true;
                    }

                    /**
                     * Utilitaire : valider un champ et retourner le message d'erreur
                     */
                    function validateField(fieldId, label, rules) {
                        const field = document.getElementById(fieldId);
                        if (!field) return null;

                        const val = (field.value || '').trim();
                        const feedback = field.closest('.mb-3, .mb-4, .col-md-6, .col-md-4, .col-12')
                                          ?.querySelector('.invalid-feedback');

                        // Vérifier required
                        if (rules.required && !val) {
                            field.classList.add('is-invalid');
                            if (feedback) feedback.textContent = `${label} est obligatoire`;
                            return `${label} est obligatoire`;
                        }
                        // Vérifier min length
                        if (rules.min && val.length > 0 && val.length < rules.min) {
                            field.classList.add('is-invalid');
                            if (feedback) feedback.textContent = `${label} : minimum ${rules.min} caractères (actuellement ${val.length})`;
                            return `${label} : minimum ${rules.min} caractères (actuellement ${val.length})`;
                        }
                        // Vérifier max length
                        if (rules.max && val.length > rules.max) {
                            field.classList.add('is-invalid');
                            if (feedback) feedback.textContent = `${label} : maximum ${rules.max} caractères`;
                            return `${label} : maximum ${rules.max} caractères`;
                        }
                        // Vérifier pattern
                        if (rules.pattern && val && !new RegExp(rules.pattern).test(val)) {
                            field.classList.add('is-invalid');
                            if (feedback) feedback.textContent = rules.patternMsg || `${label} : format invalide`;
                            return rules.patternMsg || `${label} : format invalide`;
                        }

                        field.classList.remove('is-invalid');
                        if (feedback) feedback.textContent = '';
                        return null;
                    }

                    /**
                     * Validation étape 3 : Demandeur
                     */
                    function validateStep3() {
                        const errors = [];

                        // Champs texte/select obligatoires avec règles
                        const fields = [
                            ['demandeur_nip', 'NIP', { required: true, pattern: '^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$', patternMsg: 'NIP : format attendu XX-0000-00000000' }],
                            ['demandeur_civilite', 'Civilité', { required: true }],
                            ['demandeur_nom', 'Nom', { required: true, max: 255 }],
                            ['demandeur_prenom', 'Prénom', { required: true, max: 255 }],
                            ['demandeur_date_naissance', 'Date de naissance', { required: true }],
                            ['demandeur_nationalite', 'Nationalité', { required: true }],
                            ['demandeur_telephone', 'Téléphone', { required: true, pattern: '^[0-9]{8,9}$', patternMsg: 'Téléphone : 8 ou 9 chiffres requis' }],
                            ['demandeur_email', 'Email', { required: true }],
                            ['demandeur_adresse', 'Adresse', { required: true, max: 500 }],
                            ['demandeur_role', 'Rôle / Qualité', { required: true }]
                        ];

                        fields.forEach(([id, label, rules]) => {
                            const err = validateField(id, label, rules);
                            if (err) errors.push(err);
                        });

                        // Checkboxes obligatoires
                        ['demandeur_engagement', 'demandeur_responsabilite'].forEach(checkId => {
                            const check = document.getElementById(checkId);
                            if (!check || !check.checked) {
                                if (check) check.classList.add('is-invalid');
                                const label = checkId === 'demandeur_engagement' ? "Engagement du demandeur" : "Responsabilité du demandeur";
                                errors.push(`${label} doit être coché`);
                            } else if (check) {
                                check.classList.remove('is-invalid');
                            }
                        });

                        if (errors.length > 0) {
                            showNotification('Champs à corriger :<br>• ' + errors.join('<br>• '), 'warning');
                        }
                        return errors.length === 0;
                    }

                    /**
                     * Validation étape 4 : Organisation
                     */
                    function validateStep4() {
                        const errors = [];

                        const fields = [
                            ['org_nom', 'Nom de l\'organisation', { required: true, max: 255 }],
                            ['org_objet', 'Objet social / Mission', { required: true, min: 50 }],
                            ['org_date_creation', 'Date de création', { required: true }],
                            ['org_telephone', 'Téléphone principal', { required: true, pattern: '^[0-9]{8,9}$', patternMsg: 'Téléphone : 8 ou 9 chiffres requis' }]
                        ];

                        fields.forEach(([id, label, rules]) => {
                            const err = validateField(id, label, rules);
                            if (err) errors.push(err);
                        });

                        if (errors.length > 0) {
                            showNotification('Champs à corriger :<br>• ' + errors.join('<br>• '), 'warning');
                        }
                        return errors.length === 0;
                    }

                    /**
                     * Validation étape 5 : Coordonnées et Localisation
                     */
                    function validateStep5() {
                        const errors = [];

                        const fields = [
                            ['org_province', 'Province', { required: true }],
                            ['org_departement', 'Département', { required: true }]
                        ];

                        fields.forEach(([id, label, rules]) => {
                            const err = validateField(id, label, rules);
                            if (err) errors.push(err);
                        });

                        // Adresse : id = org_adresse_complete OU name = org_adresse
                        const adresseField = document.getElementById('org_adresse_complete') || document.querySelector('[name="org_adresse"]');
                        if (adresseField && (!adresseField.value || adresseField.value.trim() === '')) {
                            adresseField.classList.add('is-invalid');
                            errors.push('Adresse / Siège social est obligatoire');
                        } else if (adresseField) {
                            adresseField.classList.remove('is-invalid');
                        }

                        if (errors.length > 0) {
                            showNotification('Champs à corriger :<br>• ' + errors.join('<br>• '), 'warning');
                        }
                        return errors.length === 0;
                    }

                    /**
                     * Validation étape 6 : Fondateurs
                     */
                    function validateStep6() {
                        const typeData = window.typesOrganisationData ? window.typesOrganisationData[OrganisationApp.organisationType] : null;
                        const minRequired = typeData ? typeData.nb_min_fondateurs_majeurs : 3;
                        const currentCount = OrganisationApp.foundateurs.length;

                        if (currentCount < minRequired) {
                            showNotification(
                                `<strong>Fondateurs insuffisants</strong><br>` +
                                `Vous avez ajouté <strong>${currentCount}</strong> fondateur(s).<br>` +
                                `Le minimum requis pour ce type d'organisation est de <strong>${minRequired}</strong> fondateur(s) majeur(s).`,
                                'warning'
                            );
                            return false;
                        }
                        return true;
                    }

                    /**
                     * Validation étape 7 : Documents (ex-étape 8)
                     */
                    function validateStep7() {
                        if (!OrganisationApp.organisationType) {
                            showNotification('Type d\'organisation non défini', 'error');
                            return false;
                        }

                        const requirements = OrganisationApp.documentRequirements[OrganisationApp.organisationType];
                        if (!requirements) return true;

                        const missingDocs = [];

                        // Vérifier les documents obligatoires
                        requirements.required.forEach(docType => {
                            const fileInput = document.getElementById(`doc_${docType}`);
                            if (!fileInput || !fileInput.files[0]) {
                                missingDocs.push(getDocumentLabel(docType));
                            }
                        });

                        if (missingDocs.length > 0) {
                            showNotification(
                                `Documents obligatoires manquants :<br>• ${missingDocs.join('<br>• ')}`,
                                'warning'
                            );

                            // Highlight des champs manquants
                            requirements.required.forEach(docType => {
                                const fileInput = document.getElementById(`doc_${docType}`);
                                if (!fileInput || !fileInput.files[0]) {
                                    fileInput?.classList.add('is-invalid');
                                }
                            });

                            return false;
                        }

                        // Valider les fichiers uploadés
                        const invalidFiles = [];
                        requirements.required.forEach(docType => {
                            const fileInput = document.getElementById(`doc_${docType}`);
                            if (fileInput && fileInput.files[0]) {
                                const file = fileInput.files[0];

                                // Vérifier la taille
                                if (file.size > 5 * 1024 * 1024) {
                                    invalidFiles.push(`${getDocumentLabel(docType)} (trop volumineux)`);
                                }

                                // Vérifier le type
                                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                                if (!allowedTypes.includes(file.type)) {
                                    invalidFiles.push(`${getDocumentLabel(docType)} (format non autorisé)`);
                                }
                            }
                        });

                        if (invalidFiles.length > 0) {
                            showNotification(
                                `Fichiers invalides :<br>• ${invalidFiles.join('<br>• ')}`,
                                'error'
                            );
                            return false;
                        }

                        return true;
                    }

                    /**
                     * Validation étape 8 : Soumission (ex-étape 9)
                     */
                    function validateStep8() {
                        const requiredChecks = [
                            'declaration_veracite', 'declaration_conformite', 'declaration_autorisation'
                        ];

                        let isValid = true;

                        requiredChecks.forEach(checkId => {
                            const check = document.getElementById(checkId);
                            if (!check || !check.checked) {
                                if (check) check.classList.add('is-invalid');
                                isValid = false;
                            } else if (check) {
                                check.classList.remove('is-invalid');
                            }
                        });

                        // Vérification spéciale pour parti politique
                        if (OrganisationApp.organisationType === 'parti_politique') {
                            const partiCheck = document.getElementById('declaration_exclusivite_parti');
                            if (partiCheck && !partiCheck.checked) {
                                partiCheck.classList.add('is-invalid');
                                isValid = false;
                            } else if (partiCheck) {
                                partiCheck.classList.remove('is-invalid');
                            }
                        }

                        if (!isValid) {
                            showNotification('Veuillez accepter toutes les déclarations obligatoires', 'warning');
                        }

                        return isValid;
                    }

                    /**
                     * Mise à jour de l'affichage des étapes
                     */
                    function updateStepDisplay() {
                        // Masquer toutes les étapes
                        document.querySelectorAll('.step-content').forEach(step => {
                            step.style.display = 'none';
                        });

                        // Afficher l'étape actuelle
                        const currentStepElement = document.getElementById(`step${OrganisationApp.currentStep}`);
                        if (currentStepElement) {
                            currentStepElement.style.display = 'block';
                        }

                        // Mettre à jour les indicateurs
                        updateStepIndicators();
                        updateProgressBar();
                    }

                    /**
                     * Mise à jour des indicateurs d'étapes
                     */
                    function updateStepIndicators() {
                        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                            const stepNumber = index + 1;
                            indicator.classList.remove('active', 'completed');

                            if (stepNumber < OrganisationApp.currentStep) {
                                indicator.classList.add('completed');
                            } else if (stepNumber === OrganisationApp.currentStep) {
                                indicator.classList.add('active');
                            }
                        });
                    }

                    /**
                     * Mise à jour de la barre de progression
                     */
                    function updateProgressBar() {
                        const progressBar = document.getElementById('globalProgress');
                        const currentStepNumber = document.getElementById('currentStepNumber');

                        if (progressBar) {
                            const percentage = (OrganisationApp.currentStep / OrganisationApp.totalSteps) * 100;
                            progressBar.style.width = percentage + '%';
                        }

                        if (currentStepNumber) {
                            currentStepNumber.textContent = OrganisationApp.currentStep;
                        }
                    }

                    /**
             * Mise à jour des boutons de navigation
             */
                    function updateNavigationButtons() {
                        const prevBtn = document.getElementById('prevBtn');
                        const nextBtn = document.getElementById('nextBtn');
                        const submitBtn = document.getElementById('submitBtn');
                        const submitPhase1Btn = document.getElementById('submitPhase1Btn');
                        const submitTraditionalBtn = document.getElementById('submitTraditionalBtn');
                        const saveDraftBtn = document.getElementById('saveDraftBtn');
                        const submissionInfo = document.getElementById('submission-info');

                        console.log('updateNavigationButtons - Etape: ' + OrganisationApp.currentStep + '/' + OrganisationApp.totalSteps);

                        // Bouton précédent
                        if (prevBtn) {
                            if (OrganisationApp.currentStep > 1) {
                                prevBtn.style.display = 'inline-block';
                            } else {
                                prevBtn.style.display = 'none';
                            }
                        }

                        // Bouton "Garder en brouillon" - visible à partir de l'étape 2
                        if (saveDraftBtn) {
                            if (OrganisationApp.currentStep >= 2) {
                                saveDraftBtn.style.display = 'inline-block';
                            } else {
                                saveDraftBtn.style.display = 'none';
                            }
                        }

                        // ✅ CORRECTION : Traiter l'étape 8 comme la DERNIÈRE étape (soumission)
                        if (OrganisationApp.currentStep === OrganisationApp.totalSteps) {
                            // DERNIÈRE ÉTAPE (8) : Soumission - Afficher boutons workflow
                            if (nextBtn) nextBtn.style.display = 'none';
                            if (submitBtn) submitBtn.style.display = 'none';

                            // Afficher les informations de soumission
                            if (submissionInfo) submissionInfo.style.display = 'block';

                            // Décider quel bouton afficher selon le workflow
                            const adherentsCount = (OrganisationApp.adherents || []).length;
                            console.log(`📊 Analyse volume adhérents: ${adherentsCount}`);

                            // Par défaut, toujours afficher le bouton Phase 1 pour le workflow 2 phases
                            if (submitPhase1Btn) {
                                submitPhase1Btn.style.display = 'inline-block';
                                console.log('✅ Bouton Phase 1 affiché');
                            }

                            // Optionnel : afficher aussi le bouton traditionnel selon les préférences
                            const submissionMode = document.querySelector('input[name="submission_mode"]:checked');
                            if (submissionMode && submissionMode.value === 'traditional') {
                                if (submitTraditionalBtn) {
                                    submitTraditionalBtn.style.display = 'inline-block';
                                    console.log('✅ Bouton traditionnel aussi affiché');
                                }
                            } else {
                                if (submitTraditionalBtn) submitTraditionalBtn.style.display = 'none';
                            }

                            console.log('✅ Dernière étape (8 - Soumission) - Boutons workflow affichés');

                        } else {
                            // Toutes les autres étapes (1-7) : bouton suivant visible, soumission masquée
                            if (nextBtn) nextBtn.style.display = 'inline-block';
                            if (submitBtn) submitBtn.style.display = 'none';
                            if (submitPhase1Btn) submitPhase1Btn.style.display = 'none';
                            if (submitTraditionalBtn) submitTraditionalBtn.style.display = 'none';
                            if (submissionInfo) submissionInfo.style.display = 'none';

                            console.log(`✅ Étape ${OrganisationApp.currentStep} - Bouton suivant affiché`);
                        }
                    }


                    /**
                     * Sauvegarder les données de l'étape actuelle
                     */
                    function saveCurrentStepData() {
                        const currentStepElement = document.getElementById(`step${OrganisationApp.currentStep}`);
                        if (!currentStepElement) return;

                        const inputs = currentStepElement.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            const name = input.name || input.id;
                            if (!name) return;

                            if (input.type === 'checkbox' || input.type === 'radio') {
                                if (input.checked) {
                                    OrganisationApp.formData[name] = input.value;
                                }
                            } else if (input.type === 'file') {
                                // Pour les fichiers, stocker les métadonnées
                                if (input.files && input.files.length > 0) {
                                    OrganisationApp.formData[name] = {
                                        hasFile: true,
                                        fileName: input.files[0].name,
                                        fileSize: input.files[0].size
                                    };
                                }
                            } else {
                                OrganisationApp.formData[name] = input.value;
                            }
                        });

                        // Déclencher la sauvegarde en cache
                        saveToCache();
                    }

                    /**
                     * Générer le récapitulatif pour l'étape 8 (ex-étape 9)
                     */
                    function generateRecap() {
                        const recapContainer = document.getElementById('recap_content');
                        if (!recapContainer) return;

                        let recapHtml = '<div class="row">';

                        // Type d'organisation
                        if (OrganisationApp.organisationType) {
                            recapHtml += `
                                                                        <div class="col-md-6 mb-3">
                                                                            <div class="card border-0 shadow-sm">
                                                                                <div class="card-body">
                                                                                    <h6 class="text-primary"><i class="fas fa-list-ul me-2"></i>Type d'organisation</h6>
                                                                                    <p class="mb-0 fw-bold">${getOrganizationTypeLabel(OrganisationApp.organisationType)}</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    `;
                        }

                        // Nom de l'organisation
                        const orgNom = document.getElementById('org_nom');
                        if (orgNom && orgNom.value) {
                            recapHtml += `
                                                                        <div class="col-md-6 mb-3">
                                                                            <div class="card border-0 shadow-sm">
                                                                                <div class="card-body">
                                                                                    <h6 class="text-primary"><i class="fas fa-building me-2"></i>Nom de l'organisation</h6>
                                                                                    <p class="mb-0 fw-bold">${orgNom.value}</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    `;
                        }

                        // Demandeur
                        const demandeurNom = document.getElementById('demandeur_nom');
                        const demandeurPrenom = document.getElementById('demandeur_prenom');
                        const demandeurRole = document.getElementById('demandeur_role');
                        if (demandeurNom && demandeurPrenom && demandeurNom.value && demandeurPrenom.value) {
                            recapHtml += `
                                                                        <div class="col-md-6 mb-3">
                                                                            <div class="card border-0 shadow-sm">
                                                                                <div class="card-body">
                                                                                    <h6 class="text-primary"><i class="fas fa-user me-2"></i>Demandeur principal</h6>
                                                                                    <p class="mb-1 fw-bold">${demandeurPrenom.value} ${demandeurNom.value}</p>
                                                                                    <small class="text-muted">${demandeurRole ? demandeurRole.options[demandeurRole.selectedIndex]?.text || '' : ''}</small>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    `;
                        }

                        // Nombre de fondateurs
                        const recapTypeData = window.typesOrganisationData[OrganisationApp.organisationType];
                        const recapMinFondateurs = recapTypeData ? recapTypeData.nb_min_fondateurs_majeurs : 3;
                        recapHtml += `
                                                                    <div class="col-md-6 mb-3">
                                                                        <div class="card border-0 shadow-sm">
                                                                            <div class="card-body">
                                                                                <h6 class="text-primary"><i class="fas fa-users me-2"></i>Fondateurs</h6>
                                                                                <p class="mb-0 fw-bold">${OrganisationApp.foundateurs.length} fondateur(s)</p>
                                                                                <small class="text-muted">
                                                                                    ${OrganisationApp.foundateurs.length >= recapMinFondateurs ?
                                '<i class="fas fa-check text-success me-1"></i>Minimum requis atteint' :
                                '<i class="fas fa-exclamation-triangle text-warning me-1"></i>Minimum requis: ' + recapMinFondateurs
                            }
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                `;

                        // Localisation
                        const province = document.getElementById('org_province');
                        const prefecture = document.getElementById('org_prefecture');
                        if (province && prefecture && province.value && prefecture.value) {
                            recapHtml += `
                                                                        <div class="col-md-6 mb-3">
                                                                            <div class="card border-0 shadow-sm">
                                                                                <div class="card-body">
                                                                                    <h6 class="text-primary"><i class="fas fa-map-marker-alt me-2"></i>Localisation</h6>
                                                                                    <p class="mb-0 fw-bold">${prefecture.value}, ${province.value}</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    `;
                        }

                        // Documents uploadés
                        const documentsUploaded = Object.keys(OrganisationApp.uploadedDocuments || {}).length;
                        const requirements = OrganisationApp.documentRequirements[OrganisationApp.organisationType];
                        const documentsRequired = requirements ? requirements.required.length : 0;

                        recapHtml += `
                                                                    <div class="col-md-6 mb-3">
                                                                        <div class="card border-0 shadow-sm">
                                                                            <div class="card-body">
                                                                                <h6 class="text-primary"><i class="fas fa-file-alt me-2"></i>Documents</h6>
                                                                                <p class="mb-0 fw-bold">${documentsUploaded} / ${documentsRequired} obligatoires</p>
                                                                                <small class="text-muted">
                                                                                    ${documentsUploaded >= documentsRequired ?
                                '<i class="fas fa-check text-success me-1"></i>Documents complets' :
                                '<i class="fas fa-exclamation-triangle text-warning me-1"></i>Documents manquants'
                            }
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                `;

                        recapHtml += '</div>';

                        // Ajout d'un message de validation globale
                        const isValid = OrganisationApp.foundateurs.length >= recapMinFondateurs && documentsUploaded >= documentsRequired;

                        if (isValid) {
                            recapHtml += `
                                                                        <div class="alert alert-success border-0 mt-4">
                                                                            <div class="d-flex align-items-center">
                                                                                <i class="fas fa-check-circle fa-2x me-3"></i>
                                                                                <div>
                                                                                    <h6 class="alert-heading mb-1">Dossier prêt pour soumission</h6>
                                                                                    <p class="mb-0">Toutes les exigences minimales sont respectées. Vous pouvez procéder à la soumission.</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    `;
                        } else {
                            recapHtml += `
                                                                        <div class="alert alert-warning border-0 mt-4">
                                                                            <div class="d-flex align-items-center">
                                                                                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                                                                <div>
                                                                                    <h6 class="alert-heading mb-1">Dossier incomplet</h6>
                                                                                    <p class="mb-0">Veuillez compléter toutes les sections obligatoires avant la soumission.</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    `;
                        }

                        recapContainer.innerHTML = recapHtml;
                    }

                    /**
                     * Mettre à jour le contenu du guide selon le type
                     */
                    function updateGuideContent() {
                        const guideContent = document.getElementById('guide-content');
                        const selectedTypeTitle = document.getElementById('selectedTypeTitle');

                        if (!OrganisationApp.organisationType) return;

                        if (selectedTypeTitle) {
                            selectedTypeTitle.textContent = getOrganizationTypeLabel(OrganisationApp.organisationType);
                        }

                        if (guideContent) {
                            const content = getGuideContentForType(OrganisationApp.organisationType);
                            guideContent.innerHTML = content;
                        }
                    }

                    /**
                     * Contenu du guide selon le type d'organisation - DYNAMIQUE DEPUIS LA DB
                     */
                    function getGuideContentForType(type) {
                        const typeData = window.typesOrganisationData[type];
                        if (!typeData) {
                            return `<div class="alert alert-info border-0 mb-4 shadow-sm">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-info-circle fa-3x me-3 text-info"></i>
                                                        <div>
                                                            <h5 class="alert-heading mb-1">Guide spécifique à votre type d'organisation</h5>
                                                            <p class="mb-0">Le contenu s'affichera selon votre sélection à l'étape précédente</p>
                                                        </div>
                                                    </div>
                                                </div>`;
                        }

                        const couleur = typeData.couleur || '#009e3f';
                        const icone = typeData.icone || 'fa-building';

                        // Section en-tête du guide
                        let html = `
                                        <div class="alert border-0 mb-4 shadow-sm" style="background-color: ${couleur}15; border-left: 4px solid ${couleur} !important;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas ${icone} fa-3x me-3" style="color: ${couleur};"></i>
                                                <div>
                                                    <h5 class="alert-heading mb-1">Guide pour créer : ${typeData.nom}</h5>
                                                    <p class="mb-0 text-muted">${typeData.description || ''}</p>
                                                </div>
                                            </div>
                                        </div>`;

                        // Loi de référence
                        if (typeData.loi_reference) {
                            html += `
                                        <div class="alert alert-light border mb-4">
                                            <i class="fas fa-gavel me-2" style="color: ${couleur};"></i>
                                            <strong>Base légale :</strong> ${typeData.loi_reference}
                                        </div>`;
                        }

                        html += `<div class="row">`;

                        // Colonne gauche : Exigences
                        html += `
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-header border-0" style="background-color: ${couleur}15;">
                                                    <h6 class="mb-0" style="color: ${couleur};"><i class="fas fa-clipboard-check me-2"></i>Exigences minimales</h6>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-unstyled mb-0">
                                                        <li class="mb-2"><i class="fas fa-users me-2" style="color: ${couleur};"></i>Min. <strong>${typeData.nb_min_fondateurs_majeurs}</strong> fondateur(s) majeur(s)</li>
                                                        <li class="mb-2"><i class="fas fa-user-friends me-2" style="color: ${couleur};"></i>Min. <strong>${typeData.nb_min_adherents_creation}</strong> adhérent(s) à la création</li>
                                                        <li class="mb-2"><i class="fas fa-${typeData.is_lucratif ? 'coins' : 'heart'} me-2" style="color: ${couleur};"></i>But ${typeData.is_lucratif ? 'lucratif' : 'non lucratif'}</li>
                                                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2" style="color: ${couleur};"></i>Siège social au Gabon</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>`;

                        // Colonne droite : Documents requis (depuis la relation pivot)
                        const docsObligatoires = typeData.documents.filter(d => d.is_obligatoire);
                        const docsFacultatifs = typeData.documents.filter(d => !d.is_obligatoire);

                        html += `
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-header border-0" style="background-color: ${couleur}15;">
                                                    <h6 class="mb-0" style="color: ${couleur};"><i class="fas fa-file-alt me-2"></i>Documents requis</h6>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-unstyled mb-0">`;

                        if (docsObligatoires.length > 0) {
                            docsObligatoires.forEach(doc => {
                                html += `<li class="mb-2"><i class="fas fa-check-circle me-2 text-success"></i>${doc.nom} <span class="badge bg-danger ms-1">Obligatoire</span></li>`;
                            });
                        }
                        if (docsFacultatifs.length > 0) {
                            docsFacultatifs.forEach(doc => {
                                html += `<li class="mb-2"><i class="fas fa-minus-circle me-2 text-muted"></i>${doc.nom} <span class="badge bg-secondary ms-1">Facultatif</span></li>`;
                            });
                        }
                        if (typeData.documents.length === 0) {
                            html += `<li class="text-muted"><i class="fas fa-info-circle me-2"></i>Aucun document spécifique configuré</li>`;
                        }

                        html += `       </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`;

                        // Guide de création (texte riche depuis la DB)
                        if (typeData.guide_creation) {
                            html += `
                                        <div class="card border-0 shadow-sm mt-3">
                                            <div class="card-header border-0" style="background-color: ${couleur}15;">
                                                <h6 class="mb-0" style="color: ${couleur};"><i class="fas fa-book-open me-2"></i>Guide de création</h6>
                                            </div>
                                            <div class="card-body guide-creation-content">
                                                ${formatGuideContent(typeData.guide_creation)}
                                            </div>
                                        </div>`;
                        }

                        // Texte législatif
                        if (typeData.texte_legislatif) {
                            html += `
                                        <div class="card border-0 shadow-sm mt-3">
                                            <div class="card-header border-0 bg-light">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-balance-scale me-2"></i>Cadre législatif</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-muted" style="font-style: italic; font-size: 0.95em;">
                                                    ${formatGuideContent(typeData.texte_legislatif)}
                                                </div>
                                            </div>
                                        </div>`;
                        }

                        return html;
                    }

                    /**
                     * Formater le contenu Markdown simplifié en HTML
                     */
                    function formatGuideContent(text) {
                        if (!text) return '';
                        let html = text
                            .replace(/^### (.+)$/gm, '<h6 class="fw-bold mt-3 mb-2">$1</h6>')
                            .replace(/^## (.+)$/gm, '<h5 class="fw-bold mt-3 mb-2">$1</h5>')
                            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                            .replace(/^- (.+)$/gm, '<li>$1</li>')
                            .replace(/^(\d+)\. (.+)$/gm, '<li>$2</li>')
                            .replace(/\n\n/g, '</p><p>')
                            .replace(/\n/g, '<br>');
                        // Envelopper les <li> dans des <ul>
                        html = html.replace(/(<li>.*?<\/li>)+/gs, '<ul class="mb-2">$&</ul>');
                        return `<p>${html}</p>`;
                    }

                    /**
                     * Mettre à jour les exigences des fondateurs - DYNAMIQUE DEPUIS LA DB
                     */
                    function updateFoundersRequirements() {
                        const requirementsDiv = document.getElementById('fondateurs_requirements');
                        if (!requirementsDiv) return;

                        const typeData = window.typesOrganisationData[OrganisationApp.organisationType];
                        const minRequired = typeData ? typeData.nb_min_fondateurs_majeurs : 3;
                        const minFoundersEl = document.getElementById('min_fondateurs');
                        if (minFoundersEl) {
                            minFoundersEl.textContent = minRequired;
                        }
                    }

                    /**
                     * Mettre à jour les exigences des documents
                     */
                    function updateDocumentsRequirements() {
                        const documentsContainer = document.getElementById('documents_container');
                        if (!documentsContainer || !OrganisationApp.organisationType) return;

                        const requirements = OrganisationApp.documentRequirements[OrganisationApp.organisationType];
                        if (!requirements) return;

                        const allDocuments = [...requirements.required, ...requirements.optional];

                        let documentsHtml = `
                                                                    <div class="alert alert-info border-0 mb-4">
                                                                        <h6 class="alert-heading">
                                                                            <i class="fas fa-info-circle me-2"></i>
                                                                            Documents requis pour ${getOrganizationTypeLabel(OrganisationApp.organisationType)}
                                                                        </h6>
                                                                        <p class="mb-2">
                                                                            <strong>Documents obligatoires :</strong> ${requirements.required.length}
                                                                            ${requirements.optional.length > 0 ? `| <strong>Documents optionnels :</strong> ${requirements.optional.length}` : ''}
                                                                        </p>
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-upload me-1"></i>
                                                                            Formats acceptés : PDF, JPG, PNG (taille max : 5MB par fichier)
                                                                        </small>
                                                                    </div>
                                                                `;

                        allDocuments.forEach(doc => {
                            const isRequired = requirements.required.includes(doc);
                            const label = getDocumentLabel(doc);
                            const existing = OrganisationApp.uploadedDocuments[doc];

                            documentsHtml += `
                                <div class="card border-0 shadow-sm mb-3" id="doc_card_${doc}">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h6 class="mb-1">
                                                    ${isRequired ? '<span class="text-danger">*</span> ' : ''}
                                                    ${label}
                                                </h6>
                                                <div id="upload_zone_${doc}">
                                                    <input type="file"
                                                           class="form-control"
                                                           id="doc_${doc}"
                                                           name="documents[${doc}]"
                                                           accept=".pdf,.jpg,.jpeg,.png"
                                                           onchange="handleDocumentUpload('${doc}', this)"
                                                           ${isRequired ? 'required' : ''}>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        ${isRequired ? 'Document obligatoire' : 'Document optionnel'} - PDF, JPG, PNG (max 5MB)
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div id="status_${doc}" class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>En attente
                                                </div>
                                                <div class="progress mt-2 d-none" id="progress_container_${doc}" style="height: 6px;">
                                                    <div class="progress-bar bg-info" id="progress_${doc}" style="width: 0%"></div>
                                                </div>
                                                <div id="preview_${doc}" class="mt-2 d-none"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        documentsContainer.innerHTML = documentsHtml;

                        // Restaurer l'état des fichiers déjà chargés
                        allDocuments.forEach(doc => {
                            const existing = OrganisationApp.uploadedDocuments[doc];
                            if (existing && existing.uploaded) {
                                restoreDocumentPreview(doc, existing);
                            }
                        });
                    }

                    /**
                     * Restaurer la preview d'un document déjà chargé
                     */
                    function restoreDocumentPreview(docType, docData) {
                        const statusEl = document.getElementById('status_' + docType);
                        const previewEl = document.getElementById('preview_' + docType);

                        if (statusEl) {
                            statusEl.innerHTML = '<i class="fas fa-check me-1 text-success"></i>Fichier charge';
                        }

                        if (previewEl) {
                            var previewHtml = '';
                            if (docData.type && docData.type.startsWith('image/') && docData.file) {
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    previewEl.innerHTML = '<div class="d-flex align-items-center gap-2">' +
                                        '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 80px;">' +
                                        '<div><small class="d-block text-muted">' + docData.name + '</small>' +
                                        '<small class="text-muted">(' + (docData.size / 1024).toFixed(1) + ' Ko)</small></div>' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="removeDocument(\'' + docType + '\')">' +
                                        '<i class="fas fa-trash"></i></button></div>';
                                    previewEl.classList.remove('d-none');
                                };
                                reader.readAsDataURL(docData.file);
                            } else {
                                previewEl.innerHTML = '<div class="d-flex align-items-center gap-2">' +
                                    '<i class="fas fa-file-pdf text-danger fa-2x"></i>' +
                                    '<div><small class="d-block text-muted">' + (docData.name || 'Document') + '</small>' +
                                    '<small class="text-muted">(' + (docData.size / 1024).toFixed(1) + ' Ko)</small></div>' +
                                    '<button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="removeDocument(\'' + docType + '\')">' +
                                    '<i class="fas fa-trash"></i></button></div>';
                                previewEl.classList.remove('d-none');
                            }
                        }
                    }

                    /**
                     * Supprimer un document chargé
                     */
                    function removeDocument(docType) {
                        delete OrganisationApp.uploadedDocuments[docType];

                        var fileInput = document.getElementById('doc_' + docType);
                        if (fileInput) fileInput.value = '';

                        var statusEl = document.getElementById('status_' + docType);
                        if (statusEl) statusEl.innerHTML = '<i class="fas fa-clock me-1"></i>En attente';

                        var previewEl = document.getElementById('preview_' + docType);
                        if (previewEl) {
                            previewEl.innerHTML = '';
                            previewEl.classList.add('d-none');
                        }

                        console.log('Document supprime:', docType);
                    }

                        documentsContainer.innerHTML = documentsHtml;
                    }

                    /**
                     * Obtenir le label d'un document
                     */
                    function getDocumentLabel(doc) {
                        const labels = {
                            'statuts': 'Statuts de l\'organisation',
                            'pv_ag': 'Procès-verbal de l\'assemblée générale constitutive',
                            'liste_fondateurs': 'Liste des membres fondateurs',
                            'justif_siege': 'Justificatif du siège social',
                            'projet_social': 'Projet social détaillé',
                            'budget_previsionnel': 'Budget prévisionnel',
                            'programme_politique': 'Programme politique',
                            'liste_50_adherents': 'Liste de 50 adhérents minimum',
                            'expose_doctrine': 'Exposé de la doctrine religieuse',
                            'justif_lieu_culte': 'Justificatif du lieu de culte'
                        };
                        return labels[doc] || doc;
                    }

                    /**
                     * Gestion upload document
                     */
                    function handleDocumentUpload(docType, fileInput) {
                        const file = fileInput.files[0];
                        if (!file) return;

                        // Validation de la taille
                        if (file.size > 5 * 1024 * 1024) {
                            showNotification('Le fichier ne peut pas dépasser 5MB', 'error');
                            fileInput.value = '';
                            return;
                        }

                        // Validation du type
                        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                        if (!allowedTypes.includes(file.type)) {
                            showNotification('Type de fichier non autorisé. Utilisez PDF, JPG ou PNG.', 'error');
                            fileInput.value = '';
                            return;
                        }

                        // Mise à jour du statut
                        const statusElement = document.getElementById(`status_${docType}`);
                        const progressContainer = document.getElementById(`progress_container_${docType}`);
                        const progressBar = document.getElementById(`progress_${docType}`);
                        const previewElement = document.getElementById(`preview_${docType}`);

                        if (statusElement) {
                            statusElement.innerHTML = '<i class="fas fa-upload me-1 text-info"></i>Fichier sélectionné';
                        }

                        // Simulation du progress (à remplacer par vraie logique d'upload)
                        if (progressContainer && progressBar) {
                            progressContainer.classList.remove('d-none');
                            let progress = 0;
                            const interval = setInterval(() => {
                                progress += 10;
                                progressBar.style.width = progress + '%';

                                if (progress >= 100) {
                                    clearInterval(interval);
                                    setTimeout(() => {
                                        progressContainer.classList.add('d-none');
                                        if (statusElement) {
                                            statusElement.innerHTML = '<i class="fas fa-check me-1 text-success"></i>Fichier téléchargé';
                                        }

                                        // Aperçu avec bouton supprimer
                                        if (file.type.startsWith('image/') && previewElement) {
                                            const reader = new FileReader();
                                            reader.onload = function (e) {
                                                previewElement.innerHTML = '<div class="d-flex align-items-center gap-2">' +
                                                    '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 80px;">' +
                                                    '<div><small class="d-block text-muted">' + file.name + '</small>' +
                                                    '<small class="text-muted">(' + (file.size / 1024).toFixed(1) + ' Ko)</small></div>' +
                                                    '<button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="removeDocument(\'' + docType + '\')">' +
                                                    '<i class="fas fa-trash"></i></button></div>';
                                                previewElement.classList.remove('d-none');
                                            };
                                            reader.readAsDataURL(file);
                                        } else if (previewElement) {
                                            previewElement.innerHTML = '<div class="d-flex align-items-center gap-2">' +
                                                '<i class="fas fa-file-pdf text-danger fa-2x"></i>' +
                                                '<div><small class="d-block text-muted">' + file.name + '</small>' +
                                                '<small class="text-muted">(' + (file.size / 1024).toFixed(1) + ' Ko)</small></div>' +
                                                '<button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="removeDocument(\'' + docType + '\')">' +
                                                '<i class="fas fa-trash"></i></button></div>';
                                            previewElement.classList.remove('d-none');
                                        }
                                    }, 500);
                                }
                            }, 100);
                        }

                        // Stocker le fichier dans l'application
                        OrganisationApp.uploadedDocuments[docType] = {
                            file: file,
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            uploaded: true
                        };

                        console.log('Document uploadé:', docType, file.name);
                    }

                    /**
                     * Obtenir le label d'un type d'organisation - DYNAMIQUE DEPUIS LA DB
                     */
                    function getOrganizationTypeLabel(type) {
                        const typeData = window.typesOrganisationData[type];
                        return typeData ? typeData.nom : type;
                    }

                    /**
                     * Afficher une notification
                     */
                    function showNotification(message, type = 'info') {
                        const alertClass = {
                            success: 'alert-success',
                            error: 'alert-danger',
                            warning: 'alert-warning',
                            info: 'alert-info'
                        }[type] || 'alert-info';

                        const alertDiv = document.createElement('div');
                        alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
                        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
                        alertDiv.innerHTML = `
                                                                    ${message}
                                                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                                                `;

                        document.body.appendChild(alertDiv);

                        setTimeout(() => {
                            alertDiv.remove();
                        }, 5000);
                    }

                    /**
                     * Scroll vers le haut
                     */
                    function scrollToTop() {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }

                    // ========================================
                    // GESTION DES TYPES D'ORGANISATION
                    // ========================================

                    /**
                     * Initialiser les cartes de type d'organisation
                     */
                    function initOrganizationTypeCards() {
                        const typeCards = document.querySelectorAll('.organization-type-card');
                        const selectedInfo = document.getElementById('selectedTypeInfo');
                        const selectedTypeName = document.getElementById('selectedTypeName');

                        typeCards.forEach(card => {
                            card.addEventListener('click', function () {
                                const radio = this.querySelector('input[type="radio"]');
                                if (radio) {
                                    radio.checked = true;

                                    // Mettre à jour l'affichage
                                    typeCards.forEach(c => c.classList.remove('selected'));
                                    this.classList.add('selected');

                                    // Afficher les informations de sélection
                                    if (selectedInfo && selectedTypeName) {
                                        const typeLabel = this.querySelector('.card-title').textContent;
                                        selectedTypeName.textContent = typeLabel;
                                        selectedInfo.classList.remove('d-none');
                                    }

                                    // Mettre à jour le type dans l'application
                                    OrganisationApp.organisationType = radio.value;

                                    // Sauvegarde auto du brouillon dès la sélection du type
                                    saveStepToServer(1);

                                    // Afficher/masquer la déclaration exclusivité parti politique
                                    const partiDeclarationDiv = document.getElementById('declaration_parti_politique');
                                    if (partiDeclarationDiv) {
                                        if (radio.value === 'parti_politique') {
                                            partiDeclarationDiv.classList.remove('d-none');
                                        } else {
                                            partiDeclarationDiv.classList.add('d-none');
                                        }
                                    }

                                    // Déclencher la validation
                                    validateStep1();
                                }
                            });
                        });
                    }

                    // ========================================
                    // GESTION DES FONDATEURS
                    // ========================================

                    /**
                     * Ajouter un fondateur
                     */
                    function addFondateur() {
                        const formData = {
                            civilite: document.getElementById('fondateur_civilite')?.value || '',
                            nom: document.getElementById('fondateur_nom')?.value?.trim() || '',
                            prenom: document.getElementById('fondateur_prenom')?.value?.trim() || '',
                            nip: document.getElementById('fondateur_nip')?.value?.trim() || '',
                            fonction: document.getElementById('fondateur_fonction')?.value || '',
                            telephone: document.getElementById('fondateur_telephone')?.value?.trim() || '',
                            email: document.getElementById('fondateur_email')?.value?.trim() || ''
                        };

                        // Validation détaillée avec messages clairs
                        const errors = [];
                        const nipRegex = /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/;

                        if (!formData.nom) {
                            errors.push('Nom du fondateur obligatoire');
                            document.getElementById('fondateur_nom')?.classList.add('is-invalid');
                        } else {
                            document.getElementById('fondateur_nom')?.classList.remove('is-invalid');
                        }

                        if (!formData.prenom) {
                            errors.push('Prénom du fondateur obligatoire');
                            document.getElementById('fondateur_prenom')?.classList.add('is-invalid');
                        } else {
                            document.getElementById('fondateur_prenom')?.classList.remove('is-invalid');
                        }

                        if (!formData.nip) {
                            errors.push('NIP obligatoire');
                            document.getElementById('fondateur_nip')?.classList.add('is-invalid');
                        } else if (!nipRegex.test(formData.nip)) {
                            errors.push('NIP : format invalide. Attendu : XX-0000-AAAAMMJJ (ex: A1-2345-19901225)');
                            document.getElementById('fondateur_nip')?.classList.add('is-invalid');
                        } else {
                            document.getElementById('fondateur_nip')?.classList.remove('is-invalid');
                        }

                        if (!formData.fonction) {
                            errors.push('Fonction obligatoire');
                            document.getElementById('fondateur_fonction')?.classList.add('is-invalid');
                        } else {
                            document.getElementById('fondateur_fonction')?.classList.remove('is-invalid');
                        }

                        if (!formData.telephone) {
                            errors.push('Téléphone obligatoire');
                            document.getElementById('fondateur_telephone')?.classList.add('is-invalid');
                        } else {
                            document.getElementById('fondateur_telephone')?.classList.remove('is-invalid');
                        }

                        if (errors.length > 0) {
                            showNotification('Veuillez corriger :<br>• ' + errors.join('<br>• '), 'warning');
                            return;
                        }

                        // Vérifier les doublons
                        if (OrganisationApp.foundateurs.some(f => f.nip === formData.nip)) {
                            showNotification('Ce NIP existe déjà dans la liste des fondateurs', 'warning');
                            document.getElementById('fondateur_nip')?.classList.add('is-invalid');
                            return;
                        }

                        // Ajouter le fondateur
                        const fondateur = {
                            id: Date.now(),
                            ...formData,
                            dateAjout: new Date().toISOString()
                        };

                        OrganisationApp.foundateurs.push(fondateur);
                        updateFoundersList();
                        clearFoundersForm();
                        saveToCache();

                        const typeData = window.typesOrganisationData ? window.typesOrganisationData[OrganisationApp.organisationType] : null;
                        const minRequired = typeData ? typeData.nb_min_fondateurs_majeurs : 3;
                        const remaining = minRequired - OrganisationApp.foundateurs.length;

                        if (remaining > 0) {
                            showNotification(`Fondateur ${fondateur.prenom} ${fondateur.nom} ajouté. Encore ${remaining} fondateur(s) requis.`, 'success');
                        } else {
                            showNotification(`Fondateur ${fondateur.prenom} ${fondateur.nom} ajouté. Minimum atteint ✓`, 'success');
                        }
                    }

                    // ========================================
                    // GESTION DES MEMBRES DU BUREAU
                    // ========================================

                    /**
                     * Ajouter un membre du bureau
                     */
                    function addMembreBureau() {
                        const formData = {
                            nip: document.getElementById('membre_nip')?.value?.trim() || '',
                            nom: document.getElementById('membre_nom')?.value?.trim() || '',
                            prenom: document.getElementById('membre_prenom')?.value?.trim() || '',
                            fonction: document.getElementById('membre_fonction')?.value || '',
                            contact: document.getElementById('membre_contact')?.value?.trim() || '',
                            domicile: document.getElementById('membre_domicile')?.value?.trim() || '',
                            afficher_recepisse: document.getElementById('membre_afficher_recepisse')?.checked || false
                        };

                        // Validation détaillée
                        const errors = [];
                        const nipRegex = /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/;

                        if (!formData.nip) {
                            errors.push('NIP obligatoire');
                            document.getElementById('membre_nip')?.classList.add('is-invalid');
                        } else if (!nipRegex.test(formData.nip)) {
                            errors.push('NIP : format attendu XX-0000-AAAAMMJJ');
                            document.getElementById('membre_nip')?.classList.add('is-invalid');
                        } else {
                            document.getElementById('membre_nip')?.classList.remove('is-invalid');
                        }

                        if (!formData.nom) {
                            errors.push('Nom obligatoire');
                            document.getElementById('membre_nom')?.classList.add('is-invalid');
                        } else {
                            document.getElementById('membre_nom')?.classList.remove('is-invalid');
                        }

                        if (!formData.prenom) {
                            errors.push('Prénom obligatoire');
                            document.getElementById('membre_prenom')?.classList.add('is-invalid');
                        } else {
                            document.getElementById('membre_prenom')?.classList.remove('is-invalid');
                        }

                        if (!formData.fonction) {
                            errors.push('Fonction obligatoire');
                            document.getElementById('membre_fonction')?.classList.add('is-invalid');
                        } else {
                            document.getElementById('membre_fonction')?.classList.remove('is-invalid');
                        }

                        if (errors.length > 0) {
                            showNotification('Membre bureau — champs à corriger :<br>• ' + errors.join('<br>• '), 'warning');
                            return;
                        }

                        // Vérifier les doublons
                        if (OrganisationApp.membresBureau.some(m => m.nip === formData.nip)) {
                            showNotification('Ce NIP existe déjà dans la liste des membres du bureau', 'warning');
                            document.getElementById('membre_nip')?.classList.add('is-invalid');
                            return;
                        }

                        // Vérifier le maximum de 3 membres pour le récépissé
                        if (formData.afficher_recepisse) {
                            const countRecepisse = OrganisationApp.membresBureau.filter(m => m.afficher_recepisse).length;
                            if (countRecepisse >= 3) {
                                showNotification('Maximum 3 membres peuvent être affichés sur le récépissé. Décochez l\'option pour ce membre ou retirez un autre membre du récépissé.', 'warning');
                                return;
                            }
                        }

                        // Ajouter le membre
                        const membre = {
                            id: Date.now(),
                            ...formData,
                            ordre: OrganisationApp.membresBureau.length,
                            dateAjout: new Date().toISOString()
                        };

                        OrganisationApp.membresBureau.push(membre);
                        updateMembresBureauList();
                        clearMembresBureauForm();
                        saveToCache();

                        showNotification(`Membre ${membre.prenom} ${membre.nom} ajouté au bureau`, 'success');
                    }

                    /**
                     * Mettre à jour la liste des membres du bureau
                     */
                    function updateMembresBureauList() {
                        const listContainer = document.getElementById('membres_bureau_list');
                        const countElement = document.getElementById('membres_bureau_count');
                        const recepisseCountElement = document.getElementById('membres_recepisse_count');

                        const totalCount = OrganisationApp.membresBureau.length;
                        const recepisseCount = OrganisationApp.membresBureau.filter(m => m.afficher_recepisse).length;

                        if (countElement) {
                            countElement.textContent = `${totalCount} membre(s)`;
                        }
                        if (recepisseCountElement) {
                            recepisseCountElement.textContent = `${recepisseCount}/3 sur récépissé`;
                            recepisseCountElement.className = recepisseCount >= 3 ? 'badge bg-warning' : 'badge bg-success';
                        }

                        if (!listContainer) return;

                        if (totalCount === 0) {
                            listContainer.innerHTML = `
                                                                        <div class="text-center py-4 text-muted">
                                                                            <i class="fas fa-user-tie fa-3x mb-3"></i>
                                                                            <p>Aucun membre du bureau ajouté</p>
                                                                        </div>
                                                                    `;
                            return;
                        }

                        let html = '<div class="table-responsive"><table class="table table-hover">';
                        html += `
                                                                    <thead class="table-primary">
                                                                        <tr>
                                                                            <th>NIP</th>
                                                                            <th>Nom</th>
                                                                            <th>Prénom</th>
                                                                            <th>Fonction</th>
                                                                            <th>Contact</th>
                                                                            <th>Sur récépissé</th>
                                                                            <th>Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                `;

                        OrganisationApp.membresBureau.forEach((membre, index) => {
                            html += `
                                                                        <tr>
                                                                            <td><code>${membre.nip}</code></td>
                                                                            <td><strong>${membre.nom}</strong></td>
                                                                            <td>${membre.prenom}</td>
                                                                            <td><span class="badge bg-primary">${membre.fonction}</span></td>
                                                                            <td>${membre.contact || '-'}</td>
                                                                            <td>
                                                                                ${membre.afficher_recepisse
                                    ? '<span class="badge bg-success"><i class="fas fa-check"></i> Oui</span>'
                                    : '<span class="badge bg-secondary">Non</span>'}
                                                                            </td>
                                                                            <td>
                                                                                <button class="btn btn-outline-danger btn-sm" onclick="removeMembreBureau(${index})">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    `;
                        });

                        html += '</tbody></table></div>';
                        listContainer.innerHTML = html;
                    }

                    /**
                     * Supprimer un membre du bureau
                     */
                    function removeMembreBureau(index) {
                        if (confirm('Supprimer ce membre du bureau ?')) {
                            const membre = OrganisationApp.membresBureau[index];
                            OrganisationApp.membresBureau.splice(index, 1);
                            updateMembresBureauList();
                            showNotification(`Membre ${membre.prenom} ${membre.nom} supprimé`, 'info');
                        }
                    }

                    /**
                     * Vider le formulaire des membres du bureau
                     */
                    function clearMembresBureauForm() {
                        document.getElementById('membre_nip').value = '';
                        document.getElementById('membre_nom').value = '';
                        document.getElementById('membre_prenom').value = '';
                        document.getElementById('membre_fonction').value = '';
                        document.getElementById('membre_contact').value = '';
                        document.getElementById('membre_domicile').value = '';
                        document.getElementById('membre_afficher_recepisse').checked = false;
                    }

                    /**
                     * Mettre à jour la liste des fondateurs
                     */
                    function updateFoundersList() {
                        const listContainer = document.getElementById('fondateurs_list');
                        const countElement = document.getElementById('fondateurs_count');

                        if (countElement) {
                            countElement.textContent = `${OrganisationApp.foundateurs.length} fondateur(s)`;
                        }

                        if (!listContainer) return;

                        if (OrganisationApp.foundateurs.length === 0) {
                            listContainer.innerHTML = `
                                                                        <div class="text-center py-4 text-muted">
                                                                            <i class="fas fa-users fa-3x mb-3"></i>
                                                                            <p>Aucun fondateur ajouté</p>
                                                                        </div>
                                                                    `;
                            return;
                        }

                        let html = '<div class="table-responsive"><table class="table table-hover">';
                        html += `
                                                                    <thead class="table-dark">
                                                                        <tr>
                                                                            <th>Civilité</th>
                                                                            <th>Nom</th>
                                                                            <th>Prénom</th>
                                                                            <th>NIP</th>
                                                                            <th>Fonction</th>
                                                                            <th>Téléphone</th>
                                                                            <th>Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                `;

                        OrganisationApp.foundateurs.forEach((fondateur, index) => {
                            html += `
                                                                        <tr>
                                                                            <td>${fondateur.civilite}</td>
                                                                            <td><strong>${fondateur.nom}</strong></td>
                                                                            <td>${fondateur.prenom}</td>
                                                                            <td><code>${fondateur.nip}</code></td>
                                                                            <td>${fondateur.fonction || '-'}</td>
                                                                            <td>${fondateur.telephone || '-'}</td>
                                                                            <td>
                                                                                <button class="btn btn-outline-danger btn-sm" onclick="removeFondateur(${index})">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    `;
                        });

                        html += '</tbody></table></div>';
                        listContainer.innerHTML = html;
                    }

                    /**
                     * Supprimer un fondateur (avec sauvegarde automatique)
                     */
                    function removeFondateur(index) {
                        if (confirm('Supprimer ce fondateur ?')) {
                            const fondateur = OrganisationApp.foundateurs[index];
                            OrganisationApp.foundateurs.splice(index, 1);
                            updateFoundersList();

                            // Sauvegarder automatiquement
                            saveToCache();

                            showNotification(`Fondateur ${fondateur.prenom} ${fondateur.nom} supprimé`, 'success');
                        }
                    }

                    /**
                     * Vider le formulaire des fondateurs
                     */
                    function clearFoundersForm() {
                        const inputs = ['fondateur_civilite', 'fondateur_nom', 'fondateur_prenom',
                            'fondateur_nip', 'fondateur_fonction', 'fondateur_telephone', 'fondateur_email'];

                        inputs.forEach(inputId => {
                            const input = document.getElementById(inputId);
                            if (input) {
                                if (input.tagName === 'SELECT') {
                                    input.selectedIndex = 0;
                                } else {
                                    input.value = '';
                                }
                            }
                        });
                    }

                    // ========================================
                    // INITIALISATION
                    // ========================================

                    /**
                     * Initialisation de l'application
                     */
                    function initApplication() {
                        console.log('🚀 Initialisation OrganisationApp v2.0 (8 étapes)');

                        // Initialiser l'affichage
                        updateStepDisplay();
                        updateNavigationButtons();

                        // Initialiser les cartes de type
                        initOrganizationTypeCards();

                        // Événements des boutons
                        const addFondateurBtn = document.getElementById('addFondateurBtn');
                        if (addFondateurBtn) {
                            addFondateurBtn.addEventListener('click', addFondateur);
                        }

                        // Événementpour ajouter un membre du bureau
                        const addMembreBureauBtn = document.getElementById('addMembreBureauBtn');
                        if (addMembreBureauBtn) {
                            addMembreBureauBtn.addEventListener('click', addMembreBureau);
                        }

                        // Événements des provinces/départements
                        const provinceSelect = document.getElementById('org_province');
                        if (provinceSelect) {
                            provinceSelect.addEventListener('change', updateDepartements);
                        }

                        // Initialiser la gestion zone urbaine/rurale
                        initZoneToggle();

                        console.log('✅ Application initialisée avec succès');
                    }

                    /**
                     * Mettre à jour les départements selon la province
                     */
                    /**
                     * Fonction utilitaire pour charger un select via AJAX
                     */
                    function loadSelectOptions(url, selectElement, placeholder, callback, preSelectValue) {
                        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
                        selectElement.disabled = true;

                        return fetch(url, {
                            headers: { 'Accept': 'application/json' }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.data.length > 0) {
                                    data.data.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.id;
                                        option.textContent = item.nom;
                                        option.dataset.nom = item.nom;
                                        if (item.code) option.dataset.code = item.code;
                                        selectElement.appendChild(option);
                                    });
                                    selectElement.disabled = false;

                                    // Pré-sélectionner une valeur si demandée (restauration cache)
                                    if (preSelectValue) {
                                        selectElement.value = preSelectValue;
                                    }
                                } else {
                                    selectElement.innerHTML = `<option value="">Aucun résultat</option>`;
                                }
                                if (callback) callback(data);
                                return data;
                            })
                            .catch(error => {
                                console.error('Erreur chargement:', error);
                                selectElement.innerHTML = `<option value="">Erreur de chargement</option>`;
                            });
                    }

                    /**
                     * Réinitialiser un select
                     */
                    function resetSelect(selectId, placeholder) {
                        const el = document.getElementById(selectId);
                        if (el) {
                            el.innerHTML = `<option value="">${placeholder}</option>`;
                            el.disabled = true;
                        }
                    }

                    /**
                     * Obtenir le type de zone sélectionné
                     */
                    function getZoneType() {
                        const input = document.getElementById('zone_type_input');
                        return input ? input.value : 'urbaine';
                    }

                    /**
                     * Appliquer l'état visuel et fonctionnel d'une zone
                     */
                    function applyZoneState(zoneValue) {
                        const isUrbaine = zoneValue === 'urbaine';
                        const cardUrbaine = document.getElementById('zoneCardUrbaine');
                        const cardRurale = document.getElementById('zoneCardRurale');
                        const hiddenInput = document.getElementById('zone_type_input');
                        const radioUrbaine = document.getElementById('radioUrbaine');
                        const radioRurale = document.getElementById('radioRurale');

                        // Mettre à jour la valeur cachée
                        if (hiddenInput) hiddenInput.value = zoneValue;

                        // Mettre à jour les boutons radio
                        if (radioUrbaine) radioUrbaine.checked = isUrbaine;
                        if (radioRurale) radioRurale.checked = !isUrbaine;

                        // Afficher/masquer les champs selon la zone
                        document.querySelectorAll('.zone-urbaine-field').forEach(function (el) {
                            el.style.display = isUrbaine ? '' : 'none';
                        });
                        document.querySelectorAll('.zone-rurale-field').forEach(function (el) {
                            el.style.display = isUrbaine ? 'none' : '';
                        });

                        // Style des cartes zone
                        if (cardUrbaine) {
                            cardUrbaine.style.borderColor = isUrbaine ? '#17a2b8' : '#dee2e6';
                            cardUrbaine.style.backgroundColor = isUrbaine ? '#17a2b810' : '';
                        }
                        if (cardRurale) {
                            cardRurale.style.borderColor = isUrbaine ? '#dee2e6' : '#28a745';
                            cardRurale.style.backgroundColor = isUrbaine ? '' : '#28a74510';
                        }
                    }

                    /**
                     * Initialiser le toggle zone urbaine / rurale
                     */
                    function initZoneToggle() {
                        document.querySelectorAll('.zone-type-btn').forEach(function (card) {
                            card.addEventListener('click', function () {
                                const zoneValue = this.dataset.zone;
                                const previousZone = getZoneType();

                                // Ne rien faire si c'est la même zone
                                if (zoneValue === previousZone) return;

                                // Appliquer l'état visuel
                                applyZoneState(zoneValue);

                                const isUrbaine = zoneValue === 'urbaine';

                                // Réinitialiser les selects de la zone opposée
                                if (isUrbaine) {
                                    resetSelect('org_canton_id', 'Sélectionnez un département...');
                                    resetSelect('org_regroupement_id', 'Sélectionnez un canton...');
                                    resetSelect('org_village_id', 'Sélectionnez un regroupement...');
                                    const cn = document.getElementById('canton_nom');
                                    const rn = document.getElementById('regroupement_nom');
                                    const vn = document.getElementById('village_nom');
                                    if (cn) cn.value = '';
                                    if (rn) rn.value = '';
                                    if (vn) vn.value = '';
                                } else {
                                    resetSelect('org_commune_id', 'Sélectionnez un département...');
                                    resetSelect('org_arrondissement_id', 'Sélectionnez une commune...');
                                    resetSelect('org_quartier_id', 'Sélectionnez un arrondissement...');
                                    const cmn = document.getElementById('commune_nom');
                                    const atn = document.getElementById('arrondissement_text');
                                    const qn = document.getElementById('quartier_nom');
                                    if (cmn) cmn.value = '';
                                    if (atn) atn.value = '';
                                    if (qn) qn.value = '';
                                }

                                // Recharger selon le département sélectionné
                                const deptSelect = document.getElementById('org_departement');
                                const deptId = deptSelect ? deptSelect.value : '';
                                if (deptId) {
                                    if (isUrbaine) {
                                        loadCommunes(deptId);
                                    } else {
                                        loadCantons(deptId);
                                    }
                                }

                                console.log('Zone changée:', zoneValue);
                            });
                        });

                        // État initial
                        applyZoneState(getZoneType());
                    }

                    /**
                     * Province → Département (réinitialise toutes les cascades)
                     */
                    function updateDepartements() {
                        const provinceSelect = document.getElementById('org_province');
                        const departementSelect = document.getElementById('org_departement');

                        if (!provinceSelect || !departementSelect) return;

                        // Stocker le nom de la province
                        const selectedOpt = provinceSelect.options[provinceSelect.selectedIndex];
                        document.getElementById('province_nom').value = selectedOpt ? (selectedOpt.dataset.nom || selectedOpt.text) : '';

                        const provinceId = provinceSelect.value;
                        if (!provinceId) {
                            resetSelect('org_departement', 'Sélectionnez d\'abord une province');
                            // Réinitialiser toutes les cascades
                            resetSelect('org_commune_id', 'Sélectionnez un département...');
                            resetSelect('org_arrondissement_id', 'Sélectionnez une commune...');
                            resetSelect('org_quartier_id', 'Sélectionnez un arrondissement...');
                            resetSelect('org_canton_id', 'Sélectionnez un département...');
                            resetSelect('org_regroupement_id', 'Sélectionnez un canton...');
                            resetSelect('org_village_id', 'Sélectionnez un regroupement...');
                            return;
                        }

                        loadSelectOptions(
                            `{{ url('operator/api/geo/departements') }}/${provinceId}`,
                            departementSelect,
                            'Sélectionnez un département',
                            function () {
                                // Réinitialiser les cascades en aval
                                resetSelect('org_commune_id', 'Sélectionnez un département...');
                                resetSelect('org_arrondissement_id', 'Sélectionnez une commune...');
                                resetSelect('org_quartier_id', 'Sélectionnez un arrondissement...');
                                resetSelect('org_canton_id', 'Sélectionnez un département...');
                                resetSelect('org_regroupement_id', 'Sélectionnez un canton...');
                                resetSelect('org_village_id', 'Sélectionnez un regroupement...');
                            }
                        );
                    }

                    /**
                     * Département → Communes (Zone Urbaine) ou Cantons (Zone Rurale)
                     */
                    function onDepartementChange() {
                        const deptSelect = document.getElementById('org_departement');
                        if (!deptSelect) return;

                        const selectedOpt = deptSelect.options[deptSelect.selectedIndex];
                        document.getElementById('departement_nom').value = selectedOpt ? (selectedOpt.dataset.nom || selectedOpt.text) : '';

                        const deptId = deptSelect.value;
                        if (!deptId) return;

                        if (getZoneType() === 'urbaine') {
                            loadCommunes(deptId);
                        } else {
                            loadCantons(deptId);
                        }
                    }

                    // ===== ZONE URBAINE : Commune → Arrondissement → Quartier =====

                    function loadCommunes(departementId) {
                        resetSelect('org_arrondissement_id', 'Sélectionnez une commune...');
                        resetSelect('org_quartier_id', 'Sélectionnez un arrondissement...');
                        document.getElementById('commune_nom').value = '';
                        document.getElementById('arrondissement_text').value = '';
                        document.getElementById('quartier_nom').value = '';

                        loadSelectOptions(
                            `{{ url('operator/api/geo/communes') }}/${departementId}`,
                            document.getElementById('org_commune_id'),
                            'Sélectionnez une commune'
                        );
                    }

                    function onCommuneChange() {
                        const sel = document.getElementById('org_commune_id');
                        const selectedOpt = sel.options[sel.selectedIndex];
                        document.getElementById('commune_nom').value = selectedOpt ? (selectedOpt.dataset.nom || selectedOpt.text) : '';

                        resetSelect('org_arrondissement_id', 'Chargement...');
                        resetSelect('org_quartier_id', 'Sélectionnez un arrondissement...');
                        document.getElementById('arrondissement_text').value = '';
                        document.getElementById('quartier_nom').value = '';

                        if (sel.value) {
                            loadSelectOptions(
                                `{{ url('operator/api/geo/arrondissements') }}/${sel.value}`,
                                document.getElementById('org_arrondissement_id'),
                                'Sélectionnez un arrondissement'
                            );
                        }
                    }

                    function onArrondissementChange() {
                        const sel = document.getElementById('org_arrondissement_id');
                        const selectedOpt = sel.options[sel.selectedIndex];
                        document.getElementById('arrondissement_text').value = selectedOpt ? (selectedOpt.dataset.nom || selectedOpt.text) : '';

                        resetSelect('org_quartier_id', 'Chargement...');
                        document.getElementById('quartier_nom').value = '';

                        if (sel.value) {
                            loadSelectOptions(
                                `{{ url('operator/api/geo/quartiers') }}/${sel.value}`,
                                document.getElementById('org_quartier_id'),
                                'Sélectionnez un quartier'
                            );
                        }
                    }

                    function onQuartierChange() {
                        const sel = document.getElementById('org_quartier_id');
                        const selectedOpt = sel.options[sel.selectedIndex];
                        document.getElementById('quartier_nom').value = selectedOpt ? (selectedOpt.dataset.nom || selectedOpt.text) : '';
                    }

                    // ===== ZONE RURALE : Canton → Regroupement → Village =====

                    function loadCantons(departementId) {
                        resetSelect('org_regroupement_id', 'Sélectionnez un canton...');
                        resetSelect('org_village_id', 'Sélectionnez un regroupement...');
                        document.getElementById('canton_nom').value = '';
                        document.getElementById('regroupement_nom').value = '';
                        document.getElementById('village_nom').value = '';

                        loadSelectOptions(
                            `{{ url('operator/api/geo/cantons') }}/${departementId}`,
                            document.getElementById('org_canton_id'),
                            'Sélectionnez un canton'
                        );
                    }

                    function onCantonChange() {
                        const sel = document.getElementById('org_canton_id');
                        const selectedOpt = sel.options[sel.selectedIndex];
                        document.getElementById('canton_nom').value = selectedOpt ? (selectedOpt.dataset.nom || selectedOpt.text) : '';

                        resetSelect('org_regroupement_id', 'Chargement...');
                        resetSelect('org_village_id', 'Sélectionnez un regroupement...');
                        document.getElementById('regroupement_nom').value = '';
                        document.getElementById('village_nom').value = '';

                        if (sel.value) {
                            loadSelectOptions(
                                `{{ url('operator/api/geo/regroupements') }}/${sel.value}`,
                                document.getElementById('org_regroupement_id'),
                                'Sélectionnez un regroupement'
                            );
                        }
                    }

                    function onRegroupementChange() {
                        const sel = document.getElementById('org_regroupement_id');
                        const selectedOpt = sel.options[sel.selectedIndex];
                        document.getElementById('regroupement_nom').value = selectedOpt ? (selectedOpt.dataset.nom || selectedOpt.text) : '';

                        resetSelect('org_village_id', 'Chargement...');
                        document.getElementById('village_nom').value = '';

                        if (sel.value) {
                            loadSelectOptions(
                                `{{ url('operator/api/geo/villages') }}/${sel.value}`,
                                document.getElementById('org_village_id'),
                                'Sélectionnez un village'
                            );
                        }
                    }

                    function onVillageChange() {
                        const sel = document.getElementById('org_village_id');
                        const selectedOpt = sel.options[sel.selectedIndex];
                        document.getElementById('village_nom').value = selectedOpt ? (selectedOpt.dataset.nom || selectedOpt.text) : '';
                    }

                    // Attacher les événements cascades
                    (function initCascadeEvents() {
                        const dept = document.getElementById('org_departement');
                        if (dept) dept.addEventListener('change', onDepartementChange);

                        const commune = document.getElementById('org_commune_id');
                        if (commune) commune.addEventListener('change', onCommuneChange);

                        const arrond = document.getElementById('org_arrondissement_id');
                        if (arrond) arrond.addEventListener('change', onArrondissementChange);

                        const quartier = document.getElementById('org_quartier_id');
                        if (quartier) quartier.addEventListener('change', onQuartierChange);

                        const canton = document.getElementById('org_canton_id');
                        if (canton) canton.addEventListener('change', onCantonChange);

                        const regroup = document.getElementById('org_regroupement_id');
                        if (regroup) regroup.addEventListener('change', onRegroupementChange);

                        const village = document.getElementById('org_village_id');
                        if (village) village.addEventListener('change', onVillageChange);
                    })();

                    /**
                     * Obtenir la géolocalisation
                     */
                    function getGeolocation() {
                        const btn = document.getElementById('getLocationBtn');
                        const latInput = document.getElementById('org_latitude');
                        const lngInput = document.getElementById('org_longitude');

                        if (!navigator.geolocation) {
                            showNotification('La géolocalisation n\'est pas supportée par ce navigateur', 'warning');
                            return;
                        }

                        // Disable button and show loading
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Localisation...';
                        }

                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;

                                // Vérifier si on est au Gabon (approximativement)
                                if (lat >= -3.978 && lat <= 2.318 && lng >= 8.695 && lng <= 14.502) {
                                    if (latInput) latInput.value = lat.toFixed(6);
                                    if (lngInput) lngInput.value = lng.toFixed(6);

                                    showNotification('Position obtenue avec succès', 'success');
                                } else {
                                    showNotification('Position détectée hors du Gabon. Veuillez vérifier.', 'warning');
                                    if (latInput) latInput.value = lat.toFixed(6);
                                    if (lngInput) lngInput.value = lng.toFixed(6);
                                }
                            },
                            function (error) {
                                let message = 'Erreur de géolocalisation: ';
                                switch (error.code) {
                                    case error.PERMISSION_DENIED:
                                        message += 'Permission refusée';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        message += 'Position indisponible';
                                        break;
                                    case error.TIMEOUT:
                                        message += 'Délai dépassé';
                                        break;
                                    default:
                                        message += 'Erreur inconnue';
                                        break;
                                }
                                showNotification(message, 'error');
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );

                        // Restore button state
                        if (btn) {
                            setTimeout(() => {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="fas fa-map-marker-alt me-2"></i>Obtenir ma position actuelle';
                            }, 3000);
                        }
                    }

                    /**
                     * Validation avancée des champs
                     */
                    function validateFieldsAdvanced() {
                        // Validation email
                        const emailInputs = document.querySelectorAll('input[type="email"]');
                        emailInputs.forEach(input => {
                            input.addEventListener('blur', function () {
                                if (this.value && !isValidEmail(this.value)) {
                                    this.classList.add('is-invalid');
                                    showFieldError(this, 'Format email invalide');
                                } else {
                                    this.classList.remove('is-invalid');
                                    clearFieldError(this);
                                }
                            });
                        });

                        // Validation téléphone
                        const phoneInputs = document.querySelectorAll('input[type="text"]');
                        phoneInputs.forEach(input => {
                            input.addEventListener('blur', function () {
                                if (this.value && !isValidGabonPhone(this.value)) {
                                    this.classList.add('is-invalid');
                                    showFieldError(this, 'Numéro de téléphone gabonais invalide');
                                } else {
                                    this.classList.remove('is-invalid');
                                    clearFieldError(this);
                                }
                            });
                        });

                        // Validation dates
                        const dateInputs = document.querySelectorAll('input[type="date"]');
                        dateInputs.forEach(input => {
                            input.addEventListener('change', function () {
                                if (this.id === 'demandeur_date_naissance') {
                                    const age = calculateAge(new Date(this.value));
                                    if (age < 18) {
                                        this.classList.add('is-invalid');
                                        showFieldError(this, 'Le demandeur doit être majeur (18 ans minimum)');
                                    } else {
                                        this.classList.remove('is-invalid');
                                        clearFieldError(this);
                                    }
                                }
                            });
                        });
                    }

                    /**
                     * Valider email
                     */
                    function isValidEmail(email) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        return emailRegex.test(email);
                    }

                    /**
                     * Valider téléphone gabonais
                     */
                    function isValidGabonPhone(phone) {
                        // Format gabonais : 8-9 chiffres
                        const phoneRegex = /^[0-9]{8,9}$/;
                        return phoneRegex.test(phone.replace(/\s/g, ''));
                    }

                    /**
                     * Calculer l'âge
                     */
                    function calculateAge(birthDate) {
                        const today = new Date();
                        let age = today.getFullYear() - birthDate.getFullYear();
                        const monthDiff = today.getMonth() - birthDate.getMonth();

                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }

                        return age;
                    }

                    /**
                     * Afficher erreur de champ
                     */
                    function showFieldError(field, message) {
                        if (!field) return;

                        // Supprimer l'ancienne erreur
                        clearFieldError(field);

                        // Créer le message d'erreur
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = message;

                        // Ajouter après le champ
                        field.parentNode.insertBefore(errorDiv, field.nextSibling);
                    }

                    /**
                     * Supprimer erreur de champ
                     */
                    function clearFieldError(field) {
                        if (!field) return;

                        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
                        if (errorDiv) {
                            errorDiv.remove();
                        }
                    }

                    // ========================================
                    // SYSTÈME DE CACHE/SAUVEGARDE AUTOMATIQUE
                    // ========================================

                    /**
                     * Sauvegarder les données dans le cache
                     */
                    function saveToCache() {
                        if (!OrganisationApp.cacheConfig.enabled) return;

                        try {
                            const cacheData = {
                                currentStep: OrganisationApp.currentStep,
                                organisationType: OrganisationApp.organisationType,
                                formData: OrganisationApp.formData,
                                foundateurs: OrganisationApp.foundateurs,
                                membresBureau: OrganisationApp.membresBureau,
                                uploadedDocuments: OrganisationApp.uploadedDocuments,
                                timestamp: new Date().toISOString(),
                                expiresAt: new Date(Date.now() + OrganisationApp.cacheConfig.expirationHours * 60 * 60 * 1000).toISOString()
                            };

                            // Collecter les données des formulaires
                            collectAllFormData();
                            cacheData.formData = OrganisationApp.formData;

                            const cacheString = JSON.stringify(cacheData);

                            // Vérifier la taille du cache
                            if (cacheString.length > OrganisationApp.cacheConfig.maxCacheSize) {
                                console.warn('Cache trop volumineux, sauvegarde partielle');
                                // Sauvegarder seulement les données essentielles
                                const essentialData = {
                                    currentStep: cacheData.currentStep,
                                    organisationType: cacheData.organisationType,
                                    formData: filterEssentialFormData(cacheData.formData),
                                    foundateurs: cacheData.foundateurs,
                                    membresBureau: cacheData.membresBureau,
                                    timestamp: cacheData.timestamp,
                                    expiresAt: cacheData.expiresAt
                                };
                                localStorage.setItem(OrganisationApp.cacheConfig.keyPrefix + 'data', JSON.stringify(essentialData));
                            } else {
                                localStorage.setItem(OrganisationApp.cacheConfig.keyPrefix + 'data', cacheString);
                            }

                            // Mettre à jour l'indicateur de sauvegarde
                            updateSaveIndicator('saved');

                            console.log('💾 Données sauvegardées dans le cache', new Date().toLocaleTimeString());

                        } catch (error) {
                            console.error('❌ Erreur sauvegarde cache:', error);
                            if (error.name === 'QuotaExceededError') {
                                showNotification('Espace de stockage insuffisant. Données partiellement sauvegardées.', 'warning');
                                // Essayer de nettoyer l'ancien cache
                                clearExpiredCache();
                            }
                        }
                    }

                    /**
                     * Charger les données depuis le cache
                     */
                    function loadFromCache() {
                        if (!OrganisationApp.cacheConfig.enabled) return false;

                        try {
                            const cacheString = localStorage.getItem(OrganisationApp.cacheConfig.keyPrefix + 'data');
                            if (!cacheString) return false;

                            const cacheData = JSON.parse(cacheString);

                            // Vérifier l'expiration
                            if (new Date(cacheData.expiresAt) < new Date()) {
                                console.log('🗑️ Cache expiré, suppression');
                                clearCache();
                                return false;
                            }

                            // Confirmer avec l'utilisateur
                            if (confirm('Des données non sauvegardées ont été trouvées. Voulez-vous les restaurer ?')) {
                                // Restaurer les données
                                OrganisationApp.currentStep = cacheData.currentStep || 1;
                                OrganisationApp.organisationType = cacheData.organisationType || null;
                                OrganisationApp.formData = cacheData.formData || {};
                                OrganisationApp.foundateurs = cacheData.foundateurs || [];
                                OrganisationApp.membresBureau = cacheData.membresBureau || [];
                                OrganisationApp.uploadedDocuments = cacheData.uploadedDocuments || {};

                                // Restaurer les champs du formulaire
                                restoreFormFields();

                                // Mettre à jour l'affichage
                                updateStepDisplay();
                                updateNavigationButtons();
                                updateFoundersList();
                                updateMembresBureauList(); // ✅ FIX: Restaurer aussi la liste des membres du bureau

                                // Restaurer le type d'organisation si défini
                                if (OrganisationApp.organisationType) {
                                    const typeRadio = document.querySelector(`input[name="type_organisation"][value="${OrganisationApp.organisationType}"]`);
                                    if (typeRadio) {
                                        typeRadio.checked = true;
                                        typeRadio.dispatchEvent(new Event('change'));
                                    }
                                }

                                // ✅ FIX: Restaurer les selects de géolocalisation en cascade
                                restoreGeolocationCascades();

                                showNotification('Données restaurées avec succès', 'success');
                                updateSaveIndicator('restored');

                                console.log('📥 Données restaurées depuis le cache', cacheData.timestamp);
                                return true;
                            } else {
                                clearCache();
                                return false;
                            }

                        } catch (error) {
                            console.error('❌ Erreur chargement cache:', error);
                            clearCache(); // Nettoyer en cas d'erreur
                            return false;
                        }
                    }

                    /**
                     * Restaurer les selects géolocalisation en cascade après chargement du cache.
                     * Charge chaque niveau en séquence : province → département → commune/canton → etc.
                     */
                    async function restoreGeolocationCascades() {
                        const fd = OrganisationApp.formData;

                        // 1. Province déjà remplie côté serveur (options HTML), on vérifie juste la valeur
                        const provinceSelect = document.getElementById('org_province');
                        const savedProvince = fd['org_province_id'] || fd['org_province'];
                        if (provinceSelect && savedProvince) {
                            provinceSelect.value = savedProvince;
                        }

                        // 2. Charger les départements à partir de la province
                        const provinceId = provinceSelect ? provinceSelect.value : null;
                        if (!provinceId) return;

                        const deptSelect = document.getElementById('org_departement');
                        const savedDept = fd['org_departement_id'] || fd['org_departement'];

                        try {
                            await loadSelectOptions(
                                `{{ url('operator/api/geo/departements') }}/${provinceId}`,
                                deptSelect,
                                'Sélectionnez un département',
                                null,
                                savedDept
                            );

                            if (!savedDept || !deptSelect.value) return;

                            const zoneType = fd['zone_type'] || getZoneType();

                            if (zoneType === 'urbaine') {
                                // 3a. Commune
                                const savedCommune = fd['org_commune_id'];
                                if (savedCommune) {
                                    const communeSelect = document.getElementById('org_commune_id');
                                    await loadSelectOptions(
                                        `{{ url('operator/api/geo/communes') }}/${deptSelect.value}`,
                                        communeSelect,
                                        'Sélectionnez une commune',
                                        null,
                                        savedCommune
                                    );

                                    // 4a. Arrondissement
                                    const savedArrond = fd['org_arrondissement_id'];
                                    if (savedArrond && communeSelect.value) {
                                        const arrondSelect = document.getElementById('org_arrondissement_id');
                                        await loadSelectOptions(
                                            `{{ url('operator/api/geo/arrondissements') }}/${communeSelect.value}`,
                                            arrondSelect,
                                            'Sélectionnez un arrondissement',
                                            null,
                                            savedArrond
                                        );

                                        // 5a. Quartier
                                        const savedQuartier = fd['org_quartier_id'];
                                        if (savedQuartier && arrondSelect.value) {
                                            await loadSelectOptions(
                                                `{{ url('operator/api/geo/quartiers') }}/${arrondSelect.value}`,
                                                document.getElementById('org_quartier_id'),
                                                'Sélectionnez un quartier',
                                                null,
                                                savedQuartier
                                            );
                                        }
                                    }
                                }
                            } else {
                                // 3b. Canton
                                const savedCanton = fd['org_canton_id'];
                                if (savedCanton) {
                                    const cantonSelect = document.getElementById('org_canton_id');
                                    await loadSelectOptions(
                                        `{{ url('operator/api/geo/cantons') }}/${deptSelect.value}`,
                                        cantonSelect,
                                        'Sélectionnez un canton',
                                        null,
                                        savedCanton
                                    );

                                    // 4b. Regroupement
                                    const savedRegroup = fd['org_regroupement_id'];
                                    if (savedRegroup && cantonSelect.value) {
                                        const regroupSelect = document.getElementById('org_regroupement_id');
                                        await loadSelectOptions(
                                            `{{ url('operator/api/geo/regroupements') }}/${cantonSelect.value}`,
                                            regroupSelect,
                                            'Sélectionnez un regroupement',
                                            null,
                                            savedRegroup
                                        );

                                        // 5b. Village
                                        const savedVillage = fd['org_village_id'];
                                        if (savedVillage && regroupSelect.value) {
                                            await loadSelectOptions(
                                                `{{ url('operator/api/geo/villages') }}/${regroupSelect.value}`,
                                                document.getElementById('org_village_id'),
                                                'Sélectionnez un village',
                                                null,
                                                savedVillage
                                            );
                                        }
                                    }
                                }
                            }

                            console.log('✅ Cascades géolocalisation restaurées');
                        } catch (err) {
                            console.error('❌ Erreur restauration géolocalisation:', err);
                        }
                    }

                    /**
                     * Collecter toutes les données du formulaire
                     */
                    function collectAllFormData() {
                        const form = document.getElementById('organisationForm');
                        if (!form) return;

                        // Collecter tous les inputs, selects et textareas
                        const elements = form.querySelectorAll('input, select, textarea');

                        elements.forEach(element => {
                            const name = element.name || element.id;
                            if (!name) return;

                            if (element.type === 'radio') {
                                if (element.checked) {
                                    OrganisationApp.formData[name] = element.value;
                                }
                            } else if (element.type === 'checkbox') {
                                OrganisationApp.formData[name] = element.checked;
                            } else if (element.type === 'file') {
                                // Pour les fichiers, on ne peut pas les sauvegarder en localStorage
                                // On sauvegarde juste l'info qu'un fichier était sélectionné
                                if (element.files && element.files.length > 0) {
                                    OrganisationApp.formData[name] = {
                                        hasFile: true,
                                        fileName: element.files[0].name,
                                        fileSize: element.files[0].size
                                    };
                                }
                            } else {
                                OrganisationApp.formData[name] = element.value;
                            }
                        });
                    }

                    /**
                     * Restaurer les champs du formulaire
                     */
                    function restoreFormFields() {
                        const form = document.getElementById('organisationForm');
                        if (!form || !OrganisationApp.formData) return;

                        Object.keys(OrganisationApp.formData).forEach(name => {
                            const element = form.querySelector(`[name="${name}"], #${name}`);
                            if (!element) return;

                            const value = OrganisationApp.formData[name];

                            if (element.type === 'radio') {
                                if (element.value === value) {
                                    element.checked = true;
                                }
                            } else if (element.type === 'checkbox') {
                                element.checked = !!value;
                            } else if (element.type === 'file') {
                                // On ne peut pas restaurer les fichiers, mais on peut indiquer qu'il y en avait
                                if (value && value.hasFile) {
                                    const statusElement = document.getElementById(`status_${name.replace('documents[', '').replace(']', '')}`);
                                    if (statusElement) {
                                        statusElement.innerHTML = '<i class="fas fa-info me-1 text-info"></i>Fichier précédemment sélectionné (à re-sélectionner)';
                                    }
                                }
                            } else {
                                element.value = value || '';
                            }
                        });
                    }

                    /**
                     * Filtrer les données essentielles pour économiser l'espace
                     */
                    function filterEssentialFormData(formData) {
                        const essential = {};
                        const essentialFields = [
                            'type_organisation', 'demandeur_nom', 'demandeur_prenom', 'demandeur_nip',
                            'demandeur_email', 'demandeur_telephone', 'org_nom', 'org_objet',
                            'org_province', 'org_adresse_complete'
                        ];

                        essentialFields.forEach(field => {
                            if (formData[field]) {
                                essential[field] = formData[field];
                            }
                        });

                        return essential;
                    }

                    /**
                     * Nettoyer le cache
                     */
                    function clearCache() {
                        try {
                            localStorage.removeItem(OrganisationApp.cacheConfig.keyPrefix + 'data');
                            console.log('🗑️ Cache nettoyé');
                            updateSaveIndicator('cleared');
                        } catch (error) {
                            console.error('❌ Erreur nettoyage cache:', error);
                        }
                    }

                    /**
                     * Nettoyer les caches expirés
                     */
                    function clearExpiredCache() {
                        try {
                            // Nettoyer tous les caches de formulaire expirés
                            for (let i = 0; i < localStorage.length; i++) {
                                const key = localStorage.key(i);
                                if (key && key.startsWith(OrganisationApp.cacheConfig.keyPrefix)) {
                                    try {
                                        const data = JSON.parse(localStorage.getItem(key));
                                        if (data.expiresAt && new Date(data.expiresAt) < new Date()) {
                                            localStorage.removeItem(key);
                                            console.log('🗑️ Cache expiré supprimé:', key);
                                        }
                                    } catch (e) {
                                        // Supprimer les caches corrompus
                                        localStorage.removeItem(key);
                                    }
                                }
                            }
                        } catch (error) {
                            console.error('❌ Erreur nettoyage caches expirés:', error);
                        }
                    }

                    /**
                     * Mettre à jour l'indicateur de sauvegarde
                     */
                    function updateSaveIndicator(status) {
                        const indicator = document.getElementById('save-indicator');
                        if (!indicator) return;

                        const now = new Date().toLocaleTimeString();

                        switch (status) {
                            case 'saving':
                                indicator.innerHTML = '<i class="fas fa-spinner fa-spin text-info"></i> Sauvegarde...';
                                break;
                            case 'saved':
                                indicator.innerHTML = `<i class="fas fa-check-circle text-success"></i> Sauvegardé automatiquement à ${now}`;
                                break;
                            case 'restored':
                                indicator.innerHTML = `<i class="fas fa-history text-info"></i> Données restaurées à ${now}`;
                                break;
                            case 'error':
                                indicator.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i> Erreur de sauvegarde';
                                break;
                            case 'cleared':
                                indicator.innerHTML = '<i class="fas fa-trash text-muted"></i> Cache nettoyé';
                                break;
                            default:
                                indicator.innerHTML = '<i class="fas fa-circle text-muted"></i> En attente';
                        }
                    }

                    /**
                     * Auto-sauvegarde périodique
                     */
                    function setupAutoSave() {
                        // Sauvegarde automatique
                        setInterval(() => {
                            if (OrganisationApp.currentStep > 1) {
                                updateSaveIndicator('saving');
                                setTimeout(() => {
                                    saveToCache();
                                }, 100);
                            }
                        }, OrganisationApp.cacheConfig.autoSaveInterval);

                        // Sauvegarde lors de changements importants
                        document.addEventListener('input', debounce(() => {
                            if (OrganisationApp.currentStep > 1) {
                                saveToCache();
                            }
                        }, 1000));

                        // Sauvegarde avant fermeture de page
                        window.addEventListener('beforeunload', (e) => {
                            saveToCache();

                            // Avertir l'utilisateur s'il y a des données non sauvegardées définitivement
                            if (OrganisationApp.currentStep > 1 && !OrganisationApp.isSubmitting) {
                                e.preventDefault();
                                e.returnValue = 'Vos modifications seront sauvegardées automatiquement, mais le dossier ne sera pas soumis. Continuer ?';
                                return e.returnValue;
                            }
                        });
                    }

                    /**
                     * Fonction debounce pour limiter les appels
                     */
                    function debounce(func, wait) {
                        let timeout;
                        return function executedFunction(...args) {
                            const later = () => {
                                clearTimeout(timeout);
                                func(...args);
                            };
                            clearTimeout(timeout);
                            timeout = setTimeout(later, wait);
                        };
                    }

                    /**
                     * Sauvegarder manuellement (bouton)
                     */
                    function saveManually() {
                        updateSaveIndicator('saving');
                        collectAllFormData();
                        saveToCache();
                        showNotification('Données sauvegardées manuellement', 'success');
                    }

                    /**
                     * Nettoyer manuellement (bouton)
                     */
                    function clearCacheManually() {
                        if (confirm('Êtes-vous sûr de vouloir supprimer toutes les données sauvegardées ?')) {
                            clearCache();
                            showNotification('Cache nettoyé', 'info');
                        }
                    }

                    /**
                     * Vérification de connectivité
                     */
                    function checkConnectivity() {
                        if (!navigator.onLine) {
                            showNotification('Connexion internet indisponible. Vos données sont sauvegardées localement.', 'warning');
                        }
                    }

                    /**
                     * Charger un brouillon depuis le serveur et restaurer le formulaire
                     */
                    async function loadDraftFromServer(draftId) {
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                            const response = await fetch('/operator/organisations/draft/' + draftId, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                credentials: 'same-origin'
                            });

                            const result = await response.json();
                            if (!result.success || !result.draft) {
                                updateStepDisplay();
                                updateNavigationButtons();
                                return;
                            }

                            const draft = result.draft;
                            const formData = draft.form_data || {};

                            // Stocker le draftId
                            OrganisationApp.draftId = draft.id;

                            // Mapping backend step -> frontend step
                            const backendToFrontend = {};
                            Object.entries(OrganisationApp.frontendToBackendStep).forEach(function(entry) {
                                backendToFrontend[entry[1]] = parseInt(entry[0]);
                            });

                            // Restaurer le type d'organisation (step_1)
                            if (formData.step_1 && formData.step_1.data) {
                                var orgType = formData.step_1.data.type_organisation;
                                if (orgType) {
                                    OrganisationApp.organisationType = orgType;

                                    // Cocher le radio
                                    var typeRadio = document.querySelector('input[name="type_organisation"][value="' + orgType + '"]');
                                    if (typeRadio) {
                                        typeRadio.checked = true;

                                        // Mettre à jour le hidden field
                                        var hiddenType = document.getElementById('organizationType');
                                        if (hiddenType) hiddenType.value = orgType;

                                        // Highlight visuel de la carte sélectionnée
                                        document.querySelectorAll('.organization-type-card').forEach(function(c) {
                                            c.classList.remove('selected', 'border-primary', 'shadow-lg');
                                        });
                                        var card = typeRadio.closest('.organization-type-card');
                                        if (card) {
                                            card.classList.add('selected', 'border-primary', 'shadow-lg');
                                        }

                                        // Afficher l'info de sélection
                                        var selectedInfo = document.getElementById('selectedTypeInfo');
                                        var selectedTypeName = document.getElementById('selectedTypeName');
                                        if (selectedTypeName && card) {
                                            var titleEl = card.querySelector('.card-title');
                                            selectedTypeName.textContent = titleEl ? titleEl.textContent : orgType;
                                        }
                                        if (selectedInfo) selectedInfo.classList.remove('d-none');

                                        // Afficher/masquer la déclaration parti politique
                                        var partiDiv = document.getElementById('declaration_parti_politique');
                                        if (partiDiv) {
                                            partiDiv.classList.toggle('d-none', orgType !== 'parti_politique');
                                        }
                                    }
                                }
                            }

                            // Restaurer les données de chaque étape dans les champs du formulaire
                            Object.keys(formData).forEach(function(stepKey) {
                                var stepData = formData[stepKey] && formData[stepKey].data ? formData[stepKey].data : {};
                                Object.keys(stepData).forEach(function(field) {
                                    if (field === 'fondateurs' || field === 'membresBureau' || field === 'documents' || field === 'organization_type') return;
                                    var el = document.getElementById(field) || document.querySelector('[name="' + field + '"]');
                                    if (!el) return;
                                    if (el.type === 'checkbox') {
                                        el.checked = !!stepData[field];
                                    } else if (el.type === 'radio') {
                                        if (el.value === stepData[field]) el.checked = true;
                                    } else if (el.type !== 'file') {
                                        el.value = stepData[field] || '';
                                    }
                                });
                            });

                            // Restaurer les fondateurs (step_6)
                            if (formData.step_6 && formData.step_6.data && formData.step_6.data.fondateurs) {
                                OrganisationApp.foundateurs = formData.step_6.data.fondateurs;
                                if (typeof updateFoundersList === 'function') updateFoundersList();
                            }

                            // Restaurer les membres du bureau (step_6)
                            if (formData.step_6 && formData.step_6.data && formData.step_6.data.membresBureau) {
                                OrganisationApp.membresBureau = formData.step_6.data.membresBureau;
                                if (typeof updateMembresBureauList === 'function') updateMembresBureauList();
                            }

                            // Déterminer la première étape frontend non complétée
                            var frontendToBackend = OrganisationApp.frontendToBackendStep;
                            var targetFrontendStep = OrganisationApp.totalSteps; // par défaut dernière étape
                            for (var fe = 1; fe <= OrganisationApp.totalSteps; fe++) {
                                var be = frontendToBackend[fe];
                                var sk = 'step_' + be;
                                if (!formData[sk] || formData[sk].status !== 'completed') {
                                    targetFrontendStep = fe;
                                    break;
                                }
                            }

                            OrganisationApp.currentStep = targetFrontendStep;
                            updateStepDisplay();
                            updateNavigationButtons();
                            handleStepSpecificActions(targetFrontendStep);

                            showNotification('Brouillon restaure - Etape ' + targetFrontendStep + ' sur ' + OrganisationApp.totalSteps, 'success');
                            console.log('Brouillon ' + draftId + ' restaure. Etape frontend: ' + targetFrontendStep);

                        } catch (error) {
                            console.error('Erreur chargement brouillon:', error);
                            updateStepDisplay();
                            updateNavigationButtons();
                        }
                    }

                    /**
                     * Initialisation complète de l'application
                     */
                    async function initApplicationComplete() {
                        console.log('Initialisation OrganisationApp v2.0 (8 etapes)');

                        // Nettoyer les caches expirés au démarrage
                        clearExpiredCache();

                        // Vérifier si on reprend un brouillon serveur
                        var resumeDraftId = @json($resumeDraftId ?? null);
                        if (resumeDraftId) {
                            try {
                                await loadDraftFromServer(resumeDraftId);
                            } catch (draftErr) {
                                console.error('Erreur restauration brouillon:', draftErr);
                                updateStepDisplay();
                                updateNavigationButtons();
                            }
                        } else {
                            // Sinon, essayer de charger depuis le cache localStorage
                            var cacheLoaded = loadFromCache();
                            if (!cacheLoaded) {
                                updateStepDisplay();
                                updateNavigationButtons();
                            }
                        }

                        // Initialiser les cartes de type
                        initOrganizationTypeCards();

                        // Événements des boutons fondateurs
                        const addFondateurBtn = document.getElementById('addFondateurBtn');
                        if (addFondateurBtn) {
                            addFondateurBtn.addEventListener('click', addFondateur);
                        }

                        // Événement pour ajouter un membre du bureau
                        const addMembreBureauBtn = document.getElementById('addMembreBureauBtn');
                        if (addMembreBureauBtn) {
                            addMembreBureauBtn.addEventListener('click', addMembreBureau);
                        }

                        // Événements géolocalisation
                        const getLocationBtn = document.getElementById('getLocationBtn');
                        if (getLocationBtn) {
                            getLocationBtn.addEventListener('click', getGeolocation);
                        }

                        // Événements des provinces/départements
                        const provinceSelect = document.getElementById('org_province');
                        if (provinceSelect) {
                            provinceSelect.addEventListener('change', updateDepartements);
                        }

                        // Initialiser la gestion zone urbaine/rurale
                        initZoneToggle();

                        // Validation avancée des champs
                        validateFieldsAdvanced();

                        // Auto-sauvegarde
                        setupAutoSave();

                        // Ajouter boutons de gestion cache dans l'interface
                        addCacheManagementButtons();

                        // Vérification connectivité
                        window.addEventListener('online', () => showNotification('Connexion rétablie', 'success'));
                        window.addEventListener('offline', checkConnectivity);

                        console.log('✅ Application initialisée avec succès - Toutes fonctionnalités actives + Cache');
                    }

                    /**
                     * Ajouter les boutons de gestion du cache
                     */
                    function addCacheManagementButtons() {
                        const saveIndicator = document.getElementById('save-indicator');
                        if (saveIndicator && saveIndicator.parentNode) {
                            const buttonsHtml = `
                                                                        <div class="btn-group btn-group-sm ms-2" role="group">
                                                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="saveManually()" title="Sauvegarder maintenant">
                                                                                <i class="fas fa-save"></i>
                                                                            </button>
                                                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearCacheManually()" title="Vider le cache">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    `;

                            // Créer un conteneur pour l'indicateur et les boutons
                            const container = document.createElement('div');
                            container.className = 'd-flex align-items-center justify-content-end';
                            container.innerHTML = `<div id="save-indicator-moved"></div>${buttonsHtml}`;

                            // Déplacer l'indicateur dans le nouveau conteneur
                            const movedIndicator = container.querySelector('#save-indicator-moved');
                            movedIndicator.appendChild(saveIndicator);

                            // Remplacer dans le DOM
                            saveIndicator.parentNode.parentNode.replaceChild(container, saveIndicator.parentNode);
                        }
                    }

                    // Initialisation au chargement de la page
                    document.addEventListener('DOMContentLoaded', initApplicationComplete);

                    // Sauvegarde auto quand l'usager quitte la page
                    window.addEventListener('beforeunload', function() {
                        if (OrganisationApp.currentStep >= 1 && !OrganisationApp.isSavingStep) {
                            saveStepToServer(OrganisationApp.currentStep);
                        }
                    });

                    // Fonctions globales pour les boutons et événements
                    window.changeStep = changeStep;
                    window.removeFondateur = removeFondateur;
                    window.handleDocumentUpload = handleDocumentUpload;
                    window.removeDocument = removeDocument;
                    window.toggleSubmissionMode = toggleSubmissionMode;
                    window.saveManually = saveManually;

                    /**
                     * Sauvegarder l'étape courante en brouillon puis rediriger vers la liste des brouillons
                     */
                    window.saveDraftAndExit = async function () {
                        try {
                            // Sauvegarder les données locales puis le serveur
                            if (typeof saveCurrentStepData === 'function') saveCurrentStepData();

                            var saveIndicator = document.getElementById('save-indicator');
                            if (saveIndicator) saveIndicator.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Enregistrement du brouillon...';

                            var res = await saveStepToServer(OrganisationApp.currentStep);

                            if (res && res.success) {
                                showNotification('Brouillon enregistré avec succès. Vous pourrez le reprendre depuis votre espace.', 'success');
                                setTimeout(function () {
                                    window.location.href = '/operator/organisations/brouillons';
                                }, 1500);
                            } else {
                                showNotification('Impossible d\'enregistrer le brouillon. Veuillez réessayer.', 'warning');
                                if (saveIndicator) saveIndicator.innerHTML = '';
                            }
                        } catch (e) {
                            console.error('Erreur saveDraftAndExit:', e);
                            showNotification('Erreur lors de la sauvegarde : ' + e.message, 'error');
                        }
                    };
                    window.clearCacheManually = clearCacheManually;

                    // Fonctions utilitaires globales
                    window.OrganisationUtils = {
                        showNotification: showNotification,
                        getOrganizationTypeLabel: getOrganizationTypeLabel,
                        validateStep: validateStep,
                        updateStepDisplay: updateStepDisplay,
                        saveToCache: saveToCache,
                        loadFromCache: loadFromCache,
                        clearCache: clearCache
                    };

                    console.log('📋 Script principal chargé (8 étapes) avec gestion documents et cache complet');
                </script>

                <!-- Scripts NIP Validation -->
                <script>
                    function showNipExample() {
                        if (window.NipValidation) {
                            const example = window.NipValidation.generateExample();
                            const validation = window.NipValidation.validateFormat(example);
                            const resultDiv = document.getElementById('nipExampleResult');

                            if (validation.valid && validation.extracted_info) {
                                resultDiv.innerHTML = `
                                                                            <div class="alert alert-success">
                                                                                <strong>Exemple généré :</strong> <code>${example}</code><br>
                                                                                <small>Âge calculé : ${validation.extracted_info.age} ans</small>
                                                                            </div>
                                                                        `;
                            } else {
                                resultDiv.innerHTML = `
                                                                            <div class="alert alert-info">
                                                                                <strong>Exemple généré :</strong> <code>${example}</code>
                                                                            </div>
                                                                        `;
                            }
                        }
                    }

                    // Ajouter bouton d'aide NIP dans les champs
                    document.addEventListener('DOMContentLoaded', function () {
                        const nipInputs = document.querySelectorAll('input[data-validate="nip"]');
                        nipInputs.forEach(function (input) {
                            const container = input.closest('.input-group');
                            if (container && !container.querySelector('.btn-help-nip')) {
                                const helpBtn = document.createElement('button');
                                helpBtn.type = 'button';
                                helpBtn.className = 'btn btn-outline-info btn-help-nip';
                                helpBtn.innerHTML = '<i class="fas fa-question-circle"></i>';
                                helpBtn.title = 'Aide format NIP';
                                helpBtn.onclick = function () {
                                    $('#nipHelpModal').modal('show');
                                };

                                // Ajouter après l'input-group-text existant
                                const inputGroupText = container.querySelector('.input-group-text');
                                if (inputGroupText) {
                                    container.insertBefore(helpBtn, inputGroupText.nextSibling);
                                } else {
                                    container.appendChild(helpBtn);
                                }
                            }
                        });
                    });
                </script>

                <!-- INTÉGRATION WORKFLOW 2 PHASES -->
                <script>
                    // Initialisation du workflow 2 phases
                    document.addEventListener('DOMContentLoaded', function () {
                        console.log('🔧 Initialisation workflow 2 phases...');

                        // Vérifier si le module est chargé
                        if (window.Workflow2Phases) {
                            try {
                                const workflow2PhasesInit = window.Workflow2Phases.init();
                                console.log('✅ Workflow 2 phases initialisé:', workflow2PhasesInit);
                            } catch (error) {
                                console.error('❌ Erreur initialisation workflow 2 phases:', error);
                            }
                        } else {
                            console.warn('⚠️ Module Workflow2Phases non trouvé');
                        }
                    });

                    // ============================================================
                    // 🔍 DEBUG SOUMISSION - À CONSULTER DANS LA CONSOLE NAVIGATEUR
                    // ============================================================
                    window._debugSubmission = function (label) {
                        const formEl = document.getElementById('organisationForm');
                        const formAction = formEl ? formEl.action : 'FORM_NOT_FOUND';
                        const formMethod = formEl ? formEl.method : 'FORM_NOT_FOUND';
                        const currentUrl = window.location.href;
                        const currentOrigin = window.location.origin;
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        const csrfToken = csrfMeta ? csrfMeta.content : 'META_NOT_FOUND';

                        console.group('🔍 DEBUG SOUMISSION - ' + label);
                        console.log('📍 Page actuelle:', currentUrl);
                        console.log('🌐 Origin:', currentOrigin);
                        console.log('📝 Form action (raw HTML):', formEl ? formEl.getAttribute('action') : 'N/A');
                        console.log('📝 Form action (resolved):', formAction);
                        console.log('📝 Form method:', formMethod);
                        console.log('🔑 CSRF token:', csrfToken ? csrfToken.substring(0, 15) + '...' : 'ABSENT');
                        console.log('🔧 Workflow2Phases chargé:', !!window.Workflow2Phases);
                        if (window.Workflow2Phases) {
                            console.log('🔧 W2P config.routes.phase1:', window.Workflow2Phases.config.routes.phase1);
                        }
                        console.groupEnd();

                        return { formAction, formMethod, currentOrigin, csrfToken };
                    };

                    // submitPhase1 déjà exposé dans le bloc workflow
                </script>

                <script src="{{ asset('js/unified-config-manager.js') }}"></script>
                <script src="{{ asset('js/unified-csrf-manager.js') }}"></script>
                <script src="{{ asset('js/csrf-manager.js') }}"></script> <!-- Avec détection -->
                <script src="{{ asset('js/workflow-2phases.js') }}"></script>
                <script src="{{ asset('js/chunking-import.js') }}"></script>

        @endpush