<?php

    namespace dom;

    require_once(__DIR__."/dom8.php");
    
    if (!defined("DOM_MENU_ID")) define("DOM_MENU_ID", "menu");

    $__frameworks_toolbar = array(

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

    \dom\init_extend_frameworks_table($__frameworks_toolbar);

    function hook_toolbar($row)
    {
        set("toolbar",      true);
        set("toolbar_$row", true);
    }
        
    function css_vars_color_scheme_light_brands_toolbar($current_selector = ":root")
    {
        if (has("dom_toolbar_no_css")) return "";
        
        heredoc_start(-2); ?><style>:root {<?php heredoc_flush(null); ?> 

            } .toolbar-row, .toolbar-row * {

            --warning-disable: do not use empty rulesets;

            <?= brand_color_css_property("darkandlight", "#dddddd", 35, "light") ?> 

            } <?= $current_selector ?> {

        <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
    }

    function css_vars_color_scheme_dark_brands_toolbar($current_selector = ":root")
    {
        if (has("dom_toolbar_no_css")) return "";
        
        heredoc_start(-2); ?><style>:root {<?php heredoc_flush(null); ?> 
    
            } .toolbar-row, .toolbar-row * {

            --warning-disable: do not use empty rulesets;

            <?= brand_color_css_property("darkandlight", "#222222", 35, "dark") ?> 

            } <?= $current_selector ?> {

        <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
    }

    function css_toolbar_layout()
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
            
            .toolbar-row                                    { width: 100%; margin-left: 0px !important; margin-right: 0px !important; display: flex; overflow: hidden; }
            .toolbar-row                                    { height: var(--header-toolbar-height); align-items: center; }
            .toolbar-row-banner                             { height: var(--header-height); max-height: var(--header-height); min-height: var(--header-min-height); }

            .toolbar-row, 
            .toolbar-row *                                  { margin: 0; }

            /* QUICK DIRTY TMP HACK */ 
            .toolbar-row-banner .toolbar-cell-left          { display: flex; gap: calc(0.5 * var(--gap)); justify-content: end; margin-right: var(--gap); } 

            .toolbar-row-nav, 
            .toolbar-row-nav *                              { margin: 0; padding: 0; white-space: nowrap; }

            .toolbar-row-nav, 
            .toolbar-row-nav :is(section, div, ul)          { display: flex; /*flex-shrink: 0;*/ align-items: center;  scrollbar-width: none; flex-wrap: nowrap; }
            .toolbar-row-nav,
            .toolbar-row-nav :is(section, div, ul) > *      { flex-shrink: 0; }
            .toolbar-row-nav,
            .toolbar-row-nav ul                             { gap: var(--gap); }
            .toolbar-row-nav li *                           { min-height: var(--line-height); }
            .toolbar-row-nav .toolbar-cell-left .menu-list  { flex-direction: column; align-items: stretch; }

            .toolbar-row-nav :not(:is(section, div, ul, img))   { display: block; }
            
            .toolbar-row-nav .toolbar-cell-left             { width: clamp(calc(var(--header-toolbar-height) - var(--gap)), calc(var(--left-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--left-text-margin-ratio) * 100%)); }

            .toolbar-row-nav .toolbar-cell-center,
            .toolbar-row-nav .toolbar-cell-center *         { flex-shrink: 1; overflow: hidden; text-overflow: ellipsis;  }

            .toolbar-row-nav .toolbar-cell-right            { flex-grow: 1; justify-content: end; } 
            .toolbar-row-nav .toolbar-cell-right            { margin-right: clamp(var(--gap),  calc(var(--right-text-margin-ratio) * calc(100% - var(--max-text-width)) + var(--gap)), calc(var(--right-text-margin-ratio) * 100%)); }

            .toolbar .row.static                            { visibility: hidden; position: fixed; left: 0px; top: 0px; z-index: 999999; } 

            .toolbar .nav-link                              { font-size: 1.5em; } 

            .toolbar .menu-entries                          { flex-direction: column; align-items: stretch; }

            .menu-toggle                                    { width: var(--header-toolbar-height); flex-direction: column; }
            .menu-toggle a,       .toolbar-title a,
            .menu-toggle a:hover, .toolbar-title a:hover    { text-decoration: none; }

            /* Menu open/close mechanism */
    
          /*.menu                                           { display: none }*/ /* BY DEFAULT, DYNAMIC MENU IS NOT SUPPORTED */
          /*a.menu-switch-link.close                        { display: none }*/
    
            /* Menu list */
                
            #<?= DOM_MENU_ID 
            ?>-open .menu                                   { position: absolute; left: var(--gap); transform: translateY(var(--header-toolbar-height)); }
            .menu                                           { max-height: 0; transition: max-height 1s ease-out; text-align: left; }
            .menu ul                                        { padding: 0; gap: 0; list-style-type: none; align-items: stretch; flex-direction: column; }
            .menu li                                        { padding: 0; }
            .menu li > *                                    { padding: calc(0.5 * var(--gap)) var(--gap); }
    
        <?php if (!AMP()) { ?> 
    
            /* Toolbar */
        
            .toolbar { position: sticky; left: 0; top: calc(var(--header-min-height) - var(--header-height)); }
            
            /* Menu open/close mechanism */
    
            <?php if (!!get("dom_toolbar_no_js") || !!get("no_js")) /* When no JS there is no :target use */ { ?>
            #<?= DOM_MENU_ID ?>-open        a.menu-switch-link.open   { display: inline-block !important;  }
            <?php } ?>
            #<?= DOM_MENU_ID ?>-open:target a.menu-switch-link.open   { display: none !important; }/*
            
            #<?= DOM_MENU_ID ?>-open        a.menu-switch-link.close  { display: none !important;  }*/

            <?php if (!!get("dom_toolbar_no_js") || !!get("no_js")) /* When no JS there is no :target use */ { ?>
            #<?= DOM_MENU_ID ?>-open:target a.menu-switch-link.close  { display: inline-block !important; }
            <?php } ?>
            
            #<?= DOM_MENU_ID ?>-open        .menu                     { /*display: none;  */max-height:   0vh; }
            #<?= DOM_MENU_ID ?>-open:target .menu                     { display: flex !important; max-height: 100vh; } /* TODO change to flex ? */
                
        <?php } if (AMP()) { ?> 
    
            /* AMP DEFAULTS */
            
            .menu                                           { display: block } /* AMP DYNAMIC MENU SUPPORTED */
                                            
            amp-sidebar                                     { text-align: left; }
            amp-sidebar .menu                               { position: relative; }
            amp-sidebar ul                                  { list-style-type: none; padding-left: 0px } 
    
        <?php } ?>

            [hidden="hidden"] { display: none !important }

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
    
        <?php heredoc_flush("raw_css"); ?></style><?php return css_layer("base-components", heredoc_stop(null));
    }

    function css_toolbar_colors()
    {
        if (has("dom_toolbar_no_css")) return "";
        
        heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 
    
            /* Colors: toolbar & menu */

            .toolbar-row                            { background-color: var(--theme-color, #000); --color: var(--text-on-theme-color, #000); color: var(--color); }

            .toolbar-cell-left   :is(a, a:visited)  { color: var(--text-on-theme-color,         #eee); }
            .toolbar-cell-center :is(a, a:visited)  { color: var(--text-on-theme-color,         #eee); }
            .toolbar-cell-right  :is(a, a:visited)  { color: var(--text-on-accent-color,        #eee); }

            .toolbar-cell-left   :is(a:hover)       { color: var(--text-on-theme-color-accent,  #fff); }
            .toolbar-cell-center :is(a:hover)       { color: var(--text-on-theme-color-accent,  #fff); }
            .toolbar-cell-right  :is(a:hover)       { color: var(--text-on-accent-color-accent, #fff); }

            .toolbar-cell .menu                     { color: var(--text-darker-color,           #ddd); background-color: var(--background-lighter-color,    #222); box-shadow: 0px 0px 2px 2px #00000022; }
            .toolbar-cell .menu :is(a, a:visited)   { color: var(--link-color,                  #eee); }
            .toolbar-cell .menu a:hover             { color: var(--link-hover-color,            #fff); background-color: var(--background-darker-color,     #000);;}
     
            <?php if (AMP()) { ?> 
            amp-sidebar                             { background-color: var(--background-color, #111); color: var(--text-color, #eee); }
            <?php } ?> 

            /* Menu list */

            .menu                                           { box-shadow: 1px 1px 4px 0 rgba(0,0,0,.2); }
        
        <?php heredoc_flush("raw_css"); ?></style><?php return css_layer("base-components", heredoc_stop(null));
    }
    /*
    function include_css_main_toolbar_adaptation($main_selector = "main") { return delayed_component("_".__FUNCTION__, $main_selector); }

    function _include_css_main_toolbar_adaptation($main_selector)
    {
        if (has("dom_toolbar_no_js")) return "";
        
        if (!!get("toolbar_banner") && !!get("toolbar_nav")) return "$main_selector { margin-top: calc(var(--header-height) + var(--header-toolbar-height)); }";
        if (!!get("toolbar_banner"))                         return "$main_selector { margin-top: calc(var(--header-height)); }";
        if (!!get("toolbar_nav"))                            return "$main_selector { margin-top: calc(var(--header-toolbar-height)); }";

        return "";
    }*/

    function js_toolbar_framework_material()
    {
        if (has("dom_toolbar_no_js")) return "";
        
        if ("material" != get("framework")) return "";

        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

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

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_toolbar_height()
    {
        if (has("dom_toolbar_no_js") || !!get("no_css")) return "";
        
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

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

                    var h = (animate) 
                        ? (header_height + ((target > header_height) ? 1 : -1) * 0.1 * Math.max(1, Math.abs(target - header_height))) 
                        : target;

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
                var toolbars = document.querySelectorAll(".toolbar:not(html[data-css-naked-day] .toolbar)");
                var toolbar  = toolbars ? toolbars[0] : null;

                if (toolbar)
                {
                    toolbar.style.position = "fixed";
                    toolbar.style.top      = "0px";
                    
                    var under_toolbar_element = document.querySelector("body > main");

                    if (under_toolbar_element)
                    {                
                        var toolbar_nav    = toolbar.querySelector(".toolbar-row-nav");
                        var toolbar_banner = toolbar.querySelector(".toolbar-row-banner");

                             if (toolbar_banner && toolbar_nav) { under_toolbar_element.style.marginTop = "calc(var(--header-height) + var(--header-toolbar-height))";   }
                        else if (toolbar_banner)                { under_toolbar_element.style.marginTop = "calc(var(--header-height))";                                  }
                        else if (toolbar_nav)                   { under_toolbar_element.style.marginTop = "calc(var(--header-toolbar-height))";                          }
                    }

                    window.cancelAnimationFrame(idAnimationFrame);
                    updateToolbarHeight(false);
                }
            }

            dom.on_loaded(onInitToolbarHeight);
            dom.on_scroll(onUpdateToolbarHeight);

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_toolbar_menu()
    {
        if (has("dom_toolbar_no_js")) return "";
        
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            /* TOOLBAR MENU */

            function show(selector) {

                /*document.querySelector(selector).style.display = "block";*/
                document.querySelector(selector).removeAttribute("hidden");
            }

            function hide(selector) {

              /*document.querySelector(selector).style.display = "none";*/
                document.querySelector(selector).setAttribute("hidden", "hidden");
            }

            document.querySelectorAll('#menu-open .menu-switch-link.open').forEach(function (e) { e.addEventListener('click', function(ev) {
        
                hide("#menu-open .menu-switch-link.open"  );
                show("#menu-open .menu-switch-link.close" );
                show("#menu-open .menu");

                document.querySelector("#menu-open .menu").style.maxHeight = "100vh";

                ev.preventDefault();
                
                }); });

            document.querySelectorAll('#menu-open .menu-switch-link.close').forEach(function (e) { e.addEventListener('click', function(ev) {
        
                show("#menu-open .menu-switch-link.open"  );
                hide("#menu-open .menu-switch-link.close" );
                hide("#menu-open .menu");

                document.querySelector("#menu-open .menu").style.maxHeight = "initial";

                ev.preventDefault();

                }); });

            document.querySelectorAll('#menu-open .menu-list a').forEach(function (e) { e.addEventListener('click', function(ev) {

                show("#menu-open .menu-switch-link.open"  );
                hide("#menu-open .menu-switch-link.close" );
                hide("#menu-open .menu");

                document.querySelector("#menu-open .menu").style.maxHeight = "initial";

              /*ev.preventDefault();*/ /* Menu links keep being recorded in navigation history */

                }); });

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_toolbar_banner_rotation()
    {
        if (has("dom_toolbar_no_js")) return "";

        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 
            
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
                                    toolbar_row_banner.style.backgroundColor = "var(--theme-color)";
                                    toolbar_row_banner.style.backgroundImage = "var(--linear-gradient), url(" + urls[index_url] + ")";
                                    index_url = (index_url + 1) % urls.length;
                                }

            
                            }, 10*1000);
                        }
                    }
                };
                
                <?php if (has("noajax") && is_string(get("support_header_backgrounds"))) { ?>
                rotate_backgrounds("<?= get("support_header_backgrounds") ?>"); 
                <?php } else { ?> 
                ajax("?ajax=header-backgrounds", rotate_backgrounds);
                <?php } ?> 
            }

            on_loaded(onInitRotatingHeaders);
        
        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function scripts_body_toolbar()
    {
        if (has("ajax")) return "";

        return                                                            ((!!get("toolbar_support_height",      true)) ? (
                script(js_toolbar_height                ()).   "") : ""). ((!!get("support_header_backgrounds", false)) ? (
                script(js_toolbar_banner_rotation       ()).   "") : ""). ((!!get("script_toolbar_menu",         true)) ? (
                script(js_toolbar_menu                  ()).   "") : ""). ((!!get("script_framework_material",   true)) ? (
                script(js_toolbar_framework_material    ()).   "") : "");
    }

    // ICONS
    
    function icon_entry($icon, $label = "", $link = "JAVASCRIPT_VOID", $attributes = false, $target = false, $id = false, $encrypted = false)
    {
        $link = ("JAVASCRIPT_VOID" == $link) ? url_void() : $link;
        
        if (($attributes === internal_link || $attributes === external_link) && $target === false) { $target = $attributes; $attributes = false; }
        if ($target === false) { $target = internal_link; }
        
        return array($icon, $label, $link, $id, $target, $attributes, $encrypted);
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
        
        if (!in_array("aria-label", $attributes)                    ) $attributes["aria-label"  ] = $label;
        if (!in_array("alt",        $attributes) && !AMP()          ) $attributes["alt"         ] = $label;
        if (!in_array("id",         $attributes) && (false !== $id) ) $attributes["id"          ] = $id;

        if ($encrypted)
        {
            return a_encrypted($link, $icon, $attributes, $target);
        }
        else
        {
            if ($link == false || $link == url_void() || $link == url_empty())
            {
                $attributes = \dom\attributes_add_class($attributes, "transparent link");

                return button($icon, $attributes);
            }
            else if (0 === stripos($link, "javascript:"))
            {
                $js_function = trim(str_replace("javascript:", "", $link), ";");
              //$js_function = substr($js_function, 0, stripos($js_function, "("));

                $attributes = \dom\attributes_add_class($attributes, "transparent link");
                $attributes = \dom\attributes_add($attributes, \dom\attributes(\dom\attr("onclick", $js_function) ));

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

    $__ul_menu_index = -1;

    function ul_menu($menu_entries = array(), $default_target = internal_link, $sidebar = auto)
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
                    $menu_lis .= li("", array("class" => component_class("li", "list-item-separator")/*, "role" => "separator"*/));
                }
                else
                {    
                //  if (!is_array($menu_entry)) $menu_entry = array($menu_entry, url_void());
                    if (!is_array($menu_entry)) $menu_entry = array($menu_entry, "#".anchor_name($menu_entry));
                            
                    $item       = get($menu_entry, "item",       get($menu_entry, 0, ""));
                    $link       = get($menu_entry, "link",       get($menu_entry, 1, false));
                    $target     = get($menu_entry, "target",     get($menu_entry, 2, $default_target));
                    $attributes = get($menu_entry, "attributes", get($menu_entry, 3, false));
                    
                    $menu_lis .= li(a(span($item), $link, $attributes, $target), array("class" => component_class("li", "list-item")/*, "role" => "menuitem"*/, "tabindex" => "0"));
                }
            }
        }

        $html = "";

             if (AMP())                             { $html =  ul($menu_lis, array(/*"role" => "group",*/ "class" => component_class("ul",  'menu-list')/*." ".component_class('menu')*/ /*, "role" => "menu", "aria-hidden" => "true" */                                                )); }
        else if (get("framework") == "bootstrap")   { $html = div($menu_lis, array(/*"role" => "group",*/ "class" => component_class("div", 'menu-list')                                 /*, "role" => "menu", "aria-hidden" => "true", "aria-labelledby" => "navbarDropdownMenuLink" */ )); }
        else                                        { $html =  ul($menu_lis, array(/*"role" => "group",*/ "class" => component_class("ul",  'menu-list')                                 /*, "role" => "menu" */                                                                         )); }

        if (AMP() && !!$sidebar)
        {
            hook_amp_sidebar(
                tag('amp-sidebar class="menu" id="'.DOM_MENU_ID.'" layout="nodisplay"', $html)
                );

            //$html = span("","placeholder-amp-sidebar");
        }

        return $html;
    }

    function menu_switch() { return (get("framework") == "material"  ? (a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons mdc-top-app-bar__icon--menu", /*"role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false",*/ "on" => ("tap:".DOM_MENU_ID.".toggle")                                                                ))) : "")
                                .   (get("framework") == "bootstrap" ? (a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons",                             /*"role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false",*/ "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  ))) : "")
                                .   (get("framework") == "spectre"   ? (a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons",                             /*"role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false",*/ "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  ))) : "")
                                .   (get("framework") == "NONE"      ? (a(span("☰", "menu-switch-symbol menu-toggle-content")   
                                                                      . a(span("✕", "menu-close-symbol  menu-close-content"), "#".DOM_MENU_ID."-close", array("class" => "menu-switch-link close nav-link material-icons", "hidden" => "hidden"/*, "aria-label" => "Menu Toggle"*/))
                                                                                                                            , "#".DOM_MENU_ID."-open",  array("class" => "menu-switch-link open nav-link material-icons", "name" => "menu-close",                            /*"role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false",*/ "on" => ("tap:".DOM_MENU_ID.".toggle")                     ))) : "")
                                                            ; 
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
      //return a(T("Skip to main"), "#".anchor_name(get("title")),  "skip-to-main");
        return a(T("Skip to main"), "#main",                        "skip-to-main");
    }

    function toolbar_banner_sections_builder($section1 = false, $section2 = false, $section3 = false)
    {
        $skip_to_main = delayed_component("toolbar_skip_to_main");

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
            
            "role"  => ("navigation"/*." "."menuItem"*/),
            "class" => (component_class("div", "toolbar-cell-right") . ' ' . 
                        component_class("div", "toolbar-cell-right-shrink"))));
    }

    function  ul_menu_auto($sidebar = auto) { return delayed_component("_".__FUNCTION__, $sidebar); }
    function _ul_menu_auto($sidebar = auto) { $sections = get("hook_sections"); return (!is_array($sections) || count($sections) < 2) ? "" : ul_menu($sections, internal_link, $sidebar); }

    function  menu_toggle_auto($sidebar = auto) { return menu_toggle(ul_menu_auto(), $sidebar); }

    function toolbar_nav_menu($html = false, $attributes = false, $menu_entries_shrink_to_fit = false, $sidebar = auto)
    {
        if (!!get("gemini")) return "";
        
        if (false !== $html && false === stripos($html, "menu-toggle")) $html = menu_toggle($html);
        if (false === $html)                                            $html = menu_toggle_auto($sidebar);
        
        return toolbar_section(

            ($html === false) ? '' : $html,

            attributes(
              /*attr("role", "menu"),*/
                attr("class",
                    component_class("div", "toolbar-cell-left") .          ($menu_entries_shrink_to_fit ? (' '.
                    component_class("div", "toolbar-cell-right-shrink")    ) : "")
                    )
                )
            );
    }

    function toolbar_nav_title($html, $attributes = false)
    {
        hook_title($html);

        if ($html !== false && $html != "")
        {
            if (false === stripos($html,"<a"))   $html =   a($html, '.');
            if (false === stripos($html,"<h1"))  $html =  h1($html);
        }

        if (false === stripos($html,"<div")) $html = div($html, "toolbar-title");
        
        return toolbar_section(($html === false) ? '' : $html, attributes(/*attr("role", "navigation menuitem"), */component_class("div", "toolbar-cell-center")));
    }

    function toolbar_nav($html, $attributes = false)
    {
        hook_toolbar("nav");

        if (false === stripos($html,"toolbar-cell")) $html = toolbar_nav_menu().toolbar_nav_title($html);
        
        $menu_id_amp = DOM_MENU_ID."-static";
        
        $html_amp = $html;
        $html_amp = str_replace(DOM_MENU_ID.'.toogle',  $menu_id_amp.'.toogle',  $html_amp);
        $html_amp = str_replace('id="'.DOM_MENU_ID.'"', 'id="'.$menu_id_amp.'"', $html_amp);

                   $html  = toolbar_row($html,     array("id" => "toolbar-row-nav",        /*"role" => "menubar",*/ "class" => "toolbar-row-nav"         ));
        if (AMP()) $html .= toolbar_row($html_amp, array("id" => "toolbar-row-nav-static", /*"role" => "menubar",*/ "class" => "toolbar-row-nav static"  ));

        return $html;
    }

    function toolbar($html, $attributes = false)
    {
        //if (!!get("gemini")) return "";

        if (false === stripos($html,"toolbar-row")) $html = toolbar_banner().toolbar_nav($html);
        
        $amp_observer = "";
        $amp_anim     = "";
        
        if (AMP())
        {            
            hook_amp_require("animation");
            hook_amp_require("position-observer");

            $amp_anim     = eol().'<amp-animation id="toolbarStaticShow" layout="nodisplay"><script type="application/json">{ "duration": "0", "fill": "forwards", "animations": [ { "selector": "#toolbar-row-nav-static", "keyframes": { "visibility": "visible" } } ] }</script></amp-animation>'
                          . eol().'<amp-animation id="toolbarStaticHide" layout="nodisplay"><script type="application/json">{ "duration": "0", "fill": "forwards", "animations": [ { "selector": "#toolbar-row-nav-static", "keyframes": { "visibility": "hidden"  } } ] }</script></amp-animation>';

            $amp_observer = eol().'<amp-position-observer target="toolbar-row-nav" intersection-ratios="1" on="enter:toolbarStaticHide.start;exit:toolbarStaticShow.start" layout="nodisplay"></amp-position-observer>';
        }

        return  

            $amp_anim.
            comment("PRE Toolbar").
            header(
                $html.
                $amp_observer, 
                attributes_add_class($attributes, component_class("header", "toolbar toolbar-container"))
                ).
            "";
    }

?>