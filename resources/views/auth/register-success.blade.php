@extends('layouts.public')

@section('title', 'Compte créé avec succès')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-check fs-1 text-success"></i>
                        </div>
                    </div>

                    <h3 class="mb-3 text-success">Compte créé avec succès !</h3>

                    <p class="text-muted mb-2">
                        Votre compte a été créé avec succès sur la plateforme DGELP.
                    </p>

                    @if(session('email_sent'))
                        <div class="alert alert-info text-start mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-envelope me-2"></i>
                                Vérifiez votre boîte email
                            </h6>
                            <p class="mb-0 small">
                                Un email de vérification a été envoyé à <strong>{{ session('user_email') }}</strong>.
                                Cliquez sur le lien contenu dans cet email pour activer votre compte.
                            </p>
                        </div>
                    @endif

                    @if(session('email_failed'))
                        <div class="alert alert-warning text-start mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Email non envoyé
                            </h6>
                            <p class="mb-0 small">
                                L'envoi de l'email de vérification a échoué. Vous pourrez le renvoyer
                                après votre première connexion.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Instructions -->
            <div class="card mt-4 border-primary">
                <div class="card-body">
                    <h6 class="card-title text-primary">
                        <i class="fas fa-list-ol me-2"></i>
                        Instructions à suivre
                    </h6>
                    <ol class="mb-0 small">
                        <li class="mb-2">
                            Ouvrez votre boîte email et recherchez le message de la DGELP
                        </li>
                        <li class="mb-2">
                            Cliquez sur le bouton <strong>"Vérifier mon email"</strong> contenu dans le message
                        </li>
                        <li class="mb-2">
                            Une fois votre email vérifié, connectez-vous avec vos identifiants
                        </li>
                        <li>
                            Complétez votre profil et soumettez votre dossier de formalisation
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Note spam -->
            <div class="card mt-3 border-warning">
                <div class="card-body">
                    <p class="mb-0 small text-muted">
                        <i class="fas fa-info-circle text-warning me-2"></i>
                        Si vous ne recevez pas l'email dans les prochaines minutes,
                        vérifiez votre dossier <strong>spam</strong> ou <strong>courrier indésirable</strong>.
                    </p>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
