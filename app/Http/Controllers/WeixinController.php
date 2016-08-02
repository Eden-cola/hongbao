<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class WeixinController extends Controller
{
    const APPID = "wxf1563182e6379566";
    const SECRET = "d4624c36b6795d1d99dcf0547af5443d";
    const TIME_RAND_NUM = 60;

    private $time;

    protected function getUserInfo(Request $request)
    {
        session_start();
        //return array(); //是否屏蔽微信端打开限制

        if(isset($_SESSION['userInfo'])){
            return $_SESSION['userInfo'];
        }
        $scope = 'snsapi_userinfo';
        $code = $request->input("code");
        if(!empty($code)){
            $webAccess = $this->getWebAccess($code);
            $userInfo = $this->getNewUserInfo($webAccess->access_token, $webAccess->openid);
            if(empty($userInfo->errcode))
                $_SESSION['userInfo'] = $userInfo;
            return $userInfo;
        }
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        //dump($redirect_uri);
        //exit;
        $this->getCode($url, $scope);
    }

    protected function getOpenid(Request $request)
    {
        session_start();
        //return array(); //是否屏蔽微信端打开限制

        if(isset($_SESSION['openid'])){
            return $_SESSION['openid'];
        }
        $scope = 'snsapi_base';
        $code = $request->input('code');
        if(!empty($code)){
            $webAccess = $this->getWebAccess($code);
            if(empty($webAccess->errcode)){
                $openid = $webAccess->openid;
                $_SESSION['openid'] = $openid;
                return $openid;
            }
        }
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        //dump($redirect_uri);
        //exit;
        $this->getCode($url, $scope);
    }

    private function getNewUserInfo($accessToken,$openid)
    {
        $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$accessToken."&openid=".$openid."&lang=zh_CN";
        $result = json_decode($this->curlGetContents($url));
        return $result;
    }

    //获取jsSDK的签名包
    //$encode bool 是否直接encode成json数据
    protected function getSignPackage($encode = false) {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

       // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "debug"     => false,
            "appId"     => self::APPID,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        if($encode)
            return json_encode($signPackage);
        return $signPackage; 
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    private function getWebAccess($code)
    {
        $appid = self::APPID;
        $secret= self::SECRET;
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
        $result = json_decode($this->curlGetContents($url));
        return $result;
    }

    //getCode模块，网页端获取Code，并进一步获取openid
    private function getCode($redirect_uri, $scope){
        $appid = self::APPID;
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=".$scope."#wechat_redirect";
        header("Location:".$url);
        exit;
    }

    private function getAccessToken()
    {
        $time = $this->getTime();
        /*暂时不从缓存中读取数据
        $accessToken = S('AccessToken');
        if(!empty($accessToken['accessToken'])){
            if($accessToken['expiresTime']>$time){
                return $accessToken['accessToken'];
            }
        }
         */
        $accessToken = $this->getMysqlAccessToken();
        if(!empty($accessToken)){
            if($accessToken['expiresTime']>$time){
                //S('AccessToken', $accessToken);
                return $accessToken['accessToken'];
            }
        }
        $accessToken = $this->getNewAccessToken();
        if(!empty($accessToken['accessToken'])){
            $this->setMysqlAccessToken($accessToken);
            //S('AccessToken', $accessToken);
            return $accessToken['accessToken'];
        }
        return false;
    }
    
    private function getMysqlAccessToken()
    {
        $r = DB::table("accessToken")->orderBy('id','desc')->first();
        if(!$r) return false;
        $result['accessToken']=$r->accessToken;
        $result['expiresTime']=$r->expiresTime;
        return $result;
    }

    private function setMysqlAccessToken($accessToken)
    {
        $result = DB::table("accessToken")->insert($accessToken);
        return $result;
    }

    private function getNewAccessToken()
    {
        $appid = self::APPID;
        $secret= self::SECRET;
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
        $result = json_decode($this->curlGetContents($url));
        $accessToken = array();
        $accessToken['accessToken'] = $result->access_token;
        $expires_in = $result->expires_in;
        $accessToken['expiresTime'] = $expires_in + time();
        return $accessToken;
    }

    private function getJsApiTicket()
    {
        $time = $this->getTime();
        /*暂时不从缓存中读取数据
        $jsApiTicket = S('JsApiTicket');
        if(!empty($jsApiTicket)){
            if($jsApiTicket['expiresTime']>$time){
                return $jsApiTicket['jsApiTicket'];
            }
        }
         */
        $jsApiTicket = $this->getMysqlJsApiTicket();
        if(!empty($jsApiTicket)){
            if($jsApiTicket['expiresTime']>$time){
                //S('JsApiTicket', $jsApiTicket);
                return $jsApiTicket['jsApiTicket'];
            }
        }
        $jsApiTicket = $this->getNewJsApiTicket();
        $this->setMysqlJsApiTicket($jsApiTicket);
        //S('JsApiTicket', $jsApiTicket);
        return $jsApiTicket['jsApiTicket'];
    }
    
    private function getMysqlJsApiTicket()
    {
        $r = DB::table("jsApiTicket")->orderBy('id','desc')->first();
        if(!$r) return false;
        $result['jsApiTicket']=$r->jsApiTicket;
        $result['expiresTime']=$r->expiresTime;
        return $result;
    }

    private function setMysqlJsApiTicket($jsApiTicket)
    {
        $result = DB::table("jsApiTicket")->insert($jsApiTicket);
        return $result;
    }

    private function getNewJsApiTicket()
    {
        $accessToken = $this->getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$accessToken."&type=jsapi";
        $result = json_decode($this->curlGetContents($url));
        $jsApiTicket = array();
        $jsApiTicket['jsApiTicket'] = $result->ticket;
        $expires_in = $result->expires_in;
        $jsApiTicket['expiresTime'] = $expires_in + time();
        return $jsApiTicket;
    }

    protected function getUserInfoByOpenid($openid)
    {
        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$accessToken}&openid={$openid}&lang=zh_CN";
        $result = json_decode($this->curlGetContents($url), 1);
        if($result['subscribe'] == 0) return false;
        return $result;
    }

    //curlGetContents方法，用于替代file_get_content
    //$url:目标地址
    //$postData：可选，通过post传递的内容
    private function curlGetContents($url,$postData=null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        if ($postData) {
        　　curl_setopt($ch, CURLOPT_POST, 1);
        　　curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
    private function getTime()
    {
        if($this->time){
            return $this->time;
        }
        $time = time()+rand(0,self::TIME_RAND_NUM);
        $this->time = $time;
        return $time;
    }
}
