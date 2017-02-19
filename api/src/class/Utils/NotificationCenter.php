<?php

namespace App\Utils;

use Sendinblue\Mailin;

class NotificationCenter {

    public static function sendEmailEmissaoPOS($pedido) {
        $clienteData = $pedido->Cliente()->findOne();
        if ($clienteData && $clienteData->email) {

            $subject = 'Produto entregue à transportadora';

            $fields = array(
                'cliente',
                'serial',
                'pin',
                'modelo_pos',
                'operadora',
                'chip_numero',
            );

            $clientes = \ORM::for_table('cliente')
                    ->select('cliente.codigo', 'cliente')
                    ->select('cli_equipamentos.pin', 'pin')
                    ->select('sys_equipamentos.numero_serie', 'serial')
                    ->select('sys_modelo.nome', 'modelo_pos')
                    ->select('sys_operadora.nome', 'operadora')
                    ->select('sys_chip.numero', 'chip_numero')
                    ->innerJoin('cli_equipamentos', array('cli_equipamentos.fk_cliente', '=', 'cliente.codigo'))
                    ->innerJoin('sys_equipamentos', array('sys_equipamentos.codigo', '=', 'cli_equipamentos.fk_equipamento'))
                    ->innerJoin('sys_modelo', array('sys_modelo.codigo', '=', 'sys_equipamentos.fk_modelo'))
                    ->innerJoin('sys_operadora', array('sys_operadora.codigo', '=', 'cli_equipamentos.fk_operadora'))
                    ->innerJoin('sys_chip', array('sys_chip.codigo', '=', 'cli_equipamentos.fk_chip'))
                    ->innerJoin('pedido', array('pedido.fk_cliente', '=', 'cliente.codigo'))
                    ->where(array('pedido.codigo' => $pedido->codigo))
                    ->where(array('cliente.codigo' => $clienteData->codigo));

            $clientes_final = $clientes->find_array();

            $html = '';

            foreach ($clientes_final as $cliente) {
                $html .= '<tr>';
                $html .= '<td style="color: #808080; border-right: 1px solid #e1e1e1; border-bottom: 1px solid #e1e1e1; padding: 7px;">';
                $html .= $cliente['modelo_pos'];
                $html .= '</td>';
                $html .= '<td style="color: #808080; border-right: 1px solid #e1e1e1; border-bottom: 1px solid #e1e1e1; padding: 7px;">';

                $serial = $cliente['serial'];
                $serialMiddle = strlen($serial) / 2;
                $serialP1 = substr($cliente['serial'], 0, $serialMiddle);
                $serialP2 = substr($cliente['serial'], $serialMiddle, strlen($serial));

                $html .= $serialP1 . ' ' . $serialP2;
                $html .= '</td>';
                $html .= '<td style="color: #808080; border-right: 1px solid #e1e1e1; border-bottom: 1px solid #e1e1e1; padding: 7px;">';
                $html .= $cliente['pin'];
                $html .= '</td>';
                $html .= '<td style="color: #808080; border-right: 1px solid #e1e1e1; border-bottom: 1px solid #e1e1e1; padding: 7px;">';
                $html .= $cliente['operadora'];
                $html .= '</td>';
                $html .= '<td style="color: #808080; border-right: 1px solid #e1e1e1; border-bottom: 1px solid #e1e1e1; padding: 7px;">';

                $chip = $cliente['chip_numero'];
                $chipMiddle = strlen($chip) / 2;
                $chip1 = substr($cliente['chip_numero'], 0, $chipMiddle);
                $chip2 = substr($cliente['chip_numero'], $chipMiddle, strlen($chip));

                $html .= strlen($chip) > 9 ? $chip1 . ' ' . $chip2 : $chip;
                $html .= '</td>';
                $html .= '</tr>';
            }

            $info_equipamentos = $html;

            $template_id = 56;
            $to = $clienteData->email;

            /* Notifica Usuario por Email */
            $nome = $clienteData->tipo_pessoa == 'pf' ? $clienteData->nome : $clienteData->nome_fantasia;
            $attrTemplate = array(
                "NOME_CLIENTE" => $nome,
                "SUBJECT" => $subject,
                "INFO_EQUIPAMENTOS" => $info_equipamentos
            );
            $emailData = array(
                "template_id" => $template_id,
                "to" => $to,
                "from" => array("noreplay@foccus.cc" => "acqio"),
                "attr" => $attrTemplate
            );

            \App\Utils\NotificationCenter::sendWithSendInBlue($emailData);
        }
    }

    public static function sendEmailResetPassword($user, $linkToken) {
        if ($user && $user->email) {
            /* Notifica Usuario por Email */
            $attrTemplate = array(
                "NOME_DESTINATARIO" => $user->nome,
                "MENSAGEM" => "Você solicitou a redefinição da sua senha.<br /><br />
                               Está pronto para fazer isso?<br /><br />
                               É só clicar no link abaixo e inserir sua nova senha.<br /><br />
                               <a href='{$linkToken}'>$linkToken</a>",
                "SUBJECT" => 'Alteração de senha'
            );
            $emailData = array(
                "template_id" => 50,
                "to" => $user->email,
                "from" => array("noreplay@foccus.cc" => "acqio"),
                "attr" => $attrTemplate
            );

            \App\Utils\NotificationCenter::sendWithSendInBlue($emailData);
        }
    }

