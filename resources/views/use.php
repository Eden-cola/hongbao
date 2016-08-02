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
        <div class="weui_icon_area"><i class="weui_icon_info weui_icon_msg"></i></div>
        <div class="weui_text_area">
            <h2 class="weui_msg_title">领取红包</h2>
            <p class="weui_msg_desc">请输入正确手机号以领取红包</p>
        </div>
        <div class="weui_cells weui_cells_form">
            <div class="weui_cell">
                <div class="weui_cell_hd"><label class="weui_label">手机号</label></div>
                <div class="weui_cell_bd weui_cell_primary">
                    <input id="phone" class="weui_input" name="phone" type="number" pattern="[0-9]*" placeholder="请输入手机号"/>
                </div>
            </div>
        </div>
        <div class="weui_opr_area">
            <p class="weui_btn_area">
            <a href="javascript:;" class="weui_btn weui_btn_primary submit">领取</a>
            </p>
        </div>
    </div>
</div>
<script type="text/html" id="tpl-success">
    <div class="weui_msg">
        <div class="weui_icon_area"><i class="weui_icon_success weui_icon_msg"></i></div>
        <div class="weui_text_area">
            <h2 class="weui_msg_title">领取成功</h2>
            <p class="weui_msg_desc">您获得了由XXXX提供的NN元红包！</p>
        </div>
        <div class="weui_opr_area">
            <p class="weui_btn_area">
            <a href="javascript:;" class="weui_btn weui_btn_primary">确认</a>
            </p>
        </div>
    </div>
</script>
<script src="/dist/zepto.min.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
var configData = <?=$signPackage?>;
configData.jsApiList = [

];
wx.config(configData);
var data = <?=$data?>;
$(function(){
    $(".submit").click(function(){
        data.phone = $("phone").val();
        $.getJSON("/qr-ajax-use", data, function(json){
            console.log(json);
        });
        $("#container").html($("#tpl-success").html());
    });
});
</script>
</body>
</html>
