{{--
============================================================================
ADHERENTS-IMPORT.BLADE.PHP - VUE PHASE 2 IMPORT ADHÉRENTS
Vue complète pour l'import d'adhérents en Phase 2 du workflow SGLP
Version: 4.3 - Timeout étendu + Architecture modulaire CORRIGÉE
============================================================================
--}}

@extends('layouts.operator')

@section('title', 'Import des Adhérents - Phase 2')
@section('page-title', 'Phase 2: Import des Adhérents')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/gabon-charte.css') }}">
<link rel="stylesheet" href="{{ asset('css/confirmation-import.css') }}">
<link rel="stylesheet" href="{{ asset('css/adherents-manager.css') }}">
<link rel="stylesheet" href="{{ asset('css/chunking-interface.css') }}">


<style>
/* ========================================================================
   STYLES PHASE 2 - DESIGN GABONAIS MODERNE
   ======================================================================== */
:root {
    --gabon-green: #009e3f;
    --gabon-green-light: #00b347;
    --gabon-yellow: #ffcd00;
    --gabon-blue: #003f7f;
    --phase2-gradient: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-green-light) 100%);
    --warning-gradient: linear-gradient(135deg, var(--gabon-yellow) 0%, #fd7e14 100%);
}

.phase2-header {
    background: var(--phase2-gradient);
    color: white;
    padding: 2rem 0;
    position: relative;
    overflow: hidden;
}

.phase2-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="gabon-pattern" x="0" y="0" width="25" height="25" patternUnits="userSpaceOnUse"><circle cx="12.5" cy="12.5" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23gabon-pattern)"/></svg>');
    opacity: 0.3;
}

.phase-content {
    position: relative;
    z-index: 2;
}

.phase-indicator {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50px;
    padding: 0.5rem 1.5rem;
    display: inline-block;
    margin-bottom: 1rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    animation: phaseGlow 3s ease-in-out infinite;
}

@keyframes phaseGlow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
    50% { box-shadow: 0 0 0 15px rgba(255, 255, 255, 0); }
}

.stats-dashboard {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin: -3rem 0 2rem 0;
    position: relative;
    z-index: 10;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    border: 3px solid var(--gabon-green);
}

.upload-zone {
    border: 3px dashed #dee2e6;
    border-radius: 20px;
    padding: 3rem 2rem;
    text-align: center;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.upload-zone::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s;
}

.upload-zone:hover::before {
    left: 100%;
}

.upload-zone:hover {
    border-color: var(--gabon-green);
    background: linear-gradient(135deg, rgba(0, 158, 63, 0.05) 0%, rgba(0, 179, 71, 0.1) 100%);
    transform: translateY(-2px);
    box-shadow: 0 15px 40px rgba(0, 158, 63, 0.2);
}

.upload-zone.dragover {
    border-color: var(--gabon-green);
    background: linear-gradient(135deg, rgba(0, 158, 63, 0.1) 0%, rgba(0, 179, 71, 0.15) 100%);
    transform: scale(1.02);
}

.upload-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 2rem;
    background: var(--phase2-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: uploadPulse 2s ease-in-out infinite;
}

@keyframes uploadPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.progress-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin: 2rem 0;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    border-left: 5px solid var(--gabon-blue);
}

.btn-gabon {
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: bold;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border: none;
    position: relative;
    overflow: hidden;
}

.btn-gabon::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s;
}

.btn-gabon:hover::before {
    left: 100%;
}

.btn-primary-gabon {
    background: var(--phase2-gradient);
    color: white;
    box-shadow: 0 6px 20px rgba(0, 158, 63, 0.4);
}

.btn-primary-gabon:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 158, 63, 0.6);
}

.btn-warning-gabon {
    background: var(--warning-gradient);
    color: #333;
    box-shadow: 0 6px 20px rgba(255, 205, 0, 0.4);
}

.chunking-modal .modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
}

.chunking-modal .modal-header {
    background: var(--phase2-gradient);
    color: white;
    border: none;
    padding: 2rem;
}

.chunk-progress {
    height: 8px;
    border-radius: 10px;
    background: #e9ecef;
    overflow: hidden;
    margin: 1rem 0;
}

.chunk-progress-bar {
    height: 100%;
    background: var(--phase2-gradient);
    transition: width 0.3s ease;
    border-radius: 10px;
}

.alert-phase2 {
    border: none;
    border-radius: 15px;
    padding: 1.5rem;
    margin: 1rem 0;
}

.alert-phase2.alert-success {
    background: linear-gradient(135deg, rgba(0, 158, 63, 0.1) 0%, rgba(0, 179, 71, 0.05) 100%);
    border-left: 4px solid var(--gabon-green);
}

