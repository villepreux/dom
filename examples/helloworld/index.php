<?php

require_once(dirname(__FILE__)."/../../dom.php"); 

dom_init();

dom_output(
    html(
        head().
        body(
            h1("Hello World!").
            p("This is a Hello World example").
            
            content(pre(

'include "dom.php";
dom_init();
dom_output(html(head().body(
    h1("Hello World!").
    p("This is a Hello World example"))));'

                )).
            style(':root { --main-max-width: 400px; } body { margin: 10px } pre { text-align: left; color: grey; white-space: pre-wrap; font-size: smaller; }')


            )
        )
    );

?>