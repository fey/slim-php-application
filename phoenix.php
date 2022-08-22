<?php

return [
    'migration_dirs' => [
        __DIR__ . '/database/migrations',
    ],
    'environments' => [
        'local' => [
            'adapter' => 'pgsql',
            'db_name' => 'postgres',
            'host' => 'localhost',
            'username' => 'postgres',
            'password' => '123',
            'charset' => 'utf8',
            'port' => 54320
        ],
        'production' => [
            'adapter' => 'pgsql',
            'db_name' => 'phoenix',
            'host' => 'localhost',
            'username' => 'postgres',
            'password' => '123',
            'charset' => 'utf8',
        ],
    ],
    'default_environment' => 'local',
    'log_table_name' => 'phoenix_log',
];
