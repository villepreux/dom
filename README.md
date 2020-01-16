# DOM

Web **Do**cument PHP **M**arkup framework

https://villepreux.github.io/dom/

## Intention

  * Writing web documents quickly.
  * Using HTML markup syntax.
  * Automatization of boilerplate code.
  * Automatization of derived content (jsonfeed, RSS, ...)
  * "Crossplay"
    * Writing markup that is compatible with different frameworks (normalize vs sanitize vs reset, material vs bootstrap vs spectre vs..., AMP,...).
    * Switching framework without touching markup
  * Components
    * Provide ready-to-use component (videos, social-media cards, maps, ...)
    * Easy building of new custom-made components
  * Fast
  * Valid markup (HTML, CSS, JSON, AMP...)
  * Good automatic SEO

## Getting started

### Hello World

  * Include the framework source
  * Declare header
  * Output document

```php
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
```

### Complete example

```php
<?php

include("dom.php"); 

doc_header();

set("theme_color", "orange");

doc_output(
  
  html( // HTML document

    head(). // Header. Keeping defaults.

    body(

      toolbar(h1("Hello World!")). // A toolbar, where I decide to put my level-1 headline

      style(" 
        
        .toolbar .row:first-child { 
          
          background: center/cover url(https://images.unsplash.com/photo-1445586831130-7f00f5eac0f2);
          
          }
        
        "). // Some inline CSS for a shorter and self-contained example, 
           // but of course could be defined in a separated .css stylesheet

      content( // My main content section

        article( // Some random content
          h2("Headline 1").
          lorem_ipsum().
          "").

        article( // Some more random content
          h2("Headline 2").
          lorem_ipsum().
          "").

      ""). // This is just a convenient pattern i'm used to with this framework

      footer( // A footer at the bottom
        
        p("This is my footer at the bottom").
        p(a("?rss", svg_rss())." ".a("https://www.facebook.com/my_facebook", svg_facebook())).
        
        "").

      "")
    ).

    rss(). // I'm also interested in having a RSS feed and json-content from my content
    jsonfeed().

  "");

?>
```

## Known issues

### Coding conventions

  * Line length. Single-line functions extrem use.
  * Function naming. Sticking close to html markup vs avoiding conflicts. providing both "options" (prefix/namespace vs short html markup) ?
  * ...

### Others

WIP

## TODO List

  * Refactoring: Make a facade (without changing current user interface)
  * Options for CSS automatic classes naming conventions
  * Optimize server-side performances
  * Where possible, use sub-components aggregation instead of multiple parameters
  * Where possible, use named, unordered & optional parameters
  * Refactor of toolbar component
  * Make internal css boilerplate smaller and also optionnal

----

(c) Antoine Villepreux 2020-20XX
