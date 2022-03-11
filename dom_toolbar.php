<?php

    require_once(dirname(__FILE__)."/dom.php");

    $__dom_frameworks_toolbar = array(

        'material' => array
        (
            'classes' => array
            (
                'toolbar'                   => 'mdc-top-app-bar mdc-top-app-bar--dense mdc-top-app-bar--fixed mdc-top-app-bar--waterfall mdc-top-app-bar--flexible mdc-top-app-bar--flexible-default-behavior mdc-top-app-bar--fixed-lastrow-only'
                , 'toolbar-row'               => 'mdc-top-app-bar__row row'
                , 'toolbar-cell'              => 'mdc-top-app-bar__section'
                , 'toolbar-cell-right'        => 'mdc-top-app-bar__section--align-end'
                , 'toolbar-cell-left'         => 'mdc-top-app-bar__section--align-start'
                , 'toolbar-cell-center'       => 'mdc-top-app-bar__section--align-middle'
                , 'toolbar-cell-shrink'       => 'mdc-top-app-bar__section--shrink-to-fit'
            
                , 'toolbar-title'             => 'mdc-top-app-bar__title'

                , 'toolbar-icon'              => 'material-icons mdc-top-app-bar__icon'

                , 'menu-toggle'               => 'mdc-menu-anchor'
                , 'menu'                      => 'mdc-menu'
                , 'menu-list'                 => 'mdc-menu__items mdc-list sidebar'
                , 'menu-list-item'            => 'mdc-list-item'
                , 'menu-list-item-separator'  => 'mdc-list-divider'
                
            )
        )
        
        ,"bootstrap" => array
        (
            "classes" => array
            (
                'menu-list'             => 'dropdown-menu sidebar'
                , 'toolbar'               => 'navbar sticky-top'        
            )
        )
        
        ,"spectre" => array
        (
            "classes" => array
            (
                'toolbar-row'               => 'navbar'
                , 'toolbar-cell'              => 'navbar-section'

            )
        )
    );

    dom_init_extend_frameworks_table($__dom_frameworks_toolbar);

    function hook_toolbar($row)
    {
        dom_set("toolbar",      true);
        dom_set("toolbar_$row", true);
    }
        
    function include_css_main_toolbar_adaptation($main_selector = "main") { return delayed_component("_".__FUNCTION__, $main_selector); }

    function _include_css_main_toolbar_adaptation($main_selector)
    {
        if (!!dom_get("toolbar_banner") && !!dom_get("toolbar_nav")) return "$main_selector { margin-top: calc(var(--header-height) + var(--header-toolbar-height)); }";
        if (!!dom_get("toolbar_banner"))                             return "$main_selector { margin-top: calc(var(--header-height)); }";
        if (!!dom_get("toolbar_nav"))                                return "$main_selector { margin-top: calc(var(--header-toolbar-height)); }";

        return "";
    }

    function include_css_boilerplate_toolbar()
    {
        if (!!dom_get("no_css")) return '';

        dom_heredoc_start(-3); ?><style><?php dom_heredoc_flush(null); ?>

            .toolbar                                        { width: 100%; z-index: 1; }
            
            .toolbar-row                                    { width: 100%; margin-left: 0px; margin-right: 0px; display: flex; }

            .toolbar-row                                    {    background-color: var(--theme-color);      color: var(--background-color); }
            .toolbar-row a                                  { /* background-color: var(--theme-color); */   color: var(--background-color); }
            .toolbar-row-banner                             {    background-color: var(--theme-color); /*   color: default; */           }
            
            .toolbar-row                                    { height: var(--header-toolbar-height); align-items: center; }
            .toolbar-row-banner                             { height: var(--header-height); max-height: var(--header-height); min-height: var(--header-min-height); }

            .toolbar-row-nav                                { padding-right: var(--dom-gap); margin-right: var(--dom-gap); }
            .toolbar-row-nav .cell:nth-child(1)             { width: calc(100vw / 2 - var(--scrollbar-width) / 2 - var(--main-max-width) / 2); min-width: var(--header-toolbar-height); }
            .toolbar-row-nav .cell:nth-child(2)             { flex: 0 1 auto; text-align: left;  }
            .toolbar-row-nav .cell:nth-child(3)             { flex: 1 0 auto; text-align: right; align-items: center; }

            .toolbar-row-nav .cell:nth-child(3) a           { padding-left: var(--dom-gap); }
            .toolbar-row-nav .cell:nth-child(3) ul          { display: inline-block; list-style-type: none; padding-inline-start: 0px; padding-inline-end: 0px; margin-block-end: 0px; margin-block-start: 0px; }
            .toolbar-row-nav .cell:nth-child(3) li          { display: inline-block; vertical-align: middle; }
            .toolbar-row-nav .cell:nth-child(3) li a        { display: inline-block; width: 100%; padding: var(--dom-gap); }

            .toolbar .nav-link                              { padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px; font-size: 1.5em; } 
            .toolbar .row.static                            { visibility: hidden; position: fixed; top: 0px; z-index: 999999; } 

            .menu-toggle                                    { width: var(--header-toolbar-height); }
            .menu-toggle a,       .toolbar-title a,
            .menu-toggle a:hover, .toolbar-title a:hover    { text-decoration: none; }
            .toolbar-title .headline1                       { margin-top: 0px; margin-bottom: 0px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .menu                                           { display: none } /* BY DEFAULT, DYNAMIC MENU IS NOT SUPPORTED */

            /* Menu open/close button layout */

            .menu-switch-symbol, .menu-close-symbol         { height: var(--header-toolbar-height); line-height: var(--header-toolbar-height); }

            /* Menu open/close mechanism */

            .menu-close-symbol                              { display: none; }

            /* Menu list */

            .menu                                           { background-color: var(--theme-color); color: var(--background-color); box-shadow: 1px 1px 4px 0 rgba(0,0,0,.2); }
            .menu a:hover                                   { background-color: var(--background-color); color: var(--theme-color); }

            #<?= DOM_MENU_ID 
            ?>-open .menu                                   { position: absolute; }
            .menu                                           { max-height: 0; transition: max-height 1s ease-out; text-align: left; }
            .menu ul                                        { list-style-type: none; padding-inline-start: 0px; padding-inline-end: 0px; margin-block-end: 0px; margin-block-start: 0px; }
            .menu li a                                      { display: inline-block; width: 100%; padding: var(--dom-gap); }

        <?php if (!AMP()) { ?> 

            /* Toolbar */

            <?php if (dom_get("no_js")) { ?> 
            
            .toolbar                                        { position: sticky; top: calc(var(--header-min-height) - var(--header-height)); }

            <?php } else { ?> 

            .toolbar                                        { position: fixed; top: 0px; } <?= include_css_main_toolbar_adaptation(".main") ?> 

            <?php } ?> 
            
            /* Menu open/close mechanism */

            #<?= DOM_MENU_ID ?>-open        .menu-switch-symbol           { display: inline-block;  }
            #<?= DOM_MENU_ID ?>-open:target .menu-switch-symbol           { display: none; }
            
            #<?= DOM_MENU_ID ?>-open        .menu-close-symbol            { display: none;  }
            #<?= DOM_MENU_ID ?>-open:target .menu-close-symbol            { display: inline-block; }
            
            #<?= DOM_MENU_ID ?>-open        .menu                         { display: none;  max-height:   0vh; }
            #<?= DOM_MENU_ID ?>-open:target .menu                         { display: block; max-height: 100vh; }
                
        <?php } if (AMP()) { ?> 

            /* AMP DEFAULTS */
            
            .menu                                           { display: block } /* AMP DYNAMIC MENU SUPPORTED */
                                            
            amp-sidebar                                     { background-color: var(--background-color); }
            amp-sidebar                                     { text-align: left; }
            amp-sidebar .menu                               { position: relative; }
            amp-sidebar ul                                  { list-style-type: none; padding-left: 0px } 

    <?php } ?>

        <?php if ("material" == dom_get("framework")) { ?> 
            
            .toolbar .row .cell         { overflow: visible }
                
            #<?= DOM_MENU_ID ?>-open        .menu     { display: block; max-height: 100vh; }
            #<?= DOM_MENU_ID ?>-open:target .menu     { display: block; max-height: 100vh; }
            
            .menu                       { display: block } /* MATERIAL DESIGN LIB DYNAMIC MENU SUPPORTED */

            .mdc-top-app-bar--dense 
            .mdc-top-app-bar__row       { height: var(--header-toolbar-height); /*align-items: center;*/ }
            .mdc-top-app-bar            { <?php if (dom_AMP()) { ?> position: inherit; <?php } ?> } 
            .mdc-top-app-bar__section   { flex: 0 1 auto; }
            .mdc-top-app-bar--dense 
            .mdc-top-app-bar__title     { padding-left: 0px; }
            .mdc-menu--open             { margin-top: var(--header-toolbar-height); }
            
        <?php } if ("bootstrap" == dom_get("framework")) { ?> 

            /* BOOTSTRAP DEFAULTS */
            
            .menu   { display: block } /* BOOTSTRAP LIB DYNAMIC MENU SUPPORTED */
            .navbar { padding: 0px }
            
        <?php } if ("spectre" == dom_get("framework")) { ?> 

        <?php } ?> 

            /* PRINT */
                
            @media print {

                .toolbar-row-banner                   { display: none }
                .toolbar-row-nav                      { background-color: transparent; align-items: flex-start; justify-content: flex-end; }
                .toolbar-row-nav .toolbar-cell-left   { display: none }
                .toolbar-row-nav .toolbar-cell-right  { display: none }
                .toolbar-row-nav .toolbar-cell-center { background-color: transparent;  padding-right: var(--scrollbar-width); }
            }    

        <?php dom_heredoc_flush("raw_css"); ?></style><?php return dom_heredoc_stop(null);
    }

    function dom_js_toolbar_framework_material()
    {
        if ("material" != dom_get("framework")) return "";

        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

        /* MDC (MATERIAL DESIGN COMPONENTS) FRAMEWORK */

        if (typeof window.mdc !== "undefined") { window.mdc.autoInit(); }

        /* Adjust toolbar margin */

        document.querySelector(".mdc-top-app-bar").position = "fixed";

        (function()
        {
            var pollId = 0;

            pollId = setInterval(function()
            {
                var e = document.querySelector(".mdc-top-app-bar");

                if (e != null)
                { 
                    var pos = getComputedStyle(e).position;

                    if (pos === "fixed" || pos === "relative")
                    {
                        material_init();
                        clearInterval(pollId);
                    }
                }

            }, 250);

            function material_init()
            {
                var e = document.querySelector(".mdc-top-app-bar");

                if (e != null && typeof mdc !== "undefined")
                { 
                    var toolbar = mdc.topAppBar.MDCTopAppBar.attachTo(e);
                    toolbar.fixedAdjustElement = document.querySelector(".mdc-top-app-bar--dense-");
                }
            }

        })();

        /*  Menu */

        var menuEl = document.querySelector(".mdc-menu");

        if (menuEl != null && typeof mdc !== "undefined")
        {  
            var menuToggle = document.querySelector(".menu-toggle");
            var menu       = new mdc.menu.MDCMenu(menuEl);

            menuToggle.addEventListener("click", function() 
            { 
                menu.open = !menu.open; 
            });

            menuEl.addEventListener("MDCMenu:selected", function(evt) 
            {
                const detail = evt.detail;

                detail.item.textContent;
                detail.index;
            });
        }  

        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_toolbar()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

            /* TOOLBAR */

            var idAnimationFrame = null;
        
            function updateToolbarHeight(animate)
            {
                var toolbar_row_banners = document.querySelectorAll(".toolbar-row-banner");
                var toolbars            = document.querySelectorAll(".toolbar");

                var toolbar_row_banner  = toolbar_row_banners ? toolbar_row_banners[0] : null;
                var toolbar             = toolbars            ? toolbars[0]            : null;

                if (toolbar != null && toolbar_row_banner != null)
                {
                    var header_height     = parseInt(window.getComputedStyle(toolbar_row_banner, null).getPropertyValue(    "height").replace("px",""), 10);
                    var header_max_height = parseInt(window.getComputedStyle(toolbar_row_banner, null).getPropertyValue("max-height").replace("px",""), 10);
                    var header_min_height = parseInt(window.getComputedStyle(toolbar_row_banner, null).getPropertyValue("min-height").replace("px",""), 10);
        
                    var stuck_height = header_max_height - header_min_height;

                    if (window.scrollY > stuck_height) { toolbar.classList.add(   "scrolled"); toolbar.classList.remove("top"); }
                    else                               { toolbar.classList.remove("scrolled"); toolbar.classList.add(   "top"); }
        
                    var target = Math.max(0, header_max_height - window.scrollY);

                    var h = (animate) ? (header_height + ((target > header_height) ? 1 : -1) * 0.1 * Math.max(1, Math.abs(target - header_height))) : target;
                    h = parseInt(Math.max(0, h), 0); /* So it's properly snapped */

                    toolbar_row_banner.style.height = h + "px";

                    if (Math.abs(h - target) > 0.1)
                    {
                        idAnimationFrame = window.requestAnimationFrame(onUpdateToolbarHeight);
                    }
                }
            }
        
            function onUpdateToolbarHeight()
            {
                window.cancelAnimationFrame(idAnimationFrame);
                updateToolbarHeight(true);
            }
        
            function onInitToolbarHeight()
            {
                window.cancelAnimationFrame(idAnimationFrame);
                updateToolbarHeight(false);
            }

            dom_on_ready( onInitToolbarHeight);
            dom_on_loaded(onInitToolbarHeight);
            dom_on_scroll(onUpdateToolbarHeight);

        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_toolbar_banner_rotation()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>
            
            /* TOOLBAR BANNER IMAGE ROTATION */
            
            function onInitRotatingHeaders()
            {
                var rotate_backgrounds = function(content)
                {
                    if (content != "")
                    {
                        var index_url = 0;
                        var urls = content.split(",");
            
                    /*console.log("DOM: DEBUG: Header backgrounds: ", urls);*/

                        if (urls && !(typeof urls === "undefined") && urls.length > 0)
                        {
                            setInterval(function()
                            {
                                var toolbar_row_banners = document.querySelectorAll(".toolbar-row-banner");
                                var toolbar_row_banner  = toolbar_row_banners ? toolbar_row_banners[0] : null;

                                if (toolbar_row_banner)
                                {
                                    toolbar_row_banner.style.backgroundImage = "url(" + urls[index_url] + ")";
                                    index_url = (index_url + 1) % urls.length;
                                }

            
                            }, 10*1000);
                        }
                    }
                };
                
                <?php if (has("noajax") && is_string(get("support_header_backgrounds"))) { ?>
                rotate_backgrounds("<?= get("support_header_backgrounds") ?>"); 
                <?php } else { ?> 
                dom_ajax("?ajax=header-backgrounds", rotate_backgrounds);
                <?php } ?> 
            }

            dom_on_loaded(onInitRotatingHeaders);
        
        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function scripts_body_toolbar()
    {
        if (dom_has("ajax")) return "";

        return                                                                    ((!!dom_get("dom_script_toolbar",            true)) ? (
                dom_script(dom_js_toolbar                       ()).   "") : ""). ((!!dom_get("support_header_backgrounds",   false)) ? (
                dom_script(dom_js_toolbar_banner_rotation       ()).   "") : ""). ((!!dom_get("dom_script_framework_material", true)) ? (
                dom_script(dom_js_toolbar_framework_material    ()).   "") : "");
    }

    // ICONS
    
    function icon_entry($icon, $label = "", $link = "JAVASCRIPT_VOID", $attributes = false, $target = false, $id = false)
    {
        $link = ("JAVASCRIPT_VOID" == $link) ? url_void() : $link;
        
        if (($attributes === DOM_INTERNAL_LINK || $attributes === DOM_EXTERNAL_LINK) && $target === false) { $target = $attributes; $attributes = false; }
        if ($target === false) { $target = DOM_INTERNAL_LINK; }
        
        return array($icon, $label, $link, $id, $target, $attributes);
    }

    function dom_icon_entry_to_link($icon_entry, $default_target = DOM_INTERNAL_LINK)
    {
        $icon       = dom_get($icon_entry, "icon",          dom_get($icon_entry, 0, ""));
        $label      = dom_get($icon_entry, "label",         dom_get($icon_entry, 1, ""));
        $link       = dom_get($icon_entry, "link",          dom_get($icon_entry, 2, false));
        $id         = dom_get($icon_entry, "id",            dom_get($icon_entry, 3, false));
        $target     = dom_get($icon_entry, "target",        dom_get($icon_entry, 4, $default_target));
        $attributes = dom_get($icon_entry, "attributes",    dom_get($icon_entry, 5, false));

        if (false === $attributes) $attributes = array();
        
        if (!in_array("aria-label", $attributes)                    ) $attributes["aria-label"  ] = $label;
        if (!in_array("alt",        $attributes) && !dom_AMP()      ) $attributes["alt"         ] = $label;
        if (!in_array("id",         $attributes) && (false !== $id) ) $attributes["id"          ] = $id;

        return a($icon, $link, $attributes, $target);
    }

    function icon_entries($icon_entries, $default_target = DOM_INTERNAL_LINK)
    {
        if (is_array($icon_entries))
        {
            return wrap_each($icon_entries, dom_eol(), "dom_icon_entry_to_link", false);
        }
        else if (is_string($icon_entries))
        {
            return $icon_entries;
        }

        return "";
    }
    
    // MENU

    function menu_entry($text = "", $link = false)
    {
        return ($link === false) ? $text : array($text, $link);
    }

    $__dom_ul_menu_index = -1;

    function ul_menu($menu_entries = array(), $default_target = DOM_INTERNAL_LINK, $sidebar = DOM_AUTO)
    {
        global $__dom_ul_menu_index;
        ++$__dom_ul_menu_index;

        if ($sidebar === DOM_AUTO) $sidebar = (0 == $__dom_ul_menu_index);

        $menu_lis = "";
        {
            if (!is_array($menu_entries)) $menu_entries = array($menu_entries);

            if (false != $menu_entries) foreach ($menu_entries as $menu_entry)
            {
                if ($menu_entry == array() || $menu_entry == "")
                {
                    $menu_lis .= li("", array("class" => dom_component_class("list-item-separator"), "role" => "separator"));
                }
                else
                {    
                //  if (!is_array($menu_entry)) $menu_entry = array($menu_entry, url_void());
                    if (!is_array($menu_entry)) $menu_entry = array($menu_entry, "#".anchor_name($menu_entry));
                            
                    $item       = dom_get($menu_entry, "item",   dom_get($menu_entry, 0, ""));
                    $link       = dom_get($menu_entry, "link",   dom_get($menu_entry, 1, false));
                    $target     = dom_get($menu_entry, "target", dom_get($menu_entry, 2, $default_target));
                    $attributes = false;
                    
                    $menu_lis .= li(a(span($item), $link, $attributes, $target), array("class" => dom_component_class("list-item"), "role" => "menuitem", "tabindex" => "0"));
                }
            }
        }

        $html = "";

                if (dom_AMP())                             { $html =  ul($menu_lis, array("class" => dom_component_class('menu-list')/*." ".dom_component_class('menu')*/, "role" => "menu", "aria-hidden" => "true"                                                   )); }
        else if (dom_get("framework") == "bootstrap")   { $html = div($menu_lis, array("class" => dom_component_class('menu-list'),                                     "role" => "menu", "aria-hidden" => "true", "aria-labelledby" => "navbarDropdownMenuLink"    )); }
        else                                            { $html =  ul($menu_lis, array("class" => dom_component_class('menu-list'),                                     "role" => "menu"                                                                            )); }

        if (dom_AMP() && !!$sidebar)
        {
            hook_amp_sidebar(
                dom_tag('amp-sidebar class="menu" id="'.DOM_MENU_ID.'" layout="nodisplay"', $html)
                );

            //$html = span("","placeholder-amp-sidebar");
        }

        return $html;
    }

    function menu_switch() { return (dom_get("framework") == "material"  ? (a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons mdc-top-app-bar__icon--menu", "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle")                                                                ))) : "")
                                .   (dom_get("framework") == "bootstrap" ? (a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons",                             "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  ))) : "")
                                .   (dom_get("framework") == "spectre"   ? (a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons",                             "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  ))) : "")
                                .   (dom_get("framework") == "NONE"      ? (a(span("☰", "menu-switch-symbol menu-toggle-content")   
                                                                            . a(span("✕", "menu-close-symbol  menu-close-content"), "#".DOM_MENU_ID."-close",  array("class" => "menu-switch-link close nav-link material-icons", "aria-label" => "Menu Toggle"))
                                                                                                                                        , "#".DOM_MENU_ID."-open",   array("class" => "menu-switch-link open nav-link material-icons", "name" => "menu-close",                            "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle")                     ))) : "")
                                                            ; 
    }

    // TOOLBAR

    function toolbar_row    ($html,  $attributes = false) {                      return div     (   $html,  dom_attributes_add_class($attributes, dom_component_class("toolbar-row") ." ".dom_component_class("row"))   ); }
    function toolbar_section($html,  $attributes = false) {                      return section (   $html,  dom_attributes_add_class($attributes, dom_component_class("toolbar-cell")." ".dom_component_class("cell"))  ); }

    function toolbar_banner_sections_builder($section1 = false, $section2 = false, $section3 = false)
    {
        if (is_array($section1)) $section1 = toolbar_section(icon_entries($section1), dom_component_class("toolbar-cell-left"   ));
        if (is_array($section2)) $section2 = toolbar_section(icon_entries($section2), dom_component_class("toolbar-cell-center" ));
        if (is_array($section3)) $section3 = toolbar_section(icon_entries($section3), dom_component_class("toolbar-cell-right"  ));

        if ($section1 === false) $section1 = "";
        if ($section2 === false) $section2 = "";
        if ($section3 === false) $section3 = "";

        if (stripos($section1, "<section") === false) $section1 = toolbar_section($section1);
        if (stripos($section2, "<section") === false) $section2 = toolbar_section($section2);
        if (stripos($section3, "<section") === false) $section3 = toolbar_section($section3);

        return $section1.$section2.$section3;
    }

    function toolbar_banner($icon_entries = false, $section2 = false, $section3 = false)
    {
        hook_toolbar("banner");

        return toolbar_row(
            toolbar_banner_sections_builder(
                $icon_entries,
                $section2,
                $section3
                ),
            "toolbar-row-banner"
            );
    }

    function menu_entries($html, $sidebar = DOM_AUTO)
    {
        if (false === stripos($html, "menu-list") 
        &&  false === stripos($html, "_ul_menu_auto")) $html = ul_menu($html, DOM_INTERNAL_LINK, $sidebar);

        return (dom_get("framework") != "bootstrap" ? div($html, "menu-entries " . dom_component_class("menu")) : $html);
    }

    function menu_toggle($html, $sidebar = DOM_AUTO)
    {
        if (false === stripos($html, "menu-entries")) $html = menu_entries($html, $sidebar);
        if (false === stripos($html, "menu-switch"))  $html = menu_switch().$html;

        return div($html, array("id" => "menu-open", "class" => dom_component_class("menu-toggle")));
    }

    function toolbar_nav_toolbar($html = false)
    {
        if (false === $html) $html = ul_menu_auto();

        return toolbar_section(($html === false) ? '' : $html,  array(
            
            "role" => "navigation",
            "class" => (dom_component_class("toolbar-cell-right") . ' ' . 
                        dom_component_class("toolbar-cell-right-shrink"))));
    }

    function  ul_menu_auto($sidebar = DOM_AUTO) { return delayed_component("_".__FUNCTION__, $sidebar); }
    function _ul_menu_auto($sidebar = DOM_AUTO) { return ul_menu(dom_get("hook_sections"), DOM_INTERNAL_LINK, $sidebar); }

    function  menu_toggle_auto($sidebar = DOM_AUTO) { return menu_toggle(ul_menu_auto(), $sidebar); }

    function toolbar_nav_menu($html = false, $attributes = false, $menu_entries_shrink_to_fit = false, $sidebar = DOM_AUTO)
    {
        if (false !== $html && false === stripos($html, "menu-toggle")) $html = menu_toggle($html);
        if (false === $html)                                            $html = menu_toggle_auto($sidebar);
        
        return toolbar_section(($html === false) ? '' : $html,  dom_component_class("toolbar-cell-left") . ($menu_entries_shrink_to_fit ? (' '.
                                                                dom_component_class("toolbar-cell-right-shrink")    ) : ""));
    }

    function toolbar_nav_title($html, $attributes = false)
    {
        hook_title($html);

        if ($html !== false && $html != "")
        {
            if (false === stripos($html,"<a"))   $html =   a($html,'.');
            if (false === stripos($html,"<h1"))  $html =  h1($html);
        }

        if (false === stripos($html,"<div")) $html = div($html, "toolbar-title");
        
        return toolbar_section(($html === false) ? '' : $html, dom_component_class("toolbar-cell-center"));
    }

    function toolbar_nav($html, $attributes = false)
    {
        hook_toolbar("nav");

        if (false === stripos($html,"toolbar-cell")) $html = toolbar_nav_menu().toolbar_nav_title($html);
        
        $menu_id_amp = DOM_MENU_ID."-static";
        
        $html_amp = $html;
        $html_amp = str_replace(DOM_MENU_ID.'.toogle',  $menu_id_amp.'.toogle',  $html_amp);
        $html_amp = str_replace('id="'.DOM_MENU_ID.'"', 'id="'.$menu_id_amp.'"', $html_amp);

                        $html  = toolbar_row($html,     array("id" => "toolbar-row-nav",        "class" => "toolbar-row-nav"         ));
        if (dom_AMP()) $html .= toolbar_row($html_amp, array("id" => "toolbar-row-nav-static", "class" => "toolbar-row-nav static"  ));

        return $html;
    }

    function toolbar($html, $attributes = false)
    {
        if (false === stripos($html,"toolbar-row")) $html = toolbar_banner().toolbar_nav($html);
        
        $amp_observer = "";
        $amp_anim     = "";
        
        if (dom_AMP())
        {            
            hook_amp_require("animation");
            hook_amp_require("position-observer");

            $amp_anim     = dom_eol().'<amp-animation id="toolbarStaticShow" layout="nodisplay"><script type="application/json">{ "duration": "0", "fill": "forwards", "animations": [ { "selector": "#toolbar-row-nav-static", "keyframes": { "visibility": "visible" } } ] }</script></amp-animation>'
                            . dom_eol().'<amp-animation id="toolbarStaticHide" layout="nodisplay"><script type="application/json">{ "duration": "0", "fill": "forwards", "animations": [ { "selector": "#toolbar-row-nav-static", "keyframes": { "visibility": "hidden"  } } ] }</script></amp-animation>';

            $amp_observer = dom_eol().'<amp-position-observer target="toolbar-row-nav" intersection-ratios="1" on="enter:toolbarStaticHide.start;exit:toolbarStaticShow.start" layout="nodisplay"></amp-position-observer>';
        }

        return  

            $amp_anim.
            comment("PRE Toolbar").
            dom_header(
                $html.
                $amp_observer, 
                dom_attributes_add_class($attributes, dom_component_class("toolbar toolbar-container"))
                );
    }

?>