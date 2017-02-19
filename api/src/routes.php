<?php

/*Carregamento automatico das rotas publicas*/
$routesPrivateDir = dirname(__FILE__) . '/routes/public/';
foreach (scandir($routesPrivateDir) as $filename) {
    $path = $routesPrivateDir . $filename;
    if (is_file($path)) {
        require_once $path;
    }
}

$app->group("/rest", function () use ($app) {

    $app->get('/niveis', function ($request, $response) use ($app) {
        try {

            App\Utils\UserSession::checkUserAuthenticated($app);

            $nivelEntity = new App\Entity\Nivel($app->jwtHash->data->userId);

            $result = $nivelEntity->getNotEspecified();

            return App\Utils\Functions::responseJSON($response, array(
                'code' => 200,
                'result' => $result
            ));
        } catch (Exception $ex) {

            return App\Utils\Functions::exceptionJSON($response, $ex);
        }
    });

    $app->get('/mcc', function ($request, $response) use ($app) {

        try {

            App\Utils\UserSession::checkUserAuthenticated($app);

            $mccEntity = new App\Entity\Mcc($app->jwtHash->data->userId);

            $result = $mccEntity->getAll();

            return App\Utils\Functions::responseJSON($response, array(
                'code' => 200,
                'result' => $result
            ));
        } catch (Exception $ex) {

            return App\Utils\Functions::exceptionJSON($response, $ex);
        }
    });

    $app->get('/teste-email/{email}/{template}', function ($request, $response, $args) use ($app) {

        try {
            $email = $args['email'];
            $template = $args['template'];
            $return = App\Utils\NotificationCenter::sendTestMail($email, $template);

            if (!$return) {
                return App\Utils\Functions::responseJSON($response, array(
                    'code' => 400,
                    'result' => 'Erro ao enviar e-mail. Sem retorno!'
                ));
            }

            return App\Utils\Functions::responseJSON($response, array(
                'code' => 200,
                'result' => $return
            ));
        } catch (Exception $ex) {

            return App\Utils\Functions::exceptionJSON($response, $ex);
        }
    });

    /*Carregamento automatico das rotas privadas*/
    $routesPrivateDir = dirname(__FILE__) . '/routes/private/';
    foreach (scandir($routesPrivateDir) as $filename) {
        $path = $routesPrivateDir . $filename;
        if (is_file($path)) {
            require_once $path;
        }
    }
});



