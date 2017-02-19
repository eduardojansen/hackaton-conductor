<?php

$app->get('/', function ($request, $response) use ($app) {
    echo time();
});

$app->post('/authenticate', function ($request, $response, $args) use ($app) {

    $params = $request->getParsedBody();

    $validator = new App\Utils\Validator();

    try {

        $validator->checkRequiredFields(array('token', 'app_id'), $params);

        $auth = new App\Utils\Auth($app);
        $user = $auth->authenticate($params);

        //Cadastra novo usuário
        if (!$user) {
            $newUser = new App\Entity\Usuario('nolog');
            $idNewUser = $newUser->save($params);
        }


        $codUser = $user->codigo ? $user->codigo : $idNewUser;
        $token = App\Utils\UserSession::generateToken($codUser, $app);
        setcookie("stoken", $token, (time() + 3600) * 24);

        $response = $response->withHeader('Token', $token);
    } catch (Exception $ex) {

        return App\Utils\Functions::exceptionJSON($response, $ex);
    }

    return $response;
});

/* Envio do e-mail com o hash para mudança da senha */
$app->map(['POST', 'PUT'], '/reset-password', function ($request, $response, $args) use ($app) {

    $params = $request->getParsedBody();

    $validator = new App\Utils\Validator();

    try {

        $requiredFields = array('email');

        if ($request->isPut()) {
            $requiredFields[] = 'password';
        }

        $validator->checkRequiredFields($requiredFields, $params);

        $usuarioEntity = new App\Entity\Usuario();

        $user = $usuarioEntity->getByEmail($params['email']);

        $expireDate = date('Y-m-d H:i:s', time() + 3600);
        $arrayDados = array(
            'user'        => $user->codigo,
            'expire_date' => $expireDate
        );

        $dados = serialize($arrayDados);

        $token = App\Utils\Functions::encrypt($dados);
        $user->token = $token;
        $user->password = 'password-reset';
        $user->infoLog = $user->codigo;
        $user->save();

        $linkToken = BASE_URL . 'resetar-senha/' . $token;
        App\Utils\NotificationCenter::sendEmailResetPassword($user, $linkToken);

        return App\Utils\Functions::responseJSON($response, array('code' => 200, 'result' => array('message' => 'Um e-mail foi enviado com as instrução para a nova senha.')));
    } catch (Exception $ex) {
        return App\Utils\Functions::exceptionJSON($response, $ex);
    }
});

$app->put('/reset-password/{hash}', function ($request, $response, $args) use ($app) {

    try {
        $params = $request->getParsedBody();

        $validator = new App\Utils\Validator();
        $requiredFields = array('hash', 'password');
        $params['hash'] = $args['hash'];
        $validator->checkRequiredFields($requiredFields, $params);

        $decryptedToken = \App\Utils\Functions::decrypt($args['hash']);
        $infoToken = unserialize($decryptedToken);

        if (!is_array($infoToken) || !isset($infoToken['expire_date'])) {
            throw new \Exception("Token inválido.", 400);
        }

        $currentTime = date('Y-m-d H:i:s');
        if (strtotime($currentTime) > strtotime($infoToken['expire_date'])) {
            throw new \Exception("Este token encontra-se expirado.", 400);
        }

        $user = Model::factory('Usuario')->filter('notDeleted')->where('token', $args['hash'])->find_one();

        if (!$user) {
            throw new \Exception("Token não encontrado.", 400);
        }

        $user->token = '';
        $user->infoLog = $user->codigo;
        $user->password = md5($params['password']);
        $user->save();

        $content = array(
            'code'   => 200,
            'result' => "Senha alterada com sucesso."
        );

        return App\Utils\Functions::responseJSON($response, $content);
    } catch (Exception $ex) {
        return App\Utils\Functions::exceptionJSON($response, $ex);
    }
});


$app->group('/validator', function () use ($app) {

    $app->get('/check-user-exists', function ($request, $response) use ($app) {

        $query_string = $request->getQueryParams();

        $validator = new App\Utils\Validator();

        try {

            $requiredFields = array('email');

            $validator->checkRequiredFields($requiredFields, $query_string);

            $usuarioEntity = new App\Entity\Usuario();

            $result = $usuarioEntity->checkExists($query_string);

            return App\Utils\Functions::responseJSON($response, array('code' => 200, 'result' => $result));
        } catch (Exception $ex) {

            return App\Utils\Functions::exceptionJSON($response, $ex);
        }
    });
});

$app->group('/cron', function () use ($app) {

    $app->get('/notificacoes', function ($request, $response) use ($app) {

        $entity = new App\Entity\Notificacao();

        $args = [
            'order'   => 'desc',
            'orderby' => 'data_cadastro',
            'filters' => [
                'notificado' => 0
            ]
        ];

        $results = $entity->getAll($args, $entity->default_fields);

        return App\Utils\Functions::responseJSON($this->response, array(
            'code'   => 200,
            'result' => $results
        ));

    });

    $app->get('/notificacao/{id:[0-9]+}', function ($request, $response, $args) use ($app) {

        $entity = new App\Entity\Notificacao('nolog');

        $notification = $entity->get($args['id'], $entity->default_fields);

        $notification['notificado'] = 1;

        $result = $entity->save($notification, $entity->exclude_save);

        return App\Utils\Functions::responseJSON($this->response, array(
            'code'   => 200,
            'result' => $result
        ));

    });

});




