<?php

use Psr\Container\ContainerInterface;
use Slim\Views\Twig;

return [
    'db' => function (ContainerInterface $c) {
        $capsule = new Illuminate\Database\Capsule\Manager();
        $connection = $c->get('db.connection');
        $capsule->addConnection($connection);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    },
    'view' => function () {
        return Twig::create(dirname(__DIR__) . '/templates');
    }
];
