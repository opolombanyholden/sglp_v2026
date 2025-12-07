<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récépissé Provisoire - {{ $organisation->nom ?? $nom_organisation }}</title>
    <style>
        @page {
            margin: 1.5cm 2cm;
        }
        
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        
        /* ===== EN-TÊTE MINISTÉRIEL ===== */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .header-left {
            width: 60%;
            vertical-align: top;
            text-align: left;
        }
        
        .header-right {
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        
        .ministry-header {
            font-size: 10pt;
            line-height: 1.3;
        }
        
        .ministry-name {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
        }
        
        .separator {
            font-size: 9pt;
            margin: 2px 0;
        }
        
        .direction {
            font-size: 9pt;
            text-transform: uppercase;
        }
        
        .logo-box {
            text-align: center;
        }
        
        .logo-box img {
            max-height: 70px;
            margin: 3px;
        }
        
        /* ===== NUMÉRO DE RÉFÉRENCE ===== */
        .reference-number {
            font-weight: bold;
            font-size: 11pt;
            margin: 25px 0 20px 0;
        }
        
        /* ===== TITRE DU DOCUMENT ===== */
        .document-title {
            text-align: center;
            margin: 25px 0;
        }
        
        .document-title h1 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
            display: inline;
        }
        
        /* ===== CONTENU PRINCIPAL ===== */
        .main-content {
            text-align: justify;
            line-height: 1.6;
            margin: 20px 0;
            font-size: 12pt;
        }
        
        /* ===== NOM ORGANISATION ===== */
        .organisation-name {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 15px 0;
        }
        
        /* ===== DATE ET SIGNATURE ===== */
        .date-location {
            text-align: right;
            margin-top: 30px;
            font-size: 12pt;
        }
        
        .signature-section {
            text-align: right;
            margin-top: 15px;
            font-size: 11pt;
            line-height: 1.4;
        }
        
        .signature-title {
            font-weight: bold;
        }
        
        .signature-space {
            height: 70px;
        }
        
        /* ===== QR CODE ===== */
        .qr-section {
            position: fixed;
            bottom: 20px;
            left: 40px;
            width: 90px;
        }
        
        .qr-box {
            text-align: center;
        }
        
        .qr-image {
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>

    <!-- ===== EN-TÊTE MINISTÉRIEL ===== -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="ministry-header">
                    <div class="ministry-name">MINISTÈRE DE L'INTÉRIEUR, DE LA SÉCURITÉ</div>
                    <div class="ministry-name">ET DE LA DÉCENTRALISATION</div>
                    <div class="separator">--------------</div>
                    <div class="direction">SECRÉTARIAT GÉNÉRAL</div>
                    <div class="separator">--------------</div>
                    <div class="direction">DIRECTION GÉNÉRALE DES ÉLECTIONS</div>
                    <div class="direction">ET DES LIBERTÉS PUBLIQUES</div>
                    <div class="separator">--------------</div>
                    <div class="direction">DIRECTION DES PARTIS POLITIQUES</div>
                    <div class="direction">ASSOCIATIONS ET LIBERTÉ DE CULTE</div>
                </div>
            </td>
            <td class="header-right">
                <div class="logo-box">
                    {{-- Logos République Gabonaise si disponibles --}}
                </div>
            </td>
        </tr>
    </table>

    <!-- ===== NUMÉRO DE RÉFÉRENCE ===== -->
    @php
        // Code type selon organisation
        $typeCode = match($type_organisation ?? $organisation->type ?? 'association') {
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
    <div class="reference-number">
        {{ $numero_administratif ?? $numero_reference ?? $numeroGenere }}
    </div>

    <!-- ===== TITRE ===== -->
    <div class="document-title">
        <h1>RÉCÉPISSÉ PROVISOIRE</h1>
    </div>

    <!-- ===== CONTENU ===== -->
    @php
        // Type d'organisation
        $typeOrg = $type_organisation ?? $organisation->type ?? 'association';
        
        // Libellé du type
        $typeLibelle = match($typeOrg) {
            'parti_politique' => 'du parti politique',
            'ong' => 'de l\'Organisation Non Gouvernementale (ONG)',
            'confession_religieuse' => 'de la confession religieuse',
            default => 'de l\'association à but non lucratif'
        };
        
        // Domaine d'activité
        $domaine = $organisation->domaine ?? $organisation->objet ?? $domaine_activite ?? '';
        
        // Fonction du représentant
        $fonction = $fonction_dirigeant ?? $fonction_representant ?? match($typeOrg) {
            'parti_politique' => 'Secrétaire Général(e)',
            default => 'Président(e)'
        };
        
        // Loi de référence
        $loiRef = match($typeOrg) {
            'parti_politique' => 'la loi n°016/2025 du 27 juin 2025 relative aux partis politiques',
            'ong' => 'la loi n°001/2005 du 4 février 2005 relative aux ONG',
            default => 'la loi n° 35/62 du 10 décembre 1962 relative aux associations'
        };
        
        // Type déclaration
        $typeDeclaration = match($typeOrg) {
            'parti_politique' => 'de parti politique',
            'ong' => 'd\'ONG',
            default => 'd\'association'
        };
    @endphp

    <div class="main-content">
        Nous soussignés, Ministre de l'Intérieur, de la Sécurité et de la Décentralisation, 
        attestons que <strong>{{ $civilite ?? 'Monsieur' }} {{ $nom_prenom ?? 'NOM PRÉNOM' }}</strong> 
        de nationalité <strong>{{ ucfirst($nationalite ?? 'Gabonaise') }}</strong>, 
        <strong>{{ $fonction }}</strong> {{ $typeLibelle }}@if($domaine), œuvrant dans le domaine <strong>{{ ucfirst($domaine) }}</strong>@endif dénommée :
    </div>

    <!-- ===== NOM ORGANISATION ===== -->
    <div class="organisation-name">
        « {{ strtoupper($organisation->nom ?? $nom_organisation ?? 'NOM ORGANISATION') }}@if(isset($sigle_organisation) && $sigle_organisation) ({{ strtoupper($sigle_organisation) }})@elseif(isset($organisation->sigle) && $organisation->sigle) ({{ strtoupper($organisation->sigle) }})@endif »
    </div>

    <!-- ===== SUITE CONTENU ===== -->
    <div class="main-content">
        Dont le siège social est fixé à <strong>{{ $adresse_siege ?? $organisation->adresse ?? 'Libreville' }}</strong>,
        @if(isset($telephone) && $telephone !== 'Non renseigné')
            Téléphone : <strong>{{ $telephone }}</strong>,
        @elseif(isset($org_telephone) && $org_telephone)
            Téléphone : <strong>{{ $org_telephone }}</strong>,
        @endif
        a déposé à nos services un dossier complet visant à obtenir un récépissé définitif de déclaration {{ $typeDeclaration }} conformément aux dispositions de {{ $loiRef }} en République Gabonaise.
        <br><br>
        En foi de quoi, le présent récépissé est délivré à l'intéressé{{ ($civilite ?? '') === 'Madame' ? 'e' : '' }} pour servir et valoir ce que de droit.
    </div>

    <!-- ===== DATE ===== -->
    <div class="date-location">
        Fait à Libreville, le {{ $date_generation ?? now()->format('d/m/Y') }}
    </div>

    <!-- ===== SIGNATURE ===== -->
    <div class="signature-section">
        <div class="signature-title">Le Ministre de l'Intérieur, de la Sécurité</div>
        <div class="signature-title">et de la Décentralisation</div>
        <div class="signature-space"></div>
    </div>

    <!-- ===== QR CODE ===== -->
    <div class="qr-section">
        <div class="qr-box">
            @if(isset($qr_code) && $qr_code)
                @php
                    $qrService = app(\App\Services\QrCodeService::class);
                    $qrBase64 = $qrService->getQrCodeBase64ForPdf($qr_code);
                @endphp
                @if($qrBase64)
                    <img src="{{ $qrBase64 }}" alt="QR Code" width="80" height="80" class="qr-image">
                @elseif(!empty($qr_code->svg_content))
                    <div style="width:80px;height:80px;margin:0 auto;overflow:hidden;">
                        {!! str_replace(['width="150"','height="150"'],['width="80"','height="80"'],$qr_code->svg_content) !!}
                    </div>
                @endif
            @endif
        </div>
    </div>

</body>
</html>