<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Http\Model\QrcodeModel;

class LotController extends AjaxController
{
    const DOWNLOAD_SALT = "EWADF;HAVCp;iehV:piOYQHUW3";
    const DOWNLOAD_EXT = 10;
    //添加批次
    static public function add (Request $request)
    {
        $data = self::check($request);
        $data['status'] = 1;
        $data['create_time'] = time();
        $lotId = DB::table("lot")->insertGetId($data);
        self::triggerRequest("lot-create", ['id'=>$lotId]);
        return self::result(1, ['msg'=>'ok']);
    }

    static public function create (Request $request)
    {
        ignore_user_abort(TRUE); //如果客户端断开连接，不会引起脚本abort.
        set_time_limit(0);//取消脚本执行延时上限
        $lotId = $request->input('id');
        $lotInfo = DB::table("lot")->where('id', $lotId)->first();
        $create = QrcodeModel::create($lotId, $lotInfo->count, $lotInfo->total, $lotInfo->max, $lotInfo->min);
        if(!$create){
            return false;
        }
        //开始生成压缩包
        $zip = QrcodeModel::mkzip($lotId);
        if($zip){
            DB::table("lot")->where('id', $lotId)->update(['status'=>2]);
        }
    }

    //获取批次列表
    static public function List (Request $request)
    {
        $data = self::check($request);
        $id = $data['id'];
        $list = DB::table("lot");
        if(!empty($id)) {
            $list = $list->where('id', '<', $id);
        }
        $list = $list->orderBy('id', 'desc')->take(50)->get();
        return self::result(1, ['list'=>$list]);
    }

    static public function getDownloadUrl (Request $request)
    {
        $data = self::check($request);
        $time = time();
        $key = self::DownloadKey($data['id'], $time);
        $url = self::PREFIX. 'download?id='. $data['id']. '&time='. $time. '&key='. $key;
        return self::result(1, ['url'=>$url]);
    }

    static private function DownloadKey ($lotId, $time, $key=null)
    {
        $trueKey = md5($lotId. $time. self::DOWNLOAD_SALT);
        if(empty($key))
            return $trueKey;
        else
            return ($trueKey == $key)? true: false;
    }

    static public function download (Request $request)
    {
        $id = $request->input('id');
        $time = $request->input('time');
        $key = $request->input('key');
        if(time()-$time<self::DOWNLOAD_EXT && self::DownloadKey($id, $time, $key)) {
            $filename = $id.".zip";
            $filepath = QrcodeModel::ZIP_PATH.$filename;
            header("Cache-Control: public"); 
            header("Content-Description: File Transfer"); 
            header('Content-disposition: attachment; filename='.basename($filename)); //文件名   
            header("Content-Type: application/zip"); //zip格式的   
            header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
            header('Content-Length: '. filesize($filepath)); //告诉浏览器，文件大小   
            @readfile($filepath);
        } else {
            echo "check key error";
        }
    }
}

