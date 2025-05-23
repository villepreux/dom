<?php require_once("dom.php"); 

// Imports
use function dom\{set,get,init,output,HSTART,HERE,HSTOP,path,html,rss,jsonfeed,head,head_boilerplate,link_style,css_light_dark_switch,body,header,footer,script,style,main,article,h2,h3,h4,lorem_ipsum,p,a,svg_rss,svg_facebook,url_img_random}; // Page
use function dom\{grid,card,card_title,card_media,card_text,img,cards_async,url_img_loading}; // Image cards
use function dom\{toolbar,toolbar_styles,toolbar_nav_title,toolbar_nav_toolbar,toolbar_banner,toolbar_nav,toolbar_nav_menu,ul_menu_auto,url_void,svg_darkandlight}; // Header toolbar

init();

function flickr_placeholder($classnames)
{
    return grid(str_repeat(card(card_media(img(url_img_loading()))), 12), $classnames);
}

output(
    html( // HTML document
        head(
            head_boilerplate().
            toolbar_styles().
            link_style(path("css/highlight/styles/default.min.css"), "screen", true).
            style(css_light_dark_switch(
                file_get_contents(path("css/highlight/styles/a11y-light.min.css")),
                file_get_contents(path("css/highlight/styles/a11y-dark.min.css")),
                true
                )).
            "").
        body(
            toolbar(
                toolbar_banner().
                toolbar_nav(
                    toolbar_nav_menu(ul_menu_auto()).
                    toolbar_nav_title("Hello World!").
                    toolbar_nav_toolbar(ul_menu_auto().a(svg_darkandlight(), url_void(), "darkandlight")))).
            main( // My main content section
                article( // Some random content
                    h2("First Headline").
                    lorem_ipsum()).
                article(
                    header(
                        h2("Cards section").
                        p(date("d/m/Y")." - Cards section")).
                    h3("Social networks").
                        p("Flickr #tokyo #neons").
                        grid(
                            cards_async("socials", "thumbs", array("flickr:#tokyo,neons"), "", false, false, "self", array(-1, "flickr_placeholder"))). // Loading flickr thumbs
                    h3("Examples").
                        grid(str_repeat(
                            card(
                                card_title(h4("We love cards")).
                                card_media(img(url_img_random("Random image", 300, 200))).
                                card_text(
                                    p("Cards seem to be a popular web component nowadays.").
                                    p("So we got it. And we also got social networks accounts cards pulling."))), 
                            4))).
                article( // Some more random content
                    h2("Third Headline").
                    lorem_ipsum().
                    this(h3("Source code of this example"), "card").
                    p(a("Back to examples", "..")))).
            footer(
                p("Image &copy; Unsplash via ".a("Picsum", "https://picsum.photos")).
                p("DOM.PHP v".dom\version." - This is my footer at the bottom").
                p(a(svg_rss(), "?rss").a(svg_facebook(), "https://www.facebook.com/my_facebook")))).

        /* Some inline style & scripts (Could have been put in css/main.css and js/app.js) */

        /* Color scheme switcher script */

        script( (function () { HSTART() ?><script><?= HERE() ?>

            dom.on_ready(function() {

                /* Colors contrast utilities */

                function getsRGB(c)         { c = parseInt(c, 16) / 255; c = (c <= 0.03928) ? c / 12.92 : Math.pow(((c + 0.055) / 1.055), 2.4); return c; }
                function hex(x)             { return ("0" + parseInt(x).toString(16)).slice(-2); }
                function rgb_to_hex(c)      { if (c.search("rgba") >= 0) { c = c.match(/^rgba\((\d+),\s*(\d+),\s*(\d+),\s*(\d+)\)$/); return "#" + hex(c[1]) + hex(c[2]) + hex(c[3]); } if (c.search("rgb")  >= 0) { c = c.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/); return "#" + hex(c[1]) + hex(c[2]) + hex(c[3]); } return c; }
                function get_luminosity(c)  { c = rgb_to_hex(c); return (0.2126 * getsRGB(c.substr(1, 2)) + 0.7152 * getsRGB(c.substr(3, 2)) + 0.0722 * getsRGB(c.substr(  -2)) ); }

                /* Color scheme switch */
        
                document.querySelectorAll(".darkandlight").forEach(function(e) { e.addEventListener("click", function() {
            
                    var background_color = window.getComputedStyle(document.querySelector("body")).getPropertyValue("background-color");
                    var is_dark = get_luminosity(background_color) < 0.5;
                    document.documentElement.setAttribute("data-theme", is_dark ? "light" : "dark");
        
                }); });
            });

            <?= HERE("raw_js") ?></script><?php return HSTOP(); } )()).

        /* Currently scrolled nav link style & script */

        style( (function () { HSTART() ?><style><?= HERE() ?>

                .toolbar:not(.toolbar-duplicate) .toolbar-row-nav .toolbar-cell-right a.current {

                    text-decoration: underline;
                }

            <?= HERE("raw_css") ?></style><?php return HSTOP(); } )()).

        script( (function () { HSTART() ?><script><?= HERE() ?>
        
            dom.on_ready(function() {

                function update_current_link_from_scroll() {

                    var current_link = null;
                    
                    document.querySelectorAll(".toolbar:not(.toolbar-duplicate) .toolbar-row-nav .toolbar-cell-right a").forEach(function(e) {

                        if (e.hash != "" && e.hash != "#!") {
                            
                            if (current_link == null) current_link = e;

                            var x = document.querySelector(e.hash);

                            if (x) {

                                var scrollMargin = 16 + parseInt(window.getComputedStyle(x).getPropertyValue("scroll-margin-top").replace("px",""));
                                
                                if (window.scrollY >= (x.getBoundingClientRect().top + window.pageYOffset - scrollMargin)
                                ||  window.scrollY >= (document.body.offsetHeight    - window.innerHeight - scrollMargin) ) {
                                    
                                    current_link = e;
                                }
                            }
                        }
                    });
                
                    if (current_link != null) {

                        console.log(current_link);

                        document.querySelectorAll(".toolbar:not(.toolbar-duplicate) .toolbar-row-nav .toolbar-cell-right a").forEach(function(e) { e.classList.remove("current"); });                    
                        current_link.classList.add("current");
                    }
                }

                update_current_link_from_scroll();				
                window.addEventListener("scroll", update_current_link_from_scroll);            
            });
    
            <?= HERE("raw_js") ?></script><?php return HSTOP(); } )())).

    rss().       // I'm also interested in having a RSS feed...
    jsonfeed()); // ... and json-content from my content
