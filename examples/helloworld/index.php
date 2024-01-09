<?php
include "dom.php"; 
use function dom\{init,output,head,body,main,h1,p,pre,content};
init();
output(head().body(main(
    h1("Hello World!").        
    p("This is a Hello World example").
    pre(htmlentities(content("index.php")))
    )));