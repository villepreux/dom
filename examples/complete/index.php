<?php

    require_once(dirname(__FILE__)."/../../dom_html.php"); 
    require_once(dirname(__FILE__)."/../../dom_toolbar.php"); 

    use function dom\{set,get,unsplash_url_img,is_localhost,init,output,css_gradient,html,rss,jsonfeed,tile,head,body,header,footer,style,env_add,toolbar,main,article,anchor,h1,h2,h3,lorem_ipsum,p,grid,card,card_title,card_media,card_text,img,hr,a,svg_rss,svg_facebook,toolbar_banner,toolbar_nav,toolbar_nav_menu,ul_menu_auto,toolbar_nav_title,toolbar_nav_toolbar,svg_darkandlight,url_void,cards_async,script};

    set("my_example_img_src", "https://images.unsplash.com/photo-1551641506-ee5bf4cb45f1"); // get/set can used as a helper

    // TODO ------------------------------------------------------------------------>
    // TODO : FIX CANONICAL PATH HACK

    if (!!get("static"))
    {
        set("canonical", rtrim(str_replace("www.villepreux.net",    "villapirorum.netlify.app", get("canonical")), "/"));
        set("canonical", rtrim(str_replace("villepreux.net",        "villapirorum.netlify.app", get("canonical")), "/"));
        set("canonical", rtrim(str_replace("http://localhost",      "https://",                 get("canonical")), "/"));
        set("canonical", rtrim(str_replace("http://127.0.0.1",      "https://",                 get("canonical")), "/"));
    }

    if (is_localhost())
    {
        set("canonical", rtrim(str_replace("https://localhost", "http://localhost", get("canonical")), "/"));
        set("canonical", rtrim(str_replace("https://127.0.0.1", "http://127.0.0.1", get("canonical")), "/"));
    }

    set("canonical", str_replace("///", "//", get("canonical")));

    // TODO
    // TODO ------------------------------------------------------------------------>

    define("TOKEN_FLICKR", "8359186a91acb42a4934c5a2c73195d1");

    init();

    output(
	
        html( // HTML document

            head(). // Header. Keeping defaults.

            body(

                style("

                    @media screen and (max-width: ".env_add("main_max_width", "scrollbar_width", "gap", "gap").") { main { padding-left: var(--gap); padding-right: var(--gap); } }

                    "). // Some inline CSS for a shorter example, but of course could be defined in a separated stylesheet,
                        // which is needed in order to work well as an AMP page

                toolbar(

                    toolbar_banner().
                    toolbar_nav(
                        toolbar_nav_menu(ul_menu_auto()).
                        toolbar_nav_title("Hello World!").
                        toolbar_nav_toolbar(ul_menu_auto().a(svg_darkandlight(), url_void(), "darkandlight", DOM_INTERNAL_LINK))
                        )
                    ).

                main( // My main content section

                    article( // Some random content
                        h2("First Headline").
                        lorem_ipsum()
                        ).

                    article(

                        header(
                            h2("Cards section").
                            p(date("d/m/Y")." - Cards section")
                            ).

                        h3("Social networks").

                        anchor("here").grid(

                            cards_async("socials", "thumbs", array("flickr:#tokyonight"))

                            ).

                        h3("Examples").

                        grid(

                            card(
                                card_title("We love cards").
                                card_media(img(get("my_example_img_src"))).
                                card_text(
                                    "Cards seem to be a popular web component nowadays.".
                                    "So we got it. And we also got social networks accounts cards pulling."
                                    )
                                ).
                            card(
                                card_title("We love cards").
                                card_media(img(get("my_example_img_src"))).
                                card_text(
                                    "Feel free to use whatever web framework css machinery to render your cards"
                                    )
                                ).
                            card(
                                card_title("We love cards").
                                card_media(img(get("my_example_img_src"))).
                                card_text(
                                    "Cards seem to be a popular web component nowadays.".
                                    "So we got it. And we also got social networks accounts cards pulling."
                                    )
                                ).
                            card(
                                card_title("We love cards").
                                card_media(img(get("my_example_img_src"))).
                                card_text(
                                    "Cards seem to be a popular web component nowadays.".
                                    "So we got it. And we also got social networks accounts cards pulling."
                                    )
                                )
                            )
                        ).

                    article( // Some more random content
                        h2("Third Headline").
                        lorem_ipsum()
                        ).

                    hr().

                    //  p("Photo by ".a("A. L.",          "https://unsplash.com/@overdriv3", DOM_EXTERNAL_LINK)." on ".a("Unsplash", "https://unsplash.com/s/photos/red",       DOM_EXTERNAL_LINK)."")
                        p("Photo by ".a("Jezael Melgoza", "https://unsplash.com/@jezael",    DOM_EXTERNAL_LINK)." on ".a("Unsplash", "https://unsplash.com/photos/VmnOaiN2P90", DOM_EXTERNAL_LINK)."")
                ).

                footer(
                    p("DOM.PHP v".DOM_VERSION." - This is my footer at the bottom").
                    p(a(svg_rss(), "?rss").a(svg_facebook(), "https://www.facebook.com/my_facebook"))
                    )
                ).

                
            script('

            console.log("test");

                dom.on_ready(function() {

                    document.querySelector(".darkandlight").addEventListener("click", function() {
                        /* TODO SOMETHING MORE ROBUST */
                        document.documentElement.setAttribute("data-colorscheme", (window.getComputedStyle(document.querySelector("main")).getPropertyValue("color") == "rgb(242, 242, 242)") ? "light" : "dark");
                        });       

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
        
                ').
            "").

        rss(). // I'm also interested in having a RSS feed and json-content from my content
        jsonfeed().
                    
	    "");

?>