    public static function sendEmailConfirmacaoCliente($clienteData) {
        if ($clienteData && $clienteData->email) {


            $subject = 'Confirmação dos dados cadastrais';

            $template_id = 57;
            $to = $clienteData->email;

            /* Notifica Usuario por Email */
            $nome = $clienteData->tipo_pessoa == 'pf' ? $clienteData->nome : $clienteData->nome_fantasia;
            $link = BASE_URL_EMAIL_CONFIRMACAO . \App\Utils\Functions::encrypt($clienteData->codigo);
            $attrTemplate = array(
                "NOME_CLIENTE" => $nome,
                "LINK_CONFIRMACAO" => $link, //'<a href="' . $link . '" style="color: #004b87; text-decoration: underline">clique aqui</a>',
                "SUBJECT" => $subject
            );
            $emailData = array(
                "template_id" => $template_id,
                "to" => $to,
                "from" => array("noreplay@foccus.cc" => "acqio"),
                "attr" => $attrTemplate
            );

            \App\Utils\NotificationCenter::sendWithSendInBlue($emailData);
        }
    }

    public static function sendTestMail($to, $template_id) {
        $emailData = array(
            "template_id" => $template_id,
            "to" => $to,
            "from" => array("noreplay@foccus.cc" => "acqio"),
            "attr" => "Teste"
        );

        return \App\Utils\NotificationCenter::sendWithSendInBlue($emailData);
    }

    public static function sendEmailWorkflowStatusChanged($workflow) {
        $pedido = $workflow->Pedido()->findOne();
        $cliente = $pedido->Cliente()->findOne();
        $franqueado = $cliente->Franqueado()->findOne();
        $fda = $franqueado->Fda()->findOne();
        $numDoc = ($cliente->tipo_pessoa == 'pf') ? Formatting::mask($cliente->cpf, '###.###.###-##') : Formatting::mask($cliente->cnpj, '##.###.###/####-##');

        $attrTemplate = array();
        if ($workflow->status == 'canceled' && SEND_MAIL_CANCELED === true) {
            $template_id = 48;
            $subject = "( Acqio ) Processo de credenciamento cancelado - {$numDoc}";
            $attrTemplate['MOTIVO'] = $workflow->motivo ? $workflow->motivo : "MOTIVO NÃO INFORMADO";
            $list['fda'] = array('nome' => $fda->razao_social, 'email' => $fda->email);
            $list['franqueado'] = array('nome' => $franqueado->razao_social, 'email' => $franqueado->email);
        } else if ($workflow->status == 'deny' && SEND_MAIL_DENY === true) {
            $subject = "( Acqio ) Processo de credenciamento negado - {$numDoc}";
            $template_id = 51;
            $attrTemplate['MOTIVO'] = $workflow->motivo ? $workflow->motivo : "MOTIVO NÃO INFORMADO";
            $list['fda'] = array('nome' => $fda->razao_social, 'email' => $fda->email);
            $list['franqueado'] = array('nome' => $franqueado->razao_social, 'email' => $franqueado->email);
        } else if ($workflow->status == 'pending' && SEND_MAIL_PENDING === true) {
            //#### NOTIFICA APENAS O FRANQUEADO ####
            $subject = "( Acqio ) Pendência no processo de credenciamento - {$numDoc}";
            $template_id = 45;
            $url = BASE_URL . 'andamento/cadastro-cliente/' . $cliente->codigo;
            $attrTemplate["LINK_TAREFA"] = $url;
            $list['franqueado'] = array('nome' => $franqueado->razao_social, 'email' => $franqueado->email);
        } else {
            return null;
        }

        /* Notificacao por Email */

        $attrTemplate["NOME_CLIENTE"] = strtoupper($cliente->nome_fantasia);
        $attrTemplate["SUBJECT"] = $subject;

        foreach ($list as $item) {
            $attrTemplate['NOME_DESTINATARIO'] = strtoupper($item['nome']);
            $emailData = array(
                "template_id" => $template_id,
                "to" => strtoupper($item['email']),
                "attr" => $attrTemplate,
            );

            \App\Utils\NotificationCenter::sendWithSendInBlue($emailData);
        }
    }

    public static function sendXMLTransportadora($_emailData) {

        $mailin = new Mailin('https://api.sendinblue.com/v2.0', '6D4ydOAtJ8r32PTa');

        $attachment_ext = pathinfo($_emailData['attachment_path']);
        if ($attachment_ext['extension'] == 'csv' ||
                $attachment_ext['extension'] == 'pdf' ||
                $attachment_ext['extension'] == 'xml') {
            $attachment = array($_emailData['attachment_name'] => chunk_split(base64_encode(file_get_contents($_emailData['attachment_path']))));
        } else {
            $attachment = null;
        }


        $nome = $_emailData['nome'];
        $emailData['template_id'] = $_emailData['template_id'];
        $emailData['to'] = $_emailData['to'];
        $emailData['attachment'] = $attachment;
        $emailData['attr'] = array(
            'NOME_DESTINATARIO' => strtoupper($nome),
            'MENSAGEM' => $_emailData['html'],
            'SUBJECT' => $_emailData['subject']
        );

        \App\Utils\NotificationCenter::sendWithSendInBlue($emailData);
    }

