<?php

namespace App\Models;


class Estabelecimento extends BaseModel
{
    public static $_table = 'tb_estabelecimento';
    public static $_id_column = 'codigo';

    public static function notDeleted($orm)
    {
        return $orm->where_not_in('tb_estabelecimento.status', array('deleted'));
    }

}
