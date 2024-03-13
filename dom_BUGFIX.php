<?php

require_once(__DIR__."/dom8.php");
use function dom\{set,get,is_localhost};

// TODO ------------------------------------------------------------------------>
// TODO : FIX CANONICAL PATH HACK

if (!!get("static"))
{
    set("canonical", rtrim(str_replace("www.".get("local_domain"),  get("live_domain"), get("canonical")), "/"));
    set("canonical", rtrim(str_replace(       get("local_domain"),  get("live_domain"), get("canonical")), "/"));
    set("canonical", rtrim(str_replace("http://localhost",          "https://",         get("canonical")), "/"));
    set("canonical", rtrim(str_replace("http://127.0.0.1",          "https://",         get("canonical")), "/"));
}

if (is_localhost())
{
    set("canonical", rtrim(str_replace("https://localhost", "http://localhost", get("canonical")), "/"));
    set("canonical", rtrim(str_replace("https://127.0.0.1", "http://127.0.0.1", get("canonical")), "/"));
}

set("canonical", str_replace("///", "//", get("canonical")));

//if (!!get("static"))
//{
//	set("REQUEST_URI", str_replace("/".get("local_domain")."/", "/", get("REQUEST_URI")));
//}

// TODO
// TODO ------------------------------------------------------------------------>

?>