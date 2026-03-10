@extends('layouts.admin')
@section('title', 'Message de ' . $message->nom)
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
            <i class="fas fa-envelope-open mr-2" style="color:#009e3f;"></i>Message de {{ $message->nom }}
        </h1>
        <a href="{{ route('admin.portail.messages.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ $message->sujet }}</strong>
                    @php
                        $badgeColors = ['non_lu'=>'danger','lu'=>'primary','traite'=>'success','archive'=>'secondary'];
                        $badgeLabels = ['non_lu'=>'Non lu','lu'=>'Lu','traite'=>'Traité','archive'=>'Archivé'];
                    @endphp
                    <span class="badge badge-{{ $badgeColors[$message->statut] ?? 'secondary' }}">
                        {{ $badgeLabels[$message->statut] ?? $message->statut }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>De :</strong> {{ $message->nom }} (<a href="mailto:{{ $message->email }}">{{ $message->email }}</a>)
                        </div>
                        <div class="col-md-6 text-right">
                            <strong>Reçu le :</strong> {{ $message->created_at->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                    <hr>
                    <div style="white-space: pre-wrap; line-height: 1.7;">{{ $message->message }}</div>
                </div>
            </div>

            @if($message->reponse)
            <div class="card shadow-sm mb-4" style="border-left: 4px solid #009e3f;">
                <div class="card-header" style="background:#f0fff4;">
                    <strong><i class="fas fa-reply mr-1 text-success"></i>Réponse envoyée le {{ $message->date_reponse->format('d/m/Y') }}</strong>
                </div>
                <div class="card-body" style="white-space: pre-wrap;">{{ $message->reponse }}</div>
            </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-header"><strong><i class="fas fa-reply mr-1"></i>Ajouter une réponse interne</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.portail.messages.repondre', $message) }}">
                        @csrf
                        @if($errors->any())
                            <div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                        @endif
                        <div class="form-group">
                            <textarea name="reponse" class="form-control @error('reponse') is-invalid @enderror"
                                      rows="5" placeholder="Rédigez votre réponse...">{{ old('reponse', $message->reponse ?? '') }}</textarea>
                            @error('reponse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <p class="text-muted small">
                            <i class="fas fa-info-circle mr-1"></i>
                            La réponse est enregistrée dans le système. Pour envoyer par email, copiez le texte et envoyez manuellement à <strong>{{ $message->email }}</strong>.
                        </p>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i>Enregistrer la réponse
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><strong>Informations</strong></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Nom</td><td>{{ $message->nom }}</td></tr>
                        <tr><td class="text-muted">Email</td><td><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></td></tr>
                        <tr><td class="text-muted">IP</td><td>{{ $message->ip_address ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Reçu le</td><td>{{ $message->created_at->format('d/m/Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header"><strong>Changer le statut</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.portail.messages.statut', $message) }}">
                        @csrf
                        <select name="statut" class="form-control form-control-sm mb-2">
                            @foreach(['non_lu'=>'Non lu','lu'=>'Lu','traite'=>'Traité','archive'=>'Archivé'] as $val => $label)
                                <option value="{{ $val }}" {{ $message->statut == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm btn-block">Mettre à jour</button>
                    </form>
                </div>
            </div>

            <form action="{{ route('admin.portail.messages.destroy', $message) }}" method="POST"
                  onsubmit="return confirm('Supprimer définitivement ce message ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm btn-block">
                    <i class="fas fa-trash mr-1"></i>Supprimer ce message
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
