<?php

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;
use Twig\Extra\Html\HtmlExtension;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$containerBuilder = new ContainerBuilder();

// Add DI container definitions
$containerBuilder->addDefinitions([
    'app.debug' => env('APP_DEBUG', false),
    'app.env' => env('APP_ENV', 'development'),
    'db.driver' => 'pgsql',
    'db.host' => fn() => parse_url(env('DATABASE_URL', ''), PHP_URL_HOST) ?: env('DB_HOST'),
    'db.port' => fn() => parse_url(env('DATABASE_URL', ''), PHP_URL_PORT) ?: env('DB_PORT', 5432),
    'db.database' => fn() => trim(parse_url(env('DATABASE_URL', ''), PHP_URL_PATH), '/') ?: env('DB_DATABASE'),
    'db.username' => fn() => parse_url(env('DATABASE_URL', ''), PHP_URL_USER) ?: env('DB_USERNAME'),
    'db.password' => fn() => parse_url(env('DATABASE_URL', ''), PHP_URL_PASS) ?: env('DB_PASSWORD'),
    'db.schema' => env('DB_SCHEMA', 'public'),
    'db.connection' => function (ContainerInterface $c): array {
        return [
            'driver' => $c->get('db.driver'),
            'database' => $c->get('db.database'),
            'username' => $c->get('db.username'),
            'password' => $c->get('db.password'),
            'port' => $c->get('db.port'),
            'host' => $c->get('db.host'),
        ];
    },
    'phoenix.config' => fn(ContainerInterface $c) => [
        'migration_dirs' => [
            __DIR__ . '/../database/migrations',
        ],
        'environments' => [
            'pgsql' => [
                'adapter' => 'pgsql',
                'db_name' => $c->get('db.database'),
                'host' => $c->get('db.host'),
                'username' => $c->get('db.username'),
                'password' => $c->get('db.password'),
                'port' => $c->get('db.port'),
            ],
        ],
        'default_environment' => 'pgsql',
        'log_table_name' => 'phoenix_log',
    ],
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
