<?php

/** @var \Psr\Container\ContainerInterface $container */

$container = require __DIR__ . '/config/container.php';

return $container->get('config.db.migrations-db');
