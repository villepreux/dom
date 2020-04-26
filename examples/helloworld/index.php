<?php

require_once(dirname(__FILE__)."/dom.php"); 
dom_init();
dom_output(html(head().body(h1("Hello World!").p("This is a Hello World example."))));

?>