<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class AjaxController extends BaseController
{
    const SALT = 'this_is_a_random_key';
    const EXT_TIME = 3600 * 24;
    const ATTEMPT_LIMIT = 10;
    const PREFIX = "http://zyx-test.yuanhuiit.cn/";
    
    public function __construct() {
        header('Access-Control-Allow-Origin:*');
    }

    static public function signIn (Request $request)
    {
        $user = $request->input('user');
        $password = $request->input('password');
        $id = 1;
        $signTime = time();
        $token = md5($id.$signTime.self::SALT);
        return self::result(0, ['id'=>$id, 'token'=>$token, 'sign_time'=>$signTime]);
    }

    static protected function check (Request $request)
    {
        $signTime = $request->input('signTime');
        $id = $request->input('id');
        $token = $request->input('token');
        if (time() - $signTime > self::EXT_TIME)
            return false;
        if ($token !== md5($id.$signTime.self::SALT))
            return false;
        return $request->input('data');
    }

    /**
     * @params int $code 返回状态码
     * @params str $result 返回内容
     */
    static protected function result ($code, $result)
    {
        return json_encode(['code' => $code, 'result' => $result]);
    }

    static public function triggerRequest($url, $post = null)
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
}

