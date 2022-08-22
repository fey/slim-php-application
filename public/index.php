<?php

use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$container = require dirname(__DIR__) . '/container.php';

$app = AppFactory::createFromContainer($container);


$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    $db = $this->get('db');
    $users = $db->table('users')->select()->get();
    $response->getBody()->write($users->toJson());

    return $response;
})->setName('root');

$app->get('/users', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    /** @var \Illuminate\Database\Capsule\Manager $db  */
    $db = $this->get('db');
    $users = $db->table('users')->select()->get();
    $response->getBody()->write($users->toJson());

    return $response;
})->setName('users.index');

$app->run();