.fade-in {
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from { 
        opacity: 0; 
        transform: translateY(30px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* === STYLES FINALISATION === */
.btn-finalization {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    position: relative;
    overflow: hidden;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-finalization::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.6s;
}

.btn-finalization:hover::before {
    left: 100%;
}

.btn-finalization:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 35px rgba(0, 158, 63, 0.3);
}

.btn-warning-gabon {
    background: var(--warning-gradient);
    color: #333;
    box-shadow: 0 6px 20px rgba(255, 205, 0, 0.4);
    border: 2px solid rgba(255, 205, 0, 0.3);
}

.btn-warning-gabon:hover {
    color: #333;
    background: linear-gradient(135deg, #fd7e14 0%, var(--gabon-yellow) 100%);
    box-shadow: 0 10px 30px rgba(255, 205, 0, 0.6);
    border-color: var(--gabon-yellow);
}

.btn-primary-gabon.btn-finalization:hover {
    box-shadow: 0 12px 35px rgba(0, 158, 63, 0.5);
}

#finalization-processing {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 249, 250, 0.95) 100%);
    border-radius: 15px;
    padding: 1.5rem;
    border: 2px solid var(--gabon-green);
    backdrop-filter: blur(10px);
}

#finalization-buttons.processing {
    opacity: 0.6;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

@media (max-width: 768px) {
    .phase2-header {
        padding: 1rem 0;
    }
    
    .btn-finalization {
        min-height: 100px;
        font-size: 0.9rem;
    }
    
    .btn-finalization .fa-2x {
        font-size: 1.5em !important;
    }
    
    .btn-finalization small {
        font-size: 0.75rem;
    }
    
    .stats-dashboard {
        margin: -2rem 0 1rem 0;
        padding: 1rem;
    }
    
    .upload-zone {
        padding: 2rem 1rem;
    }
    
    .upload-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    #finalization-buttons {
        margin: 0 -0.5rem;
    }
    
    #finalization-buttons .col-md-6 {
        padding: 0 0.5rem;
        margin-bottom: 1rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- 
    ========================================================================
    SECTION 1: HEADER PHASE 2 AVEC CONTEXTE ORGANISATION
    ========================================================================
    --}}
    <div class="phase2-header">
        <div class="container">
            <div class="phase-content">
                <!-- Indicateur de phase -->
                <div class="phase-indicator">
                    <i class="fas fa-users me-2"></i>
                    Phase 2 : Import des Adhérents
                </div>

                <!-- Breadcrumb gabonais -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-white-50 mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('operator.dashboard') }}" class="text-white opacity-75">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('operator.dossiers.index') }}" class="text-white opacity-75">Organisations</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('operator.dossiers.show', $organisation->id ?? 1) }}" class="text-white opacity-75">{{ $organisation->nom ?? 'Organisation' }}</a>
                        </li>
                        <li class="breadcrumb-item active text-white">Import Adhérents</li>
                    </ol>
                </nav>

                <!-- Titre principal avec organisation -->
                <div class="row align-items-center mt-3">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i class="fas fa-upload me-3"></i>
                            Import des Adhérents
                        </h1>
                        <div class="header-subtitle">
                            <strong>{{ $organisation->nom ?? 'Organisation Test' }}</strong>
                            @if(isset($organisation->sigle) && $organisation->sigle)
                            ({{ $organisation->sigle }})
                            @endif
                            | {{ ucfirst($organisation->type ?? 'association') }}
                        </div>
                        <div class="header-meta mt-2">
                            <span class="me-3">
                                <i class="fas fa-file-alt me-1"></i>
                                {{ $dossier->numero_dossier ?? 'DOS-2025-001' }}
                            </span>
                            <span class="me-3">
                                <i class="fas fa-receipt me-1"></i>
                                {{ $dossier->numero_recepisse ?? 'REC-2025-001' }}
                            </span>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>
                                Phase 1 Complétée
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('operator.dossiers.show', $organisation->id ?? 1) }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            <button class="btn btn-warning" onclick="showHelp()">
                                <i class="fas fa-question-circle me-2"></i>Aide
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        {{-- 
        ========================================================================
        SECTION 2: DASHBOARD STATISTIQUES ADHÉRENTS
        ========================================================================
        --}}
        <div class="stats-dashboard fade-in">
            <div class="row align-items-center mb-4">
                <div class="col-md-8">
                    <h3 class="text-primary fw-bold mb-2">
                        <i class="fas fa-chart-pie me-2"></i>
                        Statistiques des Adhérents
                    </h3>
                    <p class="text-muted mb-0">Suivi en temps réel avec détection automatique des gros volumes</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshStats()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="showStatsDetails()">
                            <i class="fas fa-chart-bar me-1"></i>Détails
                        </button>
                    </div>
                </div>
            </div>

            <!-- Grid statistiques -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <h4 class="text-primary mb-1">{{ $adherents_stats['existants'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Existants</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                            <h4 class="text-warning mb-1">{{ $adherents_stats['minimum_requis'] ?? 10 }}</h4>
                            <p class="text-muted mb-0">Minimum Requis</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            @php $manquants = $adherents_stats['manquants'] ?? 5; @endphp
                            <div class="{{ $manquants > 0 ? 'text-danger' : 'text-success' }} mb-2">
                                <i class="fas fa-{{ $manquants > 0 ? 'user-plus' : 'check-circle' }} fa-2x"></i>
                            </div>
                            <h4 class="{{ $manquants > 0 ? 'text-danger' : 'text-success' }} mb-1">
                                {{ $manquants > 0 ? $manquants : '✓' }}
                            </h4>
                            <p class="text-muted mb-0">{{ $manquants > 0 ? 'Manquants' : 'Complet' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-upload fa-2x"></i>
                            </div>
                            <h4 class="text-info mb-1" id="import-count">0</h4>
                            <p class="text-muted mb-0">À Importer</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détection du mode de traitement -->
            <div class="alert alert-phase2 alert-info">
                <div class="d-flex align-items-center">
                    <i class="fas fa-magic fa-2x me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Détection Automatique du Volume</h6>
                        <p class="mb-0">
                            <span id="processing-mode">Mode standard</span> activé. 
                            Le système bascule automatiquement vers le chunking pour volumes > {{ $upload_config['chunking_threshold'] ?? 200 }} adhérents.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
        ========================================================================
        SECTION 3: INTERFACE D'IMPORT PRINCIPALE
        ========================================================================
        --}}
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-cloud-upload-alt me-2"></i>
                                Interface d'Import Intelligente
                            </h5>
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-light btn-sm" onclick="refreshUploadZone()">
                                    <i class="fas fa-sync-alt me-1"></i>Actualiser
                                </button>
                                <button class="btn btn-outline-light btn-sm" onclick="clearUploadZone()">
                                    <i class="fas fa-trash me-1"></i>Vider
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Zone d'upload modernisée -->
                        <div class="upload-zone" id="upload-zone">
                            <div id="upload-initial" class="upload-state">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-white"></i>
                                </div>
                                <h4 class="text-primary fw-bold mb-3">Glissez-déposez votre fichier ou cliquez pour sélectionner</h4>
                                <p class="text-muted mb-4">
                                    <strong>Formats acceptés :</strong> Excel (.xlsx) ou CSV<br>
                                    <strong>Taille maximum :</strong> {{ $upload_config['max_file_size'] ?? '10MB' }}<br>
                                    <strong>Volume maximum :</strong> {{ number_format($upload_config['max_adherents'] ?? 50000) }} adhérents<br>
                                    <small><i class="fas fa-magic me-1"></i>Chunking automatique activé pour volumes > {{ $upload_config['chunking_threshold'] ?? 200 }}</small>
                                </p>
                                <div class="d-flex gap-3 justify-content-center flex-wrap">
                                    <button type="button" id="select-file-btn" class="btn btn-gabon btn-primary-gabon">
                                        <i class="fas fa-file-excel me-2"></i>
                                        Sélectionner un fichier
                                    </button>
                                    <a href="{{ $urls['template_download'] ?? '#' }}" class="btn btn-gabon btn-warning-gabon">
                                        <i class="fas fa-download me-2"></i>
                                        Télécharger le modèle
                                    </a>
                                </div>
                                <input type="file" id="file-input" class="d-none" accept=".xlsx,.csv">
                            </div>

                            <!-- État de traitement avec chunking -->
                            <div id="upload-processing" class="upload-state d-none">
                                <div class="upload-icon mb-4">
                                    <i class="fas fa-cog fa-spin fa-3x text-white"></i>
                                </div>
                                <h4 id="processing-title" class="text-primary fw-bold mb-3">Traitement intelligent en cours...</h4>
                                
                                <!-- Progress principal -->
                                <div class="chunk-progress mb-3">
                                    <div id="progress-bar" class="chunk-progress-bar" style="width: 0%"></div>
                                </div>
                                <div class="text-center mb-3">
                                    <span id="progress-text" class="badge bg-primary fs-6">0%</span>
                                </div>
                                
                                <!-- Détails du traitement -->
                                <div id="processing-details" class="text-muted">
                                    <div id="current-chunk" class="fw-bold mb-2">Préparation...</div>
                                    <div id="processing-stats" class="small">
                                        <div class="row text-center g-2">
                                            <div class="col-md-3">
                                                <div class="bg-light p-2 rounded">
                                                    <div class="fw-bold text-primary" id="processed-count">0</div>
                                                    <small>Traités</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="bg-light p-2 rounded">
                                                    <div class="fw-bold text-success" id="valid-count">0</div>
                                                    <small>Valides</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="bg-light p-2 rounded">
                                                    <div class="fw-bold text-warning" id="anomaly-count">0</div>
                                                    <small>Anomalies</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="bg-light p-2 rounded">
                                                    <div class="fw-bold text-info" id="speed-indicator">--</div>
                                                    <small>Vitesse</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Contrôles du traitement -->
                                <div class="mt-4" id="processing-controls">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button class="btn btn-outline-warning btn-sm" onclick="pauseProcessing()" id="pause-btn">
                                            <i class="fas fa-pause me-1"></i>Pause
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="cancelProcessing()">
                                            <i class="fas fa-stop me-1"></i>Annuler
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Résultats finaux -->
                            <div id="upload-results" class="upload-state d-none">
                                <div id="success-results" class="d-none">
                                    <div class="upload-icon bg-success mb-4">
                                        <i class="fas fa-check-circle fa-3x text-white"></i>
                                    </div>
                                    <h4 class="text-success fw-bold mb-3">Import réussi !</h4>
                                    <div id="import-summary" class="mt-3"></div>
                                    
                                    <!-- ✨ BOUTONS DE FINALISATION DUAL -->
                                    <div class="mt-4">
                                        <!-- Alerte informative -->
                                        <div class="alert alert-phase2 alert-info mb-4">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                                <div>
                                                    <h6 class="alert-heading mb-1">Finalisation du dossier</h6>
                                                    <p class="mb-0">
                                                        Choisissez le mode de finalisation selon vos besoins.
                                                        Vous pouvez soumettre immédiatement ou sauvegarder pour soumettre plus tard.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Conteneur des boutons avec design gabonais -->
                                        <div class="row g-3" id="finalization-buttons">
                                            <div class="col-md-6">
                                                <button type="button" 
                                                        id="finalize-later-btn" 
                                                        class="btn btn-gabon btn-warning-gabon btn-finalization btn-lg w-100"
                                                        onclick="finalise({{ $dossier->id ?? 1 }}, 'later')">
                                                    <div class="text-center">
                                                        <i class="fas fa-save fa-2x mb-2"></i>
                                                        <div class="fw-bold">Sauvegarder et soumettre plus tard</div>
                                                        <small class="opacity-75">Conserve en brouillon pour révision</small>
                                                    </div>
                                                </button>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" 
                                                        id="finalize-now-btn" 
                                                        class="btn btn-gabon btn-primary-gabon btn-finalization btn-lg w-100"
                                                        onclick="finalise({{ $dossier->id ?? 1 }}, 'now')">
                                                    <div class="text-center">
                                                        <i class="fas fa-rocket fa-2x mb-2"></i>
                                                        <div class="fw-bold">Soumettre Maintenant</div>
                                                        <small class="opacity-75">Finalise et soumet immédiatement</small>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Indicateur de traitement AJAX -->
                                        <div id="finalization-processing" class="d-none mt-4">
                                            <div class="text-center">
                                                <div class="spinner-border text-primary me-2" role="status">
                                                    <span class="visually-hidden">Traitement...</span>
                                                </div>
                                                <span id="finalization-message" class="text-muted fw-bold">Finalisation en cours...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                 
                                <div id="error-results" class="d-none">
                                    <div class="upload-icon bg-danger mb-4">
                                        <i class="fas fa-exclamation-triangle fa-3x text-white"></i>
                                    </div>
                                    <h4 class="text-danger fw-bold mb-3">Erreur lors de l'import</h4>
                                    <div id="error-message" class="text-danger mt-3 p-3 bg-light rounded"></div>
                                    <div class="mt-4">
                                        <button class="btn btn-outline-primary" onclick="resetUpload()">
                                            <i class="fas fa-redo me-2"></i>Réessayer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations et instructions -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="alert alert-phase2 alert-info">
                                    <h6 class="fw-bold mb-2">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Instructions d'import
                                    </h6>
                                    <ol class="mb-0 small">
                                        <li>Téléchargez le modèle Excel ci-dessus</li>
                                        <li>Remplissez les informations des adhérents</li>
                                        <li>Sauvegardez au format Excel (.xlsx)</li>
                                        <li>Glissez-déposez le fichier dans la zone</li>
                                        <li>Le système traite automatiquement</li>
                                        <li>Vérifiez les résultats et finalisez</li>
                                    </ol>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-phase2 alert-success">
                                    <h6 class="fw-bold mb-2">
                                        <i class="fas fa-magic me-2"></i>
                                        Fonctionnalités avancées
                                    </h6>
                                    <ul class="mb-0 small">
                                        <li><strong>NIP gabonais :</strong> Format XX-QQQQ-YYYYMMDD validé</li>
                                        <li><strong>Chunking adaptatif :</strong> Traitement par lots intelligent</li>
                                        <li><strong>Détection doublons :</strong> Automatique avec conservation</li>
                                        <li><strong>Gestion anomalies :</strong> Classification et rapport détaillé</li>
                                        <li><strong>Pause/Reprise :</strong> Contrôle total du processus</li>
                                        <li><strong>Monitoring temps réel :</strong> Statistiques en direct</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 
    ========================================================================
    SECTION 4: MODAL DE FINALISATION AVANCÉE
    ========================================================================
    --}}
    <div class="modal fade" id="finalizeModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-rocket me-2"></i>
                        Finalisation du Dossier Phase 2
                    </h5>
                </div>
                <div class="modal-body">
                    <div id="finalize-summary">
                        <h6 class="text-primary mb-3">Récapitulatif final de l'import :</h6>
                        
                        <!-- Statistiques finales -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center">
                                        <h4 class="text-primary mb-1" id="final-total-count">0</h4>
                                        <small class="text-muted">Total importés</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center">
                                        <h4 class="text-success mb-1" id="final-valid-count">0</h4>
                                        <small class="text-muted">Adhérents valides</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="final-stats"></div>
                        <div id="final-anomalies" class="mt-3"></div>
                    </div>
                    
                    <div class="alert alert-info border-0 mt-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Information importante</h6>
                                <p class="mb-0">
                                    Une fois finalisé, votre dossier sera envoyé pour traitement administratif.
                                    Vous recevrez un accusé de réception détaillé avec QR Code.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cancelFinalization()">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="button" id="confirm-finalize" class="btn btn-success btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>
                        Confirmer et Finaliser
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 
    ========================================================================
    SECTION 5: MODAL D'AIDE CONTEXTUELLE
    ========================================================================
    --}}
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-question-circle me-2"></i>
                        Guide d'Import Phase 2 - Adhérents SGLP
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-primary">🎯 Objectif Phase 2</h6>
                            <p class="small">
                                Importer et valider la liste complète des adhérents de votre organisation
                                selon les exigences légales gabonaises.
                            </p>
                            <h6 class="text-primary">📋 Prérequis</h6>
                            <ul class="small">
                                <li>Phase 1 complétée (organisation créée)</li>
                                <li>Fichier Excel/CSV avec adhérents</li>
                                <li>NIPs valides format XX-QQQQ-YYYYMMDD</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-success">✨ Fonctionnalités</h6>
                            <ul class="small">
                                <li><strong>Chunking adaptatif :</strong> Traitement par lots intelligent</li>
                                <li><strong>Validation temps réel :</strong> Contrôle automatique</li>
                                <li><strong>Gestion anomalies :</strong> Conservation + classification</li>
                                <li><strong>Pause/Reprise :</strong> Contrôle utilisateur</li>
                                <li><strong>Monitoring live :</strong> Statistiques temps réel</li>
                            </ul>
                            <h6 class="text-success">🔧 Format NIP</h6>
                            <div class="bg-light p-2 rounded">
                                <code>A1-2345-19901225</code><br>
                                <small>XX-QQQQ-YYYYMMDD</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-warning">⚠️ Points d'attention</h6>
                            <ul class="small">
                                <li>Vérifiez la validité des NIPs</li>
                                <li>Éliminez les doublons avant import</li>
                                <li>Respectez le format des colonnes</li>
                                <li>Surveillez les anomalies détectées</li>
                                <li>Finalisez après vérification complète</li>
                            </ul>
                            <h6 class="text-info">📞 Support</h6>
                            <p class="small">
                                En cas de problème, contactez le support technique SGLP
                                ou utilisez le système de diagnostic intégré.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire caché pour soumission -->
