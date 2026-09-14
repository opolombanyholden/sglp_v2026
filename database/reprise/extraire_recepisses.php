<?php
/**
 * Reprise de l'existant — étape 1 : extraction (lecture seule).
 *
 * Parcourt les récépissés archivés (.docx / .pdf), en extrait les champs
 * structurés et produit un tableur de contrôle. N'écrit RIEN en base.
 *
 * Usage :  php database/reprise/extraire_recepisses.php [dossier] [sortie.csv]
 *
 * Le tableur produit sert de pièce de contrôle : il doit être relu et corrigé
 * avant l'étape 2 (import). Seules les lignes portant un numéro sont
 * importables — les autres restent à compléter à la main.
 */

$source = $argv[1] ?? __DIR__ . '/../../backups/recepisse';
$sortie = $argv[2] ?? __DIR__ . '/recepisses_a_reprendre.csv';

if (!is_dir($source)) {
    fwrite(STDERR, "Dossier introuvable : $source\n");
    exit(1);
}

// Gabarits vierges : à ne jamais reprendre.
const GABARITS = ['template', 'RECEPISSE DEFINITIF ASSOCIATION', 'RECEPISSE PROVISOIR ASSOCIATION'];

const MOTIF_NUMERO = '/(?:PP|AS)20\d{6}|DOS-20\d{2}-\d{5}/';

