@extends('layouts.operator')

@section('title', 'Fondateurs - ' . ($organisation->nom ?? ''))

@section('page-title', 'Gestion des fondateurs')

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
                            <li class="breadcrumb-item active text-white">Fondateurs</li>
                        </ol>
                    </nav>
                    <h2 class="mb-1"><i class="fas fa-star mr-2"></i>Membres fondateurs</h2>
                    <p class="mb-0" style="opacity:.9">{{ $organisation->nom }} - {{ $fondateurs->count() }} fondateur(s)</p>
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
            {{-- Table fondateurs --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list mr-2 text-warning"></i>Liste des fondateurs</h5>
                    <button class="btn btn-sm btn-primary" type="button" data-toggle="collapse" data-target="#formAjout">
                        <i class="fas fa-plus mr-1"></i>Ajouter
                    </button>
                </div>
                <div class="card-body p-0">
                    @if($fondateurs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>NIP</th>
                                    <th>Nom & Prénom</th>
                                    <th>Date naissance</th>
                                    <th>Profession</th>
                                    <th>Fonction</th>
                                    <th>Pièce</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fondateurs as $fondateur)
                                <tr>
                                    <td>{{ $fondateur->ordre }}</td>
                                    <td><code>{{ $fondateur->nip ?? 'N/A' }}</code></td>
                                    <td>
                                        <strong>{{ $fondateur->nom }}</strong> {{ $fondateur->prenom }}
                                        @if($fondateur->adherent)
                                            <br><a href="{{ route('operator.adherents.show', [$organisation, $fondateur->adherent]) }}" class="small text-primary">Voir fiche adhérent</a>
                                        @endif
                                    </td>
                                    <td>{{ $fondateur->date_naissance ? \Carbon\Carbon::parse($fondateur->date_naissance)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $fondateur->profession ?? '-' }}</td>
                                    <td>{{ $fondateur->fonction ?? '-' }}</td>
                                    <td>
                                        @if($fondateur->piece_identite_path)
                                            <a href="{{ asset('storage/' . $fondateur->piece_identite_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Voir la pièce">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-4 text-center">
                        <i class="fas fa-star fa-3x text-gray-300 mb-3"></i>
                        <p class="text-muted">Aucun fondateur enregistré.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Formulaire ajout fondateur (dépliable) --}}
            <div class="collapse" id="formAjout">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-plus mr-2 text-primary"></i>Ajouter un fondateur</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('operator.adherents.addFondateur', $organisation) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label class="small">NIP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('nip') is-invalid @enderror" name="nip" value="{{ old('nip') }}" required placeholder="XX-QQQQ-YYYYMMDD">
                                @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label class="small">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('nom') is-invalid @enderror" name="nom" value="{{ old('nom') }}" required>
                                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-6">
                                    <label class="small">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('prenom') is-invalid @enderror" name="prenom" value="{{ old('prenom') }}" required>
                                    @error('prenom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label class="small">Date naissance <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm @error('date_naissance') is-invalid @enderror" name="date_naissance" value="{{ old('date_naissance') }}" required>
                                    @error('date_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-6">
                                    <label class="small">Lieu naissance <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('lieu_naissance') is-invalid @enderror" name="lieu_naissance" value="{{ old('lieu_naissance') }}" required>
                                    @error('lieu_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label class="small">Sexe <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm @error('sexe') is-invalid @enderror" name="sexe" required>
                                        <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                                        <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                                    </select>
                                    @error('sexe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-6">
                                    <label class="small">Nationalité <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('nationalite') is-invalid @enderror" name="nationalite" value="{{ old('nationalite', 'Gabonaise') }}" required>
                                    @error('nationalite') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label class="small">Profession <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('profession') is-invalid @enderror" name="profession" value="{{ old('profession') }}" required>
                                    @error('profession') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-6">
                                    <label class="small">Fonction <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('fonction') is-invalid @enderror" name="fonction" value="{{ old('fonction') }}" required>
                                    @error('fonction') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="small">Adresse <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('adresse_complete') is-invalid @enderror" name="adresse_complete" value="{{ old('adresse_complete') }}" required>
                                @error('adresse_complete') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label class="small">Téléphone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('telephone') is-invalid @enderror" name="telephone" value="{{ old('telephone') }}" required>
                                    @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group col-6">
                                    <label class="small">Email</label>
                                    <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="small">Pièce d'identité <span class="text-danger">*</span></label>
                                <input type="file" class="form-control-file @error('piece_identite') is-invalid @enderror" name="piece_identite" accept=".pdf,.jpg,.jpeg,.png" required>
                                @error('piece_identite') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <small class="text-muted">PDF, JPG ou PNG. Max 5 Mo.</small>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm btn-block">
                                <i class="fas fa-save mr-1"></i>Ajouter le fondateur
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle mr-2 text-info"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <ul class="small text-muted mb-0">
                        <li class="mb-2">Les fondateurs doivent avoir au moins <strong>21 ans</strong></li>
                        <li class="mb-2">Tous les champs sont obligatoires sauf l'email</li>
                        <li class="mb-2">Une pièce d'identité est requise (PDF, JPG, PNG)</li>
                        <li>Un fondateur ne peut pas être retiré (démission impossible)</li>
                    </ul>
                </div>
            </div>

            <a href="{{ route('operator.adherents.index', $organisation) }}" class="btn btn-outline-secondary btn-block">
                <i class="fas fa-arrow-left mr-1"></i>Retour aux adhérents
            </a>
        </div>
    </div>
</div>
@endsection
