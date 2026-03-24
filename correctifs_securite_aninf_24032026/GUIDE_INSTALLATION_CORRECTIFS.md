# Guide d'Installation des Correctifs de Sécurité SGLP

**Référence :** ANINF/DG/DISI/SSRI — Rapport de Vulnérabilités SGLP v02 du 18/03/2026
**Référence :** ANINF/DG/DISI/SSRI — Fiche de Remédiation SGLP v01 du 19/03/2026
**Date du correctif :** 24/03/2026
**Version :** SGLP v2.1.1

---

## 1. Résumé des vulnérabilités corrigées

| ID | Vulnérabilité | CVSS | Catégorie OWASP | Recommandation | Statut |
|----|--------------|------|-----------------|----------------|--------|
| V01 | Exposition de /phpmyadmin | 9.5 CRITIQUE | A05 : Mauvaise configuration de sécurité | R01 | ✅ Corrigé |
| V02 | Redirection HTTPS → HTTP (/admin → /admin/login) | 8.7 ÉLEVÉE | A02 : Défaillance cryptographique | R02 | ✅ Corrigé |
| V03 | Cookies sans Secure / HttpOnly / SameSite | 8.2 ÉLEVÉE | A05 : Mauvaise configuration de sécurité | R03 | ✅ Corrigé |
| V04 | En-têtes X-Frame-Options et X-Content-Type-Options insuffisants | 6.5 MOYENNE | A05 : Mauvaise configuration de sécurité | R04 | ✅ Corrigé |
| — | Sub Resource Integrity manquant sur CDN (Nuclei/ZAP) | — | A05 : Mauvaise configuration de sécurité | — | ✅ Corrigé |

---

## 2. Fichiers inclus dans le correctif

### 2.1 Nouveaux fichiers (à créer)

| Fichier | Rôle | Réf. |
|---------|------|------|
| `app/Http/Middleware/BlockSensitivePaths.php` | Bloque /phpmyadmin, /.env, /.git, etc. → retourne 404 | V01/R01 |
| `app/Http/Middleware/ForceHttps.php` | Redirige 301 HTTP → HTTPS en production | V02/R02 |

### 2.2 Fichiers modifiés (à remplacer)

| Fichier | Modifications | Réf. |
|---------|--------------|------|
| `app/Http/Kernel.php` | Ajout ForceHttps + BlockSensitivePaths dans la pile globale | V01, V02 |
| `app/Http/Middleware/SecurityHeaders.php` | X-Frame-Options: DENY, HSTS 2 ans + preload, CSP frame-ancestors 'none' | V02, V04 |
| `app/Http/Middleware/VerifyCsrfToken.php` | Cookie XSRF-TOKEN avec Secure + SameSite=Strict en production | V03 |
| `app/Providers/AppServiceProvider.php` | Force HTTPS sur toutes les URLs générées par Laravel | V02 |
| `config/session.php` | Cookie session : Secure=true en prod, SameSite=Strict | V03 |

### 2.3 Vues mises à jour (SRI — Sub Resource Integrity)

Ajout des attributs `integrity` et `crossorigin="anonymous"` sur **toutes** les ressources CDN (scripts et CSS).

