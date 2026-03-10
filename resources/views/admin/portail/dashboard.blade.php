@extends('layouts.admin')

@section('title', 'Portail Public - Tableau de bord')

@section('content')
<div class="container-fluid py-4">

    {{-- En-tête --}}
    <div class="mb-4">
        <h1 class="h3 mb-1 font-weight-bold" style="color:#0e2f5b;">
            <i class="fas fa-globe mr-2" style="color:#009e3f;"></i>Portail Public — Gestion du contenu
        </h1>
        <p class="text-muted mb-0">Gérez l'ensemble du contenu affiché sur le portail usager.</p>
    </div>

    {{-- Statistiques rapides --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="card shadow-sm border-0 text-center py-3">
                <div class="h2 font-weight-bold mb-0" style="color:#009e3f;">{{ $stats['publiees'] }}</div>
                <div class="text-muted small">Actualités publiées</div>
                <div class="text-muted" style="font-size:0.75rem;">/ {{ $stats['actualites'] }} total</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card shadow-sm border-0 text-center py-3">
                <div class="h2 font-weight-bold mb-0" style="color:#003f7f;">{{ $stats['documents'] }}</div>
                <div class="text-muted small">Documents publics</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card shadow-sm border-0 text-center py-3">
                <div class="h2 font-weight-bold mb-0" style="color:#ffcd00;">{{ $stats['faqs'] }}</div>
                <div class="text-muted small">Questions FAQ</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card shadow-sm border-0 text-center py-3">
                <div class="h2 font-weight-bold mb-0" style="color:{{ $stats['non_lus'] > 0 ? '#dc3545' : '#6c757d' }};">{{ $stats['non_lus'] }}</div>
                <div class="text-muted small">Messages non lus</div>
                <div class="text-muted" style="font-size:0.75rem;">/ {{ $stats['messages'] }} total</div>
            </div>
        </div>
    </div>

    {{-- Modules de gestion --}}
    <div class="row">

        {{-- Actualités --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:48px;height:48px;background:rgba(0,158,63,0.1);">
                            <i class="fas fa-newspaper fa-lg" style="color:#009e3f;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold">Actualités</h5>
                            <small class="text-muted">{{ $stats['actualites'] }} article(s)</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">Publiez et gérez les articles, annonces et actualités affichés sur le portail.</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.portail.actualites.index') }}" class="btn btn-sm btn-outline-success flex-fill">
                            <i class="fas fa-list mr-1"></i>Voir tout
                        </a>
                        <a href="{{ route('admin.portail.actualites.create') }}" class="btn btn-sm btn-success flex-fill">
                            <i class="fas fa-plus mr-1"></i>Nouveau
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:48px;height:48px;background:rgba(0,63,127,0.1);">
                            <i class="fas fa-file-download fa-lg" style="color:#003f7f;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold">Documents publics</h5>
                            <small class="text-muted">{{ $stats['documents'] }} document(s)</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">Formulaires, guides, modèles et textes réglementaires téléchargeables.</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.portail.documents.index') }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="fas fa-list mr-1"></i>Voir tout
                        </a>
                        <a href="{{ route('admin.portail.documents.create') }}" class="btn btn-sm btn-primary flex-fill">
                            <i class="fas fa-plus mr-1"></i>Nouveau
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- FAQ --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:48px;height:48px;background:rgba(255,205,0,0.15);">
                            <i class="fas fa-question-circle fa-lg" style="color:#856404;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold">FAQ</h5>
                            <small class="text-muted">{{ $stats['faqs'] }} question(s)</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">Gérez les questions fréquentes et leurs réponses par catégorie.</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.portail.faqs.index') }}" class="btn btn-sm btn-outline-warning flex-fill">
                            <i class="fas fa-list mr-1"></i>Voir tout
                        </a>
                        <a href="{{ route('admin.portail.faqs.create') }}" class="btn btn-sm btn-warning flex-fill">
                            <i class="fas fa-plus mr-1"></i>Nouveau
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Guides --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:48px;height:48px;background:rgba(108,117,125,0.1);">
                            <i class="fas fa-book fa-lg text-secondary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold">Guides</h5>
                            <small class="text-muted">{{ $stats['guides'] }} guide(s)</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">Guides pratiques PDF à télécharger pour accompagner les démarches.</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.portail.guides.index') }}" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="fas fa-list mr-1"></i>Voir tout
                        </a>
                        <a href="{{ route('admin.portail.guides.create') }}" class="btn btn-sm btn-secondary flex-fill">
                            <i class="fas fa-plus mr-1"></i>Nouveau
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Événements --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:48px;height:48px;background:rgba(23,162,184,0.1);">
                            <i class="fas fa-calendar-alt fa-lg text-info"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold">Événements</h5>
                            <small class="text-muted">{{ $stats['evenements'] }} événement(s)</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">Calendrier des échéances, formations et événements à venir.</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.portail.evenements.index') }}" class="btn btn-sm btn-outline-info flex-fill">
                            <i class="fas fa-list mr-1"></i>Voir tout
                        </a>
                        <a href="{{ route('admin.portail.evenements.create') }}" class="btn btn-sm btn-info flex-fill">
                            <i class="fas fa-plus mr-1"></i>Nouveau
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100" style="{{ $stats['non_lus'] > 0 ? 'border-left: 4px solid #dc3545 !important;' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:48px;height:48px;background:rgba(220,53,69,0.1);">
                            <i class="fas fa-envelope fa-lg text-danger"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold">
                                Messages contact
                                @if($stats['non_lus'] > 0)
                                    <span class="badge badge-danger ml-1">{{ $stats['non_lus'] }}</span>
                                @endif
                            </h5>
                            <small class="text-muted">{{ $stats['messages'] }} message(s) reçu(s)</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">Consultez et répondez aux messages envoyés via le formulaire de contact.</p>
                    <a href="{{ route('admin.portail.messages.index') }}" class="btn btn-sm btn-danger btn-block">
                        <i class="fas fa-envelope-open mr-1"></i>Voir les messages
                    </a>
                </div>
            </div>
        </div>

        {{-- Paramètres --}}
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:48px;height:48px;background:rgba(0,63,127,0.1);">
                            <i class="fas fa-sliders-h fa-lg" style="color:#003f7f;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold">Paramètres du portail</h5>
                            <small class="text-muted">Textes de la page d'accueil, section À propos, coordonnées de contact, pied de page...</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.portail.parametres.index') }}" class="btn btn-outline-primary ml-3" style="white-space:nowrap;">
                        <i class="fas fa-cog mr-1"></i>Configurer le portail
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- Derniers messages reçus --}}
    @if($derniers_messages->isNotEmpty())
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center" style="background:#f8f9fa;">
            <strong><i class="fas fa-inbox mr-2 text-danger"></i>Derniers messages reçus</strong>
            <a href="{{ route('admin.portail.messages.index') }}" class="btn btn-sm btn-outline-secondary">Voir tout</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>Expéditeur</th><th>Sujet</th><th>Statut</th><th>Reçu le</th><th></th>
                    </tr></thead>
                    <tbody>
                        @foreach($derniers_messages as $msg)
                        @php
                            $colors = ['non_lu'=>'danger','lu'=>'primary','traite'=>'success','archive'=>'secondary'];
                            $labels = ['non_lu'=>'Non lu','lu'=>'Lu','traite'=>'Traité','archive'=>'Archivé'];
                        @endphp
                        <tr class="{{ $msg->statut === 'non_lu' ? 'font-weight-bold' : '' }}">
                            <td>{{ $msg->nom }}<br><small class="text-muted">{{ $msg->email }}</small></td>
                            <td>{{ Str::limit($msg->sujet, 60) }}</td>
                            <td><span class="badge badge-{{ $colors[$msg->statut] ?? 'secondary' }}">{{ $labels[$msg->statut] ?? $msg->statut }}</span></td>
                            <td>{{ $msg->created_at->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('admin.portail.messages.show', $msg) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
