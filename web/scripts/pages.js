$(document).ready(function () {
    $('.addNewPageButton').on('click', function (e) {
        e.preventDefault();

        $.ajax({
            url: '../../../includes/code/pageActionAjax.php',
            data: 'action=newPage&domain_id=' + $(this).data('domain'),
            cache: false,
            processData: false,
            type: "POST"
        }).done(function (data) {
            var objData = jQuery.parseJSON(data);

            if(objData.success) {
                window.location.href = objData.redirect;
            }
        });
    });
});
