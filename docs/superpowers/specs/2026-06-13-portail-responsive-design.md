# Design — Responsive du portail (mobile & tablette)

**Date :** 2026-06-13
**Périmètre :** Portail public DGELP + formulaires de l'espace opérateur
**Direction retenue :** A — Remise au propre responsive (on conserve l'identité visuelle bleu `#002B7F` / or `#FFD700` et le contenu ; on répare l'agencement mobile/tablette).
**Approche :** #3 Hybride — couche CSS responsive dédiée + corrections Blade ciblées là où la structure doit changer.
**Cibles :** mobile (≤575px) et tablette (576–991px) traitées à égalité. Desktop (≥992px) inchangé.

---

## 1. Constat clé (cause racine de l'affichage cassé)

Le layout public (`resources/views/layouts/public.blade.php`) charge **Bootstrap 4.6.2 + jQuery 3.6**, mais les **11 vues publiques sont écrites en Bootstrap 5** : 152 occurrences de classes/attributs qui n'existent pas en BS4 —
- marges/paddings `ms-* / me-* / ps-* / pe-*` (BS4 utilise `ml-/mr-/pl-/pr-`) → **ignorés** ;
- alignement `text-{sm,md,lg,xl}-end` → **inexistant** ;
- composants interactifs `data-bs-toggle` / `data-bs-target` (BS4 écoute `data-toggle` / `data-target`) → **modales/dropdowns qui ne s'ouvrent pas**.

C'est la première cause de mise en page cassée, indépendamment du responsive.

Les layouts **opérateur** et **admin** sont des fichiers **séparés** et utilisent Bootstrap 4 + jQuery (`$('#x').modal('show')`, `data-toggle`). Ils ne sont pas impactés par un changement du layout public.

---

## 2. Phase 1 — Portail public

### 2.1 Réconcilier Bootstrap (préalable)

- Faire passer **uniquement le layout public** en **Bootstrap 5.3** (CSS + JS bundle).
- **Conserver jQuery 3.6** (chargé avant/après BS5) pour les scripts de vue qui en dépendent ; BS5 n'a pas besoin de jQuery pour ses propres composants.
- Mettre à jour la **navbar** du layout : `data-toggle="collapse"` → `data-bs-toggle="collapse"`, `data-target` → `data-bs-target`.
- Vérifier les SRI : remplacer les hash d'intégrité BS4 par ceux de BS5.3 (ou retirer l'attribut `integrity` si le hash exact n'est pas disponible, en gardant `crossorigin`).

**Effet :** les 152 usages BS5 déjà présents dans les vues fonctionnent immédiatement (marges, alignements, modales).

### 2.2 Couche responsive globale

Nouveau fichier **`public/css/portail-responsive.css`**, chargé **en dernier** dans `<head>` du layout public (après l'inline `<style>` et `@stack('styles')`) pour primer.

Contenu :
- **Breakpoints** (BS5) : `≤575.98px` mobile, `576–991.98px` tablette, `≥992px` desktop.
- **Typographie fluide** via `clamp()` pour `.hero h1`, `.hero-subtitle`, titres de section — supprime les `font-size` fixes qui débordent.
- **Conteneurs** : padding latéral ≥16px sur mobile ; largeurs lisibles sur tablette.
- **Hero** : hauteur et typo adaptées ; CTA pleine largeur sur mobile.
- **Médias fluides** : `img, iframe, video, svg { max-width:100%; height:auto }`.
- **Cibles tactiles** : `min-height:44px` pour boutons, liens de navbar, items de pagination, contrôles de formulaire.
- **Anti-débordement horizontal** : corriger les éléments fautifs ; retirer le `overflow-x:hidden` du `body` une fois les débordements réglés (il masque les bugs aujourd'hui).
- **Navbar fixe** : offset de contenu correct sous la navbar `fixed-top` à tous les breakpoints.
- **Tableaux** : repli `.table-responsive` (scroll horizontal) pour tout tableau large résiduel.

### 2.3 Corrections Blade ciblées (uniquement structurelles)

Audit du layout + des 11 vues : `welcome` (accueil), `actualites/{index,show}`, `documents/index`, `annuaire/{index,show,verify-error}`, `guides`, `faq`, `about`, `calendrier`, `contact`, `inscription/{form,confirmation,invalid}`, `document-verification/{index,verify,help}`.

Corrections appliquées **seulement** où la structure le nécessite :
- Colonnes qui doivent s'empiler : compléter les `col-*` (la plupart sont déjà `col-lg-*` → ajouter `col-12`/`col-md-*` au besoin).
- Lignes de formulaire multi-colonnes (inscription, contact) qui s'empilent proprement sur mobile.
- Grilles de cartes (actualités, documents, annuaire) : 1 colonne mobile / 2 tablette / 3 desktop.
- Nettoyer les blocs `@media` **inline** des vues qui entrent en conflit avec la couche globale.
- Vérifier le repliement (collapse) de la navbar mobile sous BS5.

L'annuaire est **déjà en cartes** (`org-card`, `col-lg-4 col-md-6`) : pas de transformation tableau→cartes nécessaire ; seulement ajustements fins.

---

## 3. Phase 2 — Formulaires opérateur

Restent en **Bootstrap 4 + jQuery** (cohérence avec l'espace opérateur — aucun changement de version ici).

Nouveau fichier **`public/css/operator-responsive.css`**, chargé dans `resources/views/layouts/operator.blade.php`.

Cibles :
- **Wizard de création** (`operator/dossiers/create.blade.php`) :
  - Indicateur d'étapes : scrollable horizontalement ou version compacte sur mobile.
  - Lignes de formulaire multi-colonnes → empilées.
  - **Listes dynamiques** (fondateurs, membres du bureau, adhérents) : `.table-responsive` (scroll) ou présentation en cartes empilées sur mobile.
  - Cartes de documents : empilées, zone d'upload pleine largeur.
  - **Boutons de navigation** (Précédent/Suivant/Soumettre) collés en bas (sticky) et pleine largeur sur mobile.
- **Page import adhérents** (`operator/dossiers/adherents-import.blade.php`) : zone d'upload, stats et tableaux adaptés mobile/tablette.

Les corrections Blade y sont également ciblées (pas de réécriture des scripts JS dynamiques).

---

## 4. Vérification

- Contrôles manuels aux largeurs **375px** (mobile), **768px** (tablette portrait), **1024px** (tablette paysage) :
  - aucun défilement horizontal involontaire ;
  - navbar/menus déroulants opérationnels ;
  - modales (annuaire, vérification) qui s'ouvrent ;
  - formulaires (inscription, contact, création) utilisables au doigt.
- **Captures Playwright** avant/après sur 2-3 pages clés (accueil, annuaire, création opérateur) comme preuve visuelle.
- Vérifier qu'aucune régression desktop (≥992px) n'est introduite.

---

## 5. Contraintes & principes

- **Réversibilité** : la majorité des corrections passe par 2 fichiers CSS additifs ; les éditions Blade restent ciblées et minimales.
- **Pas de refonte visuelle** : même charte, mêmes contenus (direction A).
- **Isolation** : le layout public (BS5) et les layouts opérateur/admin (BS4) évoluent indépendamment.
- **Phasage** : Phase 1 (public) livrée et vérifiée avant d'entamer la Phase 2 (opérateur).

## 6. Hors périmètre (YAGNI)

- Aucune modification des espaces **admin**.
- Aucune refonte graphique ni changement de palette.
- Aucune réécriture de la logique JS métier (wizard, chunking…), seulement l'agencement.
- Pas de migration Bootstrap des espaces opérateur/admin.
