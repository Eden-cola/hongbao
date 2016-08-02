;var core = (function($){
    var config = {};
    var signInfo = {};

    function init (data) {
        $.extend(config, data);
    }

    function signIn (user, password, callback) {
        var url = config.baseUrl+"/sign-in";
        var postData = {
            user: user,
            password: password
        }
        $.ajax({
            data:postData,
            dataType:"json",
            type:"POST",
            url:url,
            success:function(json){
                if(json.code == 0) {
                    signInfo.id = json.result.id;
                    signInfo.token = json.result.token;
                    signInfo.sign_time = json.result.sign_time;
                    callback(true);
                } else {
                    signOut();
                    callback(false, json.result.msg);
                }
            },
            error:function(){
                tipError("登录异常");
            }
        });
    }
    
    function signOut () {
        signInfo = {};
        config.onSignOut();
    }

    function ajax (uri, data, callback) {
        var url = config.baseUrl+"/"+uri;
        var postData = {
            id : signInfo.id,
            token : signInfo.token,
            signTime : signInfo.sign_time,
            data: data
        }
        $.ajax({
            data:postData,
            dataType:"json",
            type:"POST",
            url:url,
            success:function (json) {
                if (json.code == 101) {
                    tipError("登录过期");
                    signOut();
                }
                callback(json);
            },
            error:function(){
                tipError("无法连接到服务器");
                config.onOffline();
            }
        });
    }

    function tpl (name, param) {
        return tpl = $('#tpl-'+name).html();
    }

    function tipError (msg, time, onShow, onClick) {
        if (typeof(time)==='undefined') {
            time = 2000;
        }
        var now = new Date().getTime();
        var id = "tip-error"+now;
        $('<p id="'+id+'" class="tip-error"><a href="">'+msg+'</a></p>').appendTo("body");
        var tip = $('#'+id);
        tip.children().click(function(){
            tip.remove();
            return false;
        });
        if (typeof (onClick)==='function') {
            tip.children().click(onClick);
        }
        if (typeof (onShow)==='function') {
            setTimeout(onShow,0);
        }
        tip.show();
        setTimeout(function(){
            tip.fadeOut(500, function(){
                tip.remove();
            });
        }, time);
    }
    

    var core = {
        init : init,
        tpl : tpl,
        ajax : ajax,
        signIn : signIn,
        signOut : signOut,
        signInfo : signInfo,
        tipError : tipError,
    };
    return core;
})($);
