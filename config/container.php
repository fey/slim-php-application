<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;

$containerBuilder = new ContainerBuilder();

// Add DI container definitions
$containerBuilder->addDefinitions([
    'app.env' => env('APP_ENV', 'development'),
    'db.driver' => 'pgsql',
    'db.url' => env('DATABASE_URL'),
    'db.host' => env('DB_HOST'),
    'db.port' => env('DB_PORT', 54320),
    'db.database' => env('DB_DATABASE'),
    'db.username' => env('DB_USERNAME'),
    'db.password' => env('DB_PASSWORD'),
    'db.schema' => env('DB_SCHEMA'),
    'db.connection' => function (ContainerInterface $c) {
        $url = $c->get('db.url');

        return $url ? ['url' => $url] : [
            'driver' => $c->get('db.driver'),
            'database' => $c->get('db.database'),
            'username' => $c->get('db.username'),
            'password' => $c->get('db.password'),
            'port' => $c->get('db.port'),
            'host' => $c->get('db.host'),
        ];
    },
    'db' => function (ContainerInterface $c) {
        $capsule = new Illuminate\Database\Capsule\Manager();
        $connection = $c->get('db.connection');
        $capsule->addConnection($connection);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    },
    'view' => function () {
        return Twig::create(dirname(__DIR__) . '/templates', ['cache' => false]);
    },
    'app' => function (ContainerInterface $c): App {
        $app = AppFactory::createFromContainer($c);

        return $app;
    }
]);

// Create DI container instance
$container = $containerBuilder->build();

return $container;
