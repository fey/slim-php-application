<?php

use Illuminate\Support\Arr;
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
        $formData = $request->getParsedBody();
        $validator = new Valitron\Validator($formData);

        $validator->rule('required', 'user')->rule('array', 'user');
        $validator->rule('required', ['user.first_name', 'user.last_name', 'user.email']);
        $validator->rule('alpha', ['user.first_name', 'user.last_name']);
        $validator->rule('email', 'user.email');
        $validator->labels(['user.first_name' => 'First name', 'user.last_name' => 'Last name', 'user.email' => 'Email']);

        if (!$validator->validate()) {
            $errors = $validator->errors();

            return $this->get('view')->render($response, 'users/new.twig', [
                'form' => $formData,
                'errors' => $errors,
            ]);
        }

        ['user' => $userFormData] = $formData;

        /** @var \Illuminate\Database\Capsule\Manager $db  */
        $db = $this->get('db');

        $data = [
            'first_name' => Arr::get($userFormData, 'first_name'),
            'last_name' => Arr::get($userFormData, 'last_name'),
            'email' => Arr::get($userFormData, 'email'),
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ];

        $user = $db->table('users')->where('email', Arr::get($data, 'email'))->first();

        if ($user) {
            $this->get('flash')->addMessage('info', 'Пользователь уже существует');
        } else {
            $db->table('users')->insert($data);
            $this->get('flash')->addMessage('success', 'Пользователь успешно создан');
        }
        $redirectUrl = $this->get('app')->getRouteCollector()->getRouteParser()->urlFor('users.index');

        return $response->withRedirect($redirectUrl);
    })->setName('users.store');

    $app->get('/users/new', function (ServerRequest $request, Response $response): Response {
        return $this->get('view')->render($response, 'users/new.twig', [
            'form' => [],
            'errors' => []
        ]);
    })->setName('users.new');
};
