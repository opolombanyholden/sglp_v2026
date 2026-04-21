@extends('layouts.operator')

@section('title', 'Mes brouillons')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-pencil-alt mr-2" style="color: #FFD700;"></i>
                Mes brouillons
            </h3>
            <p class="text-muted mb-0">Dossiers en cours de remplissage, non encore finalisés</p>
        </div>
        <a href="{{ route('operator.dossiers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-1"></i> Nouvelle organisation
        </a>
    </div>

    @if($drafts->count() > 0)
        <div class="row g-4">
            @foreach($drafts as $draft)
            @php
                $typeLabels = [
                    'association' => 'Association',
                    'ong' => 'ONG',
                    'parti_politique' => 'Parti politique',
                    'confession_religieuse' => 'Confession religieuse',
                ];
                $orgName = '';
                $formData = $draft->form_data ?? [];
                if (isset($formData['step_4']['data']['org_nom'])) {
                    $orgName = $formData['step_4']['data']['org_nom'];
                }
                $stepLabels = [
                    1 => 'Type d\'organisation',
                    2 => 'Guide et exigences',
                    3 => 'Informations demandeur',
                    4 => 'Informations organisation',
                    5 => 'Coordonnées',
                    6 => 'Fondateurs',
                    7 => 'Adhérents',
                    8 => 'Documents',
                    9 => 'Validation finale',
                ];
            @endphp
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-warning text-dark px-3 py-2">
                                <i class="fas fa-pencil-alt mr-1"></i> Brouillon
                            </span>
                            <small class="text-muted">
                                {{ $draft->last_saved_at ? $draft->last_saved_at->diffForHumans() : '' }}
                            </small>
                        </div>

                        <h5 class="card-title mb-1">{{ $orgName ?: 'Organisation sans nom' }}</h5>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-tag mr-1"></i>
                            {{ $typeLabels[$draft->organization_type] ?? ucfirst($draft->organization_type ?? 'Non défini') }}
                        </p>

                        {{-- Barre de progression --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Progression</span>
                                <span class="fw-bold">{{ $draft->completion_percentage ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ $draft->completion_percentage ?? 0 }}%"></div>
                            </div>
                        </div>

                        <p class="small text-muted mb-1">
                            <i class="fas fa-layer-group mr-1"></i>
                            Etape {{ $draft->current_step ?? 1 }} sur 9
                            — <em>{{ $stepLabels[$draft->current_step] ?? '' }}</em>
                        </p>

                        @if($draft->expires_at)
                        <p class="small text-muted mb-3">
                            <i class="fas fa-clock mr-1"></i>
                            Expire {{ $draft->expires_at->diffForHumans() }}
                        </p>
                        @endif

                        {{-- Étapes complétées --}}
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-1">
                                @for($i = 1; $i <= 9; $i++)
                                    @php
                                        $stepKey = 'step_' . $i;
                                        $completed = isset($formData[$stepKey]) && ($formData[$stepKey]['status'] ?? '') === 'completed';
                                    @endphp
                                    <span class="badge {{ $completed ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.7rem;" title="{{ $stepLabels[$i] ?? '' }}">
                                        {{ $i }}
                                    </span>
                                @endfor
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('operator.organisations.draft.resume', $draft->id) }}"
                               class="btn btn-primary flex-grow-1">
                                <i class="fas fa-play mr-1"></i> Continuer
                            </a>
                            <button type="button" class="btn btn-outline-danger"
                                    onclick="if(confirm('Supprimer ce brouillon ?')) deleteDraft({{ $draft->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-3" style="font-size: 3rem; color: #ccc;">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h5 class="text-muted">Aucun brouillon en cours</h5>
                <p class="text-muted mb-4">
                    Vous n'avez aucun dossier en attente de finalisation.
                </p>
                <a href="{{ route('operator.dossiers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle mr-1"></i> Créer une organisation
                </a>
            </div>
        </div>
    @endif
</div>

<script>
async function deleteDraft(draftId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/operator/organisations/draft/' + draftId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        if (response.ok) {
            location.reload();
        }
    } catch (e) {
        console.error('Erreur suppression brouillon:', e);
    }
}
</script>
@endsection
