<?php

require_once(__DIR__."/dom.php");
use function dom\{init,output,HSTART,HERE,HSTOP,markdown,path,include_file,head,body,main,style};

init();

output(
    head().
    body(
        style((function(){HSTART()?><style><?=HERE()?>         
            img[alt="Build"]                        { width: 105px; height: 20px; }
            img[alt="justforfunnoreally.dev badge"] { width: 136px; height: 20px; }
            <?=HERE()?></style><?php return HSTOP();})()).
        main(
            markdown(
                str_replace("20XX", date("Y"), 
                str_replace("https://github.com/villepreux/dom/tree/master/", "", 
                    include_file(path("README.md"))
                    ))
                )
            )
        )
    );
