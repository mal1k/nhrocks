
// Fix IE - Object Fit Property in images

var userAgent, ieReg, ie;
userAgent = window.navigator.userAgent;
ieReg = /msie|Trident.*rv[ :]*11\./gi;
ie = ieReg.test(userAgent);

if(ie) {
    $(".objectfit").each(function () {
        var $container = $(this),
        imgUrl = $container.find("img").prop("src");
        if (imgUrl) {
            $container.css("backgroundImage", 'url(' + imgUrl + ')').addClass("custom-objectfit");
        }
    });
}