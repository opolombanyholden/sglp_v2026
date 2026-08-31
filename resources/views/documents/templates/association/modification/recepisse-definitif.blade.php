@extends('documents.layouts.official')

@section('content')
    <div class="document-number" style="padding-left:40px; font-size:8pt; margin-top:0pt; padding-top:0pt;">
        N° {{ $document['numero_affiche'] ?? $document['numero_document'] }}/MISD/SG/DGELP/DPPALC
    </div>

    <div class="document-title" style="font-size:20px; margin-top:45px;">
        RÉCÉPISSÉ DE DÉCLARATION DE MODIFICATION
        <div class="document-title-bar-vert"></div>
    </div>

    @php
        // Les données de modifications sont préparées par DocumentGenerationService :
        // - les nouvelles valeurs ont été appliquées à $organisation
        // - $modifications['types_modifies'] signale les blocs à afficher (comparaison ancien/nouveau faite côté service)
        $modifData = $modifications ?? [];
        $typeModification = $modifData['type_modification'] ?? null;
        $justification = $modifData['justification'] ?? null;
        $champsModifies = $modifData['modifications'] ?? [];
        $typesModifies = $modifData['types_modifies'] ?? [];
        $bureauModifications = $modifData['bureau_modifications'] ?? [];
    @endphp

    <div class="content" style="font-size:14px; line-height: normal; text-align: justify;">
        <p class="mb-20">
            <strong>Le Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,</strong>
        </p>

        <p class="mb-20" style="text-align: justify;">

            En application des dispositions de l'article 11 de la Loi n° 35/62 du 10 Décembre 1962 relative aux
            @if(($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique')
                partis politiques
            @else
                associations
            @endif,
            délivre
            {{ ($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique' ? 'au Parti politique' : 'à l\'Association' }}
            dénommé{{ ($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique' ? '' : 'e' }}
            @php
                $nomCorps = !empty($organisation['nom_precedent']) ? $organisation['nom_precedent'] : ($organisation['nom'] ?? '');
                $sigleCorps = array_key_exists('sigle_precedent', $organisation) ? $organisation['sigle_precedent'] : ($organisation['sigle'] ?? null);
            @endphp
            « <strong>{{ strtoupper($nomCorps) }}</strong>
            @if(!empty($sigleCorps))
                ({{ strtoupper($sigleCorps) }})
            @endif
            »,
            @if(!empty($organisation['numero_recepisse']))
                légalisé{{ ($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique' ? '' : 'e' }} sous
                le récépissé définitif
                n° {{ $organisation['numero_recepisse'] }}
                @if(!empty($organisation['date_recepisse']))
                    daté du {{ \Carbon\Carbon::parse($organisation['date_recepisse'])->translatedFormat('d F Y') }}
                @endif,
            @endif
            le présent récépissé de déclaration de modification.
        </p>

        <p class="mb-20" style="text-align: justify;">
            Les modifications intervenues au sein
            {{ ($organisation['type_code'] ?? $organisation['type'] ?? '') === 'parti_politique' ? 'dudit parti politique' : 'de ladite association' }}
            concernent :
       

        {{-- Justification de la modification (remplace la liste à puces) --}}
        @if(!empty($justification))
            <strong>
                {!! nl2br(e($justification)) !!}
            </strong>
        @endif
 </p>
        <p class="mb-20" style="margin-top: 20px;">
            <strong><u>Nouvelles informations :</u></strong>
        </p>

        {{-- Dénomination : affichée uniquement si le nom ou le sigle a été modifié --}}
        @if(isset($typesModifies['denomination']))
            <p class="mb-10" style="text-align: justify;">
                <strong><u>Dénomination :</u></strong>
                {{ strtoupper($organisation['nom']) }}
                @if(!empty($organisation['sigle']))
                    ({{ strtoupper($organisation['sigle']) }})
                @endif
            </p>
        @endif

        {{-- Objet : affiché uniquement si modifié --}}
        @if(isset($typesModifies['objet']))
            <p class="mb-10" style="text-align: justify;">
                <strong><u>Objet :</u></strong>
                {{ $organisation['objet'] ?? '' }}
            </p>
        @endif

        {{-- Bureau : affiché uniquement si le bureau a été modifié --}}
        @if(isset($typesModifies['bureau']))
            <p class="mb-10">
                <strong><u>Bureau :</u></strong>
            </p>
            <div style="margin-left: 20px; margin-bottom: 10px;">
                @foreach($bureauModifications as $membre)
                    <p style="margin-bottom: 2px;">
                        <strong><u>{{ $membre['fonction'] ?? 'Membre' }} :</u></strong>
                        <span style="text-transform: uppercase;"> {{ strtoupper(($membre['nom'] ?? '')) }}</span>
                        <span style="text-transform: capitalize;"> {{ strtoupper(' ' . ($membre['prenom'] ?? '')) }} ;</span>
                    </p>
                @endforeach
            </div>
        @endif

        {{-- Contact : affiché uniquement si modifié --}}
        @if(isset($typesModifies['contact']))
            <p class="mb-10" style="text-align: justify;">
                <strong><u>Contact :</u></strong>
                @if(!empty($organisation['telephone']))
                    {{ $organisation['telephone'] }}
                @endif
                @if(!empty($organisation['telephone_2']))
                    / {{ $organisation['telephone_2'] }}
                @endif
                @if(!empty($organisation['email']))
                    / {{ $organisation['email'] }}
                @endif
                .
            </p>
        @endif

        {{-- Adresse : affichée uniquement si modifiée --}}
        @if(isset($typesModifies['adresse']))
            <p class="mb-20" style="text-align: justify;">
                <strong><u>Adresse :</u></strong>
                @if(!empty($organisation['ville_commune']))
                    {{ $organisation['ville_commune'] }}
                @endif
                @if(!empty($organisation['quartier']))
                    , {{ $organisation['quartier'] }}
                @endif
                @if(!empty($organisation['siege_social']))
                    , {{ $organisation['siege_social'] }}
                @endif
                - Gabon.
            </p>
        @endif

        {{-- Pièces Annexées --}}
        <p class="mb-10" style="margin-top: 20px;">
            <strong><u>Pièces Annexées :</u></strong>
        </p>

        <ul style="margin-left: 30px; margin-bottom: 15px; font-size: 14px; line-height: normal;">
            <li>Procès-verbal d'Assemblée Générale Extraordinaire ;</li>
            <li>Récépissé définitif de déclaration ;</li>
            @if(!empty($bureauModifications))
                <li>La liste actualisée des membres du comité directeur ;</li>
            @endif
            @if(isset($typesModifies['denomination']) || isset($typesModifies['objet']))
                <li>Statuts modifiés.</li>
            @endif
        </ul>
    </div>

    <div class="signature-block" style="margin-top: 30px; padding-top: 10px;">
        @include('documents.components.signature')
    </div>

@endsection