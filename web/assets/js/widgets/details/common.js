$(document).ready(function() {
    $('.details-slider').flickity({
        imagesLoaded: true,
        lazyLoad: true,
        pageDots: false,
        prevNextButtons: false,
        arrowShape: {
            x0: 25,
            x1: 55, y1: 30,
            x2: 65, y2: 20,
            x3: 45
        }
    });

    $('.details-slider-nav').flickity({
        imagesLoaded: true,
        asNavFor: '.details-slider',
        lazyLoad: true,
        pageDots: false,
        cellAlign: "left",
        arrowShape: {
            x0: 25,
            x1: 55, y1: 30,
            x2: 65, y2: 20,
            x3: 45
        }
    });
});
