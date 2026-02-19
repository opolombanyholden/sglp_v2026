@extends('layouts.operator')

@section('title', ($adherent->nom ?? '') . ' ' . ($adherent->prenom ?? '') . ' - Adhérent')

@section('page-title', 'Détail adhérent')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background-color: #009e3f;">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-2">
                                    <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}" class="text-white" style="opacity:.75">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('operator.adherents.index', $organisation) }}" class="text-white" style="opacity:.75">Adhérents</a></li>
                                    <li class="breadcrumb-item active text-white">{{ $adherent->nom }} {{ $adherent->prenom }}</li>
                                </ol>
                            </nav>
                            <h2 class="mb-1"><i class="fas fa-user mr-2"></i>{{ $adherent->nom }} {{ $adherent->prenom }}</h2>
                            <p class="mb-0" style="opacity:.9">
                                {{ $organisation->nom }}
                                @if($adherent->is_fondateur) <span class="badge badge-warning ml-2"><i class="fas fa-star mr-1"></i>Fondateur</span> @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('operator.adherents.edit', [$organisation, $adherent]) }}" class="btn btn-light btn-sm mb-1">
                                <i class="fas fa-edit mr-1"></i>Modifier
                            </a>
                            <a href="{{ route('operator.adherents.index', $organisation) }}" class="btn btn-outline-light btn-sm mb-1">
                                <i class="fas fa-arrow-left mr-1"></i>Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Identification --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-id-card mr-2 text-primary"></i>Identification</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">NIP</p>
                            <p class="font-weight-bold"><code>{{ $adherent->nip ?: 'Non renseigné' }}</code></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Nom</p>
                            <p class="font-weight-bold">{{ $adherent->nom }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Prénom</p>
                            <p class="font-weight-bold">{{ $adherent->prenom }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Date de naissance</p>
                            <p>{{ $adherent->date_naissance ? $adherent->date_naissance->format('d/m/Y') : 'Non renseignée' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Lieu de naissance</p>
                            <p>{{ $adherent->lieu_naissance ?: 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Sexe</p>
                            <p>{{ $adherent->sexe === 'M' ? 'Masculin' : ($adherent->sexe === 'F' ? 'Féminin' : 'Non renseigné') }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Nationalité</p>
                            <p>{{ $adherent->nationalite ?: 'Non renseignée' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-phone mr-2 text-success"></i>Contact</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Téléphone</p>
                            <p>{{ $adherent->telephone ?: 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Email</p>
                            <p>{{ $adherent->email ?: 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Adresse</p>
                            <p>{{ $adherent->adresse_complete ?: 'Non renseignée' }}</p>
                        </div>
                    </div>
                    @if($adherent->province || $adherent->departement)
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Province</p>
                            <p>{{ $adherent->province ?: '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Département</p>
                            <p>{{ $adherent->departement ?: '-' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Professionnel --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-briefcase mr-2 text-warning"></i>Informations professionnelles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Profession</p>
                            <p>{{ $adherent->profession ?: 'Non renseignée' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Fonction</p>
                            <p class="font-weight-bold">{{ $adherent->fonction ?: 'Membre' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Date d'adhésion</p>
                            <p>{{ $adherent->date_adhesion ? $adherent->date_adhesion->format('d/m/Y') : 'Non renseignée' }}</p>
                        </div>
                    </div>
                    @if($adherent->motif_adhesion)
                    <div class="row">
                        <div class="col-12">
                            <p class="mb-1 text-muted small">Motif d'adhésion</p>
                            <p>{{ $adherent->motif_adhesion }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Anomalies --}}
            @if($adherent->has_anomalies && $adherent->anomalies_data)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle mr-2 text-danger"></i>Anomalies détectées</h5>
                    <span class="badge badge-{{ $adherent->anomalies_severity === 'critique' ? 'danger' : ($adherent->anomalies_severity === 'majeure' ? 'warning' : 'info') }}">
                        {{ ucfirst($adherent->anomalies_severity) }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Action requise</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adherent->anomalies_data as $anomalie)
                                <tr>
                                    <td>
                                        @if($anomalie['type'] === 'critique')
                                            <span class="badge badge-danger">Critique</span>
                                        @elseif($anomalie['type'] === 'majeure')
                                            <span class="badge badge-warning">Majeure</span>
                                        @else
                                            <span class="badge badge-info">Mineure</span>
                                        @endif
                                    </td>
                                    <td>{{ $anomalie['message'] ?? '-' }}</td>
                                    <td><small class="text-muted">{{ $anomalie['action_requise'] ?? '-' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Historique --}}
            @if($adherent->historique && isset($adherent->historique['events']) && count($adherent->historique['events']) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history mr-2 text-secondary"></i>Historique</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_reverse($adherent->historique['events']) as $event)
                                <tr>
                                    <td><small>{{ isset($event['date']) ? \Carbon\Carbon::parse($event['date'])->format('d/m/Y H:i') : '-' }}</small></td>
                                    <td><span class="badge badge-secondary">{{ $event['type'] ?? '-' }}</span></td>
                                    <td><small class="text-muted">{{ is_string($event['data'] ?? null) ? $event['data'] : json_encode($event['data'] ?? '') }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Statut --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle mr-2 text-primary"></i>Statut</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($adherent->is_active)
                            <span class="badge badge-success p-2" style="font-size: 1rem;"><i class="fas fa-check-circle mr-1"></i>Actif</span>
                        @else
                            <span class="badge badge-danger p-2" style="font-size: 1rem;"><i class="fas fa-times-circle mr-1"></i>Inactif</span>
                        @endif
                    </div>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <i class="fas fa-calendar mr-2 text-muted"></i>
                            <strong>Adhésion :</strong> {{ $adherent->date_adhesion ? $adherent->date_adhesion->format('d/m/Y') : 'N/A' }}
                        </li>
                        @if($adherent->is_fondateur)
                        <li class="mb-2">
                            <i class="fas fa-star mr-2 text-warning"></i>
                            <strong>Membre fondateur</strong>
                        </li>
                        @endif
                        @if($adherent->source_inscription)
                        <li class="mb-2">
                            <i class="fas fa-door-open mr-2 text-muted"></i>
                            <strong>Source :</strong> {{ $adherent->source_inscription === 'auto_inscription' ? 'Auto-inscription' : 'Saisie manuelle' }}
                        </li>
                        @endif
                        @if(!$adherent->is_active && $adherent->date_exclusion)
                        <li class="mb-2">
                            <i class="fas fa-ban mr-2 text-danger"></i>
                            <strong>Exclusion :</strong> {{ $adherent->date_exclusion->format('d/m/Y') }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-comment mr-2 text-muted"></i>
                            <strong>Motif :</strong> {{ $adherent->motif_exclusion ?? 'Non précisé' }}
                        </li>
                        @endif
                        <li>
                            <i class="fas fa-clock mr-2 text-muted"></i>
                            <strong>Créé le :</strong> {{ $adherent->created_at ? $adherent->created_at->format('d/m/Y H:i') : 'N/A' }}
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Documents --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-file mr-2 text-info"></i>Documents</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="small text-muted mb-1">Photo</p>
                        @if($adherent->photo)
                            <img src="{{ asset('storage/' . $adherent->photo) }}" alt="Photo" class="img-thumbnail" style="max-width: 120px;">
                        @else
                            <span class="text-muted small"><i class="fas fa-image mr-1"></i>Aucune photo</span>
                        @endif
                    </div>
                    <div>
                        <p class="small text-muted mb-1">Pièce d'identité</p>
                        @if($adherent->piece_identite)
                            <a href="{{ asset('storage/' . $adherent->piece_identite) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download mr-1"></i>Télécharger
                            </a>
                        @else
                            <span class="text-muted small"><i class="fas fa-id-card mr-1"></i>Non fournie</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-cogs mr-2 text-secondary"></i>Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('operator.adherents.edit', [$organisation, $adherent]) }}" class="btn btn-outline-primary btn-block btn-sm mb-2">
                        <i class="fas fa-edit mr-1"></i>Modifier
                    </a>

                    @if(!$adherent->is_active)
                    <form action="{{ route('operator.adherents.reactivate', [$organisation, $adherent]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="motif" value="Réactivation par l'opérateur">
                        <button type="submit" class="btn btn-outline-success btn-block btn-sm mb-2" onclick="return confirm('Réactiver cet adhérent ?')">
                            <i class="fas fa-user-check mr-1"></i>Réactiver
                        </button>
                    </form>
                    @endif

                    @if($adherent->is_active && !$adherent->is_fondateur)
                    {{-- Bouton pour ouvrir le formulaire d'exclusion --}}
                    <button type="button" class="btn btn-outline-warning btn-block btn-sm mb-2" data-toggle="collapse" data-target="#exclusionForm">
                        <i class="fas fa-ban mr-1"></i>Exclure / Désactiver
                    </button>

                    {{-- Formulaire d'exclusion (caché par défaut) --}}
                    <div class="collapse mb-2" id="exclusionForm">
                        <div class="card card-body border-warning p-3">
                            <form action="{{ route('operator.adherents.exclude', [$organisation, $adherent]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Motif de l'exclusion <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-sm" name="motif" rows="2" required placeholder="Précisez le motif..."></textarea>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Date d'exclusion</label>
                                    <input type="date" class="form-control form-control-sm" name="date_exclusion" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Document justificatif (PDF)</label>
                                    <input type="file" class="form-control-file form-control-sm" name="document" accept=".pdf">
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm btn-block" onclick="return confirm('Confirmer l\'exclusion de {{ $adherent->nom }} {{ $adherent->prenom }} ?')">
                                    <i class="fas fa-ban mr-1"></i>Confirmer l'exclusion
                                </button>
                            </form>
                        </div>
                    </div>

                    <form action="{{ route('operator.adherents.destroy', [$organisation, $adherent]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block btn-sm" onclick="return confirm('Confirmer la démission de {{ $adherent->nom }} {{ $adherent->prenom }} ?')">
                            <i class="fas fa-user-minus mr-1"></i>Enregistrer démission
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
