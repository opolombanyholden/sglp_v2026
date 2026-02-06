{{-- ✅ Image de fond - Ajusté pour PdfTemplateHelper (margin_bottom: 45mm) --}}
@if(isset($bg_pied_page_base64) && $bg_pied_page_base64)
    <div
        style="position: fixed; bottom: -4.5cm; left: -1.5cm; right: -1.5cm; margin: 0; padding: 0; z-index: -1; overflow: visible;">
        <img src="{{ $bg_pied_page_base64 }}" alt="Pied de page"
            style="width: 100%; height: auto; display: block; margin: 0; padding: 0;">
    </div>
@endif

{{-- Numéro de référence (le logo et header_text sont dans SetHTMLHeader) --}}
<div style="text-align: left; font-size: 18px; font-weight: bold; margin: 150px 0 30px 0;">
    N° {{ $numero_administratif ?? 'XXXX/MISD/SG/DGELP/DPPALC' }}
</div>

{{-- Titre du document --}}
<div style="text-align: center; margin: 30px 0;">
    <div style="font-size: 20px; font-weight: bold; letter-spacing: 1px;">RÉCÉPISSÉ DÉFINITIF DE LÉGALISATION</div>
    <div style="width: 70%; margin: 10px auto; height: 4px; background-color: #F00;"></div>
</div>

{{-- Contenu principal --}}
<div style="text-align: justify; line-height: 1.8; margin: 20px 0;">
    <p><strong>Le Ministre de l'Intérieur, de la Sécurité et de la Décentralisation,</strong></p>

    <p>
        Agissant conformément à ses attributions en matière de Libertés Publiques, délivre à
        {{ $civilite ?? 'Monsieur' }}
        <strong>{{ $nom_prenom ?? 'NOM PRÉNOM' }}</strong>, de nationalité {{ $nationalite ?? 'gabonaise' }},
        domicilié à {{ $domicile ?? 'ADRESSE' }},

        @if(isset($telephone) && $telephone !== 'Non renseigné')
            Téléphone : <span>{{ $telephone }}</span>,
        @else
            @if(isset($org_telephone) && $org_telephone !== 'Non renseigné')
                Téléphone : <span>{{ $org_telephone }}</span>,
            @endif
        @endif

        un récépissé définitif de déclaration de {{ $type_organisation_label ?? 'Parti politique' }},
        conformément
        <strong>{{ $loi_reference ?? 'à la loi n°016/2025 du 27 juin 2025 relative aux partis politiques en République Gabonaise' }}</strong>.
    </p>
</div>

