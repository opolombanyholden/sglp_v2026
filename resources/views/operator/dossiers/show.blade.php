@extends('layouts.operator')

@section('title', 'Dossier - ' . ($dossier->numero_dossier ?? 'N/A'))

@section('page-title', 'Détail du Dossier')

@section('content')
<div class="container-fluid">
    @php
        $organisation = $dossier->organisation;
        $statusIcons = [
            'brouillon' => ['icon' => 'edit', 'bg' => 'secondary'],
            'soumis' => ['icon' => 'clock', 'bg' => 'warning'],
            'en_cours' => ['icon' => 'cogs', 'bg' => 'info'],
            'approuve' => ['icon' => 'check', 'bg' => 'success'],
            'rejete' => ['icon' => 'times', 'bg' => 'danger']
        ];
        $statusConfig = $statusIcons[$dossier->statut] ?? ['icon' => 'question', 'bg' => 'secondary'];
    @endphp

    {{-- Header du dossier --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background-color: #009e3f;">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-2">
                                    <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}" class="text-white" style="opacity:0.75;">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('operator.dossiers.index') }}" class="text-white" style="opacity:0.75;">Dossiers</a></li>
                                    <li class="breadcrumb-item active text-white">{{ $dossier->numero_dossier ?? 'N/A' }}</li>
                                </ol>
                            </nav>
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $statusConfig['bg'] }}" style="width:50px;height:50px;">
                                        <i class="fas fa-{{ $statusConfig['icon'] }} text-white fa-lg"></i>
                                    </span>
                                </div>
                                <div>
                                    <h2 class="mb-1">{{ $dossier->numero_dossier }}</h2>
                                    <h4 class="mb-0" style="opacity:0.9;">
                                        {{ $organisation->nom ?? 'Organisation non définie' }}
                                        @if($organisation && $organisation->sigle)
                                            <span style="opacity:0.75;">({{ $organisation->sigle }})</span>
                                        @endif
                                    </h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <small style="opacity:0.75;">Type d'opération</small>
                                    <div class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $dossier->type_operation ?? 'N/A')) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small style="opacity:0.75;">Date de création</small>
                                    <div class="font-weight-bold">{{ \Carbon\Carbon::parse($dossier->created_at)->format('d/m/Y à H:i') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small style="opacity:0.75;">Délai d'attente</small>
                                    <div class="font-weight-bold">
                                        {{ $stats['delai_attente'] }} jour{{ $stats['delai_attente'] > 1 ? 's' : '' }}
                                        @if($stats['delai_attente'] > 7)
                                            <i class="fas fa-exclamation-triangle text-warning ml-1"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-light px-3 py-2" style="font-size: 1rem;">
                                {{ $dossier->statut_label ?? ucfirst($dossier->statut ?? 'N/A') }}
                            </span>
                            <div class="mt-3">
                                <a href="{{ route('operator.dossiers.index') }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left mr-1"></i>Retour aux dossiers
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Colonne principale --}}
        <div class="col-lg-8">
            {{-- Informations de l'organisation --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-building mr-2 text-primary"></i>Informations de l'organisation</h5>
                </div>
                <div class="card-body">
                    @if($organisation)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Dénomination</label>
                                    <div class="font-weight-bold">{{ $organisation->nom }}</div>
                                </div>
                                @if($organisation->sigle)
                                <div class="mb-3">
                                    <label class="text-muted small">Sigle</label>
                                    <div class="font-weight-bold">{{ $organisation->sigle }}</div>
                                </div>
                                @endif
                                <div class="mb-3">
                                    <label class="text-muted small">Type d'organisation</label>
                                    <div class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $organisation->type ?? 'N/A')) }}</div>
                                </div>
                                @if($organisation->numero_recepisse)
                                <div class="mb-3">
                                    <label class="text-muted small">Numéro de récépissé</label>
                                    <div class="font-weight-bold"><code>{{ $organisation->numero_recepisse }}</code></div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Localisation</label>
                                    <div class="font-weight-bold">
                                        {{ $organisation->provinceRef->nom ?? $organisation->province ?? 'Non renseigné' }}
                                        @if($organisation->departement || $organisation->departementRef)
                                            <br><small class="text-muted">{{ $organisation->departementRef->nom ?? $organisation->departement }}</small>
                                        @endif
                                        @if($organisation->ville_commune || $organisation->communeVilleRef)
                                            <br><small class="text-muted">{{ $organisation->communeVilleRef->nom ?? $organisation->ville_commune }}</small>
                                        @endif
                                    </div>
                                </div>
                                @if($organisation->siege_social)
                                <div class="mb-3">
                                    <label class="text-muted small">Siège social</label>
                                    <div class="font-weight-bold">{{ $organisation->siege_social }}</div>
                                </div>
                                @endif
                                @if($organisation->email)
                                <div class="mb-3">
                                    <label class="text-muted small">Email</label>
                                    <div class="font-weight-bold">{{ $organisation->email }}</div>
                                </div>
                                @endif
                                @if($organisation->telephone)
                                <div class="mb-3">
                                    <label class="text-muted small">Téléphone</label>
                                    <div class="font-weight-bold">{{ $organisation->telephone }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @if($organisation->objet)
                            <hr>
                            <div class="mb-0">
                                <label class="text-muted small">Objet social</label>
                                <div class="font-weight-bold">{{ $organisation->objet }}</div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Aucune information d'organisation disponible.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Membres Fondateurs --}}
            @if($organisation && $organisation->fondateurs && $organisation->fondateurs->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header text-white" style="background-color: #009e3f;">
                    <h5 class="mb-0">
                        <i class="fas fa-users mr-2"></i>Membres Fondateurs
                        <span class="badge badge-light ml-2">{{ $organisation->fondateurs->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>NIP</th>
                                    <th>Civilité</th>
                                    <th>Nom & Prénom</th>
                                    <th>Fonction</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($organisation->fondateurs as $index => $fondateur)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge badge-secondary">{{ $fondateur->nip ?? 'N/A' }}</span></td>
                                    <td>{{ $fondateur->civilite ?? '-' }}</td>
                                    <td><strong>{{ $fondateur->nom ?? '' }}</strong> {{ $fondateur->prenom ?? '' }}</td>
                                    <td>
                                        @if($fondateur->fonction)
                                            <span class="badge badge-secondary">{{ is_object($fondateur->fonction) ? ($fondateur->fonction->nom ?? $fondateur->fonction) : $fondateur->fonction }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($fondateur->telephone)
                                            <i class="fas fa-phone text-success mr-1"></i>{{ $fondateur->telephone }}
                                        @endif
                                        @if($fondateur->email)
                                            <br><i class="fas fa-envelope text-primary mr-1"></i>{{ $fondateur->email }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Adhérents --}}
            @if($organisation && $organisation->adherents && $organisation->adherents->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #009e3f;">
                    <h5 class="mb-0">
                        <i class="fas fa-user-friends mr-2"></i>Adhérents
                        <span class="badge badge-light ml-2">{{ $organisation->adherents->count() }}</span>
                    </h5>
                    <a href="{{ route('operator.adherents.index', $organisation) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-external-link-alt mr-1"></i>Voir tous
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>NIP</th>
                                    <th>Nom & Prénom</th>
                                    <th>Profession</th>
                                    <th>Date adhésion</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($organisation->adherents as $index => $adherent)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge badge-secondary">{{ $adherent->nip ?? 'N/A' }}</span></td>
                                    <td><strong>{{ $adherent->nom ?? '' }}</strong> {{ $adherent->prenom ?? '' }}</td>
                                    <td>{{ $adherent->profession ?? '-' }}</td>
                                    <td>
                                        @if($adherent->date_adhesion)
                                            {{ \Carbon\Carbon::parse($adherent->date_adhesion)->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($adherent->telephone)
                                            <i class="fas fa-phone text-success mr-1"></i>{{ $adherent->telephone }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Documents --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open mr-2 text-primary"></i>Pièces jointes au dossier
                        @if($dossier->documents)
                            <span class="badge badge-primary ml-2">{{ $dossier->documents->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($dossier->documents && $dossier->documents->count() > 0)
                        <div class="row">
                            @foreach($dossier->documents as $document)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body text-center">
                                        @php
                                            $extension = pathinfo($document->nom_fichier ?? $document->chemin_fichier ?? '', PATHINFO_EXTENSION);
                                            $iconClass = match (strtolower($extension)) {
                                                'pdf' => 'fa-file-pdf text-danger',
                                                'doc', 'docx' => 'fa-file-word text-primary',
                                                'xls', 'xlsx' => 'fa-file-excel text-success',
                                                'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image text-secondary',
                                                default => 'fa-file text-secondary'
                                            };
                                        @endphp
                                        <i class="fas {{ $iconClass }} fa-3x mb-3"></i>
                                        <h6 class="card-title mb-1">{{ $document->documentType->nom ?? 'Document' }}</h6>
                                        <p class="card-text small text-muted mb-2">
                                            {{ Str::limit($document->nom_fichier ?? $document->nom_original ?? 'Fichier', 25) }}
                                        </p>
                                        <div class="small text-muted mb-2">
                                            @if($document->taille)
                                                <span class="mr-2"><i class="fas fa-weight mr-1"></i>{{ number_format($document->taille / 1024, 1) }} Ko</span>
                                            @endif
                                            @if($document->created_at)
                                                <span><i class="fas fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                        @if($document->is_validated)
                                            <span class="badge badge-success mb-2"><i class="fas fa-check mr-1"></i>Validé</span>
                                        @else
                                            <span class="badge badge-secondary mb-2"><i class="fas fa-clock mr-1"></i>En attente</span>
                                        @endif
                                    </div>
                                    @if($document->chemin_fichier)
                                    <div class="card-footer bg-light">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ Storage::url($document->chemin_fichier) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                            <a href="{{ Storage::url($document->chemin_fichier) }}" download="{{ $document->nom_fichier ?? $document->nom_original }}" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Aucun document joint à ce dossier.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Historique --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history mr-2 text-primary"></i>Historique</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        {{-- Création --}}
                        <div class="d-flex mb-3">
                            <div class="mr-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary" style="width:35px;height:35px;">
                                    <i class="fas fa-plus text-white small"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">Dossier créé</h6>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($dossier->created_at)->format('d/m/Y à H:i') }}
                                </small>
                                <p class="mb-0 small text-muted">Le dossier a été créé et soumis pour traitement.</p>
                            </div>
                        </div>

                        {{-- Validations --}}
                        @if($dossier->validations && $dossier->validations->count() > 0)
                            @foreach($dossier->validations->sortBy('created_at') as $validation)
                            <div class="d-flex mb-3">
                                <div class="mr-3">
                                    @php
                                        $vBg = ($validation->decision ?? '') === 'approuve' ? 'success' : (($validation->decision ?? '') === 'rejete' ? 'danger' : 'warning');
                                        $vIcon = ($validation->decision ?? '') === 'approuve' ? 'check' : (($validation->decision ?? '') === 'rejete' ? 'times' : 'clock');
                                    @endphp
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $vBg }}" style="width:35px;height:35px;">
                                        <i class="fas fa-{{ $vIcon }} text-white small"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-1">
                                        @if(($validation->decision ?? '') === 'approuve')
                                            Validation approuvée
                                        @elseif(($validation->decision ?? '') === 'rejete')
                                            Validation rejetée
                                        @else
                                            Validation en cours
                                        @endif
                                    </h6>
                                    <small class="text-muted">
                                        {{ $validation->validated_at ? \Carbon\Carbon::parse($validation->validated_at)->format('d/m/Y à H:i') : \Carbon\Carbon::parse($validation->created_at)->format('d/m/Y à H:i') }}
                                        @if($validation->validatedBy)
                                            par {{ $validation->validatedBy->name }}
                                        @endif
                                    </small>
                                    @if($validation->commentaire)
                                        <p class="mb-0 small mt-1">{{ $validation->commentaire }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endif

                        {{-- Opérations/commentaires --}}
                        @if($dossier->operations && $dossier->operations->where('type_operation', 'commentaire')->count() > 0)
                            @foreach($dossier->operations->where('type_operation', 'commentaire')->sortBy('created_at') as $operation)
                            <div class="d-flex mb-3">
                                <div class="mr-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary" style="width:35px;height:35px;">
                                        <i class="fas fa-comment text-white small"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Commentaire</h6>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($operation->created_at)->format('d/m/Y à H:i') }}
                                        @if($operation->user)
                                            par {{ $operation->user->name }}
                                        @endif
                                    </small>
                                    @if($operation->contenu)
                                        <p class="mb-0 small mt-1">{{ $operation->contenu }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>

                    @if((!$dossier->validations || $dossier->validations->count() === 0) && (!$dossier->operations || $dossier->operations->where('type_operation', 'commentaire')->count() === 0))
                        <p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i>Aucune action enregistrée pour le moment.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Colonne latérale --}}
        <div class="col-lg-4">
            {{-- Statut du dossier --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle mr-2 text-primary"></i>Statut du Dossier</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $statusConfig['bg'] }}" style="width:70px;height:70px;">
                            <i class="fas fa-{{ $statusConfig['icon'] }} text-white fa-2x"></i>
                        </span>
                    </div>
                    <h5 class="mb-3">{{ $dossier->statut_label ?? ucfirst($dossier->statut ?? 'N/A') }}</h5>

                    @if($dossier->statut === 'rejete' && $dossier->motif_rejet)
                        <div class="alert alert-danger text-left">
                            <strong><i class="fas fa-exclamation-circle mr-1"></i>Motif de rejet :</strong>
                            <p class="mb-0 mt-1">{{ $dossier->motif_rejet }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Résumé --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar mr-2 text-primary"></i>Résumé</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <small class="text-muted d-block">Numéro de dossier</small>
                            <strong>{{ $dossier->numero_dossier ?? 'N/A' }}</strong>
                        </li>
                        <li class="mb-3">
                            <small class="text-muted d-block">Date de soumission</small>
                            <strong>{{ $dossier->submitted_at ? \Carbon\Carbon::parse($dossier->submitted_at)->format('d/m/Y H:i') : ($dossier->date_soumission ? \Carbon\Carbon::parse($dossier->date_soumission)->format('d/m/Y H:i') : 'Non soumis') }}</strong>
                        </li>
                        @if($dossier->validated_at || $dossier->date_traitement)
                        <li class="mb-3">
                            <small class="text-muted d-block">Date de traitement</small>
                            <strong>{{ \Carbon\Carbon::parse($dossier->validated_at ?? $dossier->date_traitement)->format('d/m/Y H:i') }}</strong>
                        </li>
                        @endif
                        <li class="mb-3">
                            <small class="text-muted d-block">Documents</small>
                            <strong>{{ $stats['documents_count'] }}</strong>
                        </li>
                        <li class="mb-3">
                            <small class="text-muted d-block">Fondateurs</small>
                            <strong>{{ $organisation ? $organisation->fondateurs->count() : 0 }}</strong>
                        </li>
                        <li class="mb-3">
                            <small class="text-muted d-block">Adhérents</small>
                            <strong>{{ $organisation ? $organisation->adherents->count() : 0 }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Actions sur le dossier --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-cogs mr-2 text-secondary"></i>Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('operator.dossiers.consulter-anomalies', $dossier->id) }}" class="btn btn-outline-warning btn-block btn-sm mb-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Consulter le rapport d'anomalies
                    </a>
                    <a href="{{ route('operator.dossiers.rapport-anomalies', $dossier->id) }}" class="btn btn-outline-danger btn-block btn-sm mb-2" target="_blank">
                        <i class="fas fa-file-pdf mr-1"></i>Télécharger le rapport PDF
                    </a>
                    @if($dossier->statut === 'brouillon')
                    <a href="{{ route('operator.dossiers.edit', $dossier->id) }}" class="btn btn-outline-primary btn-block btn-sm mb-2">
                        <i class="fas fa-edit mr-1"></i>Modifier le dossier
                    </a>
                    @endif
                </div>
            </div>

            {{-- Carte lien d'inscription publique --}}
            @if($organisation)
                @include('operator.partials.inscription-link-card', ['organisation' => $organisation])
            @endif
        </div>
    </div>
</div>
@endsection
