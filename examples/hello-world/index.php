<?php include "dom.php";
use function dom\{init,output,main,h1,p};
init();
output(main(
    h1("Hello World!").
    p("This is a 'Hello World' example").
    this()
    ));