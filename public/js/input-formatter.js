/**
 * input-formatter.js
 * Formatage automatique des champs à la saisie (opérateur + admin).
 *
 * Applique en direct, pendant la frappe :
 *  - NIP        : format XX-0000-AAAAMMJJ (tirets auto, 2 alphanum + 12 chiffres)
 *                 OU numérique pur si le champ n'utilise pas le format à tirets
 *                 (piloté par l'attribut `pattern` / `maxlength` du champ).
 *  - Téléphone  : chiffres uniquement, limité par maxlength (défaut 9).
 *  - Nom/Sigle  : MAJUSCULES automatiques.
 *  - Email      : minuscules + suppression des espaces.
 *
 * Détection automatique des champs (aucune modification des vues nécessaire),
 * y compris pour les champs ajoutés dynamiquement (MutationObserver).
 */
(function () {
    'use strict';

    // Types d'input à ignorer totalement
    var IGNORED_TYPES = ['hidden', 'checkbox', 'radio', 'file', 'number', 'date',
        'datetime-local', 'time', 'month', 'week', 'password', 'range', 'color', 'submit', 'button'];

    // ------------------------------------------------------------------
    // Outils caret : préservation de la position du curseur après reformatage
    // ------------------------------------------------------------------
    function countKept(str, isKept) {
        var n = 0;
        for (var i = 0; i < str.length; i++) {
            if (isKept(str[i])) n++;
        }
        return n;
    }

    function caretAfterKept(formatted, keptBefore, isKept) {
        if (keptBefore <= 0) return 0;
        var count = 0;
        for (var i = 0; i < formatted.length; i++) {
            if (isKept(formatted[i])) {
                count++;
                if (count === keptBefore) return i + 1;
            }
        }
        return formatted.length;
    }

    // ------------------------------------------------------------------
    // Formateurs
    // ------------------------------------------------------------------
    var isAlnum = function (c) { return /[A-Za-z0-9]/.test(c); };
    var isDigit = function (c) { return /[0-9]/.test(c); };
    var isNotSpace = function (c) { return !/\s/.test(c); };

    function formatNipDashed(raw) {
        var s = raw.toUpperCase().replace(/[^A-Z0-9]/g, '');
        var p1 = s.slice(0, 2);                          // 2 alphanumériques
        var digits = s.slice(2).replace(/[^0-9]/g, '');  // le reste = chiffres
        var p2 = digits.slice(0, 4);
        var p3 = digits.slice(4, 12);
        var out = p1;
        if (s.length > 2) out += '-' + p2;
        if (digits.length > 4) out += '-' + p3;
        return out;
    }

    function formatNipNumeric(raw, maxLen) {
        var d = raw.replace(/[^0-9]/g, '');
        return maxLen > 0 ? d.slice(0, maxLen) : d;
    }

    function usesDashedNip(input) {
        var p = input.getAttribute('pattern') || '';
        if (p.indexOf('-') !== -1) return true;          // ex: [A-Z0-9]{2}-[0-9]{4}-[0-9]{8}
        if (input.maxLength === 16) return true;
        // Pattern purement numérique => numérique
        if (/^[\\\[]*\[?0-9|\\d/.test(p)) return false;
        // Par défaut, format gabonais à tirets
        return true;
    }

    // Chaque entrée : {selector, build(input, raw) => string, isKept | null}
    var FORMATTERS = [
        {
            name: 'nip',
            match: function (input) {
                var key = (input.name || '') + ' ' + (input.id || '');
                return input.matches('[data-validate="nip"]') ||
                    /(^|[_\-\[ ])nip([_\-\]\d ]|$)/i.test(key);
            },
            build: function (input, raw) {
                return usesDashedNip(input)
                    ? formatNipDashed(raw)
                    : formatNipNumeric(raw, input.maxLength);
            },
            isKept: function (input) { return usesDashedNip(input) ? isAlnum : isDigit; }
        },
        {
            name: 'phone',
            match: function (input) {
                var key = (input.name || '') + ' ' + (input.id || '');
                return /telephone|phone|mobile|gsm|portable/i.test(key) ||
                    input.id === 'membre_contact' || input.name === 'membre_contact';
            },
            build: function (input, raw) {
                var max = input.maxLength && input.maxLength > 0 ? input.maxLength : 9;
                return raw.replace(/[^0-9]/g, '').slice(0, max);
            },
            isKept: function () { return isDigit; }
        },
        {
            name: 'email',
            match: function (input) {
                return input.type === 'email' ||
                    /email|courriel/i.test(input.name || '') ||
                    /email|courriel/i.test(input.id || '');
            },
            build: function (input, raw) { return raw.toLowerCase().replace(/\s+/g, ''); },
            isKept: function () { return isNotSpace; }
        },
        {
            name: 'upper',
            match: function (input) {
                var key = (input.name || '') + ' ' + (input.id || '');
                if (/prenom/i.test(key)) return false;            // ne pas toucher au prénom
                if (/nom_(fichier|original)/i.test(key)) return false;
                return /(^|[_\-\[])nom([_\-\]]|$)/i.test(key) || /sigle/i.test(key);
            },
            build: function (input, raw) { return raw.toUpperCase(); },
            isKept: null   // longueur inchangée => caret direct
        }
    ];

    function resolveFormatter(input) {
        for (var i = 0; i < FORMATTERS.length; i++) {
            if (FORMATTERS[i].match(input)) return FORMATTERS[i];
        }
        return null;
    }

    function applyFormatter(input, fmt) {
        var raw = input.value;
        var caret = input.selectionStart;
        var formatted = fmt.build(input, raw);
        if (formatted === raw) return;

        var keptPredicate = typeof fmt.isKept === 'function' ? fmt.isKept(input) : null;
        var keptBefore = keptPredicate ? countKept(raw.slice(0, caret), keptPredicate) : caret;

        input.value = formatted;

        // Repositionner le curseur (sans casser une sélection à la souris hors frappe)
        if (input === document.activeElement) {
            var pos = keptPredicate
                ? caretAfterKept(formatted, keptBefore, keptPredicate)
                : Math.min(caret, formatted.length);
            try { input.setSelectionRange(pos, pos); } catch (e) { /* champs sans sélection */ }
        }
    }

    function bind(input) {
        if (!input || input.dataset.fmtBound === '1') return;
        if (input.tagName !== 'INPUT' && input.tagName !== 'TEXTAREA') return;
        if (IGNORED_TYPES.indexOf((input.type || '').toLowerCase()) !== -1) return;

        var fmt = resolveFormatter(input);
        if (!fmt) return;

        input.dataset.fmtBound = '1';
        input.addEventListener('input', function () { applyFormatter(input, fmt); });
        // Normaliser une valeur pré-remplie (reprise de brouillon, édition admin…)
        if (input.value) applyFormatter(input, fmt);
    }

    function scan(root) {
        var nodes = (root || document).querySelectorAll('input, textarea');
        for (var i = 0; i < nodes.length; i++) bind(nodes[i]);
    }

    function init() {
        scan(document);

        // Champs ajoutés dynamiquement (fondateurs, adhérents, documents, modals…)
        if (window.MutationObserver) {
            var observer = new MutationObserver(function (mutations) {
                for (var m = 0; m < mutations.length; m++) {
                    var added = mutations[m].addedNodes;
                    for (var n = 0; n < added.length; n++) {
                        var node = added[n];
                        if (node.nodeType !== 1) continue;
                        if (node.matches && node.matches('input, textarea')) bind(node);
                        if (node.querySelectorAll) scan(node);
                    }
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
