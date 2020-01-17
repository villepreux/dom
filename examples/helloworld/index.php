<?php

require_once("dom.php");
dom_header();
dom_output(html(head().body(h1("Hello World 2!").p("This is a Hello World example."))));

?>