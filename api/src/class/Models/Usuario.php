<?php

namespace App\Models;


class Usuario extends BaseModel
{
    protected $_uniqueFields = array('token');
    public static $_table = 'tb_usuario';
    public static $_id_column = 'codigo';

    public static function notDeleted($orm)
    {
        return $orm->where_not_in('tb_usuario.status', array('deleted'));
    }

}
