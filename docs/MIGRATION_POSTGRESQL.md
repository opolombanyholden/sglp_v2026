# Bascule de MySQL vers PostgreSQL

Procédure pour faire passer un serveur SGLP **déjà en service sous MySQL** à
PostgreSQL, sans perte de données. Pour une installation neuve, suivre
directement `conception/MANUEL_DEPLOIEMENT_PRODUCTION.md`.

Durée de coupure à prévoir : environ 15 minutes (la copie elle-même prend
quelques minutes ; la base de test, 75 tables et 300 Mo, se copie en 10 s).

## Principe

1. Le schéma PostgreSQL est créé par `php artisan migrate` (une migration de
   base unique, `2026_11_19_000000_create_sglp_schema.php`, générée à partir du
   schéma MySQL réel).
2. La commande `php artisan sglp:copier-depuis-mysql` lit l'ancienne base MySQL
   (connexion `mysql_source`) et recopie toutes les lignes.
3. **La base MySQL n'est jamais modifiée** : elle reste disponible pour revenir
   en arrière.

La commande de copie :

- retire les 140 clés étrangères pendant la copie, puis les recrée — PostgreSQL
  revérifie alors toute l'intégrité des données ;
- répare les références orphelines héritées de MySQL (voir plus bas) ;
- recale les séquences des identifiants ;
- compare le nombre de lignes de chaque table entre les deux bases ;
- s'exécute dans une transaction : en cas d'erreur, rien n'est écrit.

## Prérequis

