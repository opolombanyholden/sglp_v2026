<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récépissé de Dépôt - Brouillon</title>
    <style>
        @page {
            margin: 20mm 15mm;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }
        
        /* En-tête officiel */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #009e3f;
            padding-bottom: 20px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
        }
        
        .republic-info {
            flex: 1;
            text-align: center;
        }
        
        .republic-info h1 {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .republic-info h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .republic-info p {
            font-size: 11pt;
            font-style: italic;
        }
        
        .ministry-info {
            text-align: center;
            margin-top: 10px;
        }
        
        .ministry-info h3 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #003f7f;
        }
        
        /* Filigrane BROUILLON */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120pt;
            font-weight: bold;
            color: rgba(255, 0, 0, 0.1);
            z-index: -1;
            text-transform: uppercase;
        }
        
        /* Titre document */
        .document-title {
            text-align: center;
            margin: 40px 0 30px 0;
            padding: 15px;
            background-color: #f0f0f0;
            border-left: 5px solid #009e3f;
        }
        
        .document-title h1 {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #003f7f;
            margin-bottom: 5px;
        }
        
        .document-title .subtitle {
            font-size: 12pt;
            color: #666;
            font-style: italic;
        }
        
        /* Numéro de dossier */
        .dossier-number {
            text-align: right;
            margin-bottom: 30px;
            font-size: 11pt;
        }
        
        .dossier-number strong {
            color: #003f7f;
        }
        
        /* Contenu principal */
        .content {
            margin: 30px 0;
        }
        
        .intro-text {
            text-align: justify;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        
        /* Informations association */
        .association-info {
            border: 2px solid #009e3f;
            padding: 20px;
            margin: 25px 0;
            background-color: #f9f9f9;
        }
        
        .association-info h3 {
            font-size: 14pt;
            font-weight: bold;
            color: #003f7f;
            margin-bottom: 15px;
            text-transform: uppercase;
            border-bottom: 2px solid #ffcd00;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 12px;
            align-items: baseline;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 200px;
            color: #333;
        }
        
        .info-value {
            flex: 1;
            color: #000;
        }
        
        /* Pied de page */
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .qr-section {
            text-align: center;
        }
        
        .qr-code {
            width: 100px;
            height: 100px;
            border: 1px solid #ccc;
            margin-bottom: 5px;
        }
        
        .qr-text {
            font-size: 8pt;
            color: #666;
        }
        
        .signature-section {
            text-align: center;
            min-width: 250px;
        }
        
        .signature-date {
            margin-bottom: 10px;
            font-style: italic;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 60px;
        }
        
        .signature-name {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            display: inline-block;
            min-width: 200px;
        }
        
        /* Mentions légales */
        .legal-notice {
            margin-top: 40px;
            padding: 15px;
            background-color: #fff3cd;
            border-left: 4px solid #ffcd00;
            font-size: 10pt;
        }
        
        .legal-notice h4 {
            font-size: 11pt;
            font-weight: bold;
            color: #856404;
            margin-bottom: 8px;
        }
        
        .legal-notice ul {
            list-style-position: inside;
            color: #856404;
        }
        
        .legal-notice li {
            margin-bottom: 5px;
        }
        
        /* Pied de page document */
        .document-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #666;
            padding: 10px;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>

    {{-- Filigrane BROUILLON --}}
    <div class="watermark">BROUILLON</div>

    {{-- En-tête officiel --}}
    <div class="header">
        <div class="header-top">
            <div class="logo">
                {{-- Logo armoiries du Gabon --}}
                <img src="{{ public_path('images/armoiries-gabon.png') }}" alt="Armoiries du Gabon" style="width: 100%; height: auto;">
            </div>
            
            <div class="republic-info">
                <h1>République Gabonaise</h1>
                <h2>Union - Travail - Justice</h2>
                <p>Liberté - Égalité - Fraternité</p>
            </div>
            
            <div class="logo">
                {{-- Espace pour logo ministère --}}
            </div>
        </div>
        
        <div class="ministry-info">
            <h3>Ministère de l'Intérieur et de la Sécurité</h3>
            <p>Direction Générale des Libertés Publiques</p>
        </div>
    </div>

    {{-- Numéro de dossier --}}
    <div class="dossier-number">
        <strong>Numéro de dossier :</strong> {{ $dossier->numero_dossier ?? 'TEMP-XXXX-XXXX' }}
    </div>

    {{-- Titre du document --}}
    <div class="document-title">
        <h1>Récépissé de Dépôt de Dossier</h1>
        <div class="subtitle">Création d'Association - Phase 1</div>
    </div>

    {{-- Contenu principal --}}
    <div class="content">
        <div class="intro-text">
            Le Ministère de l'Intérieur et de la Sécurité, Direction Générale des Libertés Publiques, 
            accuse réception du dossier de demande de création d'association déposé par 
            <strong>{{ $organisation->denomination ?? '[DÉNOMINATION]' }}</strong>, 
            en date du <strong>{{ now()->format('d/m/Y') }}</strong>.
        </div>

        {{-- Informations de l'association --}}
        <div class="association-info">
            <h3>Informations de l'Association</h3>
            
            <div class="info-row">
                <div class="info-label">Dénomination :</div>
                <div class="info-value">{{ $organisation->denomination ?? '[DÉNOMINATION]' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Sigle :</div>
                <div class="info-value">{{ $organisation->sigle ?? 'N/A' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Objet :</div>
                <div class="info-value">{{ $organisation->objet ?? '[OBJET]' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Siège social :</div>
                <div class="info-value">{{ $organisation->siege_social ?? '[SIÈGE SOCIAL]' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Province :</div>
                <div class="info-value">{{ $organisation->province ?? '[PROVINCE]' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Département :</div>
                <div class="info-value">{{ $organisation->departement ?? '[DÉPARTEMENT]' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Nombre de fondateurs :</div>
                <div class="info-value">{{ $organisation->fondateurs_count ?? 0 }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Email :</div>
                <div class="info-value">{{ $organisation->email ?? 'N/A' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Téléphone :</div>
                <div class="info-value">{{ $organisation->telephone ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="intro-text">
            Le dossier est actuellement en cours d'examen par nos services. Vous serez informé(e) 
            de l'avancement du traitement de votre demande par les moyens de communication indiqués 
            lors du dépôt.
        </div>

        {{-- Mentions légales --}}
        <div class="legal-notice">
            <h4>⚠️ IMPORTANT - Document provisoire</h4>
            <ul>
                <li>Ce document est un récépissé de dépôt provisoire (BROUILLON)</li>
                <li>Il atteste uniquement de la réception du dossier</li>
                <li>Il ne confère aucun droit ni personnalité juridique</li>
                <li>Le dossier fera l'objet d'un examen approfondi</li>
                <li>Un récépissé définitif sera délivré après validation complète</li>
                <li>Délai d'instruction estimé : 15 à 30 jours ouvrables</li>
            </ul>
        </div>
    </div>

    {{-- Pied de page avec signature et QR --}}
    <div class="footer">
        <div class="qr-section">
            @if(isset($qrCode))
                <img src="{{ $qrCode }}" alt="QR Code" class="qr-code">
            @else
                <div class="qr-code" style="display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                    [QR CODE]
                </div>
            @endif
            <div class="qr-text">Scanner pour vérifier l'authenticité</div>
        </div>

        <div class="signature-section">
            <div class="signature-date">
                Fait à Libreville, le {{ now()->format('d/m/Y') }}
            </div>
            <div class="signature-title">
                Le Directeur Général<br>
                des Libertés Publiques
            </div>
            @if(isset($signature))
                <img src="{{ $signature }}" alt="Signature" style="max-width: 150px; margin: 20px 0;">
            @endif
            <div class="signature-name">
                {{ $signataire ?? '[NOM DU SIGNATAIRE]' }}
            </div>
        </div>
    </div>

    {{-- Pied de page document --}}
    <div class="document-footer">
        Ministère de l'Intérieur et de la Sécurité - Direction Générale des Libertés Publiques<br>
        BP 2110 Libreville - Gabon | Tél: +241 01 XX XX XX | Email: contact@minterieur.ga
    </div>

</body>
</html>