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

Why not start with [examples](./examples/)?
The standard [Hello World](./examples/helloworld/index.php) one first, then more complete [examples](./examples/).

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
