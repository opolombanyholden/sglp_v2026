<?php

namespace App\Helpers;

class HtmlSanitizer
{
    /**
     * Tags HTML autorisés pour le contenu riche (actualités, guides, FAQ).
     */
    private static array $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u',
        'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'a', 'img', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'blockquote', 'span', 'div', 'hr', 'pre', 'code',
    ];

    /**
     * Attributs autorisés par tag.
     */
    private static array $allowedAttributes = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
        'span' => ['class'],
        'div' => ['class'],
        'p' => ['class'],
        'table' => ['class'],
    ];

    /**
     * Protocoles autorisés pour les URLs (href, src).
     */
    private static array $allowedProtocols = ['http', 'https', 'mailto'];

    /**
     * Sanitise du HTML riche en supprimant tout code dangereux.
     * Utilise DOMDocument pour un parsing robuste (pas de regex fragile).
     */
    public static function clean(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();

        $wrapper = $dom->getElementsByTagName('div')->item(0);
        if (!$wrapper) {
            return e($html);
        }

        self::sanitizeNode($wrapper);

        $result = '';
        foreach ($wrapper->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    /**
     * Sanitise du contenu SVG en supprimant les éléments dangereux.
     * Supprime : <script>, <foreignObject>, attributs événementiels (on*).
     */
    public static function cleanSvg(?string $svg): string
    {
        if (empty($svg)) {
            return '';
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div>' . $svg . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();

        $wrapper = $dom->getElementsByTagName('div')->item(0);
        if (!$wrapper) {
            return '';
        }

        self::sanitizeSvgNode($wrapper);

        $result = '';
        foreach ($wrapper->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    /**
     * Parcourt récursivement un nœud SVG pour supprimer les éléments dangereux.
     */
    private static function sanitizeSvgNode(\DOMNode $node): void
    {
        $toRemove = [];

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($child->nodeName);

                // Supprimer les tags dangereux
                if (in_array($tagName, ['script', 'foreignobject'])) {
                    $toRemove[] = $child;
                    continue;
                }

                // Supprimer les attributs événementiels (on*)
                $attrsToRemove = [];
                if ($child->attributes) {
                    foreach ($child->attributes as $attr) {
                        if (str_starts_with(strtolower($attr->name), 'on')) {
                            $attrsToRemove[] = $attr->name;
                        }
                    }
                }

                foreach ($attrsToRemove as $attrName) {
                    $child->removeAttribute($attrName);
                }

                // Récurser dans les enfants
                self::sanitizeSvgNode($child);
            }
        }

        foreach ($toRemove as $removable) {
            $removable->parentNode->removeChild($removable);
        }
    }

    private static function sanitizeNode(\DOMNode $node): void
    {
        $toRemove = [];

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($child->nodeName);

                if (!in_array($tagName, self::$allowedTags)) {
                    $toRemove[] = $child;
                    continue;
                }

                // Supprimer les attributs non autorisés
                $allowedAttrs = self::$allowedAttributes[$tagName] ?? [];
                $attrsToRemove = [];

                foreach ($child->attributes as $attr) {
                    $attrName = strtolower($attr->name);

                    // Bloquer tout attribut événementiel (on*)
                    if (str_starts_with($attrName, 'on')) {
                        $attrsToRemove[] = $attr->name;
                        continue;
                    }

                    // Bloquer style (vecteur XSS via expression/url)
                    if ($attrName === 'style') {
                        $attrsToRemove[] = $attr->name;
                        continue;
                    }

                    if (!in_array($attrName, $allowedAttrs)) {
                        $attrsToRemove[] = $attr->name;
                        continue;
                    }

                    // Valider les URLs (href, src)
                    if (in_array($attrName, ['href', 'src'])) {
                        $url = trim($attr->value);
                        $parsed = parse_url($url);
                        $scheme = strtolower($parsed['scheme'] ?? '');

                        if ($scheme && !in_array($scheme, self::$allowedProtocols)) {
                            $attrsToRemove[] = $attr->name;
                            continue;
                        }

                        // Bloquer javascript:, data:, vbscript:
                        if (preg_match('/^\s*(javascript|data|vbscript)\s*:/i', $url)) {
                            $attrsToRemove[] = $attr->name;
                        }
                    }
                }

                foreach ($attrsToRemove as $attrName) {
                    $child->removeAttribute($attrName);
                }

                // Forcer rel="noopener noreferrer" sur les liens externes
                if ($tagName === 'a' && $child->hasAttribute('href')) {
                    $child->setAttribute('rel', 'noopener noreferrer');
                }

                // Récurser dans les enfants
                self::sanitizeNode($child);
            }
        }

        foreach ($toRemove as $node) {
            $node->parentNode->removeChild($node);
        }
    }
}
