<?php

namespace dom;

require_once(__DIR__."/dom8.php");


if (!defined("DOM_MENU_ID")) define("DOM_MENU_ID", "menu");

function hook_toolbar($row)
{
    set("toolbar",      true);
    set("toolbar_$row", true);
}

function css_toolbar_layout($layer = [ "default", "component", "toolbar" ])
{
    if (has("dom_toolbar_no_css")) return "";
    
    heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 

        /* Toolbar */

        :root {

            --header-height:          0px;
            --header-min-height:      0px;
            --header-toolbar-height:  calc(2 * var(--line-height, 1rem));
            --scroll-margin:          calc(var(--gap) + var(--header-toolbar-height, 0) + var(--header-min-height, 0));
        }

        .toolbar                                        { width: 100%; z-index: 1; }
        
        .toolbar-row                                    { width: 100%; margin-left: 0px !important; margin-right: 0px !important; display: flex; /*overflow: hidden;*/ }
        /*
        .toolbar-row                                    { height: var(--header-toolbar-height); align-items: center; }
        */
        /* VARIABLE HEIGHT */
        .toolbar-row                                    { align-items: center; }
        .toolbar-row-nav                                { min-height: var(--header-toolbar-height); }

        .toolbar-row-banner                             { height: var(--header-height); max-height: var(--header-height); min-height: var(--header-min-height); }

        .toolbar-row, 
        .toolbar-row *                                  { margin: 0; }

        /* QUICK DIRTY TMP HACK */ 
        .toolbar-row-banner .toolbar-cell-left          { display: flex; gap: calc(0.5 * var(--gap)); justify-content: end; margin-right: var(--gap); } 

        .toolbar-row-nav, 
        .toolbar-row-nav *                              { margin: 0; padding: 0; white-space: nowrap; }
        /*
        .toolbar-row-nav, 
        .toolbar-row-nav :is(section, div, ul)          { display: flex; align-items: center;  scrollbar-width: none; flex-wrap: nowrap; }
        */
        /* VARIABLE HEIGHT */
        .toolbar-row-nav, 
        .toolbar-row-nav :is(section, div, ul)          { display: flex; align-items: center;  scrollbar-width: none; flex-wrap: wrap; max-width: 100%; }

        .toolbar-row-nav,
        .toolbar-row-nav :is(section, div, ul) > *      { flex-shrink: 0; }
        .toolbar-row-nav,
        .toolbar-row-nav ul                             { gap: 0 1rem; }
        .toolbar-row-nav li *                           { min-height: var(--line-height); }
        .toolbar-row-nav .toolbar-cell-left .menu-list  { flex-direction: column; align-items: stretch; }

        .toolbar-row-nav :not(:is(section,div,ul,img,a>nav)):not([hidden], .hidden)  { display: block; }
        
        .toolbar-row-nav .toolbar-cell-left             { width: clamp(calc(var(--header-toolbar-height) - var(--gap)), calc(var(--left-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--left-text-margin-ratio) * 100%)); }

        .toolbar-row-nav .toolbar-cell-center,
        .toolbar-row-nav .toolbar-cell-center *         { flex-shrink: 1; /*overflow: hidden; text-overflow: ellipsis;*/  }

        .toolbar-row-nav .toolbar-cell-right            { flex-grow: 1; justify-content: end; } 
        .toolbar-row-nav .toolbar-cell-right            { margin-right: clamp(var(--gap),  calc(var(--right-text-margin-ratio) * calc(100% - var(--max-text-width)) + var(--gap)), calc(var(--right-text-margin-ratio) * 100%)); }
        .toolbar-row-nav .toolbar-cell-right            { padding-left: 1rem; }

        .toolbar .row.static                            { visibility: hidden; position: fixed; left: 0px; top: 0px; z-index: 999999; } 

        .toolbar .nav-link                              { font-size: 1.5em; } 

        .toolbar .menu-entries                          { flex-direction: column; align-items: stretch; }

        .toolbar-duplicate                              { z-index: -1; display: none; opacity: 0; }
        body > main                                     { margin-top: 0 !important; }


        .menu-toggle                                    { width: var(--header-toolbar-height); flex-direction: column; }
        .menu-toggle a,       .toolbar-title a,
        .menu-toggle a:hover, .toolbar-title a:hover    { text-decoration: none; }

        /* Menu open/close mechanism */

        /* Menu list */
            
        #<?= DOM_MENU_ID 
        ?>-open .menu   { position: absolute; left: var(--gap); transform: translateY(var(--header-toolbar-height)); }
        .menu           { max-height: 0; transition: max-height 1s ease-out; text-align: left; }
        .menu ul        { padding: 0; gap: 0; list-style-type: none; align-items: stretch; flex-direction: column; }
        .menu li        { padding: 0; }
        .menu li > *    { padding: calc(0.5 * var(--gap)) var(--gap); }

        /* Toolbar */
    
        .toolbar:not(.toolbar-duplicate) { position: sticky; left: 0; top: min(0px, calc(var(--header-min-height) - var(--header-height))); }
        
        /* Menu open/close mechanism */

        <?php if (!!get("dom_toolbar_no_js") || !!get("no_js") || !get("script_toolbar_menu", true)) /* When no JS there is no :target use */ { ?>
        #<?= DOM_MENU_ID ?>-open        a.menu-switch-link.open   { display: inline-block !important;  }
        <?php } ?>
        #<?= DOM_MENU_ID ?>-open:target a.menu-switch-link.open   { display: none !important; }/*
        
        #<?= DOM_MENU_ID ?>-open        a.menu-switch-link.close  { display: none !important;  }*/

        <?php if (!!get("dom_toolbar_no_js") || !!get("no_js") || !get("script_toolbar_menu", true)) /* When no JS there is no :target use */ { ?>
        #<?= DOM_MENU_ID ?>-open:target a.menu-switch-link.close  { display: inline-block !important; }
        <?php } ?>
        
        #<?= DOM_MENU_ID ?>-open        .menu                     { /*display: none;  */max-height:   0vh; }
        #<?= DOM_MENU_ID ?>-open:target .menu                     { display: flex !important; max-height: 100vh; } /* TODO change to flex ? */

        .toolbar [hidden="hidden"] { display: none }

        /* Scrollbar */

        .toolbar-row-nav {
            /*
            overflow-x: scroll; */
        }
        .toolbar-row-nav::-webkit-scrollbar {

            display: none;
        }
    
        /* PRINT */
            
        @media print {

            .toolbar-row-banner                   { display: none }
            .toolbar-row-nav                      { align-items: flex-start; justify-content: flex-end; }
            .toolbar-row-nav .toolbar-cell-left   { display: none }
            .toolbar-row-nav .toolbar-cell-right  { display: none }
            .toolbar-row-nav .toolbar-cell-center { padding-right: var(--scrollbar-width); }
        }

    <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
}

