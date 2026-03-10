@extends('layouts.admin')
@section('title', 'Messages - Portail')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
                <i class="fas fa-envelope mr-2" style="color:#009e3f;"></i>Messages du formulaire de contact
                @if($nonLuCount > 0)
                    <span class="badge badge-warning ml-2">{{ $nonLuCount }} non lu(s)</span>
                @endif
            </h1>
        </div>
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
                <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Rechercher (nom, email, sujet)..." value="{{ request('search') }}">
                <select name="statut" class="form-control form-control-sm mr-2">
                    <option value="">Tous statuts</option>
                    <option value="non_lu" {{ request('statut') == 'non_lu' ? 'selected' : '' }}>Non lu</option>
                    <option value="lu" {{ request('statut') == 'lu' ? 'selected' : '' }}>Lu</option>
                    <option value="traite" {{ request('statut') == 'traite' ? 'selected' : '' }}>Traité</option>
                    <option value="archive" {{ request('statut') == 'archive' ? 'selected' : '' }}>Archivé</option>
                </select>
                <button class="btn btn-primary btn-sm mr-2"><i class="fas fa-search mr-1"></i>Filtrer</button>
                <a href="{{ route('admin.portail.messages.index') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Expéditeur</th><th>Sujet</th><th>Statut</th><th>Reçu le</th><th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                        @php
                            $badgeColors = ['non_lu'=>'danger','lu'=>'primary','traite'=>'success','archive'=>'secondary'];
                            $badgeLabels = ['non_lu'=>'Non lu','lu'=>'Lu','traite'=>'Traité','archive'=>'Archivé'];
                        @endphp
                        <tr class="{{ $msg->statut === 'non_lu' ? 'font-weight-bold' : '' }}">
                            <td>
                                {{ $msg->nom }}<br>
                                <small class="text-muted">{{ $msg->email }}</small>
                            </td>
                            <td>{{ Str::limit($msg->sujet, 60) }}</td>
                            <td>
                                <span class="badge badge-{{ $badgeColors[$msg->statut] ?? 'secondary' }}">
                                    {{ $badgeLabels[$msg->statut] ?? $msg->statut }}
                                </span>
                            </td>
                            <td>{{ $msg->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.portail.messages.show', $msg) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye mr-1"></i>Voir
                                </a>
                                <form action="{{ route('admin.portail.messages.destroy', $msg) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer ce message ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun message.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($messages->hasPages())<div class="card-footer">{{ $messages->links() }}</div>@endif
    </div>
</div>
@endsection
