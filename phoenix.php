<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/config/container.php';

return [
    'migration_dirs' => [
        __DIR__ . '/database/migrations',
    ],
    'environments' => [
        'pgsql' => [
            'adapter' => 'pgsql',
            'db_name' => $container->get('db.database'),
            'host' => $container->get('db.host'),
            'username' => $container->get('db.username'),
            'password' => $container->get('db.password'),
            'port' => $container->get('db.port'),
        ],
    ],
    'default_environment' => 'pgsql',
    'log_table_name' => 'phoenix_log',
];
