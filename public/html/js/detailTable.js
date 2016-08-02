;(function ($) {
    $.fn.scrollTo = function (element) {
        $(this).stop(true);
        $(this).animate({scrollTop: element.offset().top}, 300);
    }
    $.fn.detailTrSlideDown = function (speed, callback) {
        var $this = $(this);
        var speed1 = speed*3/5;
        var speed2 = speed*1/5;
        var speed3 = speed*1/5;
        setTimeout(callback, speed);
        setTimeout(function(){ $this.slideDown(speed3); },0);
        setTimeout(function(){ $this.children().slideDown(speed2); }, speed3);
        setTimeout(function(){ $this.children().children().slideDown(speed1); }, speed2+speed3);
    }
    $.fn.detailTrSlideUp = function (speed, callback) {
        var $this = $(this);
        var speed1 = speed*3/5;
        var speed2 = speed*1/5;
        var speed3 = speed*1/5;
        setTimeout(callback, speed);
        setTimeout(function(){ $this.children().children().slideUp(speed1); },0);
        setTimeout(function(){ $this.children().slideUp(speed2); },speed1);
        setTimeout(function(){ $this.slideUp(speed3); },speed2+speed1);
    }
    $.fn.detailTable = function (speed) {
        $(this).find('.details').detailTrSlideUp(0);
        $(this).find(".baseinfo").click(function(){
            var id = $(this).attr('data-id');
            var $details = $(this).next();
            var otherDetails = $(this).siblings(".details").not(':hidden').not("[data-id='"+id+"']");
            var otherBaseinfo = $(this).siblings(".baseinfo").filter(':hidden').not("[data-id='"+id+"']");
            $details.detailTrSlideDown(speed, function(){
                $("body").scrollTo($details);
            });
            $(this).detailTrSlideUp(speed);
            otherBaseinfo.detailTrSlideDown(speed);
            otherDetails.detailTrSlideUp(speed);
        });
        $(this).find(".btn-showbase").click(function(){
            var $details = $(this).parents(".details");
            var id = $details.attr('data-id');
            $details.detailTrSlideUp(speed);
            $details.prev().detailTrSlideDown(speed);
        });
    }
})($);
