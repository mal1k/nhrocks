function scrollToAnchor(aid) {
    var aTag = $(aid);
    $('html,body').animate({ scrollTop: aTag.offset().top }, 'slow');
}

$('#toggle-locations').on('click', function(){
    const openLabel = $(this).data('label')[0];
    const closeLabel = $(this).data('label')[1];

    $(this).prev().children().toggleClass('is-toggled');
    $(this).text(function (i, text) {
        return text === openLabel ? closeLabel : openLabel;
    });
    $(this).text() === openLabel ? scrollToAnchor('#browse-by-location') : '';
});