<form id="adherents-form" method="POST" action="{{ $urls['store_adherents'] ?? '#' }}" style="display: none;">
    @csrf
    <input type="hidden" id="adherents-data" name="adherents" value="">
    <input type="hidden" name="phase" value="2">
    <input type="hidden" name="processing_method" id="processing-method" value="">
    <input type="hidden" name="import_stats" id="import-stats" value="">
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>

{{-- ✅ CONFIGURATION PHASE 2 EN PREMIER --}}
<script>
console.log('🚀 Initialisation Configuration Phase 2 v5.1 CORRIGÉE');

// ✅ CONFIGURATION PHASE 2 COMPLÈTE
window.Phase2Config = {
    dossierId: {{ $dossier->id ?? 1 }},
    organisationId: {{ $organisation->id ?? 1 }},
    urls: {
        storeAdherents: '{{ $urls["store_adherents"] ?? "#" }}',
        confirmation: '{{ $urls["confirmation"] ?? "#" }}',
        templateDownload: '{{ $urls["template_download"] ?? "#" }}',
        processChunk: '{{ route("operator.chunking.upload-chunk") }}',
        refreshCSRF: '{{ url("/csrf-token") }}',
        healthCheck: '{{ url("/operator/chunking/status/health") }}'
    },
    upload: {
        chunkSize: {{ $upload_config['chunk_size'] ?? 500 }},
        maxAdherents: {{ $upload_config['max_adherents'] ?? 50000 }},
        maxFileSize: '{{ $upload_config["max_file_size"] ?? "10MB" }}',
        chunkingThreshold: {{ $upload_config['chunking_threshold'] ?? 501 }},
        chunkingEnabled: true,
        maxRetries: 5,
        pauseBetweenChunks: 3000,
        timeoutPerChunk: 25000
    },
    stats: {
        existants: {{ $adherents_stats['existants'] ?? 0 }},
        minimumRequis: {{ $adherents_stats['minimum_requis'] ?? 10 }},
        manquants: {{ $adherents_stats['manquants'] ?? 10 }}
    },
    csrf: '{{ csrf_token() }}',
    phase2: {
        enabled: true,
        dossierNumero: '{{ $dossier->numero_dossier ?? "DOS-2025-001" }}',
        organisationNom: '{{ $organisation->nom ?? "Organisation Test" }}',
        organisationType: '{{ $organisation->type ?? "association" }}',
        version: '5.1',
        insertionDuringChunking: true,
        debugMode: {{ config('app.debug') ? 'true' : 'false' }}
    }
};

