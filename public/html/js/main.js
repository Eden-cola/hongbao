$wrapper = function (param) {
    return $(".wrapper").find(param);
}

//注册juicer函数
juicer.register('percentage', function (num, count) {
    return (Math.round(num / count * 10000) / 100.00 + "%");// 小数点后两位百分比
})

juicer.register('date', function (timestrap) {
    var time = new Date(timestrap* 1000);
    return time.toLocaleString();
})

function showLogin () {
    $(".wrapper").html(core.tpl("login"));
    $("#form-sign-in").submit(function () {
        return false;
    });

    $("#form-sign-in").submit(function () {
        $('login-box').append(core.tpl("loading"));
        var params = {};
        $.each($(this).serializeArray(), function(index, item){
            params[item.name] = item.value;
        });
        core.signIn(params.id, params.password, function (status, msg){
            if (status) {
                $(".wrapper").trigger("signIn");
            } else {
                $('.login-box-msg').html(msg);
            }
        });
    });
};

function showCreate () {
    $wrapper(".content-wrapper").html( core.tpl("create"));
    $(".wrapper").trigger("fetchOver");
    $wrapper("#form-lot-add").submit(function () {
        var params = {};
        $.each($(this).serializeArray(), function(index, item){
            params[item.name] = item.value;
        });
        core.ajax('lot-add', params, function (json){
            core.tipError(json.result.msg);
            if(json.code == 1) {
                $wrapper(".menu-a-list").click();
            }
        });
    });
};

function showList () {
    $wrapper(".content-wrapper").html(core.tpl("loading"));
    core.ajax('lot-list', {id:0}, function(json){
        $wrapper(".content-wrapper").html(juicer(core.tpl("list"), json.result));
        $(".wrapper").trigger("fetchOver");
        $wrapper(".download").click(function () {
            var id = $(this).parents("tr").attr("data-id");
            download(id);
        });
    });
};

function download (id) {
    core.ajax('lot-getDownloadUrl', {'id':id}, function(json){
        var url = json.result.url;
        window.open(url);
    });
}

$(".wrapper").bind("signIn", function(){
    $(".wrapper").html(core.tpl("base"));
    $wrapper(".content-wrapper").html(core.tpl("loading"));
    $wrapper(".sidebar-menu").find("a").click( function(){ return false; });
    $wrapper(".menu-a-create").click( showCreate );
    $wrapper(".menu-a-list").click( showList );
    $wrapper("#a-signout").click(function(){
        core.signOut();
    });
    $wrapper('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
    });
    window.onbeforeunload = function(e){
        return confirm("确认要退出此系统？");
    }
    $wrapper(".menu-a-create").click();
});

$(".wrapper").bind("fetchOver", function () {
    $wrapper("form").submit(function(){return false;});
});

core.init({
    baseUrl:"http://zyx-test.yuanhuiit.cn",
    onSignOut: showLogin,
});

showLogin();
core.signIn(1,1);
$(".wrapper").trigger("signIn");
