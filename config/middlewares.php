<?php

use Slim\App;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

    $app->add(MethodOverrideMiddleware::class);

    // Handle exceptions
    $app->addErrorMiddleware(true, true, true);

    $app->add(TwigMiddleware::createFromContainer($app));
};
