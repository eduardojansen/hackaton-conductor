<?php

namespace App\Utils;

use App\Models\Nivel;
use App\Models\Permissao;
use App\Models\Usuario;
use Model;
use Exception;

class UserSession
{

    private $user;
    private $app;

    public function __construct($user, $app)
    {
        $this->user = $user;
        $this->app = $app;
    }

    public static function generateToken($userId, $app)
    {
        $user = Model::factory(Usuario::class)->find_one($userId);

        $tokenId = base64_encode($userId);
        $issuedAt = time();
        $notBefore = $issuedAt;
        $expire = $notBefore + (24 * 60 * 60);
        $serverName = 'credenciamento.acqiopayments.com.br';

        /*
         * Session Token
         */

        $entity = new \App\Entity\Usuario;
        $objUser = $entity->get($userId);
        $data = array(
            'iat'  => $issuedAt,
            'jti'  => $tokenId,
            'iss'  => $serverName,
            'nbf'  => $notBefore,
            'exp'  => $expire,
            'data' => array(
                'userId' => $userId,
                'user'   => $objUser,
            )
        );
        $secretKey = "32A734CCEF845942A44BED7E8175F";

        $container = $app->getContainer();

        $jwt = $container->jwt->encode(
            $data, $secretKey, 'HS512'
        );

        return $jwt;
    }

    public static function checkUserAuthenticated($app)
    {

        if (!$app->jwtHash->data->userId) {
            throw new Exception("Not authenticated", 401);
        }

        return true;
    }

    public static function getUserAuthenticated($app)
    {
        if (!$app->jwtHash->data->userId) {
            throw new Exception("Not authenticated", 401);
        }

        return $app->jwtHash->data->user;
    }

    public static function checkUserPermissions($app, $module, $action)
    {

        self::getUserAuthenticated($app);
        // if (!array_key_exists($module, $app->jwtHash->data->permissions)) {
        //     throw new Exception("Seu perfil de acesso não suporta este Módulo", 403);
        // }

        // if (!in_array($action, $app->jwtHash->data->permissions->$module->allow)) {
        //     throw new Exception("Seu perfil de acesso não suporta esta Ação", 403);
        // }

        return true;
    }

    public function updateSession()
    {
        $session = Model::factory('UserSession')->find_one($this->id);

        $session->token = $this->token;
        $session->save();

        // $_SESSION[$this->id]['session'] = $this;
    }

    public static function havePermissions($app, $module, $action)
    {
        if (!$app->jwtHash->data->userId) {
            return false;
        }

        if (!array_key_exists($module, $app->jwtHash->data->permissions)) {
            return false;
        }

        if (!in_array($action, $app->jwtHash->data->permissions->$module->allow)) {
            return false;
        }

        return true;
    }

}
