{{-- ✅ Image de fond - Ajusté pour PdfTemplate Helper (margin_bottom: 45mm) --}}
@if(isset($bg_pied_page_base64) && $bg_pied_page_base64)
    <div
        style="position: fixed; bottom: -4.5cm; left: -1.5cm; right: -1.5cm; margin: 0; padding: 0; z-index: -1; overflow: visible;">
        <img src="{{ $bg_pied_page_base64 }}" alt="Pied de page"
            style="width: 100%; height: auto; display: block; margin: 0; padding: 0;">
    </div>
@endif

{{-- Numéro de référence (le logo et header_text sont dans SetHTMLHeader) --}}
@php
    // Code type selon organisation
    $typeCode = match ($type_organisation ?? $organisation->type ?? 'association') {
        'association' => 'AS',
        'ong' => 'ONG',
        'parti_politique' => 'PP',
        'confession_religieuse' => 'CR',
        default => 'AS'
    };
    $annee = date('Y');
    $sequence = str_pad($dossier->id ?? $organisation->id ?? 1, 4, '0', STR_PAD_LEFT);
    $numeroGenere = "N° {$typeCode}{$annee}{$sequence}/MISD/SG/DGELP/DPPALC";
@endphp
<div style="text-align: right; font-size: 14pt; font-weight: bold; margin: 10px 0;">
    {{ $numero_administratif ?? $numero_reference ?? $numeroGenere }}
</div>

{{-- Titre --}}
<div style="text-align: center; margin: 30px 0;">
    <h1
        style="color: #009e3f; border: 2px solid #009e3f; padding: 10px 20px; display: inline-block; font-size: 16pt; margin: 0;">
        RÉCÉPISSÉ PROVISOIRE
    </h1>
</div>

{{-- Contenu --}}
@php
    // Type d'organisation
    $typeOrg = $type_organisation ?? $organisation->type ?? 'association';

    // Libellé du type
    $typeLibelle = match ($typeOrg) {
        'parti_politique' => 'du parti politique',
        'ong' => 'de l\'Organisation Non Gouvernementale (ONG)',
        'confession_religieuse' => 'de la confession religieuse',
        default => 'de l\'association à but non lucratif'
    };

    // Domaine d'activité
    $domaine = $organisation->domaine ?? $organisation->objet ?? $domaine_activite ?? '';

    // Fonction du représentant
    $fonction = $fonction_dirigeant ?? $fonction_representant ?? match ($typeOrg) {
        'parti_politique' => 'Secrétaire Général(e)',
        default => 'Président(e)'
    };

    // Loi de référence
    $loiRef = match ($typeOrg) {
        'parti_politique' => 'la loi n°016/2025 du 27 juin 2025 relative aux partis politiques',
        'ong' => 'la loi n°001/2005 du 4 février 2005 relative aux ONG',
        default => 'la loi n° 35/62 du 10 décembre 1962 relative aux associations'
    };

    // Type déclaration
    $typeDeclaration = match ($typeOrg) {
        'parti_politique' => 'de parti politique',
        'ong' => 'd\'ONG',
        default => 'd\'association'
    };
@endphp

<div style="text-align: justify; line-height: 1.8; margin: 20px 0;">
    Nous soussignés, Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,
    attestons que <strong>{{ $civilite ?? 'Monsieur' }} {{ $nom_prenom ?? 'NOM PRÉNOM' }}</strong>
    de nationalité <strong>{{ ucfirst($nationalite ?? 'Gabonaise') }}</strong>,
    <strong>{{ $fonction }}</strong> {{ $typeLibelle }}@if($domaine), œuvrant dans le domaine
    <strong>{{ ucfirst($domaine) }}</strong>@endif dénommée :
</div>

{{-- Nom organisation --}}
<div style="text-align: center; font-size: 14pt; font-weight: bold; margin: 20px 0; color: #000;">
    «
    {{ strtoupper($organisation->nom ?? $nom_organisation ?? 'NOM ORGANISATION') }}@if(isset($sigle_organisation) && $sigle_organisation)
    ({{ strtoupper($sigle_organisation) }})@elseif(isset($organisation->sigle) && $organisation->sigle)
    ({{ strtoupper($organisation->sigle) }})@endif »
</div>

{{-- Suite contenu --}}
<div style="text-align: justify; line-height: 1.8; margin: 20px 0;">
    Dont le siège social est fixé à
    <strong>{{ $adresse_siege ?? $organisation->adresse ?? 'Libreville' }}</strong>,
    @if(isset($telephone) && $telephone !== 'Non renseigné')
        Téléphone : <strong>{{ $telephone }}</strong>,
    @elseif(isset($org_telephone) && $org_telephone)
        Téléphone : <strong>{{ $org_telephone }}</strong>,
    @endif
    a déposé à nos services un dossier complet visant à obtenir un récépissé définitif de déclaration
    {{ $typeDeclaration }} conformément aux dispositions de {{ $loiRef }} en République Gabonaise.
    <br><br>
    En foi de quoi, le présent récépissé est délivré à
    l'intéressé{{ ($civilite ?? '') === 'Madame' ? 'e' : '' }} pour servir et valoir ce que de droit.
</div>

{{-- Date --}}
<div style="text-align: right; margin-top: 30px; font-style: italic;">
    Fait à Libreville, le {{ $date_generation ?? now()->format('d/m/Y') }}
</div>

{{-- Signature (sera dans le footer via PdfTemplateHelper) --}}
<div style="margin-top: 30px; text-align: right;">
    Le Ministre de l'Intérieur, de la Sécurité<br>
    et de la Décentralisation
    <br><br><br>
</div>