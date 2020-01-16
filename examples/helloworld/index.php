<?php

include("dom.php"); // include de DOM.php framework

doc_header(); // Set document header (utf8 html by default)

doc_output(html( // Output the document
  head(). // HTML standard header, with educated defaults
  body( // Then some regular content
    h1("Hello World!").
    p("This is a Hello World example.").
    "")
  ));

?>
