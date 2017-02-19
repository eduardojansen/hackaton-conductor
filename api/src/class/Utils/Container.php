<?php

namespace App\Utils;

abstract class Container
{

    public static function getUsuario()
    {
        return new \App\Entity\Usuario();
    }

    public static function getEstabelecimento($user = null)
    {
        return new \App\Entity\Estabelecimento($user);
    }


}
