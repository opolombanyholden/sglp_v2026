# SGLP v2 — Bilan d'avancement

> Référence : commit `f72490c`, branche `feat/responsive-portail-mobile-tablette`.
> Toutes les mesures de ce document ont été relevées sur le code et la base de
> développement, pas estimées.

---

## 0. Avertissement méthodologique — à lire en premier

**Aucun cahier des charges n'est versionné dans le dépôt.** Le dossier
`conception/` a été retiré de git au commit `2683c64` et ne contient plus
localement que des dumps SQL et des scripts de restauration. Les documents
supprimés à cette occasion étaient : charte graphique, manuel de déploiement,
guide administrateur, rapports d'audit ANINF — aucun document d'exigences.

Ce bilan est donc construit sur trois sources vérifiables :

1. **Le code lui-même** : routes, contrôleurs, modèles, migrations, données.
2. **Trois exigences fonctionnelles formalisées** conservées hors dépôt
   (mémoire projet), datées d'avril 2026.
3. **Le manuel d'installation et de déploiement en production v2.1**
   (`conception/MANUEL_DEPLOIEMENT_PRODUCTION.md`, 10/03/2026), replacé dans
   le dossier `conception/`. Ce document décrit la cible d'exploitation, pas
   les exigences fonctionnelles ; sa confrontation au code fait l'objet du §5.

**Conséquence directe : ce document constate l'existant, il ne mesure pas un
taux de conformité.** Toute annonce d'un pourcentage d'avancement serait
inventée. Pour obtenir une véritable matrice de conformité, il faut fournir le
cahier des charges ; la comparaison ligne à ligne pourra alors être produite.

---

## 1. Vue d'ensemble

| Élément | Valeur relevée |
|---|---|
| Framework | Laravel 9, PHP 8.2 (MAMP), MySQL (port 8889) |
| Contrôleurs | 66 (hors fichiers `copy`) |
| Modèles Eloquent | 58 |
| Migrations | 108 |
| Routes enregistrées | 920 (`php artisan route:list` s'exécute sans erreur) |
| Tests automatisés | 9 (dont 7 écrits lors de la dernière session) |

### Volumétrie métier

| Table | Lignes |
|---|---|
| organisations | 81 |
| dossiers | 91 |
| adherents | 1 227 |
| membres_bureau | 223 |
| fondateurs | 78 |
| document_generations | 362 |
| document_templates | 36 |
| users | 16 |
| roles / permissions | 10 / 61 |
| workflow_steps | 8 |
| **nip_database** | **0** ⚠️ voir §4 |

Répartition des dossiers : `creation` 81, `modification` 8, `correction` 2.
Statuts : `approuve` 83, `soumis` 4, `brouillon` 3, `accepte` 1.

---

## 2. État par domaine fonctionnel

Légende : **Opérationnel** = chemin complet vérifié (route → contrôleur →
données réelles). **Partiel** = implémenté mais avec réserve identifiée.
**Embryonnaire** = route exposée sans logique métier.

### 2.1 Authentification, rôles et permissions — **Partiel**

Double portail (`/admin/login`, `/login`), middlewares `CheckAdminRole`,
`CheckAdminOnly`, `CheckOperatorRole`, `CheckAccountStatus`. 10 rôles avancés
avec 61 permissions et une matrice de permissions administrable.

**Réserve structurante :** deux systèmes de rôles coexistent — la colonne
héritée `users.role` (admin/agent/operator/visitor, qui pilote l'accès au
back-office) et `users.role_id` (rôle avancé, qui porte les permissions).
Rien ne garantissait leur cohérence : c'est la cause du dysfonctionnement des
listes d'assignation corrigé au commit `f72490c`. Une règle de cohérence est
désormais imposée à la création/édition, mais **l'unification des deux systèmes
reste à faire**.

2FA implémentée (colonnes, codes, middleware) mais **activée sur 0 compte /16**.

### 2.2 Organisations et référentiels géographiques — **Opérationnel**

81 organisations, types d'organisation paramétrables, découpage administratif
complet (provinces, départements, cantons, arrondissements, communes,
localités, regroupements), domaines d'activité, fonctions des membres.

### 2.3 Dossiers et workflow — **Opérationnel**

91 dossiers, 8 étapes de workflow, entités de validation, priorité FIFO avec
justification pour l'urgence, historique des opérations (`dossier_operations`),
verrouillage concurrent (`DossierLock`).

L'assignation des dossiers a été fiabilisée : source unique
`User::assignables()` (administrateurs niveau ≥ 8 et modérateurs niveau ≥ 6),
contrôle serveur dans `assign()` et `attribuer()`.

### 2.4 Adhérents, fondateurs, membres du bureau — **Opérationnel**

1 227 adhérents. Import massif par découpage en lots
(`ChunkProcessorController`, `ChunkingController`), détection d'anomalies
(`AdherentAnomalie`), historique (`AdherentHistory`).

### 2.5 Documents officiels et récépissés — **Opérationnel**

36 modèles de documents, 362 documents générés, numérotation dédiée
(`DocumentNumberingService`), QR code avec URL de vérification et hash,
compteur de téléchargements, invalidation traçable. Designer de templates
(`DocumentTemplateController`, `DocumentCustomizationController`).

Types générés : `recepisse_definitif` 217, `recepisse_provisoire` 141,
`accuse_reception` 4.

### 2.6 Portail public et CMS — **Opérationnel**

7 modules administrables : actualités (6), documents (12), FAQ (12), guides
(5), événements (5), paramètres, messages de contact. Refonte responsive
mobile/tablette livrée en juin 2026.

### 2.7 Annuaire public et vérification de document — **Opérationnel**

`/annuaire`, `/annuaire/{id}`, `/annuaire/verify/{code}` (limité à 20
req/min), API publique de vérification (`/api/verify-document/{token}`,
`verify-qr`, `document-info`) sous throttle 120/min.

### 2.8 API d'interopérabilité V1 — **Opérationnel, non ouvert**

Contrairement à une première lecture partielle, l'API décrite au §12 du manuel
de déploiement **est bien implémentée** :

| Route | Contrôleur |
|---|---|
| `GET /api/v1/public/organisations` | `Api\V1\OrganisationApiController` |
| `GET /api/v1/public/organisations/{id}` | idem |
| `GET /api/v1/public/organisations/verify/{code}` | idem |
| `GET /api/v1/public/stats` | idem |
| `GET /api/v1/documentation` | closure (public) |
| `GET /api/v1/openapi.json` | closure (public) |

Authentification par Bearer token (`ApiToken::generate()`, hash SHA-256, la
valeur brute n'est jamais persistée), scopes de permission, rate limiting par
jeton, gestion complète depuis `/admin/api/tokens` (index, create, store,
destroy, activate).

**Seule réserve : 0 jeton émis.** Le canal est prêt mais aucun système tiers
n'y est raccordé — il n'a donc jamais été éprouvé en conditions réelles.

### 2.9 Modules non implémentés — **Embryonnaire**

Exposés dans le routage côté opérateur mais sans logique :

- `calendrier` → renvoie la vue `operator.calendrier-placeholder`
- `subventions` → redirections, commentaire « en attendant implémentation »
- `rapports` → s'appuie sur `DeclarationController`, pas de contrôleur dédié

---

## 3. Conformité aux trois exigences fonctionnelles formalisées

### 3.1 Récépissés distincts par type d'opération — **Satisfait, modélisation différente**

L'exigence demandait une table `recepisses` porteuse d'un `type_operation` et
un bouton d'impression par acte.

Constat : l'objectif est atteint par un autre chemin. Chaque opération donne
lieu à un **dossier** distinct portant `type_operation`
(creation/modification/correction), et chaque dossier porte ses propres
`document_generations`. La fiche dossier liste les récépissés émis avec un
bouton *Imprimer* et un bouton *Voir* par ligne, les documents invalidés étant
grisés.

*À vérifier avec le métier :* le `numero_recepisse` public de l'organisation
doit pointer vers l'acte en vigueur le plus récent tout en conservant
l'historique. Ce point n'a pas été audité.

### 3.2 Récépissé de modification citant la dénomination précédente — **Satisfait**

`DocumentGenerationService` conserve `nom_precedent` et `sigle_precedent` avant
écrasement, et les 4 templates de modification (association et politique,
provisoire et définitif) utilisent ces valeurs dans le corps du texte.

### 3.3 Création de dossier avec sauvegarde progressive — **Satisfait**

Route admin `admin.dossiers.save-draft-step`, appelée par le formulaire de
création admin. Côté opérateur, sauvegarde par étape et bouton « Garder en
brouillon » à partir de l'étape 2, avec reprise depuis les brouillons.
3 dossiers sont actuellement au statut `brouillon`.

---

## 4. Risques et points de blocage

Classés par criticité pour la reprise du projet.

### P1 — Migrations désynchronisées (bloquant tout déploiement)

**10 migrations sont marquées `Pending` alors que leurs tables existent déjà**
en base (`portail_actualites`, `portail_documents`, `portail_faqs`,
`portail_guides`, `portail_evenements`, `portail_parametres`,
`portail_messages`, `api_tokens`…).

Conséquence : `php artisan migrate` **échoue** en tentant de recréer ces
tables. C'est pourquoi la dernière migration a dû être lancée en ciblé
(`--path`).

*Portée exacte :* une installation **neuve** (base vide) déroulerait les 108
migrations sans problème. Le blocage concerne tout environnement dont la base
provient d'un dump — c'est-à-dire l'environnement de développement actuel, et
la production si elle a été montée par restauration. Or le manuel de
déploiement prescrit `php artisan migrate --force` à chaque mise à jour
(§17.1) : cette étape échouera sur un tel environnement.

*Action :* vérifier si la production présente la même dérive, puis réconcilier
la table `migrations` (insertion des lignes manquantes après contrôle que
chaque schéma correspond bien).

### P2 — Les tests s'exécutent sur la base de développement

`phpunit.xml` a ses lignes `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:`
**commentées** : la suite tourne sur la base MySQL de travail. `ExampleTest`
importe `RefreshDatabase` sans l'utiliser — le jour où quelqu'un ajoute le
trait dans une classe, **la base de développement est effacée**.

*Action :* configurer une base de test dédiée avant d'écrire d'autres tests.
Les tests actuels sont protégés par `DatabaseTransactions`, mais c'est une
protection par convention, pas par configuration.

### P3 — Base NIP vide en local

`nip_database` contient **0 ligne** alors que 1 018 904 enregistrements avaient
été importés. La base locale a visiblement été restaurée depuis un dump
`sansNIP` (présents dans `conception/`). Toutes les fonctions de vérification
d'identité par NIP sont donc inopérantes en développement.

*Action :* relancer `php artisan nip:import`, ou documenter explicitement que
l'environnement local fonctionne sans NIP.

### P4 — Couverture de tests quasi nulle

9 tests pour 66 contrôleurs et 920 routes, dont 7 écrits lors de la dernière
session sur le seul périmètre création de compte. Aucun test sur le workflow
des dossiers, la génération de documents, l'annuaire ou le portail — c'est-à-dire
sur le cœur métier.

### P5 — Code mort versionné

**22 fichiers** `… copy.php`, `_backup`, `.bak` sont suivis par git, dont des
doublons de fichiers structurants : `DossierController copy.php`,
`WorkflowService copy.php`, `PDFService copy.php`, `routes/admin copy 2.php`,
`routes/admin_backup.php`, `routes/web copy.php`. Ambiguïté permanente sur la
source de vérité, et risque réel de corriger le mauvais fichier.

### P6 — Notifications par e-mail non finalisées

`QUEUE_CONNECTION=sync` : tout envoi bloque la requête HTTP. Une seule classe
de notification existe (`CustomVerifyEmail`), aucun job en file. 26 marqueurs
`TODO/FIXME` subsistent, dont plusieurs concernent précisément l'envoi de mails
(réinitialisation de mot de passe, mail de bienvenue, identifiants).

### P7 — Modules exposés mais vides

`calendrier` et `subventions` sont accessibles côté opérateur et renvoient des
placeholders. À masquer de la navigation tant qu'ils ne sont pas implémentés,
pour ne pas dégrader la perception utilisateur.

### P8 — Hygiène du dépôt

5 fichiers `.DS_Store` restent suivis malgré leur présence dans `.gitignore`
(ils y ont été ajoutés après leur premier commit). `.phpunit.result.cache`
n'est pas ignoré.

---

## 4bis. Confrontation au manuel de déploiement v2.1

Chaque affirmation vérifiable du manuel a été testée contre le code.

### Ce que le manuel décrit et qui est confirmé

| Élément du manuel | Vérification |
|---|---|
| API V1 (§12) : 6 endpoints, Bearer token, scopes, rate limit | ✅ routes et contrôleurs présents |
| Gestion des jetons `/admin/api/tokens` | ✅ 5 routes CRUD |
| `ApiToken::generate()` avec hash SHA-256 | ✅ méthode présente |
| Commandes `qr:health-check`, `qr:fix-missing-png`, `pngdi:clean-locks`, `nip:import` | ✅ les 4 existent |
| Lien symbolique `public/storage` | ✅ en place |
| `npm run production` (Laravel Mix) | ✅ script défini |
| `.env.example` | ✅ présent |
| Middleware `SecurityHeaders` global (§13.3) | ✅ enregistré dans le Kernel |
| Portail CMS : 7 modules admin (§11.3) | ✅ routes conformes |
| Annuaire + throttle 20 req/min (§11.4) | ✅ conforme |

### Écarts constatés — à corriger dans le manuel ou dans le code

**A. La séquence de seeders du manuel est inexécutable en l'état (§6.3).**

Le manuel prescrit :
```bash
php artisan db:seed --class=ProvincesSeeder --force
php artisan db:seed --class=OrganisationTypesSeeder --force
```
**Ces deux classes n'existent pas.** Les seeders réellement disponibles sont :
`PermissionSeeder`, `RoleSeeder`, `SuperAdminSeeder`, `GeolocalisationSeeder`,
`ReferenceDataSeeder`, `DocumentTemplateSeeder`, `PortailSeeder`,
`ValidationEntitySeeder`, `WorkflowStepSeeder`, `WorkflowStepEntitySeeder`.

**B. Une installation neuve suivant le manuel produit un système inexploitable.**

`DatabaseSeeder` n'appelle que 4 seeders : Permission, Role, SuperAdmin,
Geolocalisation. Il **n'appelle pas** `ReferenceDataSeeder` (qui chaîne
lui-même ValidationEntity + WorkflowStep + WorkflowStepEntity), ni
`DocumentTemplateSeeder`.

Conséquence sur une base neuve, après `db:seed --force` + `PortailSeeder`
comme le prescrit le manuel :

| Table | Dev | Après installation « manuel » |
|---|---|---|
| `workflow_steps` | 8 | **0** → aucun dossier ne peut avancer |
| `validation_entities` | 7 | **0** |
| `document_templates` | 36 | **0** → aucun récépissé générable |
| `organisation_types` | 5 | **0** → aucune organisation créable |
| `document_types` | 22 | **0** |
| `domaines_activite` | 14 | **0** |

De plus `DocumentTemplateSeeder` ne fait que *lire* les types d'organisation
(`OrganisationType::where('code', …)->first()?->id`) : sans données de
référence préalables, il produirait des modèles orphelins.

**C. Le provisionnement réel passe par un script SQL sorti du dépôt.**

Les tables de référence ci-dessus sont en réalité peuplées par
`conception/sglp_sync_reference_data.sql` (ajouté au commit `196ad4b`, puis
perdu au commit `2683c64`).

1 612 lignes, 1 411 instructions `REPLACE INTO` — donc **idempotent, sans
effet sur les données métier** — couvrant `organisation_types`,
`document_types`, `document_templates`, `domaines_activite`, `fonctions`,
`operation_types`, le découpage géographique complet, `permissions` et le
contenu du portail.

> **✅ Corrigé.** Le fichier a été restauré depuis l'historique (intégrité
> vérifiée par empreinte SHA-256) et est de nouveau versionné.
>
> Le `.gitignore` portait déjà l'intention de le conserver
> (`!conception/sglp_sync_reference_data.sql`), mais la règle `/conception/`
> ajoutée ensuite la neutralisait : **git ne descend pas dans un dossier
> exclu**, une négation sur un fichier qu'il contient est donc sans effet. La
> règle est passée à `/conception/*`, avec la négation placée après elle.
> Les dumps volumineux, `BD_NIP_FINALE.csv` et `conception/.env` restent
> ignorés — vérifié.

*Reste à faire :* référencer ce script au §6.3 du manuel de déploiement, en
remplacement des deux seeders inexistants.

**D. Le fichier `BD_NIP_FINALE.csv` est absent** du dossier `conception/`,
alors que le §10 et la vérification §15.1 (attendu : 1 018 904 lignes) en
dépendent. Cohérent avec le constat P3.

**E. Le scheduler Laravel est vide.** Le manuel installe une tâche cron
`* * * * * php artisan schedule:run` (§14), mais `app/Console/Kernel.php` ne
déclare aucune tâche planifiée (seul l'exemple `inspire` commenté subsiste).
Cette ligne de cron ne fait donc rien. Le nettoyage des verrous fonctionne
parce qu'il est appelé directement en crontab, pas via le scheduler.

**F. `SESSION_SAME_SITE` du `.env` est inopérant.** Le manuel prescrit
`SESSION_SAME_SITE=strict` (§5.2), mais `config/session.php` fixe la valeur en
dur (`'same_site' => 'strict'`), sans lecture de l'environnement. La valeur
effective est heureusement identique à celle prescrite, mais toute tentative
future de la modifier par le `.env` restera sans effet. Même remarque pour
`'encrypt' => true`.

**G. `QUEUE_CONNECTION=sync` est prescrit par le manuel** (§5.2). La
recommandation de bascule vers une vraie file d'attente (P6) constitue donc un
**écart volontaire** à la cible documentée : à arbitrer avec l'exploitant, et à
répercuter dans le manuel le cas échéant.

---

## 5. Séquence recommandée pour la reprise

### Avant toute nouvelle fonctionnalité

1. ~~Restaurer le script de données de référence~~ — **fait** (écart C).
   Reste à corriger le §6.3 du manuel, qui prescrit encore deux seeders
   inexistants au lieu de ce script, et à valider la séquence complète sur une
   base vierge : c'est la seule preuve que le projet est réinstallable.
2. **Fournir le cahier des charges** et produire la matrice de conformité
   réelle — sans ce document, la priorisation reste une opinion.
3. **P1 — réconcilier les migrations**, après avoir vérifié si la production
   présente la même dérive.
4. **P2 — isoler la base de test.** Dix minutes de configuration qui
   suppriment un risque de perte de données.

### Consolidation (court terme)

4. **P5 — supprimer les 22 fichiers de sauvegarde** (l'historique git remplit
   déjà ce rôle).
5. **P6 — finaliser les notifications** et basculer la file d'attente hors
   `sync`.
6. **P4 — tests sur le cœur métier** : workflow de dossier et génération de
   récépissés en priorité, ce sont les chemins à valeur réglementaire.

### Dette structurelle (moyen terme)

7. **Unifier les deux systèmes de rôles.** Cible : `role_id` seul fait
   autorité, la colonne `role` devient un attribut dérivé puis disparaît. Le
   dysfonctionnement des listes d'assignation était le symptôme visible de
   cette dette ; d'autres surfaces (middlewares, statistiques du tableau de
   bord) reposent encore sur la colonne héritée.
8. **P8 — hygiène du dépôt**, puis décider du sort des modules embryonnaires
   (implémenter ou retirer).

### Sécurité

9. **Activer la 2FA** sur les comptes administrateurs — le dispositif est déjà
   en place, il n'est simplement pas utilisé. Le projet a fait l'objet d'un
   audit ANINF en mars 2026 avec corrections OWASP Top 10 appliquées ; laisser
   la 2FA inactive affaiblit ce travail.

---

## 6. Ce qui a été livré lors de la dernière session (`f72490c`)

- Assignation des dossiers réservée aux administrateurs et modérateurs, avec
  contrôle côté serveur.
- Cohérence imposée entre rôle système et rôle avancé à la création de compte,
  correction de la redirection et de la case « compte actif » inopérante.
- Numéro de téléphone ajouté aux récépissés définitifs de création ; clé
  `telephone_2` désormais alimentée (elle manquait aux récépissés de
  modification).
- Prénom rendu facultatif sur l'ensemble des formulaires concernés, aux trois
  niveaux (validation, formulaire, JavaScript) + migration rendant la colonne
  nullable sur `adherents`, `membres_bureau` et `organe_members`.
- Première suite de tests fonctionnels de bout en bout (7 cas).
