<?php

// config/l5-swagger.php
return [
    'documentations' => [
        'default' => [
            // ...
            'routes' => [
                // UI page → http://localhost:8000/api/documentation
                'api'   => 'api/documentation',

                // JSON → http://localhost:8000/api/docs
                'docs'  => 'api/docs',

                // keep this empty or add your own
                'oauth2_callback' => 'api/oauth2-callback',

                // you can protect these later if you want
                'middleware' => [
                    'api'  => [],   // or ['web']
                    'docs' => [],   // or ['web']
                    'assets' => [],
                    'oauth2_callback' => [],
                ],

                // no prefix so the paths above are exact
                'prefix' => '',
            ],

            // where to write the file (Windows-safe)
            'paths' => [
                'docs' => storage_path('api-docs'),
                'docs_json' => 'openapi.json',
                'docs_yaml' => false,
                'format_to_use_for_docs' => 'json',
                'annotations' => [
                    base_path('app/Http/Controllers'),
                    base_path('app/OpenApi'),
                    base_path('app/Models'),
                ],
            ],
        ],
    ],

    // constant we used in @OA\Server
    'constants' => [
        'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', env('APP_URL', 'http://localhost:8000')),
    ],
];
