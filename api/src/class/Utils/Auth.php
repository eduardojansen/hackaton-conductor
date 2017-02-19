<?php

namespace App\Utils;

use App\Models\Usuario;
use Model;
use Exception;

class Auth
{

    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function authenticate($params)
    {
        $user = Model::factory(Usuario::class)->select('codigo')
            ->where(array(
                'app_id' => $params['app_id']
            ))->where_not_in('status', array('deleted'))->find_one();

        return $user;
    }

}
