<?php

require_once("dom.php");
dom_header();
dom_output(html(head().body(h1("Hello World!").p("This is a Hello World example."))));

?>