<?php

namespace App\Entity;


class Log extends BaseEntity
{

    public function __construct($infoUser = null)
    {
        parent::__construct(\App\Models\Log::class, $infoUser);
    }


}
