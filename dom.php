<?php #https://github.com/villepreux/dom

    #region DOM PUBLIC API
    ######################################################################################################################################
    
    # Forward shortname function (that mimic html markup) to private API functions (if names are not used)

    if (!defined("INTERNAL_LINK"))                      define("INTERNAL_LINK", "_self");
    if (!defined("EXTERNAL_LINK"))                      define("EXTERNAL_LINK", "_blank");

    #region API : GET/SET
    ######################################################################################################################################

    if (!function_exists("at"))                         { function at($a, $k, $d = false)                                                                   { return dom_at($a, $k, $d);                    }
    if (!function_exists("get_all"))                    { function get_all($get = true, $post = true, $session = false)                                     { return dom_get_all($get, $post, $session);    }
    if (!function_exists("has"))                        { function has($k_or_a, $__or_k = false)                                                            { return dom_has($k_or_a, $__or_k);             }
    if (!function_exists("get"))                        { function get($k_or_a, $d_or_k = false, $__or_d = false)                                           { return dom_get($k_or_a, $d_or_k, $__or_d);    }
    if (!function_exists("del"))                        { function del($k)                                                                                  { return dom_del($k);                           }
    if (!function_exists("set"))                        { function set($k, $v = true, $aname = false)                                                       { return dom_set($k, $v, $aname);               }

    if (!function_exists("is_localhost"))               { function is_localhost()                                                                           { return dom_is_localhost(); } }
    if (!function_exists("AMP"))                        { function AMP()                                                                                    { return dom_AMP(); } }

    #endregion
    #region HELPERS : AJAX / ASYNC
    ######################################################################################################################################

    if (!function_exists("ajax_classes"))               { function ajax_classes     ($ajax_params, $extra = false)                                          { return dom_ajax_classes   ($ajax_params, $extra);                } }
    if (!function_exists("ajax_container"))             { function ajax_container   ($ajax_params, $placeholder = false, $period = -1)                      { return dom_ajax_container ($ajax_params, $placeholder, $period); } }
            
    if (!function_exists("async"))                      { function async            ($f)                                                                    { return $args = func_get_args(); dom_async_FUNC_ARGS($f, $args); } }

    #endregion
    #region JAVASCRIPT SNIPPETS
    ######################################################################################################################################

    if (!function_exists("string_script_ajax_head"))    { function string_script_ajax_head  ()                                                              { return dom_string_script_ajax_head(); } }
    if (!function_exists("string_script_ajax_body"))    { function string_script_ajax_body  ()                                                              { return dom_string_script_ajax_body(); } }

    #endregion
    #region SCRIPT WIDGETS
    ######################################################################################################################################

    if (!function_exists("script_ajax_head"))           { function script_ajax_head         ()                                                              { return dom_script_ajax_head(); } }
    if (!function_exists("script_ajax_body"))           { function script_ajax_body         ()                                                              { return dom_script_ajax_body(); } }
    
    #endregion
    #region DEBUG
    ######################################################################################################################################

    if (!function_exists("debug_timings"))              { function debug_timings()                                                                          { return dom_debug_timings();                       } }
    if (!function_exists("debug_callstack"))            { function debug_callstack($shift_current_call = true)                                              { return dom_debug_callstack($shift_current_call);  } }

    #endregion
    #region HELPERS : LOCALIZATION
    ######################################################################################################################################

    if (!function_exists("T"))                          { function T($label, $default = false, $lang = false)                                               { return dom_T($label, $default = false, $lang = false); } }

    #endregion
    #region ... WIP
    ######################################################################################################################################

    if (!function_exists("a"))                          { function a($html, $url = false, $attributes = false, $target = false)                             { return dom_a($html, $url, $attributes, $target); } }

    # ... WIP

    ######################################################################################################################################
    #endregion

    ######################################################################################################################################
    #endregion DOM PUBLIC API
















































    #region PRIVATE API
    ######################################################################################################################################
    
    define("DOM_AUTHOR",            "Antoine Villepreux");
    define("DOM_VERSION",           "0.3.b");
    define("DOM_PATH_MAX_DEPTH",    16);

    #region API : GET/SET
    ######################################################################################################################################

    function dom_at($a, $k, $d = false)                                 { if (is_array($k)) { foreach ($k as $k0) { if (!is_array($a) || !array_key_exists($k0,$a)) return $d; $a = dom_at($a, $k0, $d); } return $a; } else { return (is_array($a) && array_key_exists($k,$a)) ? $a[$k] : $d; } } }
    function dom_get_all($get = true, $post = true, $session = false)   { $a = array(); if ($get) $a = array_merge($a, $_GET); if ($post) $a = array_merge($a, $_POST); if ($session && isset($_SESSION) && is_array($_SESSION)) { $a = array_merge($a, $_SESSION); } return $a; } }
    function dom_has($k_or_a, $__or_k = false)                          { return (is_array($k_or_a)) ? @array_key_exists($__or_k, $k_or_a) : @array_key_exists($k_or_a, dom_get_all()); } }
    function dom_get($k_or_a, $d_or_k = false, $__or_d = false)         { return (is_array($k_or_a)) ? dom_at($k_or_a, $d_or_k, $__or_d) : dom_at(dom_get_all(), $k_or_a, $d_or_k); } }
    function dom_del($k)                                                { if (dom_has($_GET,$k)) unset($_GET[$k]); if (dom_has($_POST,$k)) unset($_POST[$k]); if (isset($_SESSION) && dom_has($_SESSION,$k)) unset($_SESSION[$k]); } }
    function dom_set($k, $v = true, $aname = false)                     { if ($aname === false) { $_GET[$k] = $v; } else if ($aname === "POST") { $_POST[$k] = $v; } else if ($aname === "SESSION" && isset($_SESSION)) { $_SESSION[$k] = $v; } } }

    function dom_is_localhost()                                         { return (false !== stripos($_SERVER['HTTP_HOST'], "localhost")) || (false !== stripos($_SERVER['HTTP_HOST'], "127.0.0.1")); }

    #endregion
    #region DEPENDENCIES
    ######################################################################################################################################
    
                                        @include dom_path("tokens.php");
    if (!function_exists("markdown"))   @include dom_path("php/vendors/markdown.php");
                                        @include dom_path("php/vendors/smartypants.php");
        
    #endregion
    #region CONFIG : PHP SETTINGS
    ######################################################################################################################################

    function dom_init_php()
    {
        if (dom_is_localhost())
        {
            @set_time_limit(10*60);
            @ini_set('memory_limit', '-1');
        }

        if (!!dom_get("debug"))
        {
            @ini_set('display_errors',1);
        }
        
        @date_default_timezone_set('Europe/Paris');
        @date_default_timezone_set('GMT');

        if (!defined('PHP_VERSION_ID')) { $version = explode('.',PHP_VERSION); define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2])); }
    }
    
    #endregion
    #region CONFIG : USER OPTIONS
    ######################################################################################################################################

    function dom_init_options()
    {
        // Cannot be modified at browser URL level

    //  dom_set("title",                             "Blog");
        dom_set("keywords",                          "");

        dom_set("canonical",                         absolute_uri(false));
        dom_set("url",                               absolute_uri());                         if (dom_path("DTD/xhtml-target.dtd", dom_path("xhtml-target.dtd")))
        dom_set("DTD",                              'PUBLIC "-//W3C//DTD XHTML-WithTarget//EN" "'.dom_path("DTD/xhtml-target.dtd", dom_path("xhtml-target.dtd")).'"');

        dom_set("normalize",                        "sanitize");

        dom_set("background_color",                 "#FFF");
        dom_set("theme_color",                      "#000");
        dom_set("text_color",                       "#000");
        dom_set("link_color",                       "#00F");
        
        dom_set("header_height",                    "256px");
        dom_set("header_min_height",                  "0px");
        dom_set("header_toolbar_height",             "48px");
            
        dom_set("default_image_width",              "300");
        dom_set("default_image_height",             "200");

        dom_set("image",                            "image.png");
        dom_set("geo_region",                       "FR-75");
        dom_set("geo_placename",                    "Paris");
        dom_set("geo_position_x",                   48.862808);
        dom_set("geo_position_y",                    2.348237);

        dom_set("support_service_worker",           true);
        
    //	dom_set("fonts",                            "Roboto:300,400,500");
            
        dom_set("twitter_page",                     "me");
        dom_set("linkedin_page",                    "me");
        dom_set("facebook_page",                    "me");
        dom_set("tumblr_blog",                      "blog");
        dom_set("instagram_user",                   "self");
        dom_set("pinterest_user",                   "blog");
        dom_set("pinterest_board",                  "blog");
        dom_set("flickr_user",                      "blog");
        dom_set("messenger_id",                     "me");
            
        dom_set("exclude_pinterest_pins_ids",       "");
        dom_set("exclude_tumblr_slugs",             "");
        dom_set("exclude_instagram_codes",          "");
        dom_set("exclude_instagram_users",          "");
        dom_set("exclude_facebook_post_ids",        "");
        dom_set("exclude_facebook_text_md5s",       "");
            
        dom_set("support_metadata_person",          true);
        dom_set("support_metadata_organization",    true);
            
        dom_set("include_custom_css",               false);
            
        dom_set("carousel",                         true);
            
        dom_set("version_normalize",                "7.0.0");
        dom_set("version_sanitize",                 "9.0.0");  
        dom_set("version_material",                "0.38.2"); // latest => SimpleMenu got broken in 0.30.0 => Got fixed in CSS => latest => Broken in 0.39.0 => 0.38.0
        dom_set("version_bootstrap",                "4.1.1");
        dom_set("version_spectre",                  "x.y.z");
        dom_set("version_popper",                  "1.11.0");
        dom_set("version_jquery",                   "3.4.1"); // Was 3.2.1
        dom_set("version_slick",                    "1.6.0");  
    //  dom_set("version_slick",                    "1.5.8"); // TRYING TO FIX 1.6.0 on iPhone
        dom_set("version_prefixfree",               "1.0.7");
        dom_set("version_h5bp",                     "7.1.0");
        
        dom_set("cache_time",                       1*60*60); // 1h

        // Can be modified at browser URL level

        dom_set("framework",                        dom_get("framework",    "NONE"                  ));
        dom_set("amp",                              dom_get("amp",          false                   ));
        dom_set("cache",                            dom_get("cache",        false                   ));
        dom_set("minify",                           dom_get("minify",       !dom_get("beautify",false)  )); // Performances first
        dom_set("page",                             dom_get("page",         1                       ));
        dom_set("n",                                dom_get("n",            12                      ));
    }

    #endregion
    #region CONFIG : INTERNALS
    ######################################################################################################################################

    function dom_init_internals()
    {
        define("DOM_AJAX_PARAMS_SEPARATOR1", "-_-");
        define("DOM_AJAX_PARAMS_SEPARATOR2", "_-_");
    }

    #endregion
    #region HELPERS : AJAX / ASYNC
    ######################################################################################################################################

    function dom_ajax_url           ($ajax_params)                                      { return './?'.http_build_query(array_merge(dom_get_all(true,false), array("ajax" => $ajax_params))); }

    function dom_ajax_param_encode2 ($p)                                                { return (is_array($p))                                     ? implode(DOM_AJAX_PARAMS_SEPARATOR2, $p) : $p; }
    function dom_ajax_param_decode2 ($p)                                                { return (false !== strpos($p, DOM_AJAX_PARAMS_SEPARATOR2)) ? explode(DOM_AJAX_PARAMS_SEPARATOR2, $p) : $p; }

    function dom_ajax_param_encode  ($prefix, $params = array())                        {                                               return $prefix . '-' .                     implode(DOM_AJAX_PARAMS_SEPARATOR1, array_map("dom_ajax_param_encode2", $params)); }
    function dom_ajax_param_decode  ($prefix, $params)                                  { $params = substr($params, strlen($prefix)+1); return array_map("dom_ajax_param_decode2", explode(DOM_AJAX_PARAMS_SEPARATOR1, $params)); }
    
    function dom_ajax_classes       ($ajax_params, $extra = false)                      { return "ajax-container ajax-container-".to_classname($ajax_params).(($extra !== false) ? (" ajax-container-".to_classname($extra)) : ""); }
    function dom_ajax_container     ($ajax_params, $placeholder = false, $period = -1)  { return  (($placeholder === false) ? ('<div class="'.dom_ajax_classes($ajax_params).'"></div>') : $placeholder) . '<script>dom_ajax("'.dom_ajax_url($ajax_params).'", function(content) { $(".ajax-container-'.to_classname($ajax_params).'").fadeOut("slow", function() { $(this).replaceWith(content); $(this).fadeIn("slow"); }); }, '.$period.'); </script>'; }

    function dom_ajax_call($f)
    {            
        $args = func_get_args();
        return dom_ajax_call_FUNC_ARGS($f, $args);
    }
        
    function dom_ajax_call_FUNC_ARGS($f, $args)
    {            
        $period = -1;

        if (is_numeric($f))
        {
            $period = $f;
            array_shift($args);
            $f = $args[0];
        }

        array_shift($args);

        return dom_ajax_call_with_args($f, $period, $args);
    }
        
    function dom_ajax_call_with_args($f, $period, $args)
    {
    //  Async calls disabled
        
        if (dom_has("noajax") || dom_has("rss") || dom_AMP())
        {  
            return call_user_func_array($f, $args);
        }
        
    //  Async calls enabled
        
        $ajax = dom_get("ajax", false);

        if (false === $ajax)
        {
        //  Async caller (or client)
        
            foreach ($args as &$arg)
            {
                if (false === $arg) $arg = "FALSE";
                if (true  === $arg) $arg = "TRUE";
            }
            
            $ajax = dom_ajax_param_encode($f, $args);

            return dom_ajax_container($ajax, img_loading(dom_ajax_classes($ajax, $f)), $period);
        }
        else
        {
        //  Async listener (or server)
        
            global $call_asyncs_started;
            if (!$call_asyncs_started)    return ""; // We have not started listening yet
            if (0 !== stripos($ajax, $f)) return ""; // This is not the function you are looking for
            
            $args = dom_ajax_param_decode($f, $ajax);
            
            foreach ($args as &$arg)
            {
                if ($arg === "FALSE") $arg = false;
                if ($arg === "TRUE")  $arg = true;
            }    

            return call_user_func_array($f, $args);
        }
    }

    #endregion
    #region JAVASCRIPT SNIPPETS
    ######################################################################################################################################
    
    function dom_string_script_ajax_head()
    {
        return  eol() . tab(1) .   '/* DOM Javascript boilerplate */'
            .   eol()
            .   eol() . tab(1) .   'var dom_ajax_pending_calls = [];'
            .   eol()
            .   eol() . tab(1) .   'function dom_ajax(url, onsuccess, period, onstart, mindelay)'
            .   eol() . tab(1) .   '{'
            .   eol() . tab(2) .       'dom_ajax_pending_calls.push(new Array(url, onsuccess, period, onstart, mindelay));'
            .   eol() . tab(1) .   '};'
            .   eol();
    }
    
    function dom_string_script_ajax_body()
    {
        return  eol() . tab(1) .   '/* DOM Javascript boilerplate */'
            .   eol()
            .   eol() . tab(1) .   'var dom_process_ajax = function(url, onsuccess, period, onstart, mindelay)'
            .   eol() . tab(1) .   '{'
            .   eol() . tab(2) .       'if (typeof onsuccess    === "undefined") onsuccess  = null;'
            .   eol() . tab(2) .       'if (typeof period       === "undefined") period     = 0;'
            .   eol() . tab(2) .       'if (typeof onstart      === "undefined") onstart    = null;'
            .   eol() . tab(2) .       'if (typeof mindelay     === "undefined") mindelay   = 500;'
            .   eol()
            .   eol() . tab(2) .       'var cb = true;'
            .   eol()
            .   eol() . tab(2) .       'if (onstart)'
            .   eol() . tab(2) .       '{'
            .   eol() . tab(3) .           'onstart();'
            .   eol() . tab(3) .           'cb = null;'
            .   eol() . tab(3) .           'setTimeout(function() { if(cb) { cb(); } else { cb = true; } }, mindelay);'
            .   eol() . tab(2) .       '}'
        //  .   eol()
        //  .   eol() . tab(2) .       'console.log("DOM Ajax call : " + onsuccess);'
            .   eol()
            .   eol() . tab(2) .       '$.ajax'
            .   eol() . tab(2) .       '({'
            .   eol() . tab(3) .           'url:      url'
            .   eol() . tab(2) .       ',   type:    "GET"'
            .   eol() . tab(2) .       ',   async:    true'
            .   eol() . tab(2) .       ',   success:  function(res) { if (onsuccess) { if (cb) { onsuccess(res); } else { cb = function() { onsuccess(res); }; } } }'
            .   eol() . tab(2) .       ',   complete: function()    { if (period > 0) { setTimeout(function() { dom_ajax(url, onsuccess, period, onstart, mindelay); }, period); } }'
            .   eol() . tab(2) .       '});'
            .   eol() . tab(1) .   '};'
            .   eol()
            .   eol() . tab(1) .   'var dom_pop_ajax_call = function()'
            .   eol() . tab(1) .   '{'
            .   eol() . tab(2) .       'if (dom_ajax_pending_calls.length > 0)'
            .   eol() . tab(2) .       '{'
            .   eol() . tab(3) .           'var ajax_pending_call = dom_ajax_pending_calls.pop();'
            .   eol()
            .   eol() . tab(3) .            ((!!dom_get("debug")) ? 'console.log("Processing ajax pending call: " + ajax_pending_call[0]); console.log(ajax_pending_call); ' : '') 
            .   eol() . tab(3) .           'dom_process_ajax(ajax_pending_call[0], ajax_pending_call[1], ajax_pending_call[2], ajax_pending_call[3], ajax_pending_call[4]);'
            .   eol() . tab(2) .       '}'
            .   eol() . tab(1) .   '};'
            .   eol()
            .   eol() . tab(1) .   'while (dom_ajax_pending_calls.length > 0) { dom_pop_ajax_call(); };'
            .   eol() . tab(1) .   'setInterval(dom_pop_ajax_call, 1*1000);'
            .   eol();
    }

    #endregion
    #region HELPERS : PATH FINDER
    ######################################################################################################################################
        
    function dom_path($path, $default = false, $search = true, $depth = 16, $max_depth = DOM_PATH_MAX_DEPTH)
    {
    //	If URL format then keep it as-is

        if ($path{0} == "/" && $path{1} == "/") return $path;
        if (0 === stripos($path, "http"))       return $path;

    //	If absolute path and localhost then use root trick if available 

        if (is_localhost() && !!dom_get("localhost_root") && ($max_depth == $depth) && (false === stripos($path, dom_get("localhost_root"))) 
        &&  (strlen($path) > 0 && $path{0} == "/")) 
        {
            $path = substr_replace($path, dom_get("localhost_root"), 0, 1);
        }

    //	If path exists then directly return it

        if (@file_exists($path)) 							{ return $path; }
        if (($max_depth == $depth) && url_exists($path))	{ return $path; }

    //	If we have already searched too many times then return fallback

        if ($depth <= 0) { return $default; }

    //	If we have reached designated root then return fallback

        if (@file_exists("./".dom_get("root_file_hint","#"))) { return $default; }

    //	If requested then search in parent folder

        if ($search)
        {
            $pathinfo   = @pathinfo($path);
            $dirname    = at($pathinfo, "dirname");
            $basename   = at($pathinfo, "basename");

            return dom_path("../$dirname/$basename", $default, $search, $depth - 1, $max_depth);
        }

        return $default;
    }

    function dom_path_coalesce()
    {
        $args = func_get_args();
        return dom_path_coalesce_FUNC_ARGS($args);
    }

    function dom_path_coalesce_FUNC_ARGS($args)
    {
        foreach ($args as $arg)
        {
            $path = dom_path($arg);
            if (!!$path) return $path;
        }

        return false;
    }

    #endregion
    #region HELPERS : DOM COMPONENTS: TAG ATTRIBUTES
    ######################################################################################################################################

    function dom_attributes($attributes, $pan = 0)
    {
        if (false === $attributes) return "";
        if (""    === $attributes) return "";
        if (" "   === $attributes) return "";
            
        if (is_array($attributes))
        {
            $html = '';
            
            if (is_array($pan)) { $i = 0; foreach ($attributes as $key => $value) { $html .= pan(' ' . $key . '=' . '"' . $value . '"', $pan[$i], ' ', 1); ++$i; } }
            else                {         foreach ($attributes as $key => $value) { $html .= pan(' ' . $key . '=' . '"' . $value . '"', $pan,     ' ', 1);       } }
            
            return $html;
        }
        
        if (false === strpos($attributes, '=')) { return ' class="' . $attributes.'"'; }
        if (' '   !=         $attributes{0})    { return ' '        . $attributes;     }
        
        return $attributes;
    }
    
    function dom_attributes_add_class($attributes, $classname)
    {
        $attributes = dom_attributes($attributes);

        if ("" === $attributes) return dom_attributes($classname);
            
        $bgn = stripos($attributes, "class=");      if (false === $bgn) return $attributes;
        $bgn = stripos($attributes, '"', $bgn);     if (false === $bgn) return $attributes;
        $end = stripos($attributes, '"', $bgn + 1); if (false === $bgn) return $attributes;

        $classes    = substr($attributes, $bgn + 1, $end - $bgn - 1) . " " . $classname;       
        $attributes = substr($attributes, 0, $bgn + 1) . $classes . substr($attributes, $end);

        return $attributes;
    }
    
    function dom_frameworks_material_classes_grid_cells() { $a = array(); foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $s) foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $m) foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $l) $a["grid-cell-$s-$m-$m"] = 'mdc-layout-grid__cell--span-'.$s.'-phone mdc-layout-grid__cell--span-'.$m.'-tablet mdc-layout-grid__cell--span-'.$l.'-desktop'; return $a; }

    $frameworks = array
    (
        'material' => array
        (
            'classes' => array_merge(array
            (
                'body'                      => 'mdc-typography'
            ,   'dark'                      => 'mdc-theme--dark'
                    
            ,   'card'                      => 'mdc-card'
            ,   'card-horizontal'           => 'mdc-card__horizontal-block'
            ,   'card-title'                => 'mdc-card__primary'
            ,   'card-title-icon'           => ''
            ,   'card-title-link'           => ''
            ,   'card-title-main'           => 'mdc-card__title mdc-card__title--large mdc-theme--primary'
            ,   'card-title-sub'            => 'mdc-card__subtitle'
            ,   'card-media'                => 'mdc-card__media'
            ,   'card-text'                 => 'mdc-card__supporting-text'
            ,   'card-actions'              => 'mdc-card__actions'   
            ,   'card-action-button'        => 'mdc-button mdc-button--compact mdc-card__action'
            ,   'card-thumb'                => 'mdc-card__thumb'
                
            ,   'button'                    => 'mdc-button'
            ,   'button-label'              => 'mdc-button__label'
            ,   'action-button'             => 'mdc-fab'
            ,   'action-button-icon'        => 'mdc-fab__icon'
                    
            ,   'headline1'                 => 'mdc-typography--headline4'
            ,   'headline2'                 => 'mdc-typography--headline6'
            ,   'headline3'                 => 'mdc-typography--headline8'
            ,   'headline4'                 => 'mdc-typography--headline10'
            ,   'headline5'                 => 'mdc-typography--headline12'
            ,   'headline6'                 => 'mdc-typography--headline14'
            ,   'headline7'                 => 'mdc-typography--headline16'
            ,   'headline8'                 => 'mdc-typography--headline18'
            ,   'headline9'                 => 'mdc-typography--headline20'
            
            ,   'toolbar'                   => 'mdc-top-app-bar mdc-top-app-bar--dense mdc-top-app-bar--fixed mdc-top-app-bar--waterfall mdc-top-app-bar--flexible mdc-top-app-bar--flexible-default-behavior mdc-top-app-bar--fixed-lastrow-only'
            ,   'toolbar-row'               => 'mdc-top-app-bar__row row'
            ,   'toolbar-cell'              => 'mdc-top-app-bar__section'
            ,   'toolbar-cell-right'        => 'mdc-top-app-bar__section--align-end'
            ,   'toolbar-cell-left'         => 'mdc-top-app-bar__section--align-start'
            ,   'toolbar-cell-center'       => 'mdc-top-app-bar__section--align-middle'
            ,   'toolbar-cell-shrink'       => 'mdc-top-app-bar__section--shrink-to-fit'
            
            ,   'toolbar-title'             => 'mdc-top-app-bar__title'

            ,   'main-below-toolbar'        => 'mdc-top-app-bar--dense-'
            ,   'footer'                    => 'mdc-theme--primary'
            ,   'grid'                      => 'mdc-layout-grid max-width'
            ,   'grid-row'                  => 'mdc-layout-grid__inner'
            ,   'grid-cell'                 => 'mdc-layout-grid__cell'), dom_frameworks_material_classes_grid_cells(), array(
            
                'progressbar'               => 'mdc-linear-progress mdc-linear-progress--indeterminate'
            ,   'progressbar-buffer'        => 'mdc-linear-progress__buffer'
            ,   'progressbar-buffer-dots'   => 'mdc-linear-progress__buffering-dots'
            ,   'progressbar-primary-bar'   => 'mdc-linear-progress__bar mdc-linear-progress__primary-bar'
            ,   'progressbar-secondary-bar' => 'mdc-linear-progress__bar mdc-linear-progress__secondary-bar'   
            ,   'progressbar-bar-inner'     => 'mdc-linear-progress__bar-inner'
            
            ,   'toolbar-icon'              => 'material-icons mdc-top-app-bar__icon'

            ,   'menu-toggle'               => 'mdc-menu-anchor'
            ,   'menu'                      => 'mdc-menu'
            ,   'menu-list'                 => 'mdc-menu__items mdc-list sidebar'
            ,   'menu-list-item'            => 'mdc-list-item'
            ,   'menu-list-item-separator'  => 'mdc-list-divider'
            
            ,   'list'                      => 'mdc-list'
            ,   'list-item'                 => 'mdc-list-item'
            ,   'list-item-separator'       => 'mdc-list-divider'
                
            ))
        )
        
    ,   "bootstrap" => array
        (
            "classes" => array
            (
                'menu-list'             => 'dropdown-menu sidebar'
            ,   'list-item-separator'   => 'dropdown-divider'
            ,   'toolbar'               => 'navbar sticky-top'        
            )
        )
        
    ,   "spectre" => array
        (
            "classes" => array
            (
                'button'                    => 'btn'
            
            ,   'toolbar-row'               => 'navbar'
            ,   'toolbar-cell'              => 'navbar-section'
                    
            ,   'card-title'                => 'card-header'
            ,   'card-title-icon'           => ''
            ,   'card-title-link'           => ''
            ,   'card-title-main'           => 'card-title'
            ,   'card-title-sub'            => 'card-subtitle'
            ,   'card-media'                => 'card-image'
            ,   'card-text'                 => 'card-body'
            

            )
        )
    );
    
    function dom_component_class($classname) 
    {
        global $frameworks;
        
        $framework = dom_get("framework", "");
        
        if ((array_key_exists($framework, $frameworks)) 
        &&  (array_key_exists($classname, $frameworks[$framework]["classes"])))
        {
            $classname .= ' ' . $frameworks[$framework]["classes"][$classname];
        }
        
        return $classname;
    }

    #endregion
    #region HELPERS : PROFILING
    ######################################################################################################################################
    
    $__dom_debug_timing_t0   = microtime(true);
    $__dom_debug_timings_log = array();
    
    function dom_debug_timings()
    {
        global $__dom_debug_timings_log;
        return $__dom_debug_timings_log;
    }
    
    function dom_debug_callstack($shift_current_call = true)
    {
        $callstack = ((PHP_VERSION_ID >= 50400) ? debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 0)  : 
                     ((PHP_VERSION_ID >= 50306) ? debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)     : 
                     ((PHP_VERSION_ID >= 50205) ? debug_backtrace(true)                               : 
                                                  debug_backtrace()                                   )));
                                                  
        if ($shift_current_call) array_shift($callstack);
        return $callstack;
    }
    
    function dom_debug_track_timing($annotation = false)
    {
        global $__dom_debug_timing_t0;
        global $__dom_debug_timings_log;
        
        $callstack = dom_debug_callstack(); array_shift($callstack);
        $lastcall  = array_shift($callstack);
        $function  = $lastcall["function"];
        $timestamp = microtime(true) - $__dom_debug_timing_t0;

        $__dom_debug_timings_log[] =  str_pad(number_format($timestamp, 2), 6, " ", STR_PAD_LEFT) . ": " . $function . ((false !== $annotation) ? ("(" . $annotation . ")") : "");
        
        return "";
    }
    
    #endregion
    #region HELPERS : LOCALIZATION
    ######################################################################################################################################
    
    function dom_T($label, $default = false, $lang = false)
    { 
        $lang = strtolower(substr((false === $lang) ? dom_get("lang", dom_at($_SERVER, 'HTTP_ACCEPT_LANGUAGE', "en")) : $lang, 0, 2));
        $key = "loc_".$lang."_".$label;
        if (false === dom_get($key, false) && false !== $default) dom_set($key, $default);
        return dom_get($key, (false === $default) ? $label : $default);
    }
    
    #endregion
    #region HELPERS : MISC
    ######################################################################################################################################
    
    if (!function_exists("to_classname")) { function to_classname($str, $tolower = true) { $str = str_replace("é","e",str_replace("è","e",str_replace("à","a",$str))); return preg_replace('/\W+/','', $tolower ? strtolower(strip_tags($str)) : strip_tags($str)); } }
    
    function dom_AMP() { return false !== dom_get("amp", false) && 0 !== dom_get("amp", false) && "0" !== dom_get("amp", false); }

    function url_exists($url)
    {
       $headers = @get_headers($url);
       return stripos($headers[0], "200 OK") ? true : false;
    }
    
    function clean_title($title)
    {
        return trim($title, "!?;.,: \t\n\r\0\x0B");
    }

    function array_open_url($urls, $content_type = 'json', $timeout = 7)
    {
        if (is_array($urls))
        {
            foreach ($urls as $url)
            {
                $content = array_open_url($url, $content_type);
                
                if (false !== $content)
                {
                    return $content;
                }
            }
            
            return false;
        }

        $url = $urls;
        
             if ($content_type == 'xml')  $content_type = 'text/xml';
        else if ($content_type == 'json') $content_type = 'application/json';
        else if ($content_type == 'html') $content_type = 'text/html';
        else if ($content_type == 'csv')  $content_type = 'text/csv';

                        $options = array('http'=>array('method' => "GET", 'header' => ("Content-Type: type=".$content_type."; charset=utf-8\r\nAccept-language: en")));
                        $context = @stream_context_create($options);
        if (!!$context) $content = @file_get_contents($url, FILE_USE_INCLUDE_PATH, $context);
        if ( !$content) $content = @file_get_contents($url);
        if ( !$content) 
        {
            $curl = @curl_init();
            
            if (false !== $curl)
            {              
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);                
                curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
                
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);          
                
                $content = curl_exec($curl); if (!$content && !!dom_get("debug")) echo curl_error($curl);
                
                curl_close($curl);
            }
        }
        if (!!$content)
        {
        //  $content = utf8_decode($content);
            
                 if (       "text/xml"  == $content_type) { $content = @json_decode(@json_encode(@simplexml_load_string($content,null,LIBXML_NOCDATA )),true); }
            else if (       "text/csv"  == $content_type) { $content = @str_getcsv($content,"\n"); if (!!$content) foreach ($content as &$rrow) { $rrow = str_getcsv($rrow, ";"); } }
            else if ("application/json" == $content_type) { $content = @json_decode($content, true); }
        }

        return $content;
    }

    function array_hashtags($text)
    {
        $hashtags = array();

        $text = preg_replace('/#(\w+)/', '{$1}', $text);
        
        while (true)
        {
            $bgn = strpos($text, '{');       if ($bgn === false) break;
            $end = strpos($text, '}', $bgn); if ($end === false) break;

            $hashtags[] = substr($text, $bgn + 1, $end - $bgn - 1);
            
            $text = substr($text, 0, $bgn) . substr($text, $end + 1);
        }
        
        return $hashtags;
    }
    
    function to_string($a)
    {
        return is_array($a) ? print_r($a, true) : (string)$a;
    }

    function is_array_filtered($a, $required_values, $unwanted_values = false)
    {
        if (is_array($unwanted_values) && count($unwanted_values) > 0)
        {
            foreach ($unwanted_values as $value) { if ($value != "" && in_array($value, $a)) { return false; } }
        }

        $match = true;
        
        if (is_array($required_values) && count($required_values) > 0)
        {
            foreach ($required_values as $value) { if ($value != "")                            { $match = false; break; } }
            foreach ($required_values as $value) { if ($value != "" && in_array($value, $a))    { $match = true;  break; } }
        }
        
        return $match;
    }
    
    function is_array_sequential($a)
    {
        return array() === $a || array_keys($a) === range(0, count($a) - 1);
    }
    
                                                function dup($html, $n)                                 { $new = ""; for ($i = 0; $i < $n; ++$i) $new .= $html; return $new; }
                                                function eol($n = 1)                                    { if (!!dom_get("minify",false)) return ''; switch (strtoupper(substr(PHP_OS,0,3))) { case 'WIN': return dup("\r\n",$n); case 'DAR': return dup("\r",$n);  } return dup("\n",$n); }
    if (!function_exists("tab"))            {   function tab($n = 1)                                    { if (!!dom_get("minify",false)) return ' '; return dup(' ', 4*$n); }    }
                                                function pan($x, $w, $c = " ", $d = 1)                  { if (!!dom_get("minify",false)) return $x; $x="$x"; while (mb_strlen($x, 'utf-8')<$w) $x=(($d<0)?$c:"").$x.(($d>0)?$c:""); return $x; }
                                                function cat()                                          { $args = func_get_args(); return cat_FUNC_ARGS($args); }
                                                function cat_FUNC_ARGS($args)                           { return wrap_each($args,''); }
                                                function if_then($e, $html_if, $html_else = '')         { return (!!$e) ? $html_if : $html_else; }
                                                function quote($txt, $quote = false)                    { return ($quote === false) ? ((false === strpos($txt, '"')) ? ('"'.$txt.'"') : ("'".$txt."'")) : ($quote.$txt.$quote); }
    
    function wrap_each($a, $glue = "", $transform = "self", $flatten_array = true)
    {
        $args = func_get_args();
        return wrap_each_FUNC_ARGS($a, $glue, $transform, $flatten_array, $args);
    }

    function wrap_each_FUNC_ARGS($a, $glue, $transform, $flatten_array, $args)
    {
        array_shift($args); // $a
        array_shift($args); // $glue
        array_shift($args); // $transform
        array_shift($args); // $flatten_array
        
        if (!is_array($a)) $a = array($a);

        $html = "";         
        $i    = 0;

        foreach ($a as $e)
        {
            if ($flatten_array && is_array($e)) { $e = wrap_each($e,","); }
            $html .= (($i++ > 0) ? $glue : '') . call_user_func_array($transform, array_merge(array($e), $args));
        } 
            
        return $html;
    }

    function add_hastag_links($text, $fn_url_search = "url_instagram_search_by_tags", $fn_url_search_userdata = false)
    {    
        if (false !== stripos($text, "facebook_article"))
        {
            return $text;
        }

        $text = preg_replace('/#(\w+)/', '{$1}', $text);
        
        while (true)
        {
            $bgn = strpos($text, '{');       if ($bgn === false) break;
            $end = strpos($text, '}', $bgn); if ($end === false) break;
            
            $hashtag = substr($text, $bgn + 1, $end - $bgn - 1);
            
            if (function_exists($fn_url_search))
            {
                $url = call_user_func($fn_url_search, $hashtag, $fn_url_search_userdata);
            }
            else if (function_exists("url_".$fn_url_search."_search_by_tags"))
            {
                $url = call_user_func("url_".$fn_url_search."_search_by_tags", $hashtag, $fn_url_search_userdata);
            }
            
            $hashtag = a('#'.$hashtag, $url, "hashtag", EXTERNAL_LINK);
            
            $text = substr($text, 0, $bgn) . $hashtag . substr($text, $end + 1);
        }
        
        return $text;
    }

    function extract_start($text, $max_length = 40, $terminators = array("|", "-\n", " -\n", " - \n", "- \n", "\n", "!", "?", ".", array("#",1)))
    {
        foreach ($terminators as $terminator)
        {
            if (!is_array($terminator)) $terminator = array($terminator,0);

            while (mb_strlen($text, 'utf-8') > $max_length)
            {
                $p = strrpos($text, $terminator[0], $terminator[1]); 
                if ($p === false) break; 
                $text = substr($text, 0, $p);
            }
        }

        return trim($text, ".,;: \t\n\r\0\x0B");
    }

    function md($text, $hard_wrap = true)
    {
        $text = SmartyPants(Markdown($text));
    //  $text = str_replace("\n", "<br/>", $text);
    
        return $text;
    }

    #endregion
    #region LOREM IPSUM

    function lorem_ipsum($nb_paragraphs = 5, $tag = "p")
    {
        $html = "";

        if ($nb_paragraphs >= 1) $html .= tag($tag, "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque enim nibh, finibus ut sapien ac, congue sagittis erat. Nulla gravida odio ac arcu maximus egestas ut ac massa. Maecenas sagittis tincidunt pretium. Suspendisse dictum orci non nibh porttitor posuere. Donec vehicula vulputate enim, vitae vulputate sapien auctor et. Ut imperdiet non augue quis suscipit. Phasellus risus ipsum, varius vitae elit laoreet, convallis pharetra nisl. Aliquam iaculis, neque quis sollicitudin volutpat, quam leo lobortis enim, consectetur volutpat sapien ipsum in mauris. Maecenas rhoncus sit amet est quis tempus. Duis nulla mauris, rhoncus eget vestibulum placerat, posuere in sem. Nulla imperdiet suscipit felis, a blandit ante dictum a.");
        if ($nb_paragraphs >= 2) $html .= tag($tag, "Nunc lobortis dapibus justo, non eleifend arcu blandit ut. Fusce viverra massa purus, vel dignissim justo dictum quis. Maecenas interdum turpis in lacinia imperdiet. In vel dui leo. Curabitur vel iaculis leo. Sed efficitur libero sed massa porttitor tristique. Nam sit amet mi elit. Donec pellentesque sit amet tellus ut aliquam. Fusce consequat commodo dui, tempus fringilla diam fermentum eu. Etiam finibus felis egestas velit elementum, at bibendum lectus volutpat. Donec non odio varius, ornare felis mattis, fermentum dui.");
        if ($nb_paragraphs >= 3) $html .= tag($tag, "Phasellus ut consectetur justo. Nam eget libero augue. Praesent ut purus dignissim, imperdiet turpis sed, gravida metus. Praesent cursus fringilla justo et maximus. Donec ut porttitor tellus. Ut ac justo imperdiet, accumsan ligula et, facilisis ligula. Sed ac nulla at purus pretium tempor. Suspendisse nec iaculis lectus.");
        if ($nb_paragraphs >= 4) $html .= tag($tag, "Nulla varius dui luctus augue blandit, non commodo lectus pulvinar. Aenean lacinia dictum lorem nec molestie. Curabitur hendrerit, tellus quis lobortis pretium, odio felis convallis metus, sed pulvinar massa libero non sapien. Praesent aliquet posuere ex, vitae rutrum magna maximus id. Sed at eleifend libero. Cras maximus lacus eget sem hendrerit hendrerit. Nullam placerat ligula metus, eget elementum risus egestas non. Sed bibendum convallis nisl ac pretium. Sed ac magna mi. Aliquam sollicitudin quam augue, at tempus quam sagittis id. Aliquam convallis consectetur est non vulputate. Phasellus rutrum elit at neque aliquam aliquet. Phasellus tincidunt sem pharetra libero pellentesque fermentum. Donec tellus mauris, pulvinar consequat est vel, faucibus lacinia ante. Proin et posuere sem, nec luctus ligula.");
        if ($nb_paragraphs >= 5) $html .= tag($tag, "Ut volutpat ultrices massa id rhoncus. Vestibulum maximus non leo in dapibus. Phasellus pellentesque dolor id dui mollis, eget laoreet est pulvinar. Ut placerat, ex sit amet interdum lobortis, magna dolor volutpat ante, a feugiat tortor ante nec nulla. Pellentesque dictum, velit vitae tristique elementum, ex augue euismod arcu, in varius quam neque efficitur lorem. Fusce in purus nunc. Fusce sed dolor erat.");
        
        return $html;
    }

    function lorem($nb_paragraphs = 5, $tag = "p") { return lorem_ipsum($nb_paragraphs, $tag); }

    #endregion
    #region HELPERS : HOOKS & PAGINATION
    ######################################################################################################################################

    $__dom_last_headline_level = false;

    function hook_headline($h, $title)
    {
        global $__dom_last_headline_level;
        $__dom_last_headline_level = (int)$h;

        if ($h == 1) hook_title($title);
        if ($h == 2) hook_section($title);
    }

    function get_last_headline()
    {
        global $__dom_last_headline_level;
        return $__dom_last_headline_level;
    }

    function hook_title($title)
    {
        if (!!$title && false === dom_get("title", false))
        {
            dom_set("title", $title);
        }        
    }

    function hook_section($title)
    {
        dom_set("hook_sections", array_merge(dom_get("hook_sections", array()), array($title)));
    }
    
    function hook_heading($heading)
    {
        if (!!$heading && false === dom_get("heading", false))
        {
            dom_set("heading", $heading);
        }        
    }
    
    function hook_toolbar($row)
    {
        dom_set("toolbar",      true);
        dom_set("toolbar_$row", true);
    }
    
    $hook_amp_sidebars = "";
    
    function hook_amp_sidebar($html)
    {
        hook_amp_require("sidebar");

        global $hook_amp_sidebars;
        $hook_amp_sidebars .= $html;
    }
    
    $hook_amp_css = "";

    function hook_amp_css($css)
    {
        $css = str_replace('@-moz-document url-prefix("")', '@media only screen',   $css);
        $css = str_replace('@-ms-viewport',                 '____dummy',            $css);
        $css = str_replace("@charset 'UTF-8';",             '',                     $css);
        $css = str_replace("!important",                    '',                     $css);
        
        global $hook_amp_css;
        $hook_amp_css .= $css;
    }

    function hook_amp_require($component)    {    if (dom_AMP())     dom_set("hook_amp_require_$component", true); }
    function has_amp_requirement($component) { return dom_AMP() && !!dom_get("hook_amp_require_$component");       }
    
    $hook_feed_nth_item = 1;

    function hook_feed_item($metadata)
    {           
        if (dom_has("rss"))
        {
            global $hook_feed_nth_item;
            
            if (!dom_has("id") || dom_get("id") == $hook_feed_nth_item)
            {
                if ((dom_at($metadata,"post_title") !== false && dom_at($metadata,"post_title") != "")
                ||  (dom_at($metadata,"post_text")  !== false && dom_at($metadata,"post_text")  != ""))
                {
                    $timestamp = dom_has($metadata, "post_timestamp") ? dom_at($metadata, "post_timestamp") : strtotime(dom_at($metadata, "post_date", date("Y/m/d", time())));
                    
                    dom_set("rss_items", array_merge(dom_get("rss_items", array()), array(array
                    (
                        "title"         => dom_at($metadata, "post_title",    "")
                    ,   "link"          => dom_at($metadata, "post_url",      "")
                    ,   "description"   => dom_at($metadata, "post_text",     "")
                    ,   "img_url"       => dom_at($metadata, "post_img_url",  "")

                    ,   "timestamp"     =>                $timestamp
                    ,   "date"          => date(DATE_RSS, $timestamp)
                    
                    ))));
                }
            }
            
            ++$hook_feed_nth_item;
        }
        
        return "";
    }
    
    function hook($type, $metadata)
    {
        $source = dom_at($metadata, "TYPE", false);
        
        if ($source != false)
        {        
            if ($type != "thumb")
            {
                hook_feed_item($metadata);
            }
        
            dom_set($source . "_" . $type, (dom_has($source . "_" . $type) ? (dom_get($source . "_" . $type) . "§") : "") . clean_title(dom_at($metadata, "post_title"))); 
        }
    }
    
    #endregion
    #region HELPERS : PAGINATION

    $__dom_next_post_index = 0;
    
    function dom_pagination_add($metadata)
    {
        hook("post", $metadata);

        global $__dom_next_post_index;
             ++$__dom_next_post_index;
    }

    function dom_pagination_is_within()
    {
        if (false === dom_get("page",false)) return true;

        $n = (int)dom_get("n",   10);
        $p = (int)dom_get("page", 1);

        $min = ($p-1) * $n;
        $max =  $p    * $n;

        global $__dom_next_post_index;
        return ($min <= $__dom_next_post_index) && ($__dom_next_post_index < $max);
    }

    #endregion
    #region HELPERS : XML DOM PARSER

    function dom_doc_load_from_html($html)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        return dom_doc_load_from_html_parse($dom->documentElement);
    }

    function dom_doc_find_classes($dom, $classname, $tag = false)
    {
        $results = array();
        if (is_array($dom)) { $nodes = array($dom); while (count($nodes) > 0) { $node = array_shift($nodes); if (!is_array($node)) continue; if ($node["class"] == $classname && (false === $tag || $node["tag"] == $tag)) { $results[] = $node; } $nodes = array_merge($node["children"], $nodes); } }
        return $results;
    }

    function dom_doc_find_tags($dom, $tags)
    {
        if (!is_array($tags)) $tags = array($tags);
        $results = array();
        if (is_array($dom)) { $nodes = array($dom); while (count($nodes) > 0) { $node = array_shift($nodes); if (!is_array($node)) continue; if (in_array($node["tag"], $tags)) {  $results[] = $node; } $nodes = array_merge($node["children"], $nodes); } }
        return $results;
    }

    function dom_doc_find_class($dom, $classnames, $tag = false)
    {
        if (is_array($dom)) 
        {
            if (!is_array($classnames)) $classnames = array($classnames);
            
            $nodes = array($dom);
            
            while (count($nodes) > 0)
            {
                $node = array_shift($nodes);
                if (!is_array($node)) continue;

                $found_tag   = (false === $tag || $node["tag"] == $tag);
                $found_class = false;

                foreach ($classnames as $classname)
                {
                    if (false !== stripos($node["class"], $classname))
                    {
                        $found_class = true;
                        break;
                    }
                }
                
                if ($found_class && $found_tag) return $node;
                
                $nodes = array_merge($node["children"], $nodes); 
            }
         }
        
        return false;
    }

    function dom_doc_find_tag($dom, $tag)
    {
        if (is_array($dom)) { $nodes = array($dom); while (count($nodes) > 0) { $node = array_shift($nodes); if (!is_array($node)) continue; if ($node["tag"] == $tag) { return $node; } $nodes = array_merge($node["children"], $nodes); } }
        return false;
    }

    function dom_doc_remove_classes($dom, $classname)
    {
        $children = array();

        foreach ($dom["children"] as $node)
        {
            if (!is_array($node))
            {
                $children[] = $node;
            }
            else if (false === stripos($node["class"], $classname))
            {
                $children[] = dom_doc_remove_classes($node, $classname);
            }
        }

        $dom["children"] = $children;
        
        return $dom;
    }

    function dom_doc_remove_tags($dom, $tag)
    {
        $children = array();

        foreach ($dom["children"] as $node)
        {
            if (!is_array($node))
            {
                $children[] = $node;
            }
            else if ($tag != $node["tag"])
            {
                $children[] = dom_doc_remove_tags($node, $tag);
            }
        }

        $dom["children"] = $children;
        
        return $dom;
    }

    function dom_doc_attributes($dom)
    {
        if (!is_array($dom)) return array();

        $core_keys = array("tag", "children", "depth", "index");

        $attributes = array();

        foreach ($dom as $key => $value)
        {
            if (in_array($key, $core_keys)) continue;
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    function dom_doc_children($dom)             { if (!is_array($dom)) return array(); return $dom["children"]; }
    function dom_doc_tag($dom)                  { if (!is_array($dom)) return "";      return $dom["tag"];      }
    function dom_doc_class($dom)                { if (!is_array($dom)) return "";      return $dom["class"];    }
    function dom_doc_attribute($dom,$attribute) { if (!is_array($dom) || !array_key_exists($attribute, $dom)) return false; return $dom[$attribute]; }

    function dom_doc_inner_html($dom, $excluded_tags = false, $exclude_attributes = true, $hook = false, $hook_userdata = false, $depth = 0)
    {
        if (!is_array($dom)) return $dom;

        if (false === $excluded_tags)  $excluded_tags = array();
        if (!is_array($excluded_tags)) $excluded_tags = array($excluded_tags);
        if (in_array($dom["tag"], $excluded_tags)) return "";

        $hooked = false;
        if ($depth > 0 && function_exists($hook)) $hooked = $hook($dom, $hook_userdata);
        if (false !== $hooked) return $hooked;

        $html = "";
        {
            if ($depth > 0 && "" != $dom["tag"])  $html .= "<".$dom["tag"]; if ($depth > 0 && !$exclude_attributes) foreach (array_keys(dom_doc_attributes($dom)) as $key) 
            if ($depth > 0 && "" != $dom[$key])   $html .= " $key=\"".$dom[$key]."\"";
            if ($depth > 0 && "" != $dom["tag"])  $html .= ">"; foreach ($dom["children"] as $node)
                                                  $html .= dom_doc_inner_html($node, $excluded_tags, $exclude_attributes, $hook, $hook_userdata, $depth + 1);
            if ($depth > 0 && "" != $dom["tag"])  $html .= "</".$dom["tag"].">";
        }

        return $html;
    }
    
    function dom_doc_load_from_html_parse($element)
    {
        $index = -1;
        return dom_doc_load_from_html_parse_ex($element, 0, $index);
    }
    
    function dom_doc_load_from_html_parse_ex($element, $depth, &$index)
    {
        ++$index;

        $obj = array("tag" => "", "id" => "", "class" => "", "children" => array(), "depth" => $depth, "index" => $index);

        if (property_exists($element,'tagName'))
        {
            $obj["tag"] = $element->tagName;
            
            if (property_exists($element,'attributes'))
            {
                foreach ($element->attributes as $attribute) 
                {
                    $obj[$attribute->name] = (string)$attribute->value;
                }
            }
            
            if (property_exists($element,'childNodes'))
            {
                $index = 0;

                foreach ($element->childNodes as $subElement) 
                {
                    if ($subElement->nodeType == XML_TEXT_NODE)
                    {
                        $obj["children"][] = $subElement->wholeText;
                    }
                    else 
                    {
                        $obj["children"][] = dom_doc_load_from_html_parse_ex($subElement, $depth + 1, $index);
                    }
                }
            }
        }
        
        return $obj;
    }
        
    #endregion
    #region HELPERS : JSON API END-POINTS
    ######################################################################################################################################
    
    function json_pinterest_pin($pin, $token = false)
    {
        dom_debug_track_timing($pin);
        
        if ($token === false && !defined("TOKEN_PINTEREST")) return array();
        
        $token      = ($token === false) ? TOKEN_PINTEREST : $token;
        $fields     = array("id","link","note","url","image","media","metadata","attribution","board","color","original_link","counts","creator","created_at");
        $end_point  = "https://api.pinterest.com/v1/pins/".$pin."/?access_token=".$token."&fields=".implode('%2C', $fields); // EXTERNAL ACCESS
        
        return array_open_url($end_point);
    }

    function json_pinterest_posts($username = false, $board = false, $token = false)
    {
        dom_debug_track_timing($username.": ".$board);
        
        if ($token    === false && !defined("TOKEN_PINTEREST")) return array();
        if ($username === false && !dom_has("pinterest_user"))  return array();
        if ($board    === false && !dom_has("pinterest_board")) return array();
        
        $token      = ($token    === false) ? TOKEN_PINTEREST    : $token;
        $username   = ($username === false) ? dom_get("pinterest_user")     : $username;
        $board      = ($board    === false) ? dom_get("pinterest_board")    : $board;
        $end_point  = "https://api.pinterest.com/v1/boards/".$username."/".$board."/pins/?access_token=".$token; // EXTERNAL ACCESS
        
        $result = array_open_url($end_point);

    //  echo comment(print_r(array_merge(array("url" => $end_point), $result), true));
        
        if (dom_at($result, "status") == "failure")
        {
            return array();
        }
        
        return $result;
    }
    
    function json_tumblr_blog($blogname = false, $method = "info", $token = false)
    {
        dom_debug_track_timing($blogname);
        
        if ($token    === false && !defined("TOKEN_TUMBLR")) return array();
        if ($blogname === false && !dom_has("tumblr_blog"))  return array();
        
        $blogname   = ($blogname === false) ? dom_get("tumblr_blog") : $blogname;
        $token      = ($token    === false) ? TOKEN_TUMBLR       : $token;    
        $end_point  = "https://api.tumblr.com/v2/blog/$blogname.tumblr.com/$method?api_key=$token"; // EXTERNAL ACCESS
        
        return array_open_url($end_point);
    }

    function endpoint_facebook($username = false, $fields_page = false, $fields_post = false, $fields_attachements = false, $token = false)
    {                   
        dom_debug_track_timing($username);

        if ($token    === false && !defined("TOKEN_FACEBOOK")) return false;
        if ($username === false && !dom_has("facebook_page"))  return false;
        
    //  $facebook_app_id        = '413755725692570';
    //  $facebook_secret_key    = '13d5e12cc69c17e4017981d1e4187568';
    //  $facebook_user_token    = 'EAAF4TwMNvpoBAJzbLq0by2H9vLbtqHDXyACZAR0C9EXR1ToqtHdnk6MSCauDbsBJkDIqUQlNT0cbTZCsnkv28BUe42ZAxgIajz9VoX7YrNwPe78S7iaAw0cK8jxBudHK829HMGUcnXWuh5zTX3Y6CQPt7ypNZCt8qG2ZAv3ZAM7SmkjdxNdSKR4kgrPRCWtm8ZD';

        $username               = ($username            === false) ? dom_get("facebook_page") : $username;
        $fields_page            = ($fields_page         === false) ? array("id","name","about","mission","hometown","website","cover","picture","birthday"/*,"email","first_name","gender","last_name","quotes"*/) : ((!is_array($fields_page)) ? array($fields_page) : $fields_page);
        $fields_attachements    = ($fields_attachements === false) ? array("media","url") : ((!is_array($fields_attachements)) ? array($fields_attachements) : $fields_attachements);
        $fields_post            = ($fields_post         === false) ? array("message","description","caption","full_picture","link","attachments%7B".implode('%2C', $fields_attachements)."%7D") : ((!is_array($fields_post)) ? array($fields_post) : $fields_post);
        $token                  = ($token               === false) ? TOKEN_FACEBOOK : $token;
        $end_point              = "https://graph.facebook.com/v2.10/".$username."?access_token=".$token."&fields=".implode('%2C', $fields_page); // EXTERNAL ACCESS
        $end_point             .= ($fields_post !== false) ? (",posts"."%7B".implode('%2C', $fields_post)."%7D") : "";

        return $end_point;
    }

    function json_facebook($username = false, $fields_page = false, $fields_post = false, $fields_attachements = false, $token = false)
    {
        dom_debug_track_timing($username);        
        $end_point = endpoint_facebook($username, $fields_page, $fields_post, $fields_attachements, $token);
        if ($end_point === false) return array();
        
        $result = array_open_url($end_point);
        /*
        if ((false !== $username) && ((false === $result) || (dom_at(dom_at($result, "meta"),  "code", "") == "200") 
                                                          || (dom_at(dom_at($result, "error"), "code", "") ==  200 )))
        {
            $result = array("data" => array());
        
            $json_articles_page = json_facebook_from_content("https://www.facebook.com/pg/".dom_get("facebook_page")."/posts/?ref=page_internal");
            $json_articles_page = dom_at($json_articles_page, "require", array());
            
            foreach ($json_articles_page as $entry)
            {
                $ownerName = dom_at($entry, array(3, 1, "ownerName"), false);
                if (!$ownerName) continue;
                
                $permalink = dom_at($entry, array(3, 1, "permalink"), false);
                if (!$permalink) continue;
                
                $item = array
                (
                    "id"    => $permalink
                ,   "user"  => array
                    (
                        "full_name" =>$ownerName
                    ,   "username"  => $page
                    )
                ,   "link" => $permalink
                );
                
                $result["data"][] = $item;

                if (false !== $limit && count($result["data"]) >= $limit) break;
            }   
        }
        */
        return $result;
    }
    
    function json_facebook_post($post_id, $username = false, $fields_post = false, $fields_attachements = false, $token = false)
    {
        dom_debug_track_timing($username.": ".$post_id);
        
        if ($token    === false && !defined("TOKEN_FACEBOOK"))  return array();
        if ($username === false && !dom_has("facebook_page"))   return array();
        
        $username               = ($username            === false) ? dom_get("facebook_page")  : $username;
        $fields_attachements    = ($fields_attachements === false) ? array("media","url") : ((!is_array($fields_attachements)) ? array($fields_attachements) : $fields_attachements);
        $fields_post            = ($fields_post         === false) ? array("message","description","caption","full_picture","link","attachments%7B".implode('%2C', $fields_attachements)."%7D","created_time","from") : ((!is_array($fields_post)) ? array($fields_post) : $fields_post);
        $token                  = ($token               === false) ? TOKEN_FACEBOOK : $token;
        $end_point              = "https://graph.facebook.com/v2.10/".$post_id."?access_token=".$token."&fields=".implode('%2C', $fields_post); // EXTERNAL ACCESS

        return array_open_url($end_point);
    }
        
    function json_facebook_from_content($url)
    {
        $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36'));
        $context = stream_context_create($options);
        $html    = @file_get_contents($url, false, $context);

        if ($html)
        {   
            while (true)
            {
                $tag_bgn = '<div id="globalContainer" class="uiContextualLayerParent">';
                $tag_end = '</body>';
                
                $pos_bgn = stripos($html, $tag_bgn, 0);                             if (false == $pos_bgn) break;
                $pos_end = stripos($html, $tag_end, $pos_bgn + strlen($tag_bgn));   if (false == $pos_bgn) break;
                
                $html =  substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));

            //  echo comment(htmlentities($html));

                $result = dom_doc_load_from_html($html);

                $nodes  = array($result);
                $result = array();

                while (count($nodes) > 0)
                {
                    $node = array_shift($nodes);

                    if (is_array($node))
                    {
                        if (array_key_exists("class", $node) && false !== stripos($node["class"], "userContentWrapper"))
                        {
                            $result[] = $node;
                        }

                        if (array_key_exists("children", $node) && is_array($node["children"]))
                        {
                            $nodes = array_merge($nodes, $node["children"]);
                        }
                    }
                }

                for ($i = 0; $i < count($result); ++$i)
                {
                    $result[$i] = $result[$i]["children"][0]["children"][1];
                }

            //  echo comment((print_r($result, true)));
                
                echo "<pre>";
                print_r($result);
                echo "</pre>";

                return $result;

                break;
            }

            
            /*
            $tag_bgn = 'dir="ltr"><script';
            $tag_end = '<script>';
            
            $pos_bgn = strpos($html, $tag_bgn, 0);
            $pos_end = strpos($html, $tag_end, $pos_bgn + strlen($tag_bgn));

            if (false !== $pos_bgn && false !== $pos_end)
            {            
                $html = substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));
                
                echo comment("$url [$pos_bgn - $pos_end] = (".htmlentities($html).")");
        
                $result = dom_doc_load_from_html($html);
                
                $result = $result["children"][0]["children"][0]["children"];
                foreach ($result as $div) { if ($div["id"] == "globalContainer") { $result = $div; break; } }
                $result = $result["children"][0]["children"][0]["children"][0]["children"][1]["children"][1]["children"][0]["children"][1]["children"][1]["children"][0]["children"][0]["children"][1]["children"][0];
                
                echo "<pre>";
                print_r($result);
                echo "</pre>";

                return $result;
            }*/
        }
        
        return false;
    }
        
    function json_facebook_articles_from_content($url)
    {
        $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36'));
        $context = stream_context_create($options);
        $html    = @file_get_contents($url, false, $context);

        if ($html)
        {            
            $tag_bgn = '<script>new (require("ServerJS"))().handle(';
            $tag_end = ');</script>';
            
            $pos_bgn = strpos($html, $tag_bgn, 0);
            $pos_end = strpos($html, $tag_end, $pos_bgn);
            
            if (false !== $pos_bgn && false !== $pos_end)
            {
                $json   = substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));
                $result = json_decode($json, true);
                return $result;
            }
        }
        
        return false;
    }
        
    function json_facebook_article_from_content($url)
    {
        $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36'));
        $context = stream_context_create($options);
        $html    = @file_get_contents($url, false, $context);
            
        if ($html)
        {            
            $tag_bgn = '<div class="_4lmk _5s6c">';
            $tag_end = '</div>';
            
            $pos_bgn = strpos($html, $tag_bgn, 0);
            $pos_end = strpos($html, $tag_end, $pos_bgn);
            
            if (false !== $pos_bgn && false !== $pos_end)
            {
                $title = substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));
                           
                $tag_bgn = '<div class="_39k5 _5s6c">';
                $tag_end = '</div><form';
                
                $pos_bgn = strpos($html, $tag_bgn, 0);
                $pos_end = strpos($html, $tag_end, $pos_bgn);
                
                if (false !== $pos_bgn && false !== $pos_end)
                {
                    $body = substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));
                    
                    return array("title" => $title, "body" => $body);
                }
            }
        }
        
        return false;
    }
        
    function json_facebook_articles($page = false, $token = false, $limit = false)
    {
        dom_debug_track_timing($page);
        
        if ($token  === false && !defined("TOKEN_FACEBOOK"))    return array();
        if ($page   === false && !dom_has("facebook_page"))     return array();
        
        $token  = ($token   === false) ? TOKEN_FACEBOOK        : $token;
        $page   = ($page    === false) ? dom_get("facebook_page")  : $page;

        $end_points = array
        (
         /* "https://graph.facebook.com/"      .$page."/instant_articles?access_token=".$token // EXTERNAL ACCESS
        ,*/ "https://graph.facebook.com/v2.10/".$page."/instant_articles?access_token=".$token // EXTERNAL ACCESS
        );

        $result = array_open_url($end_points);
        
        if ((false !== $page) && ((false === $result) || (dom_at(dom_at($result, "meta"),  "code", "") == "200") 
                                                      || (dom_at(dom_at($result, "error"), "code", "") ==  200 )))
        {
            $result = array("data" => array());
        
            $json_articles_page = json_facebook_articles_from_content("https://www.facebook.com/pg/".dom_get("facebook_page")."/notes/?ref=page_internal");
            $json_articles_page = dom_at($json_articles_page, "require");
            
            foreach ($json_articles_page as $entry)
            {
                $ownerName = dom_at($entry, array(3, 1, "ownerName"), false);
                if (!$ownerName) continue;
                
                $permalink = dom_at($entry, array(3, 1, "permalink"), false);
                if (!$permalink) continue;
                
                $item = array
                (
                    "id"    => $permalink
                ,   "user"  => array
                    (
                        "full_name" =>$ownerName
                    ,   "username"  => $page
                    )
                ,   "link" => $permalink
                );
                
                $result["data"][] = $item;

                if (false !== $limit && count($result["data"]) >= $limit) break;
            }   
        }
        
        return $result;
    }
    
    function json_facebook_article($article, $username = false, $fields_post = false, $fields_attachements = false, $token = false)
    {
        dom_debug_track_timing($username.": ".$article);

        return json_facebook_article_from_content("https://www.facebook.com".$article);
    }
    
    function json_instagram_from_content($url)
    {
        $html = @file_get_contents($url);
            
        if ($html)
        {
            $tag_bgn = '<script type="text/javascript">window._sharedData = ';
            $tag_end = ';</script>';
            
            $pos_bgn = strpos($html, $tag_bgn);
            $pos_end = strpos($html, $tag_end, $pos_bgn);
            
            if (false !== $pos_bgn && false !== $pos_end)
            {
                $json   = substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));
                $result = json_decode($json, true);
                
                return $result;
            }
        }
        
        return false;
    }
        
    function json_instagram_medias($username = false, $token = false, $tag = false, $limit = false)
    {
        dom_debug_track_timing($username);
        
        if ($token    === false && !defined("TOKEN_INSTAGRAM")) return array();
        if ($username === false && !dom_has("instagram_user"))  return array();
        
        $token      = ($token    === false) ? TOKEN_INSTAGRAM           : $token;
        $username   = ($username === false) ? dom_get("instagram_user") : $username;
        $tag        = ($tag      === false) ? dom_get("instagram_tag")  : $tag;

        $end_points = array
        (
            "https://api.instagram.com/v1/users/" . "self"      . "/media/recent/?access_token=$token" // EXTERNAL ACCESS
        ,   "https://api.instagram.com/v1/users/" . $username   . "/media/recent/?access_token=$token" // EXTERNAL ACCESS
        ,   "https://api.instagram.com/v1/tags/"  . $tag        . "/media/recent/?access_token=$token" // EXTERNAL ACCESS
        );

        $result = array_open_url($end_points);

        // DEBUG ---->
        /*
        $result = array_merge($result, array("data" => array(array
        (
            "id"    => "666"
        ,   "user"  => array
            (
                "full_name"         => "John Doe"
            ,   "username"          => "Johnny"
            ,   "profile_picture"   => "https://web.cyanide-studio.com/image.png"
            )
        ,   "caption" => array
            (
                "text" => "Loremp ipsum est!"
            )
        ,   "created_time"  => date("d/m/Y")
        ,   "link"          => "https://web.cyanide-studio.com"
        ,   "images"        => array
            (
                "low_resolution" => array
                (
                    "url" => "https://web.cyanide-studio.com/image.png"
                )
            )

        ))));
        */
        // DEBUG ---->

    //  echo comment(print_r($result, true));
        
        if ((false !== $tag) && (false === $result || dom_at(dom_at($result, "meta"), "code", "") == "200"))
        {
            $json_tag_page = json_instagram_from_content("https://www.instagram.com/explore/tags/$tag/");
            
			if ($json_tag_page)
			{
                $edges = dom_at($json_tag_page, array("entry_data","TagPage",0,"graphql","hashtag","edge_hashtag_to_media","edges"));
            
                $result = array("data" => array());
            
                foreach ($edges as $edge)
                {
                    $node = dom_at($edge,"node");
                
                    $post_url = url_instagram_post(dom_at($node, "shortcode"));
                
                    $owner = dom_at(json_instagram_from_content($post_url), array("entry_data","PostPage",0,"graphql","shortcode_media","owner"));
                    
                    $result["data"][] = array
                    (
                        "id"    => dom_at($node, "id")
                    ,   "user"  => array
                        (
                            "full_name"         => dom_at($owner, "username")
                        ,   "username"          => dom_at($owner, "username")
                        ,   "profile_picture"   => dom_at($owner, "profile_pic_url")
                        )
                    ,   "caption" => array
                        (
                            "text" => ltrim(dom_at($node, array("edge_media_to_caption","edges",0,"node","text")), "|| ")
                        )
                    ,   "created_time"  => dom_at($node, "taken_at_timestamp")
                    ,   "link"          => $post_url
                    ,   "images"        => array
                        (
                            "low_resolution" => array
                            (
                                "url" => dom_at($node, "display_url")
                            )
                        )
                    );
    
                    if (false !== $limit && count($result["data"]) >= $limit) break;
                }
            }
        }
        
        return $result;
    }
    
    function __json_flickr($method, $params = array(), $token = false)
    {        
        if ($token === false && !defined("TOKEN_FLICKR")) return array();
        
        $token      = ($token === false) ? TOKEN_FLICKR : $token;
        $method     = (0 === stripos($method, "flickr.")) ? $method : ("flickr.".$method);
        $end_point  = "https://api.flickr.com/services/rest/?method=".$method."&api_key=".$token."&format=json&nojsoncallback=1"; // EXTERNAL ACCESS

        if (!!$params) foreach ($params as $key => $val) $end_point .= "&".$key."=".urlencode($val);

        return array_open_url($end_point);
    }
    
    function json_flickr($method, $params = array(), $user_id = false, $token = false)
    {
        dom_debug_track_timing($user_id);
        
        if ($token   === false && !defined("TOKEN_FLICKR")) return array();
        if ($user_id === false && !dom_has("flickr_user"))  return array();

        $user_id = ($user_id === false) ? dom_get("flickr_user") : $user_id;
        
        if (0 === stripos($user_id, "http"))
        {        
            $data       = __json_flickr("urls.lookupUser", array("url" => $user_id), $token);
            $user_id    = dom_at(dom_at($data,"user"),"id");
        }
        else if (false === stripos($user_id, "@N"))
        {
            $data       = __json_flickr("people.findByUsername", array("username" => $user_id), $token);
            $user_id    = dom_at(dom_at($data,"user"),"id");
        }
        
        return __json_flickr($method, array_merge($params, array("user_id" => $user_id)), $token);
    }
    
    // Social networks misc. utilities
    
    function facebook_post_longid($post_id, $page_id = false)
    {
        if (false === $page_id) $page_id = dom_get("facebook_page_id");
        if (false === $post_id) $post_id = dom_get("facebook_post_id", dom_get("facebook_post_id_hero", dom_get("facebook_post_hero")));
        
        if (false === $page_id || false === $post_id) return false;
        
        return $page_id . "_" . $post_id;
    }
    
    #endregion
    #region HELPERS : JSON METADATA FROM SOCIAL NETWORKS 
    ######################################################################################################################################
    
    function sort_cmp_post_timestamp($a,$b)
    {
        return (int)dom_at($a,"post_timestamp",0) < (int)dom_at($b,"post_timestamp",0);
    }
    
    function array_socials_posts($sources = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing("start");
        
        $posts = array();
        
        $social_index = 0;
        
        if ($sources !== false && !is_array($sources)) $sources = array($sources);
        if ($sources === false)                        $sources = array();
        
        foreach ($sources as $source)
        {   
            $source        = explode(":", $source);
            $social_source = dom_at($source, 0);
            $username      = dom_at($source, 1);

            // TODO handle the case of username that should contain multiple identifier (ex. pinterest)
            
            if (is_callable("array_".$social_source."_posts"))
            {
                $source_posts = call_user_func("array_".$social_source."_posts", $username, $filter, $tags_in, $tags_out);
                
                if (is_array($source_posts))
                {
                    $posts = array_merge($posts, $source_posts);
                }
            }
            else if (!!dom_get("debug"))
            {
                echo "UNDEFINED SOCIAL SOURCE: ".to_string($sources).to_string($filter);
            }
            
            ++$social_index;
        }
        
        usort($posts, "sort_cmp_post_timestamp");
        
        dom_debug_track_timing("end");
     
        return $posts;
    }
    
    function transform_lines($message, $pattern, $line = "<hr>")
    {
        $pos_line = 0;
        
        while (true)
        {
            $pos_line = strpos($message, $pattern, $pos_line);
            
            if (false === $pos_line)
            {
                break;
            }
            
            $pos_end_line = $pos_line;
            
            while ($message{$pos_end_line} == $pattern{0})
            {
                ++$pos_end_line;
            }
            
            $message = substr($message, 0, $pos_line) . $line . substr($message, $pos_end_line);
            
            $pos_line = $pos_end_line;
        }
        
        return $message;
    }
        
    function array_instagram_posts($username, $post_filter = "", $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing();
        
        $content = json_instagram_medias(($username === false) ? dom_get("instagram_user") : $username, false, false, dom_get("page") * dom_get("n"));
        $posts   = array();

        foreach (dom_at($content, "data",  array()) as $item)
        {
            if (!dom_pagination_is_within()) continue;
            
            $filtered = dom_at($item, "id")   == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
            $excluded = in_array(dom_at($item,"id"), explode(',',dom_get("exclude_instagram_codes", "")));
            $excluded = $excluded || in_array(dom_at(dom_at($item,"user"),"full_name"), explode(',',dom_get("exclude_instagram_users", "")));
            $tagged   = true;

            if (!$filtered || $excluded || !$tagged) continue;
            
            $images = dom_at(dom_at(dom_at($item,"images"),"low_resolution"),"url");
            $images = dom_at(dom_at(dom_at($item,"videos"),"low_resolution"),"url", $images);

            if (dom_get("carousel") && array_key_exists("carousel_media", $item))
            {
                $sub_items = dom_at($item, "carousel_media", array());
                
                if (count($sub_items) > 0)
                {
                    $images = array();
                                    
                    foreach ($sub_items as $sub_item)
                    {
                    //  $images[] = dom_at(dom_at(dom_at($sub_item,"images"),"standard_resolution"), "url");
                        $images[] = dom_at(dom_at(dom_at($sub_item,"images"),"low_resolution"),      "url");
                    }
                }
            }
            
            $exclude_facebook_text_md5s = explode(',',dom_get("exclude_facebook_text_md5s", ""));
            $exclude_facebook_text_md5s[] = md5($item["caption"]["text"]);
            dom_set("exclude_facebook_text_md5s", implode(',', $exclude_facebook_text_md5s));
            
            $title          = extract_start($item["caption"]["text"]);
            $post_message   = dom_at(dom_at($item,"caption"),"text");
            
            if (dom_get("facebook_posts_no_duplicate_titles") && in_array(clean_title($title), explode('§', dom_get("facebook_posts" )))) continue;
            if (dom_get("facebook_posts_no_duplicate_titles") && in_array(clean_title($title), explode('§', dom_get("instagram_posts")))) continue;
            
            $metadata = array
            (
                "TYPE"              => "instagram"
            ,   "user_name"         => $item["user"]["full_name"]
            ,   "user_url"          => url_instagram_user($item["user"]["username"])
            ,   "user_img_url"      => $item["user"]["profile_picture"]
            ,   "post_title"        => /*rss_sanitize*/($title)
            ,   "post_text"         => /*rss_sanitize*/($post_message)
            ,   "post_timestamp"    => $item["created_time"]
            ,   "post_url"          => $item["link"]
            ,   "post_img_url"      => $images
            ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? $item : "")
            ,   "LAZY"              => true
            );
            
            dom_pagination_add($metadata);

            $posts[] = $metadata;
        }
        
        return $posts;
    }
    
    function array_instagram_post($username = false, $post_id = "", $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing();

        if ($post_id === "" || $post_id === false)
        {
            $hack = dom_get("filter", "default");
            dom_set("filter", "HACK");
            
            $posts = array_instagram_posts($username, $post_id, $tags_in, $tags_out, false, false);
            
            dom_set("filter", $hack);
            
            if (count($posts) > 0)
            {
                return array_shift($posts);
            }
            
            return false;
        }

        $username   = ($username === false) ? dom_get("instagram_user")  : $username;                
        $item       = json_instagram_post($post_id, $username);
        
        $post_title   = extract_start($item["caption"]["text"]);
        $post_message = dom_at(dom_at($item,"caption"),"text");
       
        $images = dom_at(dom_at(dom_at($item,"images"),"low_resolution"),"url");
        $images = dom_at(dom_at(dom_at($item,"videos"),"low_resolution"),"url", $images);

        if (dom_get("carousel") && array_key_exists("carousel_media", $item))
        {
            $sub_items = dom_at($item, "carousel_media", array());
            
            if (count($sub_items) > 0)
            {
                $images = array();
                                
                foreach ($sub_items as $sub_item)
                {
                //  $images[] = dom_at(dom_at(dom_at($sub_item,"images"),"standard_resolution"), "url");
                    $images[] = dom_at(dom_at(dom_at($sub_item,"images"),"low_resolution"),      "url");
                }
            }
        }
        
        $metadata = array
        (
            "TYPE"          => "instagram"
        ,   "post_title"    => $post_title
        ,   "post_text"     => $post_message
        ,   "post_url"      => $item["link"]
        ,   "post_img_url"  => $images
        ,   "DEBUG_SOURCE"  => ((!!dom_get("debug")) ? $item : "")
        ,   "LAZY"          => true
        );
        
        hook("post", $metadata);

        return $metadata;
    }
    
    function array_flickr_posts($username = false, $photo_key = false, $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing();
        
        $photoset_key = false;
        
        if (is_array($photo_key))
        {
            $photoset_key = $photo_key[0];
            $photo_key    = $photo_key[1];
        }
    
        $photos         = array();
        $photo          = false;
        $photo_id       = false;
        $photo_secret   = false;
        $photo_server   = false;
        $photo_farm     = false;
        $photo_title    = false;
        
        if (false !== $photoset_key)
        {
            $data           = json_flickr("photosets.getList", array(), $username);
            $photosets      = dom_at(dom_at($data,"photosets"),"photoset");
            $photoset       = false;
            $photoset_id    = false;
            $photoset_title = false;
            
            foreach ($photosets as $photoset_index => $photoset_nth)
            { 
                $photoset       =               $photoset_nth;
                $photoset_id    =        dom_at($photoset_nth, "id");
                $photoset_title = dom_at(dom_at($photoset_nth, "title"), "_content");

                if (is_string($photoset_key)) { if ($photoset_title ==       $photoset_key) break; }
                else                          { if ($photoset_index === (int)$photoset_key) break; }
            }
            
            $data           = json_flickr("photosets.getInfo", array("photoset_id" => $photoset_id), $username);
            $photoset_farm  = dom_at(dom_at($data,"photoset"),"farm");
            
            $data           = json_flickr("photosets.getPhotos", array("photoset_id" => $photoset_id, "media" => "photo"), $username);
            $photos         = dom_at(dom_at($data,"photoset"),"photo");
            $photo_farm     = $photoset_farm;
        }
        else
        {
            $data   = json_flickr("people.getPhotos", array(), $username); 
            $photos = dom_at(dom_at($data,"photos"),"photo");
        }
        
        $posts = array();
        
        if (is_array($photos)) foreach ($photos as $photo_nth)
        { 
            if (!dom_pagination_is_within()) continue;
            
            $photo          =        $photo_nth;
            $photo_id       = dom_at($photo_nth, "id",      $photo_id);
            $photo_secret   = dom_at($photo_nth, "secret",  $photo_secret);
            $photo_server   = dom_at($photo_nth, "server",  $photo_server);
            $photo_farm     = dom_at($photo_nth, "farm",    $photo_farm);
            $photo_title    = dom_at($photo_nth, "title",   $photo_title);
            $photo_size     = "b";
            $photo_url      = "https://farm".$photo_farm.".staticflickr.com/".$photo_server."/".$photo_id."_".$photo_secret."_".$photo_size.".jpg";

            $data = json_flickr("photos.getInfo", array("photo_id" => $photo_id), $username);
            
            $photo_description = trim(dom_at(dom_at(dom_at($data,"photo"),"description"), "_content", $photo_title), " ");
            $photo_description = (false === $photo_description || "" == $photo_description) ? $photo_title : $photo_description;
            $photo_timestamp   = dom_at(dom_at(dom_at($data,"photo"),"dates"),"posted");
            $photo_page        = false;
            $photo_urls        = dom_at(dom_at(dom_at($data,"photo"),"urls"),"url", array());
            
            foreach ($photo_urls as $url)
            {
                if (dom_at($url,"type") == "photopage")
                {
                    $photo_page = dom_at($url,"_content");
                    break;
                }
            }
            
            $filtered = (false !== stripos($photo_title, $photo_key)) || "" == "$photo_key" || false == "$photo_key";
            $excluded = in_array($photo_title, explode(',',dom_get("exclude_flickr_codes", "")));
            $tagged   = true;
            
            if (!$filtered || $excluded || !$tagged) continue;
            
            $metadata = array
            (
                "TYPE"              => "flickr"
            ,   "user_name"         => $username
            ,   "user_url"          => false
            ,   "user_img_url"      => false
            ,   "post_title"        => $photo_title
            ,   "post_text"         => /*rss_sanitize*/($photo_description)
            ,   "post_timestamp"    => $photo_timestamp
            ,   "post_url"          => $photo_page
            ,   "post_img_url"      => $photo_url
            ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? $data : "")
            ,   "LAZY"              => true
            );
            
            dom_pagination_add($metadata);

            $posts[] = $metadata;
        }
        
        return $posts;
    }
    
    function array_instagram_thumb($username, $post_filter = "", $tags_in = false, $tags_out = false)
    {
        return array_instagram_thumbs($username, $post_filter, $tags_in, $tags_out);
    }
    
    function array_instagram_thumbs($username, $post_filter = "", $tags_in = false, $tags_out = false)
    {          
        dom_debug_track_timing();
          
        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);
        $content    = json_instagram_medias(($username === false) ? dom_get("instagram_user") : $username);
        $thumbs     = array();

        foreach (dom_at($content, "data",  array()) as $item)
        {
            $item_tags = array_hashtags(dom_get(dom_get($item, "caption"), "text"));
            
            $filtered = $item["id"]   == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
            $excluded = in_array(dom_get($item,"id"),   explode(',',dom_get("exclude_instagram_codes", "")));
            $tagged   = is_array_filtered($item_tags, $tags_in, $tags_out);
            
            if (!$filtered || $excluded || !$tagged) continue;
            
            $metadata = array
            (
                "TYPE"          => "instagram"
            ,   "post_url"      => dom_at($item,"link")
            ,   "post_img_url"  => dom_at(dom_at(dom_at($item,"images"),"thumbnail"),"url")
            ,   "DEBUG_SOURCE"  => ((!!dom_get("debug")) ? array_merge($item, array("tags_in" => $tags_in), array("tags_out" => $tags_out), array("tags" => $item_tags)) : "")
            ,   "LAZY"          => true
            );

            $metadata["post_title"] = "";
            hook("thumb", $metadata);
            unset($metadata["post_title"]);

            $thumbs[] = $metadata;
        }
        
        return $thumbs;
    }
    
    function array_tumblr_posts($blogname = false, $post = "", $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing();
        
        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);
        $content    = json_tumblr_blog($blogname, "posts"); // if ($content["meta"]["msg"] != 'OK') return array();
        $posts      = array();
        
        foreach (dom_at(dom_at($content, "response"), "posts", array()) as $item)
        {   
            if (!dom_pagination_is_within()) continue;
            
            $post_title = dom_at($item, "title", extract_start(dom_at($item, "summary", dom_at(dom_at(dom_at($content, "response"), "blog"), "title"))));
            
            $filtered = $item["id"] == "$post" || "" == "$post" || false == "$post";
            $excluded = in_array(dom_get($item,"slug"), explode(',',dom_get("exclude_tumblr_slugs", "")));
            $tagged   = is_array_filtered(dom_at($item, "tags", array()), $tags_in, $tags_out);            
            $indirect = ((false !== stripos(dom_get($item, "link_url"),      "instagram.com")) 
                      || (false !== stripos(dom_get($item, "permalink_url"), "instagram.com"))) && (dom_has("instagram_posts") /*|| (dom_get("filter", "default") == "default")*/);
                    
            $indirect = $indirect || in_array(clean_title($post_title), explode('§', dom_get("facebook_posts")));
            $indirect = $indirect || in_array(clean_title($post_title), explode('§', dom_get("instagram_posts")));

            if (!$filtered || $excluded || !$tagged || $indirect) continue;
            
            $post_source_url = (dom_get("check_target_url", false)) ? ((false === array_open_url(dom_get($item, "link_url"))) ? dom_get($item, "post_url") : dom_get($item, "link_url")) : dom_at($item, "link_url", dom_at($item, "post_url"));
    
            if (dom_at($item, "type") == "photo")
            {
                if (!!dom_get("carousel"))
                {
                    $post_photo_captions = array();
                    $post_photo_imgs     = array();
                    
                    foreach (dom_get($item, "photos", array()) as $photo)
                    {
                        $post_photo_captions [] =        dom_at($photo, "caption");
                        $post_photo_imgs     [] = dom_at(dom_at($photo, "original_size", array()), "url");
                    }
                }
                else
                {
                    $post_photo_captions = "";
                    $post_photo_imgs     = false;
                    
                    foreach (dom_get($item, "photos", array()) as $photo)
                    {
                        $post_photo_captions =        dom_at($photo, "caption");
                        $post_photo_imgs     = dom_at(dom_at($photo, "original_size", array()), "url");
                        
                        break;
                    }
                }
                
                $metadata = array
                (
                    "TYPE"              => "tumblr"
                ,   "userdata"          => $blogname
                ,   "user_name"         => dom_get($item, "blog_name")
                ,   "user_url"          => dom_at(dom_at(dom_at($content, "response"), "blog"), "url")
                ,   "user_img_url"      => url_tumblr_avatar($blogname,64)
                ,   "post_title"        => $post_title
                ,   "post_text"         => dom_at($item, "caption")
                ,   "post_timestamp"    => strtotime(dom_get($item, "date"))
                ,   "post_url"          => $post_source_url
                ,   "post_img_url"      => $post_photo_imgs
                ,   "post_figcaption"   => $post_photo_captions
                ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? $item : "")
                ,   "LAZY"              => true
                );
                
                dom_pagination_add($metadata);  

                $posts[] = $metadata;
            }
            else if (dom_at($item, "type") == "video")
            {
                $post_video = array();
                {
                    $item_videos = dom_at($item, "player", array());                    
                    if (count($item_videos) > 0) $post_video = $item_videos[count($item_videos) - 1];
                }
                
                $metadata = array
                (
                    "TYPE"              => "tumblr"
                ,   "userdata"          => $blogname
                ,   "post_text"         => dom_at($item, "caption")
                ,   "post_timestamp"    => strtotime(dom_get($item, "date"))
                ,   "post_url"          => $post_source_url
                ,   "post_embed"        => dom_at($post_video, "embed_code")
                ,   "post_figcaption"   => dom_at($post_video, "caption")
                ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? $item : "")
                );
                
                dom_pagination_add($metadata);  

                $posts[] = $metadata;
            }
        }

        return $posts;        
    }
    
    
    function array_pinterest_posts($username_and_board, $pin_filter = "", $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing();
        
        if (!is_array($username_and_board)) $username_and_board = array($username_and_board, false);
        
        $username = $username_and_board[0];
        $board    = $username_and_board[1];
        
        $username   = ($username === false) ? dom_get("pinterest_user")  : $username;
        $board      = ($board    === false) ? dom_get("pinterest_board") : $board;
        $content    = json_pinterest_posts($username, $board);
        
        $pins = array();
        
        foreach (dom_at($content, "data", array()) as $item)
        {
            if (!dom_pagination_is_within()) continue;
            
            $pin_id     = $item["id"];
            $item       = json_pinterest_pin($pin_id);
            $item       = $item["data"];
            $item["id"] = $pin_id;

            $filtered = $item["id"] == "$pin_filter" || "" == "$pin_filter" || false == "$pin_filter";
            $excluded = in_array(dom_get($item,"id"), explode(',',dom_get("exclude_pinterest_pins_ids", "")));
            $tagged   = true;
            
            if (!$filtered || $excluded || !$tagged) continue;
            
            if ($item["note"] == "Tumblr")                                     continue;
            if (false !== strpos($item["note"], "Photos et vidéos Instagram")) continue;

            $metadata = array
            (
                "TYPE"              => "pinterest"
            ,   "user_name"         => $item["creator"]["first_name"].' '.$item["creator"]["last_name"]
            ,   "user_url"          => url_pinterest_board($username, $board)
            ,   "user_img_url"      => false
            ,   "post_title"        => extract_start($item["note"])
            ,   "post_text"         => $item["note"]
            ,   "post_timestamp"    => strtotime($item["created_at"])
            ,   "post_url"          =>($item["original_link"] != "" && false === stripos($item["original_link"], dom_get("canonical")) && false === stripos($item["original_link"], str_replace("https://","",dom_get("canonical")))) ? $item["original_link"] : url_pinterest_pin($item["id"])
            ,   "post_img_url"      => $item["image"]["original"]["url"]
            ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? $item : "")
            ,   "LAZY"              => true
            );
            
            dom_pagination_add($metadata);

            $pins[] = $metadata;
        }

        return $pins;
    }

    function array_tumblr_blog($blogname = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing();
        
        $blogname   = ($blogname === false) ? dom_get("tumblr_blog") : $blogname;
        $content    = json_tumblr_blog($blogname, "info");
        $item       = dom_at(dom_at($content, "response"), "blog", array());
        
        $metadata = array
        (
            "TYPE"              => "tumblr"
        ,   "userdata"          => $blogname
        ,   "user_name"         => dom_at($item, "name")
        ,   "user_url"          => dom_at($item, "url")
        ,   "user_img_url"      => url_tumblr_avatar($blogname,64)
        ,   "post_title"        => dom_at($item, "title")
        ,   "post_text"         => dom_at($item, "description")
        ,   "post_timestamp"    => dom_at($item, "updated")
        ,   "post_url"          => dom_at($item, "url")
        ,   "post_img_url"      => url_img_tumblr($blogname)
        ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? $item : "")
        ,   "LAZY"              => true
        );

        return $metadata;
    }

    function instagram_posts_presence() { return dom_has("instagram_posts") || (dom_get("filter", "default") == "default"); }
    function facebook_posts_presence()  { return dom_has("facebook_posts")  || (dom_get("filter", "default") == "default"); }
    
    function array_facebook_posts($username = false, $post = "", $tags_in = false, $tags_out = false, $limit = false, $videos = true)
    {
        dom_debug_track_timing();

        $username   = ($username === false) ? dom_get("facebook_page")  : $username;        
        $content    = json_facebook($username, array("id","name","about","mission","hometown","website","cover","picture"));
        $posts      = array();
        
        /*return array(array
        (
            "TYPE"              => "facebook"
        ,   "user_name"         => dom_get("name")
        ,   "user_url"          => dom_get("url")
        ,   "user_img_url"      => "image.png"
        ,   "post_title"        => dom_get("title")
        ,   "post_text"         => dom_get("description")
        ,   "post_timestamp"    => strtotime(date("Y/m/d", time()))
        ,   "post_url"          => dom_get("url")
        ,   "post_img_url"      => "image.png"
        ,   "DEBUG_SOURCE"      => array("content" => $content)
        ,   "LAZY"              => true
        ));*/

        $articles   = array_facebook_articles(dom_get("facebook_page"));
        
        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);

        $post_exclude_article_body = in_array("ARTICLE", $tags_out);
        if ($post_exclude_article_body) { unset($tags_out[array_search("ARTICLE",$tags_out)]); }
            
        foreach (dom_at(dom_at($content, "posts"), "data", array()) as $item_index => $item)
        {
            if (!dom_pagination_is_within())                                                                                                    continue;
            if ($item["id"] != "$post" && "" != "$post" && false != "$post")                                                                    continue;
            if (in_array(    dom_get($item,"id"),        explode(',',dom_get("exclude_facebook_post_ids",  ""))))                               continue;
            if (in_array(md5(dom_get($item, "message")), explode(',',dom_get("exclude_facebook_text_md5s", ""))))                               continue;
            if ((false !== stripos(dom_get($item, "caption"), "instagram.com"))                            && (instagram_posts_presence()))     continue;
            if ((false !== stripos(dom_at(dom_at(dom_at($item,"attachments"),"data"),"url", 
                            dom_at(dom_at(dom_at(dom_at($item,"attachments"),"data"),0),"url")), "instagram.com")) && (instagram_posts_presence())) continue;

            $item_post          = json_facebook_post(dom_at($item,"id"), $username);            
            $post_message       = dom_at($item_post, "description", dom_get($item, "message"));
            $post_title         = extract_start($post_message);
                
            $post_article       = false; foreach ($articles as $article) if ($article["post_title"] == $post_title) $post_article = $article;
            
            $post_article_tags  = ($post_article !== false) ? array("ARTICLE") : array();
            
            if (!is_array_filtered(array_merge($post_article_tags, array_hashtags($post_message)), $tags_in, $tags_out)) continue;
            
            if (0 === strpos($post_message, dom_get("instagram_user")) && instagram_posts_presence()) continue;
            if (0 === strpos($post_message, dom_get("instagram_user"))) $post_message = substr($post_message, strlen(dom_get("instagram_user")));
            
        //  if (dom_get("facebook_posts_no_duplicate_titles") && in_array(clean_title($post_title), explode('§', dom_get("facebook_posts" )))) continue;
        //  if (dom_get("facebook_posts_no_duplicate_titles") && in_array(clean_title($post_title), explode('§', dom_get("instagram_posts")))) continue;
            
            $embedding_other_post             = (false !== strpos($post_message, "<iframe"));
            $post_img_url_page_cover_fallback = ($embedding_other_post) ? false : dom_at(dom_at($content,"cover"),"source");
            
            $post_img_url =                 dom_at($item_post, "full_picture", 
                dom_at(dom_at(dom_at(dom_at(dom_at($item_post, "attachments"), "data"),     "media", 
                       dom_at(dom_at(dom_at(dom_at($item_post, "attachments"), "data"), 0), "media")), "image"), "src", 
                               
                               $post_img_url_page_cover_fallback));

            $link = dom_at($item_post, "link", false);
            
            if (false !== $link)
            {
                $video_id = rtrim($link, "/");
                $pos = strripos($video_id, "/");
                if (false !== $pos) $video_id = substr($video_id, $pos + 1);
                
                $video = json_facebook_post($video_id, $username, array("embed_html", "embeddable"), array());
                
                if (false !== $video)
                {
                    $embed_html = dom_at($video,"embed_html");
                    
                    if (false !== $embed_html)
                    {
                        $post_img_url = $embed_html;
                    }
                }
            }
            
            if ($post_article !== false)
            {
                if ($post_exclude_article_body)
                {
                    $post_message .= '<br><hr><div class="facebook_article_link"><a href="#'.md5($post_article["post_url"]).'">'.T("READ_ARTICLE", "Read article").'</a></div>';
                }
                else 
                {
                    $post_message = anchor(md5($post_article["post_url"])).$post_message.'<br><hr><div class="facebook_article">' . $post_article["post_text"] . "</div>";
                }
            }
            
            $metadata = array
            (
                "TYPE"              => "facebook"
            ,   "user_name"         => dom_get(dom_get($item_post, "from", array()), "name")
            ,   "user_url"          => url_facebook_page($content["id"])
            ,   "user_img_url"      => $content["picture"]["data"]["url"]
            ,   "post_title"        => $post_title
            ,   "post_text"         => $post_message
            ,   "post_timestamp"    => strtotime(dom_get($item_post, "created_time"))
            ,   "post_url"          => dom_at($item_post,"link",url_facebook_page(dom_at($item_post,"id")))
            ,   "post_img_url"      => $post_img_url
            ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? array("articles" => $articles, "post" => $item_post) : "")
            ,   "LAZY"              => true
            );
            
            dom_pagination_add($metadata);
            
            $posts[] = $metadata;
            
            if (false !== $limit && count($posts) >= $limit)
            {
                break;
        }
        }
        
        return $posts;
    }
    
    function array_facebook_articles($username = false, $post = "", $tags_in = false, $tags_out = false, $limit = false, $videos = true)
    {
        dom_debug_track_timing();
           
        $username   = ($username === false) ? dom_get("facebook_page")  : $username;        
        $content    = json_facebook_articles($username, false, dom_get("page") * dom_get("n"));
        $posts      = array();

        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);

        /*$posts[] = array
        (
            "TYPE"              => "facebook"
        ,   "post_title"        => "post title"
        ,   "post_text"         => "post message"
        ,   "post_url"          => "https://www.facebook.com"
        ,   "DEBUG_SOURCE"      => "DEBUG"
        ,   "LAZY"              => true
        );*/
            
        foreach (dom_at($content, "data", array()) as $item_index => $item)
        {
            if (!dom_pagination_is_within())                                                                        continue;            
            if ($item["id"] != "$post" && "" != "$post" && false != "$post")                                        continue;            
            if (in_array(    dom_get($item,"id"),        explode(',',dom_get("exclude_facebook_article_ids", "")))) continue;
            
            $item_post = json_facebook_article(dom_at($item,"id"), $username);      
            
            if (!is_array($item_post)) continue;
            
            $post_message = dom_at($item_post, "body", "");
            $post_message = strip_tags(str_replace("<div","<p", str_replace("</div>","</p>", $post_message)), "<p><ul><li><h1><h2><h3>");
            
            if (!is_array_filtered(array_hashtags($post_message), $tags_in, $tags_out)) continue;
            
            $post_title = extract_start(dom_at($item_post, "title", ""));

            $metadata = array
            (
                "TYPE"              => "facebook"
            ,   "post_title"        => $post_title
            ,   "post_text"         => $post_message
            ,   "post_url"          => "https://www.facebook.com".dom_at($item,"id")
            ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? array_merge($item, $item_post) : "")
            ,   "LAZY"              => true
            );
            
          //dom_pagination_add($metadata);
            
            $posts[] = $metadata;
            
            if (false !== $limit && count($posts) >= $limit)
            {
                break;
            }
        }
        
        return $posts;
    }
    
    function array_facebook_thumb($username = false, $post_filter = "", $tags_in = false, $tags_out = false)
    {
        return array_facebook_thumbs($username, $post_filter, $tags_in, $tags_out);
    }
    function array_facebook_thumbs($username = false, $post_filter = "", $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing();
        
        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);
            
        $username   = ($username === false) ? dom_get("facebook_page")  : $username;        
        $content    = json_facebook($username, array("id","name","about","mission","hometown","website","cover","picture"));
        $thumbs     = array();

        foreach (dom_at(dom_at($content, "posts"), "data", array()) as $item)
        {
            $filtered = $item["id"] == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
            $excluded =              in_array(    dom_get($item,"id"),        explode(',',dom_get("exclude_facebook_post_ids",  "")));
            $excluded = $excluded || in_array(md5(dom_get($item, "message")), explode(',',dom_get("exclude_facebook_text_md5s", "")));
            $tagged   = is_array_filtered(array_hashtags(dom_get($item, "message")), $tags_in, $tags_out);    
            $indirect = (false !== stripos(dom_get($item, "caption"), "instagram.com")) && (instagram_posts_presence());
            
            if ((false !== stripos(dom_at(dom_at(dom_at($item,"attachments"),"data"),"url", 
                            dom_at(dom_at(dom_at(dom_at($item,"attachments"),"data"),0),"url")), "instagram.com")) && (instagram_posts_presence())) continue;
               
            if (!$filtered || $excluded || !$tagged || $indirect) continue;
            
            $item_post    = json_facebook_post(dom_at($item,"id"), $username);                   
            $post_message = dom_at($item_post, "message", dom_get($item, "description"));
            
            if (0 === strpos($post_message, dom_get("instagram_user")) && instagram_posts_presence()) continue;
            
            $post_title = extract_start($post_message);
            
            if (in_array(clean_title($post_title), explode('§', dom_get("facebook_posts")))) continue;
            
            $metadata = array
            (
                "TYPE"          => "facebook"
            ,   "post_url"      => dom_at($item_post,"link",url_facebook_page($item_post["id"]))
            ,   "post_img_url"  => dom_at($item_post,"full_picture",dom_at(dom_at(dom_at(dom_at(dom_at($item_post,"attachments"),"data"),"media", dom_at(dom_at(dom_at(dom_at($item_post,"attachments"),"data"),0),"media")),"image"),"src", dom_at(dom_at($content,"cover"),"source")))
            ,   "DEBUG_SOURCE"  => ((!!dom_get("debug")) ? $item_post : "")
            ,   "LAZY"          => true
            );

            $metadata["post_title"] = $post_title;
            hook("thumb", $metadata);
            unset($metadata["post_title"]);
            
            $thumbs[] = $metadata;
        }
        
        return $thumbs;
    }
    
    function array_facebook_page($username = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing();
        
        $username   = ($username === false) ? dom_get("facebook_page")  : $username;
        $item       = json_facebook($username, array("id","name","about","mission","birthday","hometown","website","cover","picture"));
        
        $metadata = array
        (
            "TYPE"              => "facebook"
        ,   "user_name"         => dom_at($item, "name")
        ,   "user_url"          => url_facebook_page($username)
        ,   "user_img_url"      => false
        ,   "post_title"        => extract_start(dom_at($item,"mission"))
        ,   "post_text"         => p(dom_at($item,"mission")).p(dom_at($item,"about"))
        ,   "post_timestamp"    => strtotime(dom_at($item,"birthday"))
        ,   "post_url"          => url_facebook_page_about(dom_at($item,"id"))
        ,   "post_img_url"      => dom_at(dom_at($item,"cover"),"source")
        ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? $item : "")
        ,   "LAZY"              => true
        );

        return $metadata;
    }
    
    function array_facebook_post($username = false, $post_id = "", $tags_in = false, $tags_out = false)
    {
        dom_debug_track_timing();

        if ($post_id === "" || $post_id === false)
        {
            $hack = dom_get("filter", "default");
            dom_set("filter", "HACK");
            
            $posts = array_facebook_posts($username, $post_id, $tags_in, $tags_out, false, false);
            
            dom_set("filter", $hack);
            
            if (count($posts) > 0)
            {
                return array_shift($posts);
            }
            
            return false;
        }

        $username   = ($username === false) ? dom_get("facebook_page")  : $username;                
        $item       = json_facebook_post($post_id, $username);
        
        $post_title   = extract_start(dom_get($item, "message"));
        $post_message = dom_get($item, "message");
        
        if (0 === strpos($post_title,   dom_get("instagram_user"))) $post_title   = substr($post_title,   strlen(dom_get("instagram_user")));
        if (0 === strpos($post_message, dom_get("instagram_user"))) $post_message = substr($post_message, strlen(dom_get("instagram_user")));
           
        $metadata = array
        (
            "TYPE"          => "facebook"
        ,   "post_title"    => $post_title
        ,   "post_text"     => $post_message
        ,   "post_url"      => dom_at($item,"link",url_facebook_page(dom_at($item,"id")))
        ,   "post_img_url"  => dom_at($item,"full_picture",dom_at(dom_at(dom_at(dom_at(dom_at($item,"attachments"),"data"),"media", dom_at(dom_at(dom_at(dom_at($item,"attachments"),"data"),0),"media")),"image"),"src"))
        ,   "DEBUG_SOURCE"  => ((!!dom_get("debug")) ? $item : "")
        ,   "LAZY"          => true
        );
        
        hook("post", $metadata);

        return $metadata;
    }

    function _array_rss_posts($type, $url, $post_img_url)
    {
        $posts = array();
        
        foreach (dom_at(array_open_url($url, "xml"), array("channel","item"), array()) as $item)
        {    
            $metadata = array
            (
                "TYPE"              => $type
            ,   "post_title"        => extract_start(dom_at($item, "title"))
            ,   "post_url"          => dom_at($item, "link")
            ,   "post_date"         => dom_at($item, "pubDate")
            ,   "post_text"         => dom_at($item, "title")
            ,   "post_img_url"      => $post_img_url
            );

            $html = dom_at($item, "description", false);
            
            if (is_string($html))
            {
                $pos_img_bgn = stripos($html, '<img');
                $pos_img_end = stripos($html, '>', $pos_img_bgn + 4);
                
                if ($pos_img_bgn !== false)
                {
                    $pos_src_bgn = stripos($html, 'src="', $pos_img_bgn);
                    $pos_src_end = stripos($html, '"',     $pos_src_bgn + 5);
                    
                    $src  = substr($html, $pos_src_bgn + 5, $pos_src_end - $pos_src_bgn - 5);
                    $html = substr($html, 0, $pos_img_bgn) . substr($html, $pos_img_end + 1);
                    
                    $metadata["post_img_url"] = $src;
                    $metadata["post_text"]    = $html;
                }
                else
                {
                    $metadata["post_text"] = $html;
                }
            }
            
            $posts[] = $metadata;

            hook_feed_item($metadata);
        }
        
        return $posts;
    }

    function array_googlenews_posts($id = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        $feeds = array
        (
            "SPORT"         => ("https://news.google.com/news/rss/headlines/section/topic/" . "SPORTS"        . ".fr_fr/" . "Sports"                 . "?ned=fr&hl=fr&gl=FR")
        ,   "SCITECH"       => ("https://news.google.com/news/rss/headlines/section/topic/" . "SCITECH"       . ".fr_fr/" . "Science%2FHigh-Tech"    . "?ned=fr&hl=fr&gl=FR")
        ,   "WORLD"         => ("https://news.google.com/news/rss/headlines/section/topic/" . "WORLD"         . ".fr_fr/" . "Monde"                  . "?ned=fr&hl=fr&gl=FR")
        ,   "HEALTH"        => ("https://news.google.com/news/rss/headlines/section/topic/" . "HEALTH"        . ".fr_fr/" . "Sante"                  . "?ned=fr&hl=fr&gl=FR")   
        ,   "ENTERTAINMENT" => ("https://news.google.com/news/rss/headlines/section/topic/" . "ENTERTAINMENT" . ".fr_fr/" . "Divertissement"         . "?ned=fr&hl=fr&gl=FR")
        ,   "TECHNOLOGY"    => ("https://news.google.com/news/rss/headlines/section/topic/" . "TECHNOLOGY"    . ".fr_fr/" . "Technology"             . "?ned=fr&hl=fr&gl=FR")
        ,   "BUSINESS"      => ("https://news.google.com/news/rss/headlines/section/topic/" . "BUSINESS"      . ".fr_fr/" . "Finance"                . "?ned=fr&hl=fr&gl=FR")
        ,   "NATION"        => ("https://news.google.com/news/rss/headlines/section/topic/" . "NATION"        . ".fr_fr/" . "France"                 . "?ned=fr&hl=fr&gl=FR")
        );
        
        $feed = ($filter != "" && $filter !== false && array_key_exists($filter, $feeds)) ? $filter : "SCITECH";
        
        return _array_rss_posts("googlenews", $feeds[$feed], "https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/Google_%22G%22_Logo.svg/240px-Google_%22G%22_Logo.svg.png");
    }

    function array_numerama_posts($id = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        return _array_rss_posts("numerama", "https://www.numerama.com/feed/rss/", "https://www.numerama.com/content/themes/project-n-theme/resources/assets/images/favicons/ms-icon-310x310.png");
    }

    // Get array of cards

    function array_imgs_from_metadata  ($metadatas, $attributes = false) { if (!is_array($metadatas)) return  img_from_metadata($metadatas, $attributes); $imgs  = array(); foreach ($metadatas as $metadata) { $imgs  [] =  img_from_metadata($metadata, $attributes); } return $imgs;  }
    function array_cards_from_metadata ($metadatas, $attributes = false) { if (!is_array($metadatas)) return card_from_metadata($metadatas, $attributes); $cards = array(); foreach ($metadatas as $metadata) { $cards [] = card_from_metadata($metadata, $attributes); } return $cards; }

    $__dom_card_headline = 2;
    function dom_get_card_headline() { global $__dom_card_headline; return $__dom_card_headline; }
    
    function array_imgs  ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $attributes = false)  {                                                                            return  array_imgs_from_metadata(call_user_func("array_".$source."_".$type, $ids, $filter, $tags_in, $tags_out), ($type == "thumbs") ? dom_attributes_add_class($attributes, dom_component_class('img-thumb'))  : $attributes); }
    function array_card  ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $attributes = false)  { global $__dom_card_headline; $__dom_card_headline = 1+get_last_headline(); return        card_from_metadata(call_user_func("array_".$source."_".$type, $ids, $filter, $tags_in, $tags_out),                                                                                                  $attributes); }
    function array_cards ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $attributes = false)  { global $__dom_card_headline; $__dom_card_headline = 1+get_last_headline(); return array_cards_from_metadata(call_user_func("array_".$source."_".$type, $ids, $filter, $tags_in, $tags_out), ($type == "thumbs") ? dom_attributes_add_class($attributes, dom_component_class('card-thumb')) : $attributes); }
    
    #endregion
    #region HELPERS : DEBUG
    ######################################################################################################################################
    
    function raw_array_debug($content, $html_entities = false, $fields_sep = " ")
    {
        $content = (!is_array($content))          ? $content : ((defined("JSON_PRETTY_PRINT")) ? json_encode($content, JSON_PRETTY_PRINT) : json_encode($content));
        $content = (!$html_entities)              ? $content : htmlentities($content);
        $content = (defined("JSON_PRETTY_PRINT")) ? $content : str_replace("{", "\n{\n", str_replace("[", "\n[\n", str_replace("}", "\n}\n", str_replace("]", "\n]\n", str_replace(":", ": ", str_replace(",", ",".$fields_sep, $content))))));

        return $content;
    }
    
    #endregion
    #region HELPERS : MINIFIERS (QUICK AND DIRTY)
    ######################################################################################################################################

    function minify_html($html)
    {
        return str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), ' ', $html);
    }

    function minify_js($js) // TODO : FIX (URLs are removed as comments)
    {
    //  $js = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/",   '',                     $js);
    //  $js =  str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '),        '',                     $js);
    //  $js = preg_replace(array('(( )+\))','(\)( )+)'),                            ')',                    $js);
    //  $js = preg_replace(array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s'),         array('>','<','\\1'),   $js);
 
        return $js;
    }

    function minify_css($css)
    {
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!',                        '',  $css);
        $css =  str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '),  ' ', $css);
        $css =  str_replace('  ',                                                   ' ', $css);

        return $css;
    }

    #endregion
    #region API : CACHE SYSTEM
    ######################################################################################################################################

    function cache_start()
    {
        if (!!dom_get("cache"))
        {
            $cache_dir = "/cache";

            if (dom_has("cache_reset") && is_dir("/cache")) foreach (array_diff(scandir($cache_dir), array('.','..')) as $basename) @unlink("$cache_dir/$basename");

            $cache_basename         = md5(absolute_uri() . DOM_VERSION);
            $cache_filename         = "$cache_dir/$cache_basename";
            $cache_file_exists      = (file_exists($cache_filename)) && (filesize($cache_filename) > 0);
            $cache_file_uptodate    = $cache_file_exists && ((time() - dom_get("cache_time", 1*60*60)) < filemtime($cache_filename));
            
            dom_set("cache_filename", $cache_filename);
            
            if ($cache_file_exists && $cache_file_uptodate) 
            {   
                $cache_file = @fopen($cache_filename, 'r');
                
                if (!!$cache_file)
                {
                    echo fread($cache_file, filesize($cache_filename));
                    fclose($cache_file);            
                }

                echo eol().comment("Cached copy, generated ".date('Y-m-d H:i', filemtime($cache_filename)));
                exit;
            }
        
            ob_start();
        }        
    }

    function cache_stop()
    {
        if (!!dom_get("cache"))
        {
            $cache_file = @fopen(dom_get("cache_filename"), 'w');
            
            if (!!$cache_file)
            {   
                fwrite($cache_file, ob_get_contents());
                fclose($cache_file);            
            }
            else if (!dom_has("ajax"))
            {
                if ("html" == dom_get("doctype",false)) echo eol().comment("Could not generate cache! " . dom_get("cache_filename"));
            }
            
            ob_end_flush();
        }
    }
    
    #endregion
    #region API : PHP DOCUMENT
    ######################################################################################################################################

    function dom_include($path)
    {
        include($path);
    }

    function dom_redirect($url)
    {   
        header("Location: ".href($url));
        exit;
    }
    
    function dom_redirect_https()
    {        
        if ($_SERVER['SERVER_NAME'] != "localhost" && !isset($_SERVER['HTTPS']) && !dom_has("ajax"))
        {
            $url  = "https://";
            $url .=  $_SERVER["SERVER_NAME"];
            $url .= ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") ? (":".$_SERVER["SERVER_PORT"]) : "";
            $url .=  $_SERVER["REQUEST_URI"];

            dom_redirect($url);
        }
    }

    dom_init_php();
    dom_init_options();
    dom_init_internals();

    function dom_init($doctype = false, $encoding = false, $content_encoding_header = true, $attachement_basename = false, $attachement_length = false)
    {
        
        if ($doctype  === false) $doctype  = dom_get("doctype",  dom_has("rss") ? ((dom_get("rss") == "" || dom_get("rss") == false) ? "rss" : dom_get("rss","rss")) : "html");
        if ($encoding === false) $encoding = dom_get("encoding", dom_has("iso") ? "ISO-8859-1"  : "utf-8");

        dom_set("doctype",  $doctype);
        dom_set("encoding", $encoding);
        
        $types = array
        (
            "xml"   => 'text/xml'    
        ,   "rss"   => 'text/xml'         
        ,   "tile"  => 'text/xml'
        ,   "json"  => 'application/json' 
        ,   "html"  => 'text/html'        
        ,   "csv"   => 'text/csv'           . (($attachement_basename !== false) ? ('; name="'      . $attachement_basename . '.csv') : '')
        ,   "zip"   => 'application/zip'    . (($attachement_basename !== false) ? ('; name="'      . $attachement_basename . '.zip') : '')
        );
    
        $dispositions = array
        (
            "csv"   => 'attachment'         . (($attachement_basename !== false) ? ('; filename="'  . $attachement_basename . '.csv"') : '')
        ,   "zip"   => 'attachment'         . (($attachement_basename !== false) ? ('; filename="'  . $attachement_basename . '.zip"') : '')
        );
        
        if ($content_encoding_header !== false) header('Content-Encoding: ' . $encoding         . '');
        if (array_key_exists($doctype, $types)) header('Content-type: '     . $types[$doctype]  . '; charset=' . $encoding);
        
        if ($attachement_basename !== false)
        {
            if (array_key_exists($doctype, $dispositions))  @header('Content-Disposition: ' . $dispositions[$doctype]                                                                            . '');
            if ($attachement_length !== false)              @header('Content-Length: '      . (($attachement_length !== true) ? $attachement_length : filesize($attachement_basename . '.zip"')) . '');
        }

        generate_all_preprocess();

        cache_start();
    }

    function dom_output($doc = "")
    {
        $doc .= generate_all(dom_get("beautify"));

        if (dom_get("encoding") == "gzip") ob_start("ob_gzhandler");

        echo $doc;
        cache_stop();
        if ("html" == dom_get("doctype",false) && 1 == dom_get("debug")) echo comment("PHP Version: ".PHP_VERSION_ID.". Profiling :".eol().wrap_each(dom_debug_timings(), eol()));
        
        if (dom_get("encoding") == "gzip") ob_end_flush();
    }

    #endregion
    #region DOCUMENTS GENERATION

    function string_ms_browserconfig($beautify = false)
    {
        $eol = $beautify ? cosmetic(eol())       : "";
        $tab = $beautify ? cosmetic(eol().tab()) : "";

        $icon_dims = array(array(70,70),array(150,150),array(310,310),array(310,150));
        $pollings  = 5;

        $xml_icons = "";
        { 
            foreach ($icon_dims as $dim)
            {
                $w = $dim[0];
                $h = $dim[1];

                $path = dom_path("ms-icon-".$w."x".$h.".png");

                if ($path)
                {
                    $xml_icons .= $tab.tag((($w==$h)?"square":"wide").$w."x".$h."logo", false, array("src" => $path), true, true);
                }
            }
        }

        $xml_polling = "";
        for ($i = 0; $i < $pollings; ++$i) $xml_polling .= $tab.tag('polling-uri'.(($i>0)?($i+1):""), false, array("src" => htmlentities(dom_get("canonical").'/?rss=tile&id='.($i+1))), true, true);

        return '<?xml version="1.0" encoding="utf-8"?>'.tag('browserconfig', tag('msapplication', 
        
            $eol.tag('tile',            $xml_icons      . $tab . tag('TileColor', dom_get("theme_color"))                                       . $eol).
            $eol.tag('notification',    $xml_polling    . $tab . tag('frequency', 30) . $tab . tag('cycle', 1)                                  . $eol).
            $eol.tag('badge',           $tab . tag('polling-uri', false, array("src"=>'/badge.xml'), true, true) . $tab . tag('frequency', 30)  . $eol).
            $eol
            ));
    }

    function string_ms_badge($beautify = false)
    {
        return tag("badge", false, array("value" => "available"), true, true);
    }

    function json_manifest()
    {
        $short_title = dom_get("title");
        $pos = stripos($short_title, " ");
        if (false !== $pos) $short_title = substr($short_title, 0, $pos);
        if (strlen($short_title) > 10) $short_title = substr($short_title, 0, 10);
        
        $icons = array();

        foreach (array(36 => 0.75, 48 => 1.0, 72 => 1.5, 96 => 2.0, 144 => 3.0, 192 => 4.0, 512 => 4.0) as $w => $density)
        {            
            $filename = dom_path("android-icon-$w"."x"."$w.png");

            if ($filename)
            {
                $icons[] = array("src"=> $filename, "sizes"=> "$w"."x"."$w", "type"=> "image/png", "density"=> "$density", "purpose"=> "maskable any");
            }
        }

        $json = array(

            "name"             => dom_get("title"),
            "short_name"       => $short_title,
            
            "background_color" => dom_get("background_color"),
            "theme_color"      => dom_get("theme_color"),
           
            "start_url"        => "./?utm_source=homescreen",
            "display"          => "standalone",
            
            "related_applications"=> array( 

                array( "platform"=> "web", "url"=> dom_get("canonical") ) 

                ),
           
            "icons"=> $icons
            );

        return $json;
    }

    function string_manifest($beautify = false) { return (($beautify && defined("JSON_PRETTY_PRINT")) ? json_encode(json_manifest(), JSON_PRETTY_PRINT) : json_encode(json_manifest())); }

    function string_robots($beautify = false)
    {
        return "User-agent: *".eol()."Disallow:";
    }

    function string_human($beautify = false)
    {
        return "/* SITE */".

            eol().  "Standards"     .": ".  "HTML5, CSS3".
            eol().  "Language"      .": ".  "French".
            eol().  "Doctype"       .": ".  "HTML5".
            eol().  "Components"    .": ".  "MCW, Bootstrap, Spectre, Amp and others".
            eol().  "IDE"           .": ".  "Visual Studio Code".
            
            "";
    }

    function string_loading_svg($beautify = false)
    {
        return '<svg class="lds-spinner" width="65px" height="65px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; animation-play-state: running; animation-delay: 0s; background: none;">'.

                '<g transform="rotate(0 50 50)"   style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.9s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.
                '<g transform="rotate(36 50 50)"  style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.8s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.
                '<g transform="rotate(72 50 50)"  style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.7s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.
                '<g transform="rotate(108 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.6s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.
                '<g transform="rotate(144 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.5s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.
                '<g transform="rotate(180 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.4s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.
                '<g transform="rotate(216 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.3s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.
                '<g transform="rotate(252 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.2s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.
                '<g transform="rotate(288 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.1s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.
                '<g transform="rotate(324 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="#FF8800" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.0s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>'.

            '</svg>';
    }

    function string_service_worker_install($beautify = false)
    {
        return '<!doctype html><html><head><title>Installing service worker</title><script type="text/javascript">'.
            ''.
            'var swsource = "https://web.cyanide-studio.coml/bloodbowl/sw.js";'.
            ''.
            'if ("serviceWorker" in navigator)'.
            '{'.
            '    navigator.serviceWorker.register(swsource).then(function(reg)'.
            '    {'.
            '        console.log("AMP ServiceWorker scope: ", reg.scope);'.
            '    })'.
            '    .catch(function(err)'.
            '    {'.
            '        console.log("AMP ServiceWorker registration failed: ", err);'.
            '    });'.
            '};'.
            ''.
        '</script></head><body></body></html>';
    }

    function string_loading_svg_src_base64($beautify = false)
    {
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPgo8c3ZnIHdpZHRoPSI0MHB4IiBoZWlnaHQ9IjQwcHgiIHZpZXdCb3g9IjAgMCA0MCA0MCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjEuNDE0MjE7IiB4PSIwcHgiIHk9IjBweCI+CiAgICA8ZGVmcz4KICAgICAgICA8c3R5bGUgdHlwZT0idGV4dC9jc3MiPjwhW0NEQVRBWwogICAgICAgICAgICBALXdlYmtpdC1rZXlmcmFtZXMgc3BpbiB7CiAgICAgICAgICAgICAgZnJvbSB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIC13ZWJraXQtdHJhbnNmb3JtOiByb3RhdGUoLTM1OWRlZykKICAgICAgICAgICAgICB9CiAgICAgICAgICAgIH0KICAgICAgICAgICAgQGtleWZyYW1lcyBzcGluIHsKICAgICAgICAgICAgICBmcm9tIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKC0zNTlkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICB9CiAgICAgICAgICAgIHN2ZyB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybS1vcmlnaW46IDUwJSA1MCU7CiAgICAgICAgICAgICAgICAtd2Via2l0LWFuaW1hdGlvbjogc3BpbiAxLjVzIGxpbmVhciBpbmZpbml0ZTsKICAgICAgICAgICAgICAgIC13ZWJraXQtYmFja2ZhY2UtdmlzaWJpbGl0eTogaGlkZGVuOwogICAgICAgICAgICAgICAgYW5pbWF0aW9uOiBzcGluIDEuNXMgbGluZWFyIGluZmluaXRlOwogICAgICAgICAgICB9CiAgICAgICAgXV0+PC9zdHlsZT4KICAgIDwvZGVmcz4KICAgIDxnIGlkPSJvdXRlciI+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwwQzIyLjIwNTgsMCAyMy45OTM5LDEuNzg4MTMgMjMuOTkzOSwzLjk5MzlDMjMuOTkzOSw2LjE5OTY4IDIyLjIwNTgsNy45ODc4MSAyMCw3Ljk4NzgxQzE3Ljc5NDIsNy45ODc4MSAxNi4wMDYxLDYuMTk5NjggMTYuMDA2MSwzLjk5MzlDMTYuMDA2MSwxLjc4ODEzIDE3Ljc5NDIsMCAyMCwwWiIgc3R5bGU9ImZpbGw6YmxhY2s7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNNS44NTc4Niw1Ljg1Nzg2QzcuNDE3NTgsNC4yOTgxNSA5Ljk0NjM4LDQuMjk4MTUgMTEuNTA2MSw1Ljg1Nzg2QzEzLjA2NTgsNy40MTc1OCAxMy4wNjU4LDkuOTQ2MzggMTEuNTA2MSwxMS41MDYxQzkuOTQ2MzgsMTMuMDY1OCA3LjQxNzU4LDEzLjA2NTggNS44NTc4NiwxMS41MDYxQzQuMjk4MTUsOS45NDYzOCA0LjI5ODE1LDcuNDE3NTggNS44NTc4Niw1Ljg1Nzg2WiIgc3R5bGU9ImZpbGw6cmdiKDIxMCwyMTAsMjEwKTsiLz4KICAgICAgICA8L2c+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwzMi4wMTIyQzIyLjIwNTgsMzIuMDEyMiAyMy45OTM5LDMzLjgwMDMgMjMuOTkzOSwzNi4wMDYxQzIzLjk5MzksMzguMjExOSAyMi4yMDU4LDQwIDIwLDQwQzE3Ljc5NDIsNDAgMTYuMDA2MSwzOC4yMTE5IDE2LjAwNjEsMzYuMDA2MUMxNi4wMDYxLDMzLjgwMDMgMTcuNzk0MiwzMi4wMTIyIDIwLDMyLjAxMjJaIiBzdHlsZT0iZmlsbDpyZ2IoMTMwLDEzMCwxMzApOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksMjguNDkzOUMzMC4wNTM2LDI2LjkzNDIgMzIuNTgyNCwyNi45MzQyIDM0LjE0MjEsMjguNDkzOUMzNS43MDE5LDMwLjA1MzYgMzUuNzAxOSwzMi41ODI0IDM0LjE0MjEsMzQuMTQyMUMzMi41ODI0LDM1LjcwMTkgMzAuMDUzNiwzNS43MDE5IDI4LjQ5MzksMzQuMTQyMUMyNi45MzQyLDMyLjU4MjQgMjYuOTM0MiwzMC4wNTM2IDI4LjQ5MzksMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxMDEsMTAxLDEwMSk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMy45OTM5LDE2LjAwNjFDNi4xOTk2OCwxNi4wMDYxIDcuOTg3ODEsMTcuNzk0MiA3Ljk4NzgxLDIwQzcuOTg3ODEsMjIuMjA1OCA2LjE5OTY4LDIzLjk5MzkgMy45OTM5LDIzLjk5MzlDMS43ODgxMywyMy45OTM5IDAsMjIuMjA1OCAwLDIwQzAsMTcuNzk0MiAxLjc4ODEzLDE2LjAwNjEgMy45OTM5LDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoMTg3LDE4NywxODcpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTUuODU3ODYsMjguNDkzOUM3LjQxNzU4LDI2LjkzNDIgOS45NDYzOCwyNi45MzQyIDExLjUwNjEsMjguNDkzOUMxMy4wNjU4LDMwLjA1MzYgMTMuMDY1OCwzMi41ODI0IDExLjUwNjEsMzQuMTQyMUM5Ljk0NjM4LDM1LjcwMTkgNy40MTc1OCwzNS43MDE5IDUuODU3ODYsMzQuMTQyMUM0LjI5ODE1LDMyLjU4MjQgNC4yOTgxNSwzMC4wNTM2IDUuODU3ODYsMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxNjQsMTY0LDE2NCk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMzYuMDA2MSwxNi4wMDYxQzM4LjIxMTksMTYuMDA2MSA0MCwxNy43OTQyIDQwLDIwQzQwLDIyLjIwNTggMzguMjExOSwyMy45OTM5IDM2LjAwNjEsMjMuOTkzOUMzMy44MDAzLDIzLjk5MzkgMzIuMDEyMiwyMi4yMDU4IDMyLjAxMjIsMjBDMzIuMDEyMiwxNy43OTQyIDMzLjgwMDMsMTYuMDA2MSAzNi4wMDYxLDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoNzQsNzQsNzQpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksNS44NTc4NkMzMC4wNTM2LDQuMjk4MTUgMzIuNTgyNCw0LjI5ODE1IDM0LjE0MjEsNS44NTc4NkMzNS43MDE5LDcuNDE3NTggMzUuNzAxOSw5Ljk0NjM4IDM0LjE0MjEsMTEuNTA2MUMzMi41ODI0LDEzLjA2NTggMzAuMDUzNiwxMy4wNjU4IDI4LjQ5MzksMTEuNTA2MUMyNi45MzQyLDkuOTQ2MzggMjYuOTM0Miw3LjQxNzU4IDI4LjQ5MzksNS44NTc4NloiIHN0eWxlPSJmaWxsOnJnYig1MCw1MCw1MCk7Ii8+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4K";
    }

    function string_loading_html($beautify = false)
    {
        $css_font_family = '-apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';

        return '<html>'
        .      '<head>'
        .          '<title>Please wait...</title>'
        .          '<meta charset="utf-8" /><meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1" />'
        .          '<meta http-equiv="Content-type" content="text/html;charset=utf-8" />'
        .          '<meta http-equiv="content-language" content="en" />'
        .          '<meta name="format-detection" content="telephone=no" />'
        .          '<meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1" />'
        .          '<meta http-equiv="refresh" content="3;URL=.">'
        .      '</head>'
        .      '<body style="margin: 0; width: 100vw; text-align: center; color: #DDD; background-color: rgb(30,30,30); font-family: '.$css_font_family.'; padding-top: calc(50vh - 2em - 64px);">'
        .          '<p>OFFLINE<br>Please wait...</p>'
        .          '<p><img alt="Please wait..." src="'.string_loading_svg_src_base64($beautify).'" /></p>'
        .      '</body>'
        .  '</html>';
    }

    function string_service_worker($beautify = false)
    {
        return '

importScripts("https://storage.googleapis.com/workbox-cdn/releases/4.3.1/workbox-sw.js");

if (workbox)
{
    const LOCALHOST = ("localhost" == self.location.host);
    
    const VERSION = "0.0.0.1";

    const  cache_prefix = "'.strtoupper(to_classname(dom_get("canonical"))).'";
    const  cache_suffix = VERSION;
    
    const FALLBACK_HTML_URL = "index.php";
    const FALLBACK_IMG_URL  = "loading.svg";
    
    if (LOCALHOST) console.log("Worbox debugging");
    workbox.setConfig({debug: LOCALHOST});
        
    self.addEventListener("message", (event) => { if (event.data && event.data.type === "SKIP_WAITING")  { workbox.core.skipWaiting();  } });
    self.addEventListener("message", (event) => { if (event.data && event.data.type === "CLIENTS_CLAIM") { workbox.core.clientsClaim(); } });

    self.addEventListener("install", (event) => { event.waitUntil(caches.open(workbox.core.cacheNames.runtime).then((cache) => cache.addAll([
    
    //  FALLBACK_HTML_URL,
        FALLBACK_IMG_URL
    
    ]))); });

    workbox.core.setCacheNameDetails({ prefix: cache_prefix, suffix: cache_suffix, precache: "precache", runtime: "runtime", googleAnalytics: "ga" });
//  workbox.core.skipWaiting();
//  workbox.core.clientsClaim();

    var expiration = new workbox.expiration.Plugin({ maxEntries: 1000, maxAgeSeconds: 365 * 24 * 60 * 60 });

//  workbox.routing.registerRoute(new RegExp(".+\\\\.js$"),                       new workbox.strategies.StaleWhileRevalidate( { cacheName: cache_prefix + "-" + "cache-js"     + "-" + cache_suffix, plugins: [expiration] }));
    workbox.routing.registerRoute(new RegExp(".+\\\\.css$"),                      new workbox.strategies.StaleWhileRevalidate( { cacheName: cache_prefix + "-" + "cache-css"    + "-" + cache_suffix, plugins: [expiration] }));
    workbox.routing.registerRoute(new RegExp("\.(?:png|jpg|jpeg|svg|gif)$"),    new workbox.strategies.StaleWhileRevalidate( { cacheName: cache_prefix + "-" + "cache-images" + "-" + cache_suffix, plugins: [expiration] }));
//  workbox.routing.registerRoute(new RegExp("[\s\S]*"),                        new workbox.strategies.NetworkFirst(         { cacheName: cache_prefix + "-" + "cache-POST"   + "-" + cache_suffix, plugins: [expiration] }), "POST");
    workbox.routing.registerRoute(new RegExp("[\s\S]*"),                        new workbox.strategies.StaleWhileRevalidate( { cacheName: cache_prefix + "-" + "cache-GET"    + "-" + cache_suffix, plugins: [expiration] }), "GET");
    workbox.routing.setDefaultHandler(                                          new workbox.strategies.StaleWhileRevalidate( { cacheName: cache_prefix + "-" + "cache-OTHERS" + "-" + cache_suffix, plugins: [expiration] }));

    workbox.routing.setCatchHandler(function(event) 
    {
        if (event && !event.request && event.event) event = event.event;

        if (event && event.request)
        {
            if (event.request.destination)
            {
                var default_html = \''.string_loading_html($beautify).'\';

                switch (event.request.destination)
                { 
                    case "style":       break;
                    case "script":      break;
                //  case "document":    return caches.match(FALLBACK_HTML_URL); break;
                    case "document":    return new Response(default_html, { headers: {"content-type": "text/html"} } ); break;
                    case "audio":       break;
                    case "video":       break;
                    case "manifest":    break;
                    case "image":       return caches.match(FALLBACK_IMG_URL);  break;
                    case "font":        break;
                    case "unknown":     return new Response(default_html, { headers: {"content-type": "text/html"} } ); break;
                //  default:            return new Response(default_html, { headers: {"content-type": "text/html"} } ); break;
                //  default:            return Response.error();
                }
            }
        }

        return Response.error();
    });

    workbox.googleAnalytics.initialize();  
} 
else 
{
    console.log("Could not load workbox framework!");
}';

    }

    $__dom_generated = array(

        array("path" => "manifest.json",                 "generated" => false, "function" => "string_manifest"),
        array("path" => "browserconfig.xml",             "generated" => false, "function" => "string_ms_browserconfig"),
        array("path" => "badge.xml",                     "generated" => false, "function" => "string_ms_badge"),
        array("path" => "robots.txt",                    "generated" => false, "function" => "string_robots"),
        array("path" => "human.txt",                     "generated" => false, "function" => "string_human"),
        array("path" => "loading.svg",                   "generated" => false, "function" => "string_loading_svg"),
        array("path" => "sw.js",                         "generated" => false, "function" => "string_service_worker"),
        array("path" => "install-service-worker.html",   "generated" => false, "function" => "string_service_worker_install")

        );

    function generate_all_preprocess()
    {
        global $__dom_generated;

        foreach ($__dom_generated as &$generated)
        { 
            $generated["generated"] = true;

            if (!dom_get("generate"))
            {
                if (dom_path($generated["path"]))        { $generated["generated"] = false; continue; }
                if (dom_path($generated["path"].".php")) { $generated["generated"] = false; continue; }
            }

            $f = fopen($generated["path"], "w+");
            fclose($f);
        }
    }
    
    function generate_all($beautify = false)
    {
        $prev_beautify = false; if ($beautify) { $prev_beautify = dom_get("beautify"); dom_set("beautify", $beautify); }

        global $__dom_generated;

        foreach ($__dom_generated as $generated)
        { 
            if ($generated["generated"])
            {           
                $f = fopen($generated["path"], "w+");
                if (!$f) continue;

                $content = $generated["function"]($beautify);

                fwrite($f, utf8_encode($content));
                fclose($f);
            }
        }

        if ($beautify) { dom_set("beautify", $prev_beautify); }
    }

    #endregion
    #region CSS snippets
    ######################################################################################################################################

    function css_gradient($from = "var(--text-color)", $to = "var(--theme-color)")
    {
        return "/* Text gradient */".

            " "."background: linear-gradient(-45deg, $to 0%, $from 100%);".
            " "."color: $from;".
            
            " "."display: inline-block;".

            " "."-webkit-background-clip: text;".
            " ".   "-moz-background-clip: text;".
            " ".     "-o-background-clip: text;".
            " ".       "background-clip: text;".

            "-webkit-text-fill-color: transparent;".
        "";
    }
    
    #endregion
    #region API : DOM : URLS
    ######################################################################################################################################

    function absolute_host()                    { $host = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"]; $host = rtrim($host,"/"); $host .= "/"; return $host; }
    function relative_uri($params = false)      { $uri = explode('?', $_SERVER['REQUEST_URI'], 2); $uri = $uri[0]; $uri = ltrim($uri, "/"); if ($params) { $uri .= "?"; foreach (dom_get_all() as $key => $val) $uri .= "&$key=$val"; } return $uri; }
    function relative_uri_ex()                  { return relative_uri(true); }
    function absolute_uri($params = false)      { return absolute_host().relative_uri($params); }
    
    function url_pinterest_board            ($username = false, $board = false) { $username = ($username === false) ? dom_get("pinterest_user")  : $username; 
                                                                                  $board    = ($board    === false) ? dom_get("pinterest_board") : $board;    return "https://www.pinterest.com/$username/$board/";                      }
    function url_instagram_user             ($username = false)                 { $username = ($username === false) ? dom_get("instagram_user")  : $username; return "https://www.instagram.com/$username/";                             }
    function url_instagram_post             ($short_code)                       {                                                                         return "https://instagram.com/p/$short_code/";                             }
    function url_flickr_user                ($username = false)                 { $username = ($username === false) ? dom_get("flickr_user")     : $username; return "https://www.flickr.com/photos/$username/";                         }
    function url_flickr_page                ($page     = false)                 { $page     = ($page     === false) ? dom_get("flickr_page")     : $page;     return "https://www.flickr.com/photos/$page/";                             }
    function url_pinterest_pin              ($pin)                              {                                                                         return "https://www.pinterest.com/pin/$pin/";                              }    
    function url_facebook_page              ($page     = false)                 { $page     = ($page     === false) ? dom_get("facebook_page")   : $page;     return "https://www.facebook.com/$page";                                   }
    function url_twitter_page               ($page     = false)                 { $page     = ($page     === false) ? dom_get("twitter_page")    : $page;     return "https://twitter.com/$page";                                        }
    function url_linkedin_page              ($page     = false)                 { $page     = ($page     === false) ? dom_get("linkedin_page")   : $page;     return "https://www.linkedin.com/in/$page";                                }
    function url_facebook_page_about        ($page     = false)                 { $page     = ($page     === false) ? dom_get("facebook_page")   : $page;     return "https://www.facebook.com/$page/about";                             }
    function url_tumblr_blog                ($blogname = false)                 { $blogname = ($blogname === false) ? dom_get("tumblr_blog")     : $blogname; return "https://$blogname.tumblr.com";                                     }
    function url_tumblr_avatar              ($blogname = false, $size = 64)     { $blogname = ($blogname === false) ? dom_get("tumblr_blog")     : $blogname; return "https://api.tumblr.com/v2/blog/$blogname.tumblr.com/avatar/$size"; }
	function url_messenger                  ($id       = false)                 { $id       = ($id       === false) ? dom_get("messenger_id")    : $id;       return "https://m.me/$id";                                                 }
    function url_amp                        ()                                  {                                                                         return "?amp=1".(dom_is_localhost() ? "#development=1" : "");                  }

    function url_facebook_search_by_tags    ($tags, $userdata = false)          { return "https://www.facebook.com/hashtag/"            . urlencode($tags); }
    function url_pinterest_search_by_tags   ($tags, $userdata = false)          { return "https://www.pinterest.com/search/pins/?q="    . urlencode($tags); }
    function url_instagram_search_by_tags   ($tags, $userdata = false)          { return "https://www.instagram.com/explore/tags/"      . urlencode($tags); }
    function url_tumblr_search_by_tags      ($tags, $userdata = false)          { return "https://".$userdata.".tumblr.com/tagged/"     . urlencode($tags); }
    function url_flickr_search_by_tags      ($tags, $userdata = false)          { return "https://www.flickr.com/search/?text="         . urlencode($tags); }
    
    function url_leboncoin                  ($url = false)                      { return ($url === false) ? dom_get("leboncoin_url", dom_get("leboncoin", "https://www.leboncoin.fr")) : $url; }
    function url_seloger                    ($url = false)                      { return ($url === false) ? dom_get("seloger_url",   dom_get("seloger",   "https://www.seloger.com"))  : $url; }
        
    function url_void                       ()                                  { return "#!"; }
//  function url_print                      ()                                  { return if_then(dom_AMP(), url_void(), "javascript:window.print();");   }
    function url_print                      ()                                  { return if_then(dom_AMP(), url_void(), "javascript:scan_and_print();"); }
    
    #endregion
    #region API : DOM : COLORS
    ######################################################################################################################################

    // https://paulund.co.uk/social-media-colours

    function color_facebook         () { return '#3B5998'; }
    function color_twitter          () { return '#00ACED'; }
    function color_linkedin         () { return '#0077B5'; }
    function color_google           () { return array('#EB4132', '#FBBD01', '#31A952', '#4086F4'); } function color_googlenews() { return color_google(); }
    function color_youtube          () { return '#BB0000'; }
    function color_instagram        () { return '#517FA4'; }
    function color_pinterest        () { return '#CB2027'; }
    function color_flickr           () { return array('#FF0084','#0063DC'); }
    function color_tumblr           () { return '#32506D'; }
    function color_foursquare       () { return '#0072B1'; }
    function color_dribbble         () { return '#EA4C89'; }
    function color_vine             () { return '#00BF8F'; }
    function color_behance          () { return '#1769FF'; }
    function color_github           () { return '#171516'; }
    function color_skype            () { return '#00AFF0'; }
    function color_snapchat         () { return '#FFFA37'; }
    function color_whatsapp         () { return '#64D448'; }
    function color_rss              () { return '#FF6F00'; }
    function color_printer          () { return '#FFFFFF'; }
    function color_numerama         () { return '#E9573F'; }
    function color_messenger        () { return '#0083FF'; }
    function color_alert            () { return '#EE0000'; }
    function color_leboncoin        () { return '#EA6B30'; }
    function color_seloger          () { return '#E00034'; }
    function color_amp              () { return '#0379C4'; }
    function color_dark_and_light   () { return '#FFFFFF'; }
   
    #endregion
    #region API : DOM : HTML COMPONENTS : SPECIAL TAGS
    ######################################################################################################################################

    /**
     * Special helper / low level components
     */
    
    function self($html) { return $html; }

  /*function server_file_name($filename, $existing_only = true)
    {
        if ($existing_only)
        {
        //  Try relative path
            $realpath = realpath($filename);
            if (false !== $realpath && (PHP_VERSION_ID < 50300) && !file_exists($realpath)) { $realpath = false; }  
            if (false !== $realpath) return $realpath;
            
            if (0 === stripos($filename, ROOT))
            {
            //  Try absolute path
                if (0 !== stripos($filename, SYSTEM_ROOT)) $filename = str_replace("//","/", substr_replace($filename, SYSTEM_ROOT, 0, strlen(ROOT)));
                
                $realpath = realpath($filename);
                if (false !== $realpath && (PHP_VERSION_ID < 50300) && !file_exists($realpath)) { $realpath = false; }  
                if (false !== $realpath) return $realpath;
            }
            
            if (0 === stripos($filename, '/'))
            {
            //  Try absolute path
                if (0 !== stripos($filename, SYSTEM_ROOT)) $filename = str_replace("//","/",SYSTEM_ROOT . $filename);
                
                $realpath = realpath($filename);
                if (false !== $realpath && (PHP_VERSION_ID < 50300) && !file_exists($realpath)) { $realpath = false; }  
                if (false !== $realpath) return $realpath;
            }
            
        //  if (false === realpath($filename))  return false;
        //
        //  if (@file_exists($filename)) return $filename;
        //  
        //  if ($filename[0] == '/')
        //  {
        //      if (@file_exists(                          '/'.$filename)) return                           '/'.$filename;
        //      if (@file_exists($_SERVER['DOCUMENT_ROOT'].    $filename)) return $_SERVER['DOCUMENT_ROOT'].    $filename;
        //      if (@file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$filename)) return $_SERVER['DOCUMENT_ROOT'].'/'.$filename;   
        //  }
        //  else
        //  {
        //      $path = str_replace("index.php","",$_SERVER['PHP_SELF']);
        //
        //      if (@file_exists(                          '/'.$path.$filename)) return                           '/'.$path.$filename;
        //      if (@file_exists($_SERVER['DOCUMENT_ROOT'].    $path.$filename)) return $_SERVER['DOCUMENT_ROOT'].    $path.$filename;
        //      if (@file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$path.$filename)) return $_SERVER['DOCUMENT_ROOT'].'/'.$path.$filename;
        //  }  
        
            return false;            
        }
        else
        {
            if (false === stripos($filename, $_SERVER['DOCUMENT_ROOT']))
            {
                if ($filename[0] == '/')
                {
                    return $_SERVER['DOCUMENT_ROOT'].$filename;   
                }
                else
                {
                    $path = str_replace("index.php","",$_SERVER['PHP_SELF']);
                
                    if ($path[0] == '/')    return $_SERVER['DOCUMENT_ROOT'].    $path.$filename;
                    else                    return $_SERVER['DOCUMENT_ROOT'].'/'.$path.$filename;                    
                }  
            }
            else
            {
                return $filename;
            }
        }        
        
        return false;
    }

    function server_file_exists($filename)
    {
        return ""    !=  $filename 
            && false !== $filename 
            && false !== server_file_name($filename);
    }*/

    function include_file($filename)
    {
        ob_start();
        @include $filename;
        $content = @ob_get_clean();
        if (false !== $content) { return $content; }

        $content = @file_get_contents($filename);
        if (false !== $content) { return $content; }

        return "";
    }
    
    function raw            ($html)     { return $html; }

    function raw_html       ($html)     { if (!!dom_get("no_html")) return ''; if (!!dom_get("minify",false)) { $html    = minify_html   ($html);    } return $html; }
    function raw_js         ($js)       { if (!!dom_get("no_js"))   return ''; if (!!dom_get("minify",false)) { $js      = minify_js     ($js);      } return $js;   }
    function raw_css        ($css)      { if (!!dom_get("no_css"))  return ''; if (!!dom_get("minify",false)) { $css     = minify_css    ($css);     } return $css;  }

    function include_html   ($filename) { return (dom_has("rss") || !!dom_get("no_html")) ? '' : raw_html   (include_file($filename)); }
    function include_css    ($filename) { return (dom_has("rss") || !!dom_get("no_css"))  ? '' : raw_css    (include_file($filename)); }
    function include_js     ($filename) { return (dom_has("rss") || !!dom_get("no_js"))   ? '' : raw_js     (include_file($filename)); }
    
    /*
     * CSS tags
     */
     
    $hook_css_vars = array(); function hook_css_var($var) { global $hook_css_vars; $hook_css_vars[$var] = $var; return "HOOK_CSS_VAR_".$var; }
    $hook_css_envs = array(); function hook_css_env($var) { global $hook_css_envs; $hook_css_envs[$var] = $var; return "HOOK_CSS_ENV_".$var; }

    function css_postprocess($css)
    {
        global $hook_css_vars;
        global $hook_css_envs;
    
        foreach ($hook_css_vars as $var) $css = str_replace("HOOK_CSS_VAR_".$var, dom_get($var), $css);
        foreach ($hook_css_envs as $var) $css = str_replace("HOOK_CSS_ENV_".$var, dom_get($var), $css);
    
        return $css;
    }

    function css_name($name) { return trim(str_replace("_","-",$name)); }

    function css_var($var, $val = false, $pre_processing = false, $pan = 32) { if (false === $val) return 'var(--'.css_name($var).')';                                                 return pan('--'.css_name($var) . ': ', $pan) . $val . '; '; }
    function css_env($var, $val = false, $pre_processing = false, $pan = 32) { if (false === $val) return ($pre_processing ? hook_css_env($var) : dom_get($var)); dom_set($var, $val); return pan('--'.css_name($var) . ': ', $pan) . $val . '; '; }

    function css_env_add($vars, $pre_processing = false)
    {
        $unit = "px";
        $res  = 0;

        if (!is_array($vars))
        {
            $vars           = func_get_args();
            $pre_processing = false;
        }

        foreach ($vars as $var)
        {
            $var = ($pre_processing ? hook_css_env($var) : dom_get($var));

            if (false !== stripos($var, "px")) $unit = "px";
            if (false !== stripos($var, "em")) $unit = "em";
            if (false !== stripos($var, "%" )) $unit =  "%";

            $var = str_replace("px", "", $var);
            $var = str_replace("em", "", $var);
            $var = str_replace("%",  "", $var);

            $res += $var;
        }

        $res = (int)$res;

        return $res.$unit;
    }
    
    function css_env_mul($vars, $pre_processing = false)
    {
        $unit = "px";
        $res  = 1;

        if (!is_array($vars))
        {
            $vars           = func_get_args();
            $pre_processing = false;
        }

        foreach ($vars as $var)
        {
            $var = ($pre_processing ? hook_css_env($var) : dom_get($var,$var));

            if (false !== stripos($var, "px")) $unit = "px";
            if (false !== stripos($var, "em")) $unit = "em";
            if (false !== stripos($var, "%" )) $unit =  "%";

            $var = str_replace("px", "", $var);
            $var = str_replace("em", "", $var);
            $var = str_replace("%",  "", $var);

            $res *= $var;
        }

        $res = (int)$res;

        return $res.$unit;
    }
    
    function env        ($var, $val = false, $pre_processing = false, $pan = 32) { return css_env      ($var, $val, $pre_processing, $pan); }
    function env_add    ($vars,              $pre_processing = false, $pan = 32) { return css_env_add  ($vars,      $pre_processing, $pan); }
    function env_mul    ($vars,              $pre_processing = false, $pan = 32) { return css_env_mul  ($vars,      $pre_processing, $pan); }
    
    
    /*
     * Special HTML components
     */
    
    function if_browser($condition, $html) { return (dom_has("rss")) ? '' : ('<!--[if '.$condition.']>' . $html . '<![endif]-->'); }

    #endregion
    #region API : DOM : HTML COMPONENTS : DOCUMENT ROOT
    ######################################################################################################################################

    function jsonfeed($json = false)
    {
        dom_debug_track_timing();
        
    //  TODO : https://jsonfeed.org/mappingrssandatom => Only html hooks ? hooks => array => json => json feed
    //  TODO : https://daringfireball.net/feeds/json
    
        if ("json" == dom_get("doctype", "html"))
        {
            if ($json === false)
            {
                $json = json_encode(dom_get("rss_items", array()));
            }
            
            return $json;
        }
    }
    
    function rss($xml = false)
    {
        dom_debug_track_timing();
        
        if ("rss" == dom_get("doctype", "html"))
        {
            if ($xml === false)
            {
                $xml = rss_channel
                (
                            rss_title           (dom_get("title"))
                . eol() .   rss_description     (dom_get("keywords", dom_get("title")))
                . eol() .   rss_link            (dom_get("url")."/"."rss")
                . eol() .   rss_lastbuilddate   ()
                . eol() .   rss_copyright       ()

                . eol() .   rss_image
                            (
                                        rss_url     (dom_get("url")."/".dom_get("image"))
                            . eol() .   rss_title   (dom_get("title"))
                            . eol() .   rss_link    (dom_get("url")."/"."rss")
                            )

                . eol() .   wrap_each(dom_get("rss_items", array()), eol(), "rss_item_from_item_info", false)
                );
            }
            
            return  ''
        /*  .       '<?xml version="1.0" encoding="'.dom_get("encoding", "utf-8").'" ?>'    */
            .       '<?xml version="1.0" encoding="'.strtoupper(dom_get("encoding", "utf-8")).'"?>'
        /*  .       '<?xml-stylesheet href="'.dom_get("canonical").'/css/rss.css" type="text/css" ?>'   */
        /*  .       '<rss version="2.0" xmlns:atom="https://www.w3.org/2005/Atom" xmlns:media="https://search.yahoo.com/mrss/">'    */
            .       '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">'
            . eol()   
            . eol() . $xml
            . eol()   
            . eol() . '</rss>';
        }
    }

    function tile($xml = false)
    {
        dom_debug_track_timing();
        
        if ("tile" == dom_get("doctype", "html"))
        {
            if ($xml === false)
            {
                foreach (dom_get("rss_items", array()) as $item_info)
                {
                    $xml = tile_item_from_item_info($item_info);
                    break;
                }
            }

            return '<?xml version="1.0" encoding="'.dom_get("encoding", "utf-8").'" ?>'
            . eol()   
            . eol() . $xml
            . eol();
        }
    }

    function html($html = "")
    {
        dom_debug_track_timing();

        if (dom_has("ajax")) $_POST = array();

        if ("html" == dom_get("doctype", "html"))
        {
            if (!dom_has("ajax"))
            {
            //  Lazy html generation

                foreach (dom_get("delayed_components", array()) as $delayed_component => $param)
                {
                    $html = str_replace(comment($delayed_component), call_user_func($delayed_component, $param), $html);
                }

                dom_del("delayed_components");

            //  Clean html

                if (!dom_get("minify"))
                {
                    while (true)
                    {
                        $pos = stripos($html, eol(3)); if (false === $pos) break;
                        $html = substr_replace($html, eol(2), $pos, strlen(eol(3)));
                    }
                }

            //  Return html
                
                return raw_html('<!doctype html>'
                
                . eol()
                . eol() . pan('<!--[if lt IE 7]>',      22).' '.pan('<html '.((dom_AMP())?'amp ':'').'class="no-js lt-ie9 lt-ie8 lt-ie7"', 40).' lang="'.dom_get("lang","en").'"> '.pan('',     4).'<![endif]-->'
                . eol() . pan('<!--[if IE 7]>',         22).' '.pan('<html '.((dom_AMP())?'amp ':'').'class="no-js lt-ie9 lt-ie8"',        40).' lang="'.dom_get("lang","en").'"> '.pan('',     4).'<![endif]-->'
                . eol() . pan('<!--[if IE 8]>',         22).' '.pan('<html '.((dom_AMP())?'amp ':'').'class="no-js lt-ie9"',               40).' lang="'.dom_get("lang","en").'"> '.pan('',     4).'<![endif]-->'
                . eol() . pan('<!--[if gt IE 8]><!-->', 22).' '.pan('<html '.((dom_AMP())?'amp ':'').'class="no-js"',                      40).' lang="'.dom_get("lang","en").'"> '.pan('<!--', 4).'<![endif]-->'
                . eol()
                . eol()). $html . comment("DOM.PHP ".DOM_VERSION) . raw_html(
                  eol()
                . eol() . '</html>');
            }
            else
            {
                call_asyncs_start();

                return call_asyncs();
            }
        }
    }

    function doc($html)
    {
        dom_debug_track_timing();        
        return call_user_func(dom_get("doctype", "html"), $html);
    }

    #endregion
    #region API : DOM : HTML COMPONENTS : MARKUP : HEAD, SCRIPTS & STYLES
    ######################################################################################################################################

    function head($html = false, $async_css = false)
    { 
        dom_debug_track_timing();
        
        global $hook_amp_css;
        
        if (false === $html)
        {
            $path_manifest  = dom_path_coalesce("manifest.json.php", "manifest.json");
            $path_css       = dom_path_coalesce("css/main.css.php",  "css/main.css", "main.css.php", "main.css", "css/screen.css.php", "css/screen.css", "screen.css.php", "screen.css");

            $html =         title()

                 . eol(2) . comment("Metadata")
                 . eol(2) . metas()                                             . (!$path_manifest ? "" : (""
                 . eol(2) . link_rel("manifest", $path_manifest, false, 17)     ))
                 
                 . eol(2) . comment("DOM CSS boilerplate")
                 . eol(2) . link_styles($async_css)
                 . eol(2) . styles()
                                                                                . (!$path_css ? "" : (""
                 . eol(2) . comment("Main style $path_css")                       
                 . eol(2) . style($path_css)                                    ))
                 
                 . eol(2) . comment("DOM Javascript boilerplate")
                 . eol(2) . scripts_head();
        }
        
        $html         = css_postprocess($html);
        $hook_amp_css = css_postprocess($hook_amp_css);

        if (dom_get("support_service_worker", false))
        {
            hook_amp_require("install-serviceworker");
        }
        
        return tag('head',

                    eol(2) . $html .
                    eol(2) . if_then(dom_AMP(), "".
                        
                        eol(2) . '<style amp-custom>' . $hook_amp_css . '</style>'.                        
                        eol(2) . "<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>".
                        eol(2) . '<script async src="https://cdn.ampproject.org/v0.js"></script>'.

                        script_amp_iframe                   ().
                        script_amp_sidebar                  ().
                        script_amp_position_observer        ().
                        script_amp_animation                ().
                        script_amp_form                     ().
                        script_amp_youtube                  ().
                        script_amp_install_serviceworker    ().

                        "")
                    ); 
    }

    function delayed_component($callback, $arg = false) { dom_set("delayed_components", array_merge(dom_get("delayed_components", array()), array($callback => $arg))); return comment($callback); }
    
    function script_amp_install_serviceworker   () { return delayed_component("_".__FUNCTION__, false); }
    function script_amp_iframe                  () { return delayed_component("_".__FUNCTION__, false); }
    function script_amp_sidebar                 () { return delayed_component("_".__FUNCTION__, false); }
    function script_amp_position_observer       () { return delayed_component("_".__FUNCTION__, false); }
    function script_amp_animation               () { return delayed_component("_".__FUNCTION__, false); }
    function script_amp_form                    () { return delayed_component("_".__FUNCTION__, false); }
    function script_amp_youtube                 () { return delayed_component("_".__FUNCTION__, false); }

    function _script_amp_install_serviceworker  () { return if_then(has_amp_requirement("install-serviceworker"),   eol(1) . '<script async custom-element="amp-install-serviceworker' . '" src="https://cdn.ampproject.org/v0/amp-install-serviceworker' . '-0.1.js"></script>'); }
    function _script_amp_iframe                 () { return if_then(has_amp_requirement("iframe"),                  eol(1) . '<script async custom-element="amp-iframe'                . '" src="https://cdn.ampproject.org/v0/amp-iframe'                . '-0.1.js"></script>'); }
    function _script_amp_sidebar                () { return if_then(has_amp_requirement("sidebar"),                 eol(1) . '<script async custom-element="amp-sidebar'               . '" src="https://cdn.ampproject.org/v0/amp-sidebar'               . '-0.1.js"></script>'); }
    function _script_amp_position_observer      () { return if_then(has_amp_requirement("position-observer"),       eol(1) . '<script async custom-element="amp-position-observer'     . '" src="https://cdn.ampproject.org/v0/amp-position-observer'     . '-0.1.js"></script>'); }
    function _script_amp_animation              () { return if_then(has_amp_requirement("animation"),               eol(1) . '<script async custom-element="amp-animation'             . '" src="https://cdn.ampproject.org/v0/amp-animation'             . '-0.1.js"></script>'); }
    function _script_amp_form                   () { return if_then(has_amp_requirement("form"),                    eol(1) . '<script async custom-element="amp-form'                  . '" src="https://cdn.ampproject.org/v0/amp-form'                  . '-0.1.js"></script>'); }
    function _script_amp_youtube                () { return if_then(has_amp_requirement("youtube"),                 eol(1) . '<script async custom-element="amp-youtube'               . '" src="https://cdn.ampproject.org/v0/amp-youtube'               . '-0.1.js"></script>'); }

    function title  ($title = false) { return delayed_component("_".__FUNCTION__, $title); }
    function _title ($title = false) { return ($title === false) ? tag('title', dom_get("title") . ((dom_get("heading") != '') ? (' - '.dom_get("heading")) : '')) : tag('title', $title); }

    function link_rel_icon($name = "favicon", $size = false, $media = false, $ext = "png", $type = null)
    {
        if (is_array($name)) { $html = ""; foreach ($name as $i => $_) { $html_icon = link_rel_icon($_,    $size, $media, $ext, $type); $html .= (($i > 0 && $html_icon != "") ? eol() : "").$html_icon; } return $html; }
        if (is_array($size)) { $html = ""; foreach ($size as $i => $_) { $html_icon = link_rel_icon($name, $_,    $media, $ext, $type); $html .= (($i > 0 && $html_icon != "") ? eol() : "").$html_icon; } return $html; }
        if (is_array($ext))  { $html = ""; foreach ($ext  as $i => $_) { $html_icon = link_rel_icon($name, $size, $media, $_,   $type); $html .= (($i > 0 && $html_icon != "") ? eol() : "").$html_icon; } return $html; }
        if (is_array($type)) { $html = ""; foreach ($type as $i => $_) { $html_icon = link_rel_icon($name, $size, $media, $ext, $_   ); $html .= (($i > 0 && $html_icon != "") ? eol() : "").$html_icon; } return $html; }

        if ($type === null && false !== stripos($name,"apple") && false !== stripos($name, "splash"))   $type = "apple-touch-startup-image";
        if ($type === null && false !== stripos($name,"apple") && false !== stripos($name, "startup"))  $type = "apple-touch-startup-image";
        if ($type === null && false !== stripos($name,"apple"))                                         $type = "apple-touch-icon";
        if ($type === null)                                                                             $type = "icon";

        if (!!$size)
        {
            $size = is_int($size) ? ($size."x".$size) : $size;
            $size = str_replace("-","x",$size);

            $wh = explode("x", $size);

            $w = (int)$wh[0];
            $h = (int)$wh[1];

            if (!!$media)
            {
                if (array_key_exists(0,             $media)) $media_clean["width"]       = $media[0];
                if (array_key_exists(1,             $media)) $media_clean["height"]      = $media[1];
                if (array_key_exists(2,             $media)) $media_clean["ratio"]       = $media[2];
                if (array_key_exists(3,             $media)) $media_clean["orientation"] = $media[3];

                if (array_key_exists("width",       $media)) $media_clean["width"]       = $media["width"];
                if (array_key_exists("height",      $media)) $media_clean["height"]      = $media["height"];
                if (array_key_exists("ratio",       $media)) $media_clean["ratio"]       = $media["ratio"];
                if (array_key_exists("orientation", $media)) $media_clean["orientation"] = $media["orientation"];

                $media = $media_clean;

                if (!array_key_exists("orientation", $media))
                {
                    return        link_rel_icon($name, $w."x".$h, array_merge($media, array("orientation" => "portrait")),  $ext, $type)
                        . eol() . link_rel_icon($name, $h."x".$w, array_merge($media, array("orientation" => "landscape")), $ext, $type);
                }
            }
        }

        $info   = pathinfo($name);
        $dir    = dom_at($info, 'dirname',   false);
        $ext    = dom_at($info, 'extension', $ext);
        $name   = dom_at($info, 'filename',  $name);
        $name   = (!!$dir)  ? "$dir/$name"  : $name;
        $name   = (!!$size) ? "$name-$size" : $name;

        $attributes = array();

        if (!!$size)                            $attributes["sizes"] = $size;
        if (false === stripos($type, "apple"))  $attributes["type"]  = "image/$ext";
        if (!!$media)                           $attributes["media"] = "(device-width: ".$media_clean["width"]."px) and (device-height: ".$media_clean["height"]."px) and (-webkit-device-pixel-ratio: ".$media_clean["ratio"].") and (orientation: ".$media_clean["orientation"].")";

        $path = dom_path($name.$ext);

        return  $path ? link_rel($type, $path, $attributes) : '';
    }
    
    function metas  () { return delayed_component("_".__FUNCTION__, false); }
    function _metas ()
    {
        dom_debug_track_timing();
        
        return          meta_charset('utf-8')
            .   eol()
            .   eol() .                        meta_http_equiv('x-ua-compatible',   'ie=edge,chrome=1')
            .   eol() . if_then(dom_AMP(), '', meta_http_equiv('Content-type',      'text/html;charset=utf-8'))
            .   eol() .                        meta_http_equiv('content-language',  dom_get("lang","en"))
            .   eol()       
            .   eol() . meta(array("title" =>                       dom_get("title") . ((dom_get("heading") != '') ? (' - '.dom_get("heading")) : '')))
            .   eol()       
            .   eol() . meta('keywords',                            dom_get("title").((!!dom_get("keywords") && "" != dom_get("keywords")) ? (', '.dom_get("keywords")) : "")    )
            .   eol()       
            .   eol() . meta('format-detection',                    'telephone=no')
            .   eol() . meta('viewport',                            'width=device-width, minimum-scale=1, initial-scale=1')
        //  .   eol() . meta('robots',                              'NOODP') // Deprecated
        //  .   eol() . meta('googlebot',                           'NOODP')
            .   eol() . meta('description',                         dom_get('description', dom_get('title')))
            .   eol() . meta('author',                              dom_get('author',DOM_AUTHOR))
            .   eol() . meta('copyright',                           dom_get('author',DOM_AUTHOR).' 2000-'.date('Y'))
            .   eol() . meta('title',                               dom_get('title'))
            .   eol() . meta('theme-color',                         dom_get("theme_color"))
            .   eol()       
            .   eol() . meta('DC.title',                            dom_get('title'))
            .   eol() . meta('DC.format',                           'text/html')
            .   eol() . meta('DC.language',                         dom_get('lang','en'))
            .   eol()       
            .   eol() . meta('geo.region',                          dom_get('geo_region'))
            .   eol() . meta('geo.placename',                       dom_get('geo_placename'))
            .   eol() . meta('geo.position',                        dom_get('geo_position_x').';'. dom_get('geo_position_y'))
            .   eol() . meta('ICBM',                                dom_get('geo_position_x').', '.dom_get('geo_position_y'))              
            .   eol()       
            .   eol() . meta('twitter:card',                        'summary')              . if_then(dom_has('twitter_page'), ""
            .   eol() . meta('twitter:site',                        dom_get('twitter_page'))    )
            .   eol() . meta('twitter:url',                         dom_get('canonical'))
            .   eol() . meta('twitter:title',                       dom_get('title'))
            .   eol() . meta('twitter:description',                 dom_get('description', dom_get('title')))
            .   eol() . meta('twitter:image',                       dom_path(dom_get('image')))
            .   eol()       
            .   eol() . meta_property('og:site_name',               dom_get('og_site_name', dom_get('title')))
            .   eol() . meta_property('og:image',                   dom_path(dom_get('image')))
            .   eol() . meta_property('og:title',                   dom_get('title'))
            .   eol() . meta_property('og:description',             dom_get('description'))
            .   eol() . meta_property('og:url',                     dom_get('canonical'))            
            .   eol() . meta_property('og:type',                    'website')
            .   eol()       
            .   eol() . meta('application-name',                    dom_get('title'))                               
            .   eol()                                                                                             . if_then(dom_has("pinterest_site_verification"), ""
			.	eol() . meta('p:domain_verify', 					dom_get("pinterest_site_verification"))     ) . if_then(dom_has("google_site_verification"),    ""
            .   eol() . meta('google-site-verification',            dom_get("google_site_verification"))        )
            .   eol()
            .   eol() . meta('msapplication-TileColor',            	dom_get("theme_color"))
            .   eol() . meta('msapplication-TileImage',            	dom_path('ms-icon-144x144.png'))
            .   eol()
            .   (dom_path('ms-icon-70x70.png'    ) ? (eol() . meta('msapplication-square70x70logo',     dom_path('ms-icon-70x70.png'    ))) : '')
            .   (dom_path('ms-icon-150x150.png'  ) ? (eol() . meta('msapplication-square150x150logo',   dom_path('ms-icon-150x150.png'  ))) : '')
            .   (dom_path('ms-icon-310x150.png'  ) ? (eol() . meta('msapplication-wide310x150logo',     dom_path('ms-icon-310x150.png'  ))) : '')
            .   (dom_path('ms-icon-310x310.png'  ) ? (eol() . meta('msapplication-square310x310logo',   dom_path('ms-icon-310x310.png'  ))) : '')
            .   eol()
            .   eol() . meta('msapplication-notification',         	'frequency=30;'
                                                                .   'polling-uri' .'=/?rss=tile&id=1;'
                                                                .   'polling-uri2'.'=/?rss=tile&id=2;'
                                                                .   'polling-uri3'.'=/?rss=tile&id=3;'
                                                                .   'polling-uri4'.'=/?rss=tile&id=4;'
                                                                .   'polling-uri5'.'=/?rss=tile&id=5; cycle=1')
                                                                
            // TODO FIX HREFLANG ALTERNATE

            .   eol()   
            .   eol() . link_rel("alternate",   "/?rss",     array("type" => "application/rss+xml", "title" => "RSS"))
            .   eol() . link_rel("alternate",   "/?lang=en", array("hreflang" => "en-EN"))
            .   eol() . link_rel("alternate",   "/?lang=fr", array("hreflang" => "fr-fr"))                              . if_then(dom_AMP(), '', ''
            .   eol() . link_rel("amphtml",     "/?amp=1")                                                              )
            .   eol() . link_rel("canonical",   dom_get('canonical')) 
            .   eol()
            .   eol() . link_rel_icon(dom_get("image"))
            .   eol()
            .   eol() . link_rel_icon(array("favicon","android-icon","apple-icon"), array(16,32,57,60,72,76,96,114,120,144,152,180,192,196,310,512))
            .   eol()
            .   eol() . link_rel_icon("apple-splash", "2048x2732" , array(1024, 1366, 2)  )
            .   eol() . link_rel_icon("apple-splash", "1668x2388" , array( 834, 1194, 2)  )
            .   eol() . link_rel_icon("apple-splash", "1668x2224" , array( 834, 1112, 2)  )
            .   eol() . link_rel_icon("apple-splash", "1536x2048" , array( 768, 1024, 2)  )
            .   eol() . link_rel_icon("apple-splash", "828x1792"  , array( 414,  896, 2)  )
            .   eol() . link_rel_icon("apple-splash", "750x1334"  , array( 375,  667, 2)  )
            .   eol() . link_rel_icon("apple-splash", "640x1136"  , array( 320,  568, 2)  )
            .   eol() . link_rel_icon("apple-splash", "1242x2688" , array( 414,  896, 3)  )
            .   eol() . link_rel_icon("apple-splash", "1125x2436" , array( 375,  812, 3)  )
            .   eol() . link_rel_icon("apple-splash", "1242x2208" , array( 414,  736, 3)  )

            ;
    }
    
    function meta($p0, $p1 = false, $pan = 0)                               { return (($p1 === false) ? '<meta'.dom_attributes($p0,$pan).' />' : meta_name($p0,$p1)); }
                            
    function meta_charset($charset)                                         { return meta(array("charset"    => $charset)); }
    function meta_http_equiv($equiv,$content)                               { return meta(array("http-equiv" => $equiv,    "content" => $content), false, array(40,80)); }
    function meta_name($name,$content)                                      { return meta(array("name"       => $name,     "content" => $content), false, array(40,80)); }
    function meta_property($property,$content)                              { return meta(array("property"   => $property, "content" => $content), false, array(40,80)); }
                        
    function manifest($filename = "manifest.json") 
    {
        return link_rel("manifest", $filename) . if_then(!dom_AMP() && !dom_is_localhost(), eol(2) . '<script async src="https://cdn.jsdelivr.net/npm/pwacompat@2.0.6/pwacompat.min.js" integrity="sha384-GOaSLecPIMCJksN83HLuYf9FToOiQ2Df0+0ntv7ey8zjUHESXhthwvq9hXAZTifA" crossorigin="anonymous"></script>'); 
    }

    function link_HTML($attributes, $pan = 0)                               { if (!!dom_get("no_html")) return ''; return tag('link', '', dom_attributes($attributes,$pan), false, true); }
    function link_rel($rel, $href, $type = false, $pan = 0)                 {                           return link_HTML(array_merge(array("rel" => $rel, "href" => $href), ($type !== false) ? (is_array($type) ? $type : array("type" => $type)) : array()), $pan); }
//  function link_style($href, $media = "screen")                           {                           return link_rel("stylesheet", $href, ($media === false) ? "text/css" : array("type" => "text/css", "media" => $media)); }
    function link_style($href, $media = "screen", $async = false)           { if (!!dom_get("no_css"))  return ''; return (dom_AMP() || !!dom_get("include_custom_css")) ? style($href) : link_rel("stylesheet", $href, ($async && !dom_AMP()) ? array("type" => "text/css", "media" => "nope!", "onload" => "this.media='$media'") : array("type" => "text/css", "media" => $media)); }

    function style  ($filename_or_code = "")                                                            { $filename = dom_path($filename_or_code);                                             $css = eol().($filename ? include_css($filename) : raw_css ($filename_or_code)).eol(); if (dom_AMP()) hook_amp_css($css); return dom_AMP() ? '' : tag(if_then(dom_AMP(), 'style amp-custom', 'style'), $css); }
    function script ($filename_or_code = "", $type = "text/javascript",                 $force = false) { $filename = dom_path($filename_or_code); return if_then(!$force && dom_AMP(), '', tag('script', eol().($filename ? include_js ($filename) : raw_js  ($filename_or_code)).eol(),                                                 array("type" => $type))); }
    function script_src($src,                $type = "text/javascript", $extra = false, $force = false) { if (!!dom_get("no_js")) return ''; return if_then(!$force && dom_AMP(), '', tag('script', '',                                                                                                                   ($type === false) ? array("src" => $src) : array("type" => $type, "src" => $src), false, false, $extra)); }
    function script_json_ld($properties)                                                                { return script((((!dom_get("minify",false)) && defined("JSON_PRETTY_PRINT")) ? json_encode($properties, JSON_PRETTY_PRINT) : json_encode($properties)), "application/ld+json", true); }
    
    function dom_script_ajax_head()                                             { return if_then(!dom_AMP(), script(dom_string_script_ajax_head())); }
    function dom_script_ajax_body()                                             { return if_then(!dom_AMP(), script(dom_string_script_ajax_body())); }
    
    function schema($type, $properties = array(), $parent_schema = false)
    {
        return array_merge(($parent_schema === false) ? array() : $parent_schema, array("@context" => "https://schema.org", "@type" => $type), $properties);
    }
    
    function link_style_google_fonts($fonts = false, $async = true)
    {    
        if ($fonts === false) $fonts = dom_get("fonts");
        
        if (!!$fonts) { if (0 === stripos($fonts, '|')) $fonts = substr($fonts,1); }

        return        (!!$fonts ? link_style('https://fonts.googleapis.com/css?family='.str_replace(' ','+', $fonts), "screen", $async) : '')
            . eol() . (true     ? link_style('https://fonts.googleapis.com/icon?family=Material+Icons',               "screen", $async) : '');
    }
	
    function link_styles($async = false, $fonts = false)
    {
        if ($fonts === false) $fonts = dom_get("fonts");

        $path_normalize         = dom_path("css/normalize.min.css");
        $path_sanitize          = dom_path("css/sanitize.min.css");
        $path_h5bp              = dom_path("css/h5bp/main.css");
        $path_material          = dom_path("css/material-components-web.min.css");
        $path_bootstrap         = dom_path("css/bootstrap.min.css");
        $path_google_fonts      = dom_path("css/google-fonts.css");
        $path_material_icons    = dom_path("css/material-icons.css");
        $path_slick             = dom_path("css/slick.css");
        $path_slick             = dom_path("css/slick-theme.css");

        return                                                                                                                                                                                                                                                                                     (("normalize" == dom_get("normalize")) ? (""
            .           ($path_normalize      ? link_style($path_normalize      , "screen", false)  : link_style('https://cdnjs.cloudflare.com/ajax/libs/normalize/'         . dom_get("version_normalize") . '/normalize.min.css',                       "screen", false     ))         ) : "") . (("sanitize"  == dom_get("normalize")) ? (""
            .   eol() . ($path_sanitize       ? link_style($path_sanitize       , "screen", false)  : link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . dom_get("version_sanitize")  . '/sanitize.min.css',                        "screen", false     ))         ) : "")
        //  .   eol() . ($path_h5bp           ? link_style($path_h5bp           , "screen", false)  : link_style('https://cdn.jsdelivr.net/npm/html5-boilerplate@'           . dom_get("version_h5bp")      . '/dist/css/main.css',                       "screen", false     ))                 
                                                                                                                                                                                                                                                                                                 . (("material"  == dom_get("framework")) ? (""
            .   eol() . ($path_material       ? link_style($path_material       , "screen", false)  : link_style('https://unpkg.com/material-components-web@'                . dom_get("version_material")  . '/dist/material-components-web.min.css',    "screen", false     ))         ) : "") . (("bootstrap" == dom_get("framework")) ? (""
            .   eol() . ($path_bootstrap      ? link_style($path_bootstrap      , "screen", false)  : link_style('https://stackpath.bootstrapcdn.com/bootstrap/'             . dom_get("version_bootstrap") . '/css/bootstrap.min.css',                   "screen", false     ))         ) : "") . (("spectre"   == dom_get("framework")) ? (""
            .   eol() .                                                                               link_style('https://unpkg.com/spectre.css/dist/spectre.min.css')
            .   eol() .                                                                               link_style('https://unpkg.com/spectre.css/dist/spectre-exp.min.css')
            .   eol() .                                                                               link_style('https://unpkg.com/spectre.css/dist/spectre-icons.min.css')                                                                                                         ) : "") . (!!$fonts                          ? (""
            .   eol() . ($path_google_fonts   ? link_style($path_google_fonts   , "screen", $async) : link_style('https://fonts.googleapis.com/css?family='.str_replace(' ','+', $fonts),                                                             "screen", $async    ))         ) : "") . (("material"  == dom_get("framework")) ? ("" 
            .   eol() . ($path_material_icons ? link_style($path_material_icons , "screen", $async) : link_style('https://fonts.googleapis.com/icon?family=Material+Icons',                                                                           "screen", $async    ))         ) : "") . (!!dom_get("support_sliders", false)   ? (""
            .   eol() . ($path_slick          ? link_style($path_slick          , "screen", $async) : link_style('https://cdn.jsdelivr.net/jquery.slick/'                    . dom_get("version_slick")     . '/slick.css',                           "screen", $async    ))
            .   eol() . ($path_slick          ? link_style($path_slick          , "screen", $async) : link_style('https://cdn.jsdelivr.net/jquery.slick/'                    . dom_get("version_slick")     . '/slick-theme.css',                     "screen", $async    ))         ) : "")
            ;
    }
    
    define("IMPORTANT", !!dom_AMP() ? '' : ' !important');
    
    function include_css_main_toolbar_adaptation() { return delayed_component("_".__FUNCTION__); }

    function _include_css_main_toolbar_adaptation()
    {
        if (!!dom_get("toolbar_banner") && !!dom_get("toolbar_nav")) return ".main { margin-top: calc(var(--header-height) + var(--header-toolbar-height)); }";
        if (!!dom_get("toolbar_banner"))                             return ".main { margin-top: calc(var(--header-height)); }";
        if (!!dom_get("toolbar_nav"))                                return ".main { margin-top: calc(var(--header-toolbar-height)); }";

        return "";
    }

    function include_css_boilerplate()
    {
        return !!dom_get("no_css") ? '' : ('

    /* DOM CSS boilerplate */

    :root
    {
    	' . eol() . tab(2) . env("theme_color", 	        dom_get("theme_color")                  )
          . eol() . tab(2) . env("text_color", 		        dom_get("text_color")                   )
          . eol() . tab(2) . env("link_color", 		        dom_get("link_color")                   )
          . eol() . tab(2) . env("background_color",        dom_get("background_color")             )
          
          . eol() . tab(2) . env("header_height",           dom_get("header_height")                )
          . eol() . tab(2) . env("header_min_height",       dom_get("header_min_height")            )
          . eol() . tab(2) . env("header_toolbar_height",   dom_get("header_toolbar_height")        )
          
          . eol() . tab(2) . env("main_max_width",          "1024px"                                )
          
          . eol() . tab(2) . env("content_default_margin",  "10px"                                  )
          
          . eol() . tab(2) . env("default_image_width",     dom_get("default_image_width",  300)    )
          . eol() . tab(2) . env("default_image_height",    dom_get("default_image_height", 200)    )
          . eol() . tab(2) . env("default_image_ratio",     "calc(var(--default-image-width) / var(--default-image-height))")
          
          . eol() . tab(2) . env("scrollbar_width",         "17px").'
    }

                                  '.str_pad(" ", strlen(get("main_max_width"))).'    :root { --main-width: 100vw; }
    @media screen and (min-width: '.         env("main_max_width", false, true).') { :root { --main-width: '.env("main_max_width", false, true).'; } }
    
    /* Font stack */

    body,h1,h2,h3,h4,h5,h6                          { font-family: -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"; }

    /* Colors */
    
    body                                            { background-color: var(--background-color); }
    a, a:hover, a:visited                           { color: var(--link-color); }
   
    /* Layout */
    
     body                                           { text-align: center; min-height: 100vh; }
     main                                           { text-align: left; padding-top: unset; margin-top: 0px; margin-right: auto; margin-bottom: 0px; margin-left: auto; width: 100%; max-width: var(--main-max-width) }

     /* Main content inflate (makes footer sticky) */

     body                                           { display: flex; flex-direction: column; min-height: 100vh; } 
     body>main                                      { flex: 1; }

     /* Toolbar */

    .toolbar                                        { width: 100%; z-index: 1; }
     
    .toolbar-row                                    { width: 100%; margin-left: 0px; margin-right: 0px; display: flex; }

    .toolbar-row                                    { background-color: var(--theme-color);      color: var(--background-color); }
    .toolbar-row a                                  { background-color: var(--theme-color);      color: var(--background-color); }
    .toolbar-row-banner                             { background-color: var(--background-color); color: default;                 }
    
    .toolbar-row                                    { height: var(--header-toolbar-height); align-items: center; }
    .toolbar-row-banner                             { height: var(--header-height); min-height: var(--header-min-height); }

    .toolbar-row     .cell                          { overflow: hidden; }
    .toolbar-row-nav .cell:nth-child(1)             { width: calc(100vw / 2 - var(--scrollbar-width) / 2 - var(--main-max-width) / 2); min-width: var(--header-toolbar-height); }
    .toolbar-row-nav .cell:nth-child(2)             { flex: 0 1 auto; text-align: left; }
    .toolbar-row-nav .cell:nth-child(3)             { flex: 1 0 auto; text-align: right; margin-right:var(--content-default-margin) } 

    .toolbar-row-nav .cell:nth-child(3) a           { margin-left: var(--content-default-margin); }

    .toolbar .nav-link                              { padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px; font-size: 1.5em; } 
    .toolbar .row.static                            { visibility: hidden; position: fixed; top: 0px; z-index: 999999; } 

    .menu-toggle                                    { width: var(--header-toolbar-height); }
    .menu-toggle a,       .toolbar-title a,
    .menu-toggle a:hover, .toolbar-title a:hover    { text-decoration: none; }
    .toolbar-title .headline1                       { margin-top: 0px; margin-bottom: 0px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .menu                                           { display: none } /* BY DEFAULT, DYNAMIC MENU IS NOT SUPPORTED */

    body>.footer                                    { background-color: var(--theme-color); color: var(--background-color); }

    picture, figure, img, amp-img                   { max-width: 100%; object-fit: cover; vertical-align: top; display: inline-block }
    figure                                          { margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px;  }
    img, amp-img                                    { max-width: 100%; object-fit: cover; }
    .grid                                           { display: grid; grid-gap: var(--content-default-margin); }

    /* Back-to-top style */    
    
    .cd-top, .cd-top:visited                        { background-color: var(--theme-color); color: var(--background-color); }
    .cd-top                                         { text-decoration: none; display: inline-block; height: 40px; width: 40px; position: fixed; bottom: 40px; right: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); text-align: center; line-height: 40px; visibility: hidden; opacity: 0 }
    .cd-top                                         { transition: opacity .3s 0s, visibility 0s .3s; }
    .cd-top.cd-is-visible, .cd-top.cd-fade-out,    
    .no-touch .cd-top:hover                         { transition: opacity .3s 0s, visibility 0s 0s; }
    .cd-top.cd-is-visible                           { visibility: visible; opacity: 1; }
    .cd-top.cd-fade-out                             { opacity: .5; }
    .cd-top:hover                                   { opacity: 1; text-decoration: none }
    
    @media only screen and (min-width:  768px)      { .cd-top { right: 20px; bottom: 20px; } }
    @media only screen and (min-width: 1024px)      { .cd-top { right: 30px; bottom: 30px; line-height: 60px; height: 60px; width: 60px; font-size: 30px } }
    
    /* Animations */

    a, a svg path   { transition: .6s ease-in-out }

    /* Other utilities */    
    
/*  .div-svg-icon-container                         { position: relative; bottom: -6px; padding-right: 6px; } */
    .app-install                                    { display: none }
    .anchor                                         { visibility: hidden; display: block; /* height: 1px; */ position: relative; top: calc(-1 * var(--header-toolbar-height) - var(--header-min-height)) }
    .clearfix { height: 1% } .clearfix:after        { content:"."; height:0; line-height:0; display:block; visibility:hidden; clear:both; }
    
        
    /* Menu open/close button layout */

    .menu-switch-symbol, .menu-close-symbol { height: var(--header-toolbar-height); line-height: var(--header-toolbar-height); }

    /* Menu open/close mechanism */

    .menu-close-symbol { display: none; }

    /* Menu list */

    .menu               { background-color: var(--theme-color); color: var(--background-color); box-shadow: 1px 1px 4px 0 rgba(0,0,0,.2); }
    .menu a:hover       { background-color: var(--background-color); color: var(--theme-color); }

    .menu               { position: absolute; max-height: 0; transition: max-height 1s ease-out; text-align: left; }
    .menu ul            { list-style-type: none; padding-inline-start: 0px; padding-inline-end: 0px; margin-block-end: 0px; margin-block-start: 0px; }
    .menu li a          { display: inline-block; width: 100%; padding: var(--content-default-margin); }

    /* Main images */
        
    main figure           { display: inline-block; }
    main figure > picture,
    main figure > amp-img { display: inline-block; width: 100%; height: 0px; padding-bottom: calc(100% / var(--default-image-ratio)); overflow: hidden; position: relative; }
    
    main figure img { /* position: absolute; */ left: 0px; top: 0px; width: 100%; height: 100%;}
    
    main amp-img,         main img,         main picture         { object-fit: cover; }    
    main amp-img.loading, main img.loading, main picture.loading { object-fit: none;  }

    /* Scrollbar */
    
    body          { scrollbar-width: var(--scrollbar-width); }
    body::-webkit-scrollbar { width: var(--scrollbar-width); }
    
    body {                           scrollbar-color: var(--theme-color)                                                      var(--background-color); }
    body::-webkit-scrollbar-thumb { background-color: var(--theme-color); } body::-webkit-scrollbar-track { background-color: var(--background-color); }

    /* Aspect Ratio wrappers */

    .aspect-ratio > :first-child    { width:  100%; }
    .aspect-ratio > img             { height: auto; } 
    
    .aspect-ratio                   { position: relative; }
    .aspect-ratio::before           { content: ""; display: block; padding-bottom: calc(100% / (16 / 9)); }  
    .aspect-ratio > :first-child    { position: absolute; top: 0; left: 0; height: 100%; }  
    
    .aspect-ratio-16-9::before      { padding-bottom: calc(100% / (16 / 9));    }  
    .aspect-ratio-16-10::before     { padding-bottom: calc(100% / (16 / 10));   }  
    .aspect-ratio-4-3::before       { padding-bottom: calc(100% / (4 / 3));     }  
    .aspect-ratio-3-2::before       { padding-bottom: calc(100% / (3 / 2));     }  
    .aspect-ratio-1-1::before       { padding-bottom: calc(100%);               }  
    

'/*<-- !AMP */.(!!dom_AMP() ? '' : '

    /* Toolbar */

    .toolbar    { position: fixed; top: 0px; }
    '.include_css_main_toolbar_adaptation().'

    /* Menu open/close mechanism */

    #menu-open        .menu-switch-symbol { display: inline-block;  }
    #menu-open:target .menu-switch-symbol { display: none; }
    
    #menu-open        .menu-close-symbol { display: none;  }
    #menu-open:target .menu-close-symbol { display: inline-block; }
    
    #menu-open        .menu { display: none;  max-height:   0vh; }
    #menu-open:target .menu { display: block; max-height: 100vh; }
    
')./* !AMP -->*/'

'/*<-- AMP */.(!dom_AMP() ? '' : '

    /* AMP DEFAULTS */
    
    .menu              { display: block } /* AMP DYNAMIC MENU SUPPORTED */
    
    amp-sidebar        { background-color: var(--background-color); }
    amp-sidebar        { text-align: left; }
    amp-sidebar .menu  { position: relative; }
    amp-sidebar ul     { list-style-type: none; padding-left: 0px } 

    
')./* AMP -->*/'

'/*<-- material */.(("material" != dom_get("framework")) ? '' : '

    /* MATERIAL DESIGN DEFAULTS */
    
    :root
    {
    	--mdc-theme-primary:    var(--theme-color);
        --mdc-theme-secondary:  var(--link-color);
        --mdc-theme-background: var(--background-color);
    }
    
    .toolbar .row .cell { overflow: visible }
        
    #menu-open        .menu { display: block; max-height: 100vh; }
    #menu-open:target .menu { display: block; max-height: 100vh; }
    

    .menu { display: block } /* MATERIAL DESIGN LIB DYNAMIC MENU SUPPORTED */

    .mdc-top-app-bar--dense .mdc-top-app-bar__row { height: var(--header-toolbar-height); /*align-items: center;*/ }
    .mdc-top-app-bar { '.(dom_AMP() ? 'position: inherit;' : '').' }
    .mdc-top-app-bar__section { flex: 0 1 auto; }
    .mdc-top-app-bar--dense .mdc-top-app-bar__title { padding-left: 0px; }
    .mdc-menu--open  { margin-top: var(--header-toolbar-height); }
    
')./* material -->*/'

'/*<-- bootstrap */.(("bootstrap" != dom_get("framework")) ? '' : '

    /* BOOTSTRAP DEFAULTS */
    
    .menu   { display: block } /* BOOTSTRAP LIB DYNAMIC MENU SUPPORTED */
    .navbar { padding: 0px }
    
')./* bootstrap -->*/'

'/*<-- spectre */.(("spectre" != dom_get("framework")) ? '' : '

    /* SPECTRE DEFAULTS */
    
    .text-primary:      var(--theme-color);
    .text-secondary:    var(--link-color);
    .bg-primary:        var(--background-color);
    
')./* spectre -->*/'

		');
    }
    
    function styles()
    {
        return style(include_css_boilerplate());
    }
    
    function scripts_head()
    {   
        return     dom_script_ajax_head()         
        . eol(2) . script('var scan_and_print = function() { alert("Images are not loaded yet"); };'); 
    }
    
    function scripts_body()
    {
        $jquery_local_filename = dom_path('js/jquery-'.dom_get("version_jquery").'.min.js');

        return  ((!dom_AMP() && $jquery_local_filename) ? script_src($jquery_local_filename) : 
                (
                    script_src('https://code.jquery.com/jquery-'                . dom_get("version_jquery") . '.min.js',        false, 'crossorigin="anonymous"')
                //  script_src('https://ajax.googleapis.com/ajax/libs/jquery/'  . dom_get("version_jquery") . '/jquery.min.js', false, 'crossorigin="anonymous"')
                //  script_src('https://ajax.microsoft.com/ajax/jquery/jquery-' . dom_get("version_jquery") . '.min.js',        false, 'crossorigin="anonymous"')

                ))
        
            .   eol(2) . a("▲", url_void(), "cd-top")
            
            .   if_then(dom_get("support_sliders", false),   eol(2) . script_src('https://cdn.jsdelivr.net/jquery.slick/'            . dom_get("version_slick")     . '/slick.min.js'))            
            .   if_then("material"  == dom_get("framework"), eol(2) . script_src('https://unpkg.com/material-components-web@'        . dom_get("version_material")  . '/dist/material-components-web.min.js'))
            .   if_then("bootstrap" == dom_get("framework"), eol(2) . script_src('https://cdnjs.cloudflare.com/ajax/libs/popper.js/' . dom_get("version_popper")    . '/umd/popper.min.js'))
            .   if_then("bootstrap" == dom_get("framework"), eol(2) . script_src('https://stackpath.bootstrapcdn.com/bootstrap/'     . dom_get("version_bootstrap") . '/js/bootstrap.min.js'))
            
            .   dom_script_ajax_body()

        .   ((defined("TOKEN_GOOGLE_ANALYTICS")) ? ('' // <!-- IF GOOGLE ANALYTICS 
        
            .   eol(2) . script
                (
                    eol(1) . '/*  Google analytics */ '
                .   eol(1)
                .   eol(1) . tab() . 'window.ga=function(){ga.q.push(arguments)}; ga.q=[]; ga.l=+new Date; ga("create","'.TOKEN_GOOGLE_ANALYTICS.'","auto"); ga("send","pageview");'
                .   eol(1)
                )
                            
            .   eol(2) . script_src('https://www.google-analytics.com/analytics.js', false, 'async defer')
                        
        .   '') : '') // IF GOOGLE ANALYTICS -->
            
            .   eol(2) .    script
                            (
                                eol(1) . tab(1) .   '$(document).ready(function()'
                            .   eol(1) . tab(1) .   '{'
                            .   eol(1) . tab(2) .       '$("img").on("error", function() { $(this).attr("src", "' . url_img_blank() . '"); });'
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(2) .       'function updateLazyImages() '
                            .   eol(1) . tab(2) .       '{'
                            .   eol(1) . tab(3) .           '$("img.lazy[data-src]").each(function(i, img) '
                            .   eol(1) . tab(3) .           '{'
                            .   eol(1) . tab(4) .               'var rect = img.getBoundingClientRect();'
                            .   eol(1) . tab(2) 
                            .   eol(1) . tab(4) .               'if (rect.bottom >= 0 && rect.right >= 0 && rect.top <= (window.innerHeight || document.documentElement.clientHeight)) '
                            .   eol(1) . tab(4) .               '{'
                            .   eol(1) . tab(5) .                   '$(img).parent().find("source.lazy[data-srcset]").each(function(i, src) '
                            .   eol(1) . tab(5) .                   '{'
                            .   eol(1) . tab(6) .                       '$(src).attr("srcset", $(src).attr("data-srcset"));'
                            .   eol(1) . tab(6) .                       '$(src).removeAttr("data-srcset");'
                            .   eol(1) . tab(5) .                   '});'
                            .   eol(1) . tab(5) .                   ''
                            .   eol(1) . tab(5) .                   '$(img).attr("src", $(img).attr("data-src"));'
                            .   eol(1) . tab(5) .                   '$(img).removeAttr("data-src");'
                            .   eol(1) . tab(5) .                   '$(img).on("load", function() { $(img).removeClass("loading"); });'
                            .   eol(1) . tab(4) .               '}'
                            .   eol(1) . tab(4) .               'else'
                            .   eol(1) . tab(4) .               '{'
                            .   eol(1) . tab(4) .               '}'
                            .   eol(1) . tab(3) .           '});'
                            .   eol(1) . tab(2) .       '}'
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(2) .       'function updateToolbarHeight()'
                            .   eol(1) . tab(2) .       '{'
                            .   eol(1) . tab(3) .           '$(".toolbar-row-banner").css("height", "calc(' . dom_get("header_height")     . ' - " + $(window).scrollTop() + "px)");'
                            .   eol(1) . tab(2) .       '}'
                            
                        . (("material" != dom_get("framework")) ? '' : (''
                        
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(2) .       'if (typeof window.mdc !== "undefined") { window.mdc.autoInit(); }'
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(2) .       '/*  Adjust toolbar margin */'
                            .   eol(1) . tab(1)
                            .   eol(1) . tab(3) .           '$(".mdc-top-app-bar").css("position", "fixed");'
                            .   eol(1) . tab(3) .           '// $(".mdc-top-app-bar--dense-fixed-adjust").css("margin-top", "calc(' . dom_get("header_height") . ' + ' . dom_get("header_toolbar_height") . ')");'
                            .   eol(1) . tab(1)             
                            .   eol(1) . tab(3) .           '(function()'
                            .   eol(1) . tab(3) .           '{'
                            .   eol(1) . tab(4) .               'var pollId = 0;'
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(4) .               'pollId = setInterval(function()'
                            .   eol(1) . tab(4) .               '{'
                            .   eol(1) . tab(5) .                   'var e = document.querySelector(".mdc-top-app-bar");'
                            .   eol(1) . tab(1)
                            .   eol(1) . tab(5) .                   'if (e != null)'
                            .   eol(1) . tab(5) .                   '{ '
                            .   eol(1) . tab(6) .                       'var pos = getComputedStyle(e).position;'
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(6) .                       'if (pos === "fixed" || pos === "relative")'
                            .   eol(1) . tab(6) .                       '{'
                            .   eol(1) . tab(7) .                           'init();'
                            .   eol(1) . tab(7) .                           'clearInterval(pollId);'
                            .   eol(1) . tab(6) .                       '}'
                            .   eol(1) . tab(5) .                   '}'
                            .   eol(1) . tab(1)
                            .   eol(1) . tab(4) .               '}, 250);'
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(4) .               'function init()'
                            .   eol(1) . tab(4) .               '{'
                            .   eol(1) . tab(5) .                   'var e = document.querySelector(".mdc-top-app-bar");'
                            .   eol(1) . tab(1)
                            .   eol(1) . tab(5) .                   'if (e != null && typeof mdc !== "undefined")'
                            .   eol(1) . tab(5) .                   '{ '
                            .   eol(1) . tab(6) .                       'var toolbar = mdc.topAppBar.MDCTopAppBar.attachTo(e);'
                            .   eol(1) . tab(6) .                       'toolbar.fixedAdjustElement = document.querySelector(".mdc-top-app-bar--dense-");'
                            .   eol(1) . tab(5) .                   '}'
                            .   eol(1) . tab(4) .               '}'
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(3) .           '})(); '
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(2) .       '/*  Menu */'
                            .   eol(1) . tab(1) 
                            .   eol(1) . tab(3) .           'var menuEl = document.querySelector(".mdc-menu");'
                            .   eol(1) . tab(1)
                            .   eol(1) . tab(3) .           'if (menuEl != null && typeof mdc !== "undefined")'
                            .   eol(1) . tab(3) .           '{  '
                            .   eol(1) . tab(4) .               'var menuToggle = document.querySelector(".menu-toggle");'
                            .   eol(1) . tab(4) .               'var menu       = new mdc.menu.MDCMenu(menuEl);'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .               'menuToggle.addEventListener("click", function() '
                            .   eol(1) . tab(4) .               '{ '
                            .   eol(1) . tab(5) .                   'menu.open = !menu.open; '
                            .   eol(1) . tab(4) .               '});'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .               'menuEl.addEventListener("MDCMenu:selected", function(evt) '
                            .   eol(1) . tab(4) .               '{'
                            .   eol(1) . tab(5) .                   'const detail = evt.detail;'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(5) .                   'detail.item.textContent;'
                            .   eol(1) . tab(5) .                   'detail.index;'
                            .   eol(1) . tab(4) .               '});'
                            .   eol(1) . tab(3) .           '}'
                            
                        )) // material
                        
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(2) .       '/*  Back to top button */'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(3) .           'var $back_to_top                    = null;'
                            .   eol(1) . tab(3) .           'var  back_to_top_offset             =  300;'
                            .   eol(1) . tab(3) .           'var  back_to_top_offset_opacity     = 1200;'
                            .   eol(1) . tab(3) .           'var  back_to_top_scroll_duration    =  700;'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(3) .           'function updateBackToTopButton()'
                            .   eol(1) . tab(3) .           '{'
                            .   eol(1) . tab(4) .               '($(window).scrollTop() > back_to_top_offset) ? $back_to_top.addClass("cd-is-visible") : $back_to_top.removeClass("cd-is-visible cd-fade-out");'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .               'if ($(window).scrollTop() > back_to_top_offset_opacity)'
                            .   eol(1) . tab(4) .               '{ '
                            .   eol(1) . tab(5) .                   '$back_to_top.addClass("cd-fade-out");'
                            .   eol(1) . tab(4) .               '}'
                            .   eol(1) . tab(3) .           '}'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(3) .           '$back_to_top = $(".cd-top");'
                            .   eol(1) . tab(3) .           '$back_to_top.on("click", function(event)'
                            .   eol(1) . tab(3) .           '{'
                            .   eol(1) . tab(4) .               'event.preventDefault();'
                            .   eol(1) . tab(4) .               '$("body,html").animate({ scrollTop: 0 }, back_to_top_scroll_duration);'
                            .   eol(1) . tab(3) .           '});'                                                                                     . if_then(dom_get("support_sliders", false), ''
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(3) .           'function updateSliders()'
                            .   eol(1) . tab(3) .           '{'
                            .   eol(1) . tab(4) .               '$(".slider").not(".slick-initialized").slick({"autoplay":true});'
                            .   eol(1) . tab(3) .           '}'                                                                                       )
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(2) .       '/*  updateLazyImages(); */'                                                                  . if_then(dom_get("support_sliders", false), ''
                            .   eol(1) . tab(2) .       '/*  updateSliders(); */'                                                                     )
                            .   eol(1) . tab(3) .           'updateToolbarHeight();'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(3) .           '$(window).scroll(function()'
                            .   eol(1) . tab(3) .           '{'
                            .   eol(1) . tab(4) .               'updateBackToTopButton();'
                            .   eol(1) . tab(4) .               'updateLazyImages();'
                            .   eol(1) . tab(4) .               'updateToolbarHeight();'
                            .   eol(1) . tab(3) .           '});'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(3) .           'function initRotatingHeaders()'
                            .   eol(1) . tab(3) .           '{ '
                            .   eol(1) . tab(4) .               'dom_ajax("?ajax=header-backgrounds", function(content)'
                            .   eol(1) . tab(4) .               '{' 
                            .   eol(1) . tab(5) .                   'if (content != "")'
                            .   eol(1) . tab(5) .                   '{'
                            .   eol(1) . tab(6) .                       'var index_url = 0;'
                            .   eol(1) . tab(6) .                       'var urls = content.split(",");'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(6) .                       'if (urls && !(typeof urls === "undefined") && urls.length > 0)'
                            .   eol(1) . tab(6) .                       '{'
                            .   eol(1) . tab(7) .                           'setInterval(function()'
                            .   eol(1) . tab(7) .                           '{'
                            
                    //  . ((("material" != dom_get("framework")) && ("bootstrap" != dom_get("framework"))) ? '' : (''
                        
                            .   eol(1) . tab(8) .                               '$(".toolbar-row-banner").css("background-image", "url(" + urls[index_url] + ")");'
                            
                    //  )) // material
                        
                            .   eol(1) . tab(8) .                               'index_url = (index_url + 1) % urls.length;'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           '}, 10*1000);'
                            .   eol(1) . tab(6) .                       '}'
                            .   eol(1) . tab(5) .                   '}'
                            .   eol(1) . tab(4) .               '});'
                            .   eol(1) . tab(3) .           '}'
                            .   eol(1) . tab(3) .           ''                                                                      . ((defined("TOKEN_PACKAGE")) ? ''
                            .   eol(1) . tab(3) .           'console.log("Third-parties tokens packages : '.TOKEN_PACKAGE.'");'     : '').''
                            .   eol(1) . tab(3) .           ''
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(3) .           '$(window).on("load", function()'
                            .   eol(1) . tab(3) .           '{ '                                  . if_then(dom_has("support_header_backgrounds") && (false !== dom_get("support_header_backgrounds")), ''
                            .   eol(1) . tab(4) .               'initRotatingHeaders();'          )
                            .   eol(1) . tab(4) .               'updateLazyImages();'             . if_then(!dom_has("ajax"), ''
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .               'scan_and_print = function()'
                            .   eol(1) . tab(4) .               '{'
                            .   eol(1) . tab(5) .                   '$("html").animate({ scrollTop: $(document).height() }, 1000, "swing", function() {'
                            .   eol(1) . tab(5) .                   '$("html").animate({ scrollTop: 0                    }, 1000, "swing", function() { window.print(); }); });'
                            .   eol(1) . tab(4) .               '};'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .               'function urlBase64ToUint8Array(base64String) { const padding = "=".repeat((4 - base64String.length % 4) % 4); const base64 = (base64String + padding).replace(/\-/g, "+").replace(/_/g, "/"); const rawData = window.atob(base64); return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0))); }'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .                if_then(dom_get("support_service_worker", false),''
                            .   eol(1) . tab(4) .               '{'
                            .   eol(1) . tab(5) .                   'if ("serviceWorker" in navigator) '
                            .   eol(1) . tab(5) .                   '{' 
                            .   eol(1) . tab(6) .                       'console.log("Service Worker is supported. Registering...");'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(6) .                       'navigator.serviceWorker.register("'.dom_path('sw.js').'").then(function(registration) '
                            .   eol(1) . tab(6) .                       '{'
                            .   eol(1) . tab(7) .                           'console.log("ServiceWorker registration successful with scope: ", registration.scope);'
                            .   eol(1) . tab(7) .                           ''
                            .   eol(1) . tab(7) .                           'var registration_installing = registration.installing;'
                            .   eol(1) . tab(7) .                           'var registration_waiting    = registration.waiting;'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           'if (registration_installing && registration_installing != null)'
                            .   eol(1) . tab(7) .                           '{'
                            .   eol(1) . tab(7) .                           '    console.log("Installing...");'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           '    if (registration_installing.state === "activated" && !registration_waiting)'
                            .   eol(1) . tab(7) .                           '    {'
                            .   eol(1) . tab(7) .                           '        console.log("Send Clients claim");'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           '        registration_installing.postMessage({type: "CLIENTS_CLAIM" });'
                            .   eol(1) . tab(7) .                           '    }'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           '    registration_installing.addEventListener("statechange", function()'
                            .   eol(1) . tab(7) .                           '    {'
                            .   eol(1) . tab(7) .                           '        if (registration_installing.state === "activated" && !registration_waiting) '
                            .   eol(1) . tab(7) .                           '        {'
                            .   eol(1) . tab(7) .                           '            console.log("Send Clients claim");'
                            .   eol(1) . tab(7) .                           '            '
                            .   eol(1) . tab(7) .                           '            registration_installing.postMessage({ type: "CLIENTS_CLAIM" });'
                            .   eol(1) . tab(7) .                           '        }'
                            .   eol(1) . tab(7) .                           '    });'
                            .   eol(1) . tab(7) .                           '}'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           'navigator.serviceWorker.ready.then(function(registration) '
                            .   eol(1) . tab(7) .                           '{'
                            .   eol(1) . tab(8) .                               'registration.pushManager.getSubscription().then(function(subscription) '
                            .   eol(1) . tab(8) .                               '{'
                            .   eol(1) . tab(9) .                                   'if (!(subscription === null)) '
                            .   eol(1) . tab(9) .                                   '{'
                            .   eol(1) . tab(10) .                                      'console.log("User IS subscribed.");'
                            .   eol(1) . tab(9) .                                   '}'
                            .   eol(1) . tab(9) .                                   'else '
                            .   eol(1) . tab(9) .                                   '{'
                            .   eol(1) . tab(10) .                                      'console.log("User is NOT subscribed.");'
                            .   eol(1) . tab(9) .                                   '}'
                            .   eol(1) . tab(8) .                               '})'. if_then(dom_has("push_public_key"), ''
                            .   eol(1) . tab(8) .                               '.then(function()'
                            .   eol(1) . tab(8) .                               '{'
                            .   eol(1) . tab(9) .                                   'const subscribeOptions = { userVisibleOnly: true, applicationServerKey: urlBase64ToUint8Array("'.dom_get("push_public_key").'") };'
                            .   eol(1) . tab(9) .                                   'return registration.pushManager.subscribe(subscribeOptions);'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(8) .                               '}'
                            .   eol(1) . tab(8) .                               ').then(function(pushSubscription)'
                            .   eol(1) . tab(8) .                               '{'
                            .   eol(1) . tab(9) .                                  'console.log("Received PushSubscription: ", JSON.stringify(pushSubscription));'
                            .   eol(1) . tab(9) .                                  'return pushSubscription;'
                            .   eol(1) . tab(8) .                               '})') // push_public_key
                            .   eol(1) . tab(8) .                               ';'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(8) .                               'return registration.sync.register("myFirstSync");'
                            .   eol(1) . tab(7) .                           '});'
                            .   eol(1) . tab(6) .                       '}, '
                            .   eol(1) . tab(6) .                       'function(err) '
                            .   eol(1) . tab(6) .                       '{'
                            .   eol(1) . tab(7) .                           'console.log("ServiceWorker registration failed: ", err);'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(6) .                       '}).catch(function(err)'
                            .   eol(1) . tab(6) .                       '{'
                            .   eol(1) . tab(7) .                           'console.log("Service Worker registration failed: ", err);'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(6) .                       '});'
                            .   eol(1) . tab(1)                                                                 /* TODO : REGISTER FOR NOTIFICATIONS ON USER GESTURE */
                            .   eol(1) . tab(6) .                       'if ("PushManager" in window) '
                            .   eol(1) . tab(6) .                       '{ '                                                                                                    /*
                            .   eol(1) . tab(7) .                           'console.log("Service Worker push notifications are supported. Registering...");'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           'new Promise(function(resolve, reject) '
                            .   eol(1) . tab(7) .                           '{'
                            .   eol(1) . tab(8) .                               'Notification.requestPermission().then(function(permission) '
                            .   eol(1) . tab(8) .                               '{'
                            .   eol(1) . tab(9) .                                   'console.log("Notifications permissions : " + permission);'
                            .   eol(1) . tab(9) .                                   'if (permission !== "granted") return reject(Error("Denied notification permission"));'
                            .   eol(1) . tab(9) .                                   'resolve();'
                            .   eol(1) . tab(8) .                               '});'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           '}).then(function() '
                            .   eol(1) . tab(7) .                           '{'
                            .   eol(1) . tab(8) .                               'return navigator.serviceWorker.ready;'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           '}).then(function(registration) '
                            .   eol(1) . tab(7) .                           '{'
                            .   eol(1) . tab(8) .                               'return registration.sync.register("syncTest");'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           '}).then(function() '
                            .   eol(1) . tab(7) .                           '{'
                            .   eol(1) . tab(8) .                               'console.log("Sync registered");'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(7) .                           '}).catch(function(err) '
                            .   eol(1) . tab(7) .                           '{'
                            .   eol(1) . tab(8) .                               'console.log("It broke");'
                            .   eol(1) . tab(8) .                               'console.log(err.message);'
                            .   eol(1) . tab(7) .                           '});'                                                       */
                            .   eol(1) . tab(6) .                       '}'         /**/
                            .   eol(1) . tab(5) .                   '}'
                            .   eol(1) . tab(5) .                   'else'
                            .   eol(1) . tab(5) .                   '{'
                            .   eol(1) . tab(6) .                       'console.log("Service worker not supported");'
                            .   eol(1) . tab(5) .                   '}'
                            .   eol(1) . tab(4) .               '}') /* support_service_worker */ ) /* !ajax */
                            .   eol(1) . tab(1)                 
                            .   eol(1) . tab(4) .               'let deferredPrompt = null;'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .               'console.log("Register Before Install Prompt callback");'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .               'window.addEventListener("beforeinstallprompt", function(e) '
                            .   eol(1) . tab(4) .               '{'
                            .   eol(1) . tab(5) .                   'console.log("Before Install Prompt");'
                            .   eol(1) . tab(5) .                   'e.preventDefault();'
                            .   eol(1) . tab(5) .                   'deferredPrompt = e;'
                            .   eol(1) . tab(5) .                   '$(".app-install").css({"display": "inline-block"});' /* TODO change this hardcoded style by a class */
                            .   eol(1) . tab(4) .               '});'
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .               '$(".app-install").on("click", function(e)'
                            .   eol(1) . tab(4) .               '{'
                            .   eol(1) . tab(5) .                   '$(".app-install").css({"display": "none"});' 
                            .   eol(1) . tab(5) .                   ''
                            .   eol(1) . tab(5) .                   'if (deferredPrompt != null)'
                            .   eol(1) . tab(5) .                   '{'
                            .   eol(1) . tab(6) .                       'deferredPrompt.prompt();'
                            .   eol(1) . tab(6) .                       ''
                            .   eol(1) . tab(6) .                       'deferredPrompt.userChoice.then(function(choiceResult)'
                            .   eol(1) . tab(6) .                       '{'
                            .   eol(1) . tab(7) .                           'if (choiceResult.outcome === "accepted") console.log("User accepted the A2HS prompt");'
                            .   eol(1) . tab(7) .                           'else                                     console.log("User dismissed the A2HS prompt");'
                            .   eol(1) . tab(7) .                           ''
                            .   eol(1) . tab(7) .                           'deferredPrompt = null;'
                            .   eol(1) . tab(6) .                       '});'
                            .   eol(1) . tab(5) .                   '}'
                            .   eol(1) . tab(5) .                   'else'
                            .   eol(1) . tab(5) .                   '{'
                            .   eol(1) . tab(6) .                       'console.log("Install promt callback not received yet");'
                            .   eol(1) . tab(5) .                   '}'
                            .   eol(1) . tab(4) .               '}); '
                            .   eol(1) . tab(1)      
                            .   eol(1) . tab(4) .                                                       'setTimeout(function() { setInterval(updateLazyImages, 500); },  50);'
                            .   eol(1) . tab(4) .                if_then(dom_get("support_sliders", false), 'setTimeout(function() { setInterval(updateSliders,    500); }, 100);')
                            .   eol(1) . tab(3) .           '});'
                            .   eol(1) . tab(1) .   '});'
                            .   eol(1)
                            );
    }
    
    #endregion
    #region API : DOM : HTML COMPONENTS : MARKUP : BODY
    ######################################################################################################################################

    function comment($text) { return (dom_has("rss")) ? '' : ('<!-- ' . $text . ' //-->'); }
    
    function tag($tag, $html, $attributes = false, $force_display = false, $self_closing = false, $extra_attributes_raw = false) { $space_pos = strpos($tag, ' '); return (dom_has('rss') && !$force_display) ? '' : (('<'.$tag.dom_attributes($attributes).(($extra_attributes_raw === false) ? '' : (' '.$extra_attributes_raw))) . (($self_closing) ? '/>' : ('>'.$html.'</'.(($space_pos === false) ? $tag : substr($tag, 0, $space_pos)).'>'))); }
    
    function body($html, $html_post_scripts = "", $dark_theme = null)
    {
        dom_debug_track_timing("start");
        
        $properties_organization = array
        (
            "@context"  => "https://schema.org", 
            "@type"     => "Organization",

            "url"       => dom_get('canonical'),
            "logo"      => dom_get('canonical').'/'.dom_get("image")
        );
        
        $properties_person_same_as = array();
        
        if (dom_has("facebook_page"))   $properties_person_same_as[] = url_facebook_page   (dom_get("facebook_page"));
        if (dom_has("instagram_user"))  $properties_person_same_as[] = url_instagram_user  (dom_get("instagram_user"));
        if (dom_has("tumblr_blog"))     $properties_person_same_as[] = url_tumblr_blog     (dom_get("tumblr_blog"));
        if (dom_has("pinterest_user"))  $properties_person_same_as[] = url_pinterest_board (dom_get("pinterest_user"), dom_get("pinterest_board"));
            
        $properties_person = array
        (
            "@context"  => "https://schema.org", 
            "@type"     => "Person",
            "name"      => dom_get("publisher"),
            "url"       => dom_get('canonical'),
            "sameAs"    => $properties_person_same_as
        );
        
        global $hook_amp_sidebars;

        $app_js = dom_path_coalesce("js/app.js","app.js");
        
        $body = ''

        . eol(2) . if_browser('lte IE 9', '<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>')
        . eol(2) . if_then(dom_get("support_metadata_person",       false), script_json_ld($properties_person))
        . eol(2) . if_then(dom_get("support_metadata_organization", false), script_json_ld($properties_organization))
        
        . eol(2) . $html
        . eol(2) . $hook_amp_sidebars

        . eol(2) . comment("DOM Javascript boilerplate")
        . eol(2) . scripts_body()
        . eol(2) . ($app_js ? script_src($app_js) : comment('Could not find any app.js default user script'))
        . eol(2) . $html_post_scripts
        . eol(2) . if_then(dom_AMP() && dom_get("support_service_worker", false), '<amp-install-serviceworker src="'.dom_path('sw.js').'" layout="nodisplay" data-iframe-src="'.dom_path("install-service-worker.html").'"></amp-install-serviceworker>')
        ;

        dom_debug_track_timing("end");
        
        if (is_null($dark_theme)) $dark_theme = dom_get("dark_theme", false);
        
        return cosmetic(eol(2)).tag('body', $body, dom_component_class('body').($dark_theme ? dom_component_class('dark') : ''));
    }
    
    function cosmetic($html)
    {
        return !!dom_get("minify") ? '' : (!!dom_get("beautify", false) ? $html : '');
    }
    
//  HTML tags
    
    function div            ($html = "", $attributes = false) {                             return    cosmetic(eol(1)).tag ('div',                        $html,                                                $attributes                                                         );                      }
    function p              ($html = "", $attributes = false) {                             return    cosmetic(eol(1)).tag ('p',                          $html,                                                $attributes                                                         );                      }
    function i              ($html = "", $attributes = false) {                             return                     tag ('i',                          $html,                                                $attributes                                                         );                      }
    function pre            ($html = "", $attributes = false) {                             return    cosmetic(eol(1)).tag ('pre',                        $html,                                                $attributes                                                         );                      }
    function ul             ($html = "", $attributes = false) {                             return    cosmetic(eol(1)).tag ('ul',                         $html.cosmetic(eol(1)),                               $attributes                                                         );                      }
    function li             ($html = "", $attributes = false) {                             return    cosmetic(eol(1)).tag ('li',                         $html,                                                $attributes                                                         );                      }

    function dom_table      ($html = "", $attributes = false) {                             return    cosmetic(eol(1)).tag ('table',                      $html.cosmetic(eol(1)),    dom_attributes_add_class(  $attributes, dom_component_class('table'))                              );                      }
    function tr             ($html = "", $attributes = false) {                             return    cosmetic(eol(1)).tag ('tr',                         $html,                                                $attributes                                                         );                      }
    function td             ($html = "", $attributes = false) {                             return                     tag ('td',                         $html,                                                $attributes                                                         );                      }
    function th             ($html = "", $attributes = false) {                             return                     tag ('th',                         $html,                                                $attributes                                                         );                      }

    function strong         ($html = "", $attributes = false) {                             return                     tag ('strong',                     $html,                                                $attributes                                                         );                      }
    function em             ($html = "", $attributes = false) {                             return                     tag ('em',                         $html,                                                $attributes                                                         );                      }
    function span           ($html = "", $attributes = false) {                             return                     tag ('span',                       $html,                                                $attributes                                                         );                      }
    function figure         ($html = "", $attributes = false) {                             return    cosmetic(eol(1)).tag ('figure',                     $html.cosmetic(eol(1)),                               $attributes                                                         );                      }
    function figcaption     ($html = "", $attributes = false) {                             return                     tag ('figcaption',                 $html,                                                $attributes                                                         );                      }

    function checkbox       ($id, $html = "", $attributes = false) {                        return                     tag('input',                       $html, array("class"    => ("$attributes " . dom_component_class('checkbox')),       "id"  => $id, "type" => "checkbox") );     }
    function checkbox_label ($id, $html = "", $attributes = false) {                        return                     tag('label',                       $html, array("class"    => ("$attributes " . dom_component_class('checkbox-label')), "for" => $id)      );                      }

    function button         ($html = "", $attributes = false) {                             return                     tag ('button',                     $html,                     dom_attributes_add_class(  $attributes, dom_component_class('button'))                             );                      }
    function button_label   ($html = "", $attributes = false) {                             return                     tag ('span',                       $html,                     dom_attributes_add_class(  $attributes, dom_component_class('button-label'))                       );                      }

    function h          ($h, $html = "", $attributes = false, $anchor = false)  { hook_headline($h, $html);
                                                                                            return  cosmetic(eol(1)).
                                                                                                    (($h>=2)?anchor(!!$anchor ? $anchor : $html):'').
                                                                                                                       tag ('h'.$h,                       $html,                     dom_attributes_add_class(  $attributes, dom_component_class('headline headline'.$h))           );                      }

    function h1             ($html = "", $attributes = false, $anchor = false) {            return                     h(1,                               $html,                                                $attributes, $anchor                                                );                      }
    function h2             ($html = "", $attributes = false, $anchor = false) {            return                     h(2,                               $html,                                                $attributes, $anchor                                                );                      }
    function h3             ($html = "", $attributes = false, $anchor = false) {            return                     h(3,                               $html,                                                $attributes, $anchor                                                );                      }
    function h4             ($html = "", $attributes = false, $anchor = false) {            return                     h(4,                               $html,                                                $attributes, $anchor                                                );                      }
    function h5             ($html = "", $attributes = false, $anchor = false) {            return                     h(5,                               $html,                                                $attributes, $anchor                                                );                      }
    function section        ($html = "", $attributes = false) {                             return    cosmetic(eol(1)).tag ('section',                    $html,                     dom_attributes_add_class(  $attributes, 'section')                                             );                      }
    function dom_header     ($html = "", $attributes = false) { dom_debug_track_timing();   return    cosmetic(eol(1)).tag ('header',                     $html.cosmetic(eol(1)),    dom_attributes_add_class(  $attributes, 'header')                                              ).cosmetic(eol(1));     }
                   
    function hr             (            $attributes = false) {                             return    cosmetic(eol(1)).tag ('hr',                         false,                                                $attributes, false, true                                            );                      }
    function br             (            $attributes = false) {                             return                     tag ('br',                         false,                                                $attributes, false, true                                            );                      }

    function clearfix       () { return div("","clearfix"); }

    function dom_main       ($html = "", $attributes = false) { return content($html, $attributes); }
    function content        ($html = "", $attributes = false) { dom_debug_track_timing();   return clearfix().cosmetic(eol(2)).tag ('main',     cosmetic(eol(1)).$html.cosmetic(eol(1)),    dom_attributes_add_class(   $attributes,    dom_component_class('main')                 .                           ' ' . 
                                                                                                                                                                                                                                        dom_component_class('content')              . (!!dom_get("toolbar") ? ( ' ' . 
                                                                                                                                                                                                                                        dom_component_class('main-below-toolbar')   ) : '')) ).cosmetic(eol(1)); }
    function footer         ($html = "", $attributes = false) { dom_debug_track_timing();   return clearfix().cosmetic(eol(2)).tag ('footer',   cosmetic(eol(1)).$html.cosmetic(eol(1)),    dom_attributes_add_class(   $attributes,    dom_component_class('footer')) ); }
    
    function icon           ($icon, $attributes = false) { return      i($icon,      dom_attributes_add_class($attributes, 'material-icons')); }
    function button_icon    ($icon, $label      = false) { return button(icon($icon, dom_component_class('action-button-icon')), array("class" => dom_component_class("action-button"), "aria-label" => (($label === false) ? $icon : $label))); }
    
    if (!function_exists("table")) { function table($html = "", $attributes = false) { return dom_table($html, $attributes); } }

    function div_aspect_ratio($html, $w = 1200, $h = 675) // 16:9
    {
        $class = false;

        if ((string)(int)$h == (string)$h)
        {
            $class = "aspect-ratio-16-9"; foreach (array(
                
                array(16,  9),
                array(16, 10),
                array( 4,  3),
                array( 3,  2),
                array( 1,  1)) 
                
                as $ratio) if (((int)$w/(int)$h)==($ratio[0]/$ratio[1]))  $class = "aspect-ratio-".$ratio[0]."-".$ratio[1]."";

            $class = ' class ="aspect-ratio '.$class.'"';
        }

        return '<div'.$class.'>'.$html.'</div>';
    }
        
	function iframe($url, $title, $classes, $w = false, $h = false)
	{   
    //  TODO. See https://benmarshall.me/responsive-iframes/ for frameworks integration   

        $w = ($w === false) ? "1200" : $w;
        $h = ($h === false) ?  "675" : $h;

        hook_amp_require("iframe");

        return div_aspect_ratio('<'.if_then(dom_AMP(), 'amp-iframe sandbox="allow-scripts"', 'iframe').' title="'.$title.'" class="'.$classes.'" src="'.$url.'" width="'.$w.'" height="'.$h.'" layout="responsive" frameborder="0" style="border:0;" allowfullscreen=""></'.if_then(dom_AMP(), 'amp-iframe', 'iframe').'>', $w, $h);
    }

	function google_calendar($id, $w = false, $h = false)
	{
        $src = $id;

        if (false === stripos($id, "http"))
        {
            $src = 'https://calendar.google.com/calendar/embed'
            
            .'?'    .'showTitle'		.'=0'
            .'&amp;'.'showPrint'		.'=0'
            .'&amp;'.'showCalendars'	.'=0'
            .'&amp;'.'showTz'			.'=0'
            .'&amp;'.'height'			.'='.$h.''
            .'&amp;'.'wkst'				.'=2'
            .'&amp;'.'bgcolor'			.'=%23FFFFFF'
            .'&amp;'.'src'				.'='.$id.'%40group.calendar.google.com'
            .'&amp;'.'color'			.'=%2307bdcb'
            .'&amp;'.'ctz'				.'=Europe%2FParis';
        }
        
        if (dom_AMP()) return a('https://calendar.google.com', $src, EXTERNAL_LINK);
        
		return iframe($src, "Google Calendar", "google-calendar", $w, $h).a('https://calendar.google.com', $src, EXTERNAL_LINK);
	}
        
	function google_map($embed_url, $w = false, $h = false)
	{
        return iframe($embed_url, "Google Map", "google-map", $w, $h);
    }
        
	function google_doc($id, $w = false, $h = false)
	{
        $src = $id;

        if (false === stripos($id, "http"))
        {
            $src = "https://docs.google.com/document/$id/pub?embedded=true";
        }

        return iframe($src, "Google Doc", "google-doc", $w, $h);
    }
       
	function google_video($id, $w = false, $h = false)
	{
        $w = ($w === false) ? "1200" : $w;
        $h = ($h === false) ?  "675" : $h;

        if (dom_AMP())
        {
            hook_amp_require("youtube");
            return '<amp-youtube data-videoid="'.$id.'" layout="responsive" width="'.$w.'" height="'.$h.'"></amp-youtube>';        
        }
        else
        {        
            $url = "https://www.youtube.com/embed/$id?wmode=opaque&amp;enablejsapi=1";

            return div_aspect_ratio('<'.if_then(dom_AMP(), 'amp-iframe sandbox="allow-scripts"', 'iframe').' title="Google Video" src="'.$url.'" height="'.$h.'" width="'.$w.'" layout="responsive" scrolling="no" frameborder="0" allowfullscreen=""></'.if_then(dom_AMP(), 'amp-iframe', 'iframe').'>', $w, $h);
        }
	}
        
    function json_google_photo_album_from_content($url)
    {
        $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36'));
        $context = stream_context_create($options);
        $html    = @file_get_contents($url, false, $context);

        if ($html)
        {          
            $tag_bgn = 'data:function(){return';
            $tag_end = '}});</script>';
            
            $pos_bgn = strpos($html, $tag_bgn, 0);
            $pos_end = strpos($html, $tag_end, $pos_bgn);
            
            if (false !== $pos_bgn && false !== $pos_end)
            {
                $json   = substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));
                $result = json_decode($json, true);
                return $result;
            }
        }
        
        return false;
    }
    
    function google_photo_album($url)
    {        
        if (dom_AMP()) return a($url, $url, EXTERNAL_LINK);

        $results = json_google_photo_album_from_content($url);
        $photos  = $results[1];
        
        $images = "";
        
        foreach ($photos as $i => $photo_result)
        {
            $photo_url = $photo_result[1][0];
            
            $images .= img($photo_url, array("onError" => "this.src='".url_img_blank()."';"), "Photo");
        }

        return a(div($images), $url, EXTERNAL_LINK);
    }
	
    // Components with BlogPosting microdata

    function article            ($html = "", $attributes = false) { return cosmetic(eol(1)).tag('article', $html, /*'itemscope="" itemtype="https://schema.org/BlogPosting" ' .*/ dom_attributes_add_class($attributes, "article")); }
    
    function span_author        ($html)             { return span ($html/*, array("itemprop" => "author", "itemscope" => "", "itemtype" => "https://schema.org/Person" )*/); }
    function span_name          ($html)             { return span ($html/*, array("itemprop" => "name"                                                                 )*/); }
    function span_datepublished ($date, $timestamp) { return span ($date/*, array("itemprop" => "datePublished", "datetime" => date("c",$timestamp)                    )*/); }
    function div_articlebody    ($html)             { return div  ($html/*, array("itemprop" => "articleBody"                                                          )*/); }
    
    // LINKS

    function href($link)
    {
        $extended_link = $link;
        
        if (dom_AMP()
        && false === stripos($extended_link,"?amp") 
        && false === stripos($extended_link,"&amp") 
        && 0     !== stripos($extended_link,"#"))
        {
            $extended_link = $extended_link . ((false === stripos($extended_link,"?")) ? "?" : "") . "&amp=1";
        }

        return $extended_link;
    }
  
    function dom_a($html, $url = false, $attributes = false, $target = false)
    {
        if ($url === false) $url = $html;

        if (($attributes === INTERNAL_LINK || $attributes === EXTERNAL_LINK) && $target === false) { $target = $attributes; $attributes = false; }
        if ($target === false) { $target = ((0 === stripos($url, "http")) || (0 === stripos($url, "//"))) ? EXTERNAL_LINK : INTERNAL_LINK; }
        
        $extended_link = href($url);

        $internal_attributes = array("href" => (($url === false) ? url_void() : $extended_link), "target" => $target);
        if ($target == EXTERNAL_LINK) $internal_attributes["rel"] = "noopener";
        
        return tag('a', $html, dom_attributes($internal_attributes) . dom_attributes_add_class($attributes, "a"));
    }

    function a_email($email, $text = false, $attributes = false)
    {
        $text = ($text === false) ? $email : $text;
        
        if (dom_AMP())
        {
            return a($text, "mailto:" . $email, $attributes, EXTERNAL_LINK);
        }
        else
        {
            $script  = "document.getElementById('".md5($text)."').setAttribute('href','mailto:".preg_replace("/\"/","\\\"",$email)."'); document.getElementById('".md5($text)."').innerHTML = '".$text."';";
            
            $crypted_script = ""; for ($i=0; $i < strlen($script); $i++) { $crypted_script = $crypted_script.'%'.bin2hex(substr($script, $i, 1)); }

            return a("", "", array("id" => md5($text)), EXTERNAL_LINK).script("eval(unescape('".$crypted_script."'))");
        }
    }

    function char_phone() { return "☎"; }
    function char_email() { return "✉"; }
  //function char_unsec() { return " "; }
    function char_unsec() { return "&nbsp;"; }
    
