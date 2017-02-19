<?php
$app->group('/usuarios', function () use ($app) {

    $controller = new App\Controller\UsuarioController($app, \App\Entity\Usuario::class);

    $app->get('[/]', $controller('index'));
    $app->get('/minhas-transacoes', $controller('importarTransacoes'));
    $app->post('/sugestoes', $controller('sugestoes'));
    $app->post('[/]', $controller('post'));
    $app->get('/{id:[0-9]+}', $controller('show'));
    $app->put('/{id:[0-9]+}', $controller('edit'));
    $app->delete('/{id:[0-9]+}', $controller('delete'));


});