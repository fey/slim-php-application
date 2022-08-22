<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$container = require __DIR__ . '/container.php';

// Create Slim App instance
$app = $container->get('app');

// Register routes
$registerRoutes = require __DIR__ . '/routes.php';
$registerRoutes($app);

// Register middleware
$registerMiddlewares = require __DIR__ . '/middlewares.php';
$registerMiddlewares($app);

return $app;
