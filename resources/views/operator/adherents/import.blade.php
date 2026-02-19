@extends('layouts.operator')

@section('title', 'Importer des adhérents - ' . ($organisation->nom ?? ''))

@section('page-title', 'Import adhérents')

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
                            <li class="breadcrumb-item active text-white">Importer</li>
                        </ol>
                    </nav>
                    <h2 class="mb-1"><i class="fas fa-file-import mr-2"></i>Importer des adhérents</h2>
                    <p class="mb-0" style="opacity:.9">{{ $organisation->nom }}</p>
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
            {{-- Formulaire d'import --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-upload mr-2 text-primary"></i>Charger un fichier</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('operator.adherents.import', $organisation) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="file">Fichier CSV ou Excel</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('file') is-invalid @enderror" id="file" name="file" accept=".csv,.xlsx,.xls" required>
                                <label class="custom-file-label" for="file" data-browse="Parcourir">Choisir un fichier...</label>
                                @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <small class="form-text text-muted">Formats acceptés : CSV, XLSX, XLS. Taille max : 5 Mo.</small>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload mr-1"></i>Lancer l'import
                        </button>
                        <a href="{{ route('operator.adherents.import.template', $organisation) }}" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-download mr-1"></i>Télécharger le modèle CSV
                        </a>
                    </form>
                </div>
            </div>

            {{-- Résultats d'import --}}
            @if(session('import_result'))
            @php $result = session('import_result'); @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar mr-2 text-info"></i>Résultat de l'import</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="text-success mb-0">{{ $result['summary']['success'] ?? 0 }}</h3>
                                <small class="text-muted">Importés</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="text-danger mb-0">{{ $result['summary']['errors'] ?? 0 }}</h3>
                                <small class="text-muted">Erreurs</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h3 class="text-warning mb-0">{{ $result['summary']['duplicates'] ?? 0 }}</h3>
                                <small class="text-muted">Doublons</small>
                            </div>
                        </div>
                    </div>

                    @if(!empty($result['errors']))
                    <h6 class="text-danger"><i class="fas fa-times-circle mr-1"></i>Détail des erreurs</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Ligne</th>
                                    <th>Erreur</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($result['errors'], 0, 20) as $error)
                                <tr>
                                    <td>{{ $error['line'] ?? '-' }}</td>
                                    <td><small>{{ $error['message'] ?? '-' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($result['errors']) > 20)
                        <p class="text-muted small">... et {{ count($result['errors']) - 20 }} autres erreurs</p>
                    @endif
                    @endif
                </div>
            </div>
            @endif

            {{-- Historique imports --}}
            @if(isset($importHistory) && count($importHistory) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history mr-2 text-secondary"></i>Historique des imports</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Fichier</th>
                                    <th>Importés</th>
                                    <th>Erreurs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($importHistory as $import)
                                <tr>
                                    <td><small>{{ $import->created_at ? $import->created_at->format('d/m/Y H:i') : '-' }}</small></td>
                                    <td><small>{{ $import->filename ?? '-' }}</small></td>
                                    <td><span class="badge badge-success">{{ $import->success_count ?? 0 }}</span></td>
                                    <td><span class="badge badge-danger">{{ $import->error_count ?? 0 }}</span></td>
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
            {{-- Instructions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle mr-2 text-info"></i>Instructions</h6>
                </div>
                <div class="card-body">
                    <ol class="small text-muted mb-0">
                        <li class="mb-2">Téléchargez le modèle CSV</li>
                        <li class="mb-2">Remplissez les colonnes requises (nom, prénom, fonction)</li>
                        <li class="mb-2">Enregistrez au format CSV (séparateur : point-virgule ou virgule)</li>
                        <li class="mb-2">Importez le fichier via le formulaire</li>
                        <li>Les anomalies seront détectées automatiquement pour chaque adhérent</li>
                    </ol>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-columns mr-2 text-secondary"></i>Colonnes attendues</h6>
                </div>
                <div class="card-body">
                    <ul class="small text-muted mb-0">
                        <li><strong>nip</strong> - Numéro d'identification</li>
                        <li><strong>nom</strong> <span class="text-danger">*</span></li>
                        <li><strong>prenom</strong> <span class="text-danger">*</span></li>
                        <li><strong>date_naissance</strong> - Format YYYY-MM-DD</li>
                        <li><strong>lieu_naissance</strong></li>
                        <li><strong>sexe</strong> - M ou F</li>
                        <li><strong>telephone</strong></li>
                        <li><strong>email</strong></li>
                        <li><strong>profession</strong></li>
                        <li><strong>fonction</strong> <span class="text-danger">*</span></li>
                        <li><strong>adresse</strong></li>
                        <li><strong>province</strong></li>
                        <li><strong>departement</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Choisir un fichier...';
    e.target.nextElementSibling.textContent = fileName;
});
</script>
@endpush
@endsection
