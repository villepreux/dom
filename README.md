# DOM.PHP

Web **Do**cument PHP **M**arkup & components framework

  * https://github.com/villepreux/dom
  * https://villepreux.github.io/dom


## Intentions

  * Writing web documents/pages quickly.
  * Using HTML known markup syntax.
  * Without having to worry about boilerplate code
  * Automaticaly generating derived content (jsonfeed, RSS, sitemap...)
  * Independently of chooosen component/styling/whatever framework (normalize vs sanitize vs reset, material vs bootstrap vs spectre vs..., AMP or not,...)
  * Having access to comonly used predefined components (videos, social-media cards, maps, ...)
  * Being able to create and/or compose new components with ease
  * Compiling into fast code
  * Compiling into valid markup (HTML, CSS, JSON, AMP...)
  * Compiling into good SEO

## Getting started

Why not start with [examples](https://github.com/villepreux/dom/tree/master/examples)?
The standard [Hello World](https://github.com/villepreux/dom/tree/master/examples/helloworld/index.php) one first, then more complete [examples](https://github.com/villepreux/dom/tree/master/examples).


## Known issues

  * Codebase: Formating: Very long line lengths & extrem single-line functions use: Hard to read.
  * Codebase: Naming conventions: Missing lot of lib prefixes
  * Features: Social networks content scrapping: Broken in many cases


## TODO List

  * Codebase: Refactoring: WIP: Prefix everything + provide unprefixed facade for components markup
  * Add options for CSS automatic classes naming conventions
  * Reduce boilerplate CSS size
  * Add option for CSS classes prefixing
  * Optimize server-side performances
  * Where possible, use sub-components aggregation instead of multiple parameters
  * Convert default parameters to "auto" parameters where appropriate
  * Where possible, use named, unordered & optional parameters
  * Use heredoc syntax where possible
  * Remove jquery internal usage
  * Document the code


----

![Build](https://github.com/villepreux/dom/workflows/Build/badge.svg)

© Antoine Villepreux 2020-20XX
