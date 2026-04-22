{{-- Template universel rendu depuis le designer WYSIWYG --}}
@php
    $page = $documentTemplate->page_config ?? [];
    $format = $page['format'] ?? 'A4';
    $orientation = $page['orientation'] ?? 'portrait';
    $marginTop = $page['margin_top'] ?? 20;
    $marginRight = $page['margin_right'] ?? 20;
    $marginBottom = $page['margin_bottom'] ?? 20;
    $marginLeft = $page['margin_left'] ?? 20;
    $renderedBody = $documentTemplate->renderBodyContent($data ?? []);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTemplate->nom }}</title>
    <style>
        @page {
            size: {{ $format }} {{ $orientation }};
            margin: {{ $marginTop }}mm {{ $marginRight }}mm {{ $marginBottom }}mm {{ $marginLeft }}mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12pt;
            color: #333;
            line-height: 1.5;
        }
        .doc-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .doc-body { margin: 20px 0; }
        .doc-signature {
            margin-top: 40px;
            text-align: right;
        }
        .watermark {
            position: fixed;
            top: 40%;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 80pt;
            color: rgba(0, 43, 127, 0.08);
            transform: rotate(-30deg);
            z-index: -1;
        }
        .qr-code {
            position: fixed;
            bottom: 10mm;
            right: 10mm;
            width: 80px;
            height: 80px;
        }
    </style>
</head>
<body>
    @if($documentTemplate->has_watermark)
        <div class="watermark">OFFICIEL</div>
    @endif

    @if(!empty($documentTemplate->header_text))
        <div class="doc-header">{!! $documentTemplate->header_text !!}</div>
    @endif

    <div class="doc-body">
        {!! $renderedBody !!}
    </div>

    @if(!empty($documentTemplate->signature_text))
        <div class="doc-signature">{!! $documentTemplate->signature_text !!}</div>
    @endif

    @if($documentTemplate->has_qr_code && !empty($qr_code_url))
        <img src="{{ $qr_code_url }}" class="qr-code" alt="QR Code">
    @endif
</body>
</html>
