$(".tab-navbar.is-selected").each(function(){
    $ref = $(this).attr("href").split("#")[1];
    $(".tab-content#" + $ref).addClass("is-active");

    if($ref == "deals-classifieds"){
        $(".detail-body").addClass("no-padding");
    }
});

$(".tab-navbar").on("click", function(e){
    e.preventDefault();

    if ($(window).width() < 992) {
        if ($(this).attr("href") != '#overview') {
            $(".detail-info-mobile").hide();
        } else {
            $(".detail-info-mobile").show();
        }
    }

    $(".tab-navbar").removeClass("is-selected");
    $(".tab-content").removeClass("is-active");
    $(".detail-body").removeClass("no-padding");
    $ref = $(this).attr("href").split("#")[1];
    $(this).addClass("is-selected");
    $(".tab-content#" + $ref).addClass("is-active");

    if($ref == "deals-classifieds"){
        $(".detail-body").addClass("no-padding");
    }
});

$(".all-reviews, #view-photos").on("click", function(){
    if ($(window).width() < 992) {
        $(".detail-info-mobile").hide();
    }
});

$("#view-about").on("click", function () {
    $("#long-description").slideToggle(400);
});

$("#view-photos").on("click", function(e) {
    e.preventDefault();
    $(".tab-navbar").removeClass("is-selected");
    $(".tab-content").removeClass("is-active");
    $(".detail-body").removeClass("no-padding");
    $ref = $(this).attr("href").split("#")[1];
    $(".tab-navbar[href$='#photos']").addClass("is-selected");
    $(".tab-content#" + $ref).addClass("is-active");

    if($(window).width() > 786){
        $(window).scrollTop($('.details-header-navbar').offset().top);
    } else {
        $(window).scrollTop($('.tab-content#photos').offset().top - 50);
    }
});

$(".all-reviews").on("click", function(e) {
    e.preventDefault();
    $(".tab-navbar").removeClass("is-selected");
    $(".tab-content").removeClass("is-active");
    $(".detail-body").removeClass("no-padding");
    $ref = $(this).attr("href").split("#")[1];
    $(".tab-navbar[href$='#reviews']").addClass("is-selected");
    $(".tab-content#" + $ref).addClass("is-active");
    $(window).scrollTop($('.details-header-navbar').offset().top);
});

$("#fb-comments").on("click", function() {
    $("html, body").animate({scrollTop: $('.article-categories').offset().top}, 500);
});

$(document).on("click", "reviews-pagination > a.item-pagination", function(e){
    e.preventDefault();
    $.ajax({
        url: $(this).attr('href'),
        success: function(response) {
            $('#review-content').html(response.reviewBlock);
        }
    });
    return false; // for good measure
});

$(document).ready(function () {
    $('#modalLogin').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });

    $(".fancybox").fancybox();

    $(".first-hours").on("click", function(){
        $(".hours-more").slideToggle(400);
        $(this).find(".fa").toggleClass("is-open");
    });
});