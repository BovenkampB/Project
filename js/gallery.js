$(document).ready(function() {
    // Image swap on hover
    $("#gallery li img").hover(function(){
        $('#main-img').attr('src',$(this).attr('src').replace('img/img/', ''));
    });
    // Image preload
    var imgSwap = [];
     $("#gallery li img").each(function(){
        imgUrl = this.src.replace('img/img/', '');
        imgSwap.push(imgUrl);
    });
    $(imgSwap).preload();
});
$.fn.preload = function() {
    this.each(function(){
        $('<img/>')[0].src = this;
    });
}