function css_toolbar_colors($layer = [ "default", "component", "toolbar" ])
{
    if (has("dom_toolbar_no_css")) return "";
    
    heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 

        /* Colors: toolbar & menu */

        .toolbar-row                            { background-color: var(--theme-color, #000); --color: var(--text-on-theme-color, #000); color: var(--color); }

        .toolbar-cell-left   :is(a, a:visited)  { color: var(--text-on-theme-color,             #eee); }
        .toolbar-cell-center :is(a, a:visited)  { color: var(--text-on-theme-color,             #eee); }
        .toolbar-cell-right  :is(a, a:visited)  { color: var(--text-on-accent-color,            #eee); }

        .toolbar-cell-left   :is(a:hover)       { color: var(--text-on-theme-lighter-color,     #fff); }
        .toolbar-cell-center :is(a:hover)       { color: var(--text-on-theme-lighter-color,     #fff); }
        .toolbar-cell-right  :is(a:hover)       { color: var(--text-on-accent-lighter-color,    #fff); }

        .toolbar-cell .menu                     { color: var(--text-darker-color,               #ddd); background-color: var(--background-lighter-color,    #222); box-shadow: 0px 0px 2px 2px #00000022; }
        .toolbar-cell .menu :is(a, a:visited)   { color: var(--link-color,                      #eee); }
        .toolbar-cell .menu a:hover             { color: var(--link-hover-color,                #fff); background-color: var(--background-darker-color,     #000);;}

        /* Menu list */

        .menu                                           { box-shadow: 1px 1px 4px 0 rgba(0,0,0,.2); }
    
    <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
}

// ICONS

function icon_entry($icon, $label = "", $link = "JAVASCRIPT_VOID", $attributes = false, $target = false, $id = false, $encrypted = false, $category = false)
{
    $link = ("JAVASCRIPT_VOID" == $link) ? url_void() : $link;
    
    if (($attributes === internal_link || $attributes === external_link) && $target === false) { $target = $attributes; $attributes = false; }
    if ($target === false) { $target = internal_link; }
    
    return array($icon, $label, $link, $id, $target, $attributes, $encrypted, $category);
}

function icon_entry_to_link($icon_entry, $default_target = internal_link)
{
    if (!is_array($icon_entry)) return $icon_entry;

    $icon       = get($icon_entry, "icon", get($icon_entry, "item", get($icon_entry, 0, "")));
    $label      = get($icon_entry, "label",                         get($icon_entry, 1, ""));
    $link       = get($icon_entry, "link",                          get($icon_entry, 2, false));
    $id         = get($icon_entry, "id",                            get($icon_entry, 3, false));
    $target     = get($icon_entry, "target",                        get($icon_entry, 4, $default_target));
    $attributes = get($icon_entry, "attributes",                    get($icon_entry, 5, false));
    $encrypted  = get($icon_entry, "encrypted",                     get($icon_entry, 6, false));

    if (false === $attributes) $attributes = array();
    
    if (!in_array("aria-label", $attributes)                    ) $attributes["aria-label"  ] = $label;/*
    if (!in_array("alt",        $attributes)                    ) $attributes["alt"         ] = $label;*/
    if (!in_array("id",         $attributes) && (false !== $id) ) $attributes["id"          ] = $id;

    if ($encrypted)
    {
        return a_encrypted($icon, $link, $attributes, $target);
    }
    else
    {
        if ($link == false || $link == url_void() || $link == url_empty())
        {
            $attributes = \dom\attributes_add_class($attributes, "transparent link");
            $attributes = \dom\attributes_add($attributes, \dom\attributes(\dom\attr("type", "button") ));

            return button($icon, $attributes);
        }
        else if (0 === stripos($link, "javascript:"))
        {
            $js_function = trim(str_replace("javascript:", "", $link), ";");
            //$js_function = substr($js_function, 0, stripos($js_function, "("));

            $attributes = \dom\attributes_add_class($attributes, "transparent link");
            $attributes = \dom\attributes_add($attributes, \dom\attributes(\dom\attr("onclick", $js_function) ));
            $attributes = \dom\attributes_add($attributes, \dom\attributes(\dom\attr("type", "button") ));

            return button($icon, $attributes);
        }
        else 
        {
            return a($icon, $link, $attributes, $target);
        }
    }        
}

function icon_entries($icon_entries, $default_target = internal_link)
{
    if (is_array($icon_entries))
    {
        return wrap_each($icon_entries, " ".eol(), "icon_entry_to_link", false);
    }
    else if (is_string($icon_entries))
    {
        return $icon_entries;
    }

    return "";
}

// MENU

function menu_entry($text = false, $link = false, $target = false, $attributes = false)
{
    if (false === $text && false === $link && false === $target && false === $attributes) return array(); // Separator

    return array(
        
        "item"       => ((!!$text       && auto !== $text       ) ? $text       : ""                        ), 
        "link"       => ((!!$link       && auto !== $link       ) ? $link       : ("#".anchor_name($text))  ),
        "target"     => ((!!$target     && auto !== $target     ) ? $target     : internal_link             ), 
        "attributes" => ((!!$attributes && auto !== $attributes ) ? $attributes : false                     ), 
    );
}

function menu_li_attributes($item, $__add_transition_names = auto)
{
    $add_transition_names = (auto === $__add_transition_names) ? get("transition_names") : $__add_transition_names;
        $transition_name  = trim(to_classname($item));

    $attributes = [];
    {
        $attributes["class"] = component_class("li", "list-item");
        
        if ($transition_name != "" && $add_transition_names) 
        {
            $attributes["style"] = "view-transition-name: $transition_name;";
        }
        
    }

    return $attributes;
}

$__ul_menu_index = -1;

function ul_menu($menu_entries = array(), $default_target = internal_link, $sidebar = auto, $add_transition_names = auto)
{
    if (!!get("gemini")) return "";
    
    global $__ul_menu_index;
    ++$__ul_menu_index;

    if ($sidebar === auto) $sidebar = (0 == $__ul_menu_index);

    $menu_lis = "";
    {
        if (!is_array($menu_entries)) $menu_entries = array($menu_entries);

        if (false != $menu_entries) foreach ($menu_entries as $menu_entry)
        {
            if ($menu_entry == array() || $menu_entry == "")
            {
                $menu_lis .= li("", array("class" => component_class("li", "list-item-separator")));
            }
            else
            {    
                if (!is_array($menu_entry)) $menu_entry = array($menu_entry, "#".anchor_name($menu_entry));
                        
                $item       = get($menu_entry, "item",       get($menu_entry, 0, ""));
                $link       = get($menu_entry, "link",       get($menu_entry, 1, false));
                $target     = get($menu_entry, "target",     get($menu_entry, 2, $default_target));
                $attributes = get($menu_entry, "attributes", get($menu_entry, 3, false));

                $menu_lis .= li(a(span($item), $link, $attributes, $target), menu_li_attributes($item, $add_transition_names));
            }
        }
    }

    return ul($menu_lis, array("class" => component_class("ul", 'menu-list')));
}

function menu_switch() { 

    $a_toggle = a(span("☰", [ "class" => "menu-switch-symbol menu-toggle-content", "aria-label" => "Open menu"  ]), "#".DOM_MENU_ID."-open",    array("class" => "menu-switch-link open  nav-link material-icons",                      ));
    $a_close  = a(span("✕", [ "class" => "menu-close-symbol  menu-close-content",  "aria-label" => "Close menu" ]), "#".DOM_MENU_ID."-close",   array("class" => "menu-switch-link close nav-link material-icons", "hidden" => "hidden" ));
    
    return $a_toggle.$a_close;
}

// TOOLBAR

function toolbar_row($html, $attributes = false)
{
    return div(
            $html,
            attributes_add_class(
                $attributes,
                component_class("div", "toolbar-row")." ".
                component_class("div", "row")
                )
            );
}

function toolbar_section($html, $attributes = false)
{
    return div(
            $html,
            attributes_add(
                $attributes,                        
                attributes(
                    component_class("div", "toolbar-cell"),
                    component_class("div", "cell")
                    )
                )
            );
}

function toolbar_skip_to_main()
{
    return a(T("Skip to main"), "#main", "visually-hidden");        
}

function toolbar_banner_sections_builder($section1 = false, $section2 = false, $section3 = false)
{
    $skip_to_main = delayed_component("toolbar_skip_to_main"); // in case main content id has to be discovered on the way

    if (is_array($section1)) $section1 = toolbar_section($skip_to_main." ". icon_entries($section1), component_class("div", "toolbar-cell-left"   ));
    if (is_array($section2)) $section2 = toolbar_section(                   icon_entries($section2), component_class("div", "toolbar-cell-center" ));
    if (is_array($section3)) $section3 = toolbar_section(                   icon_entries($section3), component_class("div", "toolbar-cell-right"  ));

    if ($section1 === false) $section1 = "";
    if ($section2 === false) $section2 = "";
    if ($section3 === false) $section3 = "";

    if (stripos($section1, "<div") === false && $section1 != "") $section1 = toolbar_section($section1);
    if (stripos($section2, "<div") === false && $section2 != "") $section2 = toolbar_section($section2);
    if (stripos($section3, "<div") === false && $section3 != "") $section3 = toolbar_section($section3);

    return $section1.$section2.$section3;
}

function toolbar_banner($icon_entries = false, $section2 = false, $section3 = false)
{
    if (!!get("gemini")) return "";
    
    hook_toolbar("banner");

    return toolbar_row(
        toolbar_banner_sections_builder(
            $icon_entries,
            $section2,
            $section3
            ),
        array("class" => "toolbar-row-banner", "role" => "banner")
        );
}

function menu_entries($html, $sidebar = auto)
{
    if (false === stripos($html, "menu-list") 
    &&  false === stripos($html, "_ul_menu_auto")) $html = ul_menu($html, internal_link, $sidebar);

    return (get("framework") != "bootstrap" ? div($html, array("class" => ("menu-entries"." ".component_class("div", "menu")), "hidden" => "hidden")) : $html);
}

function menu_toggle($html, $sidebar = auto)
{
    if (!!get("no_css")) return ""; // No css == no responsiveness toggle between navbar & navdropmenu

    if (false === stripos($html, "menu-entries")) $html = menu_entries($html, $sidebar);
    if (false === stripos($html, "menu-switch"))  $html = menu_switch().$html;

    return div($html, array("role" => "switch", "aria-checked" => "false", "id" => "menu-open"/*, "hidden" => "hidden"*/, "class" => component_class("div", "menu-toggle")));
}

function toolbar_nav_toolbar($html = false)
{
    if (!!get("gemini")) return "";
    
    if (false === $html) $html = ul_menu_auto();

    return toolbar_section(($html === false) ? '' : $html,  array(
        
        "role"  => "navigation",
        "class" => (component_class("div", "toolbar-cell-right") . ' ' . 
                    component_class("div", "toolbar-cell-right-shrink"))));
}

function  ul_menu_auto($sidebar = auto) { return delayed_component("_".__FUNCTION__, $sidebar); }
function _ul_menu_auto($sidebar = auto) { $sections = get("hook_sections"); return (!is_array($sections) || count($sections) < 2) ? "" : ul_menu($sections, internal_link, $sidebar); }

function menu_toggle_auto($sidebar = auto) { return menu_toggle(ul_menu_auto(), $sidebar); }

function toolbar_nav_menu($html = false, $attributes = false, $menu_entries_shrink_to_fit = false, $sidebar = auto)
{
    if (!!get("gemini")) return "";
    
    if (false !== $html && false === stripos($html, "menu-toggle")) $html = menu_toggle($html);
    if (false === $html)                                            $html = menu_toggle_auto($sidebar);

    return toolbar_section(($html === false) ? '' : $html, attributes(attr("class",
        component_class("div", "toolbar-cell-left") .          ($menu_entries_shrink_to_fit ? (' '.
        component_class("div", "toolbar-cell-right-shrink")    ) : "")
        )));
}

function toolbar_nav_title($html, $attributes = false)
{
    hook_markup_to_title($html);

    if ($html !== false && $html != "")
    { 
        if (false === stripos($html,"<a"))   $html =   a($html, '.'); /* */ // CHOCA_WIP
        if (false === stripos($html,"<h1"))  $html =  h1($html, [ "style" => ("view-transition-name: ".\dom\view_transition_name($html)) ]);
    }

    if (false === stripos($html,"<div")) $html = div($html, "toolbar-title");
    
    return toolbar_section(($html === false) ? '' : $html, attributes(component_class("div", "toolbar-cell-center")));
}

function toolbar_nav($html, $attributes = false)
{
    hook_toolbar("nav");

    if (false === stripos($html,"toolbar-cell")) $html = toolbar_nav_menu().toolbar_nav_title($html);
    
    return toolbar_row($html, array("class" => "toolbar-row-nav"));
}

function toolbar($html, $attributes = false)
{
    if (false === stripos($html,"toolbar-row")) $html = toolbar_banner().toolbar_nav($html);
    
    $attributes = attributes_add($attributes, component_class("header", "toolbar toolbar-container"));

    set("transition_names");
    $toolbar = header($html, $attributes);
    del("transition_names");

    return  

        comment("PRE Toolbar").

        style(css_layer([ "default", "component", "toolbar" ], '

        body > header {

            display: flex;
            flex-direction: column;
            align-items: center;

            > :is(section, div) {

                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 1rem;

                :is(nav, [role="navigation"]) ul {
                
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    gap: 1rem;
                }
            }
        }
        
        '), false, [ "layer" => "default", "media" => "all" ]).

        $toolbar.
        
        "";
}

function toolbar_styles($layer = [ "default", "component", "toolbar" ])
{
    return (!!get("no_css_toolbar") ? "" : (

        eol().comment("Base-Toolbar-Layout").layered_style($layer, css_toolbar_layout(false)).
        eol().comment("Base-Toolbar-Colors").layered_style($layer, css_toolbar_colors(false)).

    ""));
}