@extends('documents.layouts.official')

@section('content')
    <div class="document-number" style="padding-left:40px; font-size:8pt; margin-top:0pt; padding-top:0pt;">
        N° {{ $document['numero_affiche'] ?? $document['numero_document'] }}/MISD/SG/DGELP/DPPALC
    </div>

    <div class="document-title" style="font-size:20px; margin-top:45px;">
        RÉCÉPISSÉ DÉFINITIF DE LEGALISATION
        <div class="document-title-bar-vert"></div>
    </div>

    <div class="content" style="font-size:14px; line-height: normal; text-align: justify;">
        <p class="mb-20">
            <strong>Le Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,</strong>
            <br />
             Agissant conformément à ses attributions en matière de Libertés Publiques, délivre au parti politique 
             ci-après, un récépissé définitif de déclaration conformément à la <strong>Loi n° 016/2025 du 27 juin 2025 relative aux partis 
             politiques en République Gabonaise.</strong> 
        </p>

        <p class="mb-20">
            <strong><u>Dénomination</u> :</strong> {{ strtoupper($organisation['nom']) }}
            @if(!empty($organisation['sigle']))
                ({{ strtoupper($organisation['sigle']) }})
            @endif
        </p>

        <p class="mb-20">
            <strong><u>Siège Social</u> :</strong> {{ $organisation['siege_social'] }}
            @if(!empty($organisation['quartier']))
                , Quartier {{ $organisation['quartier'] }}
            @endif
            @if(!empty($organisation['ville_commune']))
                , {{ $organisation['ville_commune'] }}
            @endif
            @if(!empty($organisation['boite_postale']))
                , BP : {{ $organisation['boite_postale'] }}
            @endif
        </p>

        @if(!empty($organisation['telephone']) && $organisation['telephone'] !== 'Non renseigné')
            <p class="mb-20">
                <strong><u>Téléphone</u> :</strong> {{ $organisation['telephone'] }}@if(!empty($organisation['telephone_2'])) / {{ $organisation['telephone_2'] }}@endif
            </p>
        @endif

        <p class="mb-20">
            <strong><u>Directoire</u> :</strong><br />
            @if(isset($organisation_membres) && count($organisation_membres) > 0)
                @foreach($organisation_membres->take(3) as $index => $membre)
                    <strong><u>{{ $membre['fonction'] }}</u></strong> :
                    {{ $membre['nom_complet'] }}@if($index < min(2, count($organisation_membres) - 1))
                    ;<br />@endif
                @endforeach
            @else
                <span style="text-transform:capitalize"><strong><u>{{ $organisation['president_fonction'] ?? 'Coordinateur Général' }}
                        </u></strong></span>:
                {{ $organisation['president_nom'] ?? '[Nom du Président]' }}
            @endif
        </p>

      

        <p class="mb-20">
            <strong>Pièces annexées à la déclaration et autres prescriptions :</strong>
        </p>

        <div>
            <strong>1- Pièces annexées :</strong>
        </div>
        <div style="margin-bottom:6px; font-size:14px; line-height: normal;">
                   Statuts du parti, règlement intérieur, procès-verbal d’assemblée générale constitutive, 
                   liste des membres du directoire, copies des pièces d’identité ou passeports des membres 
                   fondateurs et dirigeants du parti politique.
        </div>
        
        <div style="page-break-before: always;"></div>

        <p class="mb-4">
            <strong>2- Prescriptions :</strong>
        </p>

        <div style="text-align: justify; font-size:14px; line-height: normal;">
            Toute modification majeure intervenue au niveau des structures ou des programmes d’un parti politique, 
            notamment sur la dénomination, les statuts, le règlement intérieur, le siège ou le logo, les organes dirigeants, 
            doit être notifiée pour information aux services compétents du Ministère de l’Intérieur dans un délai de 
            quinze (15) jours à compter de la date de la modification concernée.

            <br />
            Le Directoire du parti est tenu d’avoir une comptabilité régulière et un inventaire de ses biens meubles et 
            immeubles, de justifier auprès de la Cour des Comptes l’utilisation des subventions et de se conformer aux 
            dispositions en vigueur en matière de transfert de fonds à l’étranger.
        </div>

    </div>

    <div class="signature-block" style="margin-top:1px; padding-top: 1px;">
        @include('documents.components.signature')
    </div>

@endsection