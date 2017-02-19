<?php

namespace App\Models;

class Log extends \Model
{

    public static $_table = 'tb_log';
    public static $_id_column = 'id';

    public static function notDeleted($orm)
    {
        return $orm->where_not_in('status', array('deleted'));
    }

}
