@extends('layouts.public')

@section('title', 'Vérification de récépissé — Résultat')

@section('content')

@php
    $isInvalidCode   = $raison === 'invalid_code';
    $isNotFound      = $raison === 'not_found';
    $isDocInvalide   = $raison === 'document_invalide';
@endphp

<!-- Header -->
<section class="page-header-simple">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('annuaire.index') }}">Annuaire</a></li>
                <li class="breadcrumb-item active" aria-current="page">Vérification</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Contenu principal -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">

                @if($isInvalidCode)
                {{-- ────── Code invalide (format incorrect) ────── --}}
                <div class="verify-result-card border-warning">
                    <div class="verify-icon warning">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h2 class="verify-title text-warning">Code invalide</h2>
                    <p class="verify-message">
                        Le code que vous avez saisi ne correspond pas au format attendu.
                        Veuillez vérifier et réessayer.
                    </p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Format attendu :</strong> lettres, chiffres, tirets ou barres obliques uniquement.
                        <br>Exemple : <code>ASS-2024-001234</code>
                    </div>
                </div>

                @elseif($isNotFound)
                {{-- ────── Document non trouvé — potentiellement frauduleux ────── --}}
                <div class="verify-result-card border-danger">
                    <div class="verify-icon danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h2 class="verify-title text-danger">Récépissé non reconnu</h2>
                    <p class="verify-message">
                        Aucun récépissé correspondant au code
                        <strong>« {{ e($code) }} »</strong>
                        n'a été trouvé dans la base officielle du PNGDI.
                    </p>

                    <div class="alert alert-danger mt-3">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Document potentiellement frauduleux
                        </h5>
                        <p class="mb-2">
                            Ce code ne correspond à aucun récépissé officiel enregistré. Il peut s'agir d'un
                            document contrefait ou d'une information erronée.
                        </p>
                        <hr>
                        <p class="mb-0">
                            <strong>Si vous avez reçu ce document d'une organisation :</strong>
                        </p>
                        <ul class="mt-2 mb-0">
                            <li>Ne lui remettez aucun fonds ou document officiel sans vérification complémentaire.</li>
                            <li>Signalez immédiatement la situation aux autorités compétentes.</li>
                            <li>Contactez le PNGDI pour un signalement officiel.</li>
                        </ul>
                    </div>

                    <div class="action-row mt-4">
                        <a href="{{ route('contact') }}" class="btn btn-danger">
                            <i class="fas fa-flag me-2"></i>Signaler ce document
                        </a>
                        <a href="{{ route('annuaire.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-search me-2"></i>Rechercher dans l'annuaire
                        </a>
                    </div>
                </div>

                @elseif($isDocInvalide)
                {{-- ────── Document trouvé mais organisation radiée/rejetée ────── --}}
                <div class="verify-result-card border-danger">
                    <div class="verify-icon danger">
                        <i class="fas fa-ban"></i>
                    </div>
                    <h2 class="verify-title text-danger">Récépissé non valide</h2>
                    <p class="verify-message">
                        Le récépissé <strong>« {{ e($code) }} »</strong> a bien été émis par le PNGDI
                        @if(isset($organisation))
                            pour l'organisation <strong>« {{ $organisation->nom }} »</strong>,
                        @endif
                        mais il n'est <strong>plus en vigueur</strong>.
                    </p>

                    <div class="alert alert-danger mt-3">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Récépissé invalide
                        </h5>
                        @if(isset($organisation))
                        <p class="mb-2">
                            <strong>Statut actuel :</strong>
                            @switch($organisation->statut)
                                @case('radie') Radiée du registre @break
                                @case('rejete') Dossier rejeté @break
                                @default Non reconnu @break
                            @endswitch
                        </p>
                        @endif
                        <p class="mb-0">
                            Ce récépissé ne confère plus aucun droit ou reconnaissance légale à cette organisation.
                            Toute utilisation de ce document à des fins de légitimation est irrégulière.
                        </p>
                    </div>

                    <div class="action-row mt-4">
                        <a href="{{ route('contact') }}" class="btn btn-danger">
                            <i class="fas fa-flag me-2"></i>Signaler une utilisation frauduleuse
                        </a>
                        <a href="{{ route('annuaire.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>Retour à l'annuaire
                        </a>
                    </div>
                </div>

                @endif

                {{-- Formulaire de nouvelle vérification --}}
                <div class="recheck-card mt-4">
                    <h5 class="mb-3"><i class="fas fa-redo me-2"></i>Vérifier un autre récépissé</h5>
                    <form method="GET" action="#" id="recheck-form">
                        <div class="input-group">
                            <input type="text" class="form-control" id="recheck-code"
                                   placeholder="Numéro de récépissé ou code QR"
                                   maxlength="100" pattern="[a-zA-Z0-9\-\_\/]+" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Vérifier
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Informations de contact PNGDI --}}
                <div class="contact-pngdi mt-4">
                    <i class="fas fa-phone-alt me-2 text-primary"></i>
                    <strong>Besoin d'aide ?</strong>
                    Contactez le PNGDI :
                    <a href="{{ route('contact') }}">via notre formulaire de contact</a>
                    ou appelez le <strong>+241 01 23 45 67</strong>.
                </div>

            </div>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
    .page-header-simple {
        background: #f8f9fa;
        padding: 1.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    .breadcrumb { background: transparent; margin: 0; padding: 0; }

    .verify-result-card {
        background: white;
        border-radius: 15px;
        padding: 2.5rem;
        box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        border-top: 5px solid;
        text-align: center;
    }
    .border-danger { border-top-color: #dc3545; }
    .border-warning { border-top-color: #ffc107; }

    .verify-icon {
        width: 90px; height: 90px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 1.5rem;
    }
    .verify-icon.danger { background: #fff0f0; color: #dc3545; }
    .verify-icon.warning { background: #fffbeb; color: #f59e0b; }

    .verify-title { font-size: 2rem; font-weight: 700; margin-bottom: 1rem; }
    .verify-message { font-size: 1.1rem; color: #444; line-height: 1.7; }
    .action-row { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }

    .recheck-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    }

    .contact-pngdi {
        background: #f0f7ff;
        border-radius: 10px;
        padding: 1rem 1.5rem;
        font-size: 0.9rem;
        color: #444;
    }
    .contact-pngdi a { color: var(--primary-blue); font-weight: 600; }

    @media (max-width: 576px) {
        .verify-result-card { padding: 1.5rem; }
        .action-row { flex-direction: column; }
        .action-row .btn { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('recheck-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var code = document.getElementById('recheck-code').value.trim();
        if (code) {
            window.location.href = '{{ url("annuaire/verify") }}/' + encodeURIComponent(code);
        }
    });
</script>
@endpush
