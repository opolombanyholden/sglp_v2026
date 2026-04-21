@extends('layouts.admin')

@section('title', 'Correction — Examen ' . $dossier->numero_dossier)

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.corrections.index') }}">Corrections</a></li>
                <li class="breadcrumb-item active">{{ $dossier->numero_dossier }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="h3 mb-1">Examen de la correction</h1>
                <p class="text-muted mb-0">
                    Dossier <code>{{ $dossier->numero_dossier }}</code>
                    @if($dossier->parentDossier)
                        — Corrige <code>{{ $dossier->parentDossier->numero_dossier }}</code>
                    @endif
                </p>
            </div>
            <div>
                @switch($dossier->statut)
                    @case('brouillon')
                        <span class="badge bg-secondary fs-6">Brouillon</span>
                        @break
                    @case('soumis')
                        <span class="badge bg-warning text-dark fs-6">En attente de validation</span>
                        @break
                    @case('accepte')
                        <span class="badge bg-success fs-6">Approuvé</span>
                        @break
                    @case('rejete')
                        <span class="badge bg-danger fs-6">Rejeté</span>
                        @break
                @endswitch
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif

    {{-- Résumé organisation --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Organisation concernée</h5>
        </div>
        <div class="card-body">
            @if($dossier->organisation)
                <div class="row">
                    <div class="col-md-4">
                        <strong>{{ $dossier->organisation->nom }}</strong>
                        @if($dossier->organisation->sigle)
                            <span class="text-muted">({{ $dossier->organisation->sigle }})</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-file-alt me-1 text-muted"></i>
                        {{ $dossier->organisation->numero_recepisse ?? '—' }}
                    </div>
                    <div class="col-md-4">
                        @if($dossier->organisation->organisationType)
                            <i class="fas fa-tag me-1 text-muted"></i>
                            {{ $dossier->organisation->organisationType->libelle }}
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Motif global --}}
    @if($dossier->donnees_supplementaires && isset($dossier->donnees_supplementaires['motif_global']))
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Motif de la correction</h5>
        </div>
        <div class="card-body">
            <p class="mb-0">{{ $dossier->donnees_supplementaires['motif_global'] }}</p>
        </div>
    </div>
    @endif

    {{-- Tableau des corrections --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Détail des corrections ({{ $dossier->corrections->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Catégorie</th>
                        <th>Champ</th>
                        <th>Ancienne valeur</th>
                        <th>Nouvelle valeur</th>
                        <th>Motif</th>
                        <th>Par</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dossier->corrections as $correction)
                    <tr>
                        <td><span class="badge bg-light text-dark">{{ $correction->getCategorieLabel() }}</span></td>
                        <td><code>{{ $correction->champ_corrige }}</code></td>
                        <td>
                            @if($correction->ancienne_valeur)
                                <span class="text-danger"><del>{{ Str::limit($correction->ancienne_valeur, 60) }}</del></span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-success fw-bold">{{ Str::limit($correction->nouvelle_valeur, 60) }}</span>
                        </td>
                        <td><em class="small">{{ $correction->motif_correction }}</em></td>
                        <td>
                            @if($correction->correctedByUser)
                                {{ $correction->correctedByUser->name }}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions de validation --}}
    @if(in_array($dossier->statut, ['soumis', 'en_cours']))
    <div class="row mb-4">
        {{-- Approuver --}}
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success bg-opacity-10">
                    <h5 class="mb-0 text-success"><i class="fas fa-check-circle me-2"></i>Approuver la correction</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        L'approbation va :
                        <ul class="mb-0 mt-1">
                            <li>Appliquer les corrections sur l'organisation</li>
                            <li>Invalider les documents existants (récépissés)</li>
                            <li>Les nouveaux documents devront être régénérés</li>
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('admin.corrections.approve', $dossier) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Commentaire (optionnel)</label>
                            <textarea name="commentaire" class="form-control" rows="2"
                                      placeholder="Commentaire de validation..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Confirmer l\'approbation ? Les documents existants seront invalidés.')">
                            <i class="fas fa-check me-1"></i>Approuver et appliquer les corrections
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Rejeter --}}
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-header bg-danger bg-opacity-10">
                    <h5 class="mb-0 text-danger"><i class="fas fa-times-circle me-2"></i>Rejeter la correction</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.corrections.reject', $dossier) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                            <textarea name="motif_rejet" class="form-control" rows="3" required
                                      placeholder="Raison du rejet de cette correction..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-times me-1"></i>Rejeter la correction
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Résultat si déjà traité --}}
    @if($dossier->statut === 'rejete' && $dossier->motif_rejet)
    <div class="alert alert-danger">
        <h5><i class="fas fa-times-circle me-2"></i>Correction rejetée</h5>
        <p class="mb-0">{{ $dossier->motif_rejet }}</p>
    </div>
    @endif

    @if($dossier->statut === 'accepte')
    <div class="alert alert-success">
        <h5><i class="fas fa-check-circle me-2"></i>Correction approuvée</h5>
        <p class="mb-0">Les corrections ont été appliquées le {{ $dossier->validated_at ? $dossier->validated_at->format('d/m/Y à H:i') : $dossier->updated_at->format('d/m/Y à H:i') }}.</p>
    </div>
    @endif

    {{-- Historique des opérations --}}
    @if($dossier->operations->count())
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique</h5>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @foreach($dossier->operations as $op)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ $op->type_operation_label ?? $op->type_operation }}</strong>
                            @if($op->description)
                                — {{ Str::limit($op->description, 100) }}
                            @endif
                        </div>
                        <small class="text-muted">{{ $op->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>
@endsection
