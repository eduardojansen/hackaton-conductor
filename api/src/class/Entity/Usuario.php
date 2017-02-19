<?php

namespace App\Entity;

use App\Utils\Functions;
use Exception;
use Model;

class Usuario extends BaseEntity
{
    public $base_name = "nome";
    public $default_fields = array('codigo', 'cpf', 'nome', 'email', 'anexo', 'status', 'app_id');
    public $relationship_fields = array('nome', 'anexo');


    public function __construct($infoUser = null)
    {
        parent::__construct(\App\Models\Usuario::class, $infoUser);
    }


    public function toArray($row, $fields)
    {
        $return = parent::toArray($row, $fields);


        if (isset($return['anexo']) && $return['anexo']) {
            if (!filter_var($return['anexo'], FILTER_VALIDATE_URL)) {
                $return['anexo'] = UPLOAD_URL . '/fotos/' . $return['anexo'];
            }
        }

        return $return;
    }

    public function getByEmail($email)
    {

        $row = Model::factory(\App\Models\Usuario::class)
            ->where_not_in('status', array('deleted', 'inactive'))
            ->where('email', $email)
            ->find_one();

        if (!$row) {
            throw new Exception("Usuário inválido, verifique com o administrador do sistema.", 400);
        }

        return $row;
    }

    public function getByRelationship($where)
    {

        $row = Model::factory(\App\Models\Usuario::class)
            ->filter('notDeleted')
            ->select('tb_usuario.codigo')
            ->select('tb_usuario.nome')
            ->where($where)
            ->find_one();

        if (!$row) {
            throw new Exception("Usuário inválido", 400);
        }

        return $row;
    }

    public function checkExists($params)
    {

        $sql = Model::factory(\App\Models\Usuario::class)->filter('notDeleted')
            ->where('email', $params['email'])
            ->where_not_in('status', array('deleted'));

        $result = true;

        if (isset($params['codigo'])) {

            $user = $sql->find_one();

            if ($user) {

                if ($user->fk_funcionario) {
                    $result = ($user->fk_funcionario != $params['codigo']);
                } elseif ($user->fk_fda) {
                    $result = ($user->fk_fda != $params['codigo']);
                } elseif ($user->fk_franqueado) {
                    $result = ($user->fk_franqueado != $params['codigo']);
                } elseif ($user->fk_diretoria) {
                    $result = ($user->fk_diretoria != $params['codigo']);
                }
            }
        }

        if ($result === false) {
            return $result;
        }

        if (!$sql->count()) {
            return false;
        }

        if ($sql->count() || $result) {
            throw new Exception("Usuário já cadastrado.", 400);
        }
    }

    public function validarEmail($params, $tipo)
    {
        $sql = Model::factory(\App\Models\Usuario::class)->filter('notDeleted')
            ->where('email', $params['email']);
        if (isset($params['codigo']) && $params['codigo']) {
            $sql->where_not_in($tipo, array($params['codigo']));
        }

        return $sql->find_one();

    }


}
