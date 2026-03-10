@extends('layouts.public')

@section('title', $organisation->nom)

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     BANNIÈRE DE VÉRIFICATION (affichée uniquement via /verify/...)
═══════════════════════════════════════════════════════════════ --}}
@if(isset($verificationMode) && $verificationMode)
<div class="verification-banner {{ $verificationResult === 'valid' ? 'banner-success' : 'banner-warning' }}">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            @if($verificationResult === 'valid')
                <i class="fas fa-shield-alt fa-2x text-success"></i>
                <div>
                    <strong class="d-block">Document authentifié</strong>
                    <span>Ce récépissé est valide et a été émis par le PNGDI. L'organisation est officiellement enregistrée.</span>
                </div>
                <span class="ms-auto badge bg-success fs-6 px-3 py-2">
                    <i class="fas fa-check-circle me-1"></i>VALIDE
                </span>
            @else
                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                <div>
                    <strong class="d-block">Organisation suspendue</strong>
                    <span>Le récépissé est authentique, mais l'organisation est actuellement suspendue. Ses activités peuvent être restreintes.</span>
                </div>
                <span class="ms-auto badge bg-warning text-dark fs-6 px-3 py-2">
                    <i class="fas fa-pause-circle me-1"></i>SUSPENDUE
                </span>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Header Section -->
