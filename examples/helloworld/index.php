<?php

include("dom.php"); 

doc_header();

doc_output(html(
  head().
  body(
    h1("Hello World!").
    p("This is a Hello World example.").
    "")
  ));

?>