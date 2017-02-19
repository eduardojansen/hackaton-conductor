<?php

namespace App\Entity;

use App\Models\Projeto;
use App\Utils\Functions;
use Model;
use Exception;

abstract class BaseEntity
{

    protected $_model;
    protected $_user;
    public $_entityName;
    public $authenticated_user_data;
    public $base_name = "";
    public $base_search = array();
    public $required_fields = array();
    public $default_fields = array();
    public $default_fields_edit = array();
    public $relationship_fields = array();
    public $exclude_save = array();


    public function __construct($mClassName, $infoUser = null)
    {
        if ($infoUser) {
            $this->_user = $infoUser;
        }
        $this->_model = $mClassName;
        $this->_getClassName();
    }

    private function _getClassName()
    {
        $slug = explode('\\', $this->_model);
        $this->_entityName = strtolower(end($slug));
    }


    protected function preSave($data)
    {
        unset($data['data_cadastro']);
        unset($data['data_update']);

        return $data;
    }

    protected function afterSave($row)
    {

    }

    public function toArray($row, array $fields, $hideEmptyField = true)
    {


        if ($row) {
            $row = $row->as_array();

            $result = array();

            /* adiciona em $result apenas os campos também presentes em $fields */
            foreach ($row as $key => $value) {
                if ($hideEmptyField && ($value == '' || $value == null)) {
                    continue;
                }
                if (in_array($key, $fields)) {
                    $result[$key] = \App\Utils\Functions::formatField($value, $key);
                }
            }

            /* Trazer as informaçoes do objeto relacionado */
            foreach ($fields as $value) {
                $foreignKey = substr($value, 0, 3) == 'fk_' ? $value : null;
                /* somente é array quando trata-se de relacionamento */
                if (is_array($value) || $foreignKey) {
                    //array('fk_franqueado' => 'identificador'),
                    $chave = $foreignKey ? $foreignKey : key($value);

                    $fields_rel = array('codigo');

                    /* remove fk_ de fk_franqueado e transforma no nome da Entidade Franqueado */
                    $entidade = \App\Utils\Functions::dashesToCamelCase(substr($chave, 3), true);
                    $class = '\\App\\Entity\\' . $entidade;

                    /** @var BaseEntity $entity */
                    $entity = new $class;

                    /* verifica se deve ser retornado vários campos do objeto
                     * relacionado $value['fk_franqueado'] => identificador */
                    if (!$foreignKey && is_array($value[$chave])) {
                        $fields_rel = array_merge($fields_rel, $value[$chave]);
                    } else {
                        if ($foreignKey && (!isset($entity->relationship_fields) || empty($entity->relationship_fields))) {
                            throw new \Exception("Nome base ({$entity->_entityName}) não foi definido.", 500);
                        }
                        if ($foreignKey) {
                            if (is_array($entity->relationship_fields)) {
                                $fields_rel = array_merge($fields_rel, $entity->relationship_fields);
                            }
                        } else {
                            $fields_rel[] = $value[$chave];
                        }
                    }

                    /* $chave = fk_franqueado */
                    /* Franqueado->get(ID_DO_FRANQUEADO, [ 'field1', 'field2' ]) */
                    if (isset($row[$chave])) {
                        $result[$chave] = $entity->get($row[$chave], $fields_rel);
                    }
                }
            }
        }

        /* Retorna Instancia com SubInstancias Relacionadas */

        return $result;
    }

    protected function _getRow($data)
    {
        if (isset($data['codigo'])) {
            $row = Model::factory($this->_model)->filter('notDeleted')->find_one($data['codigo']);
            if (!$row) {
                throw new Exception("Registro não localizado.", 404);
            }
        } else {
            $row = Model::factory($this->_model)->create();
        }

        return $row;
    }

