<?php include "dom.php";
use function dom\{init,HSTART,HERE,HSTOP};

init();

HSTART(); ?><html><?= HERE() ?>

<html>
  <head>
    <boilerplate/>
    <style>
      .card {
        width: calc(calc(var(--max-text-width) / 3 ) - 2 * var(--gap));
        }
    </style>
  </head>
  <body>
    <main>
      <h1>Hello World!</h1>
      <p>This is the Hello World example.</p>
      <p><a href="..">Back to tests</a></p>
      <flex>
        <card>
          <title>Card Title</title>
          <text><p>Blah blah 1</p></text>
          <media>
            <img width="300" height="200" src="https://source.unsplash.com/300x200/?chocolate&amp;ext=.jpg"/>
          </media>
          <text><p>Blah blah 2</p></text>
          <text><p>Blah blah 3</p></text>
        </card>
        <card>
          <title>Card Title</title>
          <text><p>Blah blah 1</p></text>
          <media>
            <img width="300" height="200" src="https://source.unsplash.com/300x200/?chocolate&amp;ext=.jpg"/>
          </media>
          <text><p>Blah blah 2</p></text>
          <text><p>Blah blah 3</p></text>
        </card>
        <card>
          <title>Card Title</title>
          <text><p>Blah blah 1</p></text>
          <media>
            <img width="300" height="200" src="https://source.unsplash.com/300x200/?chocolate&amp;ext=.jpg"/>
          </media>
          <text><p>Blah blah 2</p></text>
          <text><p>Blah blah 3</p></text>
        </card>
        <card>
          <title>Card Title</title>
          <text><p>Blah blah 1</p></text>
          <media>
            <img width="300" height="200" src="https://source.unsplash.com/300x200/?chocolate&amp;ext=.jpg"/>
          </media>
          <text><p>Blah blah 2</p></text>
          <text><p>Blah blah 3</p></text>
        </card>
      </flex>
      <this/>
    </main>
  </body>
</html>

<?= HERE("raw_dom", true) ?></html><?php echo HSTOP();