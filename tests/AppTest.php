<?php

namespace Feycot\PageAnalyzer\Tests;

use Illuminate\Database\Capsule\Manager as Database;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Http\ServerRequest;
use Slim\Psr7\Factory\ServerRequestFactory;

class AppTest extends TestCase
{
    protected App $app;
    protected Database $db;

    protected function setUp(): void
    {

        $this->app = require dirname(__DIR__) . '/config/bootstrap.php';
        $container =  $this->app->getContainer();
        $this->db = $container->get('db');

        $this->db->connection()->beginTransaction();
    }

    public function testRootIndex(): void
    {
        $response = $this->get('/');

        self::assertOk($response);
    }

    public function testUsersIndex(): void
    {
        $response = $this->get('/users');

        self::assertOk($response);
    }

    public function testUsersNew(): void
    {
        $response = $this->get('/users/new');

        self::assertOk($response);
    }

    public function testUsersStore(): void
    {
        $faker = \Faker\Factory::create();

        $userData = [
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'email' => $faker->email(),
        ];
        $response = $this->post('/users', [
            'user' => $userData,
        ]);

        self::assertRedirected($response);

        $actual = (array)$this->db->table('users')->where($userData)->first();


        self::assertNotNull($actual);
    }

    protected function request(string $method, string $path): ServerRequestInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest($method, $path);

        return new ServerRequest($request);
    }

    protected function post($path, $data = [])
    {
        $request = self::request('POST', $path)
        ->withHeader('Accept', 'text/html')
        ->withHeader('Content-Type', 'multipart/form-data')
        ->withParsedBody($data);

        return $this->app->handle($request);
    }

    protected function get($path)
    {
        return $this->app->handle($this->request('GET', $path));
    }

    protected function tearDown(): void
    {
        $this->db->connection()->rollBack();
    }

    protected static function assertOk($response): void
    {
        self::assertEquals(200, $response->getStatusCode());
    }

    protected static function assertRedirected($response): void
    {
        self::assertEquals(302, $response->getStatusCode());
    }
}