//  function nbsp($count = 1) { return str_repeat("&nbsp;",     $count); }
    function nbsp($count = 1) { return str_repeat(char_unsec(), $count); }
    
    function anchor_name($name, $tolower = true) { return to_classname($name, $tolower); }

    function anchor($name, $character = false, $tolower = true)
    {
        $id = anchor_name($name, $tolower);
        
        return a((false === $character) ? nbsp() : ((true === $character) ? '?' : $character), false, array("name" => $id, "id" => $id, "class" => "anchor"));
    }
    
    // GRID

    function grid ($html, $classes = false) { return div($html, dom_component_class("grid")     . (($classes === false) ? "" : (" " . (is_array($classes) ? implode(" ", $classes) : $classes)))); }
    function row  ($html, $classes = false) { return div($html, dom_component_class("grid-row") . (($classes === false) ? "" : (" " . (is_array($classes) ? implode(" ", $classes) : $classes)))); }

    function cell($html, $s = 4, $m = 4, $l = 4, $classes = false)
    {
        if ($html == "") return '';

        if ($s === false) $s = 12;
        if ($m === false) $m = $s;
        if ($l === false) $l = $m;

        return div($html, dom_component_class('grid-cell').' '.dom_component_class("grid-cell-$s-$m-$l").((false !== $classes) ? (' ' . $classes) : ''));
    }
    
    // HASHTAGS TRANSFORMS
    
    function add_hastag_links_facebook      ($text, $userdata = false) { return add_hastag_links($text, "url_facebook_search_by_tags",  $userdata); }
    function add_hastag_links_pinterest     ($text, $userdata = false) { return add_hastag_links($text, "url_pinterest_search_by_tags", $userdata); }
    function add_hastag_links_instagram     ($text, $userdata = false) { return add_hastag_links($text, "url_instagram_search_by_tags", $userdata); }
    function add_hastag_links_tumblr        ($text, $userdata = false) { return add_hastag_links($text, "url_tumblr_search_by_tags",    $userdata); }
    function add_hastag_links_flickr        ($text, $userdata = false) { return add_hastag_links($text, "url_flickr_search_by_tags",    $userdata); }
    function add_hastag_links_numerama      ($text, $userdata = false) { return $text; }
    function add_hastag_links_googlenews    ($text, $userdata = false) { return $text; }
    
    // VIDEOS
    
    function video($path, $attributes = false, $alt = false, $lazy = true)
    {
        if (is_array($path)) 
        {
            return wrap_each($path, "", "video", true, $attributes, $alt, $lazy);
        }

        if ($path === false) return '';

        if ((false !== stripos($path, "youtube"))
        ||  (false !== stripos($path, "youtu.be")))
        {
            $id  = $path;
            $sep =  stripos($id, "?v=");

            if (false === $sep)
            {
                $sep =  stripos($id, "?"); if ($sep !== false) $id = substr($id, 0,  $sep);
                $sep = strripos($id, "/"); if ($sep !== false) $id = substr($id, 1 + $sep);
            }
            else
            {
                $id = substr($id, $sep + 3);
            }

            return google_video($id);
        }

        $info     = explode('?', $path);
        $info     = $info[0];
        $info     = pathinfo($info);
        $ext      = array_key_exists('extension', $info) ? '.'.$info['extension'] : false;
        $codename = urlencode(basename($path, $ext));
        $alt      = ($alt === false) ? $codename : $alt;

        return tag
        (
            "video"
        ,   tag("source", '', dom_attributes(array("src" => $path, "type" => ("video/".str_replace(".","",$ext)))), false, true)
        ,   dom_attributes(array_merge(dom_AMP() ? array() : array("alt" => $alt), array("width" => "100%", "controls" => "no"))) . dom_attributes_add_class($attributes, "immediate")
        );
    }
    
    // IMAGES
    
    function picture($path_img, $path_sources = false, $attributes = false, $alt = false, $lazy = true, $lazy_src = false)
    {
        if (false === $path_sources) return img($path_img, $attributes, $alt, $lazy, $lazy_src);
        
        if (dom_AMP())
        {
            $path_sources = is_array($path_sources) ? $path_sources : array($path_sources);
            $path_source  = $path_sources[0];

            $img = img($path_img, $attributes, $alt, false, false);
            
            return source($path_source, $attributes, $alt, false, false, $img);
        }
        else
        {
            $sources = '';
            {
                $path_sources = is_array($path_sources) ? $path_sources : array($path_sources);
                
                foreach ($path_sources as $path_source)
                {
                    $sources .= source($path_source, $attributes, $alt, $lazy, $lazy_src);
                }
            }
            
            $img = img($path_img, $attributes, $alt, $lazy, $lazy_src);
            
            return tag('picture', $sources . $img);
        }
    }

    function source($path, $attributes = false, $alt = false, $lazy = true, $lazy_src = false, $content = '')
    {
        if (is_array($path))
        {
            return wrap_each($path, "", "source", true, $attributes, $alt, $lazy);
        }

        if ($path === false) return '';

        $info     = explode('?', $path);
        $info     = $info[0];
        $info     = pathinfo($info);
        $ext      = array_key_exists('extension', $info) ? '.'.$info['extension'] : false;
        $codename = urlencode(basename($path, $ext));
        $alt      = ($alt === false) ? $codename : $alt;
        $type     = substr($ext,1);
        
        $lazy_src = ($lazy_src === false) ? url_img_loading() : $lazy_src;

        $w = (is_array($attributes) && array_key_exists("width",  $attributes)) ? $attributes["width"]  : dom_get("default_image_width",  300);
        $h = (is_array($attributes) && array_key_exists("height", $attributes)) ? $attributes["height"] : dom_get("default_image_height", 200);

        if (!!dom_get("no_js")) $lazy = false;

        return ($lazy && !dom_AMP()) ? tag(dom_AMP() ? ('amp-img layout="responsive" width='.$w.' height='.$h.'') : 'source', $content, dom_attributes(array_merge(dom_AMP() ? array() : array("alt" => $alt), dom_AMP() ? array("srcset" => $lazy_src, "data-srcset" => $path) : array("type" => "image/$type", "srcset" => $lazy_src, "data-srcset" => $path))) . dom_attributes_add_class($attributes, "lazy"),         false, !dom_AMP() && $content == '')
                                     : tag(dom_AMP() ? ('amp-img layout="responsive" width='.$w.' height='.$h.'') : 'source', $content, dom_attributes(array_merge(dom_AMP() ? array() : array("alt" => $alt), dom_AMP() ? array("srcset"                             => $path) : array("type" => "image/$type", "srcset"                             => $path))) . dom_attributes_add_class($attributes, "immediate"),    false, !dom_AMP() && $content == '');
    }
    
    function img($path, $attributes = false, $alt = false, $lazy = true, $lazy_src = false, $content = '')
    {
        if (is_array($path)) 
        {
            return wrap_each($path, "", "img", true, $attributes, $alt, $lazy);
        }

        if ($path === false) return '';

        $info     = explode('?', $path);
        $info     = $info[0];
        $info     = pathinfo($info);
        $ext      = array_key_exists('extension', $info) ? '.'.$info['extension'] : false;
        $codename = urlencode(basename($path, $ext));
        $alt      = ($alt === false) ? $codename : $alt;
        
        $lazy_src = ($lazy_src === false) ? url_img_loading() : $lazy_src;

        $w = (is_array($attributes) && array_key_exists("width",  $attributes)) ? $attributes["width"]  : dom_get("default_image_width",  300);
        $h = (is_array($attributes) && array_key_exists("height", $attributes)) ? $attributes["height"] : dom_get("default_image_height", 200);

        if (!!dom_get("no_js")) $lazy = false;

        return ($lazy && !dom_AMP()) ? tag(dom_AMP() ? ('amp-img fallback layout="responsive" width='.$w.' height='.$h.'') : 'img', $content, dom_attributes(array_merge(dom_AMP() ? array() : array("alt" => $alt), array("src" => $lazy_src, "data-src" => $path))) . dom_attributes_add_class($attributes, "img img-responsive lazy loading"), false, !dom_AMP() && $content == '')
                                     : tag(dom_AMP() ? ('amp-img fallback layout="responsive" width='.$w.' height='.$h.'') : 'img', $content, dom_attributes(array_merge(dom_AMP() ? array() : array("alt" => $alt), array("src"                          => $path))) . dom_attributes_add_class($attributes, "img img-responsive immediate"),    false, !dom_AMP() && $content == '');
    }
    
    function img_svg($path, $attributes = false)
    {
        return img($path, $attributes ? $attributes : array("style" => "width: 100%; height: auto"));
    }

    function svg($paths, $w = 24, $h = 24, $label = "", $x0 = false, $x1 = false, $y0 = false, $y1 = false, $align = null) 
    {
        if ($align === null) $align = false;

        if ($x0 === false) $x0 = 0; if ($x1 === false) $x1 = $w; 
        if ($y0 === false) $y0 = 0; if ($y1 === false) $y1 = $h; 

        return tag('span', 
                    '<svg '. 'class="svg" '.
                              'role="img"'.(($label!="" && $label!=false)?(' '.
                        'aria-label="'.$label.'"'):('')).' '.
                             'style="width:'.$w.'px;height:'.$h.'px" '.
                           'viewBox="'.$x0.' '.$x1.' '.$y0.' '.$y1.'">'.$paths.'</svg>', 
                    array(
                        'class' => 'span-svg-wrapper span-svg-icon-container div-svg-icon-container',
                        'style' => 'display: inline-block;'.($align ? ' position: relative; bottom: -6px; padding-right: 6px;' : '').' height: '.$h.'px'
                        )
                    );
    }

    // https://materialdesignicons.com/
    
    function svg_flickr         ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_flickr          (); $colors = (is_array($color)) ? $color : array($color,$color); return svg('<path fill="'.$colors[1].'" d="M43,73.211c-23.71,0-43,19.29-43,43s19.29,43,43,43c23.71,0,43-19.29,43-43S66.71,73.211,43,73.211z"/><path fill="'.$colors[0].'" d="M189.422,73.211c-23.71,0-43,19.29-43,43s19.29,43,43,43c23.71,0,43-19.29,43-43S213.132,73.211,189.422,73.211z"/>',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      $w, $h, $label === null ? "Flickr"            : $label,   0,   0, 232.422, 232.422, $align); }
    function svg_facebook       ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_facebook        (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M5,3H19A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3M18,5H15.5A3.5,3.5 0 0,0 12,8.5V11H10V14H12V21H15V14H18V11H15V9A1,1 0 0,1 16,8H18V5Z" />',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     $w, $h, $label === null ? "Facebook"          : $label,   0,   0,  24,      24,     $align); }
    function svg_twitter        ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_twitter         (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M22.46,6C21.69,6.35 20.86,6.58 20,6.69C20.88,6.16 21.56,5.32 21.88,4.31C21.05,4.81 20.13,5.16 19.16,5.36C18.37,4.5 17.26,4 16,4C13.65,4 11.73,5.92 11.73,8.29C11.73,8.63 11.77,8.96 11.84,9.27C8.28,9.09 5.11,7.38 3,4.79C2.63,5.42 2.42,6.16 2.42,6.94C2.42,8.43 3.17,9.75 4.33,10.5C3.62,10.5 2.96,10.3 2.38,10C2.38,10 2.38,10 2.38,10.03C2.38,12.11 3.86,13.85 5.82,14.24C5.46,14.34 5.08,14.39 4.69,14.39C4.42,14.39 4.15,14.36 3.89,14.31C4.43,16 6,17.26 7.89,17.29C6.43,18.45 4.58,19.13 2.56,19.13C2.22,19.13 1.88,19.11 1.54,19.07C3.44,20.29 5.7,21 8.12,21C16,21 20.33,14.46 20.33,8.79C20.33,8.6 20.33,8.42 20.32,8.23C21.16,7.63 21.88,6.87 22.46,6Z" />',                                                                                                 $w, $h, $label === null ? "Twitter"           : $label,   0,   0,  24,      24,     $align); }
    function svg_linkedin       ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_linkedin        (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3H19M18.5,18.5V13.2A3.26,3.26 0 0,0 15.24,9.94C14.39,9.94 13.4,10.46 12.92,11.24V10.13H10.13V18.5H12.92V13.57C12.92,12.8 13.54,12.17 14.31,12.17A1.4,1.4 0 0,1 15.71,13.57V18.5H18.5M6.88,8.56A1.68,1.68 0 0,0 8.56,6.88C8.56,5.95 7.81,5.19 6.88,5.19A1.69,1.69 0 0,0 5.19,6.88C5.19,7.81 5.95,8.56 6.88,8.56M8.27,18.5V10.13H5.5V18.5H8.27Z" />',                                                                                                                                                                                                                                                                                                                                               $w, $h, $label === null ? "Linkedin"          : $label,   0,   0,  24,      24,     $align); }
    function svg_instagram      ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_instagram       (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M17.25,5.5A1.25,1.25 0 0,1 18.5,6.75A1.25,1.25 0 0,1 17.25,8A1.25,1.25 0 0,1 16,6.75A1.25,1.25 0 0,1 17.25,5.5M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z" />',                                                                                                                                                                                                                                                                                  $w, $h, $label === null ? "Instagram"         : $label,   0,   0,  24,      24,     $align); }
    function svg_pinterest      ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_pinterest       (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M13,16.2C12.2,16.2 11.43,15.86 10.88,15.28L9.93,18.5L9.86,18.69L9.83,18.67C9.64,19 9.29,19.2 8.9,19.2C8.29,19.2 7.8,18.71 7.8,18.1C7.8,18.05 7.81,18 7.81,17.95H7.8L7.85,17.77L9.7,12.21C9.7,12.21 9.5,11.59 9.5,10.73C9.5,9 10.42,8.5 11.16,8.5C11.91,8.5 12.58,8.76 12.58,9.81C12.58,11.15 11.69,11.84 11.69,12.81C11.69,13.55 12.29,14.16 13.03,14.16C15.37,14.16 16.2,12.4 16.2,10.75C16.2,8.57 14.32,6.8 12,6.8C9.68,6.8 7.8,8.57 7.8,10.75C7.8,11.42 8,12.09 8.34,12.68C8.43,12.84 8.5,13 8.5,13.2A1,1 0 0,1 7.5,14.2C7.13,14.2 6.79,14 6.62,13.7C6.08,12.81 5.8,11.79 5.8,10.75C5.8,7.47 8.58,4.8 12,4.8C15.42,4.8 18.2,7.47 18.2,10.75C18.2,13.37 16.57,16.2 13,16.2M20,2H4C2.89,2 2,2.89 2,4V20A2,2 0 0,0 4,22H20A2,2 0 0,0 22,20V4C22,2.89 21.1,2 20,2Z" />',  $w, $h, $label === null ? "Pinterest"         : $label,   0,   0,  24,      24,     $align); }
    function svg_tumblr         ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_tumblr          (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M16,11H13V14.9C13,15.63 13.14,16 14.1,16H16V19C16,19 14.97,19.1 13.9,19.1C11.25,19.1 10,17.5 10,15.7V11H8V8.2C10.41,8 10.62,6.16 10.8,5H13V8H16M20,2H4C2.89,2 2,2.89 2,4V20A2,2 0 0,0 4,22H20A2,2 0 0,0 22,20V4C22,2.89 21.1,2 20,2Z" />',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               $w, $h, $label === null ? "Tumblr"            : $label,   0,   0,  24,      24,     $align); }
    function svg_rss            ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_rss             (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M6.18,15.64A2.18,2.18 0 0,1 8.36,17.82C8.36,19 7.38,20 6.18,20C5,20 4,19 4,17.82A2.18,2.18 0 0,1 6.18,15.64M4,4.44A15.56,15.56 0 0,1 19.56,20H16.73A12.73,12.73 0 0,0 4,7.27V4.44M4,10.1A9.9,9.9 0 0,1 13.9,20H11.07A7.07,7.07 0 0,0 4,12.93V10.1Z" />',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 $w, $h, $label === null ? "RSS"               : $label,   0,   0,  24,      24,     $align); }
    function svg_printer        ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_printer         (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M18,3H6V7H18M19,12A1,1 0 0,1 18,11A1,1 0 0,1 19,10A1,1 0 0,1 20,11A1,1 0 0,1 19,12M16,19H8V14H16M19,8H5A3,3 0 0,0 2,11V17H6V21H18V17H22V11A3,3 0 0,0 19,8Z" />',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         $w, $h, $label === null ? "Printer"           : $label,   0,   0,  24,      24,     $align); }
    function svg_notifications  ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_printer         (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M14,20A2,2 0 0,1 12,22A2,2 0 0,1 10,20H14M12,2A1,1 0 0,1 13,3V4.08C15.84,4.56 18,7.03 18,10V16L21,19H3L6,16V10C6,7.03 8.16,4.56 11,4.08V3A1,1 0 0,1 12,2Z" />',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          $w, $h, $label === null ? "Notifications"     : $label,   0,   0,  24,      24,     $align); }
    function svg_messenger		($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_messenger       (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M12,2C6.5,2 2,6.14 2,11.25C2,14.13 3.42,16.7 5.65,18.4L5.71,22L9.16,20.12L9.13,20.11C10.04,20.36 11,20.5 12,20.5C17.5,20.5 22,16.36 22,11.25C22,6.14 17.5,2 12,2M13.03,14.41L10.54,11.78L5.5,14.41L10.88,8.78L13.46,11.25L18.31,8.78L13.03,14.41Z" />',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  $w, $h, $label === null ? "Messenger"         : $label,   0,   0,  24,      24,     $align); }
    function svg_alert          ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_alert           (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M13,13H11V7H13M13,17H11V15H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          $w, $h, $label === null ? "Alert"             : $label,   0,   0,  24,      24,     $align); }
    function svg_amp            ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_amp             (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M171.887 116.28l-53.696 89.36h-9.728l9.617-58.227-30.2.047c-2.684 0-4.855-2.172-4.855-4.855 0-1.152 1.07-3.102 1.07-3.102l53.52-89.254 9.9.043-9.86 58.317 30.413-.043c2.684 0 4.855 2.172 4.855 4.855 0 1.088-.427 2.044-1.033 2.854l.004.004zM128 0C57.306 0 0 57.3 0 128s57.306 128 128 128 128-57.306 128-128S198.7 0 128 0z" />',                                                                                                                                                                                                                                                                                                                                                                                                                                   $w, $h, $label === null ? "AMP"               : $label, -22, -22, 300,     300,     $align); }
    
    function svg_loading        ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_amp             (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50"><animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 50 50" to="360 50 50" repeatCount="indefinite" /></path>',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                $w, $h, $label === null ? "Loading"           : $label,   0,   0,  24,      24,     $align); }

    function svg_dark_and_light ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_dark_and_light  (); $colors = (is_array($color)) ? $color : array($color);        return svg('<path fill="'.$color.    '" d="M289.203,0C129.736,0,0,129.736,0,289.203C0,448.67,129.736,578.405,289.203,578.405 c159.467,0,289.202-129.735,289.202-289.202C578.405,129.736,448.67,0,289.203,0z M28.56,289.202 C28.56,145.48,145.481,28.56,289.203,28.56l0,0v521.286l0,0C145.485,549.846,28.56,432.925,28.56,289.202z"/>',                                                                                                                                                                                                                                                                                                                                                                                                                                                                              $w, $h, $label === null ? "DarkAndLight"      : $label, -12, -12, 640,     640,     $align); }
    
    function svg_leboncoin      ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_leboncoin       (); $colors = (is_array($color)) ? $color : array($color);        return svg('<g transform="translate(0.000000,151.000000) scale(0.100000,-0.100000)" fill="'.$color.'" stroke="none"><path d="M174 1484 c-59 -21 -123 -80 -150 -138 l-24 -51 0 -555 c0 -516 2 -558 19 -595 25 -56 67 -102 112 -125 37 -19 62 -20 624 -20 557 0 588 1 623 19 49 25 86 66 111 121 20 44 21 63 21 600 l0 555 -24 51 c-28 60 -91 117 -154 138 -66 23 -1095 22 -1158 0z m867 -244 c145 -83 270 -158 277 -167 9 -13 12 -95 12 -329 0 -172 -3 -319 -6 -328 -8 -20 -542 -326 -569 -326 -11 0 -142 70 -291 155 -203 116 -273 161 -278 177 -10 38 -7 632 4 648 15 24 532 318 561 319 17 1 123 -54 290 -149z"/><path d="M530 1187 c-118 -67 -213 -126 -213 -132 1 -5 100 -67 220 -137 l218 -126 65 36 c36 20 139 78 228 127 89 50 161 92 162 95 0 8 -439 260 -453 260 -6 -1 -109 -56 -227 -123z"/><path d="M260 721 l0 -269 228 -131 227 -130 3 266 c1 147 -1 270 -5 274 -11 10 -441 259 -447 259 -4 0 -6 -121 -6 -269z"/><path d="M1018 859 l-228 -130 0 -270 c0 -148 3 -269 7 -269 3 0 107 57 230 126 l223 126 0 274 c0 151 -1 274 -2 273 -2 0 -105 -59 -230 -130z"/></g>', $w, $h, $label === null ? "Leboncoin" : $label, 0, 0, 151.0, 151.0, $align); }
    function svg_seloger        ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_seloger         (); $colors = (is_array($color)) ? $color : array($color);        return svg('<g transform="translate(0.000000,152.000000) scale(0.100000,-0.100000)" fill="'.$color.'" stroke="none"><path d="M0 760 l0 -760 760 0 760 0 0 760 0 760 -760 0 -760 0 0 -760z m1020 387 c0 -7 -22 -139 -50 -293 -27 -153 -50 -291 -50 -306 0 -39 25 -48 135 -48 l97 0 -7 -57 c-4 -31 -9 -62 -12 -70 -8 -21 -50 -28 -173 -28 -92 0 -122 4 -152 19 -54 26 -81 76 -81 145 1 51 98 624 109 643 3 4 45 8 95 8 66 0 89 -3 89 -13z m-364 -58 c91 -17 93 -18 81 -86 -5 -32 -12 -62 -16 -66 -4 -4 -60 -3 -125 3 -85 8 -126 8 -150 0 -33 -10 -50 -38 -40 -63 2 -7 55 -46 117 -87 131 -88 157 -120 157 -195 0 -129 -86 -217 -239 -245 -62 -11 -113 -9 -245 12 l-68 10 7 61 c3 34 9 65 11 69 3 4 69 5 148 2 97 -5 148 -3 163 4 24 13 38 56 25 78 -5 9 -57 48 -117 87 -60 40 -117 84 -128 99 -33 44 -34 125 -4 191 31 69 88 112 172 130 41 9 193 7 251 -4z m664 -28 c44 -23 80 -84 80 -135 0 -52 -40 -119 -84 -140 -26 -12 -64 -16 -157 -16 l-123 0 36 38 c31 32 35 40 26 62 -14 37 -4 113 20 147 43 61 134 81 202 44z"/></g>',                                                    $w, $h, $label === null ? "Seloger"   : $label, 0, 0, 152.0, 152.0, $align); }

    function svg_numerama       ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_numerama        (); $colors = (is_array($color)) ? $color : array($color);        return svg('<g transform="translate(0.000000,80.000000) scale(0.100000,-0.100000)">'.'<path fill="'.$color.    '" d="M0 505 l0 -275 75 0 75 0 0 200 0 200 140 0 140 0 0 -200 0 -200 80 0 80 0 0 275 0 275 -295 0 -295 0 0 -275z"/><path fill="'.$color.'" d="M210 285 l0 -275 295 0 295 0 0 275 0 275 -75 0 -75 0 0 -200 0 -200 -140 0 -140 0 0 200 0 200 -80 0 -80 0 0 -275z"/></g>',                                                                                                                                                                                                                                                                                                                                                                                                                              $w, $h, $label === null ? "Numerama"   : $label, 0, 0,  80.0,    80.0,   $align); }
    function svg_googlenews     ($w = 24, $h = 24, $color = false, $align = null, $label = null) { if ($color === false) $color = color_google          (); $colors = (is_array($color)) ? $color : array($color);        return svg('<defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"/><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"/><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"/><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"/>',                                                                                                                                                                                                   $w, $h, $label === null ? "Googlenews" : $label, 0, 0,  48,      48,     $align); }

    function img_instagram      ($short_code = false, $size_code = "m")     { return img(url_img_instagram  ($short_code, $size_code),  "img-instagram" ); }
    function img_pinterest      ($pin        = false, $size_code = false)   { return img(url_img_pinterest  ($pin),                     "img-pinterest" ); }
    function img_facebook       ($username   = false, $size_code = false)   { return img(url_img_facebook   ($username),                "img-facebook"  ); }
    function img_tumblr         ($blogname   = false, $size_code = false)   { return img(url_img_tumblr     ($blogname),                "img-tumblr"    ); }
    
    function img_loading        ($attributes = false, $size_code = false)   { return img(url_img_loading(), $attributes); }    
//  function img_loading        ($attributes = false, $size_code = false)   { return svg_loading(); }    

    // IMAGES URLs
 
    function url_img_loading () { return dom_path("loading.svg");   }
    function url_img_blank   () { return dom_path("img/blank.gif"); }
 
    function url_img_instagram($short_code, $size_code = "l") { return "https://instagram.com/p/$short_code/media/?size=$size_code";      }
//  function url_img_instagram($username = false, $index = 0) { $content = json_instagram_medias(($username === false) ? dom_get("instagram_user") : $username); $n = count($content["items"]); if ($n == 0) return url_img_blank(); return $content["items"][$index % $n]["images"]["standard_resolution"]["url"]; }

    function url_img_flickr_cdn($photo_farm, $photo_server, $photo_id, $photo_secret, $photo_size = "b")
    {
        return "https://farm".$photo_farm.".staticflickr.com/".$photo_server."/".$photo_id    ."_".$photo_secret."_".$photo_size.".jpg";
    }
    
    function url_img_flickr($photo_key = 0, $photo_size = "b", $username = false, $token = false)
    {
        $photoset_key = false;
        
        if (is_array($photo_key))
        {
            $photoset_key = $photo_key[0];
            $photo_key    = $photo_key[1];
        }
    
        $photos         = array();
        $photo          = false;
        $photo_id       = false;
        $photo_secret   = false;
        $photo_server   = false;
        $photo_farm     = false;
        $photo_title    = false;
        
        if (false !== $photoset_key)
        {
            $data           = json_flickr("photosets.getList", array(), $username, $token);
            $photosets      = dom_at(dom_at($data,"photosets"),"photoset");
            $photoset       = false;
            $photoset_id    = false;
            $photoset_title = false;
            
            foreach ($photosets as $photoset_index => $photoset_nth)
            { 
                $photoset       =               $photoset_nth;
                $photoset_id    =        dom_at($photoset_nth, "id");
                $photoset_title = dom_at(dom_at($photoset_nth, "title"), "_content");

                if (is_string($photoset_key)) { if ($photoset_title ==       $photoset_key) break; }
                else                          { if ($photoset_index === (int)$photoset_key) break; }
            }
            
            $data           = json_flickr("photosets.getInfo", array("photoset_id" => $photoset_id), $username, $token);
            $photoset_farm  = dom_at(dom_at($data,"photoset"),"farm");
            
            $data           = json_flickr("photosets.getPhotos", array("photoset_id" => $photoset_id, "media" => "photo"), $username, $token);
            $photos         = dom_at(dom_at($data,"photoset"),"photo");
            $photo_farm     = $photoset_farm;
        }
        else
        {
            $data   = json_flickr("people.getPhotos", array(), $username, $token);
            $photos = dom_at(dom_at($data,"photos"),"photo");
        }
        
        foreach ($photos as $photo_index => $photo_nth)
        { 
            $photo          =        $photo_nth;
            $photo_id       = dom_at($photo_nth, "id",      $photo_id);
            $photo_secret   = dom_at($photo_nth, "secret",  $photo_secret);
            $photo_server   = dom_at($photo_nth, "server",  $photo_server);
            $photo_farm     = dom_at($photo_nth, "farm",    $photo_farm);
            $photo_title    = dom_at($photo_nth, "title",   $photo_title);

            if (is_string($photo_key)) { if ($photo_title ==       $photo_key) break; }
            else                       { if ($photo_index === (int)$photo_key) break; }
        }
        
    //  $data   = json_flickr("photos.getInfo", array("photo_id" => $photo_id), $username, $token);
    //  $url    = dom_at(dom_at(dom_at(dom_at($data,"photo"),"urls"),"url"),"_content");        
        $url    = "https://farm".$photo_farm.".staticflickr.com/".$photo_server."/".$photo_id    ."_".$photo_secret."_".$photo_size.".jpg";
                //"https://farm"."3"        .".staticflickr.com/"."2936"       ."/"."13992107912"."_"."a2c5d9fe3b" ."_"."k"        .".jpg"
        
        return $url;
    }

    function url_img_pinterest ($pin      = false) { return dom_at(dom_at(dom_at(dom_at(                            json_pinterest_pin ( $pin),                                                                    "data"),"image"),"original"),"url",                                  url_img_blank()); }
    function url_img_facebook  ($username = false) { return dom_at(dom_at(                                          json_facebook      (($username === false) ? dom_get("facebook_page") : $username, "cover", false), "cover"),"source",                                                   url_img_blank()); }
    function url_img_tumblr    ($blogname = false) { return dom_at(dom_at(dom_at(dom_at(dom_at(dom_at(dom_at(dom_at(json_tumblr_blog   (($blogname === false) ? dom_get("tumblr_blog")   : $blogname, "posts"),        "response"),"posts"),0),"trail"),0),"blog"),"theme"),"header_image", url_img_blank()); }

    // CARDS

    function dom_clean_social_media_text($text)
    {
        $text = str_replace("<br/>\n",      "<br>",     $text);
        $text = str_replace("<br>\n",       "<br>",     $text);
        $text = str_replace("<br/>",        "<br>",     $text);
        $text = str_replace("\r\n",         "<br>",     $text);
        $text = str_replace("\r",           "<br>",     $text);
        $text = str_replace("\n",           "<br>",     $text);
        $text = str_replace("<br>-<br>",    "<br><br>", $text);
        
        $text = transform_lines($text, "---");
        $text = transform_lines($text, "___");
        
        $text = str_replace("<hr><br>",     "<hr>",     $text);
        $text = str_replace("<hr>>",        "<hr> >",   $text);
        $text = str_replace("<br>>",        "<br> >",   $text);
        $text = str_replace("=>",           "→",        $text);
        $text = str_replace(">>",           "→",        $text);

        return $text;
    }

    function card_title($title = false)
    {
        $title_main         =       dom_at($title, "title",           dom_at($title, 0, $title)           );
        $title_sub          =       dom_at($title, "subtitle",        dom_at($title, 1, false)            );
        $title_icon         =       dom_at($title, "icon",            dom_at($title, 2, false)            );
        $title_link         =       dom_at($title, "link",            dom_at($title, 3, false)            );
        $title_main_link    =       dom_at($title, "link_main",       dom_at($title, 3, $title_link)      );
        $title_sub_link     =       dom_at($title, "link_subtitle",   dom_at($title, 4, $title_main_link) );
        $title_icon_link    =       dom_at($title, "link_icon",       dom_at($title, 5, $title_sub_link)  );
        $title_level        = (int) dom_at($title, "level",           dom_at($title, 6, 1)                );

        hook_heading($title_main);
        
        $title = "";
        
        if ($title_icon !== false) $title  = img(            $title_icon, array("class" => dom_component_class('card-title-icon'), "style" => "border-radius: 50%; max-width: 2.5rem; position: absolute;"), $title_main);
        if ($title_link !== false) $title  = a($title,       $title_link,                  dom_component_class('card-title-link'), EXTERNAL_LINK);
        if ($title_main !== false) $title .= h($title_level, $title_main, array("class" => dom_component_class('card-title-main'), "style" => "margin-left: ".(($title_icon !== false) ? 56 : 0)."px"/*,  "itemprop" => "headline name"*/));
        if ($title_sub  !== false) $title .= p(              $title_sub,  array("class" => dom_component_class('card-title-sub'),  "style" => "margin-left: ".(($title_icon !== false) ? 56 : 0)."px"));

        return (($title !== "") ? /*section*/dom_header($title, dom_component_class("card-title")) : "");
    }

    function card_media($media = false)
    {
        return (($media !== false) ? section($media, dom_component_class("card-media")) : "");
    }

    function card_text($text = false)
    {
        if ($text !== false)
        {
            $text = dom_clean_social_media_text($text);
        }
        
        return (($text !== false) ? section($text, dom_component_class("card-text")) : "");
    }

    function card_actions($button = false)
    {
             if ($button !== false) $button = button($button, dom_component_class('card-action-button'));
        return (($button !== false)        ? section($button, dom_component_class("card-actions")) : "");
    }
  
    function card($html, $attributes = false, $horizontal = false)
    {   
        return article($html, dom_attributes_add_class($attributes, dom_component_class("card") . ($horizontal ? dom_component_class("card-horizontal") : ''))). cosmetic(eol());
    }

/*  function card_builder($media = false, $title = false, $text = false, $button = false, $attributes = false, $horizontal = false)
    {   
        return card(
            card_title  ($title).
            card_media  ($media).
            card_text   ($text).
            card_actions($button), $attributes, $horizontal);
    }
*/
    function card_from_metadata($metadata, $attributes = false)
    {
    //  CARD INFO FROM METADATA
    
        $source   = dom_at($metadata, "TYPE",       "instagram");
        $lazy     = dom_at($metadata, "LAZY",       false);
        $userdata = dom_at($metadata, "userdata",   false);
        
        $short_label = extract_start(dom_at($metadata, "post_title"), 8, array("\n","!","?",".",array("#",1),","," "));
        
        $data = array();
        
        $data["content"] = dom_has($metadata, "post_embed") ? $metadata["post_embed"] : '';
        
        if (dom_has($metadata, "post_img_url"))
        {
            if (is_array(dom_at($metadata, "post_img_url")))
            {
                $images = "";
                
                foreach (dom_at($metadata, "post_img_url") as $post_img_url)
                {
                    $images .= div(img($post_img_url, false, $short_label, $lazy));
                }
                
                $data["content"] = div($images, "slider");
            }
            else
            {
                if (false === $metadata["post_img_url"]) $metadata["post_img_url"] = dom_at($metadata, "user_img_url");
                if (false === $metadata["post_img_url"]) $metadata["post_img_url"] = url_img_blank();
                
                     if (false !== stripos($metadata["post_img_url"], ".mp4"))      $data["content"] = video($metadata["post_img_url"], false, $short_label, false);
                else if (false !== stripos($metadata["post_img_url"], "<iframe"))   $data["content"] = $metadata["post_img_url"];
                else                                                                $data["content"] = img($metadata["post_img_url"], false, $short_label, $lazy);
            }
        }
    
        $data["content"]        = (dom_has($metadata, "post_url") && $data["content"] != "")    ?   a($data["content"], $metadata["post_url"], false, EXTERNAL_LINK)                                  : $data["content"];
        $data["content"]        =  dom_has($metadata, "post_figcaption")                        ? cat($data["content"], wrap_each($metadata["post_figcaption"], eol(), "div")) : $data["content"];

        $data["title_main"]     = dom_at($metadata, "post_title");
        $data["title_img_src"]  = dom_at($metadata, "user_img_url");
        $data["title_link"]     = dom_at($metadata, "user_url");  
        
        if ("" === $data["title_main"]) $data["title_main"] = dom_get("title");

        $data["title_sub"]      =  dom_has($metadata, "post_timestamp") ? span_datepublished(date("d/m/y", dom_at($metadata, "post_timestamp")),           dom_at($metadata, "post_timestamp")  ) 
                                : (dom_has($metadata, "post_date")      ? span_datepublished(              dom_at($metadata, "post_date", ''  ), strtotime(dom_at($metadata, "post_date"))      ) : '');
        
        $data["title_sub"]      = dom_has($metadata, "user_name")       ? cat($data["title_sub"],' ',span_author(span_name($metadata["user_name"]))) : $data["title_sub"];
        $data["title_sub"]      = dom_has($metadata, "user_url")        ?   a($data["title_sub"], $metadata["user_url"], false, EXTERNAL_LINK)                              : $data["title_sub"];
        
        $data["title_sub"]      = ($data["title_sub"] != "") ? cat((is_callable("svg_$source") ? call_user_func("svg_$source") : ''), $data["title_sub"]) : false;
        
        $data["desc"]           = dom_has($metadata, "post_text") ? div_articlebody((is_callable("add_hastag_links_$source") ? call_user_func("add_hastag_links_$source", dom_at($metadata, "post_text"), $userdata) : '')) : false;

        if (               false !== dom_at($metadata,"post_url",false)
        &&  false !== strpos($data["desc"], $metadata["post_url"])
        &&  false === strpos($data["desc"], 'href="'.$metadata["post_url"])
        &&  false === strpos($data["desc"], "href='".$metadata["post_url"])
        &&  false === strpos($data["desc"], $metadata["post_url"]."</a>"))
        {
            $data["desc"] = str_replace($metadata["post_url"], a($metadata["post_url"], $metadata["post_url"]), $data["desc"]);
        }

        if (!!dom_get("debug"))
        {
            $data["desc"] .= pre(raw_array_debug(dom_has($metadata, "DEBUG_SOURCE") ? dom_at($metadata, "DEBUG_SOURCE") : $metadata));
        }
        
    //  JSON-LD INFO FROM METADATA
        
        $properties = false;
        
        if (dom_at($metadata, "post_title"))
        {
        //  $anchor_name  =      urlencode(dom_at($metadata, "post_title"));        
            $date_yyymmdd = date("Y-m-d", dom_has($metadata, "post_timestamp") ? dom_at($metadata, "post_timestamp") : strtotime(dom_at($metadata, "post_date", date("Y/m/d", time()))));

            $properties = array
            (
                "@context"      => "https://schema.org", 
                "@type"         => "BlogPosting",

                "url"           => dom_get('canonical')/* . '#'.$anchor_name*/,
                "description"   => dom_get("title", "")     . " $source post",
                "datePublished" => $date_yyymmdd,
                "dateCreated"   => $date_yyymmdd,
                "dateModified"  => $date_yyymmdd
            );

            if (dom_get("genre")                    !== false) $properties["genre"]         = dom_get("genre", "Website");
            if (dom_get("publisher")                !== false) $properties["publisher"]     = array("@type" => "Organization","name" => dom_get("publisher", DOM_AUTHOR), "logo" => array("@type" => "ImageObject", "url"=> dom_get("canonical").'/'.dom_get("image")));
            
            if (dom_at($metadata, "post_text")      !== false) $properties["keywords"]      = implode(' ', array_hashtags(          dom_at($metadata, "post_text")));
            if (dom_at($metadata, "post_text")      !== false) $properties["articleBody"]   =                                       dom_at($metadata, "post_text");
            if (dom_at($metadata, "post_img_url")   !== false) $properties["image"]         =                                       dom_at($metadata, "post_img_url"); // TODO MULTIPLE IMAGES
            if (dom_at($metadata, "post_title")     !== false) $properties["headline"]      =                                substr(dom_at($metadata, "post_title"), 0, 110);
            if (dom_at($metadata, "user_name")      !== false) $properties["author"]        = array("@type" => "Person","name" =>   dom_at($metadata, "user_name")); else $properties["author"] = "unknown";
        }
        
    //  RETURN CARD + JSON-LD

        return card(

            card_title(

                (   dom_at($data, "title_main")     === false 
                &&  dom_at($data, "title_sub")      === false
                &&  dom_at($data, "title_img_src")  === false
                &&  dom_at($data, "title_link")     === false) ? false : array(
                    
                    dom_at($data, "title_main"),        // title
                    dom_at($data, "title_sub"),         // subtitle
                    dom_at($data, "title_img_src"),     // icon
                    dom_at($data, "title_link"),        // link/link_main
                    false,                              // link_subtitle
                    false,                              // link_icon
                    1/*dom_get_card_headline()*/        // level
                    
                    )
                ).

            card_media  (dom_at($data,"content")).
            card_text   (dom_at($data, "desc")).

            card_actions(false),
            
            $attributes
            ).

            if_then($properties !== false && dom_get("jsonld",true), script_json_ld($properties));
    }

    function img_from_metadata($metadata, $attributes = false)
    {
    //  IMG INFO FROM METADATA
    
        $lazy        = dom_at($metadata, "LAZY", false);        
        $short_label = extract_start(dom_at($metadata, "post_title"), 8, array("\n","!","?",".",array("#",1),","," "));
        
        return img($metadata["post_img_url"], $attributes, $short_label, $lazy);
    }

    function card_      ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self")                          { return wrap_each(array_card   ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), eol(2), $container);                 }        
    function imgs       ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self")                          { return wrap_each(array_imgs   ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), eol(2), $container, true);           }    
    function cards      ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self", $s = 4, $m = 4, $l = 4)  { return wrap_each(array_cards  ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), eol(2), $container, true, $s,$m,$l); }    
    function cells_card ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false)                                               { return wrap_each(array_cards  ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), eol(2), "cell",     true, $s,$m,$l); }    

    // PROGRESS-BAR
    
    function progressbar($caption = "")
    {
        return figure
        (
            div
            (
                div("", dom_component_class("progressbar-buffer-dots"))
            .   div("", dom_component_class("progressbar-buffer"))

            .   div(span("", dom_component_class("progressbar-bar-inner")), dom_component_class("progressbar-primary-bar"))
            .   div(span("", dom_component_class("progressbar-bar-inner")), dom_component_class("progressbar-secondary-bar"))

            ,   array("role" => "progressbar", "class" => dom_component_class("progressbar"))
            )

        .   figcaption($caption)
        );
    }
    
    // ICONS
    
    function icon_entry($icon, $label = "", $link = "JAVASCRIPT_VOID", $id = false, $target = false)
    {
        $link = ("JAVASCRIPT_VOID" == $link) ? url_void() : $link;
        
        if (($id === INTERNAL_LINK || $id === EXTERNAL_LINK) && $target === false) { $target = $id; $id = false; }
        if ($target === false) { $target = INTERNAL_LINK; }
        
        return array($icon, $label, $link, $id, $target);
    }

    function icon_entries($icon_entries, $default_target = INTERNAL_LINK)
    {
        if (is_array($icon_entries))
        {
            $icons = "";
            
            foreach ($icon_entries as $icon_entry)
            {
                $icon   = dom_get($icon_entry, "icon",   dom_get($icon_entry, 0, ""));
                $label  = dom_get($icon_entry, "label",  dom_get($icon_entry, 1, ""));
                $link   = dom_get($icon_entry, "link",   dom_get($icon_entry, 2, false));
                $id     = dom_get($icon_entry, "id",     dom_get($icon_entry, 3, false));
                $target = dom_get($icon_entry, "target", dom_get($icon_entry, 4, $default_target));
                
                $attributes = array_merge(array("class" => dom_component_class("toolbar-icon"), "aria-label" => $label), dom_AMP() ? array() : array("alt" => $label));
                
                if (false !== $id) $attributes = array_merge($attributes, array("id" => $id)); 
    
                $icons .= eol() . a($icon, $link, $attributes, $target);
            }
            
            return $icons;
        }
        else if (is_string($icon_entries))
        {
            return $icon_entries;
        }
    }
    
    // MENU

    define("DOM_MENU_ID","menu");
    
    function menu_entry($text = "", $link = false)
    {
        return ($link === false) ? $text : array($text, $link);
    }
  
    function ul_menu($menu_entries = array(), $default_target = INTERNAL_LINK)
    {
        $menu_lis = "";
        {
            if (!is_array($menu_entries)) $menu_entries = array($menu_entries);

            if (false != $menu_entries) foreach ($menu_entries as $menu_entry)
            {
                if ($menu_entry == array() || $menu_entry == "")
                {
                    $menu_lis .= cosmetic(eol()) . li("", array("class" => dom_component_class("list-item-separator"), "role" => "separator"));
                }
                else
                {    
                //  if (!is_array($menu_entry)) $menu_entry = array($menu_entry, url_void());
                    if (!is_array($menu_entry)) $menu_entry = array($menu_entry, "#".anchor_name($menu_entry));
                            
                    $item       = dom_get($menu_entry, "item",   dom_get($menu_entry, 0, ""));
                    $link       = dom_get($menu_entry, "link",   dom_get($menu_entry, 1, false));
                    $target     = dom_get($menu_entry, "target", dom_get($menu_entry, 2, $default_target));
                    $attributes = false;
                    
                    $menu_lis .= eol() . li(a(span($item), $link, $attributes, $target), array("class" => dom_component_class("list-item"), "role" => "menuitem", "tabindex" => "0"));
                }
            }
        }

             if (dom_AMP())                             { hook_amp_sidebar(cosmetic(eol(1)).tag('amp-sidebar id="'.DOM_MENU_ID.'" layout="nodisplay"', ul($menu_lis, array("class" => dom_component_class('menu-list'). " " . dom_component_class('menu'), "role" => "menu", "aria-hidden" => "true")))); return span("","placeholder-amp-sidebar"); }
        else if (dom_get("framework") == "bootstrap")   { return                                                                                      div($menu_lis, array("class" => dom_component_class('menu-list'), "role" => "menu", "aria-hidden" => "true", "aria-labelledby" => "navbarDropdownMenuLink"));  }
        else                                            { return                                                                                       ul($menu_lis, array("class" => dom_component_class('menu-list'), "role" => "menu", "aria-hidden" => "true"));                    }
    }

    function menu_switch() { return if_then(dom_get("framework") == "material",  a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons mdc-top-app-bar__icon--menu", "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle")                                                                )))
                                .   if_then(dom_get("framework") == "bootstrap", a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons",                             "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  ))) 
                                .   if_then(dom_get("framework") == "spectre",   a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons",                             "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  ))) 
                                .   if_then(dom_get("framework") == "NONE",      a(span("☰", "menu-switch-symbol menu-toggle-content")   
                                                                               . a(span("✕", "menu-close-symbol  menu-close-content"), "#menu-close",  array("class" => "menu-switch-link nav-link material-icons", "aria-label" => "Menu Toggle"))
                                                                                                                                     , "#menu-open",   array("class" => "menu-switch-link nav-link material-icons",                             "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle")                                                                )))
                            //  .   if_then(dom_get("framework") == "NONE",   checkbox("menu-button", "", "menu-switch-symbol menu-toggle-content" ,    array("class" => "menu-switch-link nav-link material-icons",                             "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  )).checkbox_label("menu-button", "☰"))
                                                            ; 
    }

    // TOOLBAR

    function toolbar_row    ($html,  $attributes = false) {                      return div     (   $html,  dom_attributes_add_class($attributes, dom_component_class("toolbar-row") ." ".dom_component_class("row"))   ); }
    function toolbar_section($html,  $attributes = false) {                      return section (   $html,  dom_attributes_add_class($attributes, dom_component_class("toolbar-cell")." ".dom_component_class("cell"))  ); }
/*  function toolbar_title  ($title, $attributes = false) { hook_title($title);  return div      (a('.',if_then(false === stripos($title,"<h1"), h1($title),$title)),
                                                                                                            dom_attributes_add_class($attributes, dom_component_class("toolbar-title"))                             ); }
*/
    function toolbar_banner_sections_builder($icon_entries = false)
    {
        return  ((true)                    ? (toolbar_section("")                                                                 ) : '')
            .   ((true)                    ? (toolbar_section("")                                                                 ) : '')
            .   (($icon_entries !== false) ? (toolbar_section(icon_entries($icon_entries), dom_component_class("toolbar-cell-right")) ) : '')
            ;
    }
/*
    function toolbar_nav_sections_builder($title = false, $menu_entries = false, $toolbar = false, $menu_entries_shrink_to_fit = false, $default_target = INTERNAL_LINK)
    {
        return  toolbar_section(($menu_entries === false) ? '' : menu_toggle($menu_entries, $default_target),        dom_component_class("toolbar-cell-left")  . ($menu_entries_shrink_to_fit ? (' '.dom_component_class("toolbar-cell-right-shrink")) : ""))
            .   toolbar_section(($title        === false) ? '' : toolbar_title($title),                              dom_component_class("toolbar-cell-center")                                                                                         )
            .   toolbar_section(($toolbar      === false) ? '' : $toolbar,    array("role" => "toolbar", "class" => (dom_component_class("toolbar-cell-right") .                                 ' '.dom_component_class("toolbar-cell-right-shrink"))     ))
            ;
    }
*/
    function toolbar_banner($icon_entries = false)
    {
        hook_toolbar("banner");

        return toolbar_row(toolbar_banner_sections_builder($icon_entries), "toolbar-row-banner");
    }

/*  function toolbar_nav_builder($title = false, $menu_entries = false, $toolbar = false, $menu_entries_shrink_to_fit = false)
    {
        hook_toolbar("nav");

        return toolbar_row(toolbar_nav_sections_builder($title, $menu_entries, $toolbar, $menu_entries_shrink_to_fit, INTERNAL_LINK, DOM_MENU_ID),           array("id" => "toolbar-row-nav",        "class" => "toolbar-row-nav"))         . if_then(dom_AMP(), ''
             . toolbar_row(toolbar_nav_sections_builder($title, $menu_entries, $toolbar, $menu_entries_shrink_to_fit, INTERNAL_LINK, DOM_MENU_ID."-static"), array("id" => "toolbar-row-nav-static", "class" => "toolbar-row-nav static"))  );    
    }
*/
    
/*
    function menu_entries_from_array   ($menu_entries = array(), $default_target = INTERNAL_LINK)   {                               $ul_menu = ul_menu($menu_entries, $default_target); return if_then(dom_get("framework") != "bootstrap", div($ul_menu, dom_component_class("menu")), $ul_menu); }
    function menu_toggle_from_array    ($menu_entries = array(), $default_target = INTERNAL_LINK)   { return div(menu_switch().menu_entries_from_array($menu_entries, $default_target), array("id" => "menu-open", "class" => dom_component_class("menu-toggle"))); }
*/
    function menu_entries($html)
    {
        $html = if_then(false === stripos($html, "menu-list"), ul_menu($html), $html);

        return if_then(dom_get("framework") != "bootstrap", div($html, "menu-entries " . dom_component_class("menu")), $html);
    }
    
    function menu_toggle($html)
    {
        $html = if_then(false === stripos($html, "menu-entries"), menu_entries($html), $html);
        $html = if_then(false === stripos($html, "menu-switch"),  menu_switch().$html, $html);

        return div($html, array("id" => "menu-open", "class" => dom_component_class("menu-toggle")));
    }

    function toolbar_nav_toolbar($html)
    {
        return toolbar_section(($html === false) ? '' : $html,  array(
            
            "role"  => "toolbar",
            "class" => (dom_component_class("toolbar-cell-right") . ' ' . 
                        dom_component_class("toolbar-cell-right-shrink"))));
    }
   
    function  menu_toggle_auto() { return delayed_component("_".__FUNCTION__, false); }
    function _menu_toggle_auto() { return menu_toggle(ul_menu(get("hook_sections"))); }

    function toolbar_nav_menu($html = false, $attributes = false, $menu_entries_shrink_to_fit = false)
    {
        $html = if_then(false !== $html && false === stripos($html, "menu-toggle"), menu_toggle($html), $html);
        $html = if_then(false === $html,                                            menu_toggle_auto(), $html);
        
        return toolbar_section(($html === false) ? '' : $html,  dom_component_class("toolbar-cell-left") . ($menu_entries_shrink_to_fit ? (' '.
                                                                dom_component_class("toolbar-cell-right-shrink")    ) : ""));
    }

    function toolbar_nav_title($html, $attributes = false)
    {
        hook_title($html);

        $html = if_then(false === stripos($html,"<h1"),   h1($html),                    $html);
        $html = if_then(false === stripos($html,"<a"),     a($html,'.'),                $html);
        $html = if_then(false === stripos($html,"<div"), div($html, "toolbar-title"),   $html);
        
        return toolbar_section(($html === false) ? '' : $html, dom_component_class("toolbar-cell-center"));
    }

    function toolbar_nav($html, $attributes = false)
    {
        hook_toolbar("nav");

        $html = if_then(false === stripos($html,"toolbar-cell"), toolbar_nav_menu().toolbar_nav_title($html), $html);
        
        $menu_id_amp = DOM_MENU_ID."-static";
        
        $html_amp = $html;
        $html_amp = str_replace(DOM_MENU_ID.'.toogle',  $menu_id_amp.'.toogle',  $html_amp);
        $html_amp = str_replace('id="'.DOM_MENU_ID.'"', 'id="'.$menu_id_amp.'"', $html_amp);

        return toolbar_row($html,     array("id" => "toolbar-row-nav",        "class" => "toolbar-row-nav"))         . if_then(dom_AMP(), ''
             . toolbar_row($html_amp, array("id" => "toolbar-row-nav-static", "class" => "toolbar-row-nav static"))  );    
    }

    function toolbar($html, $attributes = false)
    {
        $html = if_then(false === stripos($html,"toolbar-row"), toolbar_banner().toolbar_nav($html), $html);
        
        $amp_observer = "";
        $amp_anim     = "";
        
        if (dom_AMP())
        {            
            hook_amp_require("animation");
            hook_amp_require("position-observer");

            $amp_anim     = '<amp-animation id="toolbarStaticShow" layout="nodisplay"><script type="application/json">{ "duration": "0", "fill": "forwards", "animations": [ { "selector": "#toolbar-row-nav-static", "keyframes": { "visibility": "visible" } } ] }</script></amp-animation>'
                          . '<amp-animation id="toolbarStaticHide" layout="nodisplay"><script type="application/json">{ "duration": "0", "fill": "forwards", "animations": [ { "selector": "#toolbar-row-nav-static", "keyframes": { "visibility": "hidden"  } } ] }</script></amp-animation>';

            $amp_observer = '<amp-position-observer target="toolbar-row-nav" intersection-ratios="1" on="enter:toolbarStaticHide.start;exit:toolbarStaticShow.start" layout="nodisplay"></amp-position-observer>';
        }

        return $amp_anim . dom_header($html . $amp_observer, dom_attributes_add_class($attributes, dom_component_class('toolbar')));
    }
    
    #endregion
    #region API : DOM : HTML COMPONENTS : ASYNC
    ######################################################################################################################################
    
    $call_asyncs_started = false;

    function call_asyncs_start()
    {
        global $call_asyncs_started;
        $call_asyncs_started = true;
    }
    
    function header_backgrounds_async()
    {
        if (dom_get("ajax") != "header-backgrounds") return "";
        
        if (dom_has("support_header_backgrounds") && (false !== dom_get("support_header_backgrounds")))
        {
            if (is_string("support_header_backgrounds"))
            {
                foreach (explode(',', dom_get("support_header_backgrounds")) as $i => $url)
                {
                    echo (($i>0)?", ":"").'"'.$url.'"';
                }
            }
            else foreach (array_instagram_thumbs(dom_get("instagram_user")) as $i => $thumb)
            {
                echo (($i>0)?", ":"").'"'.$thumb["post_img_url"].'"';
            }
        }
    }

    /**
     * User async functions Registration System
     */

    $__asyncs = array();

    function dom_register_async($f)      { global $__asyncs; $__asyncs[$f] = true; }
    function dom_registered_asyncs()     { global $__asyncs; return array_keys($__asyncs); }
    
    function dom_async($f)
    {
        $args = func_get_args();
        return dom_async_FUNC_ARGS($f, $args);
    }
    
    function dom_async_FUNC_ARGS($f, $args)
    {
        $period = -1;

        if (is_numeric($f))
        {
            $period = $f;
            array_shift($args);
            $f = $args[0];
        }
 
        array_shift($args);
        
        dom_register_async($f);
        
        return dom_ajax_call_with_args($f, $period, $args);
    }
    
    /**
     * Register all low-level asynchronous components here
     */
    function call_asyncs()
    {   
        $html = header_backgrounds_async    ()
        
            .   img_instagram_async         ()
            .   img_pinterest_async         ()
            .   img_facebook_async          ()
            .   img_tumblr_async            ()
            
            .   card_async                  ()
            .   imgs_async                  ()
            .   cards_async                 ()
            
            .   google_calendar_async       ()
            .   google_photo_album_async    ()
        ;

        foreach (dom_registered_asyncs() as $registered_async)
        {
            $html .= dom_ajax_call($registered_async);
        }

        return $html;
    }
    
    /**
     * Asynchronous components
     */
     
    function img_instagram_async                              ($ids = false, $args = "m")                                                            { return dom_ajax_call("img_instagram", $ids, $args); }
    function img_pinterest_async                              ($ids = false, $args = false)                                                          { return dom_ajax_call("img_pinterest", $ids, $args); }
    function img_facebook_async                               ($ids = false, $args = false)                                                          { return dom_ajax_call("img_facebook",  $ids, $args); }
    function img_tumblr_async                                 ($ids = false, $args = false)                                                          { return dom_ajax_call("img_tumblr",    $ids, $args); }
    
    function card_async       ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self") { return dom_ajax_call("card_", $source, $type, $ids, $filter, $tags_in, $tags_out, $container); }
    function imgs_async       ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self") { return dom_ajax_call("imgs",  $source, $type, $ids, $filter, $tags_in, $tags_out, $container); }
    function cards_async      ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self") { return dom_ajax_call("cards", $source, $type, $ids, $filter, $tags_in, $tags_out, $container); }
    function cells_img_async  ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false)                      { return dom_ajax_call("imgs",  $source, $type, $ids, $filter, $tags_in, $tags_out, "cell");     }
    function cells_card_async ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false)                      { return dom_ajax_call("cards", $source, $type, $ids, $filter, $tags_in, $tags_out, "cell");     }
    
    function google_calendar_async                            ($ids = false, $w = false, $h = false)                                                 { return dom_ajax_call("google_calendar",    $ids, $w, $h); }
    function google_photo_album_async                         ($ids = false)                                                                         { return dom_ajax_call("google_photo_album", $ids); }
       

    #endregion
    #region API : DOM : RSS
    ######################################################################################################################################

    function rss_sanitize($html) { return trim(htmlspecialchars($html, ENT_QUOTES, 'utf-8')); }
    
    function rss_item_from_item_info($item_info)
    {
        if (!is_array($item_info)) $item_info = explode(',', $item_info);
        
        if (!is_array(dom_at($item_info,"img_url",false))) $item_info["img_url"] = array(dom_at($item_info,"img_url"));
        
        $rss =  
                    rss_title       (dom_at($item_info,"title",dom_get("title")))
        . eol() .   rss_link        (dom_get("canonical"))
        . eol() .   rss_description (dom_at($item_info,"description",""))
        . eol() .   rss_pubDate     (dom_at($item_info,"timestamp",0));
        
        foreach ($item_info["img_url"] as $img_url)
        {       
            if (!!$img_url)
            {
                $rss .= eol() . raw('<enclosure url="'     .$img_url  .'" type="image/'.((false !== stripos($img_url, '.jpg'))?'jpg':'png').'" length="262144" />')
                     .  eol() . raw('<media:content url="' .$img_url  .'" medium="image" />');
            }
        }
        
        $rss .= eol() . raw('<source url="'.dom_get("canonical")."/?rss".'">RSS</source>')
        //   .  eol() . raw('<guid isPermaLink="true">https://web.cyanide-studio.com/rss/bb2/xml/?&amp;limit_matches=50&amp;limit_leagues=50&amp;days_leagues=7&amp;days_matches=1&amp;id=3518</guid>')
        ;

        return rss_item($rss);
    }
 
    function rss_channel        ($html = "")                        { return cosmetic(eol()).tag('channel',                  $html,  false,         true); }
    function rss_image          ($html = "")                        { return                 tag('image',                    $html,  false,         true); }
    function rss_url            ($html = "")                        { return                 tag('url',                      $html,  false,         true); }
    function rss_item           ($html = "")                        { return                 tag('item',                     $html,  false,         true); }
    function rss_link           ($html = "")                        { return                 tag('link',                     $html,  false,         true); }
    function rss_title          ($html = "")                        { return                 tag('title',       rss_sanitize($html), false,         true); }
    function rss_description    ($html = "", $attributes = false)   { return                 tag('description', rss_sanitize($html), $attributes,   true); }

    function rss_lastbuilddate  ($date = false)                     { return                 tag('lastBuildDate', (false === $date) ? date(DATE_RSS) : date(DATE_RSS, $date), false, true); }
    function rss_pubDate        ($date = false)                     { return                 tag('pubDate',       (false === $date) ? date(DATE_RSS) : date(DATE_RSS, $date), false, true); }

    function rss_copyright      ($author = false)                   { return                 tag('copyright', "Copyright " . ((false === $author) ? dom_get("author", DOM_AUTHOR) : $author), false, true); }
    
    #endregion
    #region API : DOM : TILE
    ######################################################################################################################################

    function tile_sanitize($html) { return trim(htmlspecialchars($html, ENT_QUOTES, 'utf-8')); }
    
    function tile_item_from_item_info($item_info)
    {
        $images = "";
        {        
            if (!is_array($item_info["img_url"])) $item_info["img_url"] = array($item_info["img_url"]);
            
            for ($i = 0; $i < count($item_info["img_url"]); ++$i)
            {
                $images .= tile_image($item_info["img_url"][$i], $i+1);
            }
        }
        
        $tile  = '<tile><visual lang="en-US" version="2">';
        $tile .= tile_binding($images. eol() . tile_text($item_info["description"]), 'Tile'.'Square'.'150x150'.'PeekImageAndText'.'02');
        $tile .= tile_binding($images. eol() . tile_text($item_info["description"]), 'Tile'.'Wide'.  '310x150'.'PeekImageAndText'.'01');                      
        $tile .= '</visual></tile>';
        
        return $tile;
    }
    
    function tile_binding   ($html, $template)      { return tag('binding', eol().$html.eol(), array("template" => $template), true); }
    function tile_image     ($src,      $id = 1)    { return raw('<image id="'.$id.'" src="'.tile_sanitize($src).'"/>'); }
    function tile_text      ($txt = "", $id = 1)    { return raw('<text id="'.$id.'">'.tile_sanitize($txt).'</text>'); }
    
    #endregion
    #region HELPERS - COLOR RATIOS
    ######################################################################################################################################
    
    // (c) https://github.com/gdkraus/wcag2-color-contrast

    // calculates the luminosity of an given RGB color
    // the color code must be in the format of RRGGBB
    // the luminosity equations are from the WCAG 2 requirements
    // http://www.w3.org/TR/WCAG20/#relativeluminancedef

    function dom_calculate_luminosity($color) {

        $color = ltrim($color,"#");

        $r = hexdec(substr($color, 0, 2)) / 255;
        $g = hexdec(substr($color, 2, 2)) / 255;
        $b = hexdec(substr($color, 4, 2)) / 255;

        if ($r <= 0.03928) { $r = $r / 12.92; } else { $r = pow((($r + 0.055) / 1.055), 2.4); }
        if ($g <= 0.03928) { $g = $g / 12.92; } else { $g = pow((($g + 0.055) / 1.055), 2.4); }
        if ($b <= 0.03928) { $b = $b / 12.92; } else { $b = pow((($b + 0.055) / 1.055), 2.4); }

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    // calculates the luminosity ratio of two colors
    // the luminosity ratio equations are from the WCAG 2 requirements
    // http://www.w3.org/TR/WCAG20/#contrast-ratiodef

    function dom_calculate_luminosity_ratio($color1, $color2) {

        $l1 = dom_calculate_luminosity($color1);
        $l2 = dom_calculate_luminosity($color2);

        return ($l1 > $l2) ? (($l1 + 0.05) / ($l2 + 0.05)) : (($l2 + 0.05) / ($l1 + 0.05));
    }

    // returns an array with the results of the color contrast analysis
    // it returns a key for each level (AA and AAA, both for normal and large or bold text)
    // it also returns the calculated contrast ratio
    // the ratio levels are from the WCAG 2 requirements
    // http://www.w3.org/TR/WCAG20/#visual-audio-contrast (1.4.3)
    // http://www.w3.org/TR/WCAG20/#larger-scaledef

    define("DOM_COLOR_CONTRAST_AA_MEDIUMBOLD",  3.0);
    define("DOM_COLOR_CONTRAST_AA_LARGE",       3.0);
    define("DOM_COLOR_CONTRAST_AA_NORMAL",      4.5);
    define("DOM_COLOR_CONTRAST_AAA_MEDIUMBOLD", 4.5);
    define("DOM_COLOR_CONTRAST_AAA_LARGE",      4.5);
    define("DOM_COLOR_CONTRAST_AAA_NORMAL",     7.0);

    function dom_correct_color($color, $background = "#FFFFFF", $contrast_ratio_target = DOM_COLOR_CONTRAST_AAA_NORMAL)
    {
        $color = ltrim($color, "#");

        $r = hexdec(substr($color, 0, 2)) / 255;
        $g = hexdec(substr($color, 2, 2)) / 255;
        $b = hexdec(substr($color, 4, 2)) / 255;

        $delta = 0.01;
        
        $intensity_color        = dom_calculate_luminosity($color);
        $intensity_background   = dom_calculate_luminosity($background);

        if ($intensity_background > $intensity_color)
        {
            $delta = - $delta;
        }

        while ($r < 1 || $g < 1 || $b < 1)
        {
            $rrggbb = str_pad(dechex($r),2,"0",STR_PAD_LEFT).
                      str_pad(dechex($g),2,"0",STR_PAD_LEFT).
                      str_pad(dechex($b),2,"0",STR_PAD_LEFT);

            $ratio = dom_calculate_luminosity_ratio($background, $rrggbb);
            if ($ratio >= $contrast_ratio_target) break;

            $r = min(1, $delta + $r);
            $g = min(1, $delta + $r);
            $b = min(1, $delta + $r);
        }

        $rrggbb = str_pad(dechex($r),2,"0",STR_PAD_LEFT).
                  str_pad(dechex($g),2,"0",STR_PAD_LEFT).
                  str_pad(dechex($b),2,"0",STR_PAD_LEFT);

        return "#".$rrggbb;
    }

    #endregion

    ######################################################################################################################################
    #endregion

?>