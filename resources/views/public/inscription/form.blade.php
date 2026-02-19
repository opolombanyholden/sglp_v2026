@extends('layouts.public')

@section('title', 'Adhésion - ' . ($organisation->nom ?? 'Organisation'))

@section('content')
<style>
    :root {
        --gabon-green: #009e3f;
        --gabon-yellow: #ffcd00;
        --gabon-blue: #003f7f;
    }
    .inscription-header {
        background: linear-gradient(135deg, var(--gabon-blue) 0%, #0056b3 50%, var(--gabon-green) 100%);
        color: white;
        padding: 2.5rem 0;
    }
    .inscription-header .badge-type {
        background: var(--gabon-yellow);
        color: var(--gabon-blue);
        font-weight: 700;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    .form-section-title {
        color: var(--gabon-blue);
        font-weight: 700;
        border-bottom: 3px solid var(--gabon-yellow);
        padding-bottom: 0.5rem;
        margin-bottom: 1.2rem;
    }
    .btn-inscription {
        background: linear-gradient(135deg, var(--gabon-green), #00b347);
        border: none;
        color: white;
        font-weight: 700;
        padding: 0.8rem 2.5rem;
        font-size: 1.1rem;
        border-radius: 8px;
        transition: all 0.3s;
    }
    .btn-inscription:hover {
        background: linear-gradient(135deg, #008a36, var(--gabon-green));
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 158, 63, 0.3);
    }
    .card-organisation {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .required-star { color: #dc3545; font-weight: 700; }
    .info-alert {
        background: #fff3cd;
        border-left: 4px solid var(--gabon-yellow);
        border-radius: 0 8px 8px 0;
    }
    .upload-zone {
        border: 2px dashed #ccc;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8f9fa;
        transition: all 0.3s;
        cursor: pointer;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--gabon-green);
        background: #f0faf4;
    }
    .upload-zone .file-info {
        display: none;
        color: var(--gabon-green);
        font-weight: 600;
    }
    .upload-zone.has-file .file-info { display: block; }
    .upload-zone.has-file .upload-prompt { display: none; }
</style>

{{-- HEADER --}}
<div class="inscription-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge-type mb-2 d-inline-block">
                    <i class="fas fa-building mr-1"></i>
                    {{ ucfirst(str_replace('_', ' ', $organisation->type ?? '')) }}
                </span>
                <h1 class="mb-2">{{ $organisation->nom ?? 'Organisation' }}</h1>
                @if($organisation->sigle)
                    <h4 class="opacity-75 mb-2">({{ $organisation->sigle }})</h4>
                @endif
                <p class="mb-0 opacity-90">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    {{ $organisation->siege_social ?? $organisation->ville_commune ?? 'Gabon' }}
                    @if($organisation->province)
                        - {{ $organisation->province }}
                    @endif
                </p>
            </div>
            <div class="col-lg-4 text-lg-right text-center mt-3 mt-lg-0">
                <div class="bg-white rounded-lg p-3 d-inline-block" style="opacity: 0.95;">
                    <i class="fas fa-user-plus fa-3x" style="color: var(--gabon-green);"></i>
                    <p class="mb-0 mt-1 font-weight-bold" style="color: var(--gabon-blue);">Formulaire d'adhésion</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    {{-- Messages d'erreur --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-exclamation-triangle mr-2"></i>Erreurs de validation</h5>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        {{-- COLONNE PRINCIPALE : FORMULAIRE --}}
        <div class="col-lg-8">
            {{-- Présentation de l'organisation --}}
            @if($organisation->objet)
                <div class="card card-organisation mb-4">
                    <div class="card-body">
                        <h5 class="form-section-title">
                            <i class="fas fa-info-circle mr-2"></i>À propos de l'organisation
                        </h5>
                        <p class="text-muted mb-0">{{ $organisation->objet }}</p>
                    </div>
                </div>
            @endif

            {{-- Formulaire d'adhésion --}}
            <form method="POST" action="{{ route('public.inscription.submit', $inscriptionLink->token) }}"
                  enctype="multipart/form-data" id="inscription-form">
                @csrf

                {{-- Section 1 : Identification --}}
                <div class="card card-organisation mb-4">
                    <div class="card-body">
                        <h5 class="form-section-title">
                            <i class="fas fa-id-card mr-2"></i>Identification
                        </h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="civilite">Civilité</label>
                                    <select name="civilite" id="civilite" class="form-control @error('civilite') is-invalid @enderror">
                                        <option value="M" {{ old('civilite') === 'M' ? 'selected' : '' }}>M.</option>
                                        <option value="F" {{ old('civilite') === 'F' ? 'selected' : '' }}>Mme</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nom">Nom <span class="required-star">*</span></label>
                                    <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror"
                                           value="{{ old('nom') }}" required maxlength="100" placeholder="Votre nom">
                                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="prenom">Prénom <span class="required-star">*</span></label>
                                    <input type="text" name="prenom" id="prenom" class="form-control @error('prenom') is-invalid @enderror"
                                           value="{{ old('prenom') }}" required maxlength="100" placeholder="Votre prénom">
                                    @error('prenom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nip">NIP (Numéro d'Identification Personnel)</label>
                                    <input type="text" name="nip" id="nip" class="form-control @error('nip') is-invalid @enderror"
                                           value="{{ old('nip') }}" maxlength="20" placeholder="Ex: A1-2345-19901225">
                                    @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">Format : XX-QQQQ-AAAAMMJJ</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_naissance">Date de naissance</label>
                                    <input type="date" name="date_naissance" id="date_naissance"
                                           class="form-control @error('date_naissance') is-invalid @enderror"
                                           value="{{ old('date_naissance') }}">
                                    @error('date_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lieu_naissance">Lieu de naissance</label>
                                    <input type="text" name="lieu_naissance" id="lieu_naissance"
                                           class="form-control @error('lieu_naissance') is-invalid @enderror"
                                           value="{{ old('lieu_naissance') }}" maxlength="255">
                                    @error('lieu_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sexe">Sexe</label>
                                    <select name="sexe" id="sexe" class="form-control @error('sexe') is-invalid @enderror">
                                        <option value="">-- Sélectionner --</option>
                                        <option value="M" {{ old('sexe') === 'M' ? 'selected' : '' }}>Masculin</option>
                                        <option value="F" {{ old('sexe') === 'F' ? 'selected' : '' }}>Féminin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nationalite">Nationalité</label>
                                    <input type="text" name="nationalite" id="nationalite"
                                           class="form-control @error('nationalite') is-invalid @enderror"
                                           value="{{ old('nationalite', 'Gabonaise') }}" maxlength="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 2 : Coordonnées --}}
                <div class="card card-organisation mb-4">
                    <div class="card-body">
                        <h5 class="form-section-title">
                            <i class="fas fa-phone-alt mr-2"></i>Coordonnées
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telephone">Téléphone</label>
                                    <input type="tel" name="telephone" id="telephone"
                                           class="form-control @error('telephone') is-invalid @enderror"
                                           value="{{ old('telephone') }}" maxlength="20" placeholder="Ex: 077123456">
                                    @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" maxlength="255" placeholder="exemple@email.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="adresse_complete">Adresse</label>
                                    <input type="text" name="adresse_complete" id="adresse_complete"
                                           class="form-control @error('adresse_complete') is-invalid @enderror"
                                           value="{{ old('adresse_complete') }}" maxlength="255" placeholder="Votre adresse complète">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="province">Province</label>
                                    <input type="text" name="province" id="province"
                                           class="form-control @error('province') is-invalid @enderror"
                                           value="{{ old('province') }}" maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="departement">Département</label>
                                    <input type="text" name="departement" id="departement"
                                           class="form-control @error('departement') is-invalid @enderror"
                                           value="{{ old('departement') }}" maxlength="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 3 : Professionnel --}}
                <div class="card card-organisation mb-4">
                    <div class="card-body">
                        <h5 class="form-section-title">
                            <i class="fas fa-briefcase mr-2"></i>Informations professionnelles
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profession">Profession</label>
                                    <input type="text" name="profession" id="profession"
                                           class="form-control @error('profession') is-invalid @enderror"
                                           value="{{ old('profession') }}" maxlength="255" placeholder="Votre profession">
                                    @error('profession') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fonction">Fonction dans l'organisation</label>
                                    <select name="fonction" id="fonction" class="form-control @error('fonction') is-invalid @enderror">
                                        <option value="Membre" {{ old('fonction', 'Membre') === 'Membre' ? 'selected' : '' }}>Membre</option>
                                        <option value="Sympathisant" {{ old('fonction') === 'Sympathisant' ? 'selected' : '' }}>Sympathisant</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @if($organisation->type === 'parti_politique')
                            <div class="info-alert p-3 mt-2">
                                <i class="fas fa-info-circle mr-2" style="color: var(--gabon-blue);"></i>
                                <strong>Information importante :</strong> Conformément à la loi, un citoyen ne peut être membre de deux partis politiques simultanément. Votre NIP sera vérifié lors de la validation.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Section 4 : Pièce d'identité --}}
                <div class="card card-organisation mb-4">
                    <div class="card-body">
                        <h5 class="form-section-title">
                            <i class="fas fa-file-upload mr-2"></i>Pièce d'identité <span class="required-star">*</span>
                        </h5>
                        <p class="text-muted mb-3">
                            Veuillez joindre un scan ou une photo de votre pièce d'identité (CNI, passeport ou titre de séjour).
                        </p>

                        <div class="upload-zone" id="upload-zone" onclick="document.getElementById('piece_identite').click();">
                            <div class="upload-prompt">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                <p class="mb-1 font-weight-bold">Cliquez pour sélectionner un fichier</p>
                                <p class="text-muted small mb-0">Formats acceptés : PDF, JPG, PNG - Maximum 5 Mo</p>
                            </div>
                            <div class="file-info">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p class="mb-0" id="file-name">Fichier sélectionné</p>
                            </div>
                        </div>
                        <input type="file" name="piece_identite" id="piece_identite"
                               accept=".pdf,.jpg,.jpeg,.png" class="d-none"
                               onchange="handleFileSelect(this)">
                        @error('piece_identite')
                            <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                </div>

                {{-- Bouton de soumission --}}
                <div class="text-center mb-5">
                    <button type="submit" class="btn btn-inscription" id="btn-submit">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Soumettre ma demande d'adhésion
                    </button>
                    <p class="text-muted mt-2 small">
                        En soumettant ce formulaire, vous acceptez que vos informations soient traitées par l'organisation.
                    </p>
                </div>
            </form>
        </div>

        {{-- COLONNE LATÉRALE --}}
        <div class="col-lg-4">
            <div class="card card-organisation mb-4" style="border-top: 4px solid var(--gabon-green);">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3" style="color: var(--gabon-blue);">
                        <i class="fas fa-shield-alt mr-2"></i>Organisation vérifiée
                    </h6>
                    <p class="small text-muted mb-2">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Organisation approuvée par les autorités
                    </p>
                    @if($organisation->numero_recepisse)
                        <p class="small text-muted mb-2">
                            <i class="fas fa-file-alt mr-1" style="color: var(--gabon-blue);"></i>
                            Récépissé : {{ $organisation->numero_recepisse }}
                        </p>
                    @endif
                    <p class="small text-muted mb-0">
                        <i class="fas fa-calendar mr-1" style="color: var(--gabon-blue);"></i>
                        Créée le {{ $organisation->created_at ? $organisation->created_at->format('d/m/Y') : 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="card card-organisation mb-4">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3" style="color: var(--gabon-blue);">
                        <i class="fas fa-list-check mr-2"></i>Processus d'adhésion
                    </h6>
                    <ol class="small text-muted pl-3 mb-0">
                        <li class="mb-2">Remplissez le formulaire ci-contre</li>
                        <li class="mb-2">Joignez votre pièce d'identité</li>
                        <li class="mb-2">Soumettez votre demande</li>
                        <li class="mb-2">L'administrateur vérifie vos informations</li>
                        <li>Vous recevrez une confirmation par email</li>
                    </ol>
                </div>
            </div>

            <div class="card card-organisation">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3" style="color: var(--gabon-blue);">
                        <i class="fas fa-question-circle mr-2"></i>Besoin d'aide ?
                    </h6>
                    <p class="small text-muted mb-2">
                        Pour toute question, contactez l'administration de l'organisation :
                    </p>
                    @if($organisation->email)
                        <p class="small mb-1">
                            <i class="fas fa-envelope mr-1"></i>
                            <a href="mailto:{{ $organisation->email }}">{{ $organisation->email }}</a>
                        </p>
                    @endif
                    @if($organisation->telephone)
                        <p class="small mb-0">
                            <i class="fas fa-phone mr-1"></i> {{ $organisation->telephone }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleFileSelect(input) {
    var zone = document.getElementById('upload-zone');
    var nameEl = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert('Le fichier est trop volumineux. Taille maximum : 5 Mo.');
            input.value = '';
            zone.classList.remove('has-file');
            return;
        }
        var allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        if (allowed.indexOf(file.type) === -1) {
            alert('Format non accepté. Veuillez sélectionner un fichier PDF, JPG ou PNG.');
            input.value = '';
            zone.classList.remove('has-file');
            return;
        }
        zone.classList.add('has-file');
        nameEl.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' Mo)';
    } else {
        zone.classList.remove('has-file');
    }
}

// Prévenir double soumission
document.getElementById('inscription-form').addEventListener('submit', function(e) {
    var btn = document.getElementById('btn-submit');
    if (btn.disabled) {
        e.preventDefault();
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi en cours...';
});

// Drag & drop sur la zone d'upload
var uploadZone = document.getElementById('upload-zone');
['dragenter', 'dragover'].forEach(function(evt) {
    uploadZone.addEventListener(evt, function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
});
['dragleave', 'drop'].forEach(function(evt) {
    uploadZone.addEventListener(evt, function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
});
uploadZone.addEventListener('drop', function(e) {
    var dt = e.dataTransfer;
    if (dt.files && dt.files[0]) {
        document.getElementById('piece_identite').files = dt.files;
        handleFileSelect(document.getElementById('piece_identite'));
    }
});
</script>
@endsection
