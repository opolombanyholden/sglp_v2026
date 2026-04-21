@extends('layouts.public')

@section('title', 'Mot de passe oublié')

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fs-4 me-3"></i>
                            <div>
                                <p class="mb-0">{{ session('status') }}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0">
                            <i class="fas fa-key me-2"></i>
                            Mot de passe oublié
                        </h3>
                        <p class="mb-0 mt-2 small">
                            Entrez votre adresse email pour recevoir un lien de réinitialisation
                        </p>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Adresse email
                                </label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="exemple@email.com"
                                       required
                                       autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Envoyer le lien de réinitialisation
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-0">
                                <a href="{{ route('login') }}" class="text-primary">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Retour à la connexion
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                @if(session('status'))
                    <div class="card mt-3 border-warning">
                        <div class="card-body">
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-info-circle text-warning me-2"></i>
                                Si vous ne recevez pas l'email dans les prochaines minutes,
                                vérifiez votre dossier <strong>spam</strong> ou <strong>courrier indésirable</strong>.
                            </p>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
