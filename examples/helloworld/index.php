<?php

require_once(dirname(__FILE__)."/../../dom.php"); 

dom_init();

dom_output(

    head().
    body(

        h1("Hello World!").

        p("This is a Hello World example").
        
        content(anchor("here").pre((function(){HSTART(-3)?><script><?=HERE()?>

            include("dom.php");
            dom_init();
            dom_output(head().body(
                h1("Hello World!").
                p("This is a Hello World example")));

            <?=HERE("raw")?></script><?php return HSTOP();})())).

        style((function(){HSTART(-3)?><style><?=HERE()?>

            body { padding: 10px } 
            main { display: flex; flex-direction: column; align-items: center; justify-content: center }
            pre  { text-align: left; color: grey; white-space: pre-wrap; font-size: min(20px, max(8px, 3.7vw)); }

            <?=HERE("raw_css")?></style><?php return HSTOP();})())
        )
    );

?>