    public static function sendWithSendInBlue($_emailData) {

        if (!defined('DISABLE_EMAIL_NOTIFICATION') || DISABLE_EMAIL_NOTIFICATION === true) {
            return null;
        }

        $mailin = new Mailin('https://api.sendinblue.com/v2.0', '6D4ydOAtJ8r32PTa');
        $links = array();

        if ($_emailData['links']) {
            $links = explode('||', $_emailData['links']);
        }

        $data = array(
            "id" => $_emailData['template_id'],
            "to" => $_emailData['to'],
            "attr" => $_emailData['attr'],
            "attachment" => $_emailData['attachment']
        );

        try {
            $retorno = $mailin->send_transactional_template($data);
            \App\Utils\Functions::logMsg(array('dados' => $data, 'retorno' => $retorno), 'info', UPLOAD_PATH . "/debug-mail.log");
            return $retorno;
        } catch (\Exception $e) {
            \App\Utils\Functions::logMsg(array('dados' => $data, 'retorno' => $e->getMessage()), 'error', UPLOAD_PATH . "/debug-mail.log");
            throw new \Exception("Não foi possível enviar o email", 500);
        }
    }

    /*
     * E-mail enviado apos a criaçao da etapa 1.
     */

    public static function sendEmailCredenciamentoCliente($cliente) {

//Desativado pois no template não tem mais essas informações.
// $numDoc = ($cliente-> tipo_pessoa == 'pf') ? Formatting::mask($cliente->cpf, '###.###.###-##') : Formatting::mask($cliente->cnpj, '##.###.###/####-##');
// $nome = $cliente->tipo_pessoa == 'pf' ? $cliente->nome : $cliente->nome_fantasia;
        $emailData['template_id'] = 55;
        $emailData['to'] = $cliente->email;
        $emailData['attr'] = array(
//            'NOME_DESTINATARIO' => strtoupper($nome),
            'SUBJECT' => 'Acqio - Recebemos o seu pedido'
        );

        \App\Utils\NotificationCenter::sendWithSendInBlue($emailData);
    }

    /*
     * E-mail enviado apos a finalizacao da etapa 6 (emissao nfe).
     */

    public static function sendEmailNfeCliente($pedido, $workflow) {

        $cliente = $pedido->Cliente()->findOne();
        $anexo = $pedido->Anexos()->filter('notDeleted')
                        ->where('modulo', 'emissao-nfe')
                        ->where('tipo_anexo', 'nfe')->findOne();

        if ($anexo && file_exists(UPLOAD_PATH . '/' . $cliente->codigo . '/' . $anexo->anexo)) {

            $numDoc = ($cliente->tipo_pessoa == 'pf') ? Formatting::mask($cliente->cpf, '###.###.###-##') : Formatting::mask($cliente->cnpj, '##.###.###/####-##');

            $subject = 'Acqio - Nota Fiscal Eletrônica';

            $link = UPLOAD_URL . '/' . $cliente->codigo . '/' . $anexo->anexo;
            $nome = $cliente->tipo_pessoa == 'pf' ? $cliente->nome : $cliente->nome_fantasia;
            $emailData['template_id'] = 58;
            $emailData['to'] = $cliente->email;
            $emailData['attr'] = array(
                'NOME_DESTINATARIO' => strtoupper($nome),
                'CPF_CNPJ' => $numDoc,
                'NUMERO_NOTA' => $pedido->numero_nfe,
                'DATA_EMISSAO' => $workflow->data_update,
                'URL_NF' => '<a href="' . $link . '" style="color: #004b87; text-decoration: underline">clique aqui</a>'
            );


            \App\Utils\NotificationCenter::sendWithSendInBlue($emailData);
        }
    }

    /*
     * E-mail enviado apos a finalizacao da etapa 7 (ativar logistica).
     */

    public static function sendEmailAtivarLogistica($pedido) {
        $cliente = $pedido->Cliente()->findOne();

        $numDoc = ($cliente->tipo_pessoa == 'pf') ? Formatting::mask($cliente->cpf, '###.###.###-##') : Formatting::mask($cliente->cnpj, '##.###.###/####-##');
        $nome = $cliente->tipo_pessoa == 'pf' ? $cliente->nome :
                $cliente->nome_fantasia;
        $emailData['template_id'] = 50;
        $emailData['to'] = $cliente->email;
        $emailData['attr'] = array(
            'NOME_DESTINATARIO' => strtoupper($nome),
            'MENSAGEM' => "Notificao referente ao envio do POS, apos finalizacao da etapa 'Ativar logistica'",
            'SUBJECT' => '[falta] Título do e-mail referente ao envio do POS para o cliente'
        );


        \App\Utils\NotificationCenter::sendWithSendInBlue($emailData);
    }

}
