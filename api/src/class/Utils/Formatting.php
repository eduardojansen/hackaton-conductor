<?php

namespace App\Utils;

class Formatting {

    public static function remove_pontuation($str) {

        $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", ".", "-", chr(0));

        return str_replace($special_chars, '', $str);
    }

    public static function remove_acentos($str) {

        $from = "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ";
        $to = "aaaaeeiooouucAAAAEEIOOOUUC";

        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);

        return strtr($str, $mapping);
    }

//    public static function formatNameUpper($str) {
//
//        $str = App_Helpers_Formatting::remove_acentos($str);
//
//        return strtoupper($str);
//    }
//
//    public static function sanitize_file_name($filename) {
//        $filename_raw = $filename;
//        $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0));
//
//        $filename = str_replace($special_chars, '', $filename);
//        $filename = preg_replace('/[\s-]+/', '-', $filename);
//        $filename = trim($filename, '.-_');
//
//        $parts = explode('.', $filename);
//        $filename = array_shift($parts);
//        $extension = array_pop($parts);
//
//        foreach ((array) $parts as $part) {
//            $filename .= '.' . $part;
//        }
//
//        $filename .= '.' . $extension;
//
//        return $filename;
//    }
//
//    public static function hash_file_name($filename) {
//        $filename_raw = $filename;
//
//        $parts = explode('.', $filename);
//        $filename = md5($filename);
//        $extension = array_pop($parts);
//
//        $filename .= '.' . $extension;
//
//        return $filename;
//    }
//
//    public static function rename_file($filename, $new) {
//
//        $parts = explode('.', $filename);
//        $filename = $new;
//        $extension = array_pop($parts);
//
//        $filename .= '.' . $extension;
//
//        return $filename;
//    }
//
//    public static function format_access_level($level) {
//        switch ($level) {
//            case 'admin':
//                return 'Administrador';
//                break;
//
//            case 'fda':
//                return 'FDA';
//                break;
//
//            case 'employee_admin':
//                return 'Funcionário Administrativo';
//                break;
//
//            case 'employee_logistic':
//                return 'Funcionário Logistíca';
//                break;
//
//            case 'employee_finance':
//                return 'Funcionário Financeiro';
//                break;
//
//            case 'employee_risk':
//                return 'Funcionário Risco';
//                break;
//
//            case 'employee_credenc':
//                return 'Funcionário Credenciamento';
//                break;
//
//            case 'employee_nf':
//                return 'Funcionário NF';
//                break;
//
//            case 'employee_support':
//                return 'Funcionário Suporte';
//                break;
//
//            case 'credenc_risk':
//                return 'Credenciamento (Risco)';
//
//            default:
//                return 'Cliente';
//                break;
//        }
//    }
//
    public static function mask($val, $mask) {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }

//
//    public static function checkAccess($controller, $action = 'index', $module = 'default') {
//
//        $auth = Zend_Auth::getInstance();
//
//        if (!$auth->hasIdentity()) {
//            return false;
//        }
//
//        $acl = Zend_Registry::get('acl');
//
//        $user = $auth->getStorage()->read();
//
//        $nivel = 'guest';
//
//        switch ($user->fk_nivel) {
//            case 1:
//                $nivel = 'admin';
//                break;
//
//            case 2:
//                $nivel = 'fda';
//                break;
//
//            case 3:
//                $nivel = 'franqueado';
//                break;
//
//            case 4:
//                $nivel = 'employee_finance';
//                break;
//
//            case 5:
//                $nivel = 'employee_admin';
//                break;
//
//            case 6:
//                $nivel = 'employee_logistic';
//                break;
//
//            case 7:
//                $nivel = 'employee_risk';
//                break;
//
//            case 8:
//                $nivel = 'employee_credenc';
//                break;
//
//            case 9:
//                $nivel = 'employee_nf';
//                break;
//
//            case 10:
//                $nivel = 'employee_support';
//                break;
//
//            case 11:
//                $nivel = 'credenc_risk';
//                break;
//        }
//
//        return $acl->isAllowed($nivel, $module . ':' . $controller, $action);
//    }
//
//    public static function disabledFieldByAction($current_action, $target_action, $readonly = true) {
//
//        if ($current_action == $target_action) {
//            return ($readonly) ? 'readonly' : 'disabled="true"';
//        }
//    }
//
//    public static function disabledFieldByConditional($condition, $reference = true) {
//
//        if ($condition == $reference) {
//            return 'disabled="true"';
//        }
//    }
//
//    public static function checkFieldByConditional($condition, $reference = true) {
//
//        if ($condition == $reference) {
//            return 'checked="true"';
//        }
//    }
//
    public static function formatDateToMysql($date) {

        $date = explode('/', $date);

        return $date[2] . '-' . $date[1] . '-' . $date[0];
    }

    public static function formatMysqlToDate($date, $onlyDate = true) {
        if ($onlyDate) {
            $date = explode(' ', $date);
            $date = $date[0];
        }

        $date = explode('-', $date);

        return $date[2] . '/' . $date[1] . '/' . $date[0];
    }

    public static function populateZeroValues($base, $data) {
        foreach ($base as $value) {
            if (!isset($data[$value])) {
                $data[$value] = 0;
            }
        }
        return $data;
    }

//
//    public static function arrayToCsv(array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false) {
//        $delimiter_esc = preg_quote($delimiter, '/');
//        $enclosure_esc = preg_quote($enclosure, '/');
//
//        $output = array();
//        foreach ($fields as $field) {
//            if ($field === null && $nullToMysqlNull) {
//                $output[] = 'NULL';
//                continue;
//            }
//
//            // Enclose fields containing $delimiter, $enclosure or whitespace
//            if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
//                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
//            } else {
//                $output[] = $field;
//            }
//        }
//
//        return implode($delimiter, $output);
//    }
}

?>