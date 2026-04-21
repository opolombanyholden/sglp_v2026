@extends('layouts.public')

@section('title', 'Email Vérifié - DGELP')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            @if(request('activated'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shield-alt fs-4 me-3 text-success"></i>
                        <div>
                            <strong>Compte activé !</strong>
                            <p class="mb-0 small">Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-check-circle fs-1 text-success"></i>
                        </div>
                    </div>

                    <h3 class="mb-3 text-success">Compte activé avec succès !</h3>

                    <p class="text-muted mb-4">
                        Félicitations ! Votre adresse email a été vérifiée et votre compte est maintenant actif.
                        Vous pouvez accéder à toutes les fonctionnalités de la plateforme DGELP.
                    </p>

                </div>
            </div>

            <!-- Formulaire de connexion -->
            <div class="card mt-4 shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Connectez-vous maintenant
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>
                                Adresse email
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="exemple@email.com" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>
                                Mot de passe
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Votre mot de passe" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Se connecter
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('home') }}" class="text-muted small">
                            <i class="fas fa-home me-1"></i>
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const field = document.getElementById('password');
        const icon = field.nextElementSibling.querySelector('i');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection