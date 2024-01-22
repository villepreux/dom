<?php require_once __DIR__."/../dom_html.php";

use function dom\{init,output,HSTART,HSTOP,HERE,set,get,is_localhost,head,style,body,main,section,article,grid,card,card_title,card_media,card_text,iframe,p,a};

set("generate", false);

init();

output(head().body(
    main(section(grid(
        card(
            card_title("Minimal example").
            card_media(iframe("hello-world#here", false, false, 1200, 1200)).
            card_text(p("See ".a("example","hello-world")." in fullscreen"))).
        card(
            card_title("Basic example").
            card_media(iframe("basics#here", false, false, 1200, 1200)).
            card_text(p("See ".a("example","basics")." in fullscreen"))).                
        card(
            card_title("Complete example").
            card_media(iframe("complete#here", false, false, 1200, 1200)).
            card_text(p("See ".a("example","complete")." in fullscreen")))
        ))).
    style((function () { HSTART(-3); ?><style><?= HERE(); ?>

        .body { display: flex; flex-direction: row; align-items: center; justify-content: center; padding: var(--gap); }
        .grid { flex-grow: 1; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); padding-block: var(--gap); }
        .card { border: 1px solid var(--theme-color); border-radius: .5em; }
        
        <?php HERE("raw_css"); ?></style><?php return HSTOP(); })())
    ));
