$(document).ready(function() {
    $('.pricing-nav .button').on("click", function() {
        var period = $(this).data('period');
        var widgetId = $(this).data('widget-id');

        $(this).siblings('button').removeClass('is-active');
        $(this).addClass('is-active');

        if(period === 'monthly') {
            $('#yearly-'+widgetId).removeClass('is-active');
            $('#monthly-'+widgetId).addClass('is-active');
        } else if(period === 'yearly') {
            $('#monthly-'+widgetId).removeClass('is-active');
            $('#yearly-'+widgetId).addClass('is-active');
        } else {
            $('.plans-container .pricing-wrapper.is-active').removeClass('is-active');
            $('#'+period).addClass('is-active');
        }
    });

    // Princing Plans scroll buttons
    $endNext = true;
    $endPrev = true;

    $('.pricing-buttons .next').on('click', function(){
        if($endNext){
            $('.pricing-list').animate({scrollLeft:'+=500'}, 500);
            $('.pricing-buttons .next').hide();
            $('.pricing-buttons .previous').show();
            $endPrev = true;
        }

        $endNext = false;
    });

    $('.pricing-buttons .previous').on('click', function(){
        if($endPrev){
            $('.pricing-list').animate({scrollLeft:'-=500'}, 500);
            $('.pricing-buttons .previous').hide();
            $('.pricing-buttons .next').show();
            $endNext = true;
        }

        $endPrev = false;
    });
});

function advertiseChoice(frequency){
    Cookies.set('edirectory_advertiseChoice', frequency);
}