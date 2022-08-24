<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$app = require dirname(__DIR__) . '/config/bootstrap.php';

$app->run();