console.log('✅ Phase2Config initialisé:', window.Phase2Config);

// ✅ FONCTION INDICATEUR PROGRESSION FINALISATION
window.showFinalizationProgress = function() {
    console.log('🎯 Affichage indicateur de progression finalisation');
    
    const progressIndicator = document.createElement('div');
    progressIndicator.id = 'finalization-progress-indicator';
    progressIndicator.className = 'position-fixed top-0 start-0 w-100 bg-info text-white p-3 text-center';
    progressIndicator.style.zIndex = '9999';
    progressIndicator.innerHTML = `
        <div class="d-flex align-items-center justify-content-center">
            <div class="spinner-border spinner-border-sm me-3" role="status"></div>
            <div>
                <strong>Finalisation en cours...</strong><br>
                <small id="finalization-timer">Temps écoulé: 0:00</small><br>
                <small class="text-light">⏱️ Processus pouvant prendre jusqu'à 10 minutes pour gros volumes</small>
            </div>
        </div>
    `;
    
    document.body.appendChild(progressIndicator);
    
    const startTime = Date.now();
    const timer = setInterval(() => {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        const timerElement = document.getElementById('finalization-timer');
        if (timerElement) {
            timerElement.textContent = `Temps écoulé: ${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
    }, 1000);
    
    return function cleanup() {
        clearInterval(timer);
        const indicator = document.getElementById('finalization-progress-indicator');
        if (indicator) {
            indicator.remove();
        }
        console.log('🧹 Nettoyage indicateur de progression');
    };
};

// ✅ FONCTION FINALISATION AJAX CORRIGÉE
window.finalise = function(dossierId, statut) {
    console.log(`🎯 Finalisation dossier ${dossierId} en mode: ${statut} (TIMEOUT ÉTENDU)`);
    
    const cleanupProgress = window.showFinalizationProgress();
    
    const cleanDossierId = String(dossierId).replace(/[^0-9]/g, '');
    
    if (!cleanDossierId || !statut || !['later', 'now'].includes(statut)) {
        cleanupProgress();
        showFinalizationNotification('Erreur: Paramètres de finalisation invalides', 'error');
        console.error('❌ Paramètres invalides:', { dossierId, statut, cleanDossierId });
        return;
    }
    
    const messages = {
        'now': {
            confirm: '🚀 Finaliser et soumettre le dossier maintenant ?\n\n' +
                    '✅ Le dossier sera immédiatement envoyé pour validation\n' +
                    '📄 Un accusé de réception sera généré\n' +
                    '⚠️ Cette action est irréversible',
            processing: 'Finalisation et soumission en cours...',
            success: '🎉 Dossier finalisé et soumis avec succès !\n📄 Accusé de réception disponible'
        },
        'later': {
            confirm: '💾 Sauvegarder le dossier en brouillon ?\n\n' +
                    '✅ Le dossier sera conservé avec toutes ses données\n' +
                    '🔄 Vous pourrez le soumettre plus tard\n' +
                    '📋 Accessible depuis la liste des dossiers',
            processing: 'Sauvegarde en brouillon...',
            success: '💾 Dossier sauvegardé en brouillon !\n🔄 Soumission possible ultérieurement'
        }
    };
    
    if (!confirm(messages[statut].confirm)) {
        cleanupProgress();
        console.log('⚠️ Finalisation annulée par l\'utilisateur');
        return;
    }
    
    setFinalizationProcessing(true, messages[statut].processing);
    
    const baseUrl = window.location.origin;
    const endpoint = `${baseUrl}/operator/dossiers/${cleanDossierId}/finalize-${statut}`;
    
    console.log('🌐 Endpoint finalisation:', endpoint);
    
    const requestData = {
        processing_info: {
            started_at: new Date().toISOString(),
            processing_time: getFinalizationProcessingTime(),
            method: `ajax_${statut}`,
            solution: 'NO_MIGRATION_EXISTING_COLUMNS_ONLY'
        },
        import_stats: getFinalizationImportStats(),
        phase2_data: {
            chunking_used: window.chunkingProcessor?.wasUsed || false,
            total_chunks: window.chunkingProcessor?.totalChunks || 0,
            processing_mode: window.Phase2Config?.upload?.chunkingEnabled ? 'chunking' : 'standard',
            completion_method: 'ajax_finalisation'
        },
        browser_context: {
            user_agent: navigator.userAgent,
            timestamp: new Date().toISOString(),
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            screen_resolution: `${screen.width}x${screen.height}`,
            referrer: document.referrer || 'direct'
        },
        technical_note: 'Finalisation utilisant uniquement les colonnes existantes de la table dossiers'
    };
    
    const csrfToken = window.Phase2Config?.csrf || 
                      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                      document.querySelector('input[name="_token"]')?.value;
    
    if (!csrfToken) {
        cleanupProgress();
        console.error('❌ Token CSRF introuvable');
        setFinalizationProcessing(false);
        showFinalizationNotification('Erreur: Token de sécurité manquant. Rechargez la page.', 'error');
        return;
    }
    
    // ✅ TIMEOUT ÉTENDU AVEC ABORT CONTROLLER
    const controller = new AbortController();
    const timeoutId = setTimeout(() => {
        console.warn('⚠️ Timeout de finalisation après 10 minutes');
        controller.abort();
    }, 1200000); // 20 minutes
    
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-Solution': 'NO_MIGRATION_REQUIRED',
            'X-Extended-Timeout': '600',
            'X-Large-Volume': 'true'
        },
        body: JSON.stringify(requestData),
        credentials: 'same-origin',
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        console.log(`📡 Réponse serveur: ${response.status} ${response.statusText}`);
        
        if (!response.ok) {
            return response.text().then(text => {
                let errorData;
                try {
                    errorData = JSON.parse(text);
                } catch (e) {
                    errorData = { message: text };
                }
                
                console.error('❌ Erreur HTTP:', {
                    status: response.status,
                    data: errorData,
                    endpoint: endpoint
                });
                
                throw new Error(`Erreur HTTP ${response.status}: ${errorData.message || response.statusText}`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        cleanupProgress();
        console.log('✅ Finalisation réussie:', data);
        
        if (data.success) {
            setFinalizationProcessing(false);
            showFinalizationNotification(messages[statut].success, 'success');
            disableFinalizationButtons();
            
            if (data.redirect_url) {
                console.log(`🔄 Redirection vers: ${data.redirect_url}`);
                
                const redirectDelay = statut === 'now' ? 3000 : 2000;
                setTimeout(() => {
                    try {
                        const url = new URL(data.redirect_url, window.location.origin);
                        console.log(`🔄 Redirection effective: ${url.href}`);
                        window.location.href = url.href;
                    } catch (urlError) {
                        console.error('❌ URL redirection invalide:', urlError);
                        showFinalizationNotification('Redirection par défaut...', 'info');
                        window.location.href = '/operator/dossiers';
                    }
                }, redirectDelay);
            } else {
                const defaultUrl = statut === 'now' ? 
                    `/operator/dossiers/${cleanDossierId}/confirmation` : 
                    '/operator/dossiers';
                    
                setTimeout(() => {
                    console.log(`🔄 Redirection par défaut: ${defaultUrl}`);
                    window.location.href = defaultUrl;
                }, 2000);
            }
            
        } else {
            setFinalizationProcessing(false);
            const errorMessage = data.message || 'Erreur lors de la finalisation';
            console.error('❌ Échec finalisation:', data);
            showFinalizationNotification(errorMessage, 'error');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        cleanupProgress();
        
        console.error('❌ Erreur finalisation:', {
            message: error.message,
            endpoint: endpoint,
            statut: statut,
            dossierId: cleanDossierId,
            timeout_config: '600s',
            error_type: error.name
        });
        
        setFinalizationProcessing(false);
        
        if (error.name === 'AbortError') {
            showFinalizationNotification(
                '⏰ Timeout de finalisation après 10 minutes.\n\n' +
                '💡 Solutions :\n' +
                '• Réduire le nombre d\'adhérents\n' +
                '• Utiliser l\'import par lots\n' +
                '• Contacter le support technique', 
                'error'
            );
            return;
        }
        
        let errorMessage = 'Erreur de communication avec le serveur.';
        
        if (error.message.includes('404')) {
            errorMessage = '🔍 Routes de finalisation non trouvées.\n💡 Vérifiez les routes dans operator.php';
        } else if (error.message.includes('403')) {
            errorMessage = '🚫 Accès non autorisé.\n💡 Veuillez vous reconnecter.';
        } else if (error.message.includes('500')) {
            errorMessage = '⚠️ Erreur serveur.\n💡 Vérifiez les logs Laravel.';
        } else if (error.message.includes('Failed to fetch')) {
            errorMessage = '🌐 Problème de connexion.\n💡 Vérifiez votre connexion Internet.';
        }
        
        showFinalizationNotification(errorMessage, 'error');
    });
};

// ✅ FONCTIONS AUXILIAIRES
function setFinalizationProcessing(processing, message = '') {
    const processingDiv = document.getElementById('finalization-processing');
    const messageSpan = document.getElementById('finalization-message');
    const buttonsContainer = document.getElementById('finalization-buttons');
    
    console.log(`🔄 Processing state: ${processing ? 'START' : 'STOP'} - ${message}`);
    
    if (processing) {
        if (processingDiv) processingDiv.classList.remove('d-none');
        if (messageSpan) messageSpan.textContent = message;
        if (buttonsContainer) {
            buttonsContainer.classList.add('processing');
            buttonsContainer.style.opacity = '0.6';
            buttonsContainer.style.pointerEvents = 'none';
        }
        
        document.querySelectorAll('#finalize-later-btn, #finalize-now-btn').forEach(btn => {
            btn.disabled = true;
            btn.style.cursor = 'not-allowed';
        });
    } else {
        if (processingDiv) processingDiv.classList.add('d-none');
        if (buttonsContainer) {
            buttonsContainer.classList.remove('processing');
            buttonsContainer.style.opacity = '1';
            buttonsContainer.style.pointerEvents = 'auto';
        }
        
        document.querySelectorAll('#finalize-later-btn, #finalize-now-btn').forEach(btn => {
            if (!btn.classList.contains('permanently-disabled')) {
                btn.disabled = false;
                btn.style.cursor = 'pointer';
            }
        });
    }
}

function disableFinalizationButtons() {
    console.log('🔒 Désactivation définitive des boutons');
    
    ['finalize-later-btn', 'finalize-now-btn'].forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.disabled = true;
            btn.classList.add('permanently-disabled');
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            
            const icon = btn.querySelector('i');
            if (icon) icon.className = 'fas fa-check-circle fa-2x mb-2';
            
            const text = btn.querySelector('.fw-bold');
            if (text) {
                text.textContent = btnId.includes('later') ? 'Sauvegardé ✓' : 'Soumis ✓';
            }
        }
    });
}

function showFinalizationNotification(message, type = 'info') {
    console.log(`📢 Notification: [${type.toUpperCase()}] ${message}`);
    
    const alertConfig = {
        success: { class: 'alert-success', icon: 'fas fa-check-circle', duration: 8000 },
        error: { class: 'alert-danger', icon: 'fas fa-exclamation-triangle', duration: 12000 },
        warning: { class: 'alert-warning', icon: 'fas fa-exclamation-circle', duration: 10000 },
        info: { class: 'alert-info', icon: 'fas fa-info-circle', duration: 6000 }
    }[type] || { class: 'alert-info', icon: 'fas fa-info-circle', duration: 6000 };

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertConfig.class} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = `
        top: 20px; right: 20px; z-index: 9999; max-width: 500px; 
        box-shadow: 0 15px 50px rgba(0,0,0,0.3); border: none;
        backdrop-filter: blur(10px); white-space: pre-line;
    `;
    
    alertDiv.innerHTML = `
        <div class="d-flex align-items-start">
            <i class="${alertConfig.icon} fa-lg me-3 mt-1"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-3" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;

    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.classList.add('show'), 100);
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 300);
    }, alertConfig.duration);
}

