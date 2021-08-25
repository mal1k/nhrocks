$(document).ready(function () {
    $(document).on('click', 'div.select-rating > span', function () {
        var rating = $(this).data('rating');
        var starSpans = $('div.select-rating > span');
        starSpans.each(function() {
            if($(this).data('rating') <= rating) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
        $('#formRating').val(rating);
    });

    $('.review-helpfull-vote .like').click(function () {
        var div = $(this).parent();
        var type = $(this).data('type');
        var id = $(this).data('id');

        div.find('button.active').removeClass('active');
        $(this).addClass('active');

        $.post(Routing.generate('web_rate_review', {id: id, type: type}), function (response) {
            if (response.status == 1) { // success
                $('.up-vote-count').text(response.like);
                $('.down-vote-count').text(response.dislike);
            }
        })
    });


    $(document).on("click", ".reviews-pagination > a.item-pagination", function(e){
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            success: function(response) {
                $('#review-content').html(response.reviewBlock);
            }
        });
        return false; // for good measure
    });
});