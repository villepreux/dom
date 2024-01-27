<?php require_once(__DIR__."/../dom.php");

dom\set("script_toolbar",       true);
dom\set("script_toolbar_menu",  true);

use function dom\{delayed_component,get,at,a};

function unsplash_copyrights()
{
    return delayed_component("_unsplash_copyrights");
}

function _unsplash_copyrights()
{
    $unsplash_copyrights = get("unsplash_copyrights", array());
    if (count($unsplash_copyrights) == 0) return "";

    $photos = array();

    foreach ($unsplash_copyrights as $i => $unsplash)
    {
        $id     = trim(at($unsplash,0,false));
        $author = trim(at($unsplash,1,false));
        $source =     (at($unsplash,2,false));

        $html_photo = "";

        if (!!$id)     $html_photo .=        a("Photo #".($i+1), "https://unsplash.com/photos/".$id);
        if (!!$author) $html_photo .= " by ".a("@".$author, "https://unsplash.com/@".$author);

        if (dom\is_localhost() && !!get("debug") && !!get("debug-unsplash"))
            if (!!$source) $html_photo .= " from ".json_encode($source);

        $photos[] = $html_photo; 
    }

    $html_photos = implode(" / ", $photos);

    return "Courtesy of ".a("Unsplash", "https://unsplash.com")." ($html_photos)";
}