function getFinalizationProcessingTime() {
    return window.importStartTime ? Date.now() - window.importStartTime : 
           (window.Phase2Config?.startTime ? Date.now() - window.Phase2Config.startTime : 0);
}

function getFinalizationImportStats() {
    return {
        total_imported: parseInt(document.getElementById('import-count')?.textContent) || 0,
        total_valid: parseInt(document.getElementById('valid-count')?.textContent) || 0,
        total_anomalies: parseInt(document.getElementById('anomaly-count')?.textContent) || 0,
        existants: parseInt(document.querySelector('.stat-card.warning .h4')?.textContent) || 0,
        solution_note: 'Stats calculées sans nouvelles colonnes DB'
    };
}

// ✅ CONFIGURATION GLOBALE TIMEOUT ÉTENDU
(function() {
    'use strict';
    
    console.log('🔧 Configuration timeout étendu pour finalisation...');
    
    // Extension du timeout jQuery si présent
    if (typeof $ !== 'undefined' && $.ajaxSetup) {
        $.ajaxSetup({ timeout: 600000 }); // 10 minutes
        console.log('✅ jQuery timeout configuré à 10 minutes');
    }
    
    // Override global de fetch pour les routes de finalisation
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        if (url.includes('/finalize-') || url.includes('/finalise')) {
            console.log('🎯 Route de finalisation détectée, timeout étendu appliqué');
            
            // Ajouter headers spéciaux
            options.headers = {
                ...options.headers,
                'X-Extended-Timeout': '600',
                'X-Large-Volume-Support': 'true'
            };
        }
        
        return originalFetch(url, options);
    };
    
    console.log('✅ Configuration timeout étendu activée pour gros volumes');
})();

console.log('✅ Système finalisation AJAX v5.1 - TIMEOUT ÉTENDU - PRÊT');
console.log('📊 Upload config:', window.Phase2Config.upload);
console.log('🔧 Process chunk URL:', window.Phase2Config.urls.processChunk);
</script>

<script src="{{ asset('js/unified-config-manager.js') }}"></script>
    <script src="{{ asset('js/unified-csrf-manager.js') }}"></script>
    <script src="{{ asset('js/csrf-manager.js') }}"></script> <!-- Avec détection -->
    <script src="{{ asset('js/workflow-2phases.js') }}"></script>
    <script src="{{ asset('js/chunking-import.js') }}"></script>

<!-- ✅ SCRIPTS EXTERNES APRÈS LA CONFIGURATION -->
<!-- Workflow 2 phases -->
<script src="{{ asset('js/workflow-2phases.js') }}"></script>
<!-- ✅ SYSTÈME DE CHUNKING POUR GROS VOLUMES -->
<script src="{{ asset('js/chunking-import.js') }}"></script>
<!-- ✅ MODULE PHASE 2 EXTERNALISÉ -->
<script src="{{ asset('js/adherents-import-phase2.js') }}"></script>
@endpush