<?php

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/config/container.php';

return [
    'dbname' => $container->get('db.database'),
    'user' => $container->get('db.username'),
    'password' => $container->get('db.password'),
    'host' => $container->get('db.host'),
    'driver' => 'pdo_pgsql',
    'port' => $container->get('db.port')
];
