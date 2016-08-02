<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class TestController extends BaseController
{
    static public function test ()
    {
        self::triggerRequest("lot-create", ['id'=>4]);
        return "ok";
    }

    static public function triggerRequest($url)
    {
        if(!strstr($url, "://"))
            $url = self::PREFIX . $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(!empty($post)) {
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post );
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_exec($ch);
        curl_close($ch);
        return true;
    }

    static public function test1 ()
    {
        for($i=0;$i<100;$i++){
            error_log($i."\r\n", 3, '../test.log');
            sleep(1);
        }
    }
}

