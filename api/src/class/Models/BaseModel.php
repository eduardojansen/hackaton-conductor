<?php

namespace App\Models;

use Exception;
use Model;


class BaseModel extends \Model
{

    protected $_uniqueFields = array();


    public static function notDeleted($orm)
    {
        return $orm->where_not_in('status', array('deleted'));
    }

    /**
     * @throws Exception
     */
    public function save()
    {

        if (!isset($this->infoLog)) {
            throw new Exception("Usuário não especificado no save", 500);
        }

        $entidade = get_class($this);
        if ($this->codigo) {
            $oldValues = Model::factory($entidade)->find_one($this->codigo)->as_array();
        } else {
            $oldValues = 'created';
        }

        /* Informaçao do usuario que fez a alteracao */
        /* Caso  venha algo direrente de INT, nao vai salvar em log. */
        $user = filter_var($this->infoLog, FILTER_VALIDATE_INT);
        if (!$user && $this->infoLog != 'nolog') {
            throw new Exception("Usuário especificado no save é inválido", 500);
        }

        unset($this->orm->infoLog);

        if ($user) {

            /* Retorna os valores que foram alterados */
            $newValues = $this->checkChangedValues();

            /* Salva fk_cliente ou fk_registro, quando existir */
            $fkCliente = NULL;
            $fkPedido = NULL;

            if (isset($newValues['fk_cliente'])) {
                $fkCliente = $newValues['fk_cliente'];
            } else {
                if (isset($oldValues['fk_cliente'])) {
                    $fkCliente = $oldValues['fk_cliente'];
                }
            }

            if (isset($newValues['fk_pedido'])) {
                $fkPedido = $newValues['fk_pedido'];
            } else {
                if (isset($oldValues['fk_pedido'])) {
                    $fkPedido = $oldValues['fk_pedido'];
                }
            }


            /* Armazenar apenas os valores antigos que foram afetados */
            if ($oldValues && is_array($oldValues)) {
                $oldValuesDiff = array();
                foreach ($oldValues as $key => $value) {
                    if (isset($newValues[$key]) && $newValues[$key] != $oldValues[$key]) {
                        $oldValuesDiff[$key] = $oldValues[$key];
                    }
                }
            } else {
                $oldValuesDiff = $oldValues;
            }
        }
        if (count($this->_uniqueFields) > 0) {
            $this->validarRegistroUnico();
        }

        if ($save = parent::save()) {
            if (count($newValues) > 1 && $user) {
                $codigo = isset($newValues['codigo']) ? $newValues['codigo'] : null;
                unset($newValues['codigo']);
                $dados = array(
                    'fk_usuario' => $user,
                    'entidade'   => $entidade,
                    'codigo'     => $codigo,
                    'old_data'   => print_r($oldValuesDiff, true),
                    'new_data'   => print_r($newValues, true)
                );
                /* Log no arquivo */
                $this->logMsg($dados);

                /* Log no banco */
                $dados['old_data'] = serialize($oldValuesDiff);
                $dados['new_data'] = serialize($newValues);
//                $log = Model::factory(Log::class)->create($dados);
//                $log->save();
            }
        }
    }

    private function checkChangedValues()
    {
        $change = array();
        foreach ($this->as_array() as $key => $value) {
            if ($this->is_dirty($key) || $key == 'codigo') {
                $change[$key] = $value;
            }
        }

        return $change;
    }

    private function logMsg($dados, $level = 'info', $file = 'main.log')
    {
        $levelStr = '';

        switch ($level) {
            case 'info':
                $levelStr = 'INFO';
                break;

            case 'warning':
                $levelStr = 'WARNING';
                break;

            case 'error':
                $levelStr = 'ERROR';
                break;
        }

        $date = date('Y-m-d H:i:s');

        $msg = sprintf("[%s] [%s] [%s] [%s]: %s%s", $date, $levelStr, $dados['fk_usuario'], $dados['entidade'], $dados['old_data'], $dados['new_data'], PHP_EOL);

        file_put_contents($file, $msg, FILE_APPEND);
    }

    protected function validarRegistroUnico()
    {
        if (count($this->_uniqueFields) > 0) {
            foreach ($this->_uniqueFields as $field) {
                $where = array();
                if (is_array($field)) {
                    foreach ($field as $value) {
                        $where[$value] = $this->$value;
                    }
                } else {
                    $where = array($field => $this->$field);
                }
                $sql = Model::factory(get_class($this))->filter('notDeleted');

                if ($this->codigo) {
                    $sql->where_not_like(
                        array('codigo' => $this->codigo)
                    );
                }

                $sql->where($where);
                $hasRegistry = $sql->findOne();

                if ($hasRegistry) {
                    $campos = implode(' e ', $this->_uniqueFields);

                    if (count($this->_uniqueFields) == 1) {
                        throw new Exception("{$campos} já cadastrado no sistema.", 400);
                    }

                    throw new Exception("Os campos {$campos} já estão cadastrados no sistema.", 400);
                }
            }
        }
    }

}
