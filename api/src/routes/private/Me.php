<?php
$app->group('/me', function () use ($app) {

    $app->get('[/]', function ($request, $response, $args) use ($app) {

        try {

            App\Utils\UserSession::checkUserAuthenticated($app);

            $userEntity = new App\Entity\Usuario();

            $user = $userEntity->get($app->jwtHash->data->userId, $userEntity->default_fields);

            return App\Utils\Functions::responseJSON($response, array(
                'code'   => 200,
                'result' => $user
            ));

        } catch (Exception $ex) {

            return App\Utils\Functions::exceptionJSON($response, $ex);

        }

    });
});

$app->delete('/endsession', function ($request, $response) use ($app) {

    try {

        App\Utils\UserSession::checkUserAuthenticated($app);

        setcookie("stoken", "", time() - 3600);

    } catch (Exception $ex) {

        return App\Utils\Functions::exceptionJSON($response, $ex);

    }

});