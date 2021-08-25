var socialLikesButtons = {
    whatsapp: {
        popupUrl: 'whatsapp://send?text={title} - {url}'
    }
};

$(document).ready(function () {
    $(".share-icon").on("click", function () {
        if ($(window).width() >= 992) {
            $(this).find(".share-dropdown").slideToggle(400);
        } else {
            if ($(this).hasClass("share-results")) {
                var itemID = $(this).data("ref");
                $(".summary-item[data-id='" + itemID + "']").find(".share-dropdown-mobile").toggleClass("is-open");
            } else {
                $(".share-dropdown-mobile").toggleClass('is-open');
            }
        }
    });

    $(".share-dropdown-mobile .close-share").on("click", function () {
        $(".share-dropdown-mobile").removeClass("is-open");
    });

    $(".share-dropdown-mobile").on("click", function (e) {
        if (e.target == e.currentTarget) {
            $(this).removeClass('is-open');
        }
    });

    $(".share-dropdown").socialLikes();
    $(".share-dropdown-mobile").socialLikes();
    $('.details-social').socialLikes();
});