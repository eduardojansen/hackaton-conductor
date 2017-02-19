<?php

namespace App\Models;


class Conta extends BaseModel
{
    public static $_table = 'tb_conta';
    public static $_id_column = 'codigo';

    public static function notDeleted($orm)
    {
        return $orm->where_not_in('tb_conta.status', array('deleted'));
    }

}
