{{--
============================================================================
PARTIAL : Carte de gestion du lien d'inscription publique
Usage : @include('operator.partials.inscription-link-card', ['organisation' => $organisation])
============================================================================
--}}
@php
    $activeLink = $organisation->activeInscriptionLink;
    $pendingCount = $organisation->adherentsEnAttenteValidation()->count();
@endphp

<div class="card border-0 shadow-sm mb-4" id="inscription-link-card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #003f7f, #0056b3); color: white;">
        <h6 class="mb-0">
            <i class="fas fa-link mr-2"></i>Lien d'inscription publique
        </h6>
        @if($pendingCount > 0)
            <a href="{{ route('operator.inscription.pending', $organisation->id) }}" class="badge badge-warning badge-pill" style="font-size: 0.85rem;">
                {{ $pendingCount }} en attente
            </a>
        @endif
    </div>
    <div class="card-body">
        @if(!$organisation->isApprouvee())
            {{-- Organisation non approuvée --}}
            <div class="text-center text-muted py-3">
                <i class="fas fa-lock fa-2x mb-2"></i>
                <p class="mb-0">Le lien d'inscription sera disponible une fois l'organisation approuvée.</p>
            </div>
        @elseif($activeLink)
            {{-- Lien actif existant --}}
            <div class="mb-3">
                <label class="small font-weight-bold text-muted mb-1">Lien de partage :</label>
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" id="inscription-url"
                           value="{{ $activeLink->getPublicUrl() }}" readonly
                           style="background: #f8f9fa; font-size: 0.85rem;">
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary btn-sm" onclick="copyInscriptionUrl()" title="Copier le lien">
                            <i class="fas fa-copy" id="copy-icon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row text-center mb-3">
                <div class="col-4">
                    <div class="small text-muted">Inscriptions</div>
                    <strong class="text-primary">{{ $activeLink->inscriptions_actuelles }}</strong>
                </div>
                <div class="col-4">
                    <div class="small text-muted">En attente</div>
                    <strong class="text-warning">{{ $pendingCount }}</strong>
                </div>
                <div class="col-4">
                    <div class="small text-muted">Expire le</div>
                    <strong class="small">{{ $activeLink->date_fin ? $activeLink->date_fin->format('d/m/Y') : 'Illimité' }}</strong>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                @if($pendingCount > 0)
                    <a href="{{ route('operator.inscription.pending', $organisation->id) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-users mr-1"></i>Voir les demandes
                    </a>
                @endif
                <button class="btn btn-sm btn-outline-danger ml-auto" onclick="deactivateLink({{ $organisation->id }})">
                    <i class="fas fa-ban mr-1"></i>Désactiver
                </button>
            </div>
        @else
            {{-- Pas de lien actif --}}
            <div class="text-center py-3">
                <p class="text-muted mb-3">
                    <i class="fas fa-share-alt fa-2x mb-2 d-block" style="color: #003f7f;"></i>
                    Générez un lien pour permettre aux adhérents de s'inscrire en ligne.
                </p>
                <button class="btn btn-primary btn-sm" onclick="generateLink({{ $organisation->id }})" id="btn-generate">
                    <i class="fas fa-magic mr-1"></i>Générer le lien d'inscription
                </button>
            </div>
        @endif
    </div>
</div>

<script>
function copyInscriptionUrl() {
    var input = document.getElementById('inscription-url');
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
    var icon = document.getElementById('copy-icon');
    icon.className = 'fas fa-check text-success';
    setTimeout(function() { icon.className = 'fas fa-copy'; }, 2000);
}

function generateLink(organisationId) {
    var btn = document.getElementById('btn-generate');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Génération...';

    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/operator/organisations/' + organisationId + '/inscription/generate-link', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            // Recharger la page pour afficher le lien
            location.reload();
        } else {
            alert(data.message || 'Erreur lors de la génération.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-magic mr-1"></i>Générer le lien d\'inscription';
        }
    })
    .catch(function(error) {
        alert('Erreur de connexion. Veuillez réessayer.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic mr-1"></i>Générer le lien d\'inscription';
    });
}

function deactivateLink(organisationId) {
    if (!confirm('Êtes-vous sûr de vouloir désactiver ce lien ? Les inscriptions en cours ne seront pas affectées.')) {
        return;
    }

    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/operator/organisations/' + organisationId + '/inscription/deactivate-link', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erreur lors de la désactivation.');
        }
    })
    .catch(function(error) {
        alert('Erreur de connexion. Veuillez réessayer.');
    });
}
</script>
