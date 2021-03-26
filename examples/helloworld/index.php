<?php

require_once(dirname(__FILE__)."/../../dom.php"); 

dom_init();

dom_output(

    head().
    body(
        h1("Hello World!").
        p("This is a Hello World example").
        
        content(anchor("here").pre(

'include "dom.php";
dom_init();
dom_output(head().body(
    h1("Hello World!").
    p("This is a Hello World example")));'

            )).

        style((function () { dom_heredoc_start(-3); ?><style><?= dom_heredoc_flush(null); ?>

            body { padding: 10px } 
            main { display: flex; flex-direction: column; align-items: center; justify-content: center }
            pre  { text-align: left; color: grey; white-space: pre-wrap; font-size: min(20px, max(8px, 3.7vw)); }
            
            <?php dom_heredoc_flush("raw_css"); ?></style><?php return dom_heredoc_stop(null); })())
            


        )
    );

?>