<?php include "dom.php";
use function dom\{init,HSTART,HERE,HSTOP};

init();

HSTART(); ?><html><?= HERE() ?>

<html>
  <head>
    <boilerplate/>
    <style>
      :root { --grid-default-min-width: 300px }
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
        <card>
        <title><h3>Card Title</h3></title>
          <text><p>Blah blah 1</p></text>
          <media>
            <img width="300" height="200" src="https://source.unsplash.com/300x200/?chocolate&amp;ext=.jpg"/>
          </media>
          <text><p>Blah blah 2</p></text>
          <text><p>Blah blah 3</p></text>
        </card>
        <card>
          <title><h3>Card Title</h3></title>
          <text><p>Blah blah 1</p></text>
          <media>
            <img width="300" height="200" src="https://source.unsplash.com/300x200/?chocolate&amp;ext=.jpg"/>
          </media>
          <text><p>Blah blah 2</p></text>
          <text><p>Blah blah 3</p></text>
        </card>
        <card>
        <title><h3>Card Title</h3></title>
          <text><p>Blah blah 1</p></text>
          <media>
            <img width="300" height="200" src="https://source.unsplash.com/300x200/?chocolate&amp;ext=.jpg"/>
          </media>
          <text><p>Blah blah 2</p></text>
          <text><p>Blah blah 3</p></text>
        </card>
        <card>
        <title><h3>Card Title</h3></title>
          <text><p>Blah blah 1</p></text>
          <media>
            <img width="300" height="200" src="https://source.unsplash.com/300x200/?chocolate&amp;ext=.jpg"/>
          </media>
          <text><p>Blah blah 2</p></text>
          <text><p>Blah blah 3</p></text>
        </card>
      </grid>
      <p><a href="..">Back to examples</a></p>
      <this/>
    </main>
  </body>
</html>

<?= HERE("raw_dom", true) ?></html><?php echo HSTOP();