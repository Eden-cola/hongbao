<?php
$prefix = "/var/tmp/";
function getQr ($str) {
    $url = "http://zyx-test.yuanhuiit.cn/qrcode.php?str=".$str;
    return file_get_contents($url);
}

function mkzip ($filename, $arr) {
    $zip = new ZipArchive;
    if (!$zip->open($filename, ZipArchive::CREATE)) {
        return false;
    }
    foreach ($arr as $key => $val) {
        $qr = getQr($val);
        $zip->addFromString( $key.'.jpg', $qr);
    }
    return true;
}

$filename = "test.zip";

$arr = [
    '1'=>'aaaa',
    '2'=>'bbbb',
    '3'=>'cccc'
];

mkzip($prefix.$filename, $arr);

header("Cache-Control: public"); 
header("Content-Description: File Transfer"); 
header('Content-disposition: attachment; filename='.basename($filename)); //文件名   
header("Content-Type: application/zip"); //zip格式的   
header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
header('Content-Length: '. filesize($prefix.$filename)); //告诉浏览器，文件大小   
@readfile($prefix.$filename);