| Fichier | Ressources SRI ajoutées |
|---------|------------------------|
| `resources/views/layouts/public.blade.php` | Bootstrap 4.6.2 CSS/JS, jQuery 3.6.0, Font Awesome 6.4.0 |
| `resources/views/layouts/admin.blade.php` | Bootstrap 4.6.2 CSS/JS, jQuery 3.6.0, Font Awesome 5.15.4, CKEditor 4.22.1 |
| `resources/views/layouts/operator.blade.php` | Bootstrap 4.6.2 CSS/JS, jQuery 3.6.0, Font Awesome 6.4.0 |
| `resources/views/public/document-verification/index.blade.php` | Bootstrap 4.6.2 CSS/JS, jQuery 3.6.0, Font Awesome 6.0.0 |
| `resources/views/public/document-verification/help.blade.php` | Bootstrap 4.6.2 CSS/JS, jQuery 3.6.0, Font Awesome 6.0.0 |
| `resources/views/public/document-verification/verify.blade.php` | Bootstrap 4.6.2 CSS/JS, jQuery 3.6.0, Font Awesome 6.0.0 |
| `resources/views/api/v1/documentation.blade.php` | Bootstrap 4.6.2 CSS, Font Awesome 6.4.0 |
| `resources/views/admin/document-templates/preview.blade.php` | Bootstrap 5.1.3 CSS/JS, Font Awesome 6.0.0 |
| `resources/views/admin/geolocalisation/regroupements/create.blade.php` | Bootstrap 5.3.0 JS |
| `resources/views/admin/geolocalisation/cantons/create.blade.php` | Bootstrap 5.3.0 JS |
| `resources/views/admin/workflow-steps/timeline.blade.php` | SortableJS 1.15.0 |
| `resources/views/operator/dossiers/adherents-form.blade.php` | Bootstrap 5.3.0 JS |
| `resources/views/operator/dossiers/adherents-import.blade.php` | Axios, XLSX 0.18.5, PapaParse 5.3.2 |
| `resources/views/operator/dossiers/create.blade.php` | Axios |
| `resources/views/operator/dashboard.blade.php` | Chart.js |
| `resources/views/debug/qr-code-debug.blade.php` | Bootstrap 5.1.3 CSS |

---

## 3. Prérequis

- Accès SSH au serveur de production
- Droits d'écriture sur le répertoire de l'application
- **Sauvegarde** de la base de données et du code source effectuée
- Certificat SSL/TLS valide installé et fonctionnel sur le serveur web

---

## 4. Procédure d'installation

### 4.1 Sauvegarde préalable (OBLIGATOIRE)

```bash
ssh utilisateur@serveur
cd /chemin/vers/sglp
tar czf /tmp/sglp_backup_$(date +%Y%m%d_%H%M%S).tar.gz .
```

### 4.2 Décompresser et copier les fichiers

```bash
# Décompresser l'archive du correctif
unzip correctifs_securite_aninf.zip -d /tmp/correctifs

# Copier tous les fichiers (arborescence préservée)
cp -r /tmp/correctifs/* /chemin/vers/sglp/
```

### 4.3 Configurer le fichier .env de production

Vérifier et ajuster les variables suivantes dans le fichier `.env` :

```dotenv
# OBLIGATOIRE : environnement production
APP_ENV=production
APP_DEBUG=false

# OBLIGATOIRE : URL en HTTPS
APP_URL=https://demo-sglp.interieur.gouv.ga

# OBLIGATOIRE : cookies sécurisés
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

> **ATTENTION** : `APP_URL` doit commencer par `https://`. Si cette variable est en `http://`, les middlewares ForceHttps et les cookies Secure ne s'activeront pas.

### 4.4 Vider les caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Reconstruire les caches
php artisan config:cache
php artisan route:cache
```

### 4.5 Vérifier les permissions des fichiers

```bash
chown -R www-data:www-data app/Http/Middleware/BlockSensitivePaths.php \
    app/Http/Middleware/ForceHttps.php
chmod 644 app/Http/Middleware/BlockSensitivePaths.php \
    app/Http/Middleware/ForceHttps.php
```

---

## 5. Tests de validation post-installation

### 5.1 R01/V01 — Vérification du blocage /phpmyadmin

```bash
curl -s -o /dev/null -w "%{http_code}" https://demo-sglp.interieur.gouv.ga/phpmyadmin
# Attendu : 404

curl -s -o /dev/null -w "%{http_code}" https://demo-sglp.interieur.gouv.ga/phpMyAdmin
# Attendu : 404

curl -s -o /dev/null -w "%{http_code}" https://demo-sglp.interieur.gouv.ga/pma
# Attendu : 404

