<?php

namespace App\Utils;

use RestClient;

class Socket
{

    private $_url = 'https://fwsocket.herokuapp.com';
    private $_api;

    public static $instance;

    /**
     * Socket constructor.
     */
    private function __construct()
    {
        $this->_api = new RestClient([
            'base_url' => $this->_url
        ]);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            $instance = new Socket();
        }

        return $instance;
    }

    public function updateCountTasks()
    {
        $result = $this->_api->get("api/update-count-tasks");

//        if($result->info->http_code == 200) {
//            vd($result->decode_response());
//        } else {
//            vd($result->info);
//        }
    }

    public function notify($data)
    {
        $result = $this->_api->post(
            "api/notify",
            json_encode($data),
            [
                'Content-Type' => 'application/json',
                'From'         => str_replace('/#!/', '', BASE_URL)
            ]);

        if($result->info->http_code == 200) {
            return $result->decode_response();
        } else {
            return $result->info;
        }
    }

}
?>