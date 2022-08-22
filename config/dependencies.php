<?php

use Psr\Container\ContainerInterface;

return [
    'db' => function (ContainerInterface $c) {
        $capsule = new Illuminate\Database\Capsule\Manager();
        $connection = $c->get('db.connection');
        $capsule->addConnection($connection);

        return $capsule;
    },
];
