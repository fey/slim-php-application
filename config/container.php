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

$containerBuilder->addDefinitions(require __DIR__ . '/config.php');
$containerBuilder->addDefinitions([
    'app' => function (ContainerInterface $c): App {
        $app = AppFactory::createFromContainer($c);

        $app->addBodyParsingMiddleware();
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
        $app->addErrorMiddleware(true, true, true, $c->get('logger'));
        $app->add(TwigMiddleware::createFromContainer($app));

        return $app;
    },
    'db' => function (ContainerInterface $c): Manager {
        $capsule = new Illuminate\Database\Capsule\Manager();
        $connection = $c->get('config.db.connection');
        $capsule->addConnection($connection);
        $capsule->setAsGlobal();
        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->bootEloquent();

        if ($c->get('config.app.debug')) {
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

        if ($c->get('config.app.debug')) {
            $twig->getEnvironment()->enableDebug();
        }

        return $twig;
    },
    'flash' => function (): Messages {
        return new Messages();
    },
    'logger' => function (ContainerInterface $c): LoggerInterface {
        $logger = new Logger('name');
        $logLevel = $c->get('config.app.debug')
            ? LogLevel::DEBUG
            : LogLevel::WARNING;

        Rollbar::init($c->get('config.rollbar'));
        $logger->pushHandler(new StreamHandler('php://stdout', $logLevel));
        $logger->pushHandler(new RollbarHandler(Rollbar::logger()), $logLevel);
        return $logger;
    },
]);

$container = $containerBuilder->build();

return $container;
