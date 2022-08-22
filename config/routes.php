<?php

use Slim\App;
use Slim\Http\ServerRequest;
use Slim\Http\Response;

return function (App $app) {
    $app->get('/', function (ServerRequest $request, Response $response): Response {
        $response = $this->get('view')->render($response, 'root/index.twig');

        return $response;
    })->setName('root');

    $app->get('/users', function (ServerRequest $request, Response $response): Response {
        /** @var \Illuminate\Database\Capsule\Manager $db  */
        $db = $this->get('db');
        $users = $db->table('users')->select()->get();

        return $this->get('view')->render($response, 'users/index.twig', ['users' => $users]);
    })->setName('users.index');
    $app->post('/users', function (ServerRequest $request, Response $response): Response {
        $userData = $request->getParsedBodyParam('user');

        /** @var \Illuminate\Database\Capsule\Manager $db  */
        $db = $this->get('db');
        $users = $db->table('users')->select()->get();

        $db->table('users')->insert([
            'email' => 'test@test',
            'password_digest' => random_int(1, 1000),
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ]);

        return $response->withRedirect('/');
    });
};