<section class="page-header-detail">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                @php
                    $typeClass = match($organisation->type) {
                        'association'           => 'association',
                        'ong'                   => 'ong',
                        'parti_politique'       => 'parti',
                        'confession_religieuse' => 'confession',
                        default                 => 'autre',
                    };
                @endphp
                <div class="org-type-badge-large {{ $typeClass }}">
                    @switch($organisation->type)
                        @case('association') <i class="fas fa-users me-2"></i>Association @break
                        @case('ong') <i class="fas fa-hands-helping me-2"></i>ONG @break
                        @case('parti_politique') <i class="fas fa-landmark me-2"></i>Parti politique @break
                        @case('confession_religieuse') <i class="fas fa-pray me-2"></i>Confession religieuse @break
                        @default <i class="fas fa-building me-2"></i>{{ $organisation->type_label }} @break
                    @endswitch
                </div>
                <h1 class="page-title mt-3">{{ $organisation->nom }}</h1>
                @if($organisation->sigle)
                <p class="page-subtitle mb-1"><em>({{ $organisation->sigle }})</em></p>
                @endif
                <p class="page-subtitle">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    {{ $organisation->ville_commune ?? $organisation->prefecture }}, {{ $organisation->province }}
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('annuaire.index') }}">Annuaire</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($organisation->nom, 25) }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Colonne principale -->
            <div class="col-lg-8">

                <!-- Présentation -->
                <div class="detail-card mb-4">
                    <h3 class="detail-card-title">
                        <i class="fas fa-info-circle me-2"></i>Présentation
                    </h3>
                    <p class="detail-text">{{ $organisation->objet }}</p>

                    <div class="info-grid mt-4">
                        @if($organisation->organisationType)
                        <div class="info-item">
                            <span class="info-label">Type d'organisation</span>
                            <span class="info-value">{{ $organisation->organisationType->nom }}</span>
                        </div>
                        @endif
                        <div class="info-item">
                            <span class="info-label">Date de création</span>
                            <span class="info-value">
                                {{ $organisation->date_creation?->translatedFormat('d F Y') ?? '—' }}
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Zone</span>
                            <span class="info-value">{{ ucfirst($organisation->zone_type ?? '—') }}</span>
                        </div>
                        @if($organisation->numero_recepisse)
                        <div class="info-item">
                            <span class="info-label">N° de récépissé</span>
                            <span class="info-value recepisse-number">{{ $organisation->numero_recepisse }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Membres du bureau (informations publiques uniquement — pas de NIP) -->
                @if($organisation->membresBureauPourRecepisse && $organisation->membresBureauPourRecepisse->isNotEmpty())
                <div class="detail-card mb-4">
                    <h3 class="detail-card-title">
                        <i class="fas fa-users-cog me-2"></i>Bureau de l'organisation
                    </h3>
                    <div class="bureau-list">
                        @foreach($organisation->membresBureauPourRecepisse as $membre)
                        <div class="bureau-item">
                            <div class="bureau-avatar">
                                {{ strtoupper(substr($membre->prenom ?? $membre->nom, 0, 1)) }}
                            </div>
                            <div class="bureau-info">
                                <span class="bureau-name">{{ $membre->nom }} {{ $membre->prenom }}</span>
                                <span class="bureau-fonction">{{ $membre->fonction }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Coordonnées de l'organisation -->
                <div class="detail-card mb-4">
                    <h3 class="detail-card-title">
                        <i class="fas fa-address-card me-2"></i>Coordonnées
                    </h3>
                    <div class="contact-grid">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span class="contact-label">Siège social</span>
                                <span class="contact-value">{{ $organisation->adresse_complete }}</span>
                            </div>
                        </div>

                        @if($organisation->email)
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <span class="contact-label">Email</span>
                                <a href="mailto:{{ $organisation->email }}" class="contact-value">
                                    {{ $organisation->email }}
                                </a>
                            </div>
                        </div>
                        @endif

                        @if($organisation->telephone)
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <span class="contact-label">Téléphone</span>
                                <a href="tel:{{ $organisation->telephone }}" class="contact-value">
                                    {{ $organisation->telephone }}
                                </a>
                            </div>
                        </div>
                        @endif

                        @if($organisation->site_web)
                        <div class="contact-item">
                            <i class="fas fa-globe"></i>
                            <div>
                                <span class="contact-label">Site web</span>
                                <a href="{{ Str::startsWith($organisation->site_web, 'http') ? $organisation->site_web : 'https://'.$organisation->site_web }}"
                                   target="_blank" rel="noopener noreferrer" class="contact-value">
                                    {{ $organisation->site_web }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">

                <!-- Carte de statut officiel -->
                <div class="status-card mb-4">
                    <div class="status-header {{ $organisation->statut === 'approuve' ? 'approved' : ($organisation->statut === 'suspendu' ? 'suspended' : 'pending') }}">
                        @if($organisation->statut === 'approuve')
                            <i class="fas fa-check-circle me-2"></i>Organisation reconnue
                        @elseif($organisation->statut === 'suspendu')
                            <i class="fas fa-pause-circle me-2"></i>Organisation suspendue
                        @else
                            <i class="fas fa-clock me-2"></i>Récépissé provisoire
                        @endif
                    </div>
                    <div class="status-body">
                        @if($organisation->statut === 'approuve')
                        <p class="mb-2">Cette organisation est officiellement reconnue par le PNGDI.</p>
                        @elseif($organisation->statut === 'suspendu')
                        <p class="mb-2 text-warning">Cette organisation est actuellement suspendue. Ses activités peuvent être restreintes.</p>
                        @else
                        <p class="mb-2">Récépissé provisoire émis. Dossier en cours de traitement.</p>
                        @endif
                        <small class="text-muted">Mise à jour : {{ $organisation->updated_at->format('d/m/Y') }}</small>
                    </div>
                </div>

                <!-- Authentifier le récépissé -->
                @if($organisation->numero_recepisse)
                <div class="verify-widget mb-4">
                    <h5 class="mb-3"><i class="fas fa-qrcode me-2 text-success"></i>Authentifier ce récépissé</h5>
                    <p class="text-muted small mb-3">
                        Scannez le QR code figurant sur le document ou utilisez ce lien direct pour vérifier son authenticité.
                    </p>
                    <a href="{{ route('annuaire.verify', $organisation->numero_recepisse) }}"
                       class="btn btn-success btn-sm w-100 mb-2">
                        <i class="fas fa-shield-alt me-2"></i>Vérifier l'authenticité
                    </a>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm"
                               value="{{ $organisation->numero_recepisse }}" readonly id="recepisse-code">
                        <button class="btn btn-outline-secondary btn-sm" type="button"
                                onclick="navigator.clipboard.writeText('{{ $organisation->numero_recepisse }}'); this.innerHTML='<i class=\'fas fa-check\'></i>'">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted">N° de récépissé</small>
                </div>
                @endif

                <!-- Informations rapides -->
                <div class="quick-stats mb-4">
                    <h5 class="mb-3">Informations</h5>
                    @if($organisation->date_creation)
                    <div class="stat-row">
                        <span class="stat-icon"><i class="fas fa-history"></i></span>
                        <div>
                            <div class="stat-value">{{ $organisation->date_creation->age }} ans</div>
                            <div class="stat-label">d'existence</div>
                        </div>
                    </div>
                    @endif
                    <div class="stat-row">
                        <span class="stat-icon"><i class="fas fa-map-pin"></i></span>
                        <div>
                            <div class="stat-value">{{ $organisation->province }}</div>
                            <div class="stat-label">Province</div>
                        </div>
                    </div>
                    <div class="stat-row">
                        <span class="stat-icon"><i class="fas fa-city"></i></span>
                        <div>
                            <div class="stat-value">{{ $organisation->ville_commune ?? $organisation->prefecture }}</div>
                            <div class="stat-label">Localité</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="action-buttons mb-4">
                    <a href="{{ route('contact') }}" class="btn btn-outline-danger w-100 mb-2">
                        <i class="fas fa-flag me-2"></i>Signaler une information incorrecte
                    </a>
                    <button class="btn btn-outline-secondary w-100" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimer cette fiche
                    </button>
                </div>

            </div>
        </div>

        <!-- Organisations similaires -->
        @if($similaires->isNotEmpty())
        <div class="similar-section mt-5">
            <h3 class="section-title mb-4">
                <i class="fas fa-th me-2"></i>Autres organisations similaires
            </h3>
            <div class="row g-4">
                @foreach($similaires as $sim)
                <div class="col-md-4">
                    <div class="similar-card">
                        <h5 class="similar-name">
                            <a href="{{ route('annuaire.show', $sim->id) }}">{{ $sim->nom }}</a>
                        </h5>
                        <p class="similar-info">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $sim->ville_commune ?? $sim->prefecture }}
                        </p>
                        <p class="similar-desc">{{ Str::limit($sim->objet, 80) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>

<!-- Retour à la liste -->
<section class="py-4 bg-light">
    <div class="container text-center">
        <a href="{{ route('annuaire.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Retour à l'annuaire
        </a>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Bannière vérification */
    .verification-banner {
        padding: 1rem 0;
        font-size: 0.95rem;
    }
    .banner-success { background: #d1fae5; border-bottom: 3px solid #10b981; color: #065f46; }
    .banner-warning { background: #fff3cd; border-bottom: 3px solid #fd7e14; color: #7c4a0a; }

    /* Header */
    .page-header-detail {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
        color: white;
        padding: 4rem 0 3rem;
        position: relative;
        overflow: hidden;
    }
    .page-header-detail::before {
        content: '';
        position: absolute;
        top: -50%; right: -50%;
        width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(255,215,0,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }
    .org-type-badge-large {
        display: inline-block;
        padding: 0.5rem 1.5rem;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
    }

    /* Detail Cards */
    .detail-card { background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
    .detail-card-title {
        color: var(--primary-blue);
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }
    .detail-text { color: #666; line-height: 1.8; font-size: 1.1rem; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; }
    .info-item { display: flex; flex-direction: column; }
    .info-label { font-size: 0.875rem; color: #6c757d; margin-bottom: 0.25rem; }
    .info-value { font-size: 1.1rem; font-weight: 600; color: var(--primary-blue); }
    .recepisse-number { font-family: monospace; font-size: 1rem; letter-spacing: 0.5px; }

    /* Bureau */
    .bureau-list { display: flex; flex-direction: column; gap: 1rem; }
    .bureau-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 10px; }
    .bureau-avatar {
        width: 45px; height: 45px;
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 700; font-size: 1.1rem;
        flex-shrink: 0;
    }
    .bureau-info { display: flex; flex-direction: column; }
    .bureau-name { font-weight: 600; color: #333; }
    .bureau-fonction { font-size: 0.85rem; color: var(--primary-blue); }

    /* Contact */
    .contact-grid { display: grid; gap: 1.5rem; }
    .contact-item { display: flex; gap: 1rem; align-items: start; }
    .contact-item i {
        width: 40px; height: 40px;
        background: #f8f9fa; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: var(--primary-blue); flex-shrink: 0;
    }
    .contact-label { display: block; font-size: 0.875rem; color: #6c757d; margin-bottom: 0.25rem; }
    .contact-value { font-weight: 600; color: #333; text-decoration: none; }
    .contact-value:hover { color: var(--primary-blue); }

    /* Status Card */
    .status-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
    .status-header { padding: 1rem; color: white; font-weight: 600; text-align: center; }
    .status-header.approved { background: #28a745; }
    .status-header.suspended { background: #fd7e14; }
    .status-header.pending { background: #17a2b8; }
    .status-body { padding: 1.5rem; text-align: center; }

    /* Verify Widget */
    .verify-widget {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border-top: 3px solid #10b981;
    }

    /* Quick Stats */
    .quick-stats { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
    .stat-row { display: flex; align-items: center; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid #f0f0f0; }
    .stat-row:last-child { border-bottom: none; }
    .stat-icon {
        width: 50px; height: 50px;
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;
    }
    .stat-value { font-size: 1.25rem; font-weight: 700; color: var(--primary-blue); line-height: 1; }
    .stat-label { font-size: 0.8rem; color: #6c757d; }

    /* Similar */
    .similar-section { padding-top: 3rem; border-top: 2px solid #f0f0f0; }
    .section-title { color: var(--primary-blue); font-size: 1.75rem; }
    .similar-card { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 3px 15px rgba(0,0,0,0.08); height: 100%; transition: all 0.3s; }
    .similar-card:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(0,0,0,0.12); }
    .similar-name { font-size: 1.1rem; margin-bottom: 0.5rem; }
    .similar-name a { color: var(--primary-blue); text-decoration: none; }
    .similar-name a:hover { text-decoration: underline; }
    .similar-info { color: #6c757d; font-size: 0.875rem; margin-bottom: 0.5rem; }
    .similar-desc { color: #666; font-size: 0.9rem; line-height: 1.5; }

    /* Print */
    @media print {
        .page-header-detail, .verification-banner, .action-buttons,
        .verify-widget, .similar-section, nav, .btn { display: none !important; }
        .detail-card { box-shadow: none; border: 1px solid #ddd; break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .info-grid { grid-template-columns: 1fr; }
        .contact-item { flex-direction: column; text-align: center; }
    }
</style>
@endpush
