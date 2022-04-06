<?php

require_once dirname(__FILE__)."/../dom_html.php";

use function dom\{set,get,is_localhost,head,style,body,main,grid,card,card_title,card_media,card_text,iframe,p,a};

set("generate", false);

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

dom\init();

dom\output(head().body(
    main(grid(
        card(
            card_title("Minimal example").
            card_media(iframe("helloworld#here", false, false, 1200, 1200)).
            card_text(p("See ".a("example","helloworld")." in fullscreen"))).                
        card(
            card_title("Basic example").
            card_media(iframe("basics#here", false, false, 1200, 1200)).
            card_text(p("See ".a("example","basics")." in fullscreen"))).                
        card(
            card_title("Complete example").
            card_media(iframe("complete#here", false, false, 1200, 1200)).
            card_text(p("See ".a("example","complete")." in fullscreen")))
        )).
    style((function () { dom\heredoc_start(-3); ?><style><?= dom\heredoc_flush(null); ?>

        .body { display: flex; flex-direction: row; align-items: center; justify-content: center; padding: var(--dom-gap); }
        .grid { flex-grow: 1; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) }
        
        <?php dom\heredoc_flush("raw_css"); ?></style><?php return dom\heredoc_stop(null); })())
    ));
?>