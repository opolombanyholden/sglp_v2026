@extends('layouts.admin')
@section('title', 'Guides - Portail')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
                <i class="fas fa-book mr-2" style="color:#009e3f;"></i>Guides du portail
            </h1>
            <p class="text-muted mb-0">Gérez les guides et ressources PDF.</p>
        </div>
        <a href="{{ route('admin.portail.guides.create') }}" class="btn btn-success"><i class="fas fa-plus mr-1"></i> Nouveau guide</a>
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
                <select name="categorie" class="form-control form-control-sm mr-2">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('categorie') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm mr-2"><i class="fas fa-search mr-1"></i>Filtrer</button>
                <a href="{{ route('admin.portail.guides.index') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>#</th><th>Titre</th><th>Catégorie</th><th>Pages</th><th>Téléch.</th><th>Actif</th><th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guides as $guide)
                        <tr>
                            <td class="text-muted">{{ $guide->ordre }}</td>
                            <td>
                                <strong>{{ Str::limit($guide->titre, 60) }}</strong>
                                @if($guide->description)<br><small class="text-muted">{{ Str::limit($guide->description, 80) }}</small>@endif
                            </td>
                            <td><span class="badge badge-secondary">{{ $guide->categorie }}</span></td>
                            <td>{{ $guide->nombre_pages ?? '-' }}</td>
                            <td>{{ number_format($guide->nombre_telechargements) }}</td>
                            <td>
                                @if($guide->est_actif)<span class="badge badge-success">Oui</span>
                                @else<span class="badge badge-secondary">Non</span>@endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.portail.guides.edit', $guide) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.portail.guides.destroy', $guide) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer ce guide ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucun guide trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($guides->hasPages())<div class="card-footer">{{ $guides->links() }}</div>@endif
    </div>
</div>
@endsection
