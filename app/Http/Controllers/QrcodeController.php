<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class QrcodeController extends WeixinController
{
    const SALT = "acsi90cfjvPEWI9UJOQVEUij";
    public function use (Request $request, $id, $key)
    {
        $openid = $this->getOpenid($request);
        $userInfo = $this->getUserInfoByOpenid($openid);
        $signPackage = $this->getSignPackage(true);
        if(false == $userInfo)
            return view('error', ['signPackage'=>$signPackage, 'msg'=>"清先关注公众号"]);
        $id = base_convert($id, 36, 10);
        $info = DB::table("qrcode")->where('id',$id)->first();
        if(empty($info) || $info->key!==$key)
            return view('error', ['signPackage'=>$signPackage, 'msg'=>"无效二维码"]);
        if($info->used_id !== 0)
            return view('error', ['signPackage'=>$signPackage, 'msg'=>"此红包已被领取"]);
        $data = [
            'openid'=> $openid,
            'id'    => $id,
            'sign'  => md5($openid.$id.self::SALT)
        ];
        return view('use', ['signPackage'=>$signPackage, 'data'=>json_encode($data)]);
    }

    public function ajaxUse(Request $request)
    {
        $id = $request->input('id');
        $openid = $request->input('openid');
        $sign = $request->input('sign');
        if($sign !== md5($openid.$id.self::SALT))
            return json_encode(['err'=>1]);
        $data = [
            'openid'      => $openid,
            'phone'       => $request->input('phone'),
            'ip'          => $this->getIP(),
            'create_time' => time(),
        ];
        $usedId = DB::table('used')->insertGetId($data);
        $update = DB::table('qrcode')->where('id', $id)->update(['used_id'=>$userId]);
        if ($update !== 1) 
            return json_encode(['err'=>1]);
        $info = DB::table('qrcode')->where('id', $id)->first();
        if ($info->used_id !== $usedId)
            return json_encode(['err'=>1]);
        //TODO 发送红包
        //some code...
        return json_encode($data);
    }

    private function getIP()
    {
        static $realip;
        if (isset($_SERVER)){
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")){
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        return $realip;
    }
}

