<?php

    require_once("dom.php"); 

    set("theme_color", "#FF590D"); // There are lot of configurable defaults/options

    set("my_example_img_src", "https://images.unsplash.com/photo-1445586831130-7f00f5eac0f2"); // get/set can used as a helper

    dom_init();

    dom_output(

        html( // HTML document

            head(). // Header. Keeping defaults.

            body(

                style("

                    :root                   { --header-min-height: 64px }

                    .a                      { text-decoration: none      }
                    .a:hover                { text-decoration: underline }

                    .toolbar-row-banner     { background: center/cover url(".get("my_example_img_src")."); }
                
                    .headline2              { ".css_gradient()." }

                    .grid                   { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) }

                    .card                   { border: 1px solid #DDD; border-radius: 4px; box-shadow: 2px 2px 7px 2px #DDD; }
                    .card-text, .card-title { padding: var(--content-default-margin); }
                    .card-title p           { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                    .card-title-sub 
                    .span-svg-wrapper       { padding-right: 10px }
                    .card .img              { width: 100% }
                    .card .hashtag          { font-size: 0.8em }

                    .footer                 { background-color: dimgray }
                    .footer a               { padding: var(--content-default-margin); }

                    .card .headline         { font-size: 1em; margin: 0px; text-overflow: ellipsis; overflow: hidden; height: 24px; }
    
                    "). // Some inline CSS for a shorter example, but of course could be defined in a separated stylesheet,
                        // which is needed in order to work well as an AMP page

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

                    p(  "Image courtesy of unsplash.com. ".
                        "Photo ".a("https://unsplash.com/photos/ThIY-N_LLfY","© Gabriel Garcia Marengo").".")
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
