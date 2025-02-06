<?php include "dom.php";
use function dom\{init,output,main,h1,p,settings};
init();
output(main(
    h1("Hello World!").
    p("This is a 'Hello World' example").
    this().
    settings()
    ));