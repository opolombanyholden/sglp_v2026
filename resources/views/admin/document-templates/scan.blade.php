@extends('layouts.admin')

@section('title', 'Importer templates existants')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1">
                <i class="fas fa-search-plus text-primary"></i> Importer des templates existants
            </h2>
            <p class="text-muted">
                Fichiers <code>.blade.php</code> détectés dans
                <code>resources/views/documents/templates/</code> mais non encore enregistrés en base de données.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.document-templates.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(count($orphans) === 0)
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Tous les fichiers physiques sont déjà enregistrés en base. Rien à importer.
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            {{ count($orphans) }} fichier(s) physique(s) non enregistré(s). Remplissez le formulaire pour chacun et cliquez « Enregistrer ».
        </div>

        @foreach($orphans as $orphan)
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <i class="fas fa-file-code"></i>
                    <code>{{ $orphan['dotted'] }}</code>
                    <small class="text-muted ms-2">
                        {{ number_format($orphan['size'] / 1024, 1) }} Ko · modifié le {{ $orphan['modified_at'] }}
                    </small>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.document-templates.scan.register') }}" method="POST">
                        @csrf
                        <input type="hidden" name="template_path" value="{{ $orphan['dotted'] }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control text-uppercase" required
                                       pattern="[A-Z0-9_]+" placeholder="EX: ASSOC_MODIF_RECEP_PROV">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nom lisible <span class="text-danger">*</span></label>
                                <input type="text" name="nom" class="form-control" required
                                       placeholder="Ex: Récépissé provisoire de modification">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type de document <span class="text-danger">*</span></label>
                                <select name="type_document" class="form-select" required>
                                    <option value="">— Choisir —</option>
                                    <option value="recepisse_provisoire">Récépissé provisoire</option>
                                    <option value="recepisse_definitif">Récépissé définitif</option>
                                    <option value="recepisse_enregistrement">Récépissé d'enregistrement</option>
                                    <option value="accuse_reception">Accusé de réception</option>
                                    <option value="attestation">Attestation</option>
                                    <option value="certificat">Certificat</option>
                                    <option value="notification_rejet">Notification de rejet</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type d'organisation</label>
                                <select name="organisation_type_id" class="form-select">
                                    <option value="">— Tous / Générique —</option>
                                    @foreach($organisationTypes as $ot)
                                        <option value="{{ $ot->id }}">{{ $ot->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type d'opération</label>
                                <select name="operation_type_id" class="form-select">
                                    <option value="">— Générique —</option>
                                    @foreach($operationTypes as $op)
                                        <option value="{{ $op->id }}">{{ $op->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer ce template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
