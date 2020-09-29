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
    };

    this.init = function(){
        var member = Cookies.get('username_members');
        var mgr = Cookies.get('username_sitemgr');

        if((member || []).length || (mgr || []).length){
            console.log('logged in users will not see popup');
            return;
        }

        var self = this;
        console.log('Waiting 10 seconds to load popup');
        setTimeout(function () {
            self.openDrawer();
            self.closeDrawer();
        }, 10000);
    }

    this.init();
}

new drawerFunction();