@extends('layouts.admin')
@section('title', 'Paramètres du portail')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 font-weight-bold" style="color:#0e2f5b;">
                <i class="fas fa-sliders-h mr-2" style="color:#009e3f;"></i>Paramètres du portail public
            </h1>
            <p class="text-muted mb-0">Personnalisez les textes et contenus affichés sur le portail.</p>
        </div>
        <a href="{{ route('admin.portail.parametres.create') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-plus mr-1"></i> Nouveau paramètre
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.portail.parametres.update') }}">
        @csrf

        @foreach($parametres as $groupe => $items)
        @php
            $groupeLabels = [
                'hero'    => ['icon'=>'fas fa-home',       'label'=>'Page d\'accueil (Hero)'],
                'stats'   => ['icon'=>'fas fa-chart-bar',  'label'=>'Statistiques accueil'],
                'about'   => ['icon'=>'fas fa-info-circle','label'=>'À propos'],
                'contact' => ['icon'=>'fas fa-envelope',   'label'=>'Contact'],
                'footer'  => ['icon'=>'fas fa-bars',       'label'=>'Pied de page'],
            ];
            $info = $groupeLabels[$groupe] ?? ['icon'=>'fas fa-cog', 'label'=>ucfirst($groupe)];
        @endphp
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background:#f8f9fa;">
                <h6 class="mb-0 font-weight-bold">
                    <i class="{{ $info['icon'] }} mr-2 text-success"></i>{{ $info['label'] }}
                </h6>
            </div>
            <div class="card-body">
                @foreach($items as $param)
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">
                        <strong>{{ $param->cle }}</strong>
                        @if($param->description)
                            <br><small class="text-muted font-weight-normal">{{ $param->description }}</small>
                        @endif
                    </label>
                    <div class="col-sm-9">
                        @if($param->type === 'html')
                            <textarea name="parametres[{{ $param->cle }}][valeur]"
                                      class="form-control" rows="4">{{ old('parametres.' . $param->cle . '.valeur', $param->valeur) }}</textarea>
                        @else
                            <input type="{{ $param->type === 'email' ? 'email' : ($param->type === 'url' ? 'url' : 'text') }}"
                                   name="parametres[{{ $param->cle }}][valeur]"
                                   class="form-control"
                                   value="{{ old('parametres.' . $param->cle . '.valeur', $param->valeur) }}">
                        @endif
                        <small class="text-muted">Type : {{ $param->type }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="text-right mb-4">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save mr-2"></i>Enregistrer tous les paramètres
            </button>
        </div>
    </form>
</div>
@endsection
