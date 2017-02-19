<?php
namespace App\Controller;

use App\Entity\Anexo;
use App\Entity\BaseEntity;
use App\Entity\Notificacao;
use App\Utils\UserSession;
use App\Utils\Functions;
use App\Utils\Validator;
use MartynBiz\Slim3Controller\Controller;

//Recuperar $app $this->get('app')

class BaseController extends Controller
{
    protected $entity;
    protected $entityName;

    public function __construct(\Slim\App $app, $entity)
    {
        $this->entityClass = $entity;

        if ($entity) {
            $slug = explode('\\', $entity);
            $this->entityName = strtolower(end($slug));
        }

        parent::__construct($app);
    }


    public function index()
    {

        try {
            UserSession::checkUserPermissions($this->get('app'), $this->entityName, 'index');


            /** @var BaseEntity $entity */
            $entity = new $this->entityClass($this->get('user_id'));
            $entity->authenticated_user_data = UserSession::getUserAuthenticated($this->get('app'));

            $query_string = $this->request->getQueryParams();

            $fields = $entity->default_fields;
            if (isset($query_string['fields']) && $query_string['fields']) {
                $fields = explode(',', $query_string['fields']);
                $fields = array_merge($entity->default_fields, $fields);
                unset($query_string['fields']);
            }

            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);

            $limit = $limit ? $limit : 1000;

            $offset = ($page - 1) * $limit;

            $args = array_merge(array(
                'page'   => $page,
                'offset' => $offset,
                'limit'  => $limit,
            ), $query_string);

            if (!isset($args['order'])) {
                $args['order'] = 'desc';
            }

            if (!isset($args['orderby'])) {
                $args['orderby'] = 'codigo';
            }

            $results = $entity->getAll($args, $fields);

            return Functions::responseJSON($this->response, array(
                'code'   => 200,
                'result' => $results
            ));
        } catch (\Exception $ex) {
            return Functions::exceptionJSON($this->response, $ex);
        }

    }

    public function show($id)
    {
        try {
            UserSession::checkUserPermissions($this->get('app'), $this->entityName, 'view');
            /** @var BaseEntity $entity */
            $entity = new $this->entityClass($this->get('user_id'));
            $entity->authenticated_user_data = UserSession::getUserAuthenticated($this->get('app'));


            $query_string = $this->request->getQueryParams();

            $fields = $entity->default_fields;
            if (isset($query_string['fields']) && $query_string['fields']) {
                $fields = explode(',', $query_string['fields']);
                $default_fields = count($entity->default_fields_edit) > 0 ? $entity->default_fields_edit : $entity->default_fields;
                $fields = array_merge($default_fields, $fields);
            }


            $result = $entity->get($id, $fields);

            return Functions::responseJSON($this->response, array(
                'code'   => 200,
                'result' => $result
            ));

        } catch (\Exception $ex) {
            return Functions::exceptionJSON($this->response, $ex);
        }
    }

    public function post()
    {
        try {
            UserSession::checkUserPermissions($this->get('app'), $this->entityName, 'new');

            $parsedBody = $this->request->getParsedBody();

            $validator = new Validator();
            /** @var BaseEntity $entity */
            $entity = new $this->entityClass($this->get('user_id'));
            $entity->authenticated_user_data = UserSession::getUserAuthenticated($this->get('app'));

            $validator->checkRequiredFields($entity->required_fields, $parsedBody);

            $save_id = $entity->save($parsedBody, $entity->exclude_save);

            if ( isset($entity->_notificacoes) ) {
                $notificationData = [
                    'entidade' => $entity,
                    'entidade_id' => $save_id,
                    'mensagem' => $entity->_notificacoes['new'],
                    'fk_tipo_notificacao' => 1
                ];

                $notificacao = new Notificacao($this->get('user_id'));
                $notificacao->notify($notificationData);

            }

            if (isset($parsedBody['anexos']) && count($parsedBody['anexos']) > 0) {
                $anexoEntity = new Anexo($this->get('user_id'));
                $anexoEntity->setAnexos(['modulo' => $this->entityName, 'modulo_id' => $save_id], $parsedBody['anexos']);
            }

            $result = array(
                'code'   => 201,
                'result' => array(
                    "codigo"  => $save_id,
                    "message" => "Dados cadastrados com sucesso."
                )
            );

            return Functions::responseJSON($this->response, $result);
        } catch (\Exception $ex) {
            return Functions::exceptionJSON($this->response, $ex);
        }
    }

    public function edit($id)
    {
        try {
            UserSession::checkUserPermissions($this->get('app'), $this->entityName, 'edit');

            $parsedBody = $this->request->getParsedBody();

            $validator = new Validator();

            $parsedBody['codigo'] = $id;
            /** @var BaseEntity $entity */
            $entity = new $this->entityClass($this->get('user_id'));
            $validator->checkRequiredFields($entity->required_fields, $parsedBody);

            if ( isset($entity->_notificacoes) ) {
                if (($entity->_entityName == 'tarefa') || ($entity->_entityName == 'ticket')) {
                    $oldObj = $entity->get($id);
                }
            }

            $save_id = $entity->save($parsedBody, $entity->exclude_save);

            if ( isset($entity->_notificacoes) ) {

//                if ( ($entity->_entityName == 'tarefa') || ($entity->_entityName == 'ticket') ) {
//
//                    $oldObjClear = $oldObj;
//                    unset($oldObjClear['fk_prioridade']);
//                    unset($oldObjClear['fk_andamento']);
//
//                    $parsedBodyClear = $parsedBody;
//                    unset($parsedBodyClear['fk_prioridade']);
//                    unset($parsedBodyClear['fk_andamento']);
//
//                    if ( ($oldObjClear != $parsedBodyClear) || ( ($oldObj['fk_prioridade'] == $parsedBody['fk_prioridade']) && ($oldObj['fk_andamento'] == $parsedBody['fk_andamento']) ) ) {
//                        $notificationData = [
//                            'entidade' => $entity,
//                            'entidade_id' => $save_id,
//                            'mensagem' => $entity->_notificacoes['edit'],
//                            'fk_tipo_notificacao' => 2
//                        ];
//                    } else {
//                        if ( $oldObj['fk_prioridade'] != $parsedBody['fk_prioridade'] ) {
//                            $notificationData = [
//                                'entidade' => $entity,
//                                'entidade_id' => $save_id,
//                                'mensagem' => $entity->_notificacoes['change_priority'],
//                                'fk_tipo_notificacao' => 6
//                            ];
//                        }
//
//                        if ( $oldObj['fk_andamento'] != $parsedBody['fk_andamento'] ) {
//                            $notificationData = [
//                                'entidade' => $entity,
//                                'entidade_id' => $save_id,
//                                'mensagem' => $entity->_notificacoes['change_status'],
//                                'fk_tipo_notificacao' => 8
//                            ];
//                        }
//                    }
//
//                } else {

                $notificationData = [
                    'entidade' => $entity,
                    'entidade_id' => $save_id,
                    'mensagem' => $entity->_notificacoes['edit'],
                    'fk_tipo_notificacao' => 2
                ];

//                }

                $notificacao = new Notificacao($this->get('user_id'));
                $notificacao->notify($notificationData);
            }

            if (isset($parsedBody['anexos']) && count($parsedBody['anexos']) > 0) {
                $anexoEntity = new Anexo($this->get('user_id'));
                $anexoEntity->setAnexos(['modulo' => $this->entityName, 'modulo_id' => $save_id], $parsedBody['anexos']);
            }

            $result = array(
                'code'   => 200,
                'result' => array(
                    "codigo"  => $save_id,
                    "message" => "Dados atualizados com sucesso."
                )
            );

            return Functions::responseJSON($this->response, $result);
        } catch (\Exception $ex) {
            return Functions::exceptionJSON($this->response, $ex);
        }
    }

    public function delete($id)
    {
        try {
            UserSession::checkUserPermissions($this->get('app'), $this->entityName, 'delete');

            /** @var BaseEntity $entity */
            $entity = new $this->entityClass($this->get('user_id'));
            $entity->delete($id);

            if ( isset($entity->_notificacoes) ) {
                $notificationData = [
                    'entidade' => $entity,
                    'entidade_id' => $id,
                    'mensagem' => $entity->_notificacoes['delete'],
                    'fk_tipo_notificacao' => 3
                ];

                $notificacao = new Notificacao($this->get('user_id'));
                $notificacao->notify($notificationData);
            }

            $result = array('code' => 200, 'result' => array("message" => "Dados excluídos com sucesso."));

            return Functions::responseJSON($this->response, $result);

        } catch (\Exception $ex) {
            return Functions::exceptionJSON($this->response, $ex);
        }
    }

    public function updateAndamento($id)
    {
        try {
            UserSession::checkUserPermissions($this->get('app'), $this->entityName, 'edit');

            $dadosPut = $this->request->getParsedBody();
            $parsedBody = array(
                'codigo'       => $id,
                'fk_andamento' => $dadosPut['fk_andamento']
            );

            $validator = new Validator();


            /** @var BaseEntity $entity */
            $entity = new $this->entityClass($this->get('user_id'));
            $validator->checkRequiredFields(array('fk_andamento', 'codigo'), $parsedBody);

            $entity->save($parsedBody, $entity->exclude_save);

            if ( isset($entity->_notificacoes) ) {
                $notificationData = [
                    'entidade' => $entity,
                    'entidade_id' => $id,
                    'mensagem' => $entity->_notificacoes['change_status'],
                    'fk_tipo_notificacao' => 8
                ];

                $notificacao = new Notificacao($this->get('user_id'));
                $notificacao->notify($notificationData);
            }

            $result = array('code' => 200, 'result' => array("message" => "Andamento atualizado com sucesso."));

            return Functions::responseJSON($this->response, $result);
        } catch (\Exception $ex) {
            return Functions::exceptionJSON($this->response, $ex);
        }
    }

    public function updateParcial($id)
    {
        try {
            UserSession::checkUserPermissions($this->get('app'), $this->entityName, 'edit');

            $dados = $this->request->getParsedBody();

            $dados['codigo'] = $id;

            /** @var BaseEntity $entity */
            $entity = new $this->entityClass($this->get('user_id'));

            $entity->save($dados, $entity->exclude_save);

            if ( isset($entity->_notificacoes) ) {

                $notificationData = [
                    'entidade' => $entity,
                    'entidade_id' => $id,
                ];

                if ( isset($dados['fk_andamento']) ) {

                    $notificationData['mensagem'] = $entity->_notificacoes['change_status'];

                    if ( $dados['fk_andamento'] == 2 ) {
                        $notificationData['fk_tipo_notificacao'] = 4;
                    } elseif ( $dados['fk_andamento'] == 5 ) {
                        $notificationData['fk_tipo_notificacao'] = 7;
                    } else {
                        $notificationData['fk_tipo_notificacao'] = 8;
                    }

                    $resultMessage = 'Andamento atualizado com sucesso.';

                } elseif ( isset($dados['fk_prioridade']) ) {

                    $notificationData['mensagem'] = $entity->_notificacoes['change_priority'];
                    $notificationData['fk_tipo_notificacao'] = 6;

                    $resultMessage = 'Prioridade atualizada com sucesso.';

                } else {

                    $notificationData['mensagem'] = $entity->_notificacoes['edit'];
                    $notificationData['fk_tipo_notificacao'] = 2;

                    $resultMessage = 'Edição realizada com sucesso.';

                }

                $notificacao = new Notificacao($this->get('user_id'));
                $notificacao->notify($notificationData);
            }

            $result = array('code' => 200, 'result' => array("message" => $resultMessage));

            return Functions::responseJSON($this->response, $result);
        } catch (\Exception $ex) {
            return Functions::exceptionJSON($this->response, $ex);
        }
    }

}