<?php require_once __DIR__."/dom.php";

use function dom\{init,output,HSTART,HSTOP,HERE,set,get,is_localhost,head,style,body,main,section,article,grid,card,card_title,card_media,card_text,iframe,p,a};

set("generate", false);

init();

output(head().body(
    main(
        grid(
            card(
                card_title("Minimal").
                card_media(iframe("hello-world", false, false, 1200, 1200)).
                card_text(p("See ".a("example","hello-world")." in fullscreen"))).
            card(
                card_title("Basic").
                card_media(iframe("basics#firstheadline", false, false, 1200, 1200)).
                card_text(p("See ".a("example","basics")." in fullscreen"))).                
            card(
                card_title("Complete").
                card_media(iframe("complete#welovecards", false, false, 1200, 1200)).
                card_text(p("See ".a("example","complete")." in fullscreen")))
            ).
        section(
            p(a("Back", "..")." | ".a("More examples", "others"))
            )
        ).
    style((function () { HSTART(-3); ?><style><?= HERE(); ?>

         body { display: flex; flex-direction: row; align-items: center; justify-content: center; }
        .grid { flex-grow: 1; grid-template-columns: repeat(auto-fit, minmax(min(250px, 100%), 1fr)); padding-block: var(--gap); }
        .card { /*border: 1px solid var(--theme-color);*/ --border-radius: .5em; }
        
        /* To distinguish iframe backgrounds from our parent background surrouding the card */
        .card-media > iframe { --margin-inline: calc(0.5 * var(--gap)); margin-inline: var(--margin-inline); width: calc(100% - calc(2 * var(--margin-inline))); }
        
        <?php HERE("raw_css"); ?></style><?php return HSTOP(); })())
    ));
