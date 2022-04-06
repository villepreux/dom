<?php

require_once dirname(__FILE__)."/dom_html.php";

use function dom\{set,get,is_localhost,init,output,markdown,path,include_file,head,body,main,style};

set("generate", false);

    // TODO ------------------------------------------------------------------------>
    // TODO : FIX CANONICAL PATH HACK

    if (!!get("static"))
    {
        set("canonical", rtrim(str_replace("www.villepreux.net",    "villapirorum.netlify.app", get("canonical")), "/"));
        set("canonical", rtrim(str_replace("villepreux.net",        "villapirorum.netlify.app", get("canonical")), "/"));
        set("canonical", rtrim(str_replace("http://localhost",      "https://",                 get("canonical")), "/"));
        set("canonical", rtrim(str_replace("http://127.0.0.1",      "https://",                 get("canonical")), "/"));
    }

    if (is_localhost())
    {
        set("canonical", rtrim(str_replace("https://localhost", "http://localhost", get("canonical")), "/"));
        set("canonical", rtrim(str_replace("https://127.0.0.1", "http://127.0.0.1", get("canonical")), "/"));
    }

    set("canonical", str_replace("///", "//", get("canonical")));

    // TODO
    // TODO ------------------------------------------------------------------------>

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
