<?php

    require_once(dirname(__FILE__)."/../../dom.php"); 

    set("theme_color", "#FF590D"); // There are lot of configurable defaults/options

    set("unsplash_photo_id",    "ThIY-N_LLfY");
    set("url_banner_img",       url_img_unsplash(get("unsplash_photo_id"), 1200, 800, "gabrielgm")); // get/set can used as a helper
    set("url_card_img",         url_img_unsplash(get("unsplash_photo_id"),  300, 200, "gabrielgm")); // get/set can used as a helper

    dom_init();

    dom_output(
	
        html( // HTML document

            head(). // Header. Keeping defaults.

            body(

                style("

                    :root                           { --header-min-height: 64px }

                    .toolbar-row-banner             { background: center/cover url(".get("url_banner_img")."); }

                    .headline2                      { ".css_gradient()." }

                    .grid                           { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) }

                    .card                           { border: 1px solid #DDD; border-radius: 6px; box-shadow: 2px 2px 6px 2px #DDD; }
                    .card img                       { width: 100% }
                    .card-text,
                    .card-title                     { padding:      var(--dom-gap); }
                    .card .headline                 { font-size: 1em; margin: 0px;  }                    
                    .card-title-main,       
                    .card-title-sub                 { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

                    .footer                         { background-color: dimgray }
                    .footer a                       { padding: var(--dom-gap);  }

                    @media screen and (max-width: ".env_add("main_max_width", "scrollbar_width", "dom_gap", "dom_gap").") { main { padding-left: var(--dom-gap); padding-right: var(--dom-gap); } }

                    "). // Some inline CSS for a shorter example, but of course could be defined in a separated stylesheet

                toolbar("Hello World!").

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

                        grid(

                            card(
                                card_title("We love cards").
                                card_media(img(get("url_card_img"))).
                                card_text(
                                    "Cards seem to be a popular web component nowadays.".
                                    "So we got it. And we also got social networks accounts cards pulling."
                                    )
                                ).
                            card(
                                card_title("We love cards").
                                card_media(img(get("url_card_img"))).
                                card_text(
                                    "Feel free to use whatever web framework css machinery to render your cards"
                                    )
                                ).
                            card(
                                card_title("We love cards").
                                card_media(img(get("url_card_img"))).
                                card_text(
                                    "Cards seem to be a popular web component nowadays.".
                                    "So we got it. And we also got social networks accounts cards pulling."
                                    )
                                ).
                            card(
                                card_title("We love cards").
                                card_media(img(get("url_card_img"))).
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

                    p(  "Image courtesy of unsplash.com. ".
                        "Photo ".a("https://unsplash.com/photos/".get("unsplash_photo_id"), "© Gabriel Garcia Marengo").".")
                ).

                footer(
                    p("This is my footer at the bottom").
                    p(a(svg_rss(), "?rss").a(svg_facebook(), "https://www.facebook.com/my_facebook"))
                    )
                )
            ).

        rss(). // I'm also interested in having a RSS feed and json-content from my content
        jsonfeed()

	    );

?>
