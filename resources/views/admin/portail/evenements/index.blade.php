@extends('layouts.admin')
@section('title', 'Événements - Portail')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
                <i class="fas fa-calendar-alt mr-2" style="color:#009e3f;"></i>Calendrier & Événements
            </h1>
            <p class="text-muted mb-0">Gérez les événements et échéances du portail.</p>
        </div>
        <a href="{{ route('admin.portail.evenements.create') }}" class="btn btn-success"><i class="fas fa-plus mr-1"></i> Nouvel événement</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Rechercher..." value="{{ request('search') }}">
                <select name="type" class="form-control form-control-sm mr-2">
                    <option value="">Tous types</option>
                    <option value="echeance" {{ request('type') == 'echeance' ? 'selected' : '' }}>Échéance</option>
                    <option value="formation" {{ request('type') == 'formation' ? 'selected' : '' }}>Formation</option>
                    <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="evenement" {{ request('type') == 'evenement' ? 'selected' : '' }}>Événement</option>
                </select>
                <button class="btn btn-primary btn-sm mr-2"><i class="fas fa-search mr-1"></i>Filtrer</button>
                <a href="{{ route('admin.portail.evenements.index') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Titre</th><th>Type</th><th>Date début</th><th>Date fin</th><th>Lieu</th><th>Important</th><th>Actif</th><th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($evenements as $ev)
                        @php
                            $typeColors = ['echeance'=>'danger','formation'=>'primary','maintenance'=>'warning','evenement'=>'success'];
                            $typeLabels = ['echeance'=>'Échéance','formation'=>'Formation','maintenance'=>'Maintenance','evenement'=>'Événement'];
                        @endphp
                        <tr>
                            <td><strong>{{ Str::limit($ev->titre, 55) }}</strong></td>
                            <td><span class="badge badge-{{ $typeColors[$ev->type] ?? 'secondary' }}">{{ $typeLabels[$ev->type] ?? $ev->type }}</span></td>
                            <td>{{ $ev->date_debut ? $ev->date_debut->format('d/m/Y') : '-' }}</td>
                            <td>{{ $ev->date_fin ? $ev->date_fin->format('d/m/Y') : '-' }}</td>
                            <td>{{ Str::limit($ev->lieu ?? '-', 30) }}</td>
                            <td>
                                @if($ev->est_important)<i class="fas fa-exclamation-triangle text-warning" title="Important"></i>
                                @else<span class="text-muted">—</span>@endif
                            </td>
                            <td>
                                @if($ev->est_actif)<span class="badge badge-success">Oui</span>
                                @else<span class="badge badge-secondary">Non</span>@endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.portail.evenements.edit', $ev) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.portail.evenements.destroy', $ev) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cet événement ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun événement trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($evenements->hasPages())<div class="card-footer">{{ $evenements->links() }}</div>@endif
    </div>
</div>
@endsection
