@extends('layouts.operator')

@section('title', 'Modifier ' . ($adherent->nom ?? '') . ' ' . ($adherent->prenom ?? ''))

@section('page-title', 'Modifier un adhérent')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background-color: #009e3f;">
                <div class="card-body text-white">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent p-0 mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}" class="text-white" style="opacity:.75">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('operator.adherents.index', $organisation) }}" class="text-white" style="opacity:.75">Adhérents</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('operator.adherents.show', [$organisation, $adherent]) }}" class="text-white" style="opacity:.75">{{ $adherent->nom }}</a></li>
                            <li class="breadcrumb-item active text-white">Modifier</li>
                        </ol>
                    </nav>
                    <h2 class="mb-1"><i class="fas fa-edit mr-2"></i>Modifier : {{ $adherent->nom }} {{ $adherent->prenom }}</h2>
                    <p class="mb-0" style="opacity:.9">{{ $organisation->nom }}</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    <form action="{{ route('operator.adherents.update', [$organisation, $adherent]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                {{-- Identification --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-id-card mr-2 text-primary"></i>Identification</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="nip">NIP</label>
                                <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip', $adherent->nip) }}" placeholder="XX-QQQQ-YYYYMMDD">
                                @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Format : XX-QQQQ-YYYYMMDD</small>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="nom">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $adherent->nom) }}" required>
                                @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="prenom">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom', $adherent->prenom) }}" required>
                                @error('prenom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Informations personnelles --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user mr-2 text-info"></i>Informations personnelles</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="date_naissance">Date de naissance</label>
                                <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" id="date_naissance" name="date_naissance" value="{{ old('date_naissance', $adherent->date_naissance ? $adherent->date_naissance->format('Y-m-d') : '') }}">
                                @error('date_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="lieu_naissance">Lieu de naissance</label>
                                <input type="text" class="form-control @error('lieu_naissance') is-invalid @enderror" id="lieu_naissance" name="lieu_naissance" value="{{ old('lieu_naissance', $adherent->lieu_naissance) }}">
                                @error('lieu_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="sexe">Sexe</label>
                                <select class="form-control @error('sexe') is-invalid @enderror" id="sexe" name="sexe">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="M" {{ old('sexe', $adherent->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('sexe', $adherent->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                                </select>
                                @error('sexe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nationalite">Nationalité</label>
                            <input type="text" class="form-control @error('nationalite') is-invalid @enderror" id="nationalite" name="nationalite" value="{{ old('nationalite', $adherent->nationalite) }}">
                            @error('nationalite') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Contact --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-phone mr-2 text-success"></i>Contact</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="telephone">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone', $adherent->telephone) }}">
                                @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $adherent->email) }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Professionnel --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-briefcase mr-2 text-warning"></i>Informations professionnelles</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="profession">Profession</label>
                                <input type="text" class="form-control @error('profession') is-invalid @enderror" id="profession" name="profession" value="{{ old('profession', $adherent->profession) }}">
                                @error('profession') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fonction">Fonction <span class="text-danger">*</span></label>
                                <select class="form-control @error('fonction') is-invalid @enderror" id="fonction" name="fonction" required>
                                    @php $currentFonction = old('fonction', $adherent->fonction); @endphp
                                    <option value="Membre" {{ $currentFonction == 'Membre' ? 'selected' : '' }}>Membre</option>
                                    <option value="Président" {{ $currentFonction == 'Président' ? 'selected' : '' }}>Président</option>
                                    <option value="Vice-Président" {{ $currentFonction == 'Vice-Président' ? 'selected' : '' }}>Vice-Président</option>
                                    <option value="Secrétaire Général" {{ $currentFonction == 'Secrétaire Général' ? 'selected' : '' }}>Secrétaire Général</option>
                                    <option value="Trésorier" {{ $currentFonction == 'Trésorier' ? 'selected' : '' }}>Trésorier</option>
                                    <option value="Commissaire aux Comptes" {{ $currentFonction == 'Commissaire aux Comptes' ? 'selected' : '' }}>Commissaire aux Comptes</option>
                                </select>
                                @error('fonction') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documents --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt mr-2 text-secondary"></i>Documents</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="piece_identite">Pièce d'identité <small class="text-muted">(PDF, JPG, PNG - max 5 Mo)</small></label>
                                @if($adherent->piece_identite)
                                    <div class="mb-2">
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Fichier existant</span>
                                        <a href="{{ asset('storage/' . $adherent->piece_identite) }}" target="_blank" class="small ml-2">Voir</a>
                                    </div>
                                @endif
                                <input type="file" class="form-control-file @error('piece_identite') is-invalid @enderror" id="piece_identite" name="piece_identite" accept=".pdf,.jpg,.jpeg,.png">
                                @error('piece_identite') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($adherent->piece_identite)
                                    <small class="form-text text-muted">Laisser vide pour conserver le fichier actuel</small>
                                @endif
                            </div>
                            <div class="form-group col-md-6">
                                <label for="photo">Photo <small class="text-muted">(JPG, PNG - max 2 Mo)</small></label>
                                @if($adherent->photo)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $adherent->photo) }}" alt="Photo" class="img-thumbnail" style="max-height: 60px;">
                                    </div>
                                @endif
                                <input type="file" class="form-control-file @error('photo') is-invalid @enderror" id="photo" name="photo" accept=".jpg,.jpeg,.png">
                                @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($adherent->photo)
                                    <small class="form-text text-muted">Laisser vide pour conserver la photo actuelle</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Adresse --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt mr-2 text-danger"></i>Adresse</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="province">Province</label>
                                <input type="text" class="form-control @error('province') is-invalid @enderror" id="province" name="province" value="{{ old('province', $adherent->province) }}">
                                @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="departement">Département</label>
                                <input type="text" class="form-control @error('departement') is-invalid @enderror" id="departement" name="departement" value="{{ old('departement', $adherent->departement) }}">
                                @error('departement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="adresse_complete">Adresse complète</label>
                            <input type="text" class="form-control @error('adresse_complete') is-invalid @enderror" id="adresse_complete" name="adresse_complete" value="{{ old('adresse_complete', $adherent->adresse_complete) }}">
                            @error('adresse_complete') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Boutons --}}
                <div class="mb-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                    </button>
                    <a href="{{ route('operator.adherents.show', [$organisation, $adherent]) }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Info adhérent --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle mr-2 text-info"></i>Adhérent</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2">
                                <strong>Statut :</strong>
                                @if($adherent->is_active)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-danger">Inactif</span>
                                @endif
                            </li>
                            @if($adherent->is_fondateur)
                            <li class="mb-2"><span class="badge badge-warning"><i class="fas fa-star mr-1"></i>Fondateur</span></li>
                            @endif
                            <li class="mb-2"><strong>Créé le :</strong> {{ $adherent->created_at ? $adherent->created_at->format('d/m/Y H:i') : 'N/A' }}</li>
                            <li><strong>Modifié le :</strong> {{ $adherent->updated_at ? $adherent->updated_at->format('d/m/Y H:i') : 'N/A' }}</li>
                        </ul>
                    </div>
                </div>

                @if($adherent->has_anomalies)
                <div class="card border-left-{{ $adherent->anomalies_severity === 'critique' ? 'danger' : ($adherent->anomalies_severity === 'majeure' ? 'warning' : 'info') }} shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-{{ $adherent->anomalies_severity === 'critique' ? 'danger' : ($adherent->anomalies_severity === 'majeure' ? 'warning' : 'info') }}">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Anomalies ({{ $adherent->anomalies_severity }})
                        </h6>
                        <p class="small text-muted mb-2">Les anomalies seront recalculées lors de l'enregistrement.</p>
                        @if(is_array($adherent->anomalies_data))
                        <ul class="small mb-0">
                            @foreach($adherent->anomalies_data as $anomalie)
                                <li>{{ $anomalie['message'] ?? $anomalie['code'] ?? '-' }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection
