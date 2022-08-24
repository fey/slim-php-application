<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$container = require __DIR__ . '/container.php';

// Create Slim App instance
$app = $container->get('app');

// Register routes
$registerRoutes = require __DIR__ . '/routes.php';
$registerRoutes($app);

return $app;
