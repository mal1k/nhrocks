$(function () {
    var $tutorial = $("#demo"),
        tourStep = $(".tour-step");

    var tour = new Tour({
        backdrop: true,
        storage: false,
        onStart: function () {
            hideSidebar()

            return $tutorial.addClass("disabled", true);
        },
        onEnd: function () {
            showSidebar()

            $("aside.tutorial-tour, .wrapper").removeClass("toggletutorial");
        },
        onShown: function () {
            tourStep.removeClass("active");
            //Get current step and activate menu status active
            var step = tour.getCurrentStep();

            tourStep.each(function () {
                if ($(this).data("step") == step) {
                    $(this).addClass("active");
                }
            });
        },
        steps: auxStepsTutorial
    });

    $(".tutorial-text").click(function () {
        $("aside.tutorial-tour, .wrapper").addClass("toggletutorial");
        // Initialize the tour
        tour.init();
        tour.start();
    });

    $(document).on("click", "[data-tour]", function (e) {
        e.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }
        tour.restart();
    });

    tourStep.click(function () {
        var $el = $(this);
        $el.removeClass("active");
        $el.addClass("active");
        tour.goTo(parseInt($el.attr("data-step")));
    });

    $(".tour-step-end").click(function () {
        tour.end();
    });

    //Close help for places there are no tutorial, only help
    $(".close-help").click(function () {
        $(".toggletutorial").removeClass("toggletutorial");
    });
});