curl -s -o /dev/null -w "%{http_code}" https://demo-sglp.interieur.gouv.ga/adminer
# Attendu : 404
```

### 5.2 R02/V02 — Vérification de la redirection HTTPS + HSTS

```bash
# HTTP → HTTPS (redirect 301)
curl -s -o /dev/null -w "%{http_code}" http://demo-sglp.interieur.gouv.ga/admin
# Attendu : 301

curl -sI http://demo-sglp.interieur.gouv.ga/admin | grep -i location
# Attendu : Location: https://demo-sglp.interieur.gouv.ga/admin

# /admin redirige vers /admin/login en HTTPS (plus en HTTP)
curl -sI https://demo-sglp.interieur.gouv.ga/admin | grep -i location
# Attendu : Location: https://demo-sglp.interieur.gouv.ga/admin/login

# Vérifier HSTS
curl -sI https://demo-sglp.interieur.gouv.ga/ | grep -i strict-transport
# Attendu : Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
```

### 5.3 R03/V03 — Vérification des cookies

```bash
curl -sI https://demo-sglp.interieur.gouv.ga/login | grep -i set-cookie
```

**Attendu pour chaque cookie :**
- `Secure` : présent
- `HttpOnly` : présent sur le cookie session, absent sur XSRF-TOKEN (par design, JS doit pouvoir le lire)
- `SameSite=Strict` : présent sur les deux cookies

### 5.4 R04/V04 — Vérification des en-têtes HTTP

```bash
curl -sI https://demo-sglp.interieur.gouv.ga/ | grep -iE "x-frame|x-content|strict-transport"
# Attendu :
#   X-Frame-Options: DENY
#   X-Content-Type-Options: nosniff
#   Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
```

### 5.5 SRI — Vérification de l'intégrité des sous-ressources

```bash
# Vérifier qu'aucune ressource CDN ne manque d'attribut integrity
curl -s https://demo-sglp.interieur.gouv.ga/ | grep -oP '<(script|link)[^>]+(cdn\.jsdelivr|cdnjs\.cloudflare|code\.jquery|cdn\.ckeditor)[^>]+>' | grep -v integrity
# Attendu : aucune sortie (toutes les ressources CDN ont integrity)
```

### 5.6 Test fonctionnel global

| Test | URL | Résultat attendu |
|------|-----|-----------------|
| Page d'accueil | https://demo-sglp.interieur.gouv.ga/ | 200 OK |
| Login opérateur | https://demo-sglp.interieur.gouv.ga/login | 200 OK |
| Login admin | https://demo-sglp.interieur.gouv.ga/admin/login | 200 OK |
| Annuaire | https://demo-sglp.interieur.gouv.ga/annuaire | 200 OK |
| Documents | https://demo-sglp.interieur.gouv.ga/documents | 200 OK |
| API v1 | https://demo-sglp.interieur.gouv.ga/api/v1/documentation | 200 OK |
| phpmyadmin (bloqué) | https://demo-sglp.interieur.gouv.ga/phpmyadmin | 404 |

---

## 6. Procédure de rollback

```bash
cd /chemin/vers/sglp
tar xzf /tmp/sglp_backup_XXXXXXXX_XXXXXX.tar.gz
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 7. Configuration Apache complémentaire (recommandé)

Pour une protection en profondeur au niveau du serveur web :

```apache
# Bloquer phpMyAdmin au niveau Apache
<LocationMatch "^/(phpmyadmin|phpMyAdmin|pma|adminer)">
    Require all denied
</LocationMatch>

# Forcer HTTPS au niveau Apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Bloquer l'accès aux fichiers sensibles
<FilesMatch "^\.">
    Require all denied
</FilesMatch>
```

---

## 8. Détail technique des correctifs

### R01/V01 — BlockSensitivePaths.php (NOUVEAU)
- Middleware global interceptant toutes les requêtes
- Compare le chemin avec une liste noire : `phpmyadmin`, `phpMyAdmin`, `pma`, `adminer`, `server-status`, `server-info`, `.env`, `.git`, `telescope`, `horizon`, `_debugbar`, `setup.php`, `install.php`, `wp-admin`, `wp-login`
- Retourne HTTP 404 si le chemin est bloqué

### R02/V02 — ForceHttps.php (NOUVEAU) + SecurityHeaders + AppServiceProvider
- **ForceHttps** : redirige 301 toute requête HTTP vers HTTPS (en production ou si APP_URL commence par https)
- **AppServiceProvider** : `URL::forceScheme('https')` + `$request->server->set('HTTPS', 'on')` pour que toutes les URLs Laravel (route(), url(), redirect()) soient en HTTPS
- **SecurityHeaders** : HSTS renforcé à `max-age=63072000` (2 ans) avec `includeSubDomains` et `preload`

### R03/V03 — session.php + VerifyCsrfToken
- **session.php** : `secure` → `true` par défaut en production, `same_site` → `strict`
- **VerifyCsrfToken** : surcharge de `newCookie()` pour forcer `Secure=true` et `SameSite=Strict` sur le cookie XSRF-TOKEN en production

### R04/V04 — SecurityHeaders
- `X-Frame-Options` : `SAMEORIGIN` → `DENY`
- `X-Content-Type-Options` : `nosniff` (déjà présent, confirmé)
- CSP `frame-ancestors` : `'self'` → `'none'`

### SRI — Vues Blade (16 fichiers)
- Ajout de `integrity="sha384-..."` et `crossorigin="anonymous"` sur toutes les ressources CDN (Bootstrap, jQuery, Font Awesome, CKEditor, Chart.js, Axios, XLSX, PapaParse, SortableJS)
- 13 ressources CDN uniques protégées par SRI à travers 16 fichiers Blade

---

## 9. Matrice de conformité

| Exigence ANINF | Recommandation | Implémentation | Vérifiable par |
|----------------|---------------|----------------|----------------|
| Bloquer /phpmyadmin | R01 : Ajouter authentification forte | Middleware BlockSensitivePaths → 404 | `curl /phpmyadmin` → 404 |
| Renommer/déplacer l'URL | R01 : Renommer ou déplacer | Bloqué côté applicatif ; côté serveur : voir §7 Apache | Config Apache |
| Désactiver en prod | R01 : Désactiver si inutile | Bloqué par middleware ; recommandé de retirer phpMyAdmin du serveur | Admin système |
| HTTPS global (301) | R02 : Forcer HTTPS redirect 301 | Middleware ForceHttps + AppServiceProvider | `curl -I http://...` → 301 |
| Activer HSTS | R02 : Activer HSTS | SecurityHeaders: max-age=63072000; includeSubDomains; preload | `curl -I` → header HSTS |
| Bloquer HTTP | R02 : Bloquer tout accès HTTP | ForceHttps redirige 301 ; Apache RewriteRule recommandée | Test HTTP → redirect |
| Cookie Secure | R03 : Set-Cookie: Secure | session.php + VerifyCsrfToken | `curl -I /login` → Set-Cookie |
| Cookie HttpOnly | R03 : HttpOnly | session.php http_only=true (XSRF-TOKEN: false par design) | `curl -I /login` → Set-Cookie |
| Cookie SameSite=Strict | R03 : SameSite=Strict | session.php + VerifyCsrfToken | `curl -I /login` → Set-Cookie |
| X-Frame-Options: DENY | R04 : X-Frame-Options: DENY | SecurityHeaders middleware | `curl -I /` → header |
| X-Content-Type-Options: nosniff | R04 : nosniff | SecurityHeaders middleware | `curl -I /` → header |
| Sub Resource Integrity | Nuclei/ZAP scan | integrity + crossorigin sur tous les CDN (16 fichiers) | Inspecter source HTML |

---

*Document généré le 24/03/2026 — SGLP v2.1.1*
*Correctifs conformes aux recommandations ANINF/DG/DISI/SSRI*
