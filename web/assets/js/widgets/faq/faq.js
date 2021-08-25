$(".button-toggle-title").on("click", function(){
    var $el = $(this).parent();
    $el.next().slideToggle(400);
    $el.toggleClass("is-open");
});