function texteDocx(string $chemin): string
{
    $zip = new ZipArchive();
    if ($zip->open($chemin) !== true) {
        return '';
    }
    $xml = $zip->getFromName('word/document.xml') ?: '';
    $zip->close();

    // Conserver les frontières de paragraphe avant de retirer le balisage
    $xml = str_replace(['</w:p>', '<w:br/>'], "\n", $xml);

    return normaliser(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
}

function textePdf(string $chemin): string
{
    $sortie = [];
    exec('pdftotext -q ' . escapeshellarg($chemin) . ' - 2>/dev/null', $sortie);

    return normaliser(implode("\n", $sortie));
}

function normaliser(?string $t): string
{
    $t = (string) $t;
    // Certains documents contiennent des séquences UTF-8 invalides (copier-coller
    // depuis Word). On les écarte ici : sinon toute regex en mode /u échoue en
    // renvoyant null ou false, et l'erreur ressort loin de sa cause.
    $t = mb_convert_encoding($t, 'UTF-8', 'UTF-8');
    $t = str_replace(["\xC2\xA0", '’'], [' ', "'"], $t);
    // Avec /u, preg_replace renvoie null sur une séquence UTF-8 invalide :
    // on garde alors la valeur précédente plutôt que de propager le null.
    $t = preg_replace('/[ \t]+/u', ' ', $t) ?? $t;
    $t = preg_replace('/\n{2,}/u', "\n", $t) ?? $t;

    return trim($t);
}

/** Première capture non vide parmi plusieurs motifs. */
function premier(string $texte, array $motifs): string
{
    foreach ($motifs as $motif) {
        if (preg_match($motif, $texte, $m)) {
            $valeur = trim($m[1] ?? '');
            $valeur = trim($valeur, " \t\n:;,.«»\"'");
            if ($valeur !== '') {
                return $valeur;
            }
        }
    }

    return '';
}

/**
 * Le dossier d'archivage fait foi. Le contenu ne sert qu'à isoler les
 * confessions religieuses au sein des associations — et uniquement sur la
 * dénomination : l'en-tête ministériel contient « LIBERTE DE CULTE » sur
 * TOUS les récépissés, il ne peut servir d'indice.
 */
function typeOrganisation(string $chemin, string $denomination): string
{
    if (stripos($chemin, 'PARTIS POLITIQUE') !== false) {
        return 'parti_politique';
    }
    if (preg_match('/\b(EGLISE|MINISTERE|MISSION|ASSEMBLEE|EVANGEL|PAROISSE|TEMPLE)/ui', $denomination)) {
        return 'confession_religieuse';
    }

    return 'association';
}

/**
 * La nature se lit sur la LIGNE DE TITRE du document, isolée sur sa propre
 * ligne juste après le numéro. On ne peut pas se fier à la simple présence des
 * mots : un récépissé provisoire mentionne dans son corps « dans l'attente du
 * récépissé définitif », et le corps du texte est justement ce que contient le
 * segment analysé.
 *
 * Pour les fichiers regroupant plusieurs récépissés, le titre reste celui du
 * document : une éventuelle mixité provisoire/définitif dans un même fichier
 * doit être corrigée à la relecture du tableur.
 */
function nature(string $segment, string $texteComplet = ''): string
{
    $source = $texteComplet !== '' ? $texteComplet : $segment;

    // 1. Une ligne ne contenant que le titre
    if (preg_match('/^[^\S\n]*R[EÉ]C[EÉ]PISS[EÉ]?[^\S\n]+(D[EÉ]FINITIF|PROVISOIRE?)[^\S\n]*$/miu', $source, $m)) {
        return stripos($m[1], 'PROVISOIR') === 0 ? 'provisoire' : 'definitif';
    }

    // 2. À défaut, la première mention titrée du document
    if (preg_match('/R[EÉ]C[EÉ]PISS[EÉ]?\s+(D[EÉ]FINITIF|PROVISOIRE?)/ui', $source, $m)) {
        return stripos($m[1], 'PROVISOIR') === 0 ? 'provisoire' : 'definitif';
    }

    return 'indetermine';
}

function segmenter(string $texte): array
{
    // « Dénomination » (définitifs) ou « Nous soussignés » (provisoires)
    $parts = preg_split('/(?=Dénomination|Nous soussignés)/u', $texte) ?: [$texte];
    $parts = array_values(array_filter(
        $parts,
        fn($p) => str_contains($p, 'Dénomination') || str_contains($p, 'Nous soussignés')
    ));

    return $parts ?: [$texte];
}

function champs(string $segment, string $texteComplet): array
{
    return [
        'denomination' => premier($segment, [
            '/Dénomination de l\'Association\s*:\s*([^\n(:]+)/u',
            '/Dénomination\s*:\s*«\s*([^»]+)»/u',
            '/Dénomination[^:]*:\s*([^\n(]+)/u',
            // Gabarit provisoire : le nom figure entre guillemets, sans libellé
            '/«\s*([^»]{3,150}?)\s*»/u',
        ]),
        'sigle' => premier($segment, [
            '/en abrégé\s+([A-ZÀ-Ü0-9\-\. ]{2,40})/u',
            '/Dénomination[^:]*:[^(\n]*\(([^)]{2,40})\)/u',
        ]),
        'objet' => premier($segment, [
            '/Objet\s*:\s*(.{10,400}?)(?=\s*(?:Siège|Siege|Directoire|Pièces))/us',
        ]),
        'siege_social' => premier($segment, [
            '/Siège Social\s*:\s*(.{3,200}?)(?=\s*(?:Directoire|Objet|Pièces|Bureau|$))/us',
            // Gabarit provisoire
            '/siège social est fixé à\s*(.{3,120}?)(?=\s*(?:,\s*(?:Téléphone|Tél)|\.|,\s*a déposé))/us',
        ]),
        'telephone' => premier($segment, [
            '/(?:Téléphone|Tél)\s*[:.]?\s*([0-9][0-9 .\/\-]{6,60})/u',
        ]),
        'bureau' => premier($segment, [
            '/(?:Directoire|Bureau[^:]*)\s*:\s*(.{5,400}?)(?=\s*Pièces)/us',
        ]),
        'declarant' => premier($segment, [
            '/attestons que\s+((?:Monsieur|Madame|M\.|Mme)[^,«\n]{3,80})/u',
        ]),
        'date_document' => premier($texteComplet, [
            '/Libreville,?\s+le\s+([0-9]{1,2}[^\n,]{3,30}20\d{2})/u',
        ]),
    ];
}

// ── Parcours ─────────────────────────────────────────────────────────────
$fichiers = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
$lignes = [];
$ignores = 0;

foreach ($fichiers as $fichier) {
    $chemin = $fichier->getPathname();
    $nom = $fichier->getFilename();
    $ext = strtolower($fichier->getExtension());

    if (!in_array($ext, ['docx', 'pdf'], true) || str_starts_with($nom, '~$') || str_starts_with($nom, '.')) {
        continue;
    }
    $estGabarit = false;
    foreach (GABARITS as $g) {
        if (stripos($nom, $g) !== false) {
            $estGabarit = true;
            break;
        }
    }
    if ($estGabarit) {
        $ignores++;
        continue;
    }

    $texte = $ext === 'docx' ? texteDocx($chemin) : textePdf($chemin);
    if ($texte === '') {
        continue;
    }

    $relatif = ltrim(str_replace(realpath($source), '', realpath($chemin)), '/');
    $dossier = dirname($relatif);

    // Numéro : contenu, puis nom de fichier, puis dossier parent
    $numero = '';
    $origine = '';
    foreach ([[$texte, 'contenu'], [$nom, 'nom_fichier'], [$dossier, 'dossier']] as [$source_num, $etiquette]) {
        if (preg_match(MOTIF_NUMERO, $source_num, $m)) {
            $numero = $m[0];
            $origine = $etiquette;
            break;
        }
    }

    $qr = glob(dirname($chemin) . '/*.png') ?: [];

    foreach (segmenter($texte) as $i => $segment) {
        $c = champs($segment, $texte);
        $multiple = count(segmenter($texte)) > 1;

        $lignes[] = [
            'numero' => $multiple && $i > 0 ? '' : $numero,
            'origine_numero' => $multiple && $i > 0 ? 'A_COMPLETER' : ($origine ?: 'ABSENT'),
            'nature' => nature($segment, $texte),
            'type_organisation' => typeOrganisation($chemin, $c['denomination']),
            'denomination' => $c['denomination'],
            'sigle' => $c['sigle'],
            'objet' => mb_substr($c['objet'], 0, 300),
            'siege_social' => $c['siege_social'],
            'telephone' => $c['telephone'],
            'declarant' => $c['declarant'],
            'bureau' => mb_substr($c['bureau'], 0, 300),
            'date_document' => $c['date_document'],
            'qr_present' => $qr ? basename($qr[0]) : '',
            'format' => strtoupper($ext),
            'fichier_source' => $relatif,
            'rang_dans_fichier' => $multiple ? ($i + 1) . '/' . count(segmenter($texte)) : '1/1',
        ];
    }
}

// Tri : numéro renseigné d'abord, puis dénomination
usort($lignes, fn($a, $b) => [$a['numero'] === '', $a['numero'], $a['denomination']]
                        <=> [$b['numero'] === '', $b['numero'], $b['denomination']]);

$fh = fopen($sortie, 'w');
fwrite($fh, "\xEF\xBB\xBF"); // BOM : Excel ouvre l'UTF-8 correctement
fputcsv($fh, array_keys($lignes[0] ?? ['vide' => '']), ';');
foreach ($lignes as $l) {
    fputcsv($fh, $l, ';');
}
fclose($fh);

// ── Rapport ──────────────────────────────────────────────────────────────
$avecNumero = array_filter($lignes, fn($l) => $l['numero'] !== '');
$deja = array_filter($lignes, fn($l) => str_starts_with($l['numero'], 'DOS-'));
$sansDenom = array_filter($lignes, fn($l) => $l['denomination'] === '');

printf("Documents ignorés (gabarits vierges) : %d\n", $ignores);
printf("Récépissés extraits                  : %d\n", count($lignes));
printf("  dont numéro identifié              : %d\n", count($avecNumero));
printf("  dont déjà générés par le système   : %d (série DOS-)\n", count($deja));
printf("  dont numéro à compléter à la main  : %d\n", count($lignes) - count($avecNumero));
printf("  dont dénomination non extraite     : %d  <- à vérifier en priorité\n", count($sansDenom));
printf("\nTableur de contrôle : %s\n", realpath($sortie) ?: $sortie);
