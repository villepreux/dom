<?php include "dom.php";
use function dom\{init,output,html,head,body,header,main,h1,p,a,flex,card,card_title,card_text,card_media,img};

init();

output(html(
    head().
    body(header(h1("Hello Cards!")).main(        
        p("This is a simple example with cards.").
        p(a("Back to tests", "..")).
        flex(str_repeat(card(
            card_title("Card Title").
            card_text(p("Blah blah 1")).
            card_media(img("https://source.unsplash.com/300x200/?chocolate&ext=.jpg")).
            card_text(p("Blah blah 2")).
            card_text(p("Blah blah 3"))
            ), 4)).
        this()
        ))
    ));