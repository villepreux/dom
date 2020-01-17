<?php

    require_once("dom.php"); 

    ######################################################################################################################################

    dom_header();

    ######################################################################################################################################

    set("theme_color", "#ff590d"); // There are lot of configurable defaults/options

    set("my_example_img_src", "https://images.unsplash.com/photo-1445586831130-7f00f5eac0f2"); // get/set can used as a helper

    ######################################################################################################################################

    dom_output(
	
        html( // HTML document

            head(). // Header. Keeping defaults.

            body(

                toolbar(h1("Hello World!")). // A toolbar, where I decide to put my level-1 headline

                style(" 

                    .toolbar>.row:first-child { background: center/cover url(".get("my_example_img_src")."); }
                    .main>.grid { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) }
                     body>.footer { background-color: dimgray }

                    "). // Some inline CSS for a shorter example, but of course could be defined in a separated .css stylesheet

                content( // My main content section

                    article( // Some random content
                        h2("Headline").
                        lorem_ipsum()
                        ).

                    grid(
                        card(
                            img(get("my_example_img_src")), "We love cards",
                            "Cards seem to be a popular web component nowadays.".
                            "So we got it. And we also got social networks accounts cards pulling."
                            ).
                        card(
                            img(get("my_example_img_src")), "We love cards",
                            "Feel free to use whatever web framework css machinery to render your cards"
                            ).
                        card(
                            img(get("my_example_img_src")), "We love cards",
                            "Cards seem to be a popular web component nowadays.".
                            "So we got it. And we also got social networks accounts cards pulling."
                            ).
                        card(
                            img(get("my_example_img_src")), "We love cards",
                            "Cards seem to be a popular web component nowadays.".
                            "So we got it. And we also got social networks accounts cards pulling."
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
                    p(a("?rss", svg_rss())." ".a("https://www.facebook.com/my_facebook", svg_facebook()))
                    )
                )
            ).

        rss(). // I'm also interested in having a RSS feed and json-content from my content
        jsonfeed()

	    );

    ######################################################################################################################################

?>
