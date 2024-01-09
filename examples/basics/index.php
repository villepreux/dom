<?php

// Include framework and import needed functions
require_once("dom.php");
use function dom\{set,get,unsplash_url_img,init,output,header,html,rss,jsonfeed,head,body,toolbar,main,article,h2,lorem_ipsum,p,grid,card,card_title,card_media,card_text,img,a,footer,svg_rss,svg_facebook,aside};

init(); // Initialize framework

output( // Output
    html( // HTML document
        head(). // Header. Keeping educated defaults.
        body(
            toolbar("Hello World!"). // Using the toolbar custom component
            main( // My main content section
                article( // Some random content
                    h2("First Headline").
                    lorem_ipsum(2).
                    aside(p(lorem_ipsum(0.5))).
                    lorem_ipsum(1)
                    ).
                article(
                    header(
                        h2("Cards section").
                        p(date("d/m/Y")." - Cards section")
                        ).
                    grid(str_repeat(card(
                        card_title("We love cards").
                        card_media(img(unsplash_url_img("Baz9Oss6Hj8", 300, 200, "tylerhendy"))).
                        card_text(
                            p("Cards seem to be a popular web component nowadays.").
                            p("So we got it. And we also got social networks accounts cards pulling.")
                            )
                        ), 4))
                    ).
                article( // Some more random content
                    h2("3rd Headline").
                    lorem_ipsum(3)
                    ).	
                article( // Introspection : Show the CSS used in this example
                    h2("CSS").
                    dom\pre(htmlentities(dom\content("css/main.css")))
                    ).
                article( // Introspection : Show the markup used in this example
                    h2("Markup").
                    dom\pre(htmlentities(dom\content("index.php", 666)))
                    )
            ).
            footer(
                p("This is my footer at the bottom").                    
                p(  "Image courtesy of unsplash.com. ".
                    "Photo ".a("© tylerhendy", "https://unsplash.com/photos/Baz9Oss6Hj8").".").
                p(a(svg_rss(), "?rss").a(svg_facebook(), "https://www.facebook.com/my_facebook"))
                )
            )
        ).
    rss(). // I'm also interested in having a RSS feed... 
    jsonfeed() // ...and json-content from my content
    );

?>
