@extends('layouts.public')

@section('title', 'Inscription confirmée')

@section('content')
<style>
    .confirmation-container {
        min-height: 60vh;
        display: flex;
        align-items: center;
    }
    .confirmation-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .confirmation-header {
        background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);
        padding: 2.5rem;
        text-align: center;
        color: white;
    }
    .confirmation-icon {
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    .step-list {
        counter-reset: steps;
        list-style: none;
        padding: 0;
    }
    .step-list li {
        counter-increment: steps;
        padding: 0.8rem 0 0.8rem 3rem;
        position: relative;
        border-bottom: 1px solid #f0f0f0;
    }
    .step-list li:last-child { border-bottom: none; }
    .step-list li::before {
        content: counter(steps);
        position: absolute;
        left: 0;
        width: 32px;
        height: 32px;
        background: #003f7f;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .step-list li.done::before {
        background: #009e3f;
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
    }
</style>

<div class="confirmation-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="confirmation-card">
                    <div class="confirmation-header">
                        <div class="confirmation-icon">
                            <i class="fas fa-check fa-3x"></i>
                        </div>
                        <h2 class="mb-2">Demande envoyée avec succès !</h2>
                        @if($adherent_nom)
                            <p class="mb-0 opacity-90">Merci, <strong>{{ $adherent_nom }}</strong></p>
                        @endif
                    </div>

                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h5 style="color: #003f7f;">
                                <i class="fas fa-building mr-2"></i>
                                {{ $organisation->nom ?? 'Organisation' }}
                            </h5>
                        </div>

                        @if($requiert_validation)
                            <div class="alert alert-info mb-4" style="border-radius: 10px;">
                                <i class="fas fa-info-circle mr-2"></i>
                                Votre demande d'adhésion a été enregistrée et sera examinée par l'administrateur de l'organisation. Vous serez informé(e) de la suite donnée.
                            </div>
                        @else
                            <div class="alert alert-success mb-4" style="border-radius: 10px;">
                                <i class="fas fa-check-circle mr-2"></i>
                                Votre adhésion a été confirmée automatiquement. Bienvenue !
                            </div>
                        @endif

                        <h6 class="font-weight-bold mb-3" style="color: #003f7f;">Prochaines étapes</h6>
                        <ul class="step-list">
                            <li class="done">Formulaire d'adhésion soumis</li>
                            <li class="done">Pièce d'identité transmise</li>
                            <li>Vérification par l'administrateur</li>
                            <li>Confirmation de votre adhésion</li>
                        </ul>

                        <div class="text-center mt-4">
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-home mr-2"></i>Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
