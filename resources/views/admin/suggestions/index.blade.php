@extends('layouts.admin')

@section('title', 'Suggestions en attente')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-lightbulb text-warning mr-2"></i> Suggestions en attente</h3>
            <p class="text-muted mb-0">Valeurs proposées par les usagers (option « Autre »)</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h6 class="mb-0"><i class="fas fa-user-tie mr-2"></i> Fonctions suggérées</h6>
                    <span class="badge bg-light text-dark">{{ $fonctions->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($fonctions->isEmpty())
                        <p class="text-muted text-center py-4 mb-0"><i class="fas fa-check-circle mr-2"></i>Aucune suggestion en attente</p>
                    @else
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Proposé par</th>
                                    <th>Date</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fonctions as $f)
                                    <tr>
                                        <td>
                                            <strong>{{ $f->nom }}</strong>
                                            @if($f->description)
                                                <br><small class="text-muted">{{ $f->description }}</small>
                                            @endif
                                        </td>
                                        <td class="small">#{{ $f->suggested_by_user_id ?? '-' }}</td>
                                        <td class="small text-muted">{{ $f->created_at ? $f->created_at->diffForHumans() : '' }}</td>
                                        <td class="text-right">
                                            <form action="{{ route('admin.suggestions.approve', ['type' => 'fonction', 'id' => $f->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form action="{{ route('admin.suggestions.reject', ['type' => 'fonction', 'id' => $f->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between">
                    <h6 class="mb-0"><i class="fas fa-bullseye mr-2"></i> Domaines d'activité suggérés</h6>
                    <span class="badge bg-light text-dark">{{ $domaines->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($domaines->isEmpty())
                        <p class="text-muted text-center py-4 mb-0"><i class="fas fa-check-circle mr-2"></i>Aucune suggestion en attente</p>
                    @else
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Proposé par</th>
                                    <th>Date</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($domaines as $d)
                                    <tr>
                                        <td>
                                            <strong>{{ $d->nom }}</strong>
                                            @if($d->description)
                                                <br><small class="text-muted">{{ $d->description }}</small>
                                            @endif
                                        </td>
                                        <td class="small">#{{ $d->suggested_by_user_id ?? '-' }}</td>
                                        <td class="small text-muted">{{ $d->created_at ? $d->created_at->diffForHumans() : '' }}</td>
                                        <td class="text-right">
                                            <form action="{{ route('admin.suggestions.approve', ['type' => 'domaine', 'id' => $d->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form action="{{ route('admin.suggestions.reject', ['type' => 'domaine', 'id' => $d->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
