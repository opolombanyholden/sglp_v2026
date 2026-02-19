@extends('layouts.operator')

@section('title', 'Ajouter un adhérent - ' . ($organisation->nom ?? ''))

@section('page-title', 'Ajouter un adhérent')

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
                            <li class="breadcrumb-item active text-white">Ajouter</li>
                        </ol>
                    </nav>
                    <h2 class="mb-1"><i class="fas fa-user-plus mr-2"></i>Ajouter un adhérent</h2>
                    <p class="mb-0" style="opacity:.9">{{ $organisation->nom }}@if($organisation->sigle) ({{ $organisation->sigle }})@endif</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    <form action="{{ route('operator.adherents.store', $organisation) }}" method="POST" enctype="multipart/form-data">
        @csrf

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
                                <label for="nip">NIP <small class="text-muted">(Numéro d'Identification Personnel)</small></label>
                                <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip') }}" placeholder="XX-QQQQ-YYYYMMDD">
                                @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Format attendu : XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)</small>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="nom">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" required>
                                @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="prenom">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
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
                                <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}">
                                @error('date_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="lieu_naissance">Lieu de naissance</label>
                                <input type="text" class="form-control @error('lieu_naissance') is-invalid @enderror" id="lieu_naissance" name="lieu_naissance" value="{{ old('lieu_naissance') }}">
                                @error('lieu_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="sexe">Sexe</label>
                                <select class="form-control @error('sexe') is-invalid @enderror" id="sexe" name="sexe">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                                </select>
                                @error('sexe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nationalite">Nationalité</label>
                            <input type="text" class="form-control @error('nationalite') is-invalid @enderror" id="nationalite" name="nationalite" value="{{ old('nationalite', 'Gabonaise') }}">
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
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone') }}" placeholder="+241 XX XX XX XX">
                                @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
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
                                <input type="text" class="form-control @error('profession') is-invalid @enderror" id="profession" name="profession" value="{{ old('profession') }}">
                                @error('profession') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fonction">Fonction dans l'organisation <span class="text-danger">*</span></label>
                                <select class="form-control @error('fonction') is-invalid @enderror" id="fonction" name="fonction" required>
                                    <option value="Membre" {{ old('fonction') == 'Membre' ? 'selected' : '' }}>Membre</option>
                                    <option value="Président" {{ old('fonction') == 'Président' ? 'selected' : '' }}>Président</option>
                                    <option value="Vice-Président" {{ old('fonction') == 'Vice-Président' ? 'selected' : '' }}>Vice-Président</option>
                                    <option value="Secrétaire Général" {{ old('fonction') == 'Secrétaire Général' ? 'selected' : '' }}>Secrétaire Général</option>
                                    <option value="Trésorier" {{ old('fonction') == 'Trésorier' ? 'selected' : '' }}>Trésorier</option>
                                    <option value="Commissaire aux Comptes" {{ old('fonction') == 'Commissaire aux Comptes' ? 'selected' : '' }}>Commissaire aux Comptes</option>
                                </select>
                                @error('fonction') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="motif_adhesion">Motif d'adhésion</label>
                            <textarea class="form-control @error('motif_adhesion') is-invalid @enderror" id="motif_adhesion" name="motif_adhesion" rows="2">{{ old('motif_adhesion') }}</textarea>
                            @error('motif_adhesion') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                <input type="file" class="form-control-file @error('piece_identite') is-invalid @enderror" id="piece_identite" name="piece_identite" accept=".pdf,.jpg,.jpeg,.png">
                                @error('piece_identite') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="photo">Photo <small class="text-muted">(JPG, PNG - max 2 Mo)</small></label>
                                <input type="file" class="form-control-file @error('photo') is-invalid @enderror" id="photo" name="photo" accept=".jpg,.jpeg,.png">
                                @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                <input type="text" class="form-control @error('province') is-invalid @enderror" id="province" name="province" value="{{ old('province') }}">
                                @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="departement">Département</label>
                                <input type="text" class="form-control @error('departement') is-invalid @enderror" id="departement" name="departement" value="{{ old('departement') }}">
                                @error('departement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="adresse_complete">Adresse complète</label>
                            <input type="text" class="form-control @error('adresse_complete') is-invalid @enderror" id="adresse_complete" name="adresse_complete" value="{{ old('adresse_complete') }}">
                            @error('adresse_complete') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Boutons --}}
                <div class="mb-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save mr-2"></i>Enregistrer l'adhérent
                    </button>
                    <a href="{{ route('operator.adherents.index', $organisation) }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Aide --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle mr-2 text-info"></i>Informations</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">Les anomalies sont détectées automatiquement lors de l'enregistrement :</p>
                        <ul class="small text-muted mb-0">
                            <li><span class="badge badge-danger">Critique</span> NIP absent, mineur, double appartenance parti</li>
                            <li><span class="badge badge-warning">Majeure</span> Format NIP incorrect, date naissance manquante</li>
                            <li><span class="badge badge-info">Mineure</span> Profession manquante, téléphone invalide</li>
                        </ul>
                    </div>
                </div>

                @if($organisation->type === 'parti_politique')
                <div class="card border-left-danger shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Parti politique</h6>
                        <p class="small text-muted mb-2">Pour les partis politiques, les professions suivantes sont interdites :</p>
                        <ul class="small text-muted mb-0">
                            <li>Magistrat, Juge, Procureur</li>
                            <li>Militaire / Gendarme en activité</li>
                            <li>Commissaire de police</li>
                            <li>Préfet, Sous-préfet, Gouverneur</li>
                        </ul>
                    </div>
                </div>
                @endif

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-keyboard mr-2 text-secondary"></i>Champs obligatoires</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small text-muted mb-0">
                            <li><strong>Nom</strong> et <strong>Prénom</strong></li>
                            <li><strong>Fonction</strong> dans l'organisation</li>
                        </ul>
                        <p class="small text-muted mt-2 mb-0">Les autres champs sont recommandés mais non bloquants.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
