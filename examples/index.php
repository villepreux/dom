<?php

require_once dirname(__FILE__)."/../dom.php";

set("generate", false);

dom_init();

dom_output(head().body(
    content(grid(
        card(
            card_title("Minimal example").
            card_media(iframe("helloworld#here")).
            card_text(p("See ".a("example","helloworld")." in fullscreen"))).                
        card(
            card_title("Basic example").
            card_media(iframe("basics#here")).
            card_text(p("See ".a("example","basics")." in fullscreen"))).                
        card(
            card_title("Complete example").
            card_media(iframe("complete#here")).
            card_text(p("See ".a("example","complete")." in fullscreen")))
        )).
    style((function () { dom_heredoc_start(-3); ?><style><?= dom_heredoc_flush(null); ?>

        .body { display: flex; flex-direction: row; align-items: center; justify-content: center; padding: var(--dom-gap); background-color: #EEEEEE }
        .grid { flex-grow: 1; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) }
        .card { border: 1px solid gray; background-color: #FFFFFF; text-align: center; }
        
        <?php dom_heredoc_flush("raw_css"); ?></style><?php return dom_heredoc_stop(null); })())
    ));
?>