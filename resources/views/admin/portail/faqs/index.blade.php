@extends('layouts.admin')
@section('title', 'FAQ - Portail')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
                <i class="fas fa-question-circle mr-2" style="color:#009e3f;"></i>FAQ du portail
            </h1>
            <p class="text-muted mb-0">Gérez les questions fréquemment posées.</p>
        </div>
        <a href="{{ route('admin.portail.faqs.create') }}" class="btn btn-success"><i class="fas fa-plus mr-1"></i> Nouvelle question</a>
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
                <a href="{{ route('admin.portail.faqs.index') }}" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>#</th><th>Question</th><th>Catégorie</th><th>Ordre</th><th>Actif</th><th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faqs as $faq)
                        <tr>
                            <td class="text-muted">{{ $faq->id }}</td>
                            <td>{{ Str::limit($faq->question, 80) }}</td>
                            <td><span class="badge badge-info">{{ $faq->categorie }}</span></td>
                            <td>{{ $faq->ordre }}</td>
                            <td>
                                @if($faq->est_actif)<span class="badge badge-success">Oui</span>
                                @else<span class="badge badge-secondary">Non</span>@endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.portail.faqs.edit', $faq) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.portail.faqs.destroy', $faq) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cette question ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucune question trouvée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($faqs->hasPages())<div class="card-footer">{{ $faqs->links() }}</div>@endif
    </div>
</div>
@endsection
