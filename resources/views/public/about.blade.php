@extends('layouts.public')

@section('title', 'À propos')

@section('content')
<!-- Header Section -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title">Qui sommes-nous ?</h1>
                <p class="page-subtitle">
                    Direction Générale des Élections et des Libertés Publiques
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">À propos</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Présentation DGELP -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="about-image-wrapper">
                    <img src="{{ asset('images/slides/004.png') }}" alt="DGELP" class="img-fluid rounded-3 shadow">
                    <div class="experience-badge">
                        <span class="number">DGELP</span>
                        <span class="text">Dieudonné YAYA</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <span class="section-badge">LA DGELP</span>
                <h2 class="display-5 fw-bold text-primary mt-3 mb-4">
                    Qui sommes-nous ?
                </h2>
                <p class="lead text-muted mb-4">
                    Placée sous l'autorité du Ministère de l'Intérieur, de la Sécurité et de la Décentralisation,
                    la Direction Générale des Elections et des Libertés Publiques (DGELP) est l'organe central chargé de la mise en oeuvre de la politique du Gouvernement
                    en matière d'élections et de libertés publiques.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Notre Vision -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="section-badge">NOTRE VISION</span>
                <h2 class="display-5 fw-bold text-primary mt-3 mb-4">
                    La Modernisation au service du Citoyen
                </h2>
                <p class="lead text-muted mb-3">
                    Conformément à la vision de S.E. Brice Clotaire OLIGUI NGUEMA, Président de la République,
                    Chef de l'Etat, Chef du Gouvernement, la DGELP s'est engagée dans une transformation profonde
                    de ses méthodes de travail.
                </p>
                <p class="text-muted">
                    La digitalisation de nos services n'est pas seulement une évolution technique, c'est un pilier
                    de la Restauration des Institutions visant à garantir la <strong>transparence</strong>,
                    l'<strong>équité</strong> et la <strong>proximité</strong> avec chaque acteur de la société civile.
                </p>
            </div>
            <div class="col-lg-5">
                <div class="value-card p-4 text-center">
                    <div class="value-icon mx-auto mb-3">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h4 class="text-primary">Transparence</h4>
                    <p class="mb-3">Équité et proximité avec chaque acteur de la société civile</p>
                    <div class="value-icon mx-auto mb-3">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <h4 class="text-primary">Digitalisation</h4>
                    <p class="mb-0">Transformation profonde pour une administration modernisée</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nos Missions Régaliennes -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">NOS MISSIONS</span>
            <h2 class="display-5 fw-bold text-primary mt-3">
                Nos Missions Régaliennes
            </h2>
            <p class="lead text-muted mt-3">
                En s'appuyant sur le cadre légal, la DGELP assure des missions essentielles
                pour la vie démocratique du Gabon
            </p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="value-card h-100">
                    <div class="value-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h4>Gestion des Libertés Publiques</h4>
                    <p>Instruction des dossiers de déclaration des partis politiques, des associations civiles et religieuses, ainsi que des syndicats professionnels.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card h-100">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Encadrement de la Vie Associative</h4>
                    <p>Suivi du fonctionnement des organisations et instruction des demandes de reconnaissance d'utilité publique.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card h-100">
                    <div class="value-icon">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h4>Processus Électoraux</h4>
                    <p>Établissement de la liste nationale électorale, distribution des cartes d'électeurs et gestion du matériel électoral.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card h-100">
                    <div class="value-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4>Éducation Citoyenne</h4>
                    <p>Information et sensibilisation des citoyens sur la réglementation en matière de libertés publiques et d'élections.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Notre Organisation -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">NOTRE ORGANISATION</span>
            <h2 class="display-5 fw-bold text-primary mt-3">
                Une structure au service du territoire national
            </h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="value-card h-100">
                    <div class="value-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h4>Services Centraux</h4>
                    <ul class="list-unstyled text-start mt-3">
                        <li class="mb-2">
                            <i class="fas fa-chevron-right text-primary mr-2"></i>
                            Direction des Élections
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chevron-right text-primary mr-2"></i>
                            Direction des Partis Politiques, des Associations et des Libertés de Culte
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chevron-right text-primary mr-2"></i>
                            Direction de la Formation et Action Citoyenne
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="value-card h-100">
                    <div class="value-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <h4>Support Technique Moderne</h4>
                    <p>Un service dédié aux Systèmes d'Information garantissant la sécurité des données et la maintenance de nos plateformes digitales.</p>
                    <div class="mission-points mt-3">
                        <div class="mission-item">
                            <div class="icon-box"><i class="fas fa-shield-alt"></i></div>
                            <div><p class="mb-0">Sécurité des données</p></div>
                        </div>
                        <div class="mission-item">
                            <div class="icon-box"><i class="fas fa-tools"></i></div>
                            <div><p class="mb-0">Maintenance des plateformes</p></div>
                        </div>
                        <div class="mission-item">
                            <div class="icon-box"><i class="fas fa-cloud"></i></div>
                            <div><p class="mb-0">Infrastructure digitale</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Notre Engagement -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-12">
                <span class="section-badge">NOTRE ENGAGEMENT</span>
                <h2 class="display-5 fw-bold text-primary mt-3 mb-4">
                    Transparence et Sécurité
                </h2>
                <p class="lead text-muted mb-3">
                    La plateforme DGELP-Services en ligne incarne notre engagement pour une administration de résultats.
                </p>
                <p class="text-muted mb-4">
                    Grâce à l'archivage numérique et à la centralisation des données, nous offrons aux usagers
                    un accès simplifié à leurs dossiers et une traçabilité totale des procédures administratives.
                </p>
                <div class="mission-points">
                    <div class="mission-item">
                        <div class="icon-box"><i class="fas fa-archive"></i></div>
                        <div>
                            <h5>Archivage numérique</h5>
                            <p>Conservation sécurisée de tous les documents</p>
                        </div>
                    </div>
                    <div class="mission-item">
                        <div class="icon-box"><i class="fas fa-database"></i></div>
                        <div>
                            <h5>Centralisation des données</h5>
                            <p>Accès simplifié aux dossiers des usagers</p>
                        </div>
                    </div>
                    <div class="mission-item">
                        <div class="icon-box"><i class="fas fa-search"></i></div>
                        <div>
                            <h5>Traçabilité totale</h5>
                            <p>Suivi transparent de chaque procédure administrative</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Vous avez des questions ?</h2>
        <p class="lead mb-4">
            Notre équipe est là pour vous accompagner dans toutes vos démarches
        </p>
        <a href="{{ route('contact') }}" class="btn btn-warning btn-lg">
            <i class="fas fa-envelope me-2"></i>Contactez-nous
        </a>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
        color: white;
        padding: 4rem 0 3rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,215,0,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    /* Section Badge */
    .section-badge {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 1px;
        display: inline-block;
    }

    /* About Image */
    .about-image-wrapper {
        position: relative;
    }

    .experience-badge {
        position: absolute;
        bottom: -20px;
        right: -20px;
        background: var(--secondary-gold);
        color: var(--primary-blue);
        padding: 2rem;
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .experience-badge .number {
        display: block;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .experience-badge .text {
        display: block;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    /* Mission Items */
    .mission-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .icon-box {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    /* Value Cards */
    .value-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .value-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .value-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, rgba(255,215,0,0.2), rgba(0,43,127,0.1));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--primary-blue);
    }

    /* Team Cards */
    .team-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .team-card:hover {
        border-color: var(--primary-blue);
        transform: translateY(-5px);
    }

    .team-avatar {
        width: 100px;
        height: 100px;
        margin: 0 auto 1rem;
        background: var(--primary-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: white;
    }
</style>
@endpush