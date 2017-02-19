<?php

namespace App\Controller;

use App\Entity\Transacao;
use App\Utils\Functions;
use App\Utils\UserSession;

class UsuarioController extends BaseController
{
    /*
     * Simulador de transações do usuário.
     */
    public function importarTransacoes()
    {
        try {
            $cliente = $this->get('user_id');
            $fileContents = file_get_contents("http://" . DB_HOST . "/conductor/api-internet-bank/?cliente={$cliente}");

            $transacoes = array_filter(explode('|', $fileContents));

            $entityTransacao = new Transacao($this->get('user_id'));
            $count = $entityTransacao->importarTransacoes($transacoes);

            $result = array('code' => 201, 'result' => array("message" => "{$count} transações importadas."));

            return Functions::responseJSON($this->response, array(
                    'code'   => 201,
                    'result' => $result
                )
            );

        } catch (\Exception $ex) {
            return Functions::exceptionJSON($this->response, $ex);
        }

    }

    public function sugestoes()
    {
        $parsedBody = $this->request->getParsedBody();

        $filtros = [];

        if (isset($parsedBody['order'])) {
            $filtros['order'] = $parsedBody['order'];
        }

        if (isset($parsedBody['coordenadas'])) {
            $filtros['coordenadas'] = $parsedBody['coordenadas'];
        }

        if (isset($parsedBody['categoria'])) {
            $filtros['categoria'] = $parsedBody['categoria'];
        }

        if (isset($parsedBody['fk_estabelecimento'])) {
            $filtros['fk_estabelecimento'] = $parsedBody['fk_estabelecimento'];
        }

        $ids = $parsedBody['ids'];
        $ids_friends = array_filter(explode(',', $ids));

        $entityTransacoes = new Transacao($this->get('user_id'));
        $entityTransacoes->authenticated_user_data = UserSession::getUserAuthenticated($this->get('app'));

        $result = $entityTransacoes->sugestao($ids_friends, $filtros);

        $newResult = [];

        foreach ($result as $item) {
            $filtros['fk_estabelecimento'] = $item['codigo'];
            $item['friends_id'] = $entityTransacoes->getAmigosPorEstabelecimento($ids_friends, $filtros);
            $newResult[] = $item;
        }

        return Functions::responseJSON($this->response, array(
                'code'   => 200,
                'result' => $newResult
            )
        );
    }
}