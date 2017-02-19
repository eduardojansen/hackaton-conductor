<?php

namespace App\Entity;


use App\Models;

class Estabelecimento extends BaseEntity
{
    public $base_name = 'nome';
    public $required_fields = array('nome');
    public $default_fields = array('codigo', 'nome', 'latitude', 'longitude', 'promocao');

    public function __construct($infoUser = null)
    {
        parent::__construct(Models\Estabelecimento::class, $infoUser);
    }

}
