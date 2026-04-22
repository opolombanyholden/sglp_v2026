@extends('layouts.admin')

@section('title', 'Designer - ' . $documentTemplate->nom)

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">
                <i class="fas fa-drafting-compass text-primary mr-2"></i>
                Designer de template
            </h3>
            <p class="text-muted mb-0">
                <strong>{{ $documentTemplate->nom }}</strong>
                <span class="badge badge-secondary ml-2">{{ $documentTemplate->code }}</span>
            </p>
        </div>
        <div>
            <a href="{{ route('admin.document-templates.edit', $documentTemplate->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Retour
            </a>
            <button type="button" class="btn btn-outline-info" onclick="previewDocument()">
                <i class="fas fa-eye mr-1"></i> Prévisualiser
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Erreurs :</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.document-templates.update-designer', $documentTemplate->id) }}" id="designerForm">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Panneau principal : éditeur --}}
            <div class="col-lg-9">

                {{-- En-tête du document --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-heading mr-2"></i> En-tête du document</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="header_text" id="header_text" rows="3">{{ old('header_text', $documentTemplate->header_text) }}</textarea>
                    </div>
                </div>

                {{-- Corps du document (publipostage) --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-file-alt mr-2"></i> Corps du document</h6>
                        <small><i class="fas fa-info-circle mr-1"></i> Cliquez sur une variable dans le panneau de droite pour l'insérer</small>
                    </div>
                    <div class="card-body">
                        <textarea name="body_content" id="body_content" rows="15">{{ old('body_content', $documentTemplate->body_content) }}</textarea>
                    </div>
                </div>

                {{-- Bloc signature --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-signature mr-2"></i> Bloc signature</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="signature_text" id="signature_text" rows="3">{{ old('signature_text', $documentTemplate->signature_text) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Panneau latéral --}}
            <div class="col-lg-3">
                {{-- Variables --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-tags mr-2"></i> Variables</h6>
                        <button type="button" class="btn btn-sm btn-light" onclick="addVariable()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                        <div id="variables-list">
                            @php
                                $vars = old('variables', $documentTemplate->variables ?? []);
                            @endphp
                            @foreach($vars as $i => $var)
                                @if(is_array($var))
                                    <div class="variable-item mb-2 p-2 border rounded bg-light" data-index="{{ $i }}">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <code class="text-primary cursor-pointer" onclick="insertVariable('{{ $var['key'] ?? '' }}')" title="Cliquer pour insérer">
                                                @{{ {!! $var['key'] ?? '' !!} }}
                                            </code>
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeVariable(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <input type="text" name="variables[{{ $i }}][key]" class="form-control form-control-sm mb-1" placeholder="cle_variable" value="{{ $var['key'] ?? '' }}" pattern="[a-z0-9_]+" required>
                                        <input type="text" name="variables[{{ $i }}][label]" class="form-control form-control-sm mb-1" placeholder="Libellé" value="{{ $var['label'] ?? '' }}" required>
                                        <select name="variables[{{ $i }}][type]" class="form-control form-control-sm mb-1">
                                            @foreach(['text' => 'Texte', 'textarea' => 'Texte long', 'number' => 'Nombre', 'date' => 'Date', 'email' => 'Email', 'url' => 'URL'] as $tv => $tl)
                                                <option value="{{ $tv }}" {{ ($var['type'] ?? 'text') === $tv ? 'selected' : '' }}>{{ $tl }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="variables[{{ $i }}][default]" class="form-control form-control-sm mb-1" placeholder="Valeur par défaut" value="{{ $var['default'] ?? '' }}">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="variables[{{ $i }}][required]" value="1" {{ !empty($var['required']) ? 'checked' : '' }}>
                                            <label class="form-check-label small">Obligatoire</label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-lightbulb mr-1"></i> Cliquez sur une variable pour l'insérer dans le corps
                        </small>
                    </div>
                </div>

                {{-- Configuration page --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-cog mr-2"></i> Configuration page</h6>
                    </div>
                    <div class="card-body">
                        @php $pc = $documentTemplate->page_config ?? []; @endphp
                        <div class="form-group mb-2">
                            <label class="small mb-0">Format</label>
                            <select name="page_config[format]" class="form-control form-control-sm">
                                @foreach(['A4', 'A5', 'Letter', 'Legal'] as $f)
                                    <option value="{{ $f }}" {{ ($pc['format'] ?? 'A4') === $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small mb-0">Orientation</label>
                            <select name="page_config[orientation]" class="form-control form-control-sm">
                                <option value="portrait" {{ ($pc['orientation'] ?? 'portrait') === 'portrait' ? 'selected' : '' }}>Portrait</option>
                                <option value="landscape" {{ ($pc['orientation'] ?? '') === 'landscape' ? 'selected' : '' }}>Paysage</option>
                            </select>
                        </div>
                        <label class="small mb-1">Marges (mm)</label>
                        <div class="row no-gutters mb-1">
                            <div class="col-6 pr-1">
                                <input type="number" name="page_config[margin_top]" class="form-control form-control-sm" placeholder="Haut" value="{{ $pc['margin_top'] ?? 20 }}" min="0" max="100">
                            </div>
                            <div class="col-6 pl-1">
                                <input type="number" name="page_config[margin_right]" class="form-control form-control-sm" placeholder="Droite" value="{{ $pc['margin_right'] ?? 20 }}" min="0" max="100">
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6 pr-1">
                                <input type="number" name="page_config[margin_bottom]" class="form-control form-control-sm" placeholder="Bas" value="{{ $pc['margin_bottom'] ?? 20 }}" min="0" max="100">
                            </div>
                            <div class="col-6 pl-1">
                                <input type="number" name="page_config[margin_left]" class="form-control form-control-sm" placeholder="Gauche" value="{{ $pc['margin_left'] ?? 20 }}" min="0" max="100">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Options --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-sliders-h mr-2"></i> Options</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="has_qr_code" value="1" id="has_qr_code" {{ $documentTemplate->has_qr_code ? 'checked' : '' }}>
                            <label class="form-check-label small" for="has_qr_code">QR Code</label>
                        </div>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="has_watermark" value="1" id="has_watermark" {{ $documentTemplate->has_watermark ? 'checked' : '' }}>
                            <label class="form-check-label small" for="has_watermark">Filigrane</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_signature" value="1" id="has_signature" {{ $documentTemplate->has_signature ? 'checked' : '' }}>
                            <label class="form-check-label small" for="has_signature">Signature</label>
                        </div>
                    </div>
                </div>

                {{-- Boutons --}}
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save mr-1"></i> Enregistrer
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Modal de prévisualisation --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye mr-2"></i> Prévisualisation</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" style="height: 75vh;">
                <iframe id="previewFrame" style="width:100%; height:100%; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

{{-- CKEditor pour le WYSIWYG --}}
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

<script>
    let bodyEditor, headerEditor, signatureEditor;
    let variableIndex = {{ count($vars ?? []) }};

    document.addEventListener('DOMContentLoaded', function() {
        const baseConfig = {
            toolbar: ['heading','|','bold','italic','underline','|','bulletedList','numberedList','|','alignment','|','link','insertTable','blockQuote','|','undo','redo']
        };

        ClassicEditor.create(document.getElementById('body_content'), baseConfig)
            .then(editor => { bodyEditor = editor; })
            .catch(err => console.error(err));

        ClassicEditor.create(document.getElementById('header_text'), {
            toolbar: ['bold','italic','alignment','|','undo','redo']
        }).then(editor => { headerEditor = editor; })
          .catch(err => console.error(err));

        ClassicEditor.create(document.getElementById('signature_text'), {
            toolbar: ['bold','italic','alignment','|','undo','redo']
        }).then(editor => { signatureEditor = editor; })
          .catch(err => console.error(err));
    });

    function insertVariable(key) {
        if (!key || !bodyEditor) return;
        const text = '\u007B\u007B' + key + '\u007D\u007D';
        bodyEditor.model.change(writer => {
            bodyEditor.model.insertContent(writer.createText(text));
        });
    }

    function addVariable() {
        const i = variableIndex++;
        const html = `
            <div class="variable-item mb-2 p-2 border rounded bg-light" data-index="${i}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <code class="text-primary">nouvelle</code>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeVariable(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="text" name="variables[${i}][key]" class="form-control form-control-sm mb-1" placeholder="cle_variable" pattern="[a-z0-9_]+" required>
                <input type="text" name="variables[${i}][label]" class="form-control form-control-sm mb-1" placeholder="Libellé" required>
                <select name="variables[${i}][type]" class="form-control form-control-sm mb-1">
                    <option value="text">Texte</option>
                    <option value="textarea">Texte long</option>
                    <option value="number">Nombre</option>
                    <option value="date">Date</option>
                    <option value="email">Email</option>
                    <option value="url">URL</option>
                </select>
                <input type="text" name="variables[${i}][default]" class="form-control form-control-sm mb-1" placeholder="Valeur par défaut">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="variables[${i}][required]" value="1">
                    <label class="form-check-label small">Obligatoire</label>
                </div>
            </div>`;
        document.getElementById('variables-list').insertAdjacentHTML('beforeend', html);
    }

    function removeVariable(btn) {
        btn.closest('.variable-item').remove();
    }

    function previewDocument() {
        if (bodyEditor) bodyEditor.updateSourceElement();
        if (headerEditor) headerEditor.updateSourceElement();
        if (signatureEditor) signatureEditor.updateSourceElement();

        // Soumettre d'abord le formulaire en AJAX pour sauvegarder
        const form = document.getElementById('designerForm');
        const fd = new FormData(form);
        fd.append('_method', 'PUT');

        // Générer les données de test (valeurs par défaut)
        const testData = {};
        document.querySelectorAll('.variable-item').forEach(item => {
            const key = item.querySelector('input[name*="[key]"]')?.value;
            const def = item.querySelector('input[name*="[default]"]')?.value;
            if (key) testData[key] = def || '[' + key + ']';
        });

        fetch('{{ route("admin.document-templates.preview-designer", $documentTemplate->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ data: testData })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const iframe = document.getElementById('previewFrame');
                iframe.srcdoc = res.html;
                $('#previewModal').modal('show');
            } else {
                alert('Erreur : ' + (res.message || 'Inconnue'));
            }
        })
        .catch(err => alert('Erreur réseau : ' + err.message));
    }

    // Synchroniser les éditeurs avant soumission
    document.getElementById('designerForm').addEventListener('submit', function() {
        if (bodyEditor) bodyEditor.updateSourceElement();
        if (headerEditor) headerEditor.updateSourceElement();
        if (signatureEditor) signatureEditor.updateSourceElement();
    });
</script>

<style>
    .variable-item code { font-size: 0.7rem; }
    .cursor-pointer { cursor: pointer; }
    .ck-editor__editable_inline { min-height: 300px; }
    #body_content + .ck-editor .ck-editor__editable_inline { min-height: 400px; }
</style>
@endsection
