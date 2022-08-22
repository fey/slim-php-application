<?php

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$app = AppFactory::createFromContainer($container);

$app->run();
