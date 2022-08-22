<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

return [
    'app' => function (ContainerInterface $c): App {
        $app = AppFactory::createFromContainer($c);

        // $app->add(
        //     function ($request, $next) {
        //         // Start PHP session
        //         if (session_status() !== PHP_SESSION_ACTIVE) {
        //             session_start();
        //         }

        //         return $next->handle($request);
        //     }
        // );

        $app->addErrorMiddleware(true, true, true);

        return $app;
    }
];
