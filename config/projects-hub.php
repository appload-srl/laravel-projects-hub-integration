<?php

return [
    'enabled' => env('PROJECTS_HUB_ENABLED', true),

    'openapi_docs_path' => env(
        'PROJECTS_HUB_OPENAPI_DOCS_PATH',
        storage_path('api-docs/api-docs.json')
    ),

    'route' => [
        'prefix' => env('PROJECTS_HUB_ROUTE_PREFIX', 'api/projects-hub'),
        'middleware' => ['api'],
        'auth' => [
            'enabled' => env('PROJECTS_HUB_AUTH_ENABLED', false),
            'x-api-key' => env('PROJECTS_HUB_AUTH_X_API_KEY'),
        ],
    ],
];
