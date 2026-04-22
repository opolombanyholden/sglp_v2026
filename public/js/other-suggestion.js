/**
 * Gestion de l'option "Autre" dans les selects de fonctions et domaines
 * Ajoute automatiquement une option "Autre..." et gère la saisie personnalisée.
 *
 * Usage: sur les selects qui doivent supporter cette option, ajouter l'attribut :
 *   data-allow-other="fonction" OU data-allow-other="domaine"
 *
 * Le code crée un champ texte sous le select quand "Autre" est sélectionné,
 * et envoie la valeur saisie (au blur ou au clic sur le bouton) à l'endpoint
 * /suggestions/fonction ou /suggestions/domaine. Une fois la suggestion
 * enregistrée, une nouvelle option est ajoutée au select avec son ID réel.
 */
(function () {
    'use strict';

    const OTHER_VALUE = '__OTHER__';

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function addOtherOption(select) {
        if (select.querySelector('option[value="' + OTHER_VALUE + '"]')) return;
        const opt = document.createElement('option');
        opt.value = OTHER_VALUE;
        opt.textContent = '✏️ Autre (à saisir)...';
        opt.setAttribute('data-other-option', '1');
        select.appendChild(opt);
    }

    function buildCustomInputGroup(select) {
        const existing = select.parentElement.querySelector('.other-suggestion-group');
        if (existing) return existing;

        const group = document.createElement('div');
        group.className = 'other-suggestion-group mt-2';
        group.style.display = 'none';
        group.innerHTML = `
            <div class="input-group input-group-sm">
                <input type="text" class="form-control other-suggestion-input" placeholder="Saisir la nouvelle valeur" maxlength="255">
                <button type="button" class="btn btn-primary other-suggestion-submit">
                    <i class="fas fa-paper-plane"></i> Proposer
                </button>
            </div>
            <small class="form-text text-muted other-suggestion-help">
                <i class="fas fa-info-circle"></i> La valeur saisie sera enregistrée mais devra être approuvée par un administrateur avant d'être visible pour les autres usagers.
            </small>
            <div class="other-suggestion-result mt-1 small"></div>
        `;
        select.parentElement.appendChild(group);
        return group;
    }

    function handleSelectChange(select) {
        const type = select.getAttribute('data-allow-other');
        const group = buildCustomInputGroup(select);
        const input = group.querySelector('.other-suggestion-input');
        const submit = group.querySelector('.other-suggestion-submit');
        const result = group.querySelector('.other-suggestion-result');

        if (select.value === OTHER_VALUE) {
            group.style.display = 'block';
            input.focus();
        } else {
            group.style.display = 'none';
            result.innerHTML = '';
        }

        // Handler submit (une seule fois)
        if (!submit.dataset.bound) {
            submit.dataset.bound = '1';
            submit.addEventListener('click', function () {
                submitSuggestion(select, type, input, result);
            });
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitSuggestion(select, type, input, result);
                }
            });
        }
    }

    function submitSuggestion(select, type, input, result) {
        const nom = (input.value || '').trim();
        if (nom.length < 2) {
            result.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Saisir au moins 2 caractères.</span>';
            return;
        }

        const endpoint = type === 'fonction' ? '/suggestions/fonction' : '/suggestions/domaine';

        result.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Envoi...</span>';

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ nom: nom })
        })
        .then(function (r) { return r.json().then(function (body) { return { status: r.status, body: body }; }); })
        .then(function (res) {
            if (!res.body.success) {
                result.innerHTML = '<span class="text-danger"><i class="fas fa-times"></i> ' + (res.body.message || 'Erreur') + '</span>';
                return;
            }

            // Ajouter l'option au select et la sélectionner
            const newOpt = document.createElement('option');
            newOpt.value = res.body.id;
            newOpt.textContent = res.body.nom + (res.body.already_exists ? '' : ' (en attente de validation)');
            // Insérer avant l'option "Autre"
            const otherOpt = select.querySelector('option[value="' + OTHER_VALUE + '"]');
            select.insertBefore(newOpt, otherOpt);
            select.value = res.body.id;

            result.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> ' + res.body.message + '</span>';

            // Ne pas cacher immédiatement pour laisser le temps de lire le message
            setTimeout(function () {
                const group = select.parentElement.querySelector('.other-suggestion-group');
                if (group && select.value !== OTHER_VALUE) group.style.display = 'none';
            }, 3000);
        })
        .catch(function (e) {
            result.innerHTML = '<span class="text-danger"><i class="fas fa-times"></i> Erreur réseau</span>';
        });
    }

    function init() {
        document.querySelectorAll('select[data-allow-other]').forEach(function (select) {
            addOtherOption(select);
            select.addEventListener('change', function () { handleSelectChange(this); });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-initialiser si de nouveaux selects sont ajoutés dynamiquement
    window.initOtherSuggestion = init;
})();
