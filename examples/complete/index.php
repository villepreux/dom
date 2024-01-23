<?php require_once("dom.php"); 
    
use function dom\{set,get,init,output,HSTART,HERE,HSTOP,html,rss,jsonfeed,head,body,header,footer,style,env_add,toolbar,main,article,anchor,h2,h3,lorem_ipsum,p,grid,card,card_title,card_media,card_text,img,hr,a,svg_rss,svg_facebook,toolbar_banner,toolbar_nav,toolbar_nav_menu,ul_menu_auto,toolbar_nav_title,toolbar_nav_toolbar,svg_darkandlight,url_void,cards_async,script,url_img_loading};

set("unsplash", "_noSmX8Kgoo,jezar");

$unsplash          = explode(',',get("unsplash"));
$unsplash_id       = get($unsplash, 0);
$unsplash_author   = get($unsplash, 1);

dom\unsplash_url_img($unsplash_id, 300, 200, $unsplash_author);

init();

function flickr_placeholder($classnames)
{
    return grid(str_repeat(card(card_media(img(url_img_loading()))), 12), $classnames);
}

output(
    html( // HTML document
        head(). // Header. Keeping defaults.
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
                                card_title("We love cards").
                                card_media(img(dom\unsplash_url_img($unsplash_id, 300, 200, $unsplash_author))).
                                card_text(
                                    p("Cards seem to be a popular web component nowadays.").
                                    p("So we got it. And we also got social networks accounts cards pulling."))), 
                            4))).
                article( // Some more random content
                    h2("Third Headline").
                    lorem_ipsum())).
            footer(
                p(unsplash_copyrights()).
                p("DOM.PHP v".DOM_VERSION." - This is my footer at the bottom").
                p(a(svg_rss(), "?rss").a(svg_facebook(), "https://www.facebook.com/my_facebook")))).
        script( (function () { HSTART() ?><script><?= HERE() ?>

            dom.on_ready(function() {

                function getsRGB(c) {
            
                    c = parseInt(c, 16) / 255;
                    c = (c <= 0.03928) ? c / 12.92 : Math.pow(((c + 0.055) / 1.055), 2.4);
                    
                    return c;
                    }
            
                function hex(x) {
                    return ("0" + parseInt(x).toString(16)).slice(-2);
                    }
            
                function rgb_to_hex(c) {
            
                    if (c.search("rgba") >= 0) { c = c.match(/^rgba\((\d+),\s*(\d+),\s*(\d+),\s*(\d+)\)$/); return "#" + hex(c[1]) + hex(c[2]) + hex(c[3]); }
                    if (c.search("rgb")  >= 0) { c = c.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);           return "#" + hex(c[1]) + hex(c[2]) + hex(c[3]); }
            
                    return c;
                    }
            
                function get_luminosity(c) {
            
                    c = rgb_to_hex(c);
            
                    return (0.2126 * getsRGB(c.substr(1, 2)) 
                        + 0.7152 * getsRGB(c.substr(3, 2)) 
                        + 0.0722 * getsRGB(c.substr(  -2)) );
            
                    }
            
                function set_theme(theme) { 
                
                    document.documentElement.setAttribute("data-colorscheme", theme);
                
                    }
            
                document.querySelectorAll(".darkandlight").forEach(function(e) { e.addEventListener("click", function() {
            
                    var background_color = window.getComputedStyle(document.querySelector("body")).getPropertyValue("background-color");
            
                    var is_dark = get_luminosity(background_color) < 0.5;
            
                    set_theme(is_dark ? "light" : "dark");
            
                    }); });

            });
        
            dom.on_ready(function() {

                /*
                document.querySelector(".darkandlight").addEventListener("click", function() {
                    document.documentElement.setAttribute("data-colorscheme", (window.getComputedStyle(document.querySelector("main")).getPropertyValue("color") == "rgb(242, 242, 242)") ? "light" : "dark");
                    }); */

                function update_current_link_from_scroll() {
                    
                    var current_link = null;
                    
                    document.querySelectorAll(".toolbar-row-nav .toolbar-cell-right a").forEach(function(e) {

                        if (e.hash != "" && e.hash != "#!") {
                            
                            if (current_link == null) current_link = e;

                            var x = document.querySelector(e.hash);

                            if (x) {

                                var scrollMargin = 16 + parseInt(window.getComputedStyle(x).getPropertyValue("scroll-margin-top").replace("px",""));
                                
                                if (window.scrollY >= (x.getBoundingClientRect().top + window.pageYOffset - scrollMargin)
                                ||  window.scrollY >= (document.body.offsetHeight    - window.innerHeight - scrollMargin) ) 
                                
                                    current_link = e;

                                }
                            }
                        });
                    
                    if (current_link != null) {

                        document.querySelectorAll(".toolbar-row-nav .toolbar-cell-right a").forEach(function(e) {
                            e.classList.remove("current");
                            });
                        
                        current_link.classList.add("current");
                        
                        }
                    }

                update_current_link_from_scroll();				
                window.addEventListener("scroll", update_current_link_from_scroll);
            
            });
    
            <?= HERE("raw_js") ?></script><?php return HSTOP(); } )())).
    rss(). // I'm also interested in having a RSS feed...
    jsonfeed()); // ... and json-content from my content
