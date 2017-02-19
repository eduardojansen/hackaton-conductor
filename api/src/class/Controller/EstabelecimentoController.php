<?php

namespace App\Controller;

use App\Entity\Transacao;
use App\Utils\Functions;
use App\Utils\UserSession;

class EstabelecimentoController extends BaseController
{
    public function categorias()
    {
        try {
            $parsedBody = $this->request->getParsedBody();

            $entityTransacao = new Transacao($this->get('user_id'));
            $entityTransacao->authenticated_user_data = UserSession::getUserAuthenticated($this->get('app'));

            $ids = $parsedBody['ids'];
            $ids_friends = array_filter(explode(',', $ids));
            $categorias = $entityTransacao->getCategorias($ids_friends);

            return Functions::responseJSON($this->response, array(
                    'code'   => 200,
                    'result' => $categorias
                )
            );

        } catch (\Exception $ex) {
            return Functions::exceptionJSON($this->response, $ex);
        }

    }

}