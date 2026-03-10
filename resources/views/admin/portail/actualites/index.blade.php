@extends('layouts.admin')

@section('title', 'Actualités - Portail')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
                <i class="fas fa-newspaper mr-2" style="color:#009e3f;"></i>Actualités du portail
            </h1>
            <p class="text-muted mb-0">Gérez les articles et actualités affichés sur le portail public.</p>
        </div>
        <a href="{{ route('admin.portail.actualites.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> Nouvelle actualité
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Filtres --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="form-inline">
                <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Rechercher..." value="{{ request('search') }}">
                <select name="statut" class="form-control form-control-sm mr-2">
                    <option value="">Tous statuts</option>
                    <option value="brouillon" {{ request('statut') == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                    <option value="publie" {{ request('statut') == 'publie' ? 'selected' : '' }}>Publié</option>
                    <option value="archive" {{ request('statut') == 'archive' ? 'selected' : '' }}>Archivé</option>
                </select>
                <select name="categorie" class="form-control form-control-sm mr-2">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('categorie') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm mr-2"><i class="fas fa-search mr-1"></i>Filtrer</button>
                <a href="{{ route('admin.portail.actualites.index') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>À la une</th>
                            <th>Vues</th>
                            <th>Date publication</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actualites as $actualite)
                        <tr>
                            <td>
                                <strong>{{ Str::limit($actualite->titre, 55) }}</strong>
                                <br><small class="text-muted">{{ $actualite->slug }}</small>
                            </td>
                            <td><span class="badge badge-info">{{ $actualite->categorie }}</span></td>
                            <td>
                                @if($actualite->statut === 'publie')
                                    <span class="badge badge-success">Publié</span>
                                @elseif($actualite->statut === 'brouillon')
                                    <span class="badge badge-warning">Brouillon</span>
                                @else
                                    <span class="badge badge-secondary">Archivé</span>
                                @endif
                            </td>
                            <td>
                                @if($actualite->en_une)
                                    <i class="fas fa-star text-warning" title="À la une"></i>
                                @else
                                    <i class="far fa-star text-muted"></i>
                                @endif
                            </td>
                            <td>{{ number_format($actualite->vues) }}</td>
                            <td>{{ $actualite->date_publication ? $actualite->date_publication->format('d/m/Y') : '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.portail.actualites.edit', $actualite) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.portail.actualites.destroy', $actualite) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cette actualité ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucune actualité trouvée.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($actualites->hasPages())
        <div class="card-footer">
            {{ $actualites->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