    public function save($data, $exclude = array())
    {
        $row = $this->_getRow($data);

        /* Injetando no objeto as informaçoes do usuario que realizou a acao */
        if (isset($this->_user)) {
            $row->infoLog = $this->_user;
        }

        $data = $this->preSave($data);
        foreach ($data as $key => $value) {
            if (!in_array($key, $exclude)) {
                if (is_array($value)) {
                    if (!isset($row->codigo) || ($row->$key != $value['codigo'])) {
                        $row->$key = $value['codigo'];
                    }
                } else {
                    if ((!isset($row->codigo)) || ($row->$key != $value)) {
                        $row->$key = $value;
                    }
                }
            }
        }
        /* campo password sera convertido em md5 */
        if (!in_array('password', $exclude) && isset($data['password']) && $data['password']) {
            $row->password = md5($data['password']);
        }
        $row->save();
        $id = $row->id();

        if (!$id) {
            throw new Exception("Problema no cadastro de {$this->_entityName}", 400);
        }

        $this->afterSave($row);

        return $id;
    }

    public function delete($row_id)
    {
        if (!$row_id) {
            throw new \Exception("Código não informado para remoção.", 400);
        }

        $row = Model::factory($this->_model)->filter('notDeleted')->find_one($row_id);

        if (!$row) {
            throw new \Exception("Código informado não foi encontrado", 404);
        }

        $row->status = 'deleted';

        /* Injetando no objeto as informaçoes do usuario que realizou a acao */
        if (isset($this->_user)) {
            $row->infoLog = $this->_user;
        }


        $row->save();

    }

    public function getObj($id)
    {

        if (!$id) {
            throw new \Exception("Código não informado.", 400);
        }

        $row = Model::factory($this->_model)->find_one($id);

        return $row;
    }

    protected function _factory()
    {

        return Model::factory($this->_model);
    }

    protected function _get($id)
    {
        $factory = $this->_factory();

        $return = $factory->find_one($id);


        return $return;
    }

    public function get($id, array $fields = null)
    {

        if (!$id) {
            throw new \Exception("Código não informado.", 400);
        }

        /* Permitir buscar deletados tambem */
        $row = $this->_get($id);

        if (!$row) {
            throw new \Exception("Registro (" . ucfirst($this->_entityName) . ") não foi localizado.", 404);
        }

        return $this->toArray($row, $fields ? $fields : $this->default_fields);
    }

    public function findById($id)
    {

        if (!$id) {
            throw new \Exception("Código não informado.", 400);
        }

        $row = Model::factory($this->_model)->find_one($id);

        return $row;
    }


    public function findOne(array $where)
    {
        $row = Model::factory($this->_model)->filter('notDeleted')->where($where)->find_one();

        return $row;
    }

    public function findMany(array $where)
    {
        $row = Model::factory($this->_model)->filter('notDeleted')->where($where)->find_many();

        return $row;
    }


    protected function _setSqlBase($queryParams)
    {
        $sql = Model::factory($this->_model)
            ->filter('notDeleted');

        if ($queryParams['page']) {
            $totalCount = $sql->count();

            $sql
                ->limit($queryParams['limit'])
                ->offset($queryParams['offset']);
        }

        return $sql;
    }


    public function _getTable()
    {
        $model = $this->_model;
        $table = $model::$_table;

        return $table;
    }

    /**
     * Monta o array para pesquisa pelo filtro 's'.
     * O base_search e opcional na classe e nao implementado o base_name assume como campo unico de pesquisa.
     * @param $value
     * @return array
     */
    public function getBaseSearch($value)
    {

        $table = $this->_getTable();

        if (count($this->base_search) > 0) {
            $campos = $this->base_search;
        } else {
            $campos = array($this->base_name);
        }

        $listaArray = array();

        foreach ($campos as $campo) {
            $listaArray[] = array($table . '.' . $campo => '%' . $value . '%');
        }

        return $listaArray;

    }

    protected function _setWhere(\ORMWrapper $sql, $queryParams)
    {
        $table = $this->_getTable();

        if (isset($queryParams['data_inicio']) && $queryParams['data_inicio']
            && isset($queryParams['data_fim']) && $queryParams['data_fim']
        ) {
            $sql->where_raw("DATE({$table}.data_inicio) >= '" . $queryParams['data_inicio'] . "'");
            $sql->where_raw("DATE({$table}.data_fim) <= '" . $queryParams['data_fim'] . "'");
        }

        //Filtro para as entidades que possuírem fk_andamento e consequentemente o campo data_update_andamento
        if (isset($queryParams['data_update_andamento_inicial']) && $queryParams['data_update_andamento_inicial']
            && isset($queryParams['data_update_andamento_final']) && $queryParams['data_update_andamento_final']
        ) {
            $sql->where_raw("(DATE({$table}.data_update_andamento) BETWEEN ? AND ?)", array($queryParams['data_update_andamento_inicial'], $queryParams['data_update_andamento_final']));
        }


        if (isset($queryParams['filters']) && $queryParams['filters']) {
            $table = $this->_getTable();
            foreach ($queryParams['filters'] as $key => $value) {
                $sql->where($table . '.' . $key, $value);
            }
        }

        if (isset($queryParams['s']) && $queryParams['s']) {
            $arraySearch = $this->getBaseSearch($queryParams['s']);
            $sql->where_any_is($arraySearch, 'LIKE');
        }

        return $sql;
    }

    public function getAll($queryParams = '', array $fields)
    {
        $sql = Model::factory($this->_model)
            ->filter('notDeleted');

        $sql = $this->_setWhere($sql, $queryParams);

        if (isset($queryParams['page']) && $queryParams['page']) {
            $totalCount = $sql->count();
            $sql
//                ->limit($queryParams['limit']) #comentado temporariamente
                ->limit(1000)
                ->offset($queryParams['offset']);
        }

        if (isset($queryParams['orderby']) && $queryParams['orderby']) {

            if (isset($queryParams['order'])) {
                if ($queryParams['order'] == 'desc') {
                    $sql->order_by_desc($queryParams['orderby']);
                } else {
                    $sql->order_by_asc($queryParams['orderby']);
                }
            } else {
                $sql->order_by_desc($queryParams['orderby']);
            }
        }

        $results = $sql->find_many();
//
        $return = array();

        foreach ($results as $row) {
            $return[] = $this->toArray($row, $fields);
        }
        if (isset($totalCount)) {
            $return = array(
                'count' => $totalCount,
                'items' => $return ? $return : array()
            );
        }

        return $return;
    }

    protected function formatDates(array $dates, $row, $dados)
    {
        foreach ($dates as $date) {
            if (isset($dados[$date])) {
                $dataFormatada = $this->formatDateToMysql($row->$date);
                if ($row->$date != $dataFormatada) {
                    $row->$date = $dataFormatada;
                }
            }
        }

        return $row;
    }

    protected function formatDateToMysql($date)
    {
        $dt = \DateTime::createFromFormat('d/m/Y', $date);

        return $dt->format('Y-m-d');
    }

    protected function validarCamposObrigatorios(array $dados, array $obrigatorio)
    {
        foreach ($dados as $dado) {
            foreach ($obrigatorio as $campo) {
                if (!isset($dado[$campo]) || is_null($dado[$campo]) || empty($dado[$campo])) {
                    throw new \Exception("Campo {$campo} é obrigatório e não foi informado.", 400);
                }
            }
        }
    }

    protected function getContatoLogado()
    {
        $authenticated_user_data = $this->authenticated_user_data;
        if (isset($authenticated_user_data->contato)) {
            return $authenticated_user_data->contato;
        }

        return null;
    }

    //Filtrar no acesso do contato para exibir apenas as informações da(s) empresa(s) que ele faz parte.
    protected function getEmpresasContato()
    {
        if ($contato = $this->getContatoLogado()) {
            $empresas = $contato->empresas;
            $ids_empresas = [];
            foreach ($empresas as $empresa) {
                $ids_empresas[] = $empresa->codigo;
            }

            return $ids_empresas;
        }

        return null;
    }

    protected function formatArray($results)
    {
        $array = [];
        foreach ($results as $row) {
            $dado = $row->as_array();
            $array[] = Functions::formatArray($dado);
        }

        return $array;
    }
}
