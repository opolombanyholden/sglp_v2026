<?php

/**
 * Spécification OpenAPI 3.0.3 — SGLP API V1
 * Générée dynamiquement pour refléter l'URL de l'application.
 */

$appUrl = config('app.url');

return [
    'openapi' => '3.0.3',
    'info' => [
        'title'       => 'SGLP — API Interopérabilité V1',
        'description' => "API publique du Système de Gestion des Libertés Publiques (SGLP) du Gabon.\n\n"
            . "Permet aux systèmes tiers d'accéder à l'annuaire des organisations, de vérifier l'authenticité "
            . "des récépissés et de consulter les statistiques agrégées.\n\n"
            . "**Authentification** : Bearer token dans le header `Authorization`.\n"
            . "Contactez l'administrateur SGLP pour obtenir un token d'accès.",
        'version'     => '1.0.0',
        'contact'     => [
            'name'  => 'SGLP — Ministère de l\'Intérieur',
            'email' => 'api@sglp.ga',
        ],
        'license' => [
            'name' => 'Usage restreint — données officielles du Gouvernement gabonais',
        ],
    ],
    'servers' => [
        ['url' => $appUrl . '/api/v1/public', 'description' => 'Serveur principal'],
    ],
    'security' => [
        [['BearerAuth' => []]],
    ],
    'components' => [
        'securitySchemes' => [
            'BearerAuth' => [
                'type'         => 'http',
                'scheme'       => 'bearer',
                'bearerFormat' => 'API Token',
                'description'  => 'Token obtenu auprès de l\'administrateur SGLP.',
            ],
        ],
        'schemas' => [
            'OrganisationSummary' => [
                'type' => 'object',
                'properties' => [
                    'id'               => ['type' => 'integer'],
                    'nom'              => ['type' => 'string'],
                    'sigle'            => ['type' => 'string', 'nullable' => true],
                    'type'             => ['type' => 'string', 'enum' => ['association', 'ong', 'parti_politique', 'confession_religieuse']],
                    'type_libelle'     => ['type' => 'string'],
                    'statut'           => ['type' => 'string', 'enum' => ['soumis', 'en_validation', 'approuve', 'suspendu']],
                    'numero_recepisse' => ['type' => 'string'],
                    'province'         => ['type' => 'string', 'nullable' => true],
                    'ville_commune'    => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'OrganisationDetail' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/OrganisationSummary'],
                    [
                        'type' => 'object',
                        'properties' => [
                            'objet'          => ['type' => 'string', 'nullable' => true],
                            'adresse'        => ['type' => 'string', 'nullable' => true],
                            'telephone'      => ['type' => 'string', 'nullable' => true],
                            'email'          => ['type' => 'string', 'nullable' => true],
                            'date_creation'  => ['type' => 'string', 'format' => 'date', 'nullable' => true],
                            'date_recepisse' => ['type' => 'string', 'format' => 'date', 'nullable' => true],
                            'membres_bureau' => [
                                'type'  => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'nom'      => ['type' => 'string'],
                                        'prenom'   => ['type' => 'string'],
                                        'fonction' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'error'   => ['type' => 'string', 'example' => 'UNAUTHORIZED'],
                    'message' => ['type' => 'string'],
                ],
            ],
        ],
    ],
    'paths' => [
        '/organisations' => [
            'get' => [
                'summary'     => 'Liste des organisations',
                'description' => 'Retourne la liste paginée des organisations publiques (récépissé émis).',
                'operationId' => 'listOrganisations',
                'tags'        => ['Organisations'],
                'parameters'  => [
                    ['name' => 'search',   'in' => 'query', 'schema' => ['type' => 'string'],  'description' => 'Recherche textuelle'],
                    ['name' => 'type',     'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['association', 'ong', 'parti_politique', 'confession_religieuse']]],
                    ['name' => 'statut',   'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['soumis', 'en_validation', 'approuve', 'suspendu']]],
                    ['name' => 'province', 'in' => 'query', 'schema' => ['type' => 'string']],
                    ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 10, 'maximum' => 100, 'default' => 20]],
                    ['name' => 'page',     'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Liste paginée',
                        'content' => ['application/json' => ['schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => ['type' => 'boolean'],
                                'data'    => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/OrganisationSummary']],
                                'meta'    => ['type' => 'object'],
                                'links'   => ['type' => 'object'],
                            ],
                        ]]],
                    ],
                    '401' => ['description' => 'Non authentifié', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]]],
                ],
            ],
        ],
        '/organisations/{id}' => [
            'get' => [
                'summary'     => 'Détail d\'une organisation',
                'operationId' => 'getOrganisation',
                'tags'        => ['Organisations'],
                'parameters'  => [
                    ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                ],
                'responses' => [
                    '200' => ['description' => 'Détail organisation', 'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => ['success' => ['type' => 'boolean'], 'data' => ['$ref' => '#/components/schemas/OrganisationDetail']]]]]],
                    '404' => ['description' => 'Non trouvée'],
                    '401' => ['description' => 'Non authentifié'],
                ],
            ],
        ],
        '/organisations/verify/{code}' => [
            'get' => [
                'summary'     => 'Vérifier un récépissé',
                'description' => "Vérifie l'authenticité d'un récépissé via son numéro, un code QR ou un ID numérique.",
                'operationId' => 'verifyRecepisse',
                'tags'        => ['Vérification'],
                'parameters'  => [
                    ['name' => 'code', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string'], 'description' => 'Numéro de récépissé, code QR ou ID numérique'],
                ],
                'responses' => [
                    '200' => ['description' => 'Résultat de vérification (verified true/false)'],
                    '401' => ['description' => 'Non authentifié'],
                    '422' => ['description' => 'Code invalide'],
                ],
            ],
        ],
        '/stats' => [
            'get' => [
                'summary'     => 'Statistiques agrégées',
                'description' => 'Retourne les compteurs d\'organisations par type et par statut.',
                'operationId' => 'getStats',
                'tags'        => ['Statistiques'],
                'responses'   => [
                    '200' => ['description' => 'Statistiques'],
                    '401' => ['description' => 'Non authentifié'],
                ],
            ],
        ],
    ],
    'tags' => [
        ['name' => 'Organisations', 'description' => 'Accès à l\'annuaire des organisations'],
        ['name' => 'Vérification',  'description' => 'Vérification d\'authenticité des récépissés'],
        ['name' => 'Statistiques',  'description' => 'Données agrégées anonymisées'],
    ],
];
