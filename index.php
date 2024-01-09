<?php

require_once dirname(__FILE__)."/dom_html.php";
use function dom\{set,get,is_localhost,init,output,markdown,path,include_file,head,body,main,style};

init();

output(
    head().
    body(
        style("img[alt=\"Build\"] { width: 105px; height: 20px; }").
        main(
            markdown(
                str_replace("https://github.com/villepreux/dom/tree/master/examples", "examples", include_file(path("README.md")))
                )
            )
        )
    );
