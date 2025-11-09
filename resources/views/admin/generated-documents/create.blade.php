@extends('layouts.admin')

@section('title', 'Générer un document')

@section('content')
<div class="container-fluid px-4 py-4">
    
    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home"></i>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.documents.index') }}">Documents générés</a>
            </li>
            <li class="breadcrumb-item active">Générer un document</li>
        </ol>
    </nav>

    {{-- En-tête --}}
    <div class="mb-4">
        <h1 class="h3 mb-2" style="color: #003f7f; font-weight: 600;">
            <i class="fas fa-file-pdf mr-2"></i>Générer un document
        </h1>
        <p class="text-muted mb-0">
            <small>Création manuelle d'un document officiel</small>
        </p>
    </div>

    {{-- Messages flash --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <h6 class="alert-heading">
            <i class="fas fa-exclamation-triangle mr-2"></i>Erreurs de validation
        </h6>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #003f7f 0%, #005fa3 100%); color: white;">
                    <h5 class="mb-0">
                        <i class="fas fa-cog mr-2"></i>Configuration du document
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.documents.generate') }}" method="POST" id="generateForm">
                        @csrf
                        
                        {{-- Template --}}
                        <div class="form-group mb-4">
                            <label class="font-weight-bold">
                                <i class="fas fa-file-alt mr-1" style="color: #003f7f;"></i>
                                Template de document <span class="text-danger">*</span>
                            </label>
                            <select name="document_template_id" 
                                    id="templateSelect" 
                                    class="form-control @error('document_template_id') is-invalid @enderror"
                                    required>
                                <option value="">Sélectionner un template...</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" 
                                            data-type="{{ $template->type_document }}"
                                            data-org-type="{{ $template->organisation_type_id }}"
                                            {{ old('document_template_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->nom }} ({{ $template->type_document }})
                                    </option>
                                @endforeach
                            </select>
                            @error('document_template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Choisissez le type de document à générer
                            </small>
                        </div>

                        {{-- Organisation --}}
                        <div class="form-group mb-4">
                            <label class="font-weight-bold">
                                <i class="fas fa-building mr-1" style="color: #009e3f;"></i>
                                Organisation <span class="text-danger">*</span>
                            </label>
                            <select name="organisation_id" 
                                    id="organisationSelect" 
                                    class="form-control @error('organisation_id') is-invalid @enderror"
                                    required>
                                <option value="">Sélectionner une organisation...</option>
                                @foreach($organisations as $org)
                                    <option value="{{ $org->id }}"
                                            data-type="{{ $org->organisation_type_id }}"
                                            data-sigle="{{ $org->sigle }}"
                                            {{ old('organisation_id') == $org->id ? 'selected' : '' }}>
                                        {{ $org->nom }} 
                                        @if($org->sigle)
                                            ({{ $org->sigle }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('organisation_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Sélectionnez l'organisation pour laquelle générer le document
                            </small>
                        </div>

                        {{-- Dossier (optionnel) --}}
                        <div class="form-group mb-4">
                            <label class="font-weight-bold">
                                <i class="fas fa-folder mr-1" style="color: #ffcd00;"></i>
                                Dossier associé <span class="text-muted">(optionnel)</span>
                            </label>
                            <select name="dossier_id" 
                                    id="dossierSelect" 
                                    class="form-control">
                                <option value="">Aucun dossier spécifique...</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Liez ce document à un dossier de validation existant (optionnel)
                            </small>
                        </div>

                        {{-- Aperçu des informations --}}
                        <div id="previewSection" class="d-none">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-eye mr-2"></i>Aperçu de la génération
                                </h6>
                                <div id="previewContent"></div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-cog mr-1"></i>Générer le document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Aide --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle mr-2"></i>Aide
                    </h6>
                </div>
                <div class="card-body">
                    <h6 style="color: #003f7f;">Types de documents disponibles</h6>
                    <ul class="small text-muted mb-3">
                        <li>Récépissé provisoire</li>
                        <li>Récépissé définitif</li>
                        <li>Certificat d'enregistrement</li>
                        <li>Attestation</li>
                        <li>Notification de rejet</li>
                    </ul>

                    <h6 style="color: #003f7f;">Processus de génération</h6>
                    <ol class="small text-muted mb-3">
                        <li>Sélectionner le template</li>
                        <li>Choisir l'organisation</li>
                        <li>Optionnel: Lier à un dossier</li>
                        <li>Générer le document</li>
                        <li>Le document sera créé avec QR code</li>
                    </ol>

                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Important:</strong> Seules les organisations avec le statut 
                        "Approuvé" peuvent générer des documents officiels.
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-left: 4px solid #009e3f !important;">
                <div class="card-body">
                    <h6 style="color: #009e3f;">
                        <i class="fas fa-shield-alt mr-2"></i>Sécurité
                    </h6>
                    <p class="small text-muted mb-2">
                        Chaque document généré inclut :
                    </p>
                    <ul class="small text-muted mb-0">
                        <li>Numéro unique</li>
                        <li>QR code de vérification</li>
                        <li>Hash de sécurité</li>
                        <li>Traçabilité complète</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('templateSelect');
    const organisationSelect = document.getElementById('organisationSelect');
    const dossierSelect = document.getElementById('dossierSelect');
    const previewSection = document.getElementById('previewSection');
    const previewContent = document.getElementById('previewContent');
    const submitBtn = document.getElementById('submitBtn');

    // Mise à jour de l'aperçu
    function updatePreview() {
        const templateName = templateSelect.options[templateSelect.selectedIndex]?.text || '';
        const orgName = organisationSelect.options[organisationSelect.selectedIndex]?.text || '';

        if (templateSelect.value && organisationSelect.value) {
            previewSection.classList.remove('d-none');
            previewContent.innerHTML = `
                <p class="mb-1"><strong>Template:</strong> ${templateName}</p>
                <p class="mb-0"><strong>Organisation:</strong> ${orgName}</p>
            `;
        } else {
            previewSection.classList.add('d-none');
        }
    }

    // Chargement des dossiers selon l'organisation
    organisationSelect.addEventListener('change', function() {
        updatePreview();
        
        const orgId = this.value;
        if (!orgId) {
            dossierSelect.innerHTML = '<option value="">Aucun dossier spécifique...</option>';
            return;
        }

        // Charger les dossiers de cette organisation
        fetch(`/admin/organisations/${orgId}/dossiers`)
            .then(response => response.json())
            .then(data => {
                dossierSelect.innerHTML = '<option value="">Aucun dossier spécifique...</option>';
                data.dossiers.forEach(dossier => {
                    const option = document.createElement('option');
                    option.value = dossier.id;
                    option.textContent = `Dossier #${dossier.numero} - ${dossier.statut}`;
                    dossierSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Erreur chargement dossiers:', error);
            });
    });

    templateSelect.addEventListener('change', updatePreview);

    // Validation avant soumission
    document.getElementById('generateForm').addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Génération en cours...';
    });
});
</script>
@endpush
@endsection