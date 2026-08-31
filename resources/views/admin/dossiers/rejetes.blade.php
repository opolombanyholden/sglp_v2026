@extends('layouts.admin')

@section('title', 'Dossiers Rejetés')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-times-circle me-2 text-danger"></i>
                Dossiers Rejetés
                @if(request('type_operation'))
                    <small class="text-muted">— {{ ucfirst(str_replace('_', ' ', request('type_operation'))) }}</small>
                @endif
            </h1>
            <p class="text-muted">Liste des dossiers ayant fait l'objet d'un rejet.</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form method="GET" action="{{ route('admin.dossiers.rejetes') }}" class="d-flex flex-wrap gap-2">
                @if(request('type_operation'))
                    <input type="hidden" name="type_operation" value="{{ request('type_operation') }}">
                @endif
                <input type="text" class="form-control form-control-sm flex-grow-1" name="search"
                       value="{{ request('search') }}" placeholder="Rechercher (numéro, nom, sigle)..." style="max-width:400px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                @if(request('search') || request('type_operation'))
                    <a href="{{ route('admin.dossiers.rejetes') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
                @endif
            </form>
        </div>
        <div class="card-body">
            @if($dossiers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Dossier</th>
                                <th>Organisation</th>
                                <th>Opération</th>
                                <th>Motif rejet</th>
                                <th>Rejeté le</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dossiers as $dossier)
                                <tr>
                                    <td>
                                        <strong>{{ $dossier->numero_dossier }}</strong>
                                        <br><span class="badge bg-danger">Rejeté</span>
                                    </td>
                                    <td>
                                        <strong>{{ $dossier->organisation->nom ?? 'N/A' }}</strong>
                                        @if($dossier->organisation->sigle ?? null)
                                            <br><small class="text-muted">({{ $dossier->organisation->sigle }})</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $dossier->type_operation ?? 'création')) }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($dossier->motif_rejet, 80) ?? '—' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($dossier->updated_at)->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.dossiers.show', $dossier->id) }}"
                                           class="btn btn-outline-primary btn-sm" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">{{ $dossiers->total() }} résultat(s)</small>
                    <div>{{ $dossiers->links() }}</div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucun dossier rejeté</h5>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
