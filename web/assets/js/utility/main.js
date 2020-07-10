var headerFunctions = function(el) {
    this.attrs = {};
    this.headerHeight = el.height();
    this.siblingElement = el.next();
    this.topSpace = el.css("top");

    this.getAttributes = function(){
        if(el.get(0)){
            var attrs = el.get(0).attributes;

            for (var i in attrs) {
                var index = attrs[i];
                if (typeof index.nodeValue !== 'undefined') this.attrs[index.nodeName] = index.nodeValue;
            }
        }
    };

    this.setAttributes = function(attrs) {
        el.each(function(i, e) {
            var element = $(e);
            for (var attr in attrs) {
                element.attr(attr, attrs[attr]);
            }
        });
    };

    this.setElementSpaces = function() {
        this.paddingHeader = parseInt(el.find(".header-content").css("padding-bottom"));
        this.paddingElement = parseInt(this.siblingElement.css("padding-top"));
        this.siblingElement.css("padding-top", (this.headerHeight + this.paddingElement) - this.paddingHeader);
    };

    this.setElementLeadGen = function() {
        this.paddingHeader = parseInt(el.find(".header-content").css("padding-bottom"));
        this.paddingElement = parseInt(this.siblingElement.css("padding-top"));
        el.next().children('[data-type="3"]').find('.carousel-cell').css("padding-top", (this.headerHeight + this.paddingElement) - this.paddingHeader);
    }

    this.setHeaderVariation = function() {
        if(this.attrs['is-sticky'] === "true" || this.attrs['has-opacity'] === "true"){
            this.setElementSpaces();
        }
    };

    this.setLeadGenVariation = function() {
        if(this.attrs['is-sticky'] === "true" || this.attrs['has-opacity'] === "true"){
            this.setElementLeadGen();
        }
    }

    this.chagenBackgroundScroll = function(scrollOut) {
        if(this.attrs['is-sticky'] === "true" && this.attrs['has-opacity'] === "true"){
            this.setAttributes({
                'has-opacity': (scrollOut ? 'false' : 'true'),
            });

            if (this.attrs['is-inverse'] === "true"){
                this.setAttributes({
                    'is-inverse': (scrollOut ? 'true' : 'false'),
                });
            }

            if (el.prev().is('.admin-bar')) {
                el.css("top", $(".admin-bar").height());
            }
        }
    };

    this.unsetHeader = function() {
        this.setAttributes({
            'has-opacity': 'false'
        });
    };

    this.clearAttrHeader = function() {
        this.setAttributes({
            'has-opacity': 'false',
            'is-sticky': 'false'
        });
    };

    this.checkPrevElement = function() {
        if(el.prev().is('.admin-bar')){
            el.css("top", $(".admin-bar").height());
        }
    }

    this.init = function() {
        this.getAttributes();

        if ((!el.next().hasClass('hero-with-slider')) && (!el.next().hasClass('hero-leadgen'))){
            this.setHeaderVariation();
        }

        if (el.next().hasClass('hero-leadgen')){
            this.setLeadGenVariation();
        }

        if (this.attrs['is-sticky'] === "true" || this.attrs['has-opacity'] === "true") {
            this.checkPrevElement();
        }

        if (this.attrs['is-inverse'] === "true" && this.attrs['has-opacity'] === "true") {
            this.setAttributes({
                'is-inverse': 'false'
            });
        }
    };

    if (!el.next().is('.hero-default, .hero-wrapper, .hero-leadgen')) {
        this.unsetHeader();

        if($(window).width() <= 768){
            this.clearAttrHeader();
        }
    }

    this.init();
};

var MenuMore = function() {
    this.navBar = undefined;
    this.navBarItems = [];
    this.navBarMore = undefined;
    this.navBarMoreLabel = undefined;
    this.navBarMoreContent = undefined;

    this.init = function (navBar) {
        obj = this;
        this.navBar = navBar;
        this.navBarItems = [];
        this.navBar.find('.navbar-link').each(function(index, item){
            obj.navBarItems.push({
                width: $(item).outerWidth(true),
                name: $(item).html(),
                link: $(item).attr('href'),
                index: index,
                ref: $(item)
            });
        });
        this.navBarMore = this.navBar.find('.navbar-more').first();
        this.navBarMoreLabel = this.navBar.find('.more-label');
        this.navBarMoreContent = this.navBar.find('.more-content');
        this.navBarMore.removeClass('has-more');
        this.navBarMoreLabel.removeClass("is-open");
        this.navBarMoreContent.hide();
    };

    this.measure = function() {
        this.setNavBarMoreItems([]);
        this.invalidateVisibleItems([]);
        if (this.hasMore()) {
            var exceedItems = this.getNavBarMoreItems();
            this.setNavBarMoreItems(exceedItems);
            this.invalidateVisibleItems(exceedItems);
            this.navBarMore.addClass('has-more');
            // this.navBarMoreLabel.addClass("is-open");
            $(".more-content").css("right", (-$(".navbar-more").outerWidth(true)) - 12);
        } else {
            this.navBarMore.removeClass('has-more');
            this.navBarMoreLabel.removeClass("is-open");
        }
    };

    this.invalidateVisibleItems = function(invisibleItems){
        this.navBarItems.forEach(function(item){
            var includes = false;
            invisibleItems.forEach(function(invisibleItem) {
                if (invisibleItem === item) {
                    includes = true;
                    return;
                }
            });

            if (includes) {
                item.ref.hide();
            } else {
                item.ref.show();
            }
        });
    };

    this.setNavBarMoreItems = function(items){
        var content = this.navBarMore.find('.more-content');
        content.html('');

        items.forEach(function(item){
            content.append("<a href='" + item.link + "' class='more-link'>" + item.name + "</a>");
        })
    };

    this.getNavBarMoreItems = function() {
        if (this.hasMore()) {
            var navBarWidth = this.getNavBarWidth();
            var width = 0;
            var itensFitInNavBar = this.navBarItems
                .filter(function(item){
                    width += item.width;
                    return width <= navBarWidth;
                });
            return this.navBarItems.slice(itensFitInNavBar.length - 1);
        }
        return [];
    };

    this.hasMore = function(){
        return Math.floor(this.getNavBarItemsWidth()) > Math.floor(this.getNavBarWidth())
    };

    this.getNavBarItemsWidth = function() {
        if (this.navBarItems.length == 0) return;
        return this.navBarItems.reduce(function(acc, act){
            return (acc + act.width)
        }, 0);
    };

    this.getNavBarWidth = function(){
        return this.navBar.width()
    };
};

