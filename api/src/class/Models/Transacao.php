<?php

namespace App\Models;


class Transacao extends BaseModel
{
    public static $_table = 'tb_transacao';
    public static $_id_column = 'codigo';

    public static function notDeleted($orm)
    {
        return $orm->where_not_in('tb_transacao.status', array('deleted'));
    }



}
