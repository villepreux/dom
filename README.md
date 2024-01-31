# DOM.PHP

Web **Do**cument PHP **M**arkup & components framework

  * https://github.com/villepreux/dom
  * https://villepreux.github.io/dom


## Status

Proof Of Concept


## Intentions

### Goals

  * Writing web documents/pages quickly.
  * Using HTML known markup syntax.
  * Mainly semanticaly
  * Without having to worry about latest, state of the art, boilerplate code
  * Automaticaly generating derived content (jsonfeed, RSS, sitemap, favicons, service worker...)
  * Independently of chooosen component/styling/whatever framework (normalize vs sanitize vs reset, material vs bootstrap vs spectre vs..., react vs 11ty vs..., AMP or not,...)
  * Having access to comonly used predefined components (videos, social-media cards, maps, ...)
  * Being able to create and/or compose new components with ease
  * Compiling into fast code
  * Compiling into valid markup (HTML, CSS, JSON, AMP...)
  * Compiling into good SEO
  * Rendering well without CSS nor JS
  * Not needed JS at all if wanted or when disabled
  * Using a single language for everything (templating, css-preprocessing, ...)
  * While still allowing to inject HTML/CSS/JS anywhere at will

### How-to

  * Use PHP (Deployed everywhere. Easy to learn. Known by many. Capable of generating anything. Modern language in its latest incarnations)
  * Declarative programming
  * State of the art defaults
  * Assumes evergreen browsers


## Getting started

Why not start with examples?
The standard [Hello World](https://github.com/villepreux/dom/tree/master/examples/hello-world/index.php) one first, then more complete [examples](https://github.com/villepreux/dom/tree/master/examples).


## Known issues

  * Codebase: It's a proof of concept at this stage. So need to be rewritten. Currently has very long line lengths & extrem single-line functions use: Hard to read.
  * ~~Codebase: Naming conventions: Missing lot of lib prefixes~~ => Now having its namespace
  * Features: Social networks content scrapping: Broken in many cases => TODO : kill feature or go the API way
  * Too much default CSS => Needs cleanup while keeping out of the box nice and complete "hello world" or mardown based websites

## TODO List

  * Codebase: Refactoring: WIP: Prefix everything + provide unprefixed facade for components markup
  * Add options for CSS automatic classes naming conventions
  * Reduce boilerplate CSS size
  * Add option for CSS classes prefixing
  * Optimize server-side performances
  * Where possible, use sub-components aggregation instead of multiple parameters
  * Convert default parameters to "auto" parameters where appropriate
  * Where possible, use named, unordered & optional parameters => Upgrade to php 8 to use native named parameters?
  * Use heredoc syntax where possible
  * ~~Remove jquery internal usage~~ DONE
  * Document the code
  * Remove framework bindings for framework that are no more on top of the frameworks leaderboards
  * Design a new framework binding mechanism (would markup + classes bindings & transformations be enough?)
  * Make 11ty sample
  * Make Material Design v3 sample


----

![Build](https://github.com/villepreux/dom/workflows/Build/badge.svg)

© Antoine Villepreux 2020-20XX
