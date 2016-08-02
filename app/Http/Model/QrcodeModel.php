<?php 

namespace App\Http\Model;

use DB;
use ZipArchive;

class QrcodeModel
{
    const SALT = "dfjOHlkdf32298y89YPyHLKJ";
    const ZIP_PATH = "../zips/";
    const QR_URL_PREFIX = "http://zyx-test.yuanhuiit.cn/$";

    static public function create ($lotId, $count, $total, $max, $min) 
    {
        $getMoney = self::getMoneyFunc($count, $total, $max, $min);
        if($getMoney == false) return false;
        $tableQrcode = DB::table('qrcode');
        //初始种子为批次ID
        $key = $lotId.time();
        while($money = $getMoney()){
            //以上次的key作为种子生成新的Key
            $key = self::randomKey($key);
            $data = [
                'key'=> $key,
                'money' => $money,
                'lot_id' => $lotId
            ];
            $tableQrcode->insert($data);
        }
        return true;
    }

    static private function randomKey ($seed)
    {
        $result = substr(md5(self::SALT. $seed), 10, 10);
        $result = base_convert($result, 16, 32);
        return $result;
    }

    static private function getMoneyFunc ($count, $total, $max, $min) 
    {
        //转为整型
        $total = intval($total*100);
        $max = intval($max*100);
        $min = intval($min*100);
        if($max<$min)
            return false;
        if($max*$count<$total)
            return false;
        if($min*$count>$total)
            return false;
        $getnum = function () use (&$count, &$total, $max, $min) {
            if($count == 1) return $total;
            if($min*$count>$total-$min) return $min;
            if($max*$count<$total+$max) return $max;
            $randmax = 2*$total/$count;
            $num = rand($min, $randmax);
            if ($num > $max) return $max;
            return $num;
        };
        $func = function () use ($getnum, &$total, &$count) {
            if($count == 0) return false;
            $num = $getnum();
            $total = $total - $num;
            $count = $count - 1;
            //将整型转为浮点型返回
            return round($num/100, 2);
        };
        return $func;
    }

    static public function mkzip ($lotId)
    {
        $keyArr = DB::table("qrcode")->where('lot_id', $lotId)->lists('key', 'id');
        $filename = self::ZIP_PATH.$lotId.".zip";
        $zip = new ZipArchive;
        if (!$zip->open($filename, ZipArchive::CREATE)) {
            return false;
        }
        foreach ($keyArr as $id => $key) {
            $qr = self::getQr(base_convert($id, 10, 36).'/'.$key);
            $zip->addFromString( $id.'.jpg', $qr);
        }
        return true;
    }

    static public function getQr ($str) 
    {
        $url = "http://zyx-test.yuanhuiit.cn/qrcode.php?str=".self::QR_URL_PREFIX.$str;
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}
