<?php

    require_once(dirname(__FILE__)."/../../dom_html.php"); 
    require_once(dirname(__FILE__)."/../../dom_toolbar.php");

    use function dom\{set,get,unsplash_url_img,is_localhost,init,output,header,css_gradient,html,rss,jsonfeed,tile,head,body,style,env_add,toolbar,main,article,anchor,h1,h2,lorem_ipsum,p,grid,card,card_title,card_media,card_text,img,hr,a,footer,svg_rss,svg_facebook,aside};

    set("theme_color", "#770000"); // There are lot of configurable defaults/options

    set("unsplash_photo_id",    "Baz9Oss6Hj8"/*"ThIY-N_LLfY"*/);
    set("unsplash_photo_author","tylerhendy"/*"gabrielgm"*/);
    set("url_banner_img",       unsplash_url_img(get("unsplash_photo_id"), 1200, 800, get("unsplash_photo_author"))); // get/set can used as a helper
    set("url_card_img",         unsplash_url_img(get("unsplash_photo_id"),  300, 200, get("unsplash_photo_author"))); // get/set can used as a helper

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

    init();

    output(

        html( // HTML document

            head(). // Header. Keeping defaults.

            body(

                toolbar("Hello World!").

                main( // My main content section

                    article( // Some random content
                        h2("First Headline").
                        lorem_ipsum(3).
                        aside(p(lorem_ipsum(1))).
                        lorem_ipsum(1)
                        ).

                    article(

                        header(
                            h2("Cards section").
                            p(date("d/m/Y")." - Cards section")
                            ).

                        grid(

                            card(
                                card_title("We love cards").
                                card_media(img(get("url_card_img"))).
                                card_text(
                                    p("Cards seem to be a popular web component nowadays.").
                                    p("So we got it. And we also got social networks accounts cards pulling.")
                                    )
                                ).
                            card(
                                card_title("We love cards").
                                card_media(img(get("url_card_img"))).
                                card_text(
                                    p("Feel free to use whatever web framework css machinery to render your cards")
                                    )
                                ).
                            card(
                                card_title("We love cards").
                                card_media(img(get("url_card_img"))).
                                card_text(
                                    p("Cards seem to be a popular web component nowadays.").
                                    p("So we got it. And we also got social networks accounts cards pulling.")
                                    )
                                ).
                            card(
                                card_title("We love cards").
                                card_media(img(get("url_card_img"))).
                                card_text(
                                    p("Cards seem to be a popular web component nowadays.").
                                    p("So we got it. And we also got social networks accounts cards pulling.")
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
                        "Photo ".a("© ".get("unsplash_photo_author"), "https://unsplash.com/photos/".get("unsplash_photo_id")).".")
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
