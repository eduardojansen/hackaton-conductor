<?php

namespace App\Utils;

use Exception;

class Validator {

    public function __construct() {
        
    }

    public function checkRequiredFields($_requiredFields, $_fields) {

        if (!$_fields) {
            throw new \Exception('Fields ' . implode(', ', $_requiredFields) . ' are required', 400);
        }

        foreach ($_requiredFields as $value) {
            if (!array_key_exists($value, $_fields) || !$_fields[$value]) {
                throw new \Exception('Field ' . $value . ' required', 400);
            }
        }
    }

    /*
     * Validacoes de cliente
     */

    public static function clienteCpfCnpjExiste(array $cliente, $tipo = null) {
        /* Tratar duplicidade do cpf ou cnpj */
        $numero = null;
        if ($tipo) {
            if ($tipo == 'pj') {
                $numero = $cliente['cnpj'];
            } elseif ($tipo == 'pf') {
                $numero = $cliente['cpf'];
            } else {
                throw new \Exception("Tipo cliente não identificado", 409);
            }
        } else {
            if (isset($cliente['cpf_cnpj'])) {
                $numero = $cliente['cpf_cnpj'];
            }
        }

        if (!$numero) {
            throw new \Exception("CPF/CNPJ não foi informado.", 409);
        }

        $clienteEntity = new \App\Entity\Cliente();
        $hasCliente = $clienteEntity->getAll(array(
            'cpf_cnpj' => $numero,
            'codigo' => isset($cliente['codigo']) ? $cliente['codigo'] : null,
                ), array('codigo', 'nome')
        );

        if ($hasCliente) {
            throw new \Exception("CPF/CNPJ já cadastrado no sistema.", 409);
        }
        return true;
    }

    public static function clienteIsFranqueado(array $cliente, $tipo = null) {
        $numero = null;

        if ($tipo) {
            if ($tipo == 'pj') {
                $numero = $cliente['cnpj'];
            } elseif ($tipo == 'pf') {
                $numero = $cliente['cpf'];
            } else {
                throw new \Exception("Tipo cliente não identificado", 409);
            }
        }

        if (!$numero) {
            throw new \Exception("CPF/CNPJ não foi informado.", 409);
        }

        $entity = new \App\Entity\Franqueado();

        $isFranqueado = \Model::factory('Franqueado')->filter('notDeleted')
                        ->where_any_is(
                                array(
                                    array('cnpj' => $numero),
                                    array('cnpj' => Formatting::remove_pontuation($numero))
                                )
                        )->find_one();

        if ($isFranqueado) {
            throw new \Exception("Esta solicitação não é válida para FDA e Franqueado.", 409);
        }
        return true;
    }

    /**
     * Validate a date
     *
     * @param    string    $data
     * @param    string    formato
     * @return    bool
     */
    public static function validaData($data, $formato = 'DD/MM/AAAA') {

        switch ($formato) {
            case 'DD-MM-AAAA':
            case 'DD/MM/AAAA':
                list($d, $m, $a) = preg_split("/[-\.\/ ]/", $data);
                break;
            case 'AAAA/MM/DD':
            case 'AAAA-MM-DD':
                list($a, $m, $d) = preg_split("/[-\.\/ ]/", $data);
                break;
            case 'AAAA/DD/MM':
            case 'AAAA-DD-MM':
                list($a, $d, $m) = preg_split("/[-\.\/ ]/", $data);
                break;
            case 'MM-DD-AAAA':
            case 'MM/DD/AAAA':
                list($m, $d, $a) = preg_split("/[-\.\/ ]/", $data);
                break;
            case 'AAAAMMDD':
                $a = substr($data, 0, 4);
                $m = substr($data, 4, 2);
                $d = substr($data, 6, 2);
                break;
            case 'AAAADDMM':
                $a = substr($data, 0, 4);
                $d = substr($data, 4, 2);
                $m = substr($data, 6, 2);
                break;
            default:
                throw new Exception("Formato de data inválido", 400);
                break;
        }
        return checkdate($m, $d, $a);
    }

}
