@extends('layouts.admin')

@section('title', 'Consultation des Anomalies - ' . $dossier->numero_dossier)

@section('content')
<div class="container-fluid py-4">

    {{-- En-tête de page --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Anomalies des Adh&eacute;rents
                    </h4>
                    <p class="text-muted mb-0">
                        Organisation : <strong>{{ $organisation->nom }}</strong>
                        @if($organisation->sigle)
                            ({{ $organisation->sigle }})
                        @endif
                        <br>
                        Dossier : <strong>{{ $dossier->numero_dossier }}</strong>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.dossiers.rapport-anomalies', $dossier->id) }}"
                       class="btn btn-danger">
                        <i class="fas fa-file-pdf me-2"></i>
                        T&eacute;l&eacute;charger PDF
                    </a>
                    <a href="{{ route('admin.dossiers.show', $dossier->id) }}"
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour au dossier
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Cartes statistiques --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center py-4">
                    <h2 class="mb-1">{{ $stats['total'] ?? 0 }}</h2>
                    <small class="text-white-50">Total Adh&eacute;rents</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center py-4">
                    <h2 class="mb-1">{{ $stats['valides'] ?? 0 }}</h2>
                    <small class="text-white-50">Adh&eacute;rents Valides</small>
                    @if(($stats['pourcentage_valides'] ?? 0) > 0)
                        <div class="mt-1">
                            <span class="badge bg-light text-success">
                                {{ $stats['pourcentage_valides'] }}%
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center py-4">
                    <h2 class="mb-1">{{ $stats['avec_anomalies'] ?? 0 }}</h2>
                    <small>Avec Anomalies</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center py-4">
                    <h2 class="mb-1">{{ $stats['anomalies_critiques'] ?? 0 }}</h2>
                    <small class="text-white-50">Anomalies Critiques</small>
                </div>
            </div>
        </div>
    </div>

    {{-- R&eacute;partition par type --}}
    @if(!empty($stats['par_type']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        R&eacute;partition par Type d'Anomalie
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($stats['par_type'] as $type => $count)
                            <span class="badge bg-secondary fs-6">
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                                <span class="badge bg-light text-dark ms-1">{{ $count }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Liste des anomalies --}}
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Liste des Anomalies
                <span class="badge bg-secondary ms-2">{{ $anomalies->total() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            @if($anomalies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%">Adh&eacute;rent</th>
                                <th style="width: 12%">NIP</th>
                                <th style="width: 15%">Type d'anomalie</th>
                                <th style="width: 30%">Description</th>
                                <th style="width: 10%">Priorit&eacute;</th>
                                <th style="width: 8%">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($anomalies as $anomalie)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ $anomalie->civilite ?? '' }}
                                            {{ $anomalie->prenom ?? '' }}
                                            {{ $anomalie->nom ?? 'N/A' }}
                                        </strong>
                                    </td>
                                    <td>
                                        @if($anomalie->nip)
                                            <code class="bg-light p-1 rounded">{{ $anomalie->nip }}</code>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $anomalie->type_anomalie ?? 'inconnu')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $anomalie->description ?? $anomalie->message ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @php
                                            $priorite = $anomalie->priorite ?? 'normale';
                                            $prioriteClass = match($priorite) {
                                                'critique' => 'bg-danger',
                                                'haute' => 'bg-warning text-dark',
                                                'normale' => 'bg-info',
                                                'basse' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $prioriteClass }}">
                                            {{ ucfirst($priorite) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($anomalie->created_at)->format('d/m/Y') }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($anomalies->hasPages())
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Affichage de {{ $anomalies->firstItem() }} &agrave; {{ $anomalies->lastItem() }}
                                sur {{ $anomalies->total() }} anomalies
                            </small>
                            {{ $anomalies->links() }}
                        </div>
                    </div>
                @endif

            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h5 class="text-success">Aucune anomalie d&eacute;tect&eacute;e</h5>
                    <p class="text-muted mb-0">
                        Tous les adh&eacute;rents de cette organisation sont valides.
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
