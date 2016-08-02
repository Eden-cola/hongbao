<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
<title></title>
<link rel="stylesheet" href="/dist/weui.min.css" media="all">
<link rel="stylesheet" href="/dist/example.css" media="all">
</head>
<body ontouchstart>
<div class="container" id="container">
    <div class="weui_msg">
        <div class="weui_icon_area"><i class="weui_icon_warn weui_icon_msg"></i></div>
        <div class="weui_text_area">
        <h2 class="weui_msg_title"><?=$msg?></h2>
            <p class="weui_msg_desc">如有疑问,请联系:xxx-xxxx-xxxx</p>
        </div>
        <div class="weui_opr_area">
            <p class="weui_btn_area">
            <a href="javascript:window.close()" class="weui_btn weui_btn_default">返回</a>
            </p>
        </div>
    </div>
</div>
<script src="/dist/zepto.min.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
var configData = <?=$signPackage?>;
configData.jsApiList = [

];
wx.config(configData);
$(function(){
    $(".submit").click(function(){
        $("#container").html($("#tpl-success").html());
    });
});
</script>
</body>
</html>
