<?php

require_once(__DIR__."/../../villapirorum.php");
use function dom\{set,get,at,is_localhost,init,output,markdown,path,include_file,html,head,head_boilerplate,body,main,style,script,section,h1,p};

/**
 * Transform web component into dom component
 */
function editable_list($title = "TODO List", $items = array(), $add_new_item = "Add new list item", $attributes = false)
{
    // TODO require_head_script("js/editable-list.js");
    // TODO require_head_style("css/editable-list.css");
    $list_attributes = array("title" => $title);
    foreach ($items as $i => $item) $list_attributes["list-item-$i"] = $item;
    $list_attributes["add-item-text"] = $add_new_item;

    return dom\tag("editable-list", "", dom\attributes_add($list_attributes, $attributes));
}

init();
output(html(head(head_boilerplate()).body(main(

    h1("Editable list test").
    section(
        p("Editable list web component in action just below").
        editable_list("test list", array("premier","deuxième"), "allez hop!").
        p("Editable list web component in action just above").
    "").

"").script("js/editable-list.js"))));

?>