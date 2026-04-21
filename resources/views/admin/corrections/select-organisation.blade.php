@extends('layouts.admin')

@section('title', 'Correction — Sélection organisation')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Nouvelle correction administrative</h1>
        <p class="text-muted">Sélectionnez l'organisation dont le dossier approuvé doit être corrigé</p>
    </div>

    {{-- Recherche --}}
    <div class="card mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Rechercher par nom, sigle ou n recepissé..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-search me-1"></i>Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($organisations as $org)
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-1">{{ $org->nom }}</h5>
                    @if($org->sigle)
                        <span class="badge bg-light text-dark mb-2">{{ $org->sigle }}</span>
                    @endif
                    <p class="text-muted small mb-2">
                        @if($org->organisationType)
                            <i class="fas fa-tag me-1"></i>{{ $org->organisationType->libelle }}<br>
                        @endif
                        @if($org->numero_recepisse)
                            <i class="fas fa-file-alt me-1"></i>{{ $org->numero_recepisse }}<br>
                        @endif
                        @if($org->province)
                            <i class="fas fa-map-marker-alt me-1"></i>{{ $org->province }}
                        @endif
                    </p>
                    @if($org->dossiers->first())
                        <p class="small mb-0">
                            <span class="text-muted">Dossier :</span>
                            <code>{{ $org->dossiers->first()->numero_dossier }}</code>
                            <span class="badge bg-success ms-1">Approuvé</span>
                        </p>
                    @endif
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.corrections.create', $org) }}"
                       class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-edit me-1"></i>Corriger ce dossier
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Aucune organisation avec un dossier approuvé trouvée.
            </div>
        </div>
        @endforelse
    </div>

    @if($organisations->hasPages())
        <div class="mt-3">{{ $organisations->links() }}</div>
    @endif
</div>
@endsection
