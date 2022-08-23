<?php

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Arr;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;
use Twig\Extra\Html\HtmlExtension;

$containerBuilder = new ContainerBuilder();

// Add DI container definitions
$containerBuilder->addDefinitions([
    'app.debug' => env('APP_DEBUG', false),
    'app.env' => env('APP_ENV', 'development'),
    'db.driver' => 'pgsql',
    'db.url' => env('DATABASE_URL'),
    'db.host' => env('DB_HOST'),
    'db.port' => env('DB_PORT', 54320),
    'db.database' => env('DB_DATABASE'),
    'db.username' => env('DB_USERNAME'),
    'db.password' => env('DB_PASSWORD'),
    'db.schema' => env('DB_SCHEMA'),
    'db.connection' => function (ContainerInterface $c): array {
        $url = $c->get('db.url');

        if ($url) {
            $database = trim(parse_url($url, PHP_URL_PATH), '/');
            $username = parse_url($url, PHP_URL_USER);
            $password = parse_url($url, PHP_URL_PASS);
            $port = parse_url($url, PHP_URL_PORT);
            $host = parse_url($url, PHP_URL_HOST);
        } else {
            $database = $c->get('db.database');
            $username = $c->get('db.username');
            $password = $c->get('db.password');
            $port = $c->get('db.port');
            $host = $c->get('db.host');
        }

        return [
            'driver' => $c->get('db.driver'),
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'port' => $port,
            'host' => $host,
        ];
    },
    'db' => function (ContainerInterface $c): Manager {
        $capsule = new Illuminate\Database\Capsule\Manager();
        $connection = $c->get('db.connection');
        $capsule->addConnection($connection);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    },
    'view' => function (ContainerInterface $c): Twig {
        $twig = Twig::create(dirname(__DIR__) . '/templates', ['cache' => false]);
        $twig->addExtension(new HtmlExtension());
        $twig->addExtension(new DebugExtension());
        $twig->getEnvironment()->addGlobal('flash', $c->get('flash')->getMessages());

        if ($c->get('app.debug')) {
            $twig->getEnvironment()->enableDebug();
        }

        return $twig;
    },
    'flash' => function (): Messages {
        return new Messages();
    },
    'app' => function (ContainerInterface $c): App {
        $app = AppFactory::createFromContainer($c);

        return $app;
    }
]);

// Create DI container instance
$container = $containerBuilder->build();

return $container;
