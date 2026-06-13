@extends('layouts.admin')

@section('title', 'Gestion des Templates de Documents')

@section('content')
<div class="container-fluid py-4">
    
    {{-- En-tête --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1">
                <i class="fas fa-file-alt text-primary"></i> Templates de Documents
            </h2>
            <p class="text-muted">Gérer les modèles de documents officiels</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.document-templates.scan') }}" class="btn btn-outline-secondary me-1" title="Détecter les fichiers .blade.php non encore enregistrés">
                <i class="fas fa-search-plus"></i> Importer existants
            </a>
            <a href="{{ route('admin.document-templates.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau Template
            </a>
        </div>
    </div>

    {{-- Alertes --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtres --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.document-templates.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type d'organisation</label>
                        <select name="organisation_type_id" class="form-select">
                            <option value="">Tous les types</option>
                            @foreach($organisationTypes as $type)
                                <option value="{{ $type->id }}" 
                                    {{ request('organisation_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Type de document</label>
                        <select name="type_document" class="form-select">
                            <option value="">Tous les types</option>
                            @foreach($typesDocument as $key => $label)
                                <option value="{{ $key }}" 
                                    {{ request('type_document') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Auto-génération</label>
                        <select name="auto_generate" class="form-select">
                            <option value="">Tous</option>
                            <option value="1" {{ request('auto_generate') == '1' ? 'selected' : '' }}>
                                Oui
                            </option>
                            <option value="0" {{ request('auto_generate') == '0' ? 'selected' : '' }}>
                                Non
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" class="form-control" 
                            placeholder="Code, nom..." 
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>
                </div>

                @if(request()->hasAny(['organisation_type_id', 'type_document', 'auto_generate', 'search']))
                    <div class="mt-3">
                        <a href="{{ route('admin.document-templates.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i> Réinitialiser les filtres
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Liste des templates --}}
    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Liste des templates 
                    <span class="badge bg-primary">{{ $templates->total() }}</span>
                </h5>
                <div>
                    <small class="text-muted">
                        Affichage {{ $templates->firstItem() ?? 0 }} - {{ $templates->lastItem() ?? 0 }} 
                        sur {{ $templates->total() }}
                    </small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Type Org.</th>
                            <th>Type Doc.</th>
                            <th class="text-center">Auto</th>
                            <th class="text-center">QR Code</th>
                            <th class="text-center">Statut</th>
                            <th class="text-center">Documents</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>
                                    <code class="text-primary">{{ $template->code }}</code>
                                </td>
                                <td>
                                    <strong>{{ $template->nom }}</strong>
                                    @if($template->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                    @endif
                                    <br>
                                    @if($template->templateExists())
                                        <small class="text-success"><i class="fas fa-file-code"></i> {{ $template->template_path }}</small>
                                    @else
                                        <small class="text-danger" title="Fichier .blade.php introuvable"><i class="fas fa-exclamation-triangle"></i> {{ $template->template_path }} (fichier manquant)</small>
                                    @endif
                                </td>
                                <td>
                                    @if($template->organisationType)
                                        <span class="badge bg-info">
                                            {{ $template->organisationType->nom }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $template->type_document_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($template->auto_generate)
                                        <i class="fas fa-check-circle text-success" title="Auto-génération activée"></i>
                                    @else
                                        <i class="fas fa-times-circle text-muted" title="Manuel"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($template->has_qr_code)
                                        <i class="fas fa-qrcode text-primary" title="QR Code inclus"></i>
                                    @else
                                        <i class="fas fa-minus text-muted"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($template->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">
                                        {{ $template->generations_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.document-templates.show', $template) }}"
                                           class="btn btn-outline-info"
                                           title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.document-templates.preview', $template) }}"
                                           class="btn btn-outline-secondary"
                                           title="Prévisualiser"
                                           target="_blank">
                                            <i class="fas fa-search"></i>
                                        </a>
                                        <a href="{{ route('admin.document-templates.designer', $template) }}"
                                           class="btn btn-outline-warning"
                                           title="Designer (publipostage)">
                                            <i class="fas fa-drafting-compass"></i>
                                        </a>
                                        <a href="{{ route('admin.document-templates.edit', $template) }}"
                                           class="btn btn-outline-primary"
                                           title="Modifier les métadonnées">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.document-templates.edit-source', $template) }}"
                                           class="btn btn-outline-dark"
                                           title="Éditer le code Blade">
                                            <i class="fas fa-code"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-success"
                                                title="Dupliquer"
                                                data-bs-toggle="modal"
                                                data-bs-target="#duplicateModal-{{ $template->id }}">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <form action="{{ route('admin.document-templates.toggle-status', $template) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn {{ $template->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                                    title="{{ $template->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas {{ $template->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                            </button>
                                        </form>
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                title="Supprimer"
                                                onclick="confirmDelete('{{ $template->id }}', '{{ $template->nom }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <form id="delete-form-{{ $template->id }}"
                                          action="{{ route('admin.document-templates.destroy', $template) }}"
                                          method="POST"
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                    {{-- Modal duplication --}}
                                    <div class="modal fade" id="duplicateModal-{{ $template->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <form action="{{ route('admin.document-templates.duplicate', $template) }}" method="POST">
                                                @csrf
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Dupliquer « {{ $template->nom }} »</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-info small mb-3">
                                                            <i class="fas fa-info-circle"></i>
                                                            Le fichier <code>.blade.php</code> sera créé automatiquement.
                                                            Si vous laissez le chemin vide, il sera calculé à partir du type d'organisation et du type d'opération choisis.
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                                                <input type="text" name="code" class="form-control text-uppercase" required pattern="[A-Z0-9_]+" placeholder="EX: ASSOC_CREATION_COPIE">
                                                                <small class="text-muted">Majuscules, chiffres, underscore uniquement.</small>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                                                <input type="text" name="nom" class="form-control" required value="Copie de {{ $template->nom }}">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Type de document</label>
                                                                <select name="type_document" class="form-select">
                                                                    <option value="">— Inchangé ({{ $template->type_document }}) —</option>
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
                                                            <div class="col-md-4">
                                                                <label class="form-label">Type d'organisation</label>
                                                                <select name="organisation_type_id" class="form-select">
                                                                    <option value="">— Générique —</option>
                                                                    @foreach($organisationTypes as $ot)
                                                                        <option value="{{ $ot->id }}" {{ $template->organisation_type_id == $ot->id ? 'selected' : '' }}>{{ $ot->nom }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Type d'opération</label>
                                                                <select name="operation_type_id" class="form-select">
                                                                    <option value="">— Générique —</option>
                                                                    @foreach($operationTypes as $op)
                                                                        <option value="{{ $op->id }}" {{ $template->operation_type_id == $op->id ? 'selected' : '' }}>{{ $op->libelle ?? $op->code }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Chemin du fichier (optionnel)</label>
                                                                <input type="text" name="new_template_path" class="form-control" placeholder="documents.templates.<org>.<op>.<code> — laisser vide pour auto">
                                                                <small class="text-muted">Caractères autorisés : lettres, chiffres, point, tiret, underscore.</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-success"><i class="fas fa-copy"></i> Dupliquer</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun template trouvé</p>
                                    <a href="{{ route('admin.document-templates.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Créer le premier template
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($templates->hasPages())
            <div class="card-footer">
                {{ $templates->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function confirmDelete(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le template "${name}" ?\n\nCette action est irréversible.`)) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush

@endsection