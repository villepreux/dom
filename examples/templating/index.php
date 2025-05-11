<?php include "dom.php"; use function dom\{init,HSTART,HERE,HSTOP}; init(); HSTART() ?><html><?= HERE() ?>
<html>
  <head>
    <boilerplate/>
    <style>
      .grid { --grid-default-min-width: min(300px, calc(100% - 2 * var(--gap))); }
    </style>
  </head>
  <body>
    <header>
      <h1>Hello cards!</h1>
      <p>This is a simple example with cards.</p>
    </header>
    <main>
      <h2>Headline 2</h2>
      <grid>
        <str_repeat _0="%" _1="5">
          <card>
            <title><h3>Card Title</h3></title>
            <text><p>Blah blah 1</p></text>
            <media>
              <img src="https://picsum.photos/seed/123/300/200.webp" width="300" height="200"/>
            </media>
            <text>
              <p>Blah blah 2</p>
              <p>Blah blah 3</p>
            </text>
          </card>          
        </str_repeat>        
      </grid>
      <p><a href="..">Back to examples</a></p>
      <this/>
    </main>
  </body>
</html>
<?= HERE("raw_dom", true) ?></html><?php echo HSTOP();