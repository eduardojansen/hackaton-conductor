<?php
$app->group('/contas', function () use ($app) {

    $controller = new App\Controller\ContaController($app, \App\Entity\Conta::class);

    $app->get('[/]', $controller('index'));
    $app->post('[/]', $controller('post'));
    $app->get('/{id:[0-9]+}', $controller('show'));
    $app->put('/{id:[0-9]+}', $controller('edit'));
    $app->delete('/{id:[0-9]+}', $controller('delete'));

});