<?php

namespace App\Entity;


use App\Models;

class Conta extends BaseEntity
{
    public $base_name = 'banco';
    public $required_fields = array('numero_conta', 'numero_agencia', 'banco', 'fk_usuario');
    public $default_fields = array('codigo', 'numero_conta', 'numero_agencia', 'banco', 'fk_usuario');

    public function __construct($infoUser = null)
    {
        parent::__construct(Models\Conta::class, $infoUser);
    }
}
