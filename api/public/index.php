<?php

function pr($array, $die = false)
{
    echo '<pre>';
    print_r($array);
    echo '<pre>';
    if ($die)
        die;
}

function vd($array, $die = false)
{
    echo '<pre>';
    var_dump($array);
    echo '<pre>';
    if ($die)
        die;
}

require __DIR__ . '/../src/config.php';

$loader = require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
