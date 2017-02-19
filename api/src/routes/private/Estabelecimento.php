<?php
$app->group('/estabelecimentos', function () use ($app) {

    $controller = new App\Controller\EstabelecimentoController($app, \App\Entity\Estabelecimento::class);

    $app->get('[/]', $controller('index'));
    $app->post('/categorias', $controller('categorias'));
    $app->post('[/]', $controller('post'));
    $app->get('/{id:[0-9]+}', $controller('show'));
    $app->put('/{id:[0-9]+}', $controller('edit'));
    $app->delete('/{id:[0-9]+}', $controller('delete'));

});