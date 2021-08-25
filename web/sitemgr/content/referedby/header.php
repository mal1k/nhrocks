<head>
    <title>Welcome to the Site Manager - NH Rocks</title>
    <meta name="author" content="Arca Solutions">
    <meta charset="UTF-8">
    <meta name="ROBOTS" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">


    <link rel="shortcut icon" type="image/x-icon" href="https://nhrocks.com/custom/domain_1/content_files/favicon_2.ico">
    <!-- Custom styles for this template -->
    <link href="https://nhrocks.com/sitemgr/assets/style/styles.min.1581440601.css" rel="stylesheet" type="text/css">

    <!-- start Mixpanel -->
    <script type="text/javascript" async="" src="//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js"></script>
    <script type="text/javascript">
        (function(e, a) {
            if (!a.__SV) {
                var b = window;
                try {
                    var c, l, i, j = b.location,
                        g = j.hash;
                    c = function(a, b) {
                        return (l = a.match(RegExp(b + "=([^&]*)"))) ? l[1] : null
                    };
                    g && c(g, "state") && (i = JSON.parse(decodeURIComponent(c(g, "state"))), "mpeditor" === i.action && (b.sessionStorage.setItem("_mpcehash", g), history.replaceState(i.desiredHash || "", e.title, j.pathname + j.search)))
                } catch (m) {}
                var k, h;
                window.mixpanel = a;
                a._i = [];
                a.init = function(b, c, f) {
                    function e(b, a) {
                        var c = a.split(".");
                        2 == c.length && (b = b[c[0]], a = c[1]);
                        b[a] = function() {
                            b.push([a].concat(Array.prototype.slice.call(arguments,
                                0)))
                        }
                    }
                    var d = a;
                    "undefined" !== typeof f ? d = a[f] = [] : f = "mixpanel";
                    d.people = d.people || [];
                    d.toString = function(b) {
                        var a = "mixpanel";
                        "mixpanel" !== f && (a += "." + f);
                        b || (a += " (stub)");
                        return a
                    };
                    d.people.toString = function() {
                        return d.toString(1) + ".people (stub)"
                    };
                    k = "disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
                    for (h = 0; h < k.length; h++) e(d, k[h]);
                    a._i.push([b, c, f])
                };
                a.__SV = 1.2;
                b = e.createElement("script");
                b.type = "text/javascript";
                b.async = !0;
                b.src = "undefined" !== typeof MIXPANEL_CUSTOM_LIB_URL ? MIXPANEL_CUSTOM_LIB_URL : "file:" === e.location.protocol && "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//) ? "https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js" : "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";
                c = e.getElementsByTagName("script")[0];
                c.parentNode.insertBefore(b, c)
            }
        })(document, window.mixpanel || []);
        mixpanel.init("3d4e8fa7cb2851ed6354668616c0bf92");

    </script>
    <!-- end Mixpanel -->

    <style>
        #listingSelectBox .selectize-input {
            max-height: 34px;
        }

    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <object class="chrome-extension://jffafkigfgmjafhpkoibhfefeaebmccg/" style="display: none; visibility: hidden;"></object>
</head>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="navbar-header navbar-left">
        <a id="navBrand" class="admin-logo" href="https://nhrocks.com/sitemgr/" target="_parent" title="NH Rocks" tabindex="1">
            <img src="https://nhrocks.com/custom/domain_1/content_files/img_logo.png?1629719633" alt="NH Rocks">
        </a>
        <span class="btn btn-navbar sidebar-control" tabindex="2">
            <i class="icon-uniE605"></i>
        </span>


    </div>


    <div class="navbar-slide" id="navUser" tabindex="3">
        <span class="navbar-control">
            <i class="icon-ion-ios7-gear-outline"></i>
        </span>
        <ul class="nav navbar-nav">
            <li id="navUserSites">
                <a href="https://nhrocks.com/sitemgr/sites/" tabindex="4">
                    <i class="icon-earth94"></i>Sites </a>
            </li>
            <li id="navUserAccounts">
                <a href="https://nhrocks.com/sitemgr/account/sponsor/" tabindex="5">
                    <i class="icon-ion-person-stalker"></i>Accounts </a>
            </li>
            <li id="navUserFaq">
                <a href="http://support.edirectory.com" target="_blank" tabindex="6">
                    <i class="icon-help10"></i>Support </a>
            </li>
            <li>
                <a href="https://nhrocks.com/sitemgr/logout.php" tabindex="7">
                    <i class="icon-ion-log-in"></i>Sign out </a>
            </li>
        </ul>
    </div>
</div>
