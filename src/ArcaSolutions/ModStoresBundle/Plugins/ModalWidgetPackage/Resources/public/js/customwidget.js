const drawerFunction = function(){
    var that = this;
    this.el   = $('.custom-drawer');
    this.type = "drawer-"+this.el.attr("drawer-type");

    this.checkCookie = function(){
        return Cookies.get(this.type) ? true : false;
    }

    this.setCookie = function(){
        Cookies.set(this.type, 'closed');
    }

    this.closeDrawer = function(){
        $(".close-drawer").on("click", function () {
            that.el.removeClass("open");
            that.setCookie();
            $("body").removeClass('modal-open');
        });
    }

    this.openDrawer = function(){
        if (!this.checkCookie()){
            this.el.addClass('open');
            $("body").addClass('modal-open');
        } else {
            return false;
        }
    }

    this.init = function(){
        this.openDrawer();
        this.closeDrawer();
    }

    this.init();
}

new drawerFunction();