if($(window).width() >= 992){
    var menuMore = new MenuMore();
}

var headerFunctions = new headerFunctions($("header"));

$(window).on("scroll", function (){
    var headerHeight = 60;

    if($(this).scrollTop() > headerHeight){
        headerFunctions.chagenBackgroundScroll(true);
    } else {
        headerFunctions.chagenBackgroundScroll(false);
    }
});

$(window).on("resize", function(){
    if ($(window).width() >= 992 && $('.header-navbar').length > 0) {
        menuMore.measure();
    }
});

var btnReset = function ($button) {
    if ($button.length) {
        var size = $button.attr('data-size');
        var content = $button.attr('data-content');

        $button.removeClass('is-loading');
        $button.html(content);
        $button.width(size);
        $button.removeAttr('disabled');
    }
};

$(document).ready(function(){
    if ($(window).width() >= 992 && $('.header-navbar').length > 0){
        var navBar = $('.header-navbar');
        menuMore.init(navBar);
        menuMore.measure();
    }

    $('form').on('submit', function () {
        var $btn = $(this).find('.button[data-loading]');

        if ($btn.length) {
            var size = $btn.width();
            var content = $btn.html();

            $btn.attr('data-content', content);
            $btn.attr('data-size', size);
            $btn.attr('disabled', true);
            // $btn.width(size);
            $btn.addClass('is-loading');
            $btn.html($btn.attr('data-loading'));
        }
    });

    $(".search-toggler").on("click", function(){
        $(".thin-strip-base[data-type='4']").slideToggle(400);
    });

    if($(window).width() <= 768){
        if ($(".hero-default[data-type='4']").length > 0){
            $(".search-toggler").hide();
        }

        if ($("#thin-strip-search").length <= 0){
            $(".search-toggler").hide();
        }
    }

    if ($.fn.select2) {
        $("select:not(#feed)").select2({
            minimumResultsForSearch: Infinity,
        });
    }

    $(".more-label").on("click", function(){
        var el = $(this);
        if(!el.next(".more-content").is(':visible')){
            $(".more-label").next(".more-content").slideUp(400);
            $(".more-label").removeClass("is-open");
        }
        el.toggleClass("is-open");
        if($(window).width() >= 992){
            el.next(".more-content").fadeToggle("fast", function(){
                if ($(this).is(':visible'))
                    $(this).css('display','flex');
            });
        } else {
            el.next(".more-content").slideToggle(400);
        }
    });

    $(".user-button").on("click", function(){
        $(this).toggleClass("is-open");
        $(this).find(".user-content").fadeToggle(400);
    });

    $(".navbar-toggler").on("click", function(){
        $(".search-mobile").slideUp(400);
        $(".navbar-mobile").slideToggle(400, function(){
            $(".navbar-toggler").toggleClass("is-open");
        });
    });

    if($(window).width() <= 768){
        $(".footer-newsletter-toggler").on("click", function(){
            $(this).next().slideToggle(400);
        });
    }

    $(".sidebar-toggler").on("click", function () {
        $(this).next().slideToggle(400);
        $(this).find('.fa').toggleClass('fa-minus').toggleClass('fa-plus');
        $(this).toggleClass('is-closed');
    });

    $(".alert-message[is-dismissible='true']").click(function (e){
        var alertWidth = $(this).width();
        var clickedPosition = (e.pageX - $(this).position().left);

        if(clickedPosition > alertWidth){
            $(this).fadeOut();
        }
    });

    $(".categories-dropdown-toggle").on("click", function () {
        if ($(this).hasClass('centralized-dropdown-toggle')){
            $(this).fadeOut(function(){
                $(this).next().fadeIn(function(){
                    $('.card-centralized').each(function () {
                        var titleHeight = $(this).find('.title').height();
                        var contentHeight = $(this).find('.content').height();
                        var bottomValue = (contentHeight - titleHeight) * -1;
                        $(this).find('.content').css("bottom", (bottomValue - 10));
                    });
                });
            });
        } else {
            $(this).next().fadeToggle();
        }
    });
});

var lazyLoadInstance = new LazyLoad({
    elements_selector: ".lazy",
});