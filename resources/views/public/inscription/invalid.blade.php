@extends('layouts.public')

@section('title', 'Lien d\'inscription invalide')

@section('content')
<div class="container py-5" style="min-height: 60vh;">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center">
                <div class="mb-4">
                    <i class="fas fa-link-slash fa-5x text-muted"></i>
                </div>
                <h2 class="mb-3" style="color: #003f7f;">Lien d'inscription non disponible</h2>
                <p class="text-muted mb-4">{{ $message ?? 'Ce lien d\'inscription n\'est pas valide.' }}</p>

                @if(isset($organisation))
                    <div class="alert alert-light border mb-4">
                        <p class="mb-1"><strong>Organisation :</strong> {{ $organisation->nom }}</p>
                        <p class="mb-0 small text-muted">Contactez l'administrateur de l'organisation pour obtenir un lien valide.</p>
                    </div>
                @endif

                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-home mr-2"></i>Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
