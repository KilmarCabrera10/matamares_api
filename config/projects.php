<?php

return [
    'default' => env('DEFAULT_PROJECT', 'matamares'),
    
    'projects' => [
        'matamares' => [
            'name' => 'Matamares POS',
            'database_connection' => 'matamares',
            'namespace' => 'App\\Projects\\Matamares',
            'routes_file' => app_path('Projects/Matamares/routes.php'),
            'migrations_path' => database_path('matamares/migrations'),
            'seeders_path' => database_path('matamares/seeders'),
        ],
        'inventario' => [
            'name' => 'Sistema de Inventario',
            'database_connection' => 'inventario',
            'namespace' => 'App\\Projects\\Inventario',
            'routes_file' => app_path('Projects/Inventario/routes.php'),
            'migrations_path' => database_path('inventario/migrations'),
            'seeders_path' => database_path('inventario/seeders'),
        ],
    ],
];