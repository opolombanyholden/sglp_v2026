@extends('layouts.admin')

@section('title', 'Éditer le code source - ' . $documentTemplate->nom)

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">
                <i class="fas fa-code text-primary mr-2"></i>
                Éditeur de code source
            </h3>
            <p class="text-muted mb-0">
                Template : <strong>{{ $documentTemplate->nom }}</strong>
                <span class="badge badge-secondary ml-2">{{ $documentTemplate->code }}</span>
            </p>
        </div>
        <div>
            <a href="{{ route('admin.document-templates.edit', $documentTemplate->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Retour à l'édition
            </a>
            <a href="{{ route('admin.document-templates.preview', $documentTemplate->id) }}" class="btn btn-outline-info">
                <i class="fas fa-eye mr-1"></i> Prévisualiser
            </a>
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

    {{-- Informations du fichier --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <small class="text-muted d-block">
                        <i class="fas fa-folder mr-1"></i>
                        <strong>Chemin Blade :</strong>
                        <code>{{ $documentTemplate->template_path }}</code>
                    </small>
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-file-code mr-1"></i>
                        <strong>Fichier :</strong>
                        <code style="font-size: 0.8rem;">{{ $absolutePath }}</code>
                    </small>
                </div>
                <div class="col-md-4 text-md-right">
                    @if($exists)
                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Fichier existant</span>
                    @else
                        <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i> Fichier manquant (sera créé)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            {{-- Éditeur de code --}}
            <form method="POST" action="{{ route('admin.document-templates.update-source', $documentTemplate->id) }}" id="sourceForm">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                        <span><i class="fas fa-code mr-2"></i> Code source Blade</span>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleWrap()" title="Activer/désactiver le retour à la ligne">
                                <i class="fas fa-text-width"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="increaseFontSize()" title="Agrandir">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="decreaseFontSize()" title="Réduire">
                                <i class="fas fa-search-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <textarea name="content" id="code-editor">{{ $content }}</textarea>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Une sauvegarde automatique sera créée avant toute modification
                            </small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                <i class="fas fa-times mr-1"></i> Annuler
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-3">
            {{-- Panneau latéral : duplication + aide --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-copy mr-2"></i> Dupliquer ce template</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Créez une copie de ce template pour en créer un nouveau basé sur celui-ci.
                    </p>
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#duplicateModal">
                        <i class="fas fa-clone mr-1"></i> Dupliquer le template
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-lightbulb mr-2"></i> Variables disponibles</h6>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if(!empty($documentTemplate->variables) && is_array($documentTemplate->variables))
                        <ul class="list-unstyled small mb-0">
                            @foreach($documentTemplate->variables as $var)
                                @php
                                    $varName = is_array($var) ? ($var['key'] ?? $var['name'] ?? '') : $var;
                                @endphp
                                <li class="mb-1">
                                    <code style="font-size: 0.75rem;">@{{ {!! '$' . $varName !!} }}</code>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="small text-muted mb-0">Aucune variable définie pour ce template.</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-keyboard mr-2"></i> Raccourcis</h6>
                </div>
                <div class="card-body py-2">
                    <small class="d-block mb-1"><kbd>Ctrl+S</kbd> Enregistrer</small>
                    <small class="d-block mb-1"><kbd>Ctrl+F</kbd> Rechercher</small>
                    <small class="d-block"><kbd>Ctrl+Z</kbd> Annuler</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal duplication --}}
<div class="modal fade" id="duplicateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('admin.document-templates.duplicate', $documentTemplate->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-clone mr-2"></i> Dupliquer le template</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Le nouveau template sera créé <strong>inactif</strong> par défaut. Vous pourrez l'activer après vérification.
                    </div>

                    <div class="form-group">
                        <label>Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control text-uppercase"
                               placeholder="Ex: {{ $documentTemplate->code }}_COPY"
                               value="{{ $documentTemplate->code }}_COPY"
                               pattern="[A-Z0-9_]+" required>
                        <small class="form-text text-muted">Majuscules, chiffres et underscores uniquement</small>
                    </div>

                    <div class="form-group">
                        <label>Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control"
                               value="{{ $documentTemplate->nom }} (copie)" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label>Nouveau chemin du fichier Blade (optionnel)</label>
                        <input type="text" name="new_template_path" class="form-control"
                               placeholder="Laisser vide pour génération automatique">
                        <small class="form-text text-muted">
                            Exemple : <code>documents.templates.association.recepisse_copy</code><br>
                            Si vide, un chemin sera généré à partir du code.
                        </small>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="copy_file" value="1" id="copyFile" checked>
                        <label class="form-check-label" for="copyFile">
                            Dupliquer aussi le fichier source Blade
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-clone mr-1"></i> Dupliquer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- CodeMirror pour l'éditeur --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/search/search.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/search/searchcursor.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/dialog/dialog.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/dialog/dialog.min.css">

<script>
    let editor;
    let currentFontSize = 14;
    let wrapEnabled = true;

    document.addEventListener('DOMContentLoaded', function() {
        editor = CodeMirror.fromTextArea(document.getElementById('code-editor'), {
            mode: 'application/x-httpd-php',
            theme: 'dracula',
            lineNumbers: true,
            matchBrackets: true,
            autoCloseBrackets: true,
            indentUnit: 4,
            tabSize: 4,
            lineWrapping: wrapEnabled,
            extraKeys: {
                'Ctrl-S': function(cm) {
                    document.getElementById('sourceForm').submit();
                    return false;
                },
                'Cmd-S': function(cm) {
                    document.getElementById('sourceForm').submit();
                    return false;
                }
            }
        });

        editor.setSize('100%', '70vh');

        // Sync avant soumission
        document.getElementById('sourceForm').addEventListener('submit', function() {
            editor.save();
        });
    });

    function toggleWrap() {
        wrapEnabled = !wrapEnabled;
        if (editor) editor.setOption('lineWrapping', wrapEnabled);
    }

    function increaseFontSize() {
        currentFontSize = Math.min(currentFontSize + 2, 24);
        applyFontSize();
    }

    function decreaseFontSize() {
        currentFontSize = Math.max(currentFontSize - 2, 10);
        applyFontSize();
    }

    function applyFontSize() {
        const el = document.querySelector('.CodeMirror');
        if (el) el.style.fontSize = currentFontSize + 'px';
        if (editor) editor.refresh();
    }

    // Empêcher Ctrl+S de sauvegarder la page
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            document.getElementById('sourceForm').submit();
        }
    });
</script>

<style>
    .CodeMirror {
        height: 70vh !important;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 14px;
    }

    .CodeMirror-linenumber {
        padding: 0 8px 0 5px;
    }

    kbd {
        background: #e9ecef;
        border: 1px solid #adb5bd;
        border-radius: 3px;
        color: #495057;
        font-size: 0.75rem;
        padding: 1px 4px;
    }
</style>
@endsection
