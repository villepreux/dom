<?php include "dom.php"; // Our framework
use function dom\{init,output,head,body,main,h1,p,content};
init(); // Ignition
output(head().body(main( // Main layout
    h1("Hello World!"). // Title
    p("This is a 'Hello World' example"). // Paragraph
    this() // Going meta
    ))); // Done in 8 lines