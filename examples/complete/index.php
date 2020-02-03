<?php

    require_once("dom.php"); 

    set("my_example_img_src", "https://images.unsplash.com/photo-1445586831130-7f00f5eac0f2"); // get/set can used as a helper

    dom_init();

    dom_output(
	
        html( // HTML document

            head(). // Header. Keeping defaults.

            body(

                style("

                    @media screen and (max-width: ".env_add("main_max_width", "scrollbar_width", "content_default_margin", "content_default_margin").") { main { padding-left: var(--content-default-margin); padding-right: var(--content-default-margin); } }

                    "). // Some inline CSS for a shorter example, but of course could be defined in a separated stylesheet,
                        // which is needed in order to work well as an AMP page

                toolbar(

                    toolbar_banner().
                    toolbar_nav(
                        toolbar_nav_menu().
                        toolbar_nav_title("Hello World!").
                        toolbar_nav_toolbar(
                            a(svg_dark_and_light(24, 24, "white", false, false), url_void(), "dark-and-light", INTERNAL_LINK)					
                            )
                        )                    
                    ).

                content( // My main content section

                    article( // Some random content
                        h2("First Headline").
                        lorem_ipsum()
                        ).

                    article(

                        dom_header(
                            h2("Cards section").
                            p(date("d/m/Y")." - Cards section")
                            ).

                        h3("Social networks").

                        grid(cards_async("socials", "posts", array("instagram:mimines_et_risettes"))).

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
                        h2("3rd Headline").
                        lorem_ipsum()
                        ).

                    hr().

                        p("Photo by ".a("A. L.", "https://unsplash.com/@overdriv3", EXTERNAL_LINK)." on ".a("Unsplash", "https://unsplash.com/s/photos/red", EXTERNAL_LINK)."")
                ).

                footer(
                    p("DOM.PHP v".DOM_VERSION." - This is my footer at the bottom").
                    p(a(svg_rss(), "?rss").a(svg_facebook(), "https://www.facebook.com/my_facebook"))
                    )
                ).

                
                script('

                    $(function() {

                        $(".dark-and-light").click(function() {
                            // TODO SOMETHING MORE ROBUST
                            document.documentElement.setAttribute("data-theme", ($("main").css("color") == "rgb(221, 221, 221)") ? "light" : "dark");
                            });

                        function update_current_link_from_scroll() {
                            
                            let scroll = window.scrollY;

                            var $current_link = null;
                            var $toolbar      = $(".toolbar-cell-right a");

                            $toolbar.each(function() {

                                if (this.hash != "" && this.hash != "#!")
                                {
                                    var section = null;
                                    try { section = $(this.hash).parent().nextAll("section")[0]; } catch (e) { section = null; }

                                    if (section) {

                                        if ($current_link == null) $current_link = $(this);

                                        if ($(this.hash).offset().top <= scroll + 10 && scroll <= ($(section).offset().top + $(section).height()))
                                        {
                                            $current_link = $(this);
                                        }
                                    }
                                }
                            });
                            
                            if ($current_link != null)
                            {
                                $toolbar.removeClass("current");
                                $current_link.addClass("current");
                            }
                        }

                        update_current_link_from_scroll();				
                        window.addEventListener("scroll", update_current_link_from_scroll);
                    
                    });
            
                ')
            ).

        rss(). // I'm also interested in having a RSS feed and json-content from my content
        jsonfeed()

	    );

?>
