@extends('layouts.admin')

@section('title', ($adherent->nom ?? '') . ' ' . ($adherent->prenom ?? '') . ' - Adhérent')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-user mr-2 text-primary"></i>{{ $adherent->nom }} {{ $adherent->prenom }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.adherents.index', $organisation) }}">Adhérents</a></li>
                    <li class="breadcrumb-item active">{{ $adherent->nom }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.adherents.index', $organisation) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Retour
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Identification --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-id-card mr-2 text-primary"></i>Identification</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">NIP</label>
                            <p class="font-weight-bold mb-0"><code>{{ $adherent->nip ?: 'Non renseigné' }}</code></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Nom</label>
                            <p class="font-weight-bold mb-0">{{ $adherent->nom }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Prénom</label>
                            <p class="font-weight-bold mb-0">{{ $adherent->prenom }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Date de naissance</label>
                            <p class="mb-0">{{ $adherent->date_naissance ? $adherent->date_naissance->format('d/m/Y') : 'Non renseignée' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Lieu de naissance</label>
                            <p class="mb-0">{{ $adherent->lieu_naissance ?: 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Sexe</label>
                            <p class="mb-0">{{ $adherent->sexe === 'M' ? 'Masculin' : ($adherent->sexe === 'F' ? 'Féminin' : 'Non renseigné') }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Nationalité</label>
                            <p class="mb-0">{{ $adherent->nationalite ?: 'Non renseignée' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Téléphone</label>
                            <p class="mb-0">{{ $adherent->telephone ?: 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Email</label>
                            <p class="mb-0">{{ $adherent->email ?: 'Non renseigné' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Professionnel --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-briefcase mr-2 text-warning"></i>Informations professionnelles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Profession</label>
                            <p class="mb-0">{{ $adherent->profession ?: 'Non renseignée' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Fonction</label>
                            <p class="font-weight-bold mb-0">{{ $adherent->fonction ?: 'Membre' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Date d'adhésion</label>
                            <p class="mb-0">{{ $adherent->date_adhesion ? $adherent->date_adhesion->format('d/m/Y') : '-' }}</p>
                        </div>
                    </div>
                    @if($adherent->adresse_complete || $adherent->province)
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Adresse</label>
                            <p class="mb-0">{{ $adherent->adresse_complete ?: '-' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Province</label>
                            <p class="mb-0">{{ $adherent->province ?: '-' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-0">Département</label>
                            <p class="mb-0">{{ $adherent->departement ?: '-' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Anomalies --}}
            @if($adherent->has_anomalies && $adherent->anomalies_data)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle mr-2 text-danger"></i>Anomalies</h5>
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
                                        <span class="badge badge-{{ $anomalie['type'] === 'critique' ? 'danger' : ($anomalie['type'] === 'majeure' ? 'warning' : 'info') }}">
                                            {{ ucfirst($anomalie['type']) }}
                                        </span>
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
            <div class="card shadow-sm mb-4">
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
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle mr-2 text-primary"></i>Statut</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($adherent->is_active)
                            <span class="badge badge-success p-2" style="font-size: 1rem;">Actif</span>
                        @else
                            <span class="badge badge-danger p-2" style="font-size: 1rem;">Inactif</span>
                        @endif
                    </div>
                    <ul class="list-unstyled small mb-0">
                        @if($adherent->is_fondateur)
                        <li class="mb-2"><i class="fas fa-star text-warning mr-2"></i>Membre fondateur</li>
                        @endif
                        <li class="mb-2"><i class="fas fa-building mr-2 text-muted"></i>{{ $organisation->nom }}</li>
                        @if($adherent->source_inscription)
                        <li class="mb-2">
                            <i class="fas fa-door-open mr-2 text-muted"></i>
                            Source : {{ $adherent->source_inscription === 'auto_inscription' ? 'Auto-inscription' : 'Saisie manuelle' }}
                        </li>
                        @endif
                        @if($adherent->statut_inscription === 'en_attente_validation')
                        <li class="mb-2"><span class="badge badge-warning">En attente de validation</span></li>
                        @endif
                        @if(!$adherent->is_active && $adherent->date_exclusion)
                        <li class="mb-2"><i class="fas fa-ban text-danger mr-2"></i>Exclusion : {{ $adherent->date_exclusion->format('d/m/Y') }}</li>
                        <li class="mb-2"><i class="fas fa-comment text-muted mr-2"></i>{{ $adherent->motif_exclusion ?? 'Non précisé' }}</li>
                        @endif
                        <li><i class="fas fa-clock mr-2 text-muted"></i>Créé le {{ $adherent->created_at ? $adherent->created_at->format('d/m/Y H:i') : 'N/A' }}</li>
                    </ul>
                </div>
            </div>

            {{-- Documents --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-file mr-2 text-info"></i>Documents</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-1">Photo</p>
                    @if($adherent->photo)
                        <img src="{{ asset('storage/' . $adherent->photo) }}" alt="Photo" class="img-thumbnail mb-3" style="max-width: 100px;">
                    @else
                        <p class="text-muted small mb-3"><i class="fas fa-image mr-1"></i>Aucune photo</p>
                    @endif
                    <p class="small text-muted mb-1">Pièce d'identité</p>
                    @if($adherent->piece_identite)
                        <a href="{{ asset('storage/' . $adherent->piece_identite) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download mr-1"></i>Télécharger
                        </a>
                    @else
                        <p class="text-muted small"><i class="fas fa-id-card mr-1"></i>Non fournie</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
