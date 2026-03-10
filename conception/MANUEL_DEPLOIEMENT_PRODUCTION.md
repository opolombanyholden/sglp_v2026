# Manuel d'Installation et de Déploiement en Production
## SGLP — Système de Gestion des Libertés Publiques
**Version 2.1 — Laravel 9.x**

| Version | Date | Auteur | Modifications |
|---------|------|--------|---------------|
| 2.0 | 03/03/2026 | Équipe SGLP | Version initiale |
| 2.1 | 07/03/2026 | Équipe SGLP | Portail CMS, Annuaire dynamique, API V1, corrections sécurité |

---

## Table des matières

1. [Prérequis système](#1-prérequis-système)
2. [Architecture de déploiement](#2-architecture-de-déploiement)
3. [Préparation du serveur](#3-préparation-du-serveur)
4. [Installation de l'application](#4-installation-de-lapplication)
5. [Configuration de l'environnement (.env)](#5-configuration-de-lenvironnement-env)
6. [Configuration de la base de données](#6-configuration-de-la-base-de-données)
7. [Configuration du serveur web (Apache/Nginx)](#7-configuration-du-serveur-web-apachenginx)
8. [Configuration HTTPS/SSL](#8-configuration-httpsssl)
9. [Commandes de finalisation](#9-commandes-de-finalisation)
10. [Import de la base de données NIP](#10-import-de-la-base-de-données-nip)
11. [Portail Public CMS](#11-portail-public-cms)
12. [API Interopérabilité V1](#12-api-interopérabilité-v1)
13. [Permissions et sécurité des fichiers](#13-permissions-et-sécurité-des-fichiers)
14. [Configuration des tâches planifiées (Cron)](#14-configuration-des-tâches-planifiées-cron)
15. [Vérification post-déploiement](#15-vérification-post-déploiement)
16. [Optimisation pour la production](#16-optimisation-pour-la-production)
17. [Mises à jour et redéploiement](#17-mises-à-jour-et-redéploiement)
18. [Sauvegarde et restauration](#18-sauvegarde-et-restauration)
19. [Résolution des problèmes courants](#19-résolution-des-problèmes-courants)

---

## 1. Prérequis système

### Serveur
| Composant | Version minimale | Recommandée |
|-----------|-----------------|-------------|
| PHP | 8.1 | 8.2+ |
| MySQL | 5.7 | 8.0+ |
| Apache | 2.4 | 2.4+ |
| Nginx | 1.18 | 1.24+ |
| Node.js | 16 | 18 LTS |
| NPM | 8 | 9+ |
| Composer | 2.0 | 2.6+ |

### Extensions PHP obligatoires
```bash
# Vérifier les extensions installées
php -m | grep -E "pdo|openssl|mbstring|xml|curl|gd|zip|json|fileinfo|tokenizer|bcmath|intl"
```

Extensions requises :
- `pdo_mysql` — Connexion base de données
- `openssl` — Chiffrement et HTTPS
- `mbstring` — Gestion des chaînes multi-octets (UTF-8)
- `xml` — Traitement XML
- `curl` — Requêtes HTTP sortantes
- `gd` ou `imagick` — Génération d'images (QR codes)
- `zip` — Compression de fichiers
- `fileinfo` — Détection de types MIME
- `tokenizer` — Requis par Laravel
- `bcmath` — Calculs mathématiques
- `intl` — Internationalisation

### Configuration PHP (`php.ini`)
```ini
memory_limit = 512M
post_max_size = 55M
upload_max_filesize = 50M
max_execution_time = 300
max_input_time = 300
date.timezone = Africa/Libreville
```

### Dépendances système (pour la génération PDF)
```bash
# wkhtmltopdf (optionnel — pour Snappy PDF)
sudo apt-get install wkhtmltopdf

# Fonts pour DomPDF/MPDF
sudo apt-get install fonts-dejavu fontconfig
```

---

## 2. Architecture de déploiement

```
Internet
    │
    ▼
[Pare-feu / Load Balancer]
    │
    ▼
[Serveur Web — Apache ou Nginx]  ←── HTTPS/SSL (port 443)
    │
    ├── /public/          ← Racine web publique (DocumentRoot)
    │     └── index.php   ← Point d'entrée Laravel
    │
    └── [Application Laravel]
          ├── app/
          ├── config/
          ├── database/
          ├── resources/
          ├── routes/
          └── storage/    ← Écriture fichiers, sessions, logs
                          ← NON accessible publiquement

[Base de données MySQL]
    └── rs2715024_sglp    ← Base principale (+ nip_database)
```

---

## 3. Préparation du serveur

### 3.1 Installation des dépendances système (Ubuntu/Debian)
```bash
sudo apt-get update && sudo apt-get upgrade -y

# PHP 8.2 et extensions
sudo apt-get install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-gd php8.2-zip php8.2-intl \
    php8.2-bcmath php8.2-fileinfo php8.2-openssl

# Serveur web
sudo apt-get install -y apache2   # ou nginx

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js & NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 3.2 Création de l'utilisateur et répertoire applicatif
```bash
# Créer l'utilisateur applicatif (sans accès SSH direct)
sudo adduser --system --group --no-create-home sglp

# Créer le répertoire de déploiement
sudo mkdir -p /var/www/sglp
sudo chown sglp:www-data /var/www/sglp
sudo chmod 755 /var/www/sglp
```

---

## 4. Installation de l'application

### 4.1 Cloner ou transférer les fichiers
```bash
# Option A — Via Git
cd /var/www/sglp
git clone https://[votre-depot]/sglp_v2.git .

# Option B — Via SFTP/rsync (depuis l'environnement local)
rsync -avz --exclude='.env' --exclude='vendor/' --exclude='node_modules/' \
    --exclude='storage/logs/' \
    /Applications/MAMP/htdocs/sglp_v2/ \
    user@serveur-prod:/var/www/sglp/
```

### 4.2 Installation des dépendances PHP
```bash
cd /var/www/sglp

# Installation sans les dépendances de développement
composer install --optimize-autoloader --no-dev

# Vérifier qu'aucune erreur n'est levée
composer diagnose
```

### 4.3 Installation et compilation des assets frontend
```bash
# Installer les dépendances Node
npm ci --omit=dev

# Compiler les assets pour la production
npm run production
# ou selon la configuration :
npm run build
```

---

## 5. Configuration de l'environnement (.env)

### 5.1 Créer le fichier .env de production
```bash
cp .env.example .env
```

### 5.2 Remplir le fichier .env

Éditer `/var/www/sglp/.env` avec les valeurs suivantes :

```dotenv
# =========================================================
# APPLICATION
# =========================================================
APP_NAME="SGLP"
APP_ENV=production
APP_KEY=                          # Généré à l'étape 9.1
APP_DEBUG=false
APP_URL=https://www.sglp.ga

# =========================================================
# LOGS
# =========================================================
LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning                 # warning en production (pas debug)

# =========================================================
# BASE DE DONNÉES
# =========================================================
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=rs2715024_sglp
DB_USERNAME=rs2715024_sglp
DB_PASSWORD=[MOT_DE_PASSE_SECURISE]

# Timeouts pour gros volumes
DB_TIMEOUT=600
MYSQL_WAIT_TIMEOUT=600
MYSQL_INTERACTIVE_TIMEOUT=600
MYSQL_NET_READ_TIMEOUT=600
MYSQL_NET_WRITE_TIMEOUT=600
MYSQL_MAX_ALLOWED_PACKET=128M

# =========================================================
# CACHE & QUEUE
# =========================================================
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync

# =========================================================
# SESSION (sécurisée pour HTTPS)
# =========================================================
SESSION_DRIVER=file
SESSION_LIFETIME=120
CSRF_LIFETIME=480
SESSION_COOKIE=sglp_session
SESSION_EXPIRE_ON_CLOSE=false
SESSION_DOMAIN=.sglp.ga
SESSION_SECURE_COOKIE=true        # Obligatoire en HTTPS
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# =========================================================
# MAIL
# =========================================================
MAIL_MAILER=smtp
MAIL_HOST=mail.sglp.ga
MAIL_PORT=465
MAIL_USERNAME=inscription@sglp.ga
MAIL_PASSWORD=[MOT_DE_PASSE_MAIL]
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="inscription@sglp.ga"
MAIL_FROM_NAME="${APP_NAME}"

# =========================================================
# LIMITES UPLOAD
# =========================================================
UPLOAD_MAX_FILESIZE=50M
POST_MAX_SIZE=55M
MAX_EXECUTION_TIME=300
MEMORY_LIMIT=512M

# =========================================================
# QR CODE
# =========================================================
QR_VERIFICATION_BASE_URL=https://www.sglp.ga
```

> ⚠️ **Important :** Le fichier `.env` ne doit jamais être commité dans git, ni accessible via le navigateur. Vérifiez que `.env` figure dans `.gitignore`.

---

## 6. Configuration de la base de données

### 6.1 Créer la base et l'utilisateur MySQL
```sql
-- Se connecter en tant que root MySQL
mysql -u root -p

-- Créer la base de données
CREATE DATABASE rs2715024_sglp
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Créer l'utilisateur applicatif
CREATE USER 'rs2715024_sglp'@'localhost' IDENTIFIED BY '[MOT_DE_PASSE_SECURISE]';

-- Accorder les droits (sans SUPER ni GRANT OPTION)
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER,
      CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW,
      SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE
ON rs2715024_sglp.* TO 'rs2715024_sglp'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

### 6.2 Exécuter les migrations (106 tables)
```bash
cd /var/www/sglp

# Exécuter toutes les migrations (--force obligatoire en production)
php artisan migrate --force

# Vérifier que toutes les migrations ont été appliquées
php artisan migrate:status | grep -v "Ran"
```

Tables créées par les migrations récentes (v2.1) :

| Table | Description |
|-------|-------------|
| `portail_actualites` | Actualités du portail public |
| `portail_documents` | Documents téléchargeables |
| `portail_faqs` | Questions fréquentes |
| `portail_guides` | Guides pratiques |
| `portail_evenements` | Calendrier des événements |
| `portail_parametres` | Paramètres CMS (clé/valeur) |
| `portail_messages` | Messages reçus via formulaire contact |
| `api_tokens` | Tokens d'accès API interopérabilité |

### 6.3 Peupler les données de référence (seeders)
```bash
# Données de référence de base
php artisan db:seed --force

# Ou séparément si besoin
php artisan db:seed --class=ProvincesSeeder --force
php artisan db:seed --class=OrganisationTypesSeeder --force

# Contenu initial du Portail Public (actualités, FAQ, guides, paramètres)
php artisan db:seed --class=PortailSeeder --force
```

> **Note :** Le `PortailSeeder` peuple le contenu statique initial du portail (page d'accueil, FAQ de base, guides). Il est idempotent : inoffensif si exécuté plusieurs fois.

---

## 7. Configuration du serveur web (Apache/Nginx)

### 7.1 Apache — Virtual Host

Créer `/etc/apache2/sites-available/sglp.conf` :

```apache
<VirtualHost *:80>
    ServerName www.sglp.ga
    ServerAlias sglp.ga
    # Redirection automatique HTTP → HTTPS
    Redirect permanent / https://www.sglp.ga/
</VirtualHost>

<VirtualHost *:443>
    ServerName www.sglp.ga
    ServerAlias sglp.ga

    # Racine web : uniquement le dossier /public de Laravel
    DocumentRoot /var/www/sglp/public

    # SSL (certificat Let's Encrypt ou autre)
    SSLEngine on
    SSLCertificateFile     /etc/letsencrypt/live/sglp.ga/fullchain.pem
    SSLCertificateKeyFile  /etc/letsencrypt/live/sglp.ga/privkey.pem

    <Directory /var/www/sglp/public>
        AllowOverride All
        Require all granted
        Options -Indexes           # Désactiver le listing de répertoires
    </Directory>

    # Interdire l'accès aux fichiers sensibles
    <FilesMatch "\.(env|log|sql|sh|bak|swp)$">
        Require all denied
    </FilesMatch>

    # Logs
    ErrorLog  ${APACHE_LOG_DIR}/sglp_error.log
    CustomLog ${APACHE_LOG_DIR}/sglp_access.log combined

    # Compression GZIP
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json
    </IfModule>

    # Cache des assets statiques
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType text/css              "access plus 1 year"
        ExpiresByType application/javascript "access plus 1 year"
        ExpiresByType image/png             "access plus 1 month"
        ExpiresByType image/jpeg            "access plus 1 month"
    </IfModule>
</VirtualHost>
```

```bash
# Activer les modules Apache nécessaires
sudo a2enmod rewrite ssl headers expires deflate

# Activer le site
sudo a2ensite sglp.conf
sudo a2dissite 000-default.conf

# Recharger Apache
sudo systemctl reload apache2
```

### 7.2 Nginx — Bloc serveur (alternative)

Créer `/etc/nginx/sites-available/sglp` :

```nginx
# Redirection HTTP → HTTPS
server {
    listen 80;
    server_name sglp.ga www.sglp.ga;
    return 301 https://www.sglp.ga$request_uri;
}

server {
    listen 443 ssl http2;
    server_name www.sglp.ga sglp.ga;

    # Racine web : dossier /public uniquement
    root /var/www/sglp/public;
    index index.php;

    # SSL
    ssl_certificate     /etc/letsencrypt/live/sglp.ga/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sglp.ga/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Interdire l'accès aux fichiers sensibles
    location ~ /\.(env|git|htaccess|log|sql|sh|bak) {
        deny all;
    }

    # Désactiver le listing de répertoires
    autoindex off;

    # Front controller Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass   unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include        fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Cache assets statiques
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Logs
    error_log  /var/log/nginx/sglp_error.log;
    access_log /var/log/nginx/sglp_access.log;
}
```

```bash
# Activer le site
sudo ln -s /etc/nginx/sites-available/sglp /etc/nginx/sites-enabled/
sudo nginx -t               # Tester la configuration
sudo systemctl reload nginx
```

---

## 8. Configuration HTTPS/SSL

### 8.1 Obtenir un certificat Let's Encrypt (gratuit)
```bash
# Installer Certbot
sudo apt-get install certbot python3-certbot-apache   # pour Apache
# ou
sudo apt-get install certbot python3-certbot-nginx    # pour Nginx

# Générer le certificat
sudo certbot --apache -d sglp.ga -d www.sglp.ga
# ou
sudo certbot --nginx -d sglp.ga -d www.sglp.ga

# Le renouvellement automatique est configuré par défaut
# Vérifier : sudo certbot renew --dry-run
```

### 8.2 Vérifier la configuration SSL
```bash
# Tester le grade SSL (depuis la machine locale)
curl -I https://www.sglp.ga

# Ou utiliser le service en ligne : https://www.ssllabs.com/ssltest/
```

---

## 9. Commandes de finalisation

Ces commandes doivent être exécutées **dans l'ordre** après chaque déploiement.

```bash
cd /var/www/sglp

# ── 1. Générer la clé d'application (UNE SEULE FOIS à la première installation)
php artisan key:generate

# ── 2. Créer le lien symbolique storage → public/storage
php artisan storage:link

# ── 3. Exécuter les migrations
php artisan migrate --force

# ── 4. Vider tous les caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# ── 5. Reconstruire les caches optimisés pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── 6. Optimiser l'autoloader Composer
composer dump-autoload --optimize

# ── 7. Vérifier l'état de santé des QR codes
php artisan qr:health-check
```

> ⚠️ **`key:generate` ne doit être exécuté qu'une seule fois** lors de la première installation. Si la clé change, toutes les sessions et données chiffrées (sessions, cookies) seront invalidées.

---

## 10. Import de la base de données NIP

Le fichier `BD_NIP_FINALE.csv` contient **1 019 323 enregistrements** de citoyens gabonais. L'import doit être réalisé après les migrations.

```bash
cd /var/www/sglp

# Transférer le fichier CSV sur le serveur (depuis local)
scp conception/BD_NIP_FINALE.csv user@serveur:/var/www/sglp/conception/

# Lancer l'import (environ 5-10 minutes selon le serveur)
php artisan nip:import --chunk=1000

# En cas de réimport complet (repart de zéro)
php artisan nip:import --chunk=1000 --fresh
```

**Résultat attendu :**
```
Import terminé.
+-----------------------------------+-----------+
| Résultat                          | Nombre    |
+-----------------------------------+-----------+
| Importés                          | 1 018 904 |
| Ignorés (doublons / invalides)    | 420       |
| Total traités                     | 1 019 324 |
+-----------------------------------+-----------+
```

> **Note :** Le fichier CSV doit rester en dehors du dossier `/public` pour ne pas être accessible via le navigateur. Le répertoire `conception/` est hors de la racine web.

---

## 11. Portail Public CMS

Le portail public dispose d'un CMS complet géré depuis l'administration (`/admin/portail`). Il comprend les modules : **actualités**, **documents**, **FAQ**, **guides**, **événements**, **messages de contact** et **paramètres**.

### 11.1 Structure des fichiers uploadés (CMS)

Les fichiers uploadés par les administrateurs sont stockés dans `storage/app/public/` avec des **noms UUID** (ex: `550e8400-e29b-41d4-a716-446655440000.pdf`) pour éviter toute collision ou prédictibilité.

```
storage/app/public/
├── portail/
│   ├── actualites/      ← Images des articles (JPEG, PNG, WebP — max 2 Mo)
│   ├── documents/       ← Documents publics (PDF, DOCX — max 20 Mo)
│   └── guides/          ← Guides pratiques PDF (max 20 Mo)
```

> ⚠️ **Important :** Le lien symbolique `public/storage → storage/app/public` doit exister (`php artisan storage:link`). Sans ce lien, les images et fichiers ne sont pas accessibles depuis le navigateur.

### 11.2 Configuration des uploads

Dans le fichier `.env`, vérifier que les limites PHP autorisent les fichiers volumineux :

```dotenv
UPLOAD_MAX_FILESIZE=20M
POST_MAX_SIZE=25M
MAX_EXECUTION_TIME=60
```

Côté `php.ini` :
```ini
upload_max_filesize = 20M
post_max_size = 25M
```

### 11.3 Accès administration CMS

| Module | URL admin | Route Laravel |
|--------|-----------|---------------|
| Tableau de bord portail | `/admin/portail` | `admin.portail.dashboard` |
| Actualités | `/admin/portail/actualites` | `admin.portail.actualites.*` |
| Documents publics | `/admin/portail/documents` | `admin.portail.documents.*` |
| FAQ | `/admin/portail/faqs` | `admin.portail.faqs.*` |
| Guides | `/admin/portail/guides` | `admin.portail.guides.*` |
| Événements | `/admin/portail/evenements` | `admin.portail.evenements.*` |
| Messages reçus | `/admin/portail/messages` | `admin.portail.messages.*` |
| Paramètres | `/admin/portail/parametres` | `admin.portail.parametres.*` |

### 11.4 Annuaire des organisations

L'annuaire public (`/annuaire`) affiche dynamiquement les organisations ayant un numéro de récépissé (`numero_recepisse IS NOT NULL`) avec les statuts `soumis`, `en_validation`, `approuve`, `suspendu`.

**Vérification de récépissé :** L'URL `/annuaire/verify/{code}` accepte trois types de codes :
1. Code QR (recherche dans la table `qr_codes`)
2. Numéro de récépissé exact (ex: `ASS-2024-000042`)
3. ID numérique de l'organisation

Le endpoint est soumis à un throttle de **20 requêtes/minute par IP** pour prévenir le scraping.

---

## 12. API Interopérabilité V1

Le SGLP expose une API REST JSON permettant aux systèmes tiers d'accéder aux données officielles de l'annuaire.

### 12.1 Endpoints disponibles

Base URL : `https://www.sglp.ga/api/v1/public`

| Méthode | Endpoint | Permission | Description |
|---------|----------|-----------|-------------|
| `GET` | `/organisations` | `organisations` | Liste paginée (filtres : type, statut, province, search) |
| `GET` | `/organisations/{id}` | `organisations` | Détail organisation + membres bureau |
| `GET` | `/organisations/verify/{code}` | `verify` | Vérification récépissé / QR code |
| `GET` | `/stats` | `stats` | Statistiques agrégées par type et statut |
| `GET` | `/api/v1/documentation` | — | Documentation (public, sans auth) |
| `GET` | `/api/v1/openapi.json` | — | Spécification OpenAPI 3.0 (public) |

### 12.2 Authentification

Chaque requête doit porter un **Bearer Token** dans le header HTTP :

```
Authorization: Bearer VOTRE_TOKEN_API
Accept: application/json
```

Les tokens sont gérés depuis l'administration (`/admin/api/tokens`). Ils sont stockés en base sous forme de **hash SHA-256** — la valeur brute n'est jamais persistée et ne peut être récupérée après génération.

### 12.3 Création d'un token en production

```bash
# Via l'interface web admin (recommandé)
# → /admin/api/tokens → "Nouveau token"

# Ou via Artisan Tinker (si accès SSH)
php artisan tinker
>>> [$raw, $token] = \App\Models\ApiToken::generate([
...     'nom' => 'Nom du système client',
...     'organisation_cliente' => 'Ministère X',
...     'permissions' => ['organisations', 'stats', 'verify'],
...     'rate_limit' => 60,
... ]);
>>> echo $raw;  // ← Copier immédiatement, non récupérable ensuite
```

> ⚠️ Le token brut est affiché **une seule fois**. Le transmettre immédiatement au système client par canal sécurisé (messagerie chiffrée, coffre-fort de secrets).

### 12.4 Scopes de permission

| Scope | Endpoints autorisés |
|-------|---------------------|
| `organisations` | `/organisations`, `/organisations/{id}` |
| `verify` | `/organisations/verify/{code}` |
| `stats` | `/stats` |
| `*` | Tous les endpoints |

### 12.5 Rate limiting

- Limite globale : **120 req/min** par IP (couche réseau)
- Limite par token : configurable de 10 à 600 req/min (défaut : 60)
- Headers de réponse : `X-RateLimit-Limit`, `X-RateLimit-Remaining`
- Code HTTP 429 si dépassé avec header `Retry-After`

### 12.6 Données exposées / non exposées

| Donnée | Exposée |
|--------|---------|
| Nom, sigle, type, statut, province | ✅ Oui |
| Numéro de récépissé | ✅ Oui |
| Membres du bureau (nom, prénom, fonction) | ✅ Oui |
| **NIP des membres** | ❌ Non — jamais exposé |
| Données personnelles opérateurs | ❌ Non |
| Dossiers / documents internes | ❌ Non |

### 12.7 Révocation d'un token compromis

```bash
# Via l'interface admin : /admin/api/tokens → "Révoquer"

# Ou via Tinker
php artisan tinker
>>> \App\Models\ApiToken::where('prefix', 'XDqbB4Yc')->first()?->update(['est_actif' => false]);
```

---

## 13. Permissions et sécurité des fichiers

### 13.1 Définir les permissions correctes
```bash
cd /var/www/sglp

# Propriétaire : utilisateur applicatif, groupe : www-data (serveur web)
sudo chown -R sglp:www-data .

# Répertoires : 755 (lecture + traversée pour www-data)
sudo find . -type d -exec chmod 755 {} \;

# Fichiers : 644 (lecture seule pour www-data)
sudo find . -type f -exec chmod 644 {} \;

# Répertoires écriture obligatoire pour Laravel
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache

# Protéger le fichier .env
sudo chmod 640 .env
sudo chown sglp:www-data .env
```

### 13.2 Vérifier que les chemins sensibles sont inaccessibles
```bash
# Ces URLs doivent retourner 403 ou 404
curl -I https://www.sglp.ga/.env
curl -I https://www.sglp.ga/storage/logs/laravel.log
curl -I https://www.sglp.ga/conception/BD_NIP_FINALE.csv
```

### 13.3 Headers de sécurité (SecurityHeaders middleware)

Le middleware `App\Http\Middleware\SecurityHeaders` est enregistré **globalement** dans `app/Http/Kernel.php` et injecte automatiquement les headers suivants sur toutes les réponses :

| Header | Valeur |
|--------|--------|
| `X-Frame-Options` | `SAMEORIGIN` |
| `X-Content-Type-Options` | `nosniff` |
| `X-XSS-Protection` | `1; mode=block` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | caméra, micro, géoloc désactivés |

Ces headers sont actifs en développement et en production sans configuration supplémentaire.

---

## 14. Configuration des tâches planifiées (Cron)

```bash
# Éditer la crontab de l'utilisateur applicatif
sudo crontab -u sglp -e
```

Ajouter les lignes suivantes :
```cron
# Laravel Scheduler — toutes les minutes
* * * * * cd /var/www/sglp && php artisan schedule:run >> /dev/null 2>&1

# Nettoyage des verrous expirés — toutes les heures
0 * * * * cd /var/www/sglp && php artisan pngdi:clean-locks >> /dev/null 2>&1

# Sauvegarde base de données — tous les jours à 2h00
0 2 * * * mysqldump -u rs2715024_sglp -p[MOT_DE_PASSE] rs2715024_sglp | gzip > /backups/sglp_$(date +\%Y\%m\%d).sql.gz 2>/dev/null

# Nettoyage des logs de l'API (optionnel — rotation mensuelle)
0 3 1 * * find /var/www/sglp/storage/logs -name "laravel-*.log" -mtime +30 -delete 2>/dev/null
```

---

## 15. Vérification post-déploiement

Effectuer ces vérifications dans l'ordre après chaque déploiement.

### 15.1 Checklist de vérification

```bash
# 1. Vérifier que l'application répond
curl -o /dev/null -s -w "%{http_code}" https://www.sglp.ga/
# Attendu : 200

# 2. Vérifier la redirection HTTP → HTTPS
curl -o /dev/null -s -w "%{redirect_url}" http://www.sglp.ga/
# Attendu : https://www.sglp.ga/

# 3. Vérifier les headers de sécurité
curl -I https://www.sglp.ga/ | grep -E "X-Frame|X-Content|Strict-Transport|Content-Security"

# 4. Vérifier les logs d'erreur (aucune erreur critique)
tail -50 storage/logs/laravel.log

# 5. Vérifier le nombre d'enregistrements NIP
php artisan tinker --execute="echo \App\Models\NipDatabase::count();"
# Attendu : 1018904

# 6. Vérifier les QR codes
php artisan qr:health-check

# 7. Tester la connexion base de données
php artisan tinker --execute="echo \DB::connection()->getPdo() ? 'OK' : 'KO';"

# 8. Vérifier le Portail CMS (contenu de base)
php artisan tinker --execute="echo \App\Models\PortailParametre::count() . ' paramètres portail';"

# 9. Tester l'API sans token (doit retourner 401)
curl -s -o /dev/null -w "%{http_code}" https://www.sglp.ga/api/v1/public/stats
# Attendu : 401
```

### 15.2 Tests fonctionnels manuels

| Test | URL | Résultat attendu |
|------|-----|-----------------|
| Page d'accueil | `https://www.sglp.ga/` | Page publique visible |
| Connexion admin | `https://www.sglp.ga/admin/login` | Formulaire de connexion |
| Connexion opérateur | `https://www.sglp.ga/login` | Formulaire de connexion |
| Actualités publiques | `https://www.sglp.ga/actualites` | Liste des actualités |
| Documents publics | `https://www.sglp.ga/documents` | Liste des documents (HTTP 200) |
| FAQ | `https://www.sglp.ga/faq` | Page FAQ |
| Guides | `https://www.sglp.ga/guides` | Liste des guides |
| Annuaire | `https://www.sglp.ga/annuaire` | Liste des organisations |
| Vérification récépissé | `https://www.sglp.ga/annuaire/verify/TEST` | Page d'erreur "non trouvé" |
| Vérification document | `https://www.sglp.ga/document-verify` | Page de vérification |
| API sans token | `https://www.sglp.ga/api/v1/public/stats` | JSON `{"error":"UNAUTHORIZED"}` (401) |
| API stats documents | `https://www.sglp.ga/api/document-stats` | JSON `{"success":true}` |
| Documentation API | `https://www.sglp.ga/api/v1/documentation` | Page de documentation (public) |
| OpenAPI JSON | `https://www.sglp.ga/api/v1/openapi.json` | JSON spec OpenAPI 3.0 |
| Admin Portail | `https://www.sglp.ga/admin/portail` | Tableau de bord CMS |
| Admin API Tokens | `https://www.sglp.ga/admin/api/tokens` | Gestion des tokens API |

---

## 16. Optimisation pour la production

### 16.1 Optimisations Laravel (à exécuter après chaque déploiement)
```bash
# Cache de configuration (obligatoire)
php artisan config:cache

# Cache des routes (obligatoire)
php artisan route:cache

# Cache des vues Blade (recommandé)
php artisan view:cache

# Optimiser l'autoloader Composer
composer install --optimize-autoloader --no-dev
```

### 16.2 Optimisation de la base de données
```sql
-- Analyser et optimiser les tables principales après l'import NIP
ANALYZE TABLE nip_database;
OPTIMIZE TABLE nip_database;

-- Vérifier les index
SHOW INDEX FROM nip_database;
SHOW INDEX FROM organisations;
SHOW INDEX FROM dossiers;
```

### 16.3 Passer en mode maintenance pour les mises à jour
```bash
# Activer le mode maintenance (affiche une page 503)
php artisan down --message="Maintenance en cours, retour dans quelques minutes." --retry=60

# ... effectuer les opérations de mise à jour ...

# Désactiver le mode maintenance
php artisan up
```

---

## 17. Mises à jour et redéploiement

### 17.1 Procédure de mise à jour standard

```bash
cd /var/www/sglp

# 1. Activer le mode maintenance
php artisan down

# 2. Récupérer les nouvelles sources
git pull origin main
# ou rsync depuis le poste local

# 3. Mettre à jour les dépendances
composer install --optimize-autoloader --no-dev

# 4. Rebuilder les assets frontend (si modification)
npm ci --omit=dev && npm run production

# 5. Exécuter les nouvelles migrations
php artisan migrate --force

# 6. Vider et reconstruire les caches
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache

# 7. Corriger les permissions si nécessaire
sudo chown -R sglp:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 8. Désactiver le mode maintenance
php artisan up

# 9. Vérifier les logs
tail -20 storage/logs/laravel.log
```

---

## 18. Sauvegarde et restauration

### 18.1 Sauvegarde manuelle de la base de données
```bash
# Sauvegarde complète
mysqldump -u rs2715024_sglp -p[MOT_DE_PASSE] \
    --single-transaction \
    --routines \
    --triggers \
    rs2715024_sglp | gzip > /backups/sglp_$(date +%Y%m%d_%H%M%S).sql.gz

# Sauvegarde sans la table nip_database (trop volumineuse — 1M lignes)
mysqldump -u rs2715024_sglp -p[MOT_DE_PASSE] \
    --single-transaction \
    --ignore-table=rs2715024_sglp.nip_database \
    rs2715024_sglp | gzip > /backups/sglp_sans_nip_$(date +%Y%m%d).sql.gz
```

### 18.2 Sauvegarde des fichiers uploadés
```bash
# Sauvegarder le dossier storage/app (documents générés, uploads)
tar -czf /backups/sglp_storage_$(date +%Y%m%d).tar.gz \
    /var/www/sglp/storage/app/
```

### 18.3 Restauration
```bash
# Restaurer la base de données
gunzip < /backups/sglp_20260309.sql.gz | mysql \
    -u rs2715024_sglp -p[MOT_DE_PASSE] rs2715024_sglp

# Restaurer les fichiers
tar -xzf /backups/sglp_storage_20260309.tar.gz -C /
```

---

## 19. Résolution des problèmes courants

### Erreur 500 — Internal Server Error
```bash
# 1. Vérifier les logs Laravel
tail -100 /var/www/sglp/storage/logs/laravel.log | grep -E "ERROR|CRITICAL"

# 2. Activer temporairement le debug (NE PAS laisser en production)
# Dans .env : APP_DEBUG=true — Relire la page — Puis remettre APP_DEBUG=false

# 3. Vérifier les permissions
ls -la storage/ bootstrap/cache/

# 4. Vider le cache
php artisan cache:clear && php artisan config:clear
```

### Impossible de se connecter (login KO)
```bash
# Vérifier la configuration session
php artisan tinker --execute="echo config('session.driver');"

# Vérifier que SESSION_SECURE_COOKIE=true et que HTTPS est actif
curl -I https://www.sglp.ga/login | grep "Set-Cookie"

# Vider les sessions
rm -f storage/framework/sessions/*
```

### Erreur "CSRF token mismatch"
```bash
# Vider le cache et les sessions
php artisan cache:clear
php artisan config:clear
rm -f storage/framework/sessions/*

# Vérifier que APP_URL correspond exactement au domaine accédé
grep APP_URL .env
```

### Les QR codes ne s'affichent pas
```bash
# Vérifier le lien symbolique storage
ls -la public/storage
# Doit pointer vers : ../storage/app/public

# Recréer le lien si absent
php artisan storage:link

# Vérifier et régénérer les QR codes manquants
php artisan qr:health-check
php artisan qr:fix-missing-png
```

### Erreur de génération PDF
```bash
# Vérifier les fonts DomPDF
ls storage/fonts/

# Vérifier les permissions sur storage
sudo chmod -R 775 storage/
sudo chown -R sglp:www-data storage/

# Vider le cache des vues
php artisan view:clear
```

### Migration échoue
```bash
# Voir le statut des migrations
php artisan migrate:status

# Relancer depuis une migration spécifique
php artisan migrate --force --path=database/migrations/2026_02_19_120000_...

# En dernier recours : rollback d'une migration
php artisan migrate:rollback --step=1 --force
```

### Portail CMS — contenu absent (page d'accueil vide)
```bash
# Vérifier si le seeder a été exécuté
php artisan tinker --execute="echo \App\Models\PortailParametre::count();"
# Si 0 : relancer le seeder
php artisan db:seed --class=PortailSeeder --force

# Vérifier le lien symbolique storage (images actualités)
ls -la public/storage
php artisan storage:link   # si absent
```

### API retourne 401 alors qu'un token est fourni
```bash
# 1. Vérifier que le token est bien passé en Bearer (pas Basic, pas de guillemets)
curl -H "Authorization: Bearer VOTRE_TOKEN" https://www.sglp.ga/api/v1/public/stats

# 2. Vérifier que le token est actif en base
php artisan tinker
>>> \App\Models\ApiToken::where('est_actif', true)->pluck('prefix', 'nom');

# 3. Vérifier que le token n'est pas expiré
>>> \App\Models\ApiToken::where('est_actif', true)->get(['nom','prefix','expires_at']);

# 4. Si token perdu, en générer un nouveau depuis l'admin
# → /admin/api/tokens → "Nouveau token"
```

### API retourne 429 (rate limit dépassé)
```bash
# Vérifier la configuration du token concerné
php artisan tinker
>>> \App\Models\ApiToken::where('prefix', 'XXXXXXXX')->first()->rate_limit;

# Augmenter la limite si légitime (via l'admin ou tinker)
>>> \App\Models\ApiToken::where('prefix', 'XXXXXXXX')
...   ->first()->update(['rate_limit' => 120]);

# Le cache de rate limit se réinitialise automatiquement après 1 minute
```

### Documents publics — erreur 500 sur `/documents`
```bash
# Vérifier que la table portail_documents existe et contient des données
php artisan tinker --execute="echo \App\Models\PortailDocument::count();"

# Si la table est vide, relancer le seeder
php artisan db:seed --class=PortailSeeder --force

# Si la table n'existe pas, relancer la migration
php artisan migrate --force
```

---

## Récapitulatif des commandes essentielles

```bash
# ── PREMIÈRE INSTALLATION ──────────────────────────────────
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan db:seed --class=PortailSeeder --force   # Contenu initial portail
php artisan storage:link
php artisan nip:import --chunk=1000

# ── APRÈS CHAQUE DÉPLOIEMENT ──────────────────────────────
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── MAINTENANCE ────────────────────────────────────────────
php artisan down / php artisan up
php artisan cache:clear
php artisan queue:restart
php artisan pngdi:clean-locks

# ── SURVEILLANCE ───────────────────────────────────────────
php artisan qr:health-check
tail -f storage/logs/laravel.log

# ── API INTEROPÉRABILITÉ ───────────────────────────────────
# Créer un token (interactif via admin) : /admin/api/tokens
# Révoquer un token compromis :
php artisan tinker --execute="\App\Models\ApiToken::where('prefix','XXXXXXXX')->update(['est_actif'=>false]);"
# Vérifier les accès récents :
php artisan tinker --execute="\App\Models\ApiToken::orderByDesc('last_used_at')->get(['nom','prefix','last_used_at','total_requests'])->each(fn(\$t)=>print(\$t->nom.' | '.\$t->prefix.'... | '.\$t->total_requests.' req'.PHP_EOL));"
```

---

*Document mis à jour le 10 mars 2026 — SGLP v2.1*
*Toute modification de l'infrastructure doit être répercutée dans ce document.*
