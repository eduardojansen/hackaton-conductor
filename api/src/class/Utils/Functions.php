<?php

namespace App\Utils;

use Exception;
use ORM;
use DateTime;

class Functions
{

    public static $_dateFields = array('data_cadastro');

    public static function responseJSON($response, $attrs = array())
    {
        try {

            $list_http_status = array(200, 201, 202, 203, 204, 205, 206, 300, 301, 302, 303, 304, 306, 307, 308, 400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 500, 501, 502, 503, 504, 505, 511);

            $code = $attrs['code'];
            if (!in_array($code, $list_http_status)) {
                throw new \Exception($attrs['result']['message'], 501);
            }

            $newResponse = $response->withStatus($code);

            return $newResponse->withHeader('Content-type', 'application/json')
                ->write(json_encode($attrs['result'], JSON_NUMERIC_CHECK));
        } catch (Exception $ex) {
            self::exceptionJSON($response, $ex);
        }
    }

    public static function cleanInput($input)
    {
        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
        );

        $output = preg_replace($search, '', $input);

        return $output;
    }

    public static function sanitize($input)
    {
        if (is_array($input)) {
            foreach ($input as $var => $val) {
                $output[$var] = sanitize($val);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $input = stripslashes($input);
            }
            $input = Functions::cleanInput($input);
            //$output = mysqli_real_escape_string($input);
            $output = $input;
        }

        return $output;
    }

    public static function removePipeFromBeginAndEnd($address)
    {

        $result = $address;

        if (substr($address, 0, 1) == "|") {
            $result = substr_replace($address, '', 0, 1);
        }

        if (substr($result, strlen($result) - 1, 1) == "|") {
            $result = substr_replace($result, '', strlen($result) - 1, 1);
        }

        return $result;
    }

    public static function encrypt($string)
    {
        $output = false;

        $encrypt_method = "AES-256-CBC";
        $secret_key = HASH_SECRET;
        $secret_iv = HASH_SECRET;

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);


        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);

        return $output;
    }

    public static function decrypt($string)
    {
        $output = false;

        $encrypt_method = "AES-256-CBC";
        $secret_key = HASH_SECRET;
        $secret_iv = HASH_SECRET;

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);


        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);


        return $output;
    }

    public static function exceptionJSON($response, Exception $ex)
    {
        $list_http_status = array(200, 201, 202, 203, 204, 205, 206, 300, 301, 302, 303, 304, 306, 307, 308, 400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 500, 501, 502, 503, 504, 505, 511);

        $code = $ex->getCode();
        if (!in_array($code, $list_http_status)) {
            $code = 500;
        }

        $exceptionResponse = $response->withStatus($code);

        return $exceptionResponse->withHeader('Content-type', 'application/json')
            ->write(json_encode(array('errorMessage' => $ex->getMessage())));
    }

    public static function getAddressArray($row)
    {
        $address = array(
            'cep'         => $row->cep,
            'endereco'    => $row->endereco,
            'complemento' => $row->complemento,
            'numero'      => $row->numero
        );

        if ($row->bairro) {
            $bairro = self::loadBairro($row->bairro);
            $cidade = self::loadCidade($bairro['cidade']);

            $address['bairro'] = array(
                'id'   => $bairro['id'],
                'nome' => $bairro['nome']
            );

            $address['cidade'] = array(
                'id'   => $cidade['id'],
                'nome' => $cidade['nome'],
            );

            $address['uf'] = $cidade['uf'];
        }

        return $address;
    }

    public static function loadBairro($bairro_id)
    {

        $bairro = ORM::for_table('log_bairro', 'cep')
            ->select('bai_nu_sequencial', 'id')
            ->select('bai_no', 'nome')
            ->select('loc_nu_sequencial', 'cidade')
            ->find_one($bairro_id);

        $bairro->nome = utf8_encode($bairro->nome);

        return $bairro->as_array();
    }

    public static function loadCidade($cidade_id)
    {

        $cidade = ORM::for_table('log_localidade', 'cep')
            ->select('loc_nu_sequencial', 'id')
            ->select('loc_nosub', 'nome')
            ->select('ufe_sg', 'uf')
            ->find_one($cidade_id);

        $cidade->nome = utf8_encode($cidade->nome);

        return $cidade->as_array();
    }

    public static function array_to_object($d)
    {
        return is_array($d) ? (object)array_map(__METHOD__, $d) : $d;
    }

    public static function object_to_array($d)
    {
        if (is_object($d))
            $d = get_object_vars($d);

        return is_array($d) ? array_map(__FUNCTION__, $d) : $d;
    }

    // public static function loadBairro($bairro_id) {
    // 	$result = ORM::for_table('bairros')
    // 				->select('bairros.id', 'bairro_id')
    // 				->select('bairros.nome', 'bairro')
    // 				->select('cidades.id', 'cidade_id')
    // 				->select('cidades.nome', 'cidade')
    // 				->select('cidades.uf', 'uf')
    // 				->join('cidades', array('bairros.cidade', '=', 'cidades.id'))
    // 				->find_one($bairro_id);
    // 	$result->bairro = utf8_encode($result->bairro);
    // 	$result->cidade = utf8_encode($result->cidade);
    // 	return $result;
    // }
    // public static function loadCidade($cidade_id) {
    // 	$result = ORM::for_table('cidades')
    // 				->select('bai_nu_sequencial', 'id')
    // 				->select('bai_no', 'nome')
    // 				->select('loc_nu_sequencial', 'cidade')
    // 				->find_one($bairro_id);
    // 	$result->nome = utf8_encode($result->nome);
    // 	$cidade = Model::factory('Cidade')
    // 					->select('loc_nu_sequencial', 'id')
    // 					->select('loc_nosub', 'nome')
    // 					->select('ufe_sg', 'uf')
    // 					->find_one($cidade_id);
    // 		$cidade->nome = utf8_encode($cidade->nome);
    // 		return $cidade->as_array();
    // }


    public static function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
    {

        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    public static function formatField($field, $key)
    {
        if (substr($key, 0, 5) == 'data_') {
            if ($field) {
                return self::convertDateTo($field);
            }
        } elseif (strpos($key, 'cpf') !== false || strpos($key, 'cnpj') !== false) {
            if ($field) {

                $field = preg_replace("/\D+/", "", $field);

                if (strlen($field) == 11) {
                    return Formatting::mask($field, '###.###.###-##');
                } else {
                    return Formatting::mask($field, '##.###.###/####-##');
                }
            }
        } else {
            return self::checkUtf8($field);
        }

        return null;
    }

    public static function checkUtf8($string)
    {
        if (mb_detect_encoding($string, 'UTF-8', true) === false) {
            $string = utf8_decode($string);
        }

        return $string;
    }

    public static function convertDateTo($date, $_format = 'Y-m-d\TH:i:s')
    {
        $date = new DateTime($date);

        return $date->format($_format);
    }

    public static function createDir($targetDir)
    {
        if (!file_exists($targetDir)) {
            @mkdir($targetDir, 0777, true);
            @chmod($targetDir, 0777);
        }
    }

    public static function doUpload($targetDir, $params)
    {

        @set_time_limit(5 * 60);

        // Settings
        $cleanupTargetDir = true;
        $maxFileAge = 5 * 3600;

        self::createDir($targetDir);

        if (isset($params["name"])) {
            $fileName = $params["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $ext = explode('.', $fileName);
        $ext = $ext[count($ext) - 1];

        $fileName = md5($fileName) . '.' . $ext;

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        // Chunking might be enabled
        $chunk = isset($params["chunk"]) ? intval($params["chunk"]) : 0;
        $chunks = isset($params["chunks"]) ? intval($params["chunks"]) : 0;

        // Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            throw new Exception("Failed to open output stream.", 400);
//            return '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}';
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                throw new Exception("Failed to move uploaded file.", 400);
//                return '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}';
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                throw new Exception("Failed to open input stream.", 400);
//                return '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}';
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                throw new Exception("Failed to open input stream.", 400);
//                return '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}';
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off 
            rename("{$filePath}.part", $filePath);
        }

        return $fileName;

//        return '{"jsonrpc" : "2.0", "result" : ' . $fileName . ', "id" : "id"}';
    }

    public static function logMsg($dados, $level = 'info', $file)
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
        $msg = sprintf("[%s] [%s]: %s%s", $date, $levelStr, print_r($dados, true), PHP_EOL);

        file_put_contents($file, $msg, FILE_APPEND);
    }

    public static function base64ToImg($base64_string, $output_file)
    {
        $ifp = fopen($output_file, "wb");
        $data = explode(',', $base64_string);
        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);

        return $output_file;
    }

    public static function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    public static function cleanString($text)
    {
        $utf8 = array(
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u'  => 'A',
            '/[ÍÌÎÏ]/u'   => 'I',
            '/[íìîï]/u'   => 'i',
            '/[éèêë]/u'   => 'e',
            '/[ÉÈÊË]/u'   => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u'  => 'O',
            '/[úùûü]/u'   => 'u',
            '/[ÚÙÛÜ]/u'   => 'U',
            '/ç/'         => 'c',
            '/Ç/'         => 'C',
            '/ñ/'         => 'n',
            '/Ñ/'         => 'N',
            '/–/'         => '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'  => ' ', // Literally a single quote
            '/[“”«»„]/u'  => ' ', // Double quote
            '/ /'         => ' ', // nonbreaking space (equiv. to 0x160)
        );

        return preg_replace(array_keys($utf8), array_values($utf8), $text);
    }

    public static function csv_to_array($filename = '', $delimiter = ';')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \Exception("Arquivo não localizado ou não pode ser lido", 400);
        }

        $file = pathinfo($filename);

        if (!isset($file['extension']) || strtolower($file['extension']) !== 'csv') {
            throw new \Exception("Extensão do arquivo deve ser '.csv'", 400);
        }

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, null, $delimiter)) !== FALSE) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    public static function formatArray(array $array)
    {
        $arrayConverted = [];
        if (count($array) > 0) {
            foreach ($array as $key => $item) {
                $arrayConverted[$key] = self::formatField($item, $key);
            }
        }

        return $arrayConverted;
    }

}
