<?php

namespace App\Entity;


use App\Models;
use App\Utils\Container;

class Transacao extends BaseEntity
{
    public $base_name = 'nome';
    public $required_fields = array('cod_transacao', 'fk_estabelecimento', 'fk_usuario', 'valor');
    public $default_fields = array('codigo', 'cod_transacao', 'fk_estabelecimento', 'fk_usuario', 'valor');

    public function __construct($infoUser = null)
    {
        parent::__construct(Models\Transacao::class, $infoUser);
    }

    public function save($data, $exclude = array())
    {
        $entityEstabelecimento = Container::getEstabelecimento($this->_user);
        $estabelecimento = $entityEstabelecimento->findOne(array('nome' => $data['fk_estabelecimento']));

        $id = null;
        if (!$estabelecimento) {
            $row = \Model::factory(Models\Estabelecimento::class)->create();
            $row->nome = $data['fk_estabelecimento'];
            $row->latitude = $data['latitude'];
            $row->longitude = $data['longitude'];
            $row->categoria = $data['categoria'];
            $row->infoLog = $this->_user;
            $row->save();
            $id = $row->id();
        }

        $data['fk_estabelecimento'] = $id ? $id : $estabelecimento->codigo;
        unset($data['latitude']);
        unset($data['longitude']);
        unset($data['categoria']);
        $data['fk_usuario'] = $this->_user;


        return parent::save($data, $exclude);
    }

    public function importarTransacoes(array $transacoes)
    {
        $count = 0;
        foreach ($transacoes as $item) {
            $item = explode(';', $item);
            $data = [];
            $data['cod_transacao'] = $item[0];
            $data['data_transacao'] = $item[1];
            $data['valor'] = $item[2];
            $data['fk_estabelecimento'] = $item[3];
            $data['latitude'] = $item[4];
            $data['longitude'] = $item[5];
            $data['categoria'] = $item[6];
            $this->save($data);
            $count++;
        }

        return $count;
    }

    private function _selectBase()
    {
        $query = \ORM::for_table('tb_transacao')
            ->inner_join('tb_estabelecimento', array('tb_estabelecimento.codigo', '=', 'tb_transacao.fk_estabelecimento'))
            ->inner_join('tb_usuario', array('tb_usuario.codigo', '=', 'tb_transacao.fk_usuario'))
            ->where_not_in('tb_transacao.status', array('deleted'))
            ->where_not_in('tb_estabelecimento.status', array('deleted'))
            ->where_not_in('tb_usuario.status', array('deleted'));

        return $query;

    }

    public function getAmigosPorEstabelecimento($ids_friends, $filtros)
    {
        $transacoes = $this->_selectBase();

        $transacoes->where_in('tb_usuario.app_id', $ids_friends);
        $this->_filterRelatorio($transacoes, $filtros);

        $results = $transacoes->find_many();
        $array = $this->formatArray($results);

        $newResult = [];
        foreach ($array as $item) {
            if (!in_array($item['app_id'], $newResult)) {
                $newResult[] = $item['app_id'];
            }
        }


        return $newResult;

    }

    private function _filterRelatorio(\ORM $query, array $filtros)
    {
        if (isset($filtros['fk_estabelecimento']) && $filtros['fk_estabelecimento']) {
            $query->where('tb_transacao.fk_estabelecimento', $filtros['fk_estabelecimento']);
        }

        if (isset($filtros['fk_usuario']) && $filtros['fk_usuario']) {
            $query->where('tb_transacao.fk_usuario', $filtros['fk_usuario']);
        }


        if (isset($filtros['categoria']) && $filtros['categoria']) {
            $query->where('tb_estabelecimento.categoria', $filtros['categoria']);
        }

        return $query;

    }


    public function sugestao(array $ids_friends, $filtros)
    {
        $transacoes = $this->_selectBase();

        $transacoes->select('tb_estabelecimento.codigo')
            ->select('tb_estabelecimento.nome')
            ->select('tb_estabelecimento.promocao')
            ->select('tb_estabelecimento.latitude')
            ->select('tb_estabelecimento.longitude')
            ->select_expr('AVG(tb_transacao.valor)', 'valor_medio')
            ->select_expr('COUNT(tb_transacao.codigo)', 'qtde_transacoes')
            ->group_by_expr('tb_estabelecimento.codigo');

        if (count($ids_friends) <= 0) {
            $ids_friends = array($this->authenticated_user_data->app_id);
        }

        $transacoes->where_in('tb_usuario.app_id', $ids_friends);

        if ($filtros['order'] == 'qtde') {
            $transacoes->order_by_desc('qtde_transacoes');
            if (isset($filtros['coordenadas'])) {
                $transacoes->order_by_expr("Geo({$filtros['coordenadas']}, tb_estabelecimento.latitude, tb_estabelecimento.longitude)");
            }
            $transacoes->order_by_asc('valor_medio');
        } else {
            $transacoes->order_by_asc('valor_medio');
            if (isset($filtros['coordenadas'])) {
                $transacoes->order_by_expr("Geo({$filtros['coordenadas']}, tb_estabelecimento.latitude, tb_estabelecimento.longitude)");
            }
            $transacoes->order_by_desc('qtde_transacoes');
        }

        $this->_filterRelatorio($transacoes, $filtros);

        $results = $transacoes->find_many();
        $array = $this->formatArray($results);


        return $array;
    }


    public function getCategorias(array $ids_friends)
    {
        $transacoes = $this->_selectBase();
        $transacoes->select_expr('DISTINCT(tb_estabelecimento.categoria)');


        $transacoes->select('tb_estabelecimento.categoria')
            ->group_by_expr('tb_estabelecimento.codigo');

        //Meus amigos e eu
        $ids_friends[] = $this->authenticated_user_data->app_id;

        $transacoes->where_in('tb_usuario.app_id', $ids_friends);

        $results = $transacoes->find_many();

        $array = $this->formatArray($results);


        return $array;
    }

}
