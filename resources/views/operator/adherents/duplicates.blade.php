@extends('layouts.operator')

@section('title', 'Doublons - ' . ($organisation->nom ?? ''))

@section('page-title', 'Détection des doublons')

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
                            <li class="breadcrumb-item active text-white">Doublons</li>
                        </ol>
                    </nav>
                    <h2 class="mb-1"><i class="fas fa-copy mr-2"></i>Doublons détectés</h2>
                    <p class="mb-0" style="opacity:.9">{{ $organisation->nom }}</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    @if(isset($duplicates) && count($duplicates) > 0)
        @foreach($duplicates as $nip => $group)
        <div class="card border-left-danger shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                    NIP : <code>{{ $nip ?: 'Sans NIP' }}</code>
                    <span class="badge badge-danger ml-2">{{ count($group) }} occurrences</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Nom & Prénom</th>
                                <th>Téléphone</th>
                                <th>Fonction</th>
                                <th>Statut</th>
                                <th>Date adhésion</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group as $adherent)
                            <tr>
                                <td>{{ $adherent->id ?? '-' }}</td>
                                <td><strong>{{ $adherent->nom ?? '' }}</strong> {{ $adherent->prenom ?? '' }}</td>
                                <td>{{ $adherent->telephone ?? '-' }}</td>
                                <td>{{ $adherent->fonction ?? 'Membre' }}</td>
                                <td>
                                    @if($adherent->is_active ?? false)
                                        <span class="badge badge-success">Actif</span>
                                    @else
                                        <span class="badge badge-danger">Inactif</span>
                                    @endif
                                </td>
                                <td>{{ isset($adherent->date_adhesion) ? \Carbon\Carbon::parse($adherent->date_adhesion)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('operator.adherents.show', [$organisation, $adherent]) }}" class="btn btn-outline-primary btn-sm" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="text-muted">Aucun doublon détecté</h5>
                <p class="text-muted">Tous les NIP de cette organisation sont uniques.</p>
                <a href="{{ route('operator.adherents.index', $organisation) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Retour aux adhérents
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
