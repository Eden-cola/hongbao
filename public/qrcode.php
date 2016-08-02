<?php
header("Content-type: image/jpg");
//$px = $_GET['px'];
$url = "http://s.jiathis.com/qrcode.php?url=".$_GET['str'];
//$url = "http://pan.baidu.com/share/qrcode?w={$px}&h={$px}&url=".$str;
$qr = new Imagick($url);
$qr->negateImage(true);
$qr->setImageColorspace(Imagick::COLORSPACE_CMYK);
$qr->setImageFormat('jpeg');
echo $qr;

