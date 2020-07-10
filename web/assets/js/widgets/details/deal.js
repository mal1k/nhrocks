$(document).ready(function () {
    var date = $('#countdown').data('date');
    $('#countdown').countdown(date).on('update.countdown', function (event) {
        $(this).html(event.strftime($.templates('#countdown-style').render()));
    });

    /*
     * Download coupon
     */
    $(document).on('click', '#download-cupom', function () {
        var dealsCoupon = document.getElementsByClassName('deals-coupon');
        var fileName = $(this).attr('data-file-name');

        html2canvas(document.querySelector(".deals-coupon"), {
            scrollX: dealsCoupon.scrollX,
            scrollY: dealsCoupon.scrollY,
            x: dealsCoupon.offsetX,
            y: dealsCoupon.offsetY,
            width: dealsCoupon.innerWidth,
            height: dealsCoupon.innerHeight,
            backgroundColor: '#808080'
        }).then(canvas => {
            download(canvas.toDataURL('image/png'), (fileName !== '' ? fileName : 'file') + '.png', 'image/png');
        });
    });

    $('#modalLogin').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });

    $(".fancybox").fancybox();
});