{{-- Détails de l'organisation --}}
<div style="margin: 20px 0;">
    <div style="margin-bottom: 10px;">
        <span style="text-decoration: underline;">Dénomination :</span>
        <span style="font-weight: bold;">« {{ $nom_organisation }}
            »</span>{{ $sigle_organisation ? ', en abrégé ' : '' }}<span
            style="font-weight: bold;">{{ $sigle_organisation ?? '' }}</span>
    </div>

    <div style="margin-bottom: 10px;">
        <span style="text-decoration: underline;">Siège Social</span> :
        {{ $adresse_siege ?? $org_adresse ?? 'Libreville, GABON' }} ;
        @if(isset($boite_postale) && $boite_postale)
            <strong>BP : {{ $boite_postale }} ; </strong>
        @endif
        <strong>Tél : {{ $org_telephone ?? 'Non renseigné' }}.</strong>
    </div>

    @if(isset($objet_organisation) && $objet_organisation !== 'Non spécifié')
        <div style="margin-bottom: 10px;">
            <span style="text-decoration: underline;">Objet :</span> {{ $objet_organisation }}
        </div>
    @endif
</div>

{{-- Section Directoire --}}
<div style="margin: 20px 0;">
    <div style="margin-bottom: 10px;">
        <span style="text-decoration: underline;">Directoire :</span>
    </div>
    @if(isset($dirigeants) && is_array($dirigeants) && count($dirigeants) > 0)
        @foreach($dirigeants as $dirigeant)
            <div style="margin-left: 20px; margin-bottom: 5px;">
                • <span style="text-decoration: underline;">{{ $dirigeant['fonction'] ?? 'Dirigeant' }} :</span>
                {{ $dirigeant['nom_complet'] ?? 'Non désigné' }} ;
            </div>
        @endforeach
    @else
        <div style="margin-left: 20px; margin-bottom: 5px;">
            • <span style="text-decoration: underline;">Président-Fondateur :</span> {{ $nom_prenom ?? 'Non désigné' }} ;
        </div>
        <div style="margin-left: 20px; margin-bottom: 5px;">
            • <span style="text-decoration: underline;">Secrétaire Général :</span>
            {{ $secretaire_general ?? 'Non désigné' }} ;
        </div>
        <div style="margin-left: 20px; margin-bottom: 5px;">
            • <span style="text-decoration: underline;">Trésorier :</span> {{ $tresorier ?? 'Non désigné' }}.
        </div>
    @endif
</div>

{{-- Pièces annexées --}}
<div style="margin: 20px 0;">
    <div style="font-weight: bold; margin-bottom: 10px;">Pièces annexées à la déclaration et autres prescriptions :
    </div>

    <div style="margin-bottom: 15px;">
        <span style="text-decoration: underline;">1. Pièces annexées :</span>
        <div style="margin-left: 20px; text-align: justify;">
            @if(isset($pieces_annexees) && is_array($pieces_annexees) && count($pieces_annexees) > 0)
                @foreach($pieces_annexees as $piece)
                    {{ $piece }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            @else
                Statuts du parti, règlement intérieur, procès-verbal de la réunion constitutive du parti, liste des
                membres du directoire,
                copies certifiées conformes des cartes nationales d'identité ou passeports des membres fondateurs et
                dirigeants du parti politique,
                ainsi que leurs extraits de casier judiciaire, et l'état d'adhésion sur l'ensemble du territoire
                national.
            @endif
        </div>
    </div>
</div>

{{-- Prescriptions --}}
<div style="margin: 20px 0;">
    <div style="font-weight: bold; margin-bottom: 10px;">2 - Prescriptions :</div>

    @if(isset($prescriptions) && is_array($prescriptions) && count($prescriptions) > 0)
        @foreach($prescriptions as $prescription)
            <div style="margin-left: 20px; margin-bottom: 10px; text-align: justify;">
                {{ $prescription }}
            </div>
        @endforeach
    @else
        <div style="margin-left: 20px; margin-bottom: 10px; text-align: justify;">
            Toute modification majeure intervenue au niveau des structures ou des programmes d'un parti politique,
            notamment sur la dénomination,
            les statuts ; le règlement intérieur, le siège, l'emblème ou le logo, les organes dirigeants, doit être
            notifiée pour information
            aux services compétents du Ministère de l'Intérieur dans un délai de quinze (15) jours à compter de la
            date de la modification concernée.
        </div>

        <div style="margin-left: 20px; margin-bottom: 10px; text-align: justify;">
            Le Directoire du parti est tenu d'avoir une comptabilité régulière et un inventaire de ses biens meubles
            et immeubles,
            de justifier auprès de la Cour des Comptes l'utilisation des subventions et de se conformer aux
            dispositions en vigueur
            en matière de transfert de fonds à l'étranger.
        </div>
    @endif
</div>

{{-- Date et lieu --}}
<div style="text-align: right; margin-top: 30px; font-style: italic;">
    Fait à Libreville, le {{ $date_generation ?? now()->format('d/m/Y') }}
</div>

{{-- Signature (sera dans le footer via PdfTemplateHelper) --}}
<div style="margin-top: 30px; text-align: right;">
    <strong>Le Ministre de l'Intérieur, de la Sécurité<br>et de la Décentralisation</strong>
    <br><br>
    <strong>{{ $ministre_nom ?? 'Hermann IMMONGAULT' }}</strong>
</div>

{{-- Ampliations --}}
<div style="margin-top: 30px;">
    <u>AMPLIATIONS :</u>
    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
        <div>PR: 2</div>
        <div>VPG: 2</div>
        <div>MEF: 2</div>
        <div>CND: 6</div>
        <div>MISD: 10</div>
        <div>J.O: 2</div>
        <div>PARTI: 2</div>
        <div>CHRONO: 5</div>
        <div>ARCHIVES: 10</div>
    </div>
</div>

{{-- Copie --}}
<p style="margin-top: 15px; font-size: 11pt;">
    Copie : J.O
</p>