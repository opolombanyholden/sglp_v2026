<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DGELP API V1 — Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous">
    <style>
        :root { --primary: #002B7F; --gold: #FFD700; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
        .sidebar { background: var(--primary); color: white; min-height: 100vh; padding: 2rem 1rem; position: sticky; top: 0; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; display: block; padding: 0.4rem 0.75rem; border-radius: 6px; margin-bottom: 2px; font-size: 0.9rem; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.15); color: white; }
        .sidebar h6 { color: var(--gold); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin: 1.5rem 0 0.5rem; }
        .main-content { padding: 2rem; max-width: 900px; }
        .endpoint-card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
        .method-badge { font-size: 0.8rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 6px; }
        .method-get { background: #d4edda; color: #155724; }
        .path-code { font-family: monospace; font-size: 1rem; color: var(--primary); font-weight: 600; }
        .param-table th { font-size: 0.8rem; text-transform: uppercase; color: #666; }
        .response-block { background: #1e1e2e; color: #cdd6f4; border-radius: 8px; padding: 1rem; font-family: monospace; font-size: 0.85rem; overflow-x: auto; }
        .status-200 { color: #a6e3a1; }
        .status-401 { color: #f38ba8; }
        pre { margin: 0; white-space: pre-wrap; }
        .security-box { background: #fff3cd; border-left: 4px solid var(--gold); border-radius: 0 8px 8px 0; padding: 1rem 1.5rem; }
        h1 { color: var(--primary); }
        .toc { list-style: none; padding: 0; }
        .toc li { margin-bottom: 4px; }
        section { scroll-margin-top: 20px; }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar" style="width:240px; flex-shrink:0;">
        <div class="mb-4">
            <strong style="color:var(--gold); font-size:1.1rem;">DGELP API V1</strong><br>
            <small>Documentation officielle</small>
        </div>
        <h6>Démarrage</h6>
        <a href="#introduction">Introduction</a>
        <a href="#authentication">Authentification</a>
        <a href="#errors">Codes d'erreur</a>
        <a href="#rate-limit">Rate limiting</a>
        <h6>Endpoints</h6>
        <a href="#ep-organisations">GET /organisations</a>
        <a href="#ep-organisation">GET /organisations/{id}</a>
        <a href="#ep-verify">GET /organisations/verify/{code}</a>
        <a href="#ep-stats">GET /stats</a>
        <h6>Ressources</h6>
        <a href="{{ url('/api/v1/openapi.json') }}" target="_blank"><i class="fas fa-download me-1"></i>OpenAPI JSON</a>
    </nav>

    <!-- Contenu -->
    <div class="main-content flex-grow-1">
        <section id="introduction">
            <h1 class="mb-1">DGELP API V1</h1>
            <p class="text-muted lead mb-4">API d'interopérabilité du Système de Gestion des Libertés Publiques — Gabon</p>

            <div class="endpoint-card">
                <p>Cette API permet aux systèmes d'information tiers d'accéder aux données officielles de l'annuaire des organisations (associations, ONG, partis politiques, confessions religieuses) enregistrées par le Ministère de l'Intérieur du Gabon.</p>
                <table class="table table-sm table-borderless mb-0">
                    <tr><th style="width:160px">URL de base</th><td><code>{{ url('/api/v1/public') }}</code></td></tr>
                    <tr><th>Format</th><td>JSON (UTF-8)</td></tr>
                    <tr><th>Version</th><td>1.0.0</td></tr>
                    <tr><th>Spec OpenAPI</th><td><a href="{{ url('/api/v1/openapi.json') }}" target="_blank">openapi.json</a></td></tr>
                </table>
            </div>
        </section>

        <section id="authentication">
            <h3>Authentification</h3>
            <div class="security-box mb-3">
                <strong><i class="fas fa-key me-2"></i>Bearer Token requis</strong><br>
                Chaque requête doit inclure un token d'API valide dans le header <code>Authorization</code>.<br>
                Contactez l'administrateur DGELP pour obtenir un token.
            </div>
            <div class="endpoint-card">
                <strong>Header obligatoire :</strong>
                <div class="response-block mt-2"><pre>Authorization: Bearer VOTRE_TOKEN_API</pre></div>
                <strong class="d-block mt-3">Exemple cURL :</strong>
                <div class="response-block mt-2"><pre>curl -H "Authorization: Bearer VOTRE_TOKEN_API" \
     -H "Accept: application/json" \
     "{{ url('/api/v1/public/organisations') }}"</pre></div>
                <strong class="d-block mt-3">Exemple Python :</strong>
                <div class="response-block mt-2"><pre>import requests

headers = {"Authorization": "Bearer VOTRE_TOKEN_API", "Accept": "application/json"}
r = requests.get("{{ url('/api/v1/public/organisations') }}", headers=headers)
print(r.json())</pre></div>
            </div>
        </section>

        <section id="errors">
            <h3>Codes d'erreur</h3>
            <div class="endpoint-card">
                <table class="table table-sm">
                    <thead class="param-table"><tr><th>Code HTTP</th><th>Champ error</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td>401</td><td><code>UNAUTHORIZED</code></td><td>Token manquant, invalide ou révoqué</td></tr>
                        <tr><td>403</td><td><code>FORBIDDEN</code></td><td>Token sans la permission requise pour cet endpoint</td></tr>
                        <tr><td>404</td><td><code>NOT_FOUND</code></td><td>Ressource introuvable</td></tr>
                        <tr><td>422</td><td><code>INVALID_CODE</code></td><td>Paramètre invalide</td></tr>
                        <tr><td>429</td><td>—</td><td>Rate limit dépassé</td></tr>
                    </tbody>
                </table>
                <strong>Format de réponse erreur :</strong>
                <div class="response-block mt-2"><pre>{
  "success": false,
  "error": "UNAUTHORIZED",
  "message": "Token d'API invalide ou révoqué."
}</pre></div>
            </div>
        </section>

        <section id="rate-limit">
            <h3>Rate Limiting</h3>
            <div class="endpoint-card">
                <p>Le nombre de requêtes est limité par token. La limite par défaut est de <strong>60 requêtes par minute</strong>. Les headers de réponse indiquent le quota restant :</p>
                <div class="response-block"><pre>X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
Retry-After: 30   (présent uniquement si limite atteinte)</pre></div>
            </div>
        </section>

        <!-- ───── ENDPOINTS ───── -->

        <h2 class="mt-5 mb-3">Endpoints</h2>

        <section id="ep-organisations">
            <div class="endpoint-card">
                <div class="d-flex align-items-center mb-3">
                    <span class="method-badge method-get mr-3">GET</span>
                    <span class="path-code">/organisations</span>
                </div>
                <p>Liste paginée des organisations publiques (ayant un numéro de récépissé).</p>
                <strong>Permission requise :</strong> <code>organisations</code>

                <h6 class="mt-3">Paramètres de requête</h6>
                <table class="table table-sm param-table">
                    <thead><tr><th>Paramètre</th><th>Type</th><th>Valeurs</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>search</code></td><td>string</td><td>—</td><td>Recherche sur nom, sigle, ville</td></tr>
                        <tr><td><code>type</code></td><td>string</td><td>association | ong | parti_politique | confession_religieuse</td><td>Filtre par type</td></tr>
                        <tr><td><code>statut</code></td><td>string</td><td>soumis | en_validation | approuve | suspendu</td><td>Filtre par statut</td></tr>
                        <tr><td><code>province</code></td><td>string</td><td>—</td><td>Filtre par province</td></tr>
                        <tr><td><code>per_page</code></td><td>integer</td><td>10–100 (défaut: 20)</td><td>Résultats par page</td></tr>
                        <tr><td><code>page</code></td><td>integer</td><td>défaut: 1</td><td>Numéro de page</td></tr>
                    </tbody>
                </table>

                <strong>Exemple de réponse 200 :</strong>
                <div class="response-block mt-2"><pre>{
  "success": true,
  "data": [
    {
      "id": 42,
      "nom": "Association Citoyens Actifs du Gabon",
      "sigle": "ACAG",
      "type": "association",
      "type_libelle": "Association",
      "statut": "approuve",
      "numero_recepisse": "ASS-2024-000042",
      "province": "Estuaire",
      "ville_commune": "Libreville"
    }
  ],
  "meta": {
    "total": 1284,
    "per_page": 20,
    "current_page": 1,
    "last_page": 65
  },
  "links": {
    "first": "{{ url('/api/v1/public/organisations') }}?page=1",
    "next": "{{ url('/api/v1/public/organisations') }}?page=2"
  }
}</pre></div>
            </div>
        </section>

        <section id="ep-organisation">
            <div class="endpoint-card">
                <div class="d-flex align-items-center mb-3">
                    <span class="method-badge method-get mr-3">GET</span>
                    <span class="path-code">/organisations/{id}</span>
                </div>
                <p>Retourne les informations détaillées d'une organisation, incluant les membres du bureau (sans NIP).</p>
                <strong>Permission requise :</strong> <code>organisations</code>

                <strong class="d-block mt-3">Exemple de réponse 200 :</strong>
                <div class="response-block mt-2"><pre>{
  "success": true,
  "data": {
    "id": 42,
    "nom": "Association Citoyens Actifs du Gabon",
    "sigle": "ACAG",
    "type": "association",
    "statut": "approuve",
    "numero_recepisse": "ASS-2024-000042",
    "province": "Estuaire",
    "ville_commune": "Libreville",
    "objet": "Promotion de la citoyenneté active...",
    "adresse": "BP 1234, Libreville",
    "telephone": "+241 01 23 45 67",
    "email": "contact@acag.ga",
    "date_creation": "2020-03-15",
    "date_recepisse": "2024-01-10",
    "membres_bureau": [
      { "nom": "NDONG", "prenom": "Jean-Pierre", "fonction": "Président" },
      { "nom": "MBOUMBA", "prenom": "Marie", "fonction": "Secrétaire Général" }
    ]
  }
}</pre></div>
            </div>
        </section>

        <section id="ep-verify">
            <div class="endpoint-card">
                <div class="d-flex align-items-center mb-3">
                    <span class="method-badge method-get mr-3">GET</span>
                    <span class="path-code">/organisations/verify/{code}</span>
                </div>
                <p>Vérifie l'authenticité d'un récépissé. Accepte un numéro de récépissé, un code QR ou un ID numérique.</p>
                <strong>Permission requise :</strong> <code>verify</code>

                <h6 class="mt-3">Champ <code>status</code> de la réponse</h6>
                <table class="table table-sm">
                    <tr><td><code>VALID</code></td><td>Récépissé authentique et organisation active</td></tr>
                    <tr><td><code>SUSPENDED</code></td><td>Récépissé authentique mais organisation suspendue</td></tr>
                    <tr><td><code>INVALID</code></td><td>Récépissé émis mais plus en vigueur (radié/rejeté)</td></tr>
                    <tr><td><code>NOT_FOUND</code></td><td>Code introuvable — document potentiellement frauduleux</td></tr>
                </table>

                <strong>Exemple — récépissé valide :</strong>
                <div class="response-block mt-2"><pre>{
  "success": true,
  "verified": true,
  "status": "VALID",
  "message": "Récépissé authentique et valide.",
  "organisation": { <em class="text-muted">/* OrganisationDetail */</em> },
  "verified_at": "2026-03-10T14:32:00+01:00"
}</pre></div>

                <strong class="d-block mt-3">Exemple — code non trouvé :</strong>
                <div class="response-block mt-2"><pre>{
  "success": true,
  "verified": false,
  "status": "NOT_FOUND",
  "message": "Aucun récépissé correspondant à ce code dans la base officielle DGELP.",
  "code": "ASS-XXXX-000000"
}</pre></div>
            </div>
        </section>

        <section id="ep-stats">
            <div class="endpoint-card">
                <div class="d-flex align-items-center mb-3">
                    <span class="method-badge method-get mr-3">GET</span>
                    <span class="path-code">/stats</span>
                </div>
                <p>Statistiques agrégées et anonymisées sur l'annuaire des organisations.</p>
                <strong>Permission requise :</strong> <code>stats</code>

                <strong class="d-block mt-3">Exemple de réponse 200 :</strong>
                <div class="response-block mt-2"><pre>{
  "success": true,
  "data": {
    "total": 3842,
    "par_type": {
      "associations": 2100,
      "ong": 450,
      "partis_politiques": 62,
      "confessions_religieuses": 1230
    },
    "par_statut": {
      "approuve": 3200,
      "en_validation": 412,
      "soumis": 180,
      "suspendu": 50
    },
    "generated_at": "2026-03-10T14:32:00+01:00"
  }
}</pre></div>
            </div>
        </section>

        <footer class="mt-5 pt-4 border-top text-muted small">
            <p>DGELP — Système de Gestion des Libertés Publiques &copy; {{ date('Y') }} Ministère de l'Intérieur, Gabon.</p>
            <p>Données officielles — Usage soumis aux conditions d'accès. <a href="{{ url('/contact') }}">Contact</a>.</p>
        </footer>
    </div>
</div>
</body>
</html>
