<?php include "dom.php";
use function dom\{init,output,html,head,body,style,header,main,h1,h2,p,a,grid,card,card_title,card_text,card_media,img};

init();

output(html(head().body(
    style(":root { --grid-default-min-width: 300px }").
    header(h1("Hello Cards!").p("This is a simple example with cards.")).
    main(        
        h2("Headline 2").
        grid(str_repeat(card(
            card_title(["Card Title", 3]).
            card_text(p("Blah blah 1")).
            card_media(img("https://source.unsplash.com/300x200/?chocolate&ext=.jpg")).
            card_text(p("Blah blah 2")).
            card_text(p("Blah blah 3"))), 4)).
        p(a("Back to examples", "..")).
        this()))));