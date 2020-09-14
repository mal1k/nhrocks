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

    var errorcon = $('#error-con');

    try {
        $.ajax({
            url: "https://api.airtable.com/v0/appIIH2bx44AknWhf/listing",
            type: 'GET',
            // Fetch the stored token from localStorage and set in the header
            headers: {"Authorization": "Bearer keyliqKng8eLukP2r"},
            error: function (err) {
                errorcon.text(e.message);
                console.log(err);
            },
            success: function (data) {
                var records = data.records;
                var listingDetails = {};

                records = Object.keys(records).map(function (key) {
                    listingDetails[records[key].fields.Name] = records[key].fields;
                    return records[key].fields;
                });


                var detailNames = Object.keys(listingDetails);
                var newFeatures = records.filter(function (record) {
                    return record.new === 1 || record.new === '1';
                });


                var template = '<li class="price-advantages-item" data-itemname="Additional Uploads" data-itemorder="13">' +
                    '<div class="icon icon-md"><i class="fa"></i></div>' +
                    '<div class="item-name">Additional Uploads</div>' +
                    '</li>';

                function getNewItem(name, order, active) {
                    var newTemp = $(template).clone();
                    newTemp.find('.item-name').text(name);
                    newTemp.attr('data-itemname', name);
                    newTemp.attr('data-itemorder', order);

                    if (active) {
                        newTemp.addClass('has-advantages');
                    }

                    return newTemp;
                }


                var levels = $('#listing .pricing-item');

                $.each(levels, function (index, pricingItem) {
                    var plan = $(pricingItem).find('.pricing-plan').text();
                    var items = $(pricingItem).find('.price-advantages-item');
                    $.each(items, function (index, pricingAdvantages) {
                        var itemNames = $(pricingAdvantages).find('.item-name');
                        $.each(itemNames, function (index, itemName) {
                            var name = $(itemName).text();

                            var match = detailNames.find(function (dName) {
                                return name.indexOf(dName) > -1;
                            });

                            var order = (listingDetails[match] || {}).order || 0;
                            $(itemName).closest('.price-advantages-item').attr('data-itemname', name);
                            $(itemName).closest('.price-advantages-item').attr('data-itemorder', order);
                        });
                    });

                    $.each(newFeatures, function (index, newFeature) {
                        var isActive = newFeature[plan] === 1 || newFeature[plan] === '1';
                        var newItem = getNewItem(newFeature.Name, newFeature.order, isActive);
                        $(pricingItem).find('.price-advantages').append($(newItem));
                    });
                });

                function byOrder(a, b) {
                    return $(a).data('itemorder') - $(b).data('itemorder');
                }

                $.each(levels, function (index, pricingItem) {
                    var newItems = $(pricingItem).find('.price-advantages-item').sort(byOrder);
                    $(pricingItem).find('.price-advantages-item').remove();
                    $.each(newItems, function (index, newItem) {
                        $(pricingItem).find('.price-advantages').append($(newItem));
                    });
                });

                levels.removeClass('hidden');
            }
        });
    }catch (e) {
        errorcon.text(e.message);
    }
});

function advertiseChoice(frequency){
    Cookies.set('edirectory_advertiseChoice', frequency);
}