- PostgreSQL 13 ou plus sur le serveur (ou chez l'hébergeur — **à vérifier
  avant tout** : beaucoup d'offres mutualisées ne proposent que MySQL).
- Extension PostgreSQL `unaccent` disponible (fournie avec PostgreSQL ; sous
  Debian/Ubuntu, paquet `postgresql-contrib` sur les anciennes versions). Elle
  est installée par `php artisan migrate`, sans droit superutilisateur.
- Extension PHP `pdo_pgsql` : `php -m | grep pdo_pgsql`.
- L'extension `pdo_mysql` reste nécessaire le temps de la copie.
- Le code de la branche `feat/migration-postgresql` (ou de la version qui l'inclut).

## Procédure

### 1. Sauvegarder et couper l'accès

```bash
cd /var/www/sglp
php artisan down

mysqldump -u rs2715024_sglp -p --single-transaction rs2715024_sglp \
    | gzip > /backups/sglp_avant_postgresql_$(date +%Y%m%d_%H%M).sql.gz
tar -czf /backups/sglp_storage_avant_postgresql_$(date +%Y%m%d).tar.gz storage/app/
cp .env /backups/env_avant_postgresql
```

### 2. Créer la base PostgreSQL

```bash
sudo -u postgres psql
```
```sql
CREATE ROLE sglp LOGIN PASSWORD '[MOT_DE_PASSE_SECURISE]';
CREATE DATABASE sglp OWNER sglp ENCODING 'UTF8' TEMPLATE template0;
\q
```

L'utilisateur `sglp` n'a pas besoin d'être superutilisateur.

### 3. Déployer le code et configurer `.env`

Après la mise à jour du code (`git pull` ou transfert), modifier `.env` :

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sglp
DB_USERNAME=sglp
DB_PASSWORD=[MOT_DE_PASSE_SECURISE]

# Ancienne base, lue pendant la copie uniquement
SOURCE_DB_HOST=127.0.0.1
SOURCE_DB_PORT=3306
SOURCE_DB_DATABASE=rs2715024_sglp
SOURCE_DB_USERNAME=rs2715024_sglp
SOURCE_DB_PASSWORD=[MOT_DE_PASSE_MYSQL]
```

Supprimer les variables `MYSQL_*` devenues inutiles.

```bash
composer install --no-dev --optimize-autoloader
php artisan config:clear
```

### 4. Créer le schéma

```bash
php artisan migrate --force
```

Résultat attendu : trois migrations exécutées
(`create_personal_access_tokens_table`, `create_sglp_schema` et
`activer_extension_unaccent`).

### 5. Copier les données

```bash
php artisan sglp:copier-depuis-mysql
```

Résultat attendu, en dernière ligne :

```
Copie terminée : nombres de lignes identiques sur les 74 tables.
```

Si la commande s'arrête sur une erreur, **rien n'a été écrit** : corriger la
cause, puis relancer avec `--force` si des tables ont déjà été remplies par une
tentative précédente.

### 6. Vérifier et rouvrir

```bash
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan migrate:status     # aucune migration en attente
```

Tester : connexion admin, connexion opérateur, liste et fiche d'un dossier,
génération d'un récépissé, vérification publique d'un QR code. Puis :

```bash
php artisan up
```

### 7. Après quelques jours de fonctionnement normal

- retirer les variables `SOURCE_DB_*` du `.env` ;
- remplacer la sauvegarde `mysqldump` du cron par `pg_dump` (manuel, § 14 et 18) ;
- archiver la base MySQL, puis la supprimer.

## Revenir en arrière

Tant que la base MySQL n'est pas supprimée :

```bash
php artisan down
cp /backups/env_avant_postgresql .env
git checkout <version précédente>
php artisan optimize:clear
php artisan up
```

Attention : les données saisies **après** la bascule ne sont que dans
PostgreSQL ; elles seraient perdues par ce retour arrière.

## Ce qui change pour l'application

| Sujet | Sous MySQL | Sous PostgreSQL |
|---|---|---|
| Recherches `like` | insensibles à la casse et aux accents | idem : compilées en `unaccent(colonne) ilike unaccent(?)` (« Eglise » trouve « Église ») |
| Comparaisons `=` sur du texte | insensibles à la casse | sensibles à la casse ; les e-mails sont mis en minuscules à la connexion et à la réinitialisation du mot de passe |
| Identifiant non numérique dans une URL (`/dossiers/abc`) | 404 | 404 (traduit par `App\Exceptions\Handler`) |
| Seeders de référentiel | vidaient les tables avec `FOREIGN_KEY_CHECKS=0` | refusent de s'exécuter sur une table déjà remplie (un `TRUNCATE` supprimerait les dossiers en cascade) |
| Booléens (`tinyint(1)`) | 0 / 1 | vrai type `boolean` |
| `enum` | type natif | `varchar` + contrainte `CHECK` (mêmes valeurs autorisées) |

### Corrections de données faites pendant la copie

- **Organisations dont la commune n'existe plus** (référentiel renuméroté) :
  rattachées à la commune de même nom que leur champ `ville_commune`. Sur les
  données du 19/11/2026 : 4 organisations, `commune_ville_ref_id` 89 → 57
  (Libreville).
- Toute autre référence orpheline sur une colonne facultative est mise à NULL,
  et la commande l'affiche. Une référence orpheline sur une colonne obligatoire
  arrête la copie : elle doit être corrigée dans MySQL.

### Corrections de code

- Fonctions propres à MySQL remplacées : `YEAR()`, `MONTH()`, `DATEDIFF`,
  `TIMESTAMPDIFF`, `CURDATE()`, `GROUP_CONCAT`, `INSERT IGNORE`,
  `SUBSTRING_INDEX`, `information_schema` (taille de la base).
- Chaînes SQL entre guillemets doubles (`"haute"`, `"critique"`, `"approuve"`),
  que PostgreSQL lit comme des noms de colonnes.
- `HAVING` sur un alias de `withCount` (tableau de bord admin, matrice des
  permissions) et sur `count` (doublons de NIP à l'import).
- Numérotation des récépissés : `FOR UPDATE` porte sur les lignes, la séquence
  est calculée en PHP (PostgreSQL refuse `FOR UPDATE` sur un agrégat).
- Recherche de permissions : la saisie était collée dans le SQL (injection
  possible) ; elle passe désormais en paramètre.
- Fiche organisation (admin) : le compteur d'anomalies critiques comparait la
  colonne entière `priorite` à `'critique'` — il affichait toujours 0 sous MySQL.

## Limites connues

- Les colonnes JSON héritées de MySQL sont restées en `text` (elles étaient
  `longtext`). Les lectures/écritures via les casts Eloquent fonctionnent ; les
  requêtes sur un chemin JSON (`where('colonne->cle', …)`, `whereJsonContains`)
  ne fonctionnent pas sur ces colonnes. Seuls deux scopes inutilisés de
  `Adherent` (`nipNonTrouveDatabase`, `donneesIncoherentes`) en dépendent.
- Les migrations archivées dans `database/migrations_archive_mysql/` ne doivent
  pas être réactivées.
