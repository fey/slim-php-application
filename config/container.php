<?php

use DI\ContainerBuilder;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use Monolog\Handler\RollbarHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Rollbar\Rollbar;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\DebugExtension;
use Twig\Extra\Html\HtmlExtension;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$containerBuilder = new ContainerBuilder();

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
            'fetch'     => \PDO::FETCH_ASSOC
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
        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->bootEloquent();

        if ($c->get('app.debug')) {
            $capsule->connection()->listen(function ($query) use ($c) {
                $c->get('logger')->debug($query->sql);
            });
        }

        return $capsule;
    },
    'view' => function (ContainerInterface $c): Twig {
        $twig = Twig::create(dirname(__DIR__) . '/templates', ['cache' => false]);

        $componentsRegistry = new \RedAnt\TwigComponents\Registry($twig->getEnvironment());
        $componentsRegistry->addComponent('nav_link', 'components/nav_link.twig');

        $twig->addExtension(new HtmlExtension());
        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new \RedAnt\TwigComponents\Extension($componentsRegistry));

        $twig->getEnvironment()->addGlobal('flash', $c->get('flash')->getMessages());

        if ($c->get('app.debug')) {
            $twig->getEnvironment()->enableDebug();
        }

        return $twig;
    },
    'flash' => function (): Messages {
        return new Messages();
    },
    'rollbar.config' => function (ContainerInterface $c): array {
        return [
            'access_token' => env('ROLLBAR_TOKEN', Str::random(32)),
            'environment' => $c->get('app.env'),
            'root' => dirname(__DIR__),
        ];
    },
    'logger' => function (ContainerInterface $c): LoggerInterface {
        $logger = new Logger('name');
        $logLevel = $c->get('app.debug') ? LogLevel::DEBUG : LogLevel::WARNING;

        Rollbar::init($c->get('rollbar.config'));
        $logger->pushHandler(new StreamHandler('php://stdout', $logLevel));
        $logger->pushHandler(new RollbarHandler(Rollbar::logger()), $logLevel);
        return $logger;
    },
    'app' => function (ContainerInterface $c): App {
        $app = AppFactory::createFromContainer($c);

    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

    $app->add(function (Request $request, RequestHandler $handler): ResponseInterface {
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->get('logger');
        $logger->info('Request', [
            'path' => $request->getUri()->getPath(),
            'query_params' => $request->getQueryParams(),
            'body' => $request->getParsedBody()
        ]);

        $response = $handler->handle($request);

        $logger->info('Response', [
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    });

    $app->add(MethodOverrideMiddleware::class);

    // Handle exceptions
    $app->addErrorMiddleware(true, true, true, $c->get('logger'));

    $app->add(TwigMiddleware::createFromContainer($app));

        return $app;
    }
]);

// Create DI container instance
$container = $containerBuilder->build();

return $container;
