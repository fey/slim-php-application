<?php

use Psr\Container\ContainerInterface;
use Illuminate\Support\Str;

return [
    'config.app.debug' => env('APP_DEBUG', false),
    'config.app.env' => env('APP_ENV', 'development'),
    'config.db.driver' => 'pgsql',
    'config.db.host' => fn() => parse_url(env('DATABASE_URL', ''), PHP_URL_HOST) ?: env('DB_HOST'),
    'config.db.port' => fn() => parse_url(env('DATABASE_URL', ''), PHP_URL_PORT) ?: env('DB_PORT', 5432),
    'config.db.database' => fn() => trim(parse_url(env('DATABASE_URL', ''), PHP_URL_PATH), '/') ?: env('DB_DATABASE'),
    'config.db.username' => fn() => parse_url(env('DATABASE_URL', ''), PHP_URL_USER) ?: env('DB_USERNAME'),
    'config.db.password' => fn() => parse_url(env('DATABASE_URL', ''), PHP_URL_PASS) ?: env('DB_PASSWORD'),
    'config.db.schema' => env('DB_SCHEMA', 'public'),
    'config.db.migrations-db' => function (ContainerInterface $c) {
        return [
            'dbname' => $c->get('config.db.database'),
            'user' => $c->get('config.db.username'),
            'password' => $c->get('config.db.password'),
            'host' => $c->get('config.db.host'),
            'driver' => 'pdo_pgsql',
            'port' => $c->get('config.db.port')
        ];
    },
    'config.db.connection' => function ($c): array {
        return [
            'driver' => $c->get('config.db.driver'),
            'database' => $c->get('config.db.database'),
            'username' => $c->get('config.db.username'),
            'password' => $c->get('config.db.password'),
            'port' => $c->get('config.db.port'),
            'host' => $c->get('config.db.host'),
        ];
    },
    'config.rollbar' => function (ContainerInterface $c): array {
        return [
            'environment' => $c->get('config.app.env'),
            'access_token' => env('ROLLBAR_TOKEN', Str::random(32)),
            'root' => dirname(__DIR__),
        ];
    },
];
