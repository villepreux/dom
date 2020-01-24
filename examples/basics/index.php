<?php

    require_once("dom.php"); 

    set("theme_color", "#ff590d"); // There are lot of configurable defaults/options

    set("my_example_img_src", "https://images.unsplash.com/photo-1445586831130-7f00f5eac0f2"); // get/set can used as a helper

    dom_header();

    dom_output(
	
        html( // HTML document

            head(). // Header. Keeping defaults.

            body(

                style("

                    .toolbar-row-banner { background: center/cover url(".get("my_example_img_src")."); }
                    .grid               { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) }
                    .footer             { background-color: dimgray }

                    "). // Some inline CSS for a shorter example, but of course could be defined in a separated stylesheet,
                        // which is needed in order to work well as an AMP page

                toolbar("Hello World!").

                content( // My main content section

                    article( // Some random content
                        h2("Headline").
                        lorem_ipsum()
                        ).

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
                        ).

                    article( // Some more random content
                        h2("Headline").
                        lorem_ipsum()
                        ).	

                    hr().

                    p(  "Image courtesy of unsplash.com. ".
                        "Photo ".a("https://unsplash.com/photos/ThIY-N_LLfY","© Gabriel Garcia Marengo").".")
                ).

                footer(
                    p("This is my footer at the bottom").
                    p(a(svg_rss(), "?rss")." ".a(svg_facebook(), "https://www.facebook.com/my_facebook"))
                    )
                )
            ).

        rss(). // I'm also interested in having a RSS feed and json-content from my content
        jsonfeed()

	    );

?>
