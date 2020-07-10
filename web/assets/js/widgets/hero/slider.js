$(document).ready(function () {
    if ($('.hero-common-slider ').attr('active-slider') == 'true') {
        var heroSlider = $('.hero-common-slider');

        heroSlider.flickity({
            autoPlay: true,
            pauseAutoPlayOnHover: true,
            wrapAround: true,
            imagesLoaded: true,
            arrowShape: {
                x0: 25,
                x1: 55, y1: 30,
                x2: 65, y2: 20,
                x3: 45
            }
        });
    }
});
