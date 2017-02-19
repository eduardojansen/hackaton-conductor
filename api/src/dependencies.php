<?php

$container = $app->getContainer();

$container['app'] = function ($container) use ($app) {
    return $app;
};

$container['user_id'] = function ($container) use ($app) {
    if ($app->jwtHash->data->userId)
        return $app->jwtHash->data->userId;
    return null;
};

$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

$container['cookie'] = function ($container) {
    $request = $container->get('request');
    return new \Slim\Http\Cookies($request->getCookieParams());
};

$container["jwt"] = function ($container) {
    return new \Firebase\JWT\JWT;
};

$container["jwtHash"] = function ($container) {
    return array();
};

if (APPLICATION_ENV == 'development') {
    ORM::configure('logging', true);
}


ORM::configure('mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE);
ORM::configure('username', DB_USER);
ORM::configure('password', DB_PASSWORD);
ORM::configure('return_result_sets', true);
