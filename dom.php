<?php

    #region DOM PUBLIC API
    ######################################################################################################################################
    
    # Forward shortname function (that mimic html markup) to private API functions (if names are not used)

    #region CONSTANTS
    ######################################################################################################################################

    if (!defined("DOM_INTERNAL_LINK"))      define("DOM_INTERNAL_LINK",     "_self");
    if (!defined("DOM_EXTERNAL_LINK"))      define("DOM_EXTERNAL_LINK",     "_blank");
    if (!defined("DOM_MENU_ID"))            define("DOM_MENU_ID",           "menu");
    
    #endregion
    #region CONFIG
    ######################################################################################################################################

    if (!function_exists("at"))                         { function at($a, $k, $d = false)                                                                                                   { return dom_at($a, $k, $d);                    } }
    if (!function_exists("get_all"))                    { function get_all($get = true, $post = true, $session = false)                                                                     { return dom_get_all($get, $post, $session);    } }
    if (!function_exists("has"))                        { function has($k_or_a, $__or_k = false)                                                                                            { return dom_has($k_or_a, $__or_k);             } }
    if (!function_exists("get"))                        { function get($k_or_a, $d_or_k = false, $__or_d = false)                                                                           { return dom_get($k_or_a, $d_or_k, $__or_d);    } }
    if (!function_exists("del"))                        { function del($k)                                                                                                                  { return dom_del($k);                           } }
    if (!function_exists("set"))                        { function set($k, $v = true, $aname = false)                                                                                       { return dom_set($k, $v, $aname);               } }

    if (!function_exists("is_localhost"))               { function is_localhost()                                                                                                           { return dom_is_localhost(); } }
    if (!function_exists("AMP"))                        { function AMP()                                                                                                                    { return dom_AMP(); } }

    #endregion
    #region LOCALIZATION
    ######################################################################################################################################

    if (!function_exists("T"))                          { function T($label, $default = false, $lang = false)                                                                                   { return dom_T($label, $default = false, $lang = false); } }

    #endregion
    #region STRINGS MANIPULATION
    ######################################################################################################################################

    if (!function_exists("tab"))                        { function tab($n = 1)                                                                                                                  { return dom_tab($n); } }
    if (!function_exists("eol"))                        { function eol($n = 1)                                                                                                                  { return dom_eol($n); } }

    #endregion
    #region HTML MARKUP & COMPONENTS
    ######################################################################################################################################

    if (!function_exists("tag"))                        { function tag($tag, $html, $attributes = false, $force_display = false, $self_closing = false, $extra_attributes_raw = false)          { return dom_tag($tag, $html, $attributes, $force_display, $self_closing, $extra_attributes_raw); } }

    if (!function_exists("a"))                          { function a($html, $url = false, $attributes = false, $target = false)                                                                 { return dom_a($html, $url, $attributes, $target); } }
    if (!function_exists("footer"))                     { function footer($html = "", $attributes = false)                                                                                      { return dom_footer($html, $attributes); } }

    if (!function_exists("style"))                      { function style( $filename_or_code = "",                                            $force_minify = false, $silent_errors = DOM_AUTO)  { return dom_style( $filename_or_code,                $force_minify, $silent_errors); } }
    if (!function_exists("script"))                     { function script($filename_or_code = "", $type = "text/javascript", $force = false, $force_minify = false, $silent_errors = DOM_AUTO)  { return dom_script($filename_or_code, $type, $force, $force_minify, $silent_errors); } }

    ######################################################################################################################################
    #endregion

    ######################################################################################################################################
    #endregion DOM PUBLIC API

    ######################################################################################################################################
    ######################################################################################################################################
    ######################################################################################################################################
    ######################################################################################################################################

    #region PRIVATE API
    ######################################################################################################################################
    
    #region CONSTANTS
    ######################################################################################################################################
    
    define("DOM_AUTHOR",    "Antoine Villepreux");
    define("DOM_VERSION",   "0.6.8");
    define("DOM_AUTO",      "__DOM_AUTO__");    // ? migrate to null as auto param ?

    #endregion
    #region HELPERS : CONFIG
    ######################################################################################################################################

    function dom_at($a, $k, $d = false)                                 { if (is_array($k)) { foreach ($k as $k0) { if (!is_array($a) || !array_key_exists($k0,$a)) return $d; $a = dom_at($a, $k0, $d); } return $a; } else { return (is_array($a) && array_key_exists($k,$a)) ? $a[$k] : $d; } }
    function dom_get_all($get = true, $post = true, $session = false)   { $a = array(); if ($get) $a = array_merge($a, $_GET); if ($post) $a = array_merge($a, $_POST); if ($session && isset($_SESSION) && is_array($_SESSION)) { $a = array_merge($a, $_SESSION); } return $a; }
    function dom_has($k_or_a, $__or_k = false)                          { return (is_array($k_or_a)) ? @array_key_exists($__or_k, $k_or_a) : @array_key_exists($k_or_a, dom_get_all()); }
    function dom_get($k_or_a, $d_or_k = false, $__or_d = false)         { return (is_array($k_or_a)) ? dom_at($k_or_a, $d_or_k, $__or_d) : dom_at(dom_get_all(), $k_or_a, $d_or_k); }
    function dom_del($k)                                                { if (dom_has($_GET,$k)) unset($_GET[$k]); if (dom_has($_POST,$k)) unset($_POST[$k]); if (isset($_SESSION) && dom_has($_SESSION,$k)) unset($_SESSION[$k]); }
    function dom_set($k, $v = true, $aname = false)                     { if ($aname === false)  { $_GET[$k] = $v; } else if ($aname === "POST") { $_POST[$k] = $v; } else if ($aname === "SESSION" && isset($_SESSION)) { $_SESSION[$k] = $v; } return $v; }

    #endregion
    #region HELPERS : SERVER ARGS
    ######################################################################################################################################
    
    if (!function_exists('getallheaders'))
    {
        function getallheaders()
        {
            $headers = array();

            foreach ($_SERVER as $name => $value)
            {
                if (substr($name, 0, 5) == 'HTTP_')
                {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }
    }

    function dom_server_headers                     ()  { return array_change_key_case(getallheaders(), CASE_LOWER); }

    function dom_header_do_not_track                ()  { return 1 == dom_at(dom_server_headers(), 'dnt',      0); }
    function dom_header_global_privacy_control      ()  { return 1 == dom_at(dom_server_headers(), 'sec-gpc',  0); }

    function dom_server_http_accept_language        ($default = "en")                   { return        dom_at(array_merge($_GET, $_SERVER), 'HTTP_ACCEPT_LANGUAGE',             $default);  }
    function dom_server_server_name                 ($default = "localhost")            { return        dom_at(array_merge($_GET, $_SERVER), 'SERVER_NAME',                      $default);  }
    function dom_server_server_port                 ($default = "80")                   { return        dom_at(array_merge($_GET, $_SERVER), 'SERVER_PORT',                      $default);  }
    function dom_server_request_uri                 ($default = "www.villepreux.net")   { return        dom_at(array_merge($_GET, $_SERVER), 'REQUEST_URI',                      $default);  }
    function dom_server_https                       ($default = "on")                   { return        dom_at(array_merge($_GET, $_SERVER), 'HTTPS', is_localhost() ? "off" :   $default);  }
    function dom_server_http_host                   ($default = "127.0.0.1")            { return        dom_at(array_merge($_GET, $_SERVER), 'HTTP_HOST',                        $default);  }
    function dom_server_http_do_not_track           ()                                  { return   1 == dom_at(array_merge($_GET, $_SERVER), 'HTTP_DNT',                         0);         }

    function dom_do_not_track()
    {
        if (!!get("static")) return true; // PHP do not track detection would not work for static website

        return dom_server_http_do_not_track()
            || dom_header_global_privacy_control()
            || dom_header_do_not_track();
    }
    
    #endregion
    #region HELPERS : DEVELOPMENT ENVIRONMENT
    ######################################################################################################################################

    function dom_is_localhost() { return (false !== stripos(dom_server_http_host(), "localhost"))
                                      || (false !== stripos(dom_server_http_host(), "127.0.0.1")); }

    #endregion
    #region HELPERS : PROFILING
    ######################################################################################################################################
    
    $__dom_profiling = array();

    function dom_debug_timings($totals_only = true)
    {
        global $__dom_profiling;

        $report = array();
        $totals = array();

        foreach ($__dom_profiling as $profiling) $totals[$profiling["function"].(!!$profiling["tag"] ? ("(".$profiling["tag"].")") : "")] = 0;
        foreach ($__dom_profiling as $profiling) $totals[$profiling["function"].(!!$profiling["tag"] ? ("(".$profiling["tag"].")") : "")] += $profiling["dt"];

        if (!$totals_only)
        {
            foreach ($__dom_profiling as $profiling)
            {
                $report[] = str_pad(number_format($profiling["dt"], 2), 6, " ", STR_PAD_LEFT) . ": " . $profiling["function"] . ((false !== $profiling["tag"]) ? ("(".$profiling["tag"].")") : "");
            }
        }

        foreach ($totals as $function => $total)
        {
            $report[] = str_pad(number_format($total, 2), 6, " ", STR_PAD_LEFT) . ($totals_only ? "" : " (TOTAL)") . ": " . $function;
        }

        return $report;
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
    
    function dom_debug_functions_callstack($shift_current_call = true)
    {
        $callstack = dom_debug_callstack($shift_current_call);
        if ($shift_current_call) array_shift($callstack);

        $functions = array();
        foreach ($callstack as $call) $functions[] = $call["function"];        
        return $functions;
    }

    function dom_debug_track_delta($tag = false, $dt = false)
    {
        $functions_callstack = dom_debug_functions_callstack();
        array_shift($functions_callstack); // dom_debug_track_delta
        array_shift($functions_callstack); // __destruct

        if (count($functions_callstack) == 0)
        {
            $functions_callstack[] = "_";
        }

        $functions_callstack_string = implode(" <- ", $functions_callstack);

        global $__dom_profiling;

        $__dom_profiling[] = array(
            "dt"        => $dt,
            "tag"       => $tag,
            "callstack" => $functions_callstack_string,
            "function"  => $functions_callstack[0],
            "t"         => null
            );

        return "";
    }

    class dom_debug_track_delta_scope
    {
        function __construct($annotation = false)
        {
            $this->t = microtime(true);
            $this->annotation = $annotation;
        }

        function __destruct()
        {
            dom_debug_track_delta($this->annotation, microtime(true) - $this->t);
        }
    };
    
    function dom_debug_track_timing($annotation = false)
    {
        return new dom_debug_track_delta_scope($annotation);
    }

    #endregion
    #region HELPERS : FILE AND FOLDERS PATH FINDER
    ######################################################################################################################################
        
    function dom_path($path0, $default = false, $search = true, $depth0 = DOM_AUTO, $max_depth = DOM_AUTO, $offset_path0 = ".")
    {
        $profiler = dom_debug_track_timing();

        if ($depth0    === DOM_AUTO) $depth0    = dom_get("dom_path_max_depth", 8);
        if ($max_depth === DOM_AUTO) $max_depth = dom_get("dom_path_max_depth", 8);
        
        $param = "";

        $param_pos = stripos($path0, "?");

        if (false !== $param_pos)
        {
            $path0 = substr($path0, 0, $param_pos);
            $param = substr($path0,    $param_pos);
        }

        $searches = array(array($path0, $depth0, $offset_path0));

        while (count($searches) > 0)
        {
            $path = $searches[0][0]; $depth = $searches[0][1]; $offset_path = $searches[0][2];
            array_shift($searches);
    
            // Minimal early validation for when user is not providing a real url or path but some random text content

            if (false !== stripos($path, "\n") ) return $default;
            if (false !== stripos($path, "{")  ) return $default;
            if (false !== stripos($path, "\"") ) return $default;
        
            // If URL format then keep it as-is

            if (strlen($path) >= 2 && $path[0] == "/" && $path[1] == "/") return $path.$param;
            if (0 === stripos($path, "http"))                             return $path.$param;

            // If path exists then directly return it

            if (@file_exists($path))                                return $path.$param;
            if (($max_depth == $depth) && dom_url_exists($path))    return $path.$param;

            if (!!dom_get("dom_htaccess_rewrite_php"))
            {
                if (@file_exists("$path.php"))                              return $path.$param;
                if (($max_depth == $depth) && dom_url_exists("$path.php"))  return $path.$param;
            }

            // If we have already searched too many times then return fallback

            if ($depth <= 0) return $default;

            // If beyond root then stop here

            foreach (dom_get("dom_root_hints", array()) as $root_hint_file)
            {
                if (file_exists("$offset_path/$root_hint_file")) 
                {
                    $search = false;
                    break;
                }
            }

            // If requested then search in parent folder

            if ($search)
            {
                $searches[] = array("../$path", $depth - 1, "../$offset_path");
            }
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
    #region HELPERS : PHP FILE INCLUDE

    function dom_include($path) { if (!!$path) @include($path); }

    #endregion
    #region WIP DEPENDENCIES
    ######################################################################################################################################
    
   @dom_include(dom_path("tokens.php")); // TODO let responsibility to end-user ? or use a dom-specific name

   @dom_include(dom_path("../vendor/michelf/php-markdown/Michelf/Markdown.inc.php"));
   @dom_include(dom_path("../vendor/michelf/php-smartypants/Michelf/SmartyPants.inc.php"));

    #endregion
    #region SYSTEM : PHP SYSTEM AND CMDLINE HANDLING
    ######################################################################################################################################

    function dom_init_php()
    {        
       global $argv;

        if (is_array($argv) && count($argv) > 1)
        {
            array_shift($argv);

            foreach ($argv as $arg)
            {
                $arg = explode("=", $arg);

                if (count($arg) > 1) dom_set($arg[0], $arg[1]);
                else                 dom_set($arg[0], true);
            }
        }

        if (dom_is_localhost())
        {
            @set_time_limit(24*60*60);
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
    #region HELPERS : CURRENT URL
    ######################################################################################################################################

    function dom_host_url   ()                  { return rtrim("http".((dom_server_https()=='on')?"s":"")."://".dom_server_http_host(),"/"); }
    function dom_url_branch ($params = false)   { $uri = explode('?', dom_server_request_uri(), 2); $uri = $uri[0]; $uri = ltrim($uri, "/"); if ($params) { $uri .= "?"; foreach (dom_get_all() as $key => $val) { if (!is_array($val)) $uri .= "&$key=$val"; } } return trim($uri, "/"); }
    function dom_url        ($params = false)   { $branch = dom_url_branch($params); return ($branch == "") ? dom_host_url() : dom_host_url()."/".$branch; }

    #endregion
    #region WIP SYSTEM : DEFAULT CONFIG AND AVAILABLE USER OPTIONS
    ######################################################################################################################################

    function dom_init_options()
    {
        // TODO prefix all variables with dom_

        // Cannot be modified at browser URL level

      //dom_set("title",                             "Blog"); // Will be deducted from document headlines
        dom_set("keywords",                          "");

      //dom_set("url",                               dom_url());                              if (dom_path("DTD/xhtml-target.dtd", dom_path("xhtml-target.dtd")))
      //dom_set("DTD",                              'PUBLIC "-//W3C//DTD XHTML-WithTarget//EN" "'.dom_path("DTD/xhtml-target.dtd", dom_path("xhtml-target.dtd")).'"');

        dom_set("normalize",                        "sanitize");

        dom_set("icons_path",                       "img/icons/");

        dom_set("background_color",                 "#FFFFFF");
        dom_set("theme_color",                      "#00b0da");
        dom_set("text_color",                       "#000000");
        dom_set("link_color",                       "#0000FF");
        
        dom_set("default_image_ratio_w",            "300");
        dom_set("default_image_ratio_h",            "200");

      //dom_set("scrollbar_width",                  "17px"); // It's a css env var

        dom_set("image",                            "image.jpg");
        dom_set("geo_region",                       "FR-75");
        dom_set("geo_placename",                    "Paris");
        dom_set("geo_position_x",                   48.862808);
        dom_set("geo_position_y",                    2.348237);

        dom_set("support_service_worker",           true);
        
      //dom_set("fonts",                            "Roboto:300,400,500");
            
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
        dom_set("version_sanitize",                "12.0.1");  
        dom_set("version_material",                "0.38.2"); // latest => SimpleMenu got broken in 0.30.0 => Got fixed in CSS => latest => Broken in 0.39.0 => 0.38.0
        dom_set("version_bootstrap",                "4.1.1");
        dom_set("version_spectre",                  "x.y.z");
        dom_set("version_popper",                  "1.11.0");
        dom_set("version_jquery",                   "3.6.0"); // Was 3.5.1 / Was 3.2.1
        dom_set("version_prefixfree",               "1.0.7");
        dom_set("version_h5bp",                     "7.1.0");
        
        dom_set("cache_time",                       1*60*60); // 1h

        dom_set("dom_forwarded_flags",              array("amp","contrast","light","no_js","rss"));
        dom_set("dom_root_hints",                   array(".git", ".github", ".well-known"));

        dom_set("dom_img_lazy_loading_after",       3);

        // Can be modified at browser URL level

        dom_set("canonical",                        dom_get("canonical",    dom_url()                   ));
        dom_set("framework",                        dom_get("framework",    "NONE"                      ));
        dom_set("amp",                              dom_get("amp",          false                       ));
        dom_set("cache",                            dom_get("cache",        false                       ));
        dom_set("minify",                           dom_get("minify",       !dom_get("beautify",false)  )); // Performances first
        dom_set("page",                             dom_get("page",         1                           ));
        dom_set("n",                                dom_get("n",            12                          ));
    }

    #endregion
    #region CONFIG : INTERNALS
    ######################################################################################################################################

    function dom_init_internals()
    {
        if (!defined("DOM_AJAX_PARAMS_SEPARATOR1")) define("DOM_AJAX_PARAMS_SEPARATOR1", "-_-");
        if (!defined("DOM_AJAX_PARAMS_SEPARATOR2")) define("DOM_AJAX_PARAMS_SEPARATOR2", "_-_");
        
        if (dom_has("dom_rand_seed")) { mt_srand(dom_get("dom_rand_seed")); }
    }

    #endregion
    #region HELPERS : AJAX / ASYNC
    ######################################################################################################################################

    function dom_ajax_url_base_params($get = true, $post = false, $session = false)
    {
        // TODO prevent exposing all the vars
        $vars = dom_get_all($get, $post, $session);
        unset($vars["support_header_backgrounds"]); // Can lead to much too long URLs
        return $vars;
    }

    function dom_ajax_url           ($ajax_params)                                      { return './?'.http_build_query(array_merge(dom_ajax_url_base_params(), array("ajax" => $ajax_params))); }

    function dom_ajax_param_encode2 ($p)                                                { return (is_array($p))                                     ? implode(DOM_AJAX_PARAMS_SEPARATOR2, $p) : $p; }
    function dom_ajax_param_decode2 ($p)                                                { return (false !== strpos($p, DOM_AJAX_PARAMS_SEPARATOR2)) ? explode(DOM_AJAX_PARAMS_SEPARATOR2, $p) : $p; }

    function dom_ajax_param_encode  ($prefix, $params = array())                        {                                               return $prefix . '-' .                     implode(DOM_AJAX_PARAMS_SEPARATOR1, array_map("dom_ajax_param_encode2", $params)); }
    function dom_ajax_param_decode  ($prefix, $params)                                  { $params = substr($params, strlen($prefix)+1); return array_map("dom_ajax_param_decode2", explode(DOM_AJAX_PARAMS_SEPARATOR1, $params)); }

    function dom_ajax_placeholder   ($ajax_params, $html = "")                          { return div($html, dom_ajax_classes($ajax_params)); }
    
    function dom_ajax_classes       ($ajax_params, $extra = false)                      { return "ajax-container ajax-container-".dom_to_classname($ajax_params).(($extra !== false) ? (" ajax-container-".dom_to_classname($extra)) : ""); }
    function dom_ajax_container     ($ajax_params, $placeholder = false, $period = -1)  { return  (($placeholder === false) ? dom_ajax_placeholder($ajax_params) : $placeholder) . '<script>dom_ajax("'.dom_ajax_url($ajax_params).'", function(content) { document.querySelector(".ajax-container-'.dom_to_classname($ajax_params).'").outerHTML = content; dom_on_ajax_reception(); }, '.$period.'); </script>'; }

    function dom_ajax_call          ($f)                                                { $args = func_get_args(); return dom_ajax_call_FUNC_ARGS($f, $args); }
        
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
        // Async calls disabled
        
        if (dom_has("noajax") || !!dom_get("no_js") || dom_has("rss") || dom_AMP())
        {  
            $n = stripos($f,"/");
            $f = (false === $n) ? $f : substr($f, 0, $n);

            return call_user_func_array($f, $args);
        }
        
        // Async calls enabled
        
        $ajax = dom_get("ajax", false);

        if (false === $ajax)
        {
            // Async caller (or client)
        
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
            // Async listener (or server)
        
            global $call_asyncs_started;
            if (!$call_asyncs_started)    return ""; // We have not started listening yet
            if (0 !== stripos($ajax, $f)) return ""; // This is not the function you are looking for
            
            $args = dom_ajax_param_decode($f, $ajax);
            
            foreach ($args as &$arg)
            {
                if ($arg === "FALSE") $arg = false;
                if ($arg === "TRUE")  $arg = true;
            }    
            
            $n = stripos($f,"/");
            $f = (false === $n) ? $f : substr($f, 0, $n);

            return call_user_func_array($f, $args);
        }
    }

    #endregion
    #region HELPERS : HEREDOC

    function dom_modify_tab($txt, $tab_offset, $tab = "    ", $line_sep = PHP_EOL)
    {
        $lines = explode($line_sep, $txt);

        foreach ($lines as &$line)
        {
            $iterations = $tab_offset;

            while ($iterations++ < 0)
            {
                $pos = stripos($line, $tab);
                if ($pos !== 0) break;
                $line = substr($line, strlen($tab));
            }
            
            $iterations = $tab_offset;

            while ($iterations-- > 0)
            {
                $line = $tab.$line;
            }
        }

        return implode($line_sep, $lines);
    }

    function dom_heredoc_start($tab_offset = 0, $tab = "    ")
    {
        if (false === dom_get("dom_heredoc")) dom_set("dom_heredoc", array());

        $heredoc_stack = dom_get("dom_heredoc");

        $heredoc_stack[] = array(

            "current_output" => "",
            "tab_offset"     => $tab_offset,
            "tab"            => $tab            
        );

        dom_set("dom_heredoc", $heredoc_stack);
        
        ob_start();

        return "";
    }

    function dom_heredoc_flush($transform = false, $force_minify = false)
    {
        if (null !== $transform)
        {
            $output = ob_get_contents();

            $heredoc_stack = dom_get("dom_heredoc");

            if ($heredoc_stack[count($heredoc_stack)-1]["tab_offset"] != 0) $output = dom_modify_tab($output, $heredoc_stack[count($heredoc_stack)-1]["tab_offset"], $heredoc_stack[count($heredoc_stack)-1]["tab"]);
            if (!!$transform) $output = $transform($output, $force_minify);
        
            $heredoc_stack[count($heredoc_stack)-1]["current_output"] .= $output;

            dom_set("dom_heredoc", $heredoc_stack);
        }        

        ob_end_clean();
        ob_start();
    }

    function dom_heredoc_stop($transform = false, $force_minify = false)
    {
        dom_heredoc_flush($transform, $force_minify);
        ob_end_clean();
        
        $heredoc_stack = dom_get("dom_heredoc");
        $heredoc = array_pop($heredoc_stack);
        dom_set("dom_heredoc", $heredoc_stack);

        return $heredoc["current_output"];
    }

    #endregion
    #region JAVASCRIPT SNIPPETS
    ######################################################################################################################################

    function dom_js_ajax_head()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

            /* DOM Head Javascript boilerplate */

            var dom_ajax_pending_calls = [];

            function dom_ajax(url, onsuccess, period, onstart, mindelay)
            {
                dom_ajax_pending_calls.push(new Array(url, onsuccess, period, onstart, mindelay));
            };

        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_ajax_body()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

            /* DOM Body Javascript boilerplate */

            var dom_process_ajax = function(url, onsuccess, period, onstart, mindelay)
            {
                if (typeof onsuccess    === "undefined") onsuccess  = null;
                if (typeof period       === "undefined") period     = 0;
                if (typeof onstart      === "undefined") onstart    = null;
                if (typeof mindelay     === "undefined") mindelay   = 500;
            
                var cb = true;
            
                if (onstart)
                {
                    onstart();
                    cb = null;
                    setTimeout(function() {
                        if(cb) { cb(); } else { cb = true; } 
                        }, mindelay);
                }

                fetch(url, { method: 'GET' })
                    .then(response => response.text())
                    .then(data => { 
                        
                    if (onsuccess) {
                            if (cb) { onsuccess(data); }
                            else    { cb = function() { onsuccess(data); }; }
                            }    

                    
                    if (period > 0) {
                        setTimeout(function() {
                            dom_ajax(url, onsuccess, period, onstart, mindelay);
                            }, period);
                        }

                     });
            
              /*$.ajax
                ({
                    url:    url
                ,   type:  "GET"
                ,   async: true

                ,   success: function(res) {
                        if (onsuccess) {
                            if (cb) { onsuccess(res); }
                            else    { cb = function() { onsuccess(res); }; }
                            }
                        }

                ,   complete: function() {
                        if (period > 0) {
                            setTimeout(function() {
                                dom_ajax(url, onsuccess, period, onstart, mindelay);
                                }, period);
                            }
                        }
                });*/
            };
            
            var dom_pop_ajax_call = function()
            {
                if ((typeof dom_ajax_pending_calls !== "undefined") && dom_ajax_pending_calls.length > 0)
                {
                    var ajax_pending_call = dom_ajax_pending_calls.pop();
            
                    <?php if (!!dom_get("debug")) { ?> console.log("DOM: Processing ajax pending call: " + ajax_pending_call[0]); console.log(ajax_pending_call); <?php } ?> 
                    dom_process_ajax(ajax_pending_call[0], ajax_pending_call[1], ajax_pending_call[2], ajax_pending_call[3], ajax_pending_call[4]);
                }
            };
            
            dom_on_loaded(function() {

                while ((typeof dom_ajax_pending_calls !== "undefined") && dom_ajax_pending_calls.length > 0) { dom_pop_ajax_call(); };
                setInterval(dom_pop_ajax_call, 1*1000);

                });
            
        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
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
        if (' '   !=         $attributes[0])    { return ' '        . $attributes;     }
        
        return $attributes;
    }
    
    function dom_attributes_add_class($attributes, $classname, $add_first = false)
    {
        $attributes = dom_attributes($attributes);

        if ("" === $attributes) return dom_attributes($classname);
            
        $bgn = stripos($attributes, "class=");      if (false === $bgn) return $attributes;
        $bgn = stripos($attributes, '"', $bgn);     if (false === $bgn) return $attributes;
        $end = stripos($attributes, '"', $bgn + 1); if (false === $bgn) return $attributes;

        $classes = substr($attributes, $bgn + 1, $end - $bgn - 1);

        if ($add_first) $classes = "$classname $classes";
        else            $classes = "$classes $classname";

        $attributes = substr($attributes, 0, $bgn + 1) . $classes . substr($attributes, $end);

        return $attributes;
    }

    #endregion
    #region HELPERS : DOM COMPONENTS: FRAWEWORK CLASSES
    
    function dom_frameworks_material_classes_grid_cells() { $a = array(); foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $s) foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $m) foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $l) $a["grid-cell-$s-$m-$m"] = 'mdc-layout-grid__cell--span-'.$s.'-phone mdc-layout-grid__cell--span-'.$m.'-tablet mdc-layout-grid__cell--span-'.$l.'-desktop'; return $a; }

    $__dom_frameworks = array
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
        
       ,"bootstrap" => array
        (
            "classes" => array
            (
                'menu-list'             => 'dropdown-menu sidebar'
            ,   'list-item-separator'   => 'dropdown-divider'
            ,   'toolbar'               => 'navbar sticky-top'        
            )
        )
        
       ,"spectre" => array
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
        global $__dom_frameworks;
        
        $framework = dom_get("framework", "");
        
        if ((array_key_exists($framework, $__dom_frameworks)) 
        &&  (array_key_exists($classname, $__dom_frameworks[$framework]["classes"])))
        {
            $classname .= ' ' . $__dom_frameworks[$framework]["classes"][$classname];
        }
        
        return $classname;
    }

    #endregion
    #region HELPERS : LOCALIZATION
    ######################################################################################################################################
    
    function dom_T($label, $default = false, $lang = false)
    { 
        $lang = strtolower(substr((false === $lang) ? dom_get("lang", dom_server_http_accept_language('en')) : $lang, 0, 2));
        $key = "dom_loc_".$lang."_".$label;
        if (false === dom_get($key, false) && false !== $default) dom_set($key, $default);
        return dom_get($key, (false === $default) ? $label : $default);
    }
    
    #endregion
    #region HELPERS : MISC
    ######################################################################################################################################

    function dom_coalesce()
    {
        $args = func_get_args();
        return dom_coalesce_FUNC_ARGS($args);
    }

    function dom_coalesce_FUNC_ARGS($args, $fallback = false)
    {
        foreach ($args as $arg) if (!!$arg) return $arg;
        return $fallback;
    }

    function dom_to_classname($str, $tolower = DOM_AUTO)
    {
        if ($tolower === DOM_AUTO) $tolower = true;

        // TODO Real implementation
        
        $str =  str_replace("é","e",
                str_replace("è","e",
                str_replace("à","a",$str)));
                
        return preg_replace('/\W+/','', $tolower ? strtolower(strip_tags($str)) : strip_tags($str));
    }

    function dom_AMP()
    {
        return false !== dom_get("amp", false) 
            && 0     !== dom_get("amp", false) 
            && "0"   !== dom_get("amp", false); 
    }

    function dom_url_exists($url)
    {
        $headers = @get_headers($url);
        return (is_array($headers) && false !== stripos($headers[0], "200 OK")) ? true : false;
    }

    function dom_clean_title($title)
    {
        return trim($title, "!?;.,: \t\n\r\0\x0B");
    }

    function dom_content($urls, $timeout = 7)
    {
        if (is_array($urls))
        {
            foreach ($urls as $url)
            {
                $content = dom_content($url, $timeout);
                
                if (false !== $content)
                {
                    return $content;
                }
            }
            
            return false;
        }

        $url = $urls;
        
        $content = false;

        $curl = @curl_init();
        
        if (false !== $curl)
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,  false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,  false);                
            curl_setopt($curl, CURLOPT_USERAGENT,       'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:77.0) Gecko/20100101 Firefox/77.0');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,  true);
            curl_setopt($curl, CURLOPT_URL,             $url);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,  $timeout);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION,  true);
            
            $content = curl_exec($curl);
            
            if (!!dom_get("debug") && (!$content || $content == "")) 
            {
                echo comment("CURL ERROR: ".curl_error($curl).(!$content ? " - false result" : " - Empty result"));
                echo comment(dom_to_string(curl_getinfo($curl)));
            }

            curl_close($curl);
        }
        else
        {
            $content = file_get_contents($url);
        }

        if (!$content)
        {
            if (!!get("debug")) echo dom_eol().comment("COULD NOT PARSE $url");
        }

        return $content;
    }

    function dom_array_open_url($urls, $content_type = 'json', $timeout = 7)
    {
        $content = dom_content($urls, $timeout);

        if (!!$content)
        {
                 if ($content_type == 'xml')  $content_type = 'text/xml';
            else if ($content_type == 'json') $content_type = 'application/json';
            else if ($content_type == 'html') $content_type = 'text/html';
            else if ($content_type == 'csv')  $content_type = 'text/csv';

            
                 if (       "text/xml"  == $content_type) { $content = @json_decode(@json_encode(@simplexml_load_string($content,null,LIBXML_NOCDATA )),true); }
            else if (       "text/csv"  == $content_type) { $content = @str_getcsv($content,"\n"); if (!!$content) foreach ($content as &$rrow) { $rrow = str_getcsv($rrow, ";"); } }
            else if ("application/json" == $content_type) { $content = @json_decode($content, true); }
        }

        return $content;
    }

    function dom_array_hashtags($text)
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

    #endregion
    #region WIP API : UTILITIES : STRINGS MANIPULATION

    function dom_dup($html, $n)                             { $new = ""; for ($i = 0; $i < $n; ++$i) $new .= $html; return $new; }
    function dom_eol($n = 1)                                { if (!!dom_get("minify",false)) return '';  switch (strtoupper(substr(PHP_OS,0,3))) { case 'WIN': return dom_dup("\r\n",$n); case 'DAR': return dom_dup("\r",$n); } return dom_dup("\n",$n); }
    function dom_tab($n = 1)                                { if (!!dom_get("minify",false)) return ' '; return dom_dup(' ', 4*$n); }
    function pan($x, $w, $c = " ", $d = 1)                  { if (!!dom_get("minify",false)) return $x;  $x="$x"; while (mb_strlen($x, 'utf-8')<$w) $x=(($d<0)?$c:"").$x.(($d>0)?$c:""); return $x; }
    function precat()                                       { $args = func_get_args(); return precat_FUNC_ARGS($args); }
    function precat_FUNC_ARGS($args)                        { return wrap_each(array_reverse($args),''); }
    function cat()                                          { $args = func_get_args(); return cat_FUNC_ARGS($args); }
    function cat_FUNC_ARGS($args)                           { return wrap_each($args,''); }
    function quote($txt, $quote = false)                    { return ($quote === false) ? ((false === strpos($txt, '"')) ? ('"'.$txt.'"') : ("'".$txt."'")) : ($quote.$txt.$quote); }
    
    #endregion
    #region WIP ????

    function dom_str_replace_all($from, $to, $str)
    {
        if (is_string($str))
        {
            $len = strlen($str);

            while (true)
            {
                $str = str_replace($from, $to, $str);
                $new_len = strlen($str);
                if ($new_len >= $len) break;
                $len = $new_len;
            }
        }

        return $str;
    }
    
    function dom_to_string($x)
    {
      //return is_array($x) ? print_r($x, true) : (string)$x;
        if (is_array($x))
        {
            $rows = "";
            $r = 0;
            foreach ($x as $k => $v) { $rows .= ($r > 0 ? PHP_EOL : '').(($k).': '.(dom_to_string($v))); ++$r; }
            return ($rows);
        }
        else if (is_object($x))
        {
            return json_encode($x);
        }
        
        return (string)$x;
    }

    function dom_to_html($x)
    {
        if (is_array($x))
        {
            $rows = "";
            foreach ($x as $k => $v) $rows .= tr(td($k).td(dom_to_html($v)));
            return dom_table($rows);
        }
        else if (is_object($x))
        {
            return json_encode($x);
        }
        
        return (string)$x;
    }
    
    function dom_is_array_filtered($a, $required_values, $unwanted_values = false)
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
    
    function dom_is_array_sequential($a)
    {
        return array() === $a || array_keys($a) === range(0, count($a) - 1);
    }

    function dom_if($expr, $html)                           { return (!!$expr) ? $html : ""; }

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
        
        $a          = is_array($a)         ? $a         : array($a);
        $transforms = is_array($transform) ? $transform : array($transform);

        $html = "";         
        $i    = 0;

        foreach ($a as $e)
        {
            if ($flatten_array && is_array($e)) { $e = wrap_each($e,","); }

            foreach ($transforms as $transform) 
            {
                $e_args = $args;

                if (is_array($transform))
                {
                    $e_args = $transform;
                    $transform = $e_args[0];
                    array_shift($e_args);
                }

                $e = call_user_func_array($transform, array_merge(array($e), $e_args, array($i)));
            }
            
            $html .= (($i++ > 0) ? $glue : '') . $e;
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
            
            $hashtag = a('#'.$hashtag, $url, "hashtag", DOM_EXTERNAL_LINK);
            
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

    use Michelf\Markdown;
    use Michelf\SmartyPants;
        
    function dom_markdown($text, $hard_wrap = false, $headline_level_offset = 0, $no_header = false, $anchor = false, $smartypants = true, $markdown = true)
    {
        if ($markdown)    $html = Markdown::defaultTransform($text);
        if ($smartypants) $html = SmartyPants::defaultTransform($html);
        if ($hard_wrap)   $html = str_replace("\n", "<br>", $html);

        if (!!$no_header)
        {
            $html = str_replace("<h1",   "<span style=\"display: none\"", $html);
            $html = str_replace("</h1>", "</span>",                       $html);
        }

        if ($headline_level_offset !== false)
        {
            for ($h = 9; $h >= 1; --$h)
            {
                $pos_end = 0;

                while (true)
                {
                    $tag_end       = "</h$h>";
                    $pos_bgn       = stripos($html, "<h$h",   $pos_end);       if (false === $pos_bgn)       break;
                    $pos_bgn_inner = stripos($html, ">",      $pos_bgn);       if (false === $pos_bgn_inner) break; $pos_bgn_inner++;
                    $pos_end_inner = stripos($html, $tag_end, $pos_bgn_inner); if (false === $pos_end_inner) break;
                    $pos_end       = $pos_end_inner + strlen($tag_end);

                    $inner = substr($html, $pos_bgn_inner, $pos_end_inner - $pos_bgn_inner);

                    $headline = h($h + $headline_level_offset, $inner, false, $anchor);

                    $html_before = substr($html, 0, $pos_bgn);
                    $html_after  = substr($html, $pos_end);

                    $html = $html_before . $headline . $html_after;

                    $pos_end = strlen($html_before . $headline) + strlen($tag_end);
                }
            }
        }
    
        return $html;
    }

    #endregion
    #region WIP LOREM IPSUM

    function lorem_ipsum($nb_paragraphs = 5, $tag = "p")
    {
        $html = "";

        if ($nb_paragraphs === 0.25) $html .= dom_tag($tag, "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque enim nibh, finibus ut sapien ac, congue sagittis erat. Nulla gravida odio ac arcu maximus egestas ut ac massa.");
    //  if ($nb_paragraphs === 0.25) $html .= dom_tag($tag, "Maecenas sagittis tincidunt pretium. Suspendisse dictum orci non nibh porttitor posuere. Donec vehicula vulputate enim, vitae vulputate sapien auctor et. Ut imperdiet non augue quis suscipit.");
        if ($nb_paragraphs === 0.5)  $html .= dom_tag($tag, "Phasellus risus ipsum, varius vitae elit laoreet, convallis pharetra nisl. Aliquam iaculis, neque quis sollicitudin volutpat, quam leo lobortis enim, consectetur volutpat sapien ipsum in mauris. Maecenas rhoncus sit amet est quis tempus. Duis nulla mauris, rhoncus eget vestibulum placerat, posuere in sem. Nulla imperdiet suscipit felis, a blandit ante dictum a.");

        if ($nb_paragraphs >= 1) $html .= dom_tag($tag, "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque enim nibh, finibus ut sapien ac, congue sagittis erat. Nulla gravida odio ac arcu maximus egestas ut ac massa. Maecenas sagittis tincidunt pretium. Suspendisse dictum orci non nibh porttitor posuere. Donec vehicula vulputate enim, vitae vulputate sapien auctor et. Ut imperdiet non augue quis suscipit. Phasellus risus ipsum, varius vitae elit laoreet, convallis pharetra nisl. Aliquam iaculis, neque quis sollicitudin volutpat, quam leo lobortis enim, consectetur volutpat sapien ipsum in mauris. Maecenas rhoncus sit amet est quis tempus. Duis nulla mauris, rhoncus eget vestibulum placerat, posuere in sem. Nulla imperdiet suscipit felis, a blandit ante dictum a.");
        if ($nb_paragraphs >= 2) $html .= dom_tag($tag, "Nunc lobortis dapibus justo, non eleifend arcu blandit ut. Fusce viverra massa purus, vel dignissim justo dictum quis. Maecenas interdum turpis in lacinia imperdiet. In vel dui leo. Curabitur vel iaculis leo. Sed efficitur libero sed massa porttitor tristique. Nam sit amet mi elit. Donec pellentesque sit amet tellus ut aliquam. Fusce consequat commodo dui, tempus fringilla diam fermentum eu. Etiam finibus felis egestas velit elementum, at bibendum lectus volutpat. Donec non odio varius, ornare felis mattis, fermentum dui.");
        if ($nb_paragraphs >= 3) $html .= dom_tag($tag, "Phasellus ut consectetur justo. Nam eget libero augue. Praesent ut purus dignissim, imperdiet turpis sed, gravida metus. Praesent cursus fringilla justo et maximus. Donec ut porttitor tellus. Ut ac justo imperdiet, accumsan ligula et, facilisis ligula. Sed ac nulla at purus pretium tempor. Suspendisse nec iaculis lectus.");
        if ($nb_paragraphs >= 4) $html .= dom_tag($tag, "Nulla varius dui luctus augue blandit, non commodo lectus pulvinar. Aenean lacinia dictum lorem nec molestie. Curabitur hendrerit, tellus quis lobortis pretium, odio felis convallis metus, sed pulvinar massa libero non sapien. Praesent aliquet posuere ex, vitae rutrum magna maximus id. Sed at eleifend libero. Cras maximus lacus eget sem hendrerit hendrerit. Nullam placerat ligula metus, eget elementum risus egestas non. Sed bibendum convallis nisl ac pretium. Sed ac magna mi. Aliquam sollicitudin quam augue, at tempus quam sagittis id. Aliquam convallis consectetur est non vulputate. Phasellus rutrum elit at neque aliquam aliquet. Phasellus tincidunt sem pharetra libero pellentesque fermentum. Donec tellus mauris, pulvinar consequat est vel, faucibus lacinia ante. Proin et posuere sem, nec luctus ligula.");
        if ($nb_paragraphs >= 5) $html .= dom_tag($tag, "Ut volutpat ultrices massa id rhoncus. Vestibulum maximus non leo in dapibus. Phasellus pellentesque dolor id dui mollis, eget laoreet est pulvinar. Ut placerat, ex sit amet interdum lobortis, magna dolor volutpat ante, a feugiat tortor ante nec nulla. Pellentesque dictum, velit vitae tristique elementum, ex augue euismod arcu, in varius quam neque efficitur lorem. Fusce in purus nunc. Fusce sed dolor erat.");
        
        return $html;
    }

    function lorem($nb_paragraphs = 5, $tag = "p") { return lorem_ipsum($nb_paragraphs, $tag); }

    #endregion
    #region WIP HELPERS : HOOKS & PAGINATION
    ######################################################################################################################################

    $__dom_user_hooks = array();

    function dom_add_hook($hook_id, $hook_callback, $hook_userdata)
    {
        global $__dom_user_hooks;

        if (!array_key_exists($hook_id, $__dom_user_hooks)) $__dom_user_hooks[$hook_id] = array();
        $__dom_user_hooks[$hook_id][] = array("id" => $hook_id, "callback" => $hook_callback, "userdata" => $hook_userdata);
    }

    function dom_call_user_hook()
    {
        global $__dom_user_hooks;

        $args = func_get_args();
        $hook_id = array_shift($args);
        
        if (!array_key_exists($hook_id, $__dom_user_hooks)) return true;

        $hooks = $__dom_user_hooks[$hook_id];
        
        foreach ($hooks as $hook)
        {
            $user_args = $args;
            $user_args[] = $hook["userdata"];

            call_user_func_array($hook["callback"], $user_args);
        }

        return true;
    }

    $__dom_last_headline_level = false;

    function hook_headline($h, $title)
    {
        global $__dom_last_headline_level;
        $__dom_last_headline_level = (int)$h;

        if ($h == 1) hook_title($title);
        if ($h == 2) hook_section($title);

        dom_call_user_hook("headline", $title);
    }

    function get_last_headline()
    {
        global $__dom_last_headline_level;
        return $__dom_last_headline_level;
    }

    function clean_from_tags($html)
    {   
        while (true)
        {
            $bgn =  stripos($html, ">"); if (false === $bgn) break;
            $end = strripos($html, "<"); if (false === $end) break; if ($end < $bgn) break;

            $html = substr($html, $bgn+1, $end-$bgn-1);
        }
        
        return $html;
    }

    function hook_title($title)
    {
        if (!!$title && false === dom_get("title", false))
        {
            $title = trim(clean_from_tags($title));
            dom_set("title", $title);
        }        
    }

    function hook_section($title)
    {
        $title = trim(clean_from_tags($title));
        
        if (!!get("hook_section_filter"))
        {
            $f = get("hook_section_filter");

            if (function_exists($f))
            {
                $modified_title = $f($title);

                if (false === $modified_title)
                {
                    return "";
                }

                $title = array($modified_title, "#".anchor_name($title));
            }
        }

        dom_set("hook_sections", array_merge(dom_get("hook_sections", array()), array($title)));
    }
    
    function hook_heading($heading)
    {
        if (!!$heading && false === dom_get("heading", false))
        {
            $heading = trim(clean_from_tags($heading));
            dom_set("heading", $heading);
        }        
    }
    
    function hook_toolbar($row)
    {
        dom_set("toolbar",      true);
        dom_set("toolbar_$row", true);
    }

    // Body extension
    
    $hook_body = "";
    
    function hook_body($html)
    {
        global $hook_body;
        $hook_body .= $html;
    }

    function _body()
    {
        global $hook_body;
        return $hook_body;
    }

    // Links
    
    $dom_hook_links = array();
    
    function dom_hook_link($title, $url)
    {
        if (strlen($url) >= 1)
        {
            if ($url == ".") return;

            if ($url[0] == "#") return;
            if ($url[0] == "?") return;
            
            if (0 === stripos($url, ".?")         ) return;
            if (0 === stripos($url, "javascript") ) return;
        }

        global $dom_hook_links;

        $found_url = false;

        foreach ($dom_hook_links as $link)
        {
            if ($link["url"] == $url)
            {
                $found_url = true;
                break;
            }
        }

        if (!$found_url)
        {
            $title = trim(strip_tags($title));
            if ($title == "") $title = substr($url, (int)stripos($url, "/"));

            $dom_hook_links[] = array("title" => $title, "url" => $url);
        }
    }

    function dom_hooked_link_rel_prefetch($link)
    {
        return dom_link_rel_prefetch($link["url"]);
    }

    function _dom_hooked_links()
    {
      //return "";
        global $dom_hook_links;
        return wrap_each($dom_hook_links, dom_eol(), "dom_hooked_link_rel_prefetch", false);
    }

    // AM Sidebars
    
    $hook_amp_sidebars = "";
    
    function hook_amp_sidebar($html)
    {
        hook_amp_require("sidebar");

        global $hook_amp_sidebars;
        $hook_amp_sidebars .= $html;
    }

    function _amp_sidebars()
    {
        global $hook_amp_sidebars;
        return $hook_amp_sidebars;
    }

    // AMP JS Scripts
    
    $hook_amp_scripts = array();
    
    function hook_amp_js($js)
    {
        hook_amp_require("script");
        
        global $hook_amp_scripts;

        $hook_amp_scripts[] = $js;
    }

    function _amp_scripts_head()
    {
        global $hook_amp_scripts;
        
        if (count($hook_amp_scripts) > 0)
        {
            return
                '<meta name="amp-script-src" content="
                    '.delayed_component("_amp_sha384_hash_local_script").' 
                    " />';
        }

        return '';
    }

    function _amp_sha384_hash_local_script()
    {
        $keys = "";
        global $hook_amp_scripts;
        foreach ($hook_amp_scripts as $js) $keys .= dom_eol().hash("sha384",$js);
        return $keys;
    }

    function _amp_scripts_body()
    {
        $html = "";

        global $hook_amp_scripts;

        foreach ($hook_amp_scripts as $js)
        {
            $uuid = md5($js);

            $html .= cosmetic(dom_eol()).

                '<amp-script script="dom_amp_scripts_'.$uuid.'" layout="container"></amp-script>'.
                '<script type="text/plain" target="amp-script" id="dom_amp_scripts_'.$uuid.'">'.
                $js.
                '</script>';
        }

        return $html;
    }

    // AMP CSS
    
    $hook_amp_css = array();

    function hook_amp_css($css)
    {
        $css = str_replace('@-moz-document url-prefix("")', '@media only screen',   $css);
        $css = str_replace('@-ms-viewport',                 '____dummy',            $css);
        $css = str_replace("@charset 'UTF-8';",             '',                     $css);
        $css = str_replace("!important",                    '',                     $css);
        
        global $hook_amp_css;
        $hook_amp_css[] = $css;

        return dom_placeholder("AMP_CSS_".(count($hook_amp_css)-1));
    }

    function _amp_css($_, $html)
    {
        global $hook_amp_css;
        
        $ordered_css = array();
        foreach ($hook_amp_css as $i => $css) $ordered_css[stripos($html, dom_placeholder("AMP_CSS_$i"))] = $css;
        ksort($ordered_css);
        
        $aggregated_css = "";
        foreach ($ordered_css as $css) $aggregated_css .= dom_eol().css_postprocess($css);

        return $aggregated_css;
    }

    // AMP Requirements

    function hook_amp_require($component)    {    if (dom_AMP())     dom_set("hook_amp_require_$component", true); return ""; }
    function has_amp_requirement($component) { return dom_AMP() && !!dom_get("hook_amp_require_$component");       }
    
    function dom_rss_record_item($title = "", $text = "", $img = "", $url = "", $date = false, $timestamp = false)
    {
        $timestamp = !!$timestamp ? $timestamp : strtotime(!!$date ? $date : (!!dom_get("dom_rss_date_granularity_daily") ? date("D, d M Y 00:00:00", time()) : date(DATE_RSS, time())));
        
        dom_set("dom_rss_items", array_merge(dom_get("dom_rss_items", array()), array(array
        (
            "title"         => $title
        ,   "link"          => $url
        ,   "description"   => $text
        ,   "img_url"       => $img
        ,   "timestamp"     =>                $timestamp
        ,   "date"          => date(DATE_RSS, $timestamp)
        
        ))));

        return "";
    }

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
                    dom_rss_record_item(

                        dom_at($metadata, "post_title",     ""),
                        dom_at($metadata, "post_text",      ""),
                        dom_at($metadata, "post_img_url",   ""),
                        dom_at($metadata, "post_url",       ""),
                        dom_at($metadata, "post_date",      false),
                        dom_at($metadata, "post_timestamp", false)

                        );
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
        
            dom_set($source . "_" . $type, (dom_has($source . "_" . $type) ? (dom_get($source . "_" . $type) . "§") : "") . dom_clean_title(dom_at($metadata, "post_title"))); 
        }
    }
    
    #endregion
    #region WIP HELPERS : PAGINATION

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
    #region WIP HELPERS : XML DOM PARSER

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
    #region WIP HELPERS : JSON API END-POINTS
    ######################################################################################################################################
    
    function json_pinterest_pin($pin, $token = false)
    {
        $profiler = dom_debug_track_timing($pin);
        
        if ($token === false && !defined("TOKEN_PINTEREST")) return array();
        
        $token      = ($token === false) ? TOKEN_PINTEREST : $token;
        $fields     = array("id","link","note","url","image","media","metadata","attribution","board","color","original_link","counts","creator","created_at");
        $end_point  = "https://api.pinterest.com/v1/pins/".$pin."/?access_token=".$token."&fields=".implode('%2C', $fields); // EXTERNAL ACCESS
        
        return dom_array_open_url($end_point, "json");
    }

    function json_pinterest_posts($username = false, $board = false, $token = false)
    {
        $profiler = dom_debug_track_timing($username.": ".$board);
        
        if ($token    === false && !defined("TOKEN_PINTEREST")) return array();
        if ($username === false && !dom_has("pinterest_user"))  return array();
        if ($board    === false && !dom_has("pinterest_board")) return array();
        
        $token      = ($token    === false) ? TOKEN_PINTEREST    : $token;
        $username   = ($username === false) ? dom_get("pinterest_user")     : $username;
        $board      = ($board    === false) ? dom_get("pinterest_board")    : $board;
        $end_point  = "https://api.pinterest.com/v1/boards/".$username."/".$board."/pins/?access_token=".$token; // EXTERNAL ACCESS
        
        $result = dom_array_open_url($end_point, "json");

        if (dom_at($result, "status") == "failure")
        {
            return array();
        }
        
        return $result;
    }
    
    function json_tumblr_blog($blogname = false, $method = "info", $token = false)
    {
        $profiler = dom_debug_track_timing($blogname);
        
        if ($token    === false && !defined("TOKEN_TUMBLR")) return array();
        if ($blogname === false && !dom_has("tumblr_blog"))  return array();
        
        $blogname   = ($blogname === false) ? dom_get("tumblr_blog") : $blogname;
        $token      = ($token    === false) ? TOKEN_TUMBLR       : $token;    
        $end_point  = "https://api.tumblr.com/v2/blog/$blogname.tumblr.com/$method?api_key=$token"; // EXTERNAL ACCESS
        
        return dom_array_open_url($end_point, "json");
    }

    function endpoint_facebook($username = false, $fields_page = false, $fields_post = false, $fields_attachements = false, $token = false)
    {                   
        $profiler = dom_debug_track_timing($username);

        if ($token    === false && !defined("TOKEN_FACEBOOK")) return false;
        if ($username === false && !dom_has("facebook_page"))  return false;

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
        $profiler = dom_debug_track_timing($username);        
        $end_point = endpoint_facebook($username, $fields_page, $fields_post, $fields_attachements, $token);
        if ($end_point === false) return array();
        
        $result = dom_array_open_url($end_point, "json");
        /*
        if ((false !== $username) && ((false === $result) || (dom_at(dom_at($result, "meta"),  "code", "") == "200") 
                                                          || (dom_at(dom_at($result, "error"), "code", "") ==  200 ) 
                                                          || (dom_at(dom_at($result, "error"), "code", "") ==   10 )))
        {
            $result = array("data" => array());
        
        //  $json_articles_page = json_facebook_from_content("https://www.facebook.com/pg/".dom_get("facebook_page")."/posts/?ref=page_internal");
        //  $json_articles_page = dom_at($json_articles_page, "require", array());
            $json_articles_page = json_facebook_from_content("https://www.facebook.com/".dom_get("facebook_page"));

            return array_merge(array("DEBUG" => "TEST"), is_array($json_articles_page) ? $json_articles_page : array($json_articles_page));
            
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
        }*/
        
        return $result;
    }
    
    function json_facebook_post($post_id, $username = false, $fields_post = false, $fields_attachements = false, $token = false)
    {
        $profiler = dom_debug_track_timing($username.": ".$post_id);
        
        if ($token    === false && !defined("TOKEN_FACEBOOK"))  return array();
        if ($username === false && !dom_has("facebook_page"))   return array();
        
        $username               = ($username            === false) ? dom_get("facebook_page")  : $username;
        $fields_attachements    = ($fields_attachements === false) ? array("media","url") : ((!is_array($fields_attachements)) ? array($fields_attachements) : $fields_attachements);
        $fields_post            = ($fields_post         === false) ? array("message","description","caption","full_picture","link","attachments%7B".implode('%2C', $fields_attachements)."%7D","created_time","from") : ((!is_array($fields_post)) ? array($fields_post) : $fields_post);
        $token                  = ($token               === false) ? TOKEN_FACEBOOK : $token;
        $end_point              = "https://graph.facebook.com/v2.10/".$post_id."?access_token=".$token."&fields=".implode('%2C', $fields_post); // EXTERNAL ACCESS

        return dom_array_open_url($end_point, "json");
    }
        
    function json_facebook_from_content($url)
    {/*
        $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36'));
        $context = stream_context_create($options);
        $html    = @file_get_contents($url, false, $context);
    */
        $html = dom_array_open_url($url, "html");

        return array($url, htmlentities($html));

        if ($html)
        {   
            while (true)
            {/*
                $tag_bgn = '<div id="globalContainer" class="uiContextualLayerParent">';
                $tag_end = '</body>';
                
                $pos_bgn = stripos($html, $tag_bgn, 0);                             if (false == $pos_bgn) break;
                $pos_end = stripos($html, $tag_end, $pos_bgn + strlen($tag_bgn));   if (false == $pos_bgn) break;
                
                $html =  substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));

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

                echo "<pre>";
                print_r($result);
                echo "</pre>";*/

                $tag_bgn = "Bootloader.setResourceMap(";
                $tag_end = '</script>';
                
                $pos_bgn = stripos($html, $tag_bgn, 0);                             if (false == $pos_bgn) break;
                $pos_end = stripos($html, $tag_end, $pos_bgn + strlen($tag_bgn));   if (false == $pos_bgn) break;
                
                $json =  substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));

                $result = json_decode($json);

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
        $profiler = dom_debug_track_timing($page);
        
        if ($token  === false && !defined("TOKEN_FACEBOOK"))    return array();
        if ($page   === false && !dom_has("facebook_page"))     return array();
        
        $token  = ($token   === false) ? TOKEN_FACEBOOK        : $token;
        $page   = ($page    === false) ? dom_get("facebook_page")  : $page;

        $end_points = array
        (
         /* "https://graph.facebook.com/"      .$page."/instant_articles?access_token=".$token // EXTERNAL ACCESS
        ,*/ "https://graph.facebook.com/v2.10/".$page."/instant_articles?access_token=".$token // EXTERNAL ACCESS
        );

        $result = dom_array_open_url($end_points, "json");
        
        if ((false !== $page) && ((false === $result) || (dom_at(dom_at($result, "meta"),  "code", "") == "200") 
                                                      || (dom_at(dom_at($result, "error"), "code", "") ==  200 )))
        {
            $result = array("data" => array());
        
            $json_articles_page = json_facebook_articles_from_content("https://www.facebook.com/pg/".dom_get("facebook_page")."/notes/?ref=page_internal");
            $json_articles_page = dom_at($json_articles_page, "require");
            
            if (is_array($json_articles_page)) foreach ($json_articles_page as $entry)
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
        $profiler = dom_debug_track_timing($username.": ".$article);

        return json_facebook_article_from_content("https://www.facebook.com".$article);
    }
    
    function json_instagram_from_content($url)
    {
        $html = dom_content($url);
            
        if ($html)
        {
            $tag_bgn = '<script type="text/javascript">window._sharedData = ';
            $tag_end = ';</script>';
            
            $pos_bgn = strpos($html, $tag_bgn);
            $pos_end = strpos($html, $tag_end, $pos_bgn);
            
            if (false !== $pos_bgn && false !== $pos_end)
            {
                $json = substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));

                $result = json_decode($json, true);

                return $result;
            }
        }
        
        return false;
    }
        
    function json_instagram_medias($username = false, $token = false, $tag = false, $limit = false, $post_filter = "", $tags_in = false, $tags_out = false)
    {
        $profiler = dom_debug_track_timing($username);

        if ($token    === false && !defined("TOKEN_INSTAGRAM")) return array();
        if ($username === false && !dom_has("instagram_user"))  return array();
        
        $token      = ($token    === false) ? TOKEN_INSTAGRAM           : $token;
        $username   = ($username === false) ? dom_get("instagram_user") : $username;
        $tag        = ($tag      === false) ? dom_get("instagram_tag")  : $tag;

        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);

        $end_points = array
        (
            "https://api.instagram.com/v1/users/" . "self"      . "/media/recent/?access_token=$token" // EXTERNAL ACCESS
        ,   "https://api.instagram.com/v1/users/" . $username   . "/media/recent/?access_token=$token" // EXTERNAL ACCESS
        ,   "https://api.instagram.com/v1/tags/"  . $tag        . "/media/recent/?access_token=$token" // EXTERNAL ACCESS
        );

        $result = dom_array_open_url($end_points, "json");

        // DEBUG -->
        /*
        $result = array_merge($result, array("data" => array(array
        (
            "id"    => "666"
        ,   "user"  => array
            (
                "full_name"         => "John Doe"
            ,   "username"          => "Johnny"
            ,   "profile_picture"   => "https://www.villepreux.net/image.jpg"
            )
        ,   "caption" => array
            (
                "text" => "Loremp ipsum est!"
            )
        ,   "created_time"  => date("d/m/Y")
        ,   "link"          => "https://www.villepreux.net"
        ,   "images"        => array
            (
                "low_resolution" => array
                (
                    "url" => "https://www.villepreux.net/image.jpg"
                )
            )

        ))));
        */
        // DEBUG -->

        $could_not_access_account = (false === $result || dom_at(dom_at($result, "meta"), "code", "") == "200");
        
        if (/*(false !== $tag) &&*/ $could_not_access_account)
        {
            $tag = $username;

            $nb_parsed_pages = 0;

            foreach (array("username_json", "username_json_html", "tag_json", "username_html", "tag_html") as $mode)
            {
                $page_url = false;

                if ($mode == "username_json")       $page_url = "https://www.instagram.com/$username?__a=1";
                if ($mode == "username_json_html")  $page_url = "https://www.instagram.com/$username?__a=1";
                if ($mode == "tag_json")            $page_url = "https://www.instagram.com/explore/tags/$tag?__a=1";
                if ($mode == "tag_html")            $page_url = "https://www.instagram.com/explore/tags/$tag";
                if ($mode == "username_html")       $page_url = "https://www.instagram.com/$username";
                
                while (!!$page_url)
                {
                    $json_tag_page = false;

                    if ($mode == "username_json")       $json_tag_page = dom_array_open_url($page_url, "json");
                    if ($mode == "username_json_html")  $json_tag_page = json_instagram_from_content($page_url);
                    if ($mode == "tag_json")            $json_tag_page = dom_array_open_url($page_url, "json");
                    if ($mode == "tag_html")            $json_tag_page = json_instagram_from_content($page_url);
                    if ($mode == "username_html")       $json_tag_page = json_instagram_from_content($page_url);

                    if (!$json_tag_page) 
                    {
                        if (!!get("debug")) echo comment("FAILED TO FETCH INSTAGRAM CONTENT $mode $page_url");
                        break;
                    }
                //   else
                //  {
                //      if (!!get("debug")) echo comment("SUCCESS TO FETCH INSTAGRAM CONTENT $mode $page_url");
                //      if (!!get("debug")) echo comment(dom_to_string($json_tag_page));
                //  }

                    ++$nb_parsed_pages;

                    $edges  = false;
                    $paging = false;

                    if ($mode == "tag_html")            $edges  = dom_at($json_tag_page, array("entry_data","TagPage",0,"graphql","hashtag",    "edge_hashtag_to_media",        "edges"));
                    if ($mode == "tag_html")            $paging = dom_at($json_tag_page, array("entry_data","TagPage",0,"graphql","hashtag",    "edge_hashtag_to_media",        "page_info"));

                    if ($mode == "username_json_html")  $edges  = dom_at($json_tag_page, array("entry_data","TagPage",0,"graphql","user",       "edge_owner_to_timeline_media", "edges"));
                    if ($mode == "username_json_html")  $paging = dom_at($json_tag_page, array("entry_data","TagPage",0,"graphql","user",       "edge_owner_to_timeline_media", "page_info"));

                    if ($mode == "username_html")       $edges  = dom_at($json_tag_page, array("entry_data","TagPage",0,"graphql","user",       "edge_owner_to_timeline_media", "edges"));
                    if ($mode == "username_html")       $paging = dom_at($json_tag_page, array("entry_data","TagPage",0,"graphql","user",       "edge_owner_to_timeline_media", "page_info"));

                    if ($mode == "tag_json")            $edges  = dom_at($json_tag_page, array(                         "graphql","hashtag",    "edge_hashtag_to_media",        "edges"));
                    if ($mode == "tag_json")            $paging = dom_at($json_tag_page, array(                         "graphql","hashtag",    "edge_hashtag_to_media",        "page_info"));

                    if ($mode == "username_json")       $edges  = dom_at($json_tag_page, array(                         "graphql","user",       "edge_owner_to_timeline_media", "edges"));
                    if ($mode == "username_json")       $paging = dom_at($json_tag_page, array(                         "graphql","user",       "edge_owner_to_timeline_media", "page_info"));

                    if (!is_array($edges)) break;

                    $page_url = false;

                    if ($mode == "username_json")       $page_url = !!dom_at($paging,"has_next_page") ? ("https://www.instagram.com/$username"          ."?__a=1&max_id=".dom_at($paging,"end_cursor")) : false;
                    if ($mode == "username_json_html")  $page_url = !!dom_at($paging,"has_next_page") ? ("https://www.instagram.com/$username"          ."?__a=1&max_id=".dom_at($paging,"end_cursor")) : false;
                    if ($mode == "tag_json")            $page_url = !!dom_at($paging,"has_next_page") ? ("https://www.instagram.com/explore/tags/$tag"  ."?__a=1&max_id=".dom_at($paging,"end_cursor")) : false;
                    if ($mode == "tag_html")            $page_url = false;
                    if ($mode == "username_html")       $page_url = false;
                    
                    $result = array("data" => array());
                
                    foreach ($edges as $edge)
                    {
                        $node = dom_at($edge,"node");
                    
                        $post_url = url_instagram_post(dom_at($node, "shortcode"));
                    
                        $owner = dom_at(json_instagram_from_content($post_url), array("entry_data","PostPage",0,"graphql","shortcode_media","owner"));
                        
                        $item = array
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
            
                        $filtered  = dom_at($item, "id")   == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
                        $excluded  = in_array(dom_at($item,"id"), explode(',',dom_get("exclude_instagram_codes", "")));
                        $excluded  = $excluded || in_array(dom_at(dom_at($item,"user"),"full_name"), explode(',',dom_get("exclude_instagram_users", "")));
                        $item_tags = dom_array_hashtags(dom_get(dom_get($item, "caption"), "text"));           
                        $tagged    = dom_is_array_filtered($item_tags, $tags_in, $tags_out);

                        if (!$filtered || $excluded || !$tagged) continue;

                        $result["data"][] = $item;
        
                        if (false !== $limit && count($result["data"]) >= $limit) break;
                    }

                    if (false !== $limit && count($result["data"]) >= $limit) break;

                //  if ($nb_parsed_pages > 16) break; // Arbitrary hard limit
                }

                if (count($result["data"]) > 0) break;
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
        
        $json = dom_array_open_url($end_point, "json");

        return $json;
    }
    
    function json_flickr_no_user_fallback($method, $params = array(), $user_id = false, $token = false)
    {
        $profiler = dom_debug_track_timing($user_id);
        
        if ($token === false && !defined("TOKEN_FLICKR")) return array();

        if (false !== $user_id)
        {
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
            
            $params = array_merge($params, array("user_id" => $user_id));
        }

        return __json_flickr($method, $params, $token);
    }
    
    function json_flickr($method, $params = array(), $user_id = false, $token = false)
    {
        $profiler = dom_debug_track_timing($user_id);
        
        if ($user_id === false && !dom_has("flickr_user"))  return array();
        $user_id = ($user_id === false) ? dom_get("flickr_user") : $user_id;

        return json_flickr_no_user_fallback($method, $params, $user_id, $token);
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
    #region WIP HELPERS : JSON METADATA FROM SOCIAL NETWORKS 
    ######################################################################################################################################
    
    function sort_cmp_post_timestamp($a,$b)
    {
        return (int)dom_at($a,"post_timestamp",0) < (int)dom_at($b,"post_timestamp",0);
    }
    
    function array_socials_posts($sources = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        $profiler = dom_debug_track_timing();
        
        $posts = array();
        
        $social_index = 0;
        
        if ($sources !== false && !is_array($sources)) $sources = array($sources);
        if ($sources === false)                        $sources = array();
        
        foreach ($sources as $source)
        {   
            $source        = explode(":", $source);
            $social_source = dom_at($source, 0);
            $username      = dom_at($source, 1);

            if (0 === stripos($username,"#")) { $tags_in = substr($username,1); $username = false; }

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
                echo "UNDEFINED SOCIAL SOURCE: ".dom_to_string($sources).dom_to_string($filter);
            }
            
            ++$social_index;
        }
        
        usort($posts, "sort_cmp_post_timestamp");
     
        return $posts;
    }
    
    function array_socials_thumbs($sources = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        $profiler = dom_debug_track_timing();
        
        $posts = array();
        
        $social_index = 0;
        
        if ($sources !== false && !is_array($sources)) $sources = array($sources);
        if ($sources === false)                        $sources = array();
        
        foreach ($sources as $source)
        {   
            $source        = explode(":", $source);
            $social_source = dom_at($source, 0);
            $username      = dom_at($source, 1);

            if (0 === stripos($username,"#")) { $tags_in = substr($username,1); $username = false; }

            // TODO handle the case of username that should contain multiple identifier (ex. pinterest)
            
            if (is_callable("array_".$social_source."_thumbs"))
            {
                $source_posts = call_user_func("array_".$social_source."_thumbs", $username, $filter, $tags_in, $tags_out);
                
                if (is_array($source_posts))
                {
                    $posts = array_merge($posts, $source_posts);
                }
            }
            else if (!!dom_get("debug"))
            {
                echo "UNDEFINED SOCIAL SOURCE: ".dom_to_string($sources).dom_to_string($filter);
            }
            
            ++$social_index;
        }
        
        usort($posts, "sort_cmp_post_timestamp");
        
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
            
            while ($message[$pos_end_line] == $pattern[0])
            {
                ++$pos_end_line;
            }
            
            $message = substr($message, 0, $pos_line) . $line . substr($message, $pos_end_line);
            
            $pos_line = $pos_end_line;
        }
        
        return $message;
    }
        
    function array_instagram_posts($username, $post_filter = "", $tags_in = false, $tags_out = false, $hooks = true)
    {
        $profiler = dom_debug_track_timing();
        
        $content = json_instagram_medias(($username === false) ? dom_get("instagram_user") : $username, false, false, dom_get("page") * dom_get("n"), $post_filter, $tags_in, $tags_out);
        $posts   = array();

        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);

        foreach (dom_at($content, "data",  array()) as $item)
        {
            if (!dom_pagination_is_within()) continue;
            
            $filtered  = dom_at($item, "id")   == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
            $excluded  = in_array(dom_at($item,"id"), explode(',',dom_get("exclude_instagram_codes", "")));
            $excluded  = $excluded || in_array(dom_at(dom_at($item,"user"),"full_name"), explode(',',dom_get("exclude_instagram_users", "")));
            $item_tags = dom_array_hashtags(dom_get(dom_get($item, "caption"), "text"));           
            $tagged    = dom_is_array_filtered($item_tags, $tags_in, $tags_out);

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
            
            if (dom_get("facebook_posts_no_duplicate_titles") && in_array(dom_clean_title($title), explode('§', dom_get("facebook_posts" )))) continue;
            if (dom_get("facebook_posts_no_duplicate_titles") && in_array(dom_clean_title($title), explode('§', dom_get("instagram_posts")))) continue;
            
            $metadata = array
            (
                "TYPE"              => "instagram"
            ,   "user_name"         => $item["user"]["full_name"]
            ,   "user_url"          => url_instagram_user($item["user"]["username"])
            ,   "user_img_url"      => $item["user"]["profile_picture"]
            ,   "post_title"        => $title
            ,   "post_text"         => $post_message
            ,   "post_timestamp"    => $item["created_time"]
            ,   "post_url"          => $item["link"]
            ,   "post_img_url"      => $images
            ,   "DEBUG_SOURCE"      => ((!!dom_get("debug")) ? $item : "")
            ,   "LAZY"              => true
            );
            
            if (!!$hooks) dom_pagination_add($metadata);

            $posts[] = $metadata;
        }
        
        return $posts;
    }
    
    function array_instagram_post($username = false, $post_id = "", $tags_in = false, $tags_out = false)
    {
        $profiler = dom_debug_track_timing();

    //  if ($post_id === "" || $post_id === false)
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
    /*
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

        return $metadata;*/
    }
    
    function array_flickr_posts($username = false, $photo_key = false, $tags_in = false, $tags_out = false)
    {
        $profiler = dom_debug_track_timing();
        
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

        if ($username === false && false !== $tags_in)
        {
            $data   = json_flickr_no_user_fallback("photos.search", array("tags" => $tags_in)); 
            $photos = dom_at(dom_at($data,"photos"),"photo");
        }
        else
        {        
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
            $photo_owner    = dom_at($photo_nth, "owner",   $username);
            $photo_size     = "b";
            $photo_url      = "https://farm".$photo_farm.".staticflickr.com/".$photo_server."/".$photo_id."_".$photo_secret."_".$photo_size.".jpg";

            $data = json_flickr("photos.getInfo", array("photo_id" => $photo_id), $photo_owner);

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
            ,   "post_text"         => $photo_description
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
    
    function array_flickr_thumbs($username, $post_filter = "", $tags_in = false, $tags_out = false)
    {   
        $posts = array_flickr_posts($username, $post_filter, $tags_in, $tags_out, false);     

        foreach ($posts as &$post)
        {
            unset($post["user_name"     ]);
            unset($post["user_url"      ]);
            unset($post["user_img_url"  ]);
            unset($post["post_title"    ]);
            unset($post["post_text"     ]);
            unset($post["post_timestamp"]);

            $post["post_title"] = "";
            hook("thumb", $post);
            unset($post["post_title"]);
        }
        
        return $posts;
    }
    
    function array_instagram_thumb($username, $post_filter = "", $tags_in = false, $tags_out = false)
    {
        return array_instagram_thumbs($username, $post_filter, $tags_in, $tags_out);
    }
    
    function array_instagram_thumbs($username, $post_filter = "", $tags_in = false, $tags_out = false)
    {   
        $posts = array_instagram_posts($username, $post_filter, $tags_in, $tags_out, false);     

        foreach ($posts as &$post)
        {
            unset($post["user_name"     ]);
            unset($post["user_url"      ]);
            unset($post["user_img_url"  ]);
            unset($post["post_title"    ]);
            unset($post["post_text"     ]);
            unset($post["post_timestamp"]);

            $post["post_title"] = "";
            hook("thumb", $post);
            unset($post["post_title"]);
        }

        return $posts;
        
        /*

        $profiler = dom_debug_track_timing();
          
        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);
    //  $content    = json_instagram_medias(($username === false) ? dom_get("instagram_user") : $username, false, false, false,                          $post_filter, $tags_in, $tags_out);
        $content    = json_instagram_medias(($username === false) ? dom_get("instagram_user") : $username, false, false, dom_get("page") * dom_get("n"), $post_filter, $tags_in, $tags_out);
                
        $thumbs     = array();

        foreach (dom_at($content, "data",  array()) as $item)
        {
            $item_tags = dom_array_hashtags(dom_get(dom_get($item, "caption"), "text"));
            
            $filtered = $item["id"]   == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
            $excluded = in_array(dom_get($item,"id"),   explode(',',dom_get("exclude_instagram_codes", "")));
            $tagged   = dom_is_array_filtered($item_tags, $tags_in, $tags_out);
            
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

        */
    }
    
    function array_tumblr_posts($blogname = false, $post = "", $tags_in = false, $tags_out = false)
    {
        $profiler = dom_debug_track_timing();
        
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
            $tagged   = dom_is_array_filtered(dom_at($item, "tags", array()), $tags_in, $tags_out);            
            $indirect = ((false !== stripos(dom_get($item, "link_url"),      "instagram.com")) 
                      || (false !== stripos(dom_get($item, "permalink_url"), "instagram.com"))) && (dom_has("instagram_posts") /*|| (dom_get("filter", "default") == "default")*/);
                    
            $indirect = $indirect || in_array(dom_clean_title($post_title), explode('§', dom_get("facebook_posts")));
            $indirect = $indirect || in_array(dom_clean_title($post_title), explode('§', dom_get("instagram_posts")));

            if (!$filtered || $excluded || !$tagged || $indirect) continue;
            
            $post_source_url = (dom_get("check_target_url", false)) ? ((false === dom_array_open_url(dom_get($item, "link_url"), "json")) ? dom_get($item, "post_url") : dom_get($item, "link_url")) : dom_at($item, "link_url", dom_at($item, "post_url"));
    
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
        $profiler = dom_debug_track_timing();
        
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
        $profiler = dom_debug_track_timing();
        
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
        $profiler = dom_debug_track_timing();

        $username   = ($username === false) ? dom_get("facebook_page")  : $username;        
        $content    = json_facebook($username, array("id","name","about","mission","hometown","website","cover","picture"));
        $posts      = array();
        /*
        return array(array
        (
            "TYPE"              => "facebook"
        ,   "user_name"         => dom_get("name")
        ,   "user_url"          => dom_get("url")
        ,   "user_img_url"      => "image.jpg"
        ,   "post_title"        => dom_get("title")
        ,   "post_text"         => dom_get("description")
        ,   "post_timestamp"    => strtotime(date("Y/m/d", time()))
        ,   "post_url"          => dom_get("url")
        ,   "post_img_url"      => "image.jpg"
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
            
            if (!dom_is_array_filtered(array_merge($post_article_tags, dom_array_hashtags($post_message)), $tags_in, $tags_out)) continue;
            
            if (0 === strpos($post_message, dom_get("instagram_user")) && instagram_posts_presence()) continue;
            if (0 === strpos($post_message, dom_get("instagram_user"))) $post_message = substr($post_message, strlen(dom_get("instagram_user")));
            
        //  if (dom_get("facebook_posts_no_duplicate_titles") && in_array(dom_clean_title($post_title), explode('§', dom_get("facebook_posts" )))) continue;
        //  if (dom_get("facebook_posts_no_duplicate_titles") && in_array(dom_clean_title($post_title), explode('§', dom_get("instagram_posts")))) continue;
            
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
        $profiler = dom_debug_track_timing();
           
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
            
            if (!dom_is_array_filtered(dom_array_hashtags($post_message), $tags_in, $tags_out)) continue;
            
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
        $profiler = dom_debug_track_timing();
        
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
            $tagged   = dom_is_array_filtered(dom_array_hashtags(dom_get($item, "message")), $tags_in, $tags_out);    
            $indirect = (false !== stripos(dom_get($item, "caption"), "instagram.com")) && (instagram_posts_presence());
            
            if ((false !== stripos(dom_at(dom_at(dom_at($item,"attachments"),"data"),"url", 
                            dom_at(dom_at(dom_at(dom_at($item,"attachments"),"data"),0),"url")), "instagram.com")) && (instagram_posts_presence())) continue;
               
            if (!$filtered || $excluded || !$tagged || $indirect) continue;
            
            $item_post    = json_facebook_post(dom_at($item,"id"), $username);                   
            $post_message = dom_at($item_post, "message", dom_get($item, "description"));
            
            if (0 === strpos($post_message, dom_get("instagram_user")) && instagram_posts_presence()) continue;
            
            $post_title = extract_start($post_message);
            
            if (in_array(dom_clean_title($post_title), explode('§', dom_get("facebook_posts")))) continue;
            
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
        $profiler = dom_debug_track_timing();
        
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
        $profiler = dom_debug_track_timing();

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
        
        foreach (dom_at(dom_array_open_url($url, "xml"), array("channel","item"), array()) as $item)
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
    #region WIP HELPERS : DEBUG
    ######################################################################################################################################
    
    function raw_array_debug($content, $html_entities = false, $fields_sep = " ")
    {
        $content = (!is_array($content))          ? $content : ((defined("JSON_PRETTY_PRINT")) ? json_encode($content, JSON_PRETTY_PRINT) : json_encode($content));
        $content = (!$html_entities)              ? $content : htmlentities($content);
        $content = (defined("JSON_PRETTY_PRINT")) ? $content : str_replace("{", "\n{\n", str_replace("[", "\n[\n", str_replace("}", "\n}\n", str_replace("]", "\n]\n", str_replace(":", ": ", str_replace(",", ",".$fields_sep, $content))))));

        return $content;
    }
    
    #endregion
    #region WIP HELPERS : MINIFIERS (QUICK AND DIRTY)
    ######################################################################################################################################

    function minify_html($html)
    {
        return trim(dom_str_replace_all(array("\r\n","\r","\t","\n",'  ','    ','     '), ' ', $html));
    }

    function minify_js($js)
    {
        if (false !== stripos($js, "//")) return $js;
        
        $js = dom_str_replace_all("\n  ",   "\n ",  $js);
        $js = dom_str_replace_all(PHP_EOL,  " ",    $js);
        $js = dom_str_replace_all("\n",     " ",    $js);
        
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
    #region WIP API : CACHE SYSTEM
    ######################################################################################################################################

    function cache_start()
    {
        if (!!dom_get("cache"))
        {
            $cache_dir = dom_path("cache");

            if ($cache_dir)
            {
                if (dom_has("cache_reset") && is_dir("/cache")) foreach (array_diff(scandir($cache_dir), array('.','..')) as $basename) @unlink("$cache_dir/$basename");

                $cache_basename         = md5(dom_url(true) . DOM_VERSION);
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

                        echo dom_eol().comment("Cached copy, $cache_filename, generated ".date('Y-m-d H:i', filemtime($cache_filename)));
                    }
                    else
                    {
                        echo dom_eol().comment("Could not read cached copy, $cache_filename, generated ".date('Y-m-d H:i', filemtime($cache_filename)));
                    }

                    exit;
                }
            }
            else
            {
                // Could not find cache directory
                dom_set("cache", false);
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
                if ("html" == dom_get("doctype",false)) echo dom_eol().comment("Could not generate cache! " . dom_get("cache_filename"));
            }
            
            ob_end_flush();
        }
    }
    
    #endregion
    #region WIP API : PHP DOCUMENT
    ######################################################################################################################################

    function dom_redirect($url)
    {   
        if (!!get("static")) // PHP redirect does not work for static website
        {
            echo "<html><head><meta http-equiv=\"refresh\" content=\"0; URL='".dom_href($url)."'\" /></head></html>";
        }
        else
        {
            header("Location: ".dom_href($url));
        }

        exit;
    }
    
    function dom_redirect_https()
    {
        if (dom_has("ajax")) return;

        if (!dom_is_localhost() && dom_server_https() != "on")
        {
            $url  = "https://";
            $url .=  dom_server_server_name();
            $url .= (dom_server_server_port("80") != "80" 
                  && dom_server_server_port("80") != "443") ? (":".dom_server_server_port()) : "";
            $url .=  dom_server_request_uri();

            dom_redirect($url);
        }
    }

    dom_init_php();
    dom_init_options();
    dom_init_internals();

    function dom_init($doctype = false, $encoding = false, $content_encoding_header = true, $attachement_basename = false, $attachement_length = false)
    {
        if ($doctype    === false) { $doctype   = "html";  }
        if ($encoding   === false) { $encoding  = "utf-8"; }

        $rss = (dom_has("rss") && (dom_get("rss") == ""
                               ||  dom_get("rss") ==  false
                               ||  dom_get("rss") === true)) ? "rss" : dom_get("rss");

        $doctype                = dom_get("doctype",        dom_has("rss") ? $rss         : $doctype    );
        $encoding               = dom_get("encoding",       dom_has("iso") ? "ISO-8859-1" : $encoding   );
        $attachement_basename   = dom_get("attachement",    $attachement_basename                       );

        if ($doctype    === false) { $doctype   = "html"; }
        if ($encoding   === false) { $encoding  = "utf-8"; }

        $binary_types = array("png");
        $binary = in_array($doctype, $binary_types);

        dom_set("doctype",  $doctype);
        dom_set("encoding", $encoding);
        dom_set("binary",   $binary);        

        $types = array
        (
            "xml"       => 'text/xml'    
        ,   "rss"       => 'text/xml'
        ,   "tile"      => 'text/xml'
        ,   "png"       => 'image/png'
        ,   "json"      => 'application/json'
        ,   "html"      => 'text/html'
        ,   "css"       => 'text/css'
        ,   "js"        => 'text/javascript'
        ,   "csv"       => 'text/csv'           . (($attachement_basename !== false) ? ('; name="'      . $attachement_basename . '.csv') : '')
        ,   "zip"       => 'application/zip'    . (($attachement_basename !== false) ? ('; name="'      . $attachement_basename . '.zip') : '')
        );
    
        $dispositions = array
        (
            "csv"   => 'attachment'         . (($attachement_basename !== false) ? ('; filename="'  . $attachement_basename . '.csv"') : '')
        ,   "zip"   => 'attachment'         . (($attachement_basename !== false) ? ('; filename="'  . $attachement_basename . '.zip"') : '')
        );

        $type = $doctype;
        {
            if (!array_key_exists($type, $types))
            {
                foreach (array("html","xml","png","json","csv","zip") as $t)
                {
                    if (false !== stripos($doctype, "/$t")) $type = $t;
                }
            }
        }

        if (!$binary && $content_encoding_header !== false)  header('Content-Encoding: ' . $encoding      . '');
        if (array_key_exists($type, $types))                 header('Content-type: '     . $types[$type]  . '; charset=' . $encoding);

        if ($attachement_basename !== false)
        {
            if (array_key_exists($type, $dispositions))     @header('Content-Disposition: ' . $dispositions[$type]                                                                               . '');
            if ($attachement_length !== false)              @header('Content-Length: '      . (($attachement_length !== true) ? $attachement_length : filesize($attachement_basename . '.zip"')) . '');
        }
        
        generate_all_preprocess();

        if (!$binary) cache_start();
    }

    function dom_output($doc = "")
    {           
        if (!!dom_get("binary"))
        {
            die($doc);
        }
        else
        {
            if ("html" == dom_get("doctype", false))
            {
                if (false === stripos($doc, "<html") && !dom_has("ajax")) $doc = html($doc);
            }

            if (false !== stripos($doc, "DOM_HOOK_RSS_1"      )) $doc = str_replace(dom_placeholder("DOM_HOOK_RSS_1"       ), _rss      (true), $doc);
            if (false !== stripos($doc, "DOM_HOOK_JSONFEED_1" )) $doc = str_replace(dom_placeholder("DOM_HOOK_JSONFEED_1"  ), _jsonfeed (true), $doc);
            if (false !== stripos($doc, "DOM_HOOK_TILE_1"     )) $doc = str_replace(dom_placeholder("DOM_HOOK_TILE_1"      ), _tile     (true), $doc);
            
            if (false !== stripos($doc, "DOM_HOOK_RSS_0"      )) $doc = str_replace(dom_placeholder("DOM_HOOK_RSS_0"       ), _rss      (false), $doc);
            if (false !== stripos($doc, "DOM_HOOK_JSONFEED_0" )) $doc = str_replace(dom_placeholder("DOM_HOOK_JSONFEED_0"  ), _jsonfeed (false), $doc);
            if (false !== stripos($doc, "DOM_HOOK_TILE_0"     )) $doc = str_replace(dom_placeholder("DOM_HOOK_TILE_0"      ), _tile     (false), $doc);
        
            $doc = str_replace(dom_placeholder("DOM_HOOK_RSS_1"       ), "", $doc);
            $doc = str_replace(dom_placeholder("DOM_HOOK_JSONFEED_1"  ), "", $doc);
            $doc = str_replace(dom_placeholder("DOM_HOOK_TILE_1"      ), "", $doc);
        
            $doc = str_replace(dom_placeholder("DOM_HOOK_RSS_0"       ), "", $doc);
            $doc = str_replace(dom_placeholder("DOM_HOOK_JSONFEED_0"  ), "", $doc);
            $doc = str_replace(dom_placeholder("DOM_HOOK_TILE_0"      ), "", $doc);

            $doc .= generate_all(dom_get("beautify"));

            if (dom_get("compression") == "gzip") ob_start("ob_gzhandler");

            echo $doc;
            
            cache_stop();
        
            if ("html" == dom_get("doctype",false) && !!dom_get("debug"))
            {
                echo dom_eol().comment("PHP Version: ".PHP_VERSION_ID);
                echo dom_eol().comment("DOM Profiling:".PHP_EOL."    ".wrap_each(dom_debug_timings(), PHP_EOL."    ").PHP_EOL);
            }

            generate_all_postprocess();

            if (dom_get("compression") == "gzip") ob_end_flush();
        }
    }

    // Minimal Retro-compatibility

    function doc_header($doctype = false, $encoding = false, $content_encoding_header = true, $attachement_basename = false, $attachement_length = false)
    {
        return dom_init($doctype, $encoding, $content_encoding_header, $attachement_basename, $attachement_length);
    }

    function doc_output($doc = "")
    {
        return dom_output($doc);
    }

    #endregion
    #region WIP DOCUMENTS GENERATION

    function string_ms_browserconfig($beautify = false)
    {
        $eol = $beautify ? cosmetic(dom_eol())           : "";
        $tab = $beautify ? cosmetic(dom_eol().dom_tab()) : "";

        $icon_dims = array(array(70,70),array(150,150),array(310,310),array(310,150));
        $pollings  = 5;

        $xml_icons = "";
        { 
            foreach ($icon_dims as $dim)
            {
                $w = $dim[0];
                $h = $dim[1];

                $path = dom_path(dom_get("icons_path")."ms-icon-".$w."x".$h.".png");

                if ($path)
                {
                    $xml_icons .= $tab.dom_tag((($w==$h)?"square":"wide").$w."x".$h."logo", false, array("src" => $path), true, true);
                }
            }
        }

        $xml_polling = "";
        for ($i = 0; $i < $pollings; ++$i) $xml_polling .= $tab.dom_tag('polling-uri'.(($i>0)?($i+1):""), false, array("src" => htmlentities(dom_get("canonical").'/?rss=tile&id='.($i+1))), true, true);

        return '<?xml version="1.0" encoding="utf-8"?>'.dom_tag('browserconfig', dom_tag('msapplication', 
        
            $eol.dom_tag('tile',            $xml_icons      . $tab . dom_tag('TileColor', dom_get("theme_color"))                                       . $eol).
            $eol.dom_tag('notification',    $xml_polling    . $tab . dom_tag('frequency', 30) . $tab . dom_tag('cycle', 1)                                  . $eol).
            $eol.dom_tag('badge',           $tab . dom_tag('polling-uri', false, array("src"=>'/badge.xml'), true, true) . $tab . dom_tag('frequency', 30)  . $eol).
            $eol
            ));
    }

    function string_ms_badge($beautify = false)
    {
        return dom_tag("badge", false, array("value" => "available"), true, true);
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
            $filename = dom_path(dom_get("icons_path")."android-icon-$w"."x"."$w.png");

            if ($filename)
            {
                $icons[] = array("src"=> $filename, "sizes"=> "$w"."x"."$w", "type"=> "image/png", "density"=> "$density", "purpose"=> "maskable any");
            }
        }

        $start_url = ((is_localhost() ? dom_get("canonical") : "")."/");

        if (false === stripos($start_url, "?")) $start_url .= "?";
        $start_url .= "&utm_source=homescreen";

        $shortcuts = array();

        global $dom_hook_links;

        foreach ($dom_hook_links as $link)
        {
            $title = $link["title"];
            $url   = /*$start_url."/".*/$link["url"];

            if (false === stripos($url, "?")) $url .= "?";
            $url .= "&utm_source=homescreen";
    
            $shortcuts[] = array("name" => $title, "url" => $url);
        }

        $json = array(

            "name"             => dom_get("title"),
            "short_name"       => $short_title,
            
            "background_color" => dom_get("background_color"),
            "theme_color"      => dom_get("theme_color"),

            "shortcuts"        => $shortcuts,
           
            "start_url"        => $start_url,
            "display"          => "standalone",
            
            "related_applications"=> array( 

                array( "platform"=> "web", "url"=> dom_get("canonical") ) 

                ),
           
            "icons"=> $icons
            );

        return $json;
    }

    function string_manifest($beautify = false)
    {
        return  ($beautify && defined("JSON_PRETTY_PRINT")) 
              ? json_encode(json_manifest(), JSON_PRETTY_PRINT)
              : json_encode(json_manifest());
    }

    function string_robots($beautify = false)
    {
        return "User-agent: *".PHP_EOL."Disallow:"; // Do not use dom_eol() since having no line break is not an option here
    }

    function string_human($beautify = false)
    {
        dom_heredoc_start(-3); ?><html><?php dom_heredoc_flush(null); ?>
        
            /* SITE */

            Standards  : HTML5, CSS3
            Language   : French
            Doctype    : HTML5
            Components : DOM.php, Optionnal: MCW, Bootstrap, Spectre, Amp and others
            IDE        : Visual Studio Code
            
        <?php dom_heredoc_flush("raw"); ?></html><?php return dom_heredoc_stop(null);
    }

    function string_loading_svg($force_minify = false, $color = "#FF8800")
    {
        dom_heredoc_start(-2); ?><html><?php dom_heredoc_flush(null); ?>
        
            <svg class="lds-spinner" width="65px" height="65px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; animation-play-state: running; animation-delay: 0s; background: none;">

                <g transform="rotate(0 50 50)"   style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.9s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>
                <g transform="rotate(36 50 50)"  style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.8s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>
                <g transform="rotate(72 50 50)"  style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.7s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>
                <g transform="rotate(108 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.6s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>
                <g transform="rotate(144 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.5s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>
                <g transform="rotate(180 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.4s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>
                <g transform="rotate(216 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.3s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>
                <g transform="rotate(252 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.2s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>
                <g transform="rotate(288 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.1s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>
                <g transform="rotate(324 50 50)" style="animation-play-state: running; animation-delay: 0s;"><rect x="45" y="15" rx="18" ry="6" width="10" height="10" fill="<?= $color ?>" style="animation-play-state: running; animation-delay: 0s;"><animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.0s" repeatCount="indefinite" style="animation-play-state: running; animation-delay: 0s;"></animate></rect></g>

            </svg>
    
        <?php dom_heredoc_flush("raw_html", $force_minify); ?></html><?php return dom_heredoc_stop(null);
    }

    function string_offline_html($force_minify = false)
    {
        dom_heredoc_start(-3); ?><html><?php dom_heredoc_flush(null); ?>
        
            <!doctype html><html>
                <head>
                    <title>Please wait...</title>
                    <meta charset="utf-8" /><meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1" />
                    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
                    <meta http-equiv="content-language" content="en" />
                    <meta name="format-detection" content="telephone=no" />
                    <meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1" />
                    <meta http-equiv="refresh" content="3">
                    <style>
                        body { margin: 0; width: 100vw; text-align: center; color: #DDD; background-color: rgb(30,30,30); font-family: <?= string_system_font_stack("\'") ?>; padding-top: calc(50vh - 2em - 64px); }
                        svg  { opacity: 0; animation: fade-in 3s; } @keyframes fade-in { 0% { opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { opacity: 0; } }
                    </style>
                </head>
                <body>
                    <p>Offline<br>Please wait...</p>
                    <p><?= string_loading_svg($force_minify) ?></p>
                </body>
            </html>

        <?php dom_heredoc_flush("raw_html", $force_minify); ?></html><?php return dom_heredoc_stop(null);
    }

    function string_service_worker_install_js($force_minify = false)
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

            var swsource = "sw.js";

            if ("serviceWorker" in navigator)
            {
                navigator.serviceWorker.register(swsource).then(function(reg)
                {
                    console.log("DOM: AMP ServiceWorker scope: ", reg.scope);
                })
                .catch(function(err)
                {
                    console.log("DOM: AMP ServiceWorker registration failed: ", err);
                });
            };

        <?php dom_heredoc_flush("raw_js", $force_minify); ?></script><?php return dom_heredoc_stop(null);
    }

    function string_service_worker_install()
    {
        dom_heredoc_start(-3); ?><html><?php dom_heredoc_flush(null); ?>

            <!doctype html><html>
                <head>
                    <title>Installing service worker</title>
                    <script type="text/javascript"><?= string_service_worker_install_js(true) ?></script>
                </head>
                <body>
                </body>
            </html>

        <?php dom_heredoc_flush("raw_html"); ?></script><?php return dom_heredoc_stop(null);
    }

    function string_system_font_stack($quote = '"', $condensed = false)
    {
        $fonts = dom_get("font_stack", array(

            'Inter', 'Roboto', '-apple-system',
            'system-ui', 'BlinkMacSystemFont',
            'ui-sans-serif', $quote.'Segoe UI'.$quote,
            $quote.'San Francisco'.$quote,
            'Helvetica', 'Arial', 'sans-serif',
            $quote.'Apple Color Emoji'.$quote,
            $quote.'Segoe UI Emoji'.$quote,
            $quote.'Segoe UI Symbol'.$quote

            ));

        if ($condensed)
        {
            $fonts = array_merge(array(

                $quote.'AvenirNextCondensed-Bold'.$quote,
                $quote.'Futura-CondensedExtraBold'.$quote,
                'HelveticaNeue-CondensedBold',
                $quote.'Ubuntu Condensed'.$quote,
                $quote.'Liberation Sans Narrow'.$quote,
                $quote.'Franklin Gothic Demi Cond'.$quote,
                $quote.'Arial Narrow'.$quote,
                'sans-serif-condensed', 'Arial',
                $quote.'Trebuchet MS'.$quote,
                $quote.'Lucida Grande'.$quote,
                'Tahoma', 'Verdana', 'sans-serif'

                ), $fonts);
        }

        return implode(", ", $fonts);
    }

    function string_loading_svg_src_base64($force_minify = false)
    {
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPgo8c3ZnIHdpZHRoPSI0MHB4IiBoZWlnaHQ9IjQwcHgiIHZpZXdCb3g9IjAgMCA0MCA0MCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjEuNDE0MjE7IiB4PSIwcHgiIHk9IjBweCI+CiAgICA8ZGVmcz4KICAgICAgICA8c3R5bGUgdHlwZT0idGV4dC9jc3MiPjwhW0NEQVRBWwogICAgICAgICAgICBALXdlYmtpdC1rZXlmcmFtZXMgc3BpbiB7CiAgICAgICAgICAgICAgZnJvbSB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIC13ZWJraXQtdHJhbnNmb3JtOiByb3RhdGUoLTM1OWRlZykKICAgICAgICAgICAgICB9CiAgICAgICAgICAgIH0KICAgICAgICAgICAgQGtleWZyYW1lcyBzcGluIHsKICAgICAgICAgICAgICBmcm9tIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKC0zNTlkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICB9CiAgICAgICAgICAgIHN2ZyB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybS1vcmlnaW46IDUwJSA1MCU7CiAgICAgICAgICAgICAgICAtd2Via2l0LWFuaW1hdGlvbjogc3BpbiAxLjVzIGxpbmVhciBpbmZpbml0ZTsKICAgICAgICAgICAgICAgIC13ZWJraXQtYmFja2ZhY2UtdmlzaWJpbGl0eTogaGlkZGVuOwogICAgICAgICAgICAgICAgYW5pbWF0aW9uOiBzcGluIDEuNXMgbGluZWFyIGluZmluaXRlOwogICAgICAgICAgICB9CiAgICAgICAgXV0+PC9zdHlsZT4KICAgIDwvZGVmcz4KICAgIDxnIGlkPSJvdXRlciI+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwwQzIyLjIwNTgsMCAyMy45OTM5LDEuNzg4MTMgMjMuOTkzOSwzLjk5MzlDMjMuOTkzOSw2LjE5OTY4IDIyLjIwNTgsNy45ODc4MSAyMCw3Ljk4NzgxQzE3Ljc5NDIsNy45ODc4MSAxNi4wMDYxLDYuMTk5NjggMTYuMDA2MSwzLjk5MzlDMTYuMDA2MSwxLjc4ODEzIDE3Ljc5NDIsMCAyMCwwWiIgc3R5bGU9ImZpbGw6YmxhY2s7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNNS44NTc4Niw1Ljg1Nzg2QzcuNDE3NTgsNC4yOTgxNSA5Ljk0NjM4LDQuMjk4MTUgMTEuNTA2MSw1Ljg1Nzg2QzEzLjA2NTgsNy40MTc1OCAxMy4wNjU4LDkuOTQ2MzggMTEuNTA2MSwxMS41MDYxQzkuOTQ2MzgsMTMuMDY1OCA3LjQxNzU4LDEzLjA2NTggNS44NTc4NiwxMS41MDYxQzQuMjk4MTUsOS45NDYzOCA0LjI5ODE1LDcuNDE3NTggNS44NTc4Niw1Ljg1Nzg2WiIgc3R5bGU9ImZpbGw6cmdiKDIxMCwyMTAsMjEwKTsiLz4KICAgICAgICA8L2c+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwzMi4wMTIyQzIyLjIwNTgsMzIuMDEyMiAyMy45OTM5LDMzLjgwMDMgMjMuOTkzOSwzNi4wMDYxQzIzLjk5MzksMzguMjExOSAyMi4yMDU4LDQwIDIwLDQwQzE3Ljc5NDIsNDAgMTYuMDA2MSwzOC4yMTE5IDE2LjAwNjEsMzYuMDA2MUMxNi4wMDYxLDMzLjgwMDMgMTcuNzk0MiwzMi4wMTIyIDIwLDMyLjAxMjJaIiBzdHlsZT0iZmlsbDpyZ2IoMTMwLDEzMCwxMzApOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksMjguNDkzOUMzMC4wNTM2LDI2LjkzNDIgMzIuNTgyNCwyNi45MzQyIDM0LjE0MjEsMjguNDkzOUMzNS43MDE5LDMwLjA1MzYgMzUuNzAxOSwzMi41ODI0IDM0LjE0MjEsMzQuMTQyMUMzMi41ODI0LDM1LjcwMTkgMzAuMDUzNiwzNS43MDE5IDI4LjQ5MzksMzQuMTQyMUMyNi45MzQyLDMyLjU4MjQgMjYuOTM0MiwzMC4wNTM2IDI4LjQ5MzksMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxMDEsMTAxLDEwMSk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMy45OTM5LDE2LjAwNjFDNi4xOTk2OCwxNi4wMDYxIDcuOTg3ODEsMTcuNzk0MiA3Ljk4NzgxLDIwQzcuOTg3ODEsMjIuMjA1OCA2LjE5OTY4LDIzLjk5MzkgMy45OTM5LDIzLjk5MzlDMS43ODgxMywyMy45OTM5IDAsMjIuMjA1OCAwLDIwQzAsMTcuNzk0MiAxLjc4ODEzLDE2LjAwNjEgMy45OTM5LDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoMTg3LDE4NywxODcpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTUuODU3ODYsMjguNDkzOUM3LjQxNzU4LDI2LjkzNDIgOS45NDYzOCwyNi45MzQyIDExLjUwNjEsMjguNDkzOUMxMy4wNjU4LDMwLjA1MzYgMTMuMDY1OCwzMi41ODI0IDExLjUwNjEsMzQuMTQyMUM5Ljk0NjM4LDM1LjcwMTkgNy40MTc1OCwzNS43MDE5IDUuODU3ODYsMzQuMTQyMUM0LjI5ODE1LDMyLjU4MjQgNC4yOTgxNSwzMC4wNTM2IDUuODU3ODYsMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxNjQsMTY0LDE2NCk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMzYuMDA2MSwxNi4wMDYxQzM4LjIxMTksMTYuMDA2MSA0MCwxNy43OTQyIDQwLDIwQzQwLDIyLjIwNTggMzguMjExOSwyMy45OTM5IDM2LjAwNjEsMjMuOTkzOUMzMy44MDAzLDIzLjk5MzkgMzIuMDEyMiwyMi4yMDU4IDMyLjAxMjIsMjBDMzIuMDEyMiwxNy43OTQyIDMzLjgwMDMsMTYuMDA2MSAzNi4wMDYxLDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoNzQsNzQsNzQpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksNS44NTc4NkMzMC4wNTM2LDQuMjk4MTUgMzIuNTgyNCw0LjI5ODE1IDM0LjE0MjEsNS44NTc4NkMzNS43MDE5LDcuNDE3NTggMzUuNzAxOSw5Ljk0NjM4IDM0LjE0MjEsMTEuNTA2MUMzMi41ODI0LDEzLjA2NTggMzAuMDUzNiwxMy4wNjU4IDI4LjQ5MzksMTEuNTA2MUMyNi45MzQyLDkuOTQ2MzggMjYuOTM0Miw3LjQxNzU4IDI4LjQ5MzksNS44NTc4NloiIHN0eWxlPSJmaWxsOnJnYig1MCw1MCw1MCk7Ii8+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4K";
    }

    function string_loading_html($force_minify = false)
    {
        dom_heredoc_start(-3); ?><html><?php dom_heredoc_flush(null); ?>

            <!doctype html><html>
                <head>
                    <title>Please wait...</title>
                    <meta charset="utf-8" /><meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1" />
                    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
                    <meta http-equiv="content-language" content="en" />
                    <meta name="format-detection" content="telephone=no" />
                    <meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1" />
                    <meta http-equiv="refresh" content="3">
                </head>
                <body style="margin: 0; width: 100vw; text-align: center; color: #DDD; background-color: rgb(30,30,30); font-family: <?= string_system_font_stack("\'") ?>; padding-top: calc(50vh - 2em - 64px);">
                    <p>OFFLINE<br>Please wait...</p>
                    <p><img alt="Please wait..." src="<?= string_loading_svg_src_base64($force_minify) ?>" /></p>
                </body>
            </html>

        <?php dom_heredoc_flush("raw_html", $force_minify); ?></html><?php return dom_heredoc_stop(null);
    }

    function string_service_worker($beautify = false)
    {
        dom_heredoc_start(-3); ?><script><?php dom_heredoc_flush(null); ?>
    
            importScripts("https://storage.googleapis.com/workbox-cdn/releases/6.1.2/workbox-sw.js");

            if (workbox)
            {   
                const strategy = new workbox.strategies.CacheFirst();
                const urls     = [ "<?= dom_path("offline.html") ?>" ];

                workbox.recipes.warmStrategyCache({urls, strategy});

                workbox.recipes.offlineFallback();
                workbox.recipes.pageCache();
                workbox.recipes.staticResourceCache();
                workbox.recipes.imageCache();
                workbox.recipes.googleFontsCache();
            } 
            else 
            {
                console.log("DOM: Could not load workbox framework!");
            }

        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    $__dom_generated = array(

        array("path" => "manifest.json",                 "generate" => false, "function" => "string_manifest"),
        array("path" => "browserconfig.xml",             "generate" => false, "function" => "string_ms_browserconfig"),
        array("path" => "badge.xml",                     "generate" => false, "function" => "string_ms_badge"),
        array("path" => "robots.txt",                    "generate" => false, "function" => "string_robots"),
        array("path" => "human.txt",                     "generate" => false, "function" => "string_human"),
        array("path" => "loading.svg",                   "generate" => false, "function" => "string_loading_svg"),
        array("path" => "offline.html",                  "generate" => false, "function" => "string_offline_html"),
        array("path" => "sw.js",                         "generate" => false, "function" => "string_service_worker"),
        array("path" => "install-service-worker.html",   "generate" => false, "function" => "string_service_worker_install")

        );

    function generate_all_preprocess()
    {
        global $__dom_generated;

        foreach ($__dom_generated as &$generated)
        { 
            $generated["generate"] = !!dom_get("generate", true);

          //Unless generation is requested, do not generate each file that is already accessible
          //Even if it accesses a parent/inherited file

            if (!dom_get("generate"))
            {
                if (dom_path($generated["path"]))        { $generated["generate"] = false; continue; }
                if (dom_path($generated["path"].".php")) { $generated["generate"] = false; continue; }
            }
        }
    }

    function generate_all($beautify = false)
    {
        $prev_beautify = false; if ($beautify) { $prev_beautify = dom_get("beautify"); dom_set("beautify", $beautify); }

        global $__dom_generated;

        foreach ($__dom_generated as $generated)
        { 
            if ($generated["generate"])
            {
                $dst_path = $generated["path"];
                
                if (!!dom_get("generate_dst"))
                {
                    $dst_path = dom_get("generate_dst")."/".$generated["path"];
                }

                $f = @fopen($dst_path, "w+");

                if (!$f)
                {
                    error_log("COULD NOT OPEN ".getcwd()."/$dst_path");/*DEBUG*/
                    continue;
                }

                $content = $generated["function"]($beautify);

                fwrite($f, utf8_encode($content));
                fclose($f);
            }
        }

        if ($beautify) { dom_set("beautify", $prev_beautify); }
    }

    function generate_all_postprocess()
    {
    }

    #endregion
    #region WIP API : CSS snippets
    ######################################################################################################################################
    
    function dom_css_gradient($from = "var(--text-color)", $to = "var(--theme-color)")
    {
        return "/* Text gradient */".

            " "."background: linear-gradient(-45deg, $to 0%, $from 100%);".
            " "."color: $from;".
            
        //  " "."display: inline-block;".
            " "."width: fit-content;".

            " "."-webkit-background-clip: text;".
            " ".   "-moz-background-clip: text;".
            " ".     "-o-background-clip: text;".
            " ".        "background-clip: text;".

            "-webkit-text-fill-color: transparent;".
        "";
    }
    
    function dom_css_gradient_unset()
    {
        return "-webkit-text-fill-color: unset;";
    }

    #endregion
    #region WIP API : DOM : URLS
    ######################################################################################################################################

    function url_pinterest_board            ($username = false, $board = false) { $username = ($username === false) ? dom_get("pinterest_user")     : $username; 
                                                                                  $board    = ($board    === false) ? dom_get("pinterest_board")    : $board;      return "https://www.pinterest.com/$username/$board/";                      }
    function url_instagram_user             ($username = false)                 { $username = ($username === false) ? dom_get("instagram_user")     : $username;   return "https://www.instagram.com/$username/";                             }
    function url_instagram_post             ($short_code)                       {                                                                                  return "https://instagram.com/p/$short_code/";                             }
    function url_flickr_user                ($username = false)                 { $username = ($username === false) ? dom_get("flickr_user")        : $username;   return "https://www.flickr.com/photos/$username/";                         }
    function url_500px_user                 ($username = false)                 { $username = ($username === false) ? dom_get("500px_user")         : $username;   return "https://www.500px.com/$username/";                                 }
    function url_flickr_page                ($page     = false)                 { $page     = ($page     === false) ? dom_get("flickr_page")        : $page;       return "https://www.flickr.com/photos/$page/";                             }
    function url_pinterest_pin              ($pin)                              {                                                                                  return "https://www.pinterest.com/pin/$pin/";                              }    
    function url_facebook_page              ($page     = false)                 { $page     = ($page     === false) ? dom_get("facebook_page")      : $page;       return "https://www.facebook.com/$page";                                   }
    function url_twitter_page               ($page     = false)                 { $page     = ($page     === false) ? dom_get("twitter_page")       : $page;       return "https://twitter.com/$page";                                        }
    function url_linkedin_page              ($page     = false)                 { $page     = ($page     === false) ? dom_get("linkedin_page")      : $page;       return "https://www.linkedin.com/in/$page";                                }
    function url_github_repository          ($username = false, $repo = false)  { $username = ($username === false) ? dom_get("github_user")     : $username; 
                                                                                  $repo     = ($repo     === false) ? dom_get("github_repository")  : $repo;       return "https://github.com/$username/$repo#readme";                        }
    function url_facebook_page_about        ($page     = false)                 { $page     = ($page     === false) ? dom_get("facebook_page")      : $page;       return "https://www.facebook.com/$page/about";                             }
    function url_tumblr_blog                ($blogname = false)                 { $blogname = ($blogname === false) ? dom_get("tumblr_blog")        : $blogname;   return "https://$blogname.tumblr.com";                                     }
    function url_tumblr_avatar              ($blogname = false, $size = 64)     { $blogname = ($blogname === false) ? dom_get("tumblr_blog")        : $blogname;   return "https://api.tumblr.com/v2/blog/$blogname.tumblr.com/avatar/$size"; }
    function url_messenger                  ($id       = false)                 { $id       = ($id       === false) ? dom_get("messenger_id")       : $id;         return "https://m.me/$id";                                                 }
    
    function dom_url_amp                    ($on = true)                        {                                                                                  return (is_dir("./amp") ? "./amp" : ("?amp=".(!!$on?"1":"0"))).(dom_is_localhost()?"#development=1":"");   }

    function url_facebook_search_by_tags    ($tags, $userdata = false)          { return "https://www.facebook.com/hashtag/"            . urlencode($tags); }
    function url_pinterest_search_by_tags   ($tags, $userdata = false)          { return "https://www.pinterest.com/search/pins/?q="    . urlencode($tags); }
    function url_instagram_search_by_tags   ($tags, $userdata = false)          { return "https://www.instagram.com/explore/tags/"      . urlencode($tags); }
    function url_tumblr_search_by_tags      ($tags, $userdata = false)          { return "https://".$userdata.".tumblr.com/tagged/"     . urlencode($tags); }
    function url_flickr_search_by_tags      ($tags, $userdata = false)          { return "https://www.flickr.com/search/?text="         . urlencode($tags); }
    
    function url_leboncoin                  ($url = false)                      { return ($url === false) ? dom_get("leboncoin_url", dom_get("leboncoin", "https://www.leboncoin.fr")) : $url; }
    function url_seloger                    ($url = false)                      { return ($url === false) ? dom_get("seloger_url",   dom_get("seloger",   "https://www.seloger.com"))  : $url; }
        
    function url_void                       ()                                  { return "#!"; }
    function url_print                      ()                                  { return dom_AMP() ? url_void() : "javascript:scan_and_print();"; }
    
    #endregion
    #region WIP API : DOM : COLORS
    ######################################################################################################################################

    // https://paulund.co.uk/social-media-colours

    function color_facebook         () { return '#3B5998'; }
    function color_twitter          () { return '#00ACED'; }
    function color_linkedin         () { return '#0077B5'; }
    function color_google           () { return array('#DB4437', '#F4B400', '#0F9D58', '#4285F4'); } function color_googlenews() { return color_google(); }
    function color_deezer           () { return array('#DB4437', '#F4B400', '#0F9D58', '#4285F4'); }
    function color_soundcloud       () { return '#f79810'; }
    function color_link             () { return 'currentcolor'; }
    function color_youtube          () { return '#BB0000'; }
    function color_instagram        () { return '#517FA4'; }
    function color_pinterest        () { return '#CB2027'; }
    function color_500px            () { return '#222222'; }
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
    function color_notifications    () { return '#FFFFFF'; }
    function color_loading          () { return '#FFAA00'; }
    function color_numerama         () { return array('#E9573F','#FFFFFF'); }
    function color_messenger        () { return '#0083FF'; }
    function color_alert            () { return '#EE0000'; }
    function color_leboncoin        () { return '#EA6B30'; }
    function color_seloger          () { return '#E00034'; }
    function color_amp              () { return '#0379C4'; }
    function color_darkandlight     () { return '#FFFFFF'; }
   
    #endregion
    #region WIP API : DOM : HTML COMPONENTS : SPECIAL TAGS
    ######################################################################################################################################

    /**
     * Special helper / low level components
     */
    
    function self($html) { return $html; }

    function dom_include_file($filename, $silent_errors = DOM_AUTO)
    {
        if ($silent_errors === DOM_AUTO)
        {
            $silent_errors = is_localhost() ? false : true;
        }

        ob_start();

        $content = "";

        if ($silent_errors) { @dom_include($filename); $content = @ob_get_clean(); }
        else                {  dom_include($filename); $content =  ob_get_clean(); }
        
        if (false !== $content) { return $content; }

        if ($silent_errors) { $content = @file_get_contents($filename); }
        else                { $content =  file_get_contents($filename); }
        
        if (false !== $content) { return $content; }

        return "";
    }
    
    function raw            ($html, $force_minify = false)  { return $html; }

    function raw_html       ($html, $force_minify = false)  { if (!!dom_get("no_html")) return ''; if (!!dom_get("minify", false) || $force_minify) { $html    = minify_html   ($html);    } return trim($html ); }
    function raw_js         ($js,   $force_minify = false)  { if (!!dom_get("no_js"))   return ''; if (!!dom_get("minify", false) || $force_minify) { $js      = minify_js     ($js);      } return trim($js   ); }
    function raw_css        ($css,  $force_minify = false)  { if (!!dom_get("no_css"))  return ''; if (!!dom_get("minify", false) || $force_minify) { $css     = minify_css    ($css);     } return trim($css  ); }

    function include_html   ($filename, $force_minify = false, $silent_errors = DOM_AUTO) { return (dom_has("rss") || !!dom_get("no_html")) ? '' : raw_html   (dom_include_file($filename, $silent_errors), $force_minify); }
    function include_css    ($filename, $force_minify = false, $silent_errors = DOM_AUTO) { return (dom_has("rss") || !!dom_get("no_css"))  ? '' : raw_css    (dom_include_file($filename, $silent_errors), $force_minify); }
    function include_js     ($filename, $force_minify = false, $silent_errors = DOM_AUTO) { return (dom_has("rss") || !!dom_get("no_js"))   ? '' : raw_js     (dom_include_file($filename, $silent_errors), $force_minify); }
    
    /*
     * CSS tags
     */
     
    $hook_css_vars = array(); function hook_css_var($var) { global $hook_css_vars; $hook_css_vars[$var] = $var; return "DOM_HOOK_CSS_VAR_".$var; }
    $hook_css_envs = array(); function hook_css_env($var) { global $hook_css_envs; $hook_css_envs[$var] = $var; return "DOM_HOOK_CSS_ENV_".$var; }

    function css_postprocess($css)
    {
        global $hook_css_vars;
        global $hook_css_envs;
    
        foreach ($hook_css_vars as $var) $css = str_replace("DOM_HOOK_CSS_VAR_".$var, dom_get($var), $css);
        foreach ($hook_css_envs as $var) $css = str_replace("DOM_HOOK_CSS_ENV_".$var, dom_get($var), $css);
    
        return $css;
    }

    function css_name($name) { return trim(str_replace("_","-",$name)); }

    function css_var($var, $val = false, $pre_processing = false, $pan = DOM_AUTO) { if (DOM_AUTO === $pan) $pan = get("env_var_default_tab", 32); if (false === $val) return 'var(--'.css_name($var).')';                                                 return pan('--'.css_name($var) . ': ', $pan) . $val . '; '; }
    function css_env($var, $val = false, $pre_processing = false, $pan = DOM_AUTO) { if (DOM_AUTO === $pan) $pan = get("env_var_default_tab", 32); if (false === $val) return ($pre_processing ? hook_css_env($var) : dom_get($var)); dom_set($var, $val); return pan('--'.css_name($var) . ': ', $pan) . $val . '; '/*.((false !== stripos($var,"_unitless")) ? "" : css_env($var."_unitless", str_replace(array("px","%","vw","vh","cm","em","rem","pt","deg","rad"), array("","","","","","","","","",""), $val)))*/; }

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
            $var = ($pre_processing ? hook_css_env($var) : dom_get($var,$var));

            if (false !== stripos($var, "px")) $unit = "px";
            if (false !== stripos($var, "em")) $unit = "em";
            if (false !== stripos($var, "%" )) $unit =  "%";

            $var = str_replace("px", "", $var);
            $var = str_replace("em", "", $var);
            $var = str_replace("%",  "", $var);

            if (!is_numeric($var))
            {
            //  error_log($var);
                $var = (int)$var;
            }

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
    
    function env        ($var, $val = false, $pre_processing = false, $pan = DOM_AUTO) { return css_env      ($var, $val, $pre_processing, $pan); }
    function env_add    ($vars,              $pre_processing = false, $pan = DOM_AUTO) { return css_env_add  ($vars,      $pre_processing, $pan); }
    function env_mul    ($vars,              $pre_processing = false, $pan = DOM_AUTO) { return css_env_mul  ($vars,      $pre_processing, $pan); }
    
    
    /*
     * Special HTML components
     */
    
    function if_browser($condition, $html) { return (dom_has("rss")) ? '' : ('<!--[if '.$condition.']>' . $html . '<![endif]-->'); }

    #endregion
    #region WIP API : DOM : HTML COMPONENTS : DOCUMENT ROOT
    ######################################################################################################################################

    function jsonfeed($json = false)
    {
        return dom_placeholder("DOM_HOOK_JSONFEED_".($json ? 1 : 0));
    }

    function _jsonfeed($json = false)
    {
        $profiler = dom_debug_track_timing();
        
    //  TODO : https://jsonfeed.org/mappingrssandatom => Only html hooks ? hooks => array => json => json feed
    //  TODO : https://daringfireball.net/feeds/json
    
        if ("json" == dom_get("doctype", "html"))
        {
            if ($json === false)
            {
                $json = json_encode(dom_get("dom_rss_items", array()));
            }
            
            return $json;
        }
    }
    
    function rss($xml = false)
    {
        return dom_placeholder("DOM_HOOK_RSS_".($xml ? 1 : 0));
    }

    function _rss($xml = false)
    {
        $profiler = dom_debug_track_timing();
        
        if ("rss" == dom_get("doctype", "html"))
        {
            if ($xml === false)
            {
                $xml = dom_rss_channel(
                
                                dom_rss_title           (dom_get("title"))
                . dom_eol() .   dom_rss_description     (dom_get("keywords", dom_get("title")))
                . dom_eol() .   dom_rss_link            (dom_get("url")."/"."rss")
                . dom_eol() .   dom_rss_lastbuilddate   ()
                . dom_eol() .   dom_rss_copyright       ()

                . dom_eol() .   dom_rss_image(
                            
                                            dom_rss_url     (dom_get("url")."/".dom_get("image"))
                            . dom_eol() .   dom_rss_title   (dom_get("title"))
                            . dom_eol() .   dom_rss_link    (dom_get("url")."/"."rss")
                            )

                . dom_eol() .   wrap_each(dom_get("dom_rss_items", array()), dom_eol(), "dom_rss_item_from_item_info", false)
                );
            }

            $path_css = dom_path("css/rss.css");

            return  ''
        /*  .       '<?xml version="1.0" encoding="'.dom_get("encoding", "utf-8").'" ?>'    */
            .       '<?xml version="1.0" encoding="'.strtoupper(dom_get("encoding", "utf-8")).'"?>'
            .       (!!$path_css ? ('<?xml-stylesheet href="'.$path_css.'" type="text/css" ?>') : '')
        /*  .       '<rss version="2.0" xmlns:atom="https://www.w3.org/2005/Atom" xmlns:media="https://search.yahoo.com/mrss/">'    */
            .       '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">'
            . dom_eol()   
            . dom_eol() . $xml
            . dom_eol()   
            . dom_eol() . '</rss>';
        }
    }

    function tile($xml = false)
    {
        return dom_placeholder("DOM_HOOK_TILE_".($xml ? 1 : 0));
    }

    function _tile($xml = false)
    {
        $profiler = dom_debug_track_timing();
        
        if ("tile" == dom_get("doctype", "html"))
        {
            if ($xml === false)
            {
                foreach (dom_get("dom_rss_items", array()) as $item_info)
                {
                    $xml = tile_item_from_item_info($item_info);
                    break;
                }
            }

            return '<?xml version="1.0" encoding="'.dom_get("encoding", "utf-8").'" ?>'
            . dom_eol()   
            . dom_eol() . $xml
            . dom_eol();
        }
    }

    function parse_delayed_components($html)
    {
    //  Lazy html generation

        if ("html" == dom_get("doctype", "html") && !dom_has("ajax"))
        {
            while (true)
            {
                $delayed_components = dom_get("delayed_components", array());
                dom_del("delayed_components");

                if (count($delayed_components) <= 0) break;

                $priorities = array();

                foreach ($delayed_components as $index => $delayed_component_and_param)
                {
                    $priorities[(int)$delayed_component_and_param[2]] = true;
                }

                foreach ($priorities as $priority => $_)
                {
                    foreach ($delayed_components as $index => $delayed_component_and_param)
                    {
                        if ($priority != $delayed_component_and_param[2]) continue;

                        $delayed_component = $delayed_component_and_param[0];
                        $param             = $delayed_component_and_param[1];
                        
                        $html = str_replace(
                            dom_placeholder($delayed_component.$index),
                            call_user_func($delayed_component, $param, $html), 
                            $html
                            );
                    }
                }
            }
        }

        return $html;
    }

    function html($html = "")
    {
        $profiler = dom_debug_track_timing();
    /*
        $no_head = (false === stripos($html, "<head>") && false === stripos($html, "<head "));
        $no_body = (false === stripos($html, "<body>") && false === stripos($html, "<body "));

             if ($no_head && $no_body)  { $html = head().body($html); }
        else if ($no_head)              { $html = head().     $html;  }
        else if ($no_body)              { $html =        body($html); }
    */
        if (dom_has("ajax")) $_POST = array();

        if ("html" == dom_get("doctype", "html"))
        {
            if (!dom_has("ajax"))
            {
            //  Lazy html generation

                $html = parse_delayed_components($html);

            //  Clean html

                if (!dom_get("minify"))
                {
                    while (true)
                    {
                        $pos = stripos($html, dom_eol(3)); if (false === $pos) break;
                        $html = substr_replace($html, dom_eol(2), $pos, strlen(dom_eol(3)));
                    }
                }

            //  Return html
            
                $welcome = "Welcome my fellow web developer!".((!dom_get("beautify") && !dom_get("static")) ? " You can ?beautify=1 this source code if needed!" : "");
                
                return raw_html('<!doctype html>'.comment($welcome)
                
                . dom_eol()
                . dom_eol() . '<html'.((dom_AMP())?' amp':'').' class="no-js" lang="'.dom_get("lang","en").'"> '
                . dom_eol()
                . dom_eol()). $html . comment("DOM.PHP ".DOM_VERSION.(defined("TOKEN_PACKAGE") ? (" / ".TOKEN_PACKAGE) : "")) . raw_html(
                  dom_eol()
                . dom_eol() . '</html>');
            }
            else
            {
                call_asyncs_start();

                $async_response = call_asyncs();
           
                return $async_response;
            }
        }
        
        return "";
    }

    function doc($html)
    {
        $profiler = dom_debug_track_timing();        
        return call_user_func(dom_get("doctype", "html"), $html);
    }

    #endregion
    #region WIP API : DOM : HTML COMPONENTS : MARKUP : HEAD, SCRIPTS & STYLES
    ######################################################################################################################################


    function head_boilerplate($async_css = false)
    {
        $profiler = dom_debug_track_timing();

        $path_css = dom_path_coalesce(            
            "./css/main.css",
            "./main.css",
            "./css/screen.css",
            "./screen.css"
            );

        return title().

            dom_eol(2). comment("DOM Head Metadata").
            dom_eol(2). metas().
            dom_eol(2). link_rel_manifest().
            
            dom_eol(2). comment("DOM Head styles").
            dom_eol(2). link_styles($async_css).
            dom_eol(2). dom_boilerplate_style().
                                                                                (!$path_css ? "" : (
            dom_eol(2). comment("DOM Head project-specific main stylesheet").   (!dom_get("dom_htaccess_rewrite_php") ? (
            dom_eol(2). dom_style($path_css).                                   "") : (
            dom_eol(2). link_style($path_css).                                  "")).
                                                                                "")).
            
            dom_eol(2). comment("DOM Head scripts").
            dom_eol(2). scripts_head().
            
            "";
    }

    function head($html = false, $async_css = false)
    { 
        $profiler = dom_debug_track_timing();
        
        if (false === $html)
        {
            $html = head_boilerplate($async_css);
        }

        $html = css_postprocess($html);        

        if (dom_get("support_service_worker", false))
        {
            hook_amp_require("install-serviceworker");
        }

        $amp_scripts = "";

        if (dom_AMP())
        {
            $amp_scripts =
                
                dom_eol(2) . '<style amp-custom>' . delayed_component("_amp_css") . '</style>'.                        
                dom_eol(2) . "<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>".
                dom_eol(2) . '<script async src="https://cdn.ampproject.org/v0.js"></script>'.

                script_amp_iframe                   ().
                script_amp_sidebar                  ().
                script_amp_position_observer        ().
                script_amp_animation                ().
                script_amp_form                     ().
                script_amp_youtube                  ().
                script_amp_script                   ().
                script_amp_install_serviceworker    ().

                "";
        }

        return dom_tag('head', dom_eol(2) . $html . dom_eol(2) . $amp_scripts); 
    }

    function delayed_component($callback, $arg = false, $priority = 1)
    {
        $delayed_components = dom_get("delayed_components", array());
        $index = count($delayed_components);
        dom_set("delayed_components", array_merge($delayed_components, array(array($callback, $arg, $priority))));
        return dom_placeholder($callback.$index);
    }
    
    function script_amp_install_serviceworker   () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_iframe                  () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_sidebar                 () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_position_observer       () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_animation               () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_form                    () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_youtube                 () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_script                  () { return delayed_component("_".__FUNCTION__, false, 2); }

    function _script_amp_install_serviceworker  () { return has_amp_requirement("install-serviceworker") ? (dom_eol(1) . '<script async custom-element="amp-install-serviceworker' . '" src="https://cdn.ampproject.org/v0/amp-install-serviceworker' . '-0.1.js"></script>') : ""; }
    function _script_amp_iframe                 () { return has_amp_requirement("iframe")                ? (dom_eol(1) . '<script async custom-element="amp-iframe'                . '" src="https://cdn.ampproject.org/v0/amp-iframe'                . '-0.1.js"></script>') : ""; }
    function _script_amp_sidebar                () { return has_amp_requirement("sidebar")               ? (dom_eol(1) . '<script async custom-element="amp-sidebar'               . '" src="https://cdn.ampproject.org/v0/amp-sidebar'               . '-0.1.js"></script>') : ""; }
    function _script_amp_position_observer      () { return has_amp_requirement("position-observer")     ? (dom_eol(1) . '<script async custom-element="amp-position-observer'     . '" src="https://cdn.ampproject.org/v0/amp-position-observer'     . '-0.1.js"></script>') : ""; }
    function _script_amp_animation              () { return has_amp_requirement("animation")             ? (dom_eol(1) . '<script async custom-element="amp-animation'             . '" src="https://cdn.ampproject.org/v0/amp-animation'             . '-0.1.js"></script>') : ""; }
    function _script_amp_form                   () { return has_amp_requirement("form")                  ? (dom_eol(1) . '<script async custom-element="amp-form'                  . '" src="https://cdn.ampproject.org/v0/amp-form'                  . '-0.1.js"></script>') : ""; }
    function _script_amp_youtube                () { return has_amp_requirement("youtube")               ? (dom_eol(1) . '<script async custom-element="amp-youtube'               . '" src="https://cdn.ampproject.org/v0/amp-youtube'               . '-0.1.js"></script>') : ""; }
    function _script_amp_script                 () { return has_amp_requirement("script")                ? (dom_eol(1) . '<script async custom-element="amp-script'                . '" src="https://cdn.ampproject.org/v0/amp-script'                . '-0.1.js"></script>') : ""; }

    function title  ($title = false) { return delayed_component("_".__FUNCTION__, $title); }
    function _title ($title = false) { return ($title === false) ? dom_tag('title', dom_get("title") . ((dom_get("heading") != '') ? (' - '.dom_get("heading")) : '')) : dom_tag('title', $title); }

    function dom_link_rel_prefetch($url)
    {
        return link_rel("prefetch", $url);
    }

    function link_rel_manifest($path_manifest = false, $type = false, $pan = 17)
    {
        $profiler = dom_debug_track_timing();

        if (!$path_manifest) $path_manifest = dom_path("manifest.json");
        if (!$path_manifest) return "";

        return link_rel("manifest", $path_manifest, $type, $pan);
    }

    function link_rel_icon($name = "favicon", $size = false, $media = false, $ext = "png", $type = DOM_AUTO, $alternate = false)
    {
        if ($name === false || $name === DOM_AUTO) $name = "favicon";
        if ($ext  === false || $ext  === DOM_AUTO) $ext  = "png";
        if ($type === false || $type === DOM_AUTO) $type = false;

        if (is_array($name)) { $html = ""; foreach ($name as $i => $_) { $html_icon = link_rel_icon($_,    $size, $media, $ext, $type, $alternate); $html .= (($i > 0 && $html_icon != "") ? dom_eol() : "").$html_icon; } return $html; }
        if (is_array($size)) { $html = ""; foreach ($size as $i => $_) { $html_icon = link_rel_icon($name, $_,    $media, $ext, $type, $alternate); $html .= (($i > 0 && $html_icon != "") ? dom_eol() : "").$html_icon; } return $html; }
        if (is_array($ext))  { $html = ""; foreach ($ext  as $i => $_) { $html_icon = link_rel_icon($name, $size, $media, $_,   $type, $alternate); $html .= (($i > 0 && $html_icon != "") ? dom_eol() : "").$html_icon; } return $html; }
        if (is_array($type)) { $html = ""; foreach ($type as $i => $_) { $html_icon = link_rel_icon($name, $size, $media, $ext, $_   , $alternate); $html .= (($i > 0 && $html_icon != "") ? dom_eol() : "").$html_icon; } return $html; }

        if ($type === false && false !== stripos($name,"apple") && false !== stripos($name, "splash"))   $type = "apple-touch-startup-image";
        if ($type === false && false !== stripos($name,"apple") && false !== stripos($name, "startup"))  $type = "apple-touch-startup-image";
        if ($type === false && false !== stripos($name,"apple"))                                         $type = "apple-touch-icon";
        if ($type === false)                                                                             $type = "icon";

        if (!!$size)
        {
            $size = is_int($size) ? ($size."x".$size) : $size;
            $size = str_replace("-","x",$size);

            $wh = explode("x", $size);

            $w = (int)$wh[0];
            $h = (int)$wh[1];

            if (is_array($media))
            {
                $media_clean = array();

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
                    return            link_rel_icon($name, $w."x".$h, array_merge($media, array("orientation" => "portrait")),  $ext, $type, $alternate)
                        . dom_eol() . link_rel_icon($name, $h."x".$w, array_merge($media, array("orientation" => "landscape")), $ext, $type, $alternate);
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
        if (false === stripos($type, "apple"))  $attributes["type"]  = "image/$ext".(($ext=="svg")?"+xml":"");
        if (!!$media)                           $attributes["media"] = "(device-width: ".$media_clean["width"]."px) and (device-height: ".$media_clean["height"]."px) and (-webkit-device-pixel-ratio: ".$media_clean["ratio"].") and (orientation: ".$media_clean["orientation"].")";

        $path = dom_path($name.".".$ext);

        if (!$path) return "";

        return link_rel($type, $path, $attributes);
    }
    
    function metas  () { return delayed_component("_".__FUNCTION__, false); }
    function _metas ()
    {
        $profiler = dom_debug_track_timing();
        
        return          meta_charset('utf-8')
            .   dom_eol()
            .   dom_eol() .                   meta_http_equiv('x-ua-compatible',   'ie=edge,chrome=1')
            .   dom_eol() . (dom_AMP() ? '' : meta_http_equiv('Content-type',      'text/html;charset=utf-8'))
            .   dom_eol() .                   meta_http_equiv('content-language',  dom_get("lang","en"))
            .   dom_eol()       
            .   dom_eol() . meta(array("title" =>                       dom_get("title") . ((dom_get("heading") != '') ? (' - '.dom_get("heading")) : '')))
            .   dom_eol()       
            .   dom_eol() . meta('keywords',                            dom_get("title").((!!dom_get("keywords") && "" != dom_get("keywords")) ? (', '.dom_get("keywords")) : "")    )
            .   dom_eol()       
            .   dom_eol() . meta('format-detection',                    'telephone=no')
            .   dom_eol() . meta('viewport',                            'width=device-width, minimum-scale=1, initial-scale=1')
        //  .   dom_eol() . meta('robots',                              'NOODP') // Deprecated
        //  .   dom_eol() . meta('googlebot',                           'NOODP')
            .   dom_eol() . meta('description',                         dom_get('description', dom_get('title')))
            .   dom_eol() . meta('author',                              dom_get('author',DOM_AUTHOR))
            .   dom_eol() . meta('copyright',                           dom_get('author',DOM_AUTHOR).' 2000-'.date('Y'))
            .   dom_eol() . meta('title',                               dom_get('title'))
            .   dom_eol() . meta('theme-color',                         dom_get("theme_color"))
            .   dom_eol()       
            .   dom_eol() . meta('DC.title',                            dom_get('title'))
            .   dom_eol() . meta('DC.format',                           'text/html')
            .   dom_eol() . meta('DC.language',                         dom_get('lang','en'))
            .   dom_eol()       
            .   dom_eol() . meta('geo.region',                          dom_get('geo_region'))
            .   dom_eol() . meta('geo.placename',                       dom_get('geo_placename'))
            .   dom_eol() . meta('geo.position',                        dom_get('geo_position_x').';'. dom_get('geo_position_y'))
            .   dom_eol() . meta('ICBM',                                dom_get('geo_position_x').', '.dom_get('geo_position_y'))              
            .   dom_eol()       
            .   dom_eol() . meta('twitter:card',                        'summary')                  . (dom_has('twitter_page') ? (""
            .   dom_eol() . meta('twitter:site',                        dom_get('twitter_page'))    ) : "")
            .   dom_eol() . meta('twitter:url',                         dom_get('canonical'))
            .   dom_eol() . meta('twitter:title',                       dom_get('title'))
            .   dom_eol() . meta('twitter:description',                 dom_get('description', dom_get('title')))
            .   dom_eol() . meta('twitter:image',                       dom_path(dom_get('image')))
            .   dom_eol()       
            .   dom_eol() . meta_property('og:site_name',               dom_get('og_site_name', dom_get('title')))
            .   dom_eol() . meta_property('og:image',                   dom_path(dom_get('image')))
            .   dom_eol() . meta_property('og:title',                   dom_get('title'))
            .   dom_eol() . meta_property('og:description',             dom_get('description'))
            .   dom_eol() . meta_property('og:url',                     dom_get('canonical'))            
            .   dom_eol() . meta_property('og:type',                    'website')
            .   dom_eol()       
            .   dom_eol() . meta('application-name',                    dom_get('title'))                               
            .   dom_eol()                                                                                                   . (dom_has("pinterest_site_verification") ? (""
            .   dom_eol() . meta('p:domain_verify',                     dom_get("pinterest_site_verification"))     ) : "") . (dom_has("google_site_verification")    ? (""
            .   dom_eol() . meta('google-site-verification',            dom_get("google_site_verification"))        ) : "")
            .   dom_eol()
            .   dom_eol() . meta('msapplication-TileColor',                dom_get("theme_color"))
            .   dom_eol() . meta('msapplication-TileImage',                dom_path(dom_get("icons_path").'ms-icon-144x144.png'))
            .   dom_eol()
            .   (dom_path(dom_get("icons_path").'ms-icon-70x70.png'    ) ? (dom_eol() . meta('msapplication-square70x70logo',     dom_path(dom_get("icons_path").'ms-icon-70x70.png'    ))) : '')
            .   (dom_path(dom_get("icons_path").'ms-icon-150x150.png'  ) ? (dom_eol() . meta('msapplication-square150x150logo',   dom_path(dom_get("icons_path").'ms-icon-150x150.png'  ))) : '')
            .   (dom_path(dom_get("icons_path").'ms-icon-310x150.png'  ) ? (dom_eol() . meta('msapplication-wide310x150logo',     dom_path(dom_get("icons_path").'ms-icon-310x150.png'  ))) : '')
            .   (dom_path(dom_get("icons_path").'ms-icon-310x310.png'  ) ? (dom_eol() . meta('msapplication-square310x310logo',   dom_path(dom_get("icons_path").'ms-icon-310x310.png'  ))) : '')
            .   dom_eol()
            .   dom_eol() . meta('msapplication-notification',             'frequency=30;'
                                                                .   'polling-uri' .'='.urlencode('/?rss=tile&id=1').';'
                                                                .   'polling-uri2'.'='.urlencode('/?rss=tile&id=2').';'
                                                                .   'polling-uri3'.'='.urlencode('/?rss=tile&id=3').';'
                                                                .   'polling-uri4'.'='.urlencode('/?rss=tile&id=4').';'
                                                                .   'polling-uri5'.'='.urlencode('/?rss=tile&id=5').';'.' cycle=1')
                                                               
            // TODO FIX HREFLANG ALTERNATE
            // TODO FIX URL QUERY ARGS (incompatible with static sites)

                // Placeholder for 3rd parties who look for a css <link> in order to insert something before
            .   (!AMP() ? (dom_eol(2) . '<link rel="stylesheet" type="text/css" media="screen"/>') : "")

            .   dom_eol()   
            .   dom_eol() . link_rel("alternate",   dom_get('canonical')."/?rss",     array("type" => "application/rss+xml", "title" => "RSS"))
            .   dom_eol() . link_rel("alternate",   dom_get('canonical')."/?lang=en", array("hreflang" => "en-EN"))
            .   dom_eol() . link_rel("alternate",   dom_get('canonical')."/?lang=fr", array("hreflang" => "fr-fr"))                              . (dom_AMP() ? '' : (''
            .   dom_eol() . link_rel("amphtml",     dom_get('canonical')."/?amp=1")                                                              ))
            .   dom_eol() . link_rel("canonical",   dom_get('canonical'))
            .   dom_eol()
            .   dom_eol() . link_rel_icon("img/icon.svg")
            .   dom_eol()
            .   dom_eol() . link_rel_icon(dom_get("image"), false, false, false, false, /*alternate*/true)
            .   dom_eol()
            .   dom_eol() . link_rel_icon(array(
            
                    dom_get("icons_path")."favicon",
                    dom_get("icons_path")."android-icon",
                    dom_get("icons_path")."apple-icon",
                    dom_get("icons_path")."apple-touch-icon"),

                    array(16,32,57,60,72,76,96,114,120,144,152,180,192,196,310,512),
                    
                    false, false, false, false, /*alternate*/true)

            .   dom_eol()
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "2048x2732" , array(1024, 1366, 2)  )
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "1668x2388" , array( 834, 1194, 2)  )
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "1668x2224" , array( 834, 1112, 2)  )
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "1536x2048" , array( 768, 1024, 2)  )
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "828x1792"  , array( 414,  896, 2)  )
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "750x1334"  , array( 375,  667, 2)  )
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "640x1136"  , array( 320,  568, 2)  )
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "1242x2688" , array( 414,  896, 3)  )
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "1125x2436" , array( 375,  812, 3)  )
            .   dom_eol() . link_rel_icon(dom_get("icons_path")."apple-splash", "1242x2208" , array( 414,  736, 3)  )
            .   dom_eol()
            .   dom_eol() . delayed_component("_dom_hooked_links")          
            ;
    }
    
    function meta($p0, $p1 = false, $pan = 0)                               { return (($p1 === false) ? '<meta'.dom_attributes($p0,$pan).' />' : meta_name($p0,$p1)); }
                            
    function meta_charset($charset)                                         { return meta(array("charset"    => $charset)); }
    function meta_http_equiv($equiv,$content)                               { return meta(array("http-equiv" => $equiv,    "content" => $content), false, array(40,80)); }
    function meta_name($name,$content)                                      { return meta(array("name"       => $name,     "content" => $content), false, array(40,80)); }
    function meta_property($property,$content)                              { return meta(array("property"   => $property, "content" => $content), false, array(40,80)); }
                        
    function manifest($filename = "manifest.json") 
    {
        return link_rel("manifest", $filename) . ((!dom_AMP() && !dom_is_localhost()) ? (dom_eol(2) . '<script async src="https://cdn.jsdelivr.net/npm/pwacompat@2.0.6/pwacompat.min.js" integrity="sha384-GOaSLecPIMCJksN83HLuYf9FToOiQ2Df0+0ntv7ey8zjUHESXhthwvq9hXAZTifA" crossorigin="anonymous"></script>') : ""); 
    }

    function link_HTML($attributes, $pan = 0)                               { if (!!dom_get("no_html"))  return ''; return dom_tag('link', '', dom_attributes($attributes,$pan), false, true); }
    function link_rel($rel, $link, $type = false, $pan = 0)                 { if (!$link || $link == "") return ''; return link_HTML(array_merge(array("rel" => $rel, "href" => $link), ($type !== false) ? (is_array($type) ? $type : array("type" => $type)) : array()), $pan); }
    function link_style($link, $media = "screen", $async = false)           { if (!!dom_get("no_css"))  return ''; return ((dom_AMP() && !((0 === stripos($link, "http")) || (0 === stripos($link, "//")))) || !!dom_get("include_custom_css")) ? dom_style($link, false, true) : link_rel("stylesheet", $link, ($async && !dom_AMP()) ? array("type" => "text/css", "media" => "nope!", "onload" => "this.media='$media'") : array("type" => "text/css", "media" => $media)); }

    function dom_style( $filename_or_code = "",                                                             $force_minify = false, $silent_errors = DOM_AUTO)   { if (!$filename_or_code || $filename_or_code == "") return ''; $filename = dom_path($filename_or_code); $profiler = dom_debug_track_timing(!!$filename ? $filename : "inline"); $css = dom_eol().($filename ? include_css($filename, $force_minify, $silent_errors) : raw_css ($filename_or_code, $force_minify)).dom_eol(); return dom_AMP() ? hook_amp_css($css) : (dom_tag('style',  $css                        )); }
    function dom_script($filename_or_code = "", $type = "text/javascript",                 $force = false,  $force_minify = false, $silent_errors = DOM_AUTO)   { if (!$filename_or_code || $filename_or_code == "") return ''; $filename = dom_path($filename_or_code); $profiler = dom_debug_track_timing(!!$filename ? $filename : "inline"); $js  = dom_eol().($filename ? include_js ($filename, $force_minify, $silent_errors) : raw_js  ($filename_or_code, $force_minify)).dom_eol(); return dom_AMP() ? hook_amp_js($js)   : (dom_tag('script', $js, array("type" => $type) )); }
    function script_src($src,                   $type = "text/javascript", $extra = false, $force = false)                                                      { if (!!dom_get("no_js")) return ''; return ((!$force && dom_AMP()) ? '' : dom_tag('script', '', ($type === false) ? array("src" => $src) : array("type" => $type, "src" => $src), false, false, $extra)); }
    function script_json_ld($properties)                                                                                                                        { return dom_script((((!dom_get("minify",false)) && defined("JSON_PRETTY_PRINT")) ? json_encode($properties, JSON_PRETTY_PRINT) : json_encode($properties)), "application/ld+json", true); }
    
    function dom_script_ajax_head()                                             { return dom_AMP() ? "" : dom_script(dom_js_ajax_head()); }
    function dom_script_ajax_body()                                             { return dom_AMP() ? "" : dom_script(dom_js_ajax_body()); }
    
    function schema($type, $properties = array(), $parent_schema = false)
    {
        return array_merge(($parent_schema === false) ? array() : $parent_schema, array("@context" => "https://schema.org", "@type" => $type), $properties);
    }
    
    function link_style_google_fonts($fonts = false, $async = true)
    {    
        if ($fonts === false) $fonts = dom_get("fonts");
        if (!!$fonts)         $fonts = str_replace(' ','+', trim($fonts, ", /|"));

        return            (!!$fonts ? link_style("https://fonts.googleapis.com/css?family=$fonts",          "screen", $async) : '')
            . dom_eol() . (true     ? link_style("https://fonts.googleapis.com/icon?family=Material+Icons", "screen", $async) : '');
    }
    
    function link_styles($async = false, $fonts = false)
    {
        $profiler = dom_debug_track_timing();

        if ($fonts === false) $fonts = dom_get("fonts");

        $inline_css = dom_get("dom_inline_css", false);

        $path_normalize         = !$inline_css ? false : dom_path("css/normalize.min.css");
        $path_sanitize          = !$inline_css ? false : dom_path("css/evergreen.min.css");
        $path_h5bp              = !$inline_css ? false : dom_path("css/h5bp/main.css");
        $path_material          = !$inline_css ? false : dom_path("css/material-components-web.min.css");
        $path_bootstrap         = !$inline_css ? false : dom_path("css/bootstrap.min.css");
        $path_google_fonts      = !$inline_css ? false : dom_path("css/google-fonts.css");
        $path_material_icons    = !$inline_css ? false : dom_path("css/material-icons.css");

        return                                                                                                                                                                                                                                                                                         (("normalize" == dom_get("normalize")) ? (""
            .               ($path_normalize      ? link_style($path_normalize      , "screen", false)  : link_style('https://cdnjs.cloudflare.com/ajax/libs/normalize/'         . dom_get("version_normalize") . '/normalize.min.css',                       "screen", false     ))         ) : "") . (("sanitize"  == dom_get("normalize")) ? (""
            .   dom_eol() . ($path_sanitize       ? link_style($path_sanitize       , "screen", false)  : link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . dom_get("version_sanitize")  . '/evergreen.min.css',                       "screen", false     ))         ) : "")
        //  .   dom_eol() . ($path_h5bp           ? link_style($path_h5bp           , "screen", false)  : link_style('https://cdn.jsdelivr.net/npm/html5-boilerplate@'           . dom_get("version_h5bp")      . '/dist/css/main.css',                       "screen", false     ))                 
                                                                                                                                                                                                                                                                                                     . (("material"  == dom_get("framework")) ? (""
            .   dom_eol() . ($path_material       ? link_style($path_material       , "screen", false)  : link_style('https://unpkg.com/material-components-web@'                . dom_get("version_material")  . '/dist/material-components-web.min.css',    "screen", false     ))         ) : "") . (("bootstrap" == dom_get("framework")) ? (""
            .   dom_eol() . ($path_bootstrap      ? link_style($path_bootstrap      , "screen", false)  : link_style('https://stackpath.bootstrapcdn.com/bootstrap/'             . dom_get("version_bootstrap") . '/css/bootstrap.min.css',                   "screen", false     ))         ) : "") . (("spectre"   == dom_get("framework")) ? (""
            .   dom_eol() .                                                                               link_style('https://unpkg.com/spectre.css/dist/spectre.min.css')
            .   dom_eol() .                                                                               link_style('https://unpkg.com/spectre.css/dist/spectre-exp.min.css')
            .   dom_eol() .                                                                               link_style('https://unpkg.com/spectre.css/dist/spectre-icons.min.css')                                                                                                             ) : "") . (!!$fonts                              ? (""
            .   dom_eol() . ($path_google_fonts   ? link_style($path_google_fonts   , "screen", $async) : link_style('https://fonts.googleapis.com/css?family='.str_replace(' ','+', trim($fonts," /|")),                                                     "screen", $async    ))         ) : "") . (("material"  == dom_get("framework")) ? ("" 
            .   dom_eol() . ($path_material_icons ? link_style($path_material_icons , "screen", $async) : link_style('https://fonts.googleapis.com/icon?family=Material+Icons',                                                                               "screen", $async    ))         ) : "")
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

    function css_line($selectors = "", $styles = "", $tab = 1, $pad = 54)
    {
        return $selectors == "" ? dom_eol() : str_pad(dom_eol().dom_tab(1).$selectors, $pad)."{ ".$styles." }";
    }

    function css_aspect_ratio($e, $w, $h)
    {
        return '/* Aspect Ratio wrappers */'.
            css_line().
            css_line($e.'::before',         'content: ""; display: block; padding-bottom: calc(100% / ('.$w.' / '.$h.'));').
            css_line($e.'',                 'position: relative;').
            css_line($e.' > :first-child',  'position: absolute; top: 0; left: 0; width:  100%; height: 100%;').
            css_line($e.' > img',           'height: auto;').
            css_line().
            css_line();
    }

    function css_boilerplate_aspect_ratio($e = ".aspect-ratio", $w = 16, $h = 9)
    {
        $css = css_aspect_ratio($e, $w, $h);

        foreach (dom_supported_ratios() as $ratio) 
        {
            $css .= css_line(
                $e.'-'.$ratio[0].'-'.$ratio[1].'::before',
                'padding-bottom: calc(100% / ('.$ratio[0].' / '.$ratio[1].')); '.($ratio[0]<10 ? ' ' : '').($ratio[1]<10 ? ' ' : '').''
                );
        }

        return $css;
    }

    function include_css_boilerplate()
    {
        if (!!dom_get("no_css")) return '';

        dom_heredoc_start(-3); ?><style><?php dom_heredoc_flush(null); ?>

            /* DOM CSS boilerplate */

            :root
            {
                --non-empty-ruleset: 666;

                <?= env("theme_color",              dom_get("theme_color")                  ) ?> 
                <?= env("text_color",               dom_get("text_color")                   ) ?> 
                <?= env("link_color",               dom_get("link_color")                   ) ?> 
                <?= env("background_color",         dom_get("background_color")             ) ?> 
                
                <?= env("default_image_ratio_w",    dom_get("default_image_ratio_w", 300)   ) ?> 
                <?= env("default_image_ratio_h",    dom_get("default_image_ratio_h", 200)   ) ?> 
                <?= env("default_image_ratio",      "calc(var(--default-image-ratio-w) / ".
                                                         "var(--default-image-ratio-h))"    ) ?> 
                
                <?= env("header_height",             "256px" ) ?> 
                <?= env("header_min_height",           "0px" ) ?> 
                <?= env("header_toolbar_height",      "48px" ) ?>                 

                <?= env("main_max_width",           "1200px" ) ?>                 
                <?= env("dom_gap",                    "10px" ) ?>                 
                <?= env("scrollbar_width",            "17px" ) ?> 
                <?= env("svg_size",                   "24px" ) ?> 

                <?php $css = "";

                foreach (predefined_svg_brands() as $svg)
                {
                    $fn_color = "color_$svg";
                    $colors   = $fn_color();
                    $colors   = is_array($colors) ? $colors : array($colors);
                    $class    = "palette-$svg";
                    $var      = "--color-$svg";

                    $css .= dom_eol().dom_tab(2);
                    for ($i = 0; $i < count($colors); ++$i) $css .= pan($var.(($i > 0) ? ("-".($i+1)) : "").":", $i == 0 ? 31 : 0)." ".$colors[$i].";";
                }
                
                echo $css; ?> 
            }

            /* Sanitize ++ */

            html {
                height: 100%;
                height: -webkit-fill-available;
                block-size: -webkit-fill-available;
                block-size: stretch;
                }
            body {
                min-height: 100%;
                min-height: -webkit-fill-available;
                min-block-size: -webkit-fill-available;
                min-block-size: stretch;
                }
            nav li:before {
                content: "\200B";
                position: absolute;
                }

            /* Font stack */

            body,h1,h2,h3,h4,h5,h6                          { font-family: <?= string_system_font_stack() ?>; }

            /* Colors */
            
            body                                            { background-color: var(--background-color); }
            a, a:hover, a:visited                           { color: var(--link-color); }
        
            /* Layout */
            
            html, body                                     { margin: 0px; padding: 0px }
            body                                           { text-align: center; min-height: 100vh; }
            main                                           { text-align: left; padding-top: unset; margin-top: 0px; margin-right: auto; margin-bottom: 0px; margin-left: auto; width: 100%; max-width: var(--main-max-width) }

            /* Main content inflate (makes footer sticky) */

            body                                           { display: flex; flex-direction: column; min-height: 100vh; } 
            body>main                                      { flex: 1; }

            /* Toolbar */

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
            ?>-open .menu                                { position: absolute; }
            .menu                                           { max-height: 0; transition: max-height 1s ease-out; text-align: left; }
            .menu ul                                        { list-style-type: none; padding-inline-start: 0px; padding-inline-end: 0px; margin-block-end: 0px; margin-block-start: 0px; }
            .menu li a                                      { display: inline-block; width: 100%; padding: var(--dom-gap); }

            /* Footer */

            body>.footer                                    { background-color: var(--theme-color); color: var(--background-color); }

            /* Images */

            picture, figure, img, amp-img                   { max-width: 100%; object-fit: cover; vertical-align: top; display: inline-block }
            figure                                          { margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px;  }

            img, picture, iframe                            { background-image: url(<?= dom_path("img/loading.svg") ?>); background-repeat: no-repeat; background-position: center; }

            /* Grid */

            .grid                                           { display: grid; grid-gap: var(--dom-gap); }

            /* Back-to-top style */    
            
            .cd-top, .cd-top:visited                        { background-color: var(--theme-color); color: var(--background-color); }
            .cd-top                                         { text-decoration: none; display: inline-block; height: 40px; width: 40px; position: fixed; bottom: 40px; right: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); text-align: center; line-height: 40px; }
            .cd-top                                         { transition: opacity .3s 0s, visibility 0s .3s; }
            .cd-top.cd-is-visible, .cd-top.cd-fade-out,    
            .no-touch .cd-top:hover                         { transition: opacity .3s 0s, visibility 0s 0s; }
            .cd-top.cd-is-visible                           { visibility: visible; opacity: 1; }
            .cd-top.cd-fade-out                             { opacity: .5; }
            .cd-top:hover                                   { opacity: 1; text-decoration: none }
        <?php if (!get("no_js")) { ?> 
            .cd-top                                         { visibility: hidden; opacity: 0; }
        <?php } ?> 
            @media only screen and (min-width:  768px)      { .cd-top { right: 20px; bottom: 20px; } }
            @media only screen and (min-width: 1024px)      { .cd-top { right: 30px; bottom: 30px; line-height: 60px; height: 60px; width: 60px; font-size: 30px } }
            
            /* Animations */

            a, a svg path                                   { transition: .6s ease-in-out }

            /* Other utilities */    
            
            .app-install, .app-install.hidden               { display: none }
            .app-install.visible                            { display: inline-block }

            /* Until there is a better method that is not interfering with margin and padding of the element, use anchor dedicated tag insertion */
        
            .anchor                                         { visibility: hidden; display: block; height: 1px; position: relative; top: calc(-1 * var(--header-toolbar-height) - var(--header-min-height)) }
            summary>.anchor                                 { display: inline-block; }
        /*
            .headline:target,
            .card .headline:target {
            height:          calc(var(--header-toolbar-height) - var(--header-min-height));
            margin-top: calc(-1 * var(--header-toolbar-height) - var(--header-min-height));
                }
        */
        /*
            .headline:target,
            .card .headline:target {
            margin-top: -154px;
            padding-top: 154px;
                }
        */
            .clearfix { height: 1% } .clearfix:after        { content:"."; height:0; line-height:0; display:block; visibility:hidden; clear:both; }

            /* Main images */
                
            main figure                                     { display: inline-block; }
            main figure > picture, main figure > amp-img    { display: inline-block; width: 100%; height: 0px; padding-bottom: calc(100% / var(--default-image-ratio)); overflow: hidden; position: relative; }
            
            main figure img                                 { left: 0px; top: 0px; width: 100%; height: 100%;}
            
            main amp-img, main img, main picture            { object-fit: cover; }    
            amp-img.loading, img.loading, picture.loading   { object-fit: none;  }

            /* Scrollbar */
            
            body                                            { scrollbar-width: var(--scrollbar-width); }
            body::-webkit-scrollbar                         {           width: var(--scrollbar-width); }
            
            body                                            {  scrollbar-color: var(--theme-color)                                                      var(--background-color); }
            body::-webkit-scrollbar-thumb                   { background-color: var(--theme-color); } body::-webkit-scrollbar-track { background-color: var(--background-color); }

            /* Containers aspect ratios */

            <?= css_boilerplate_aspect_ratio() ?> 

            /* SVG */

            .span-svg-wrapper                               { display: inline-block; height: auto; }
            .span-svg-wrapper-aligned                       { position: relative; bottom: -6px; padding-right: 6px; }
            .span-svg-wrapper svg                           { width: var(--svg-size); height: var(--svg-size); }

            <?= predefined_svg_brands_css_boilerplate() ?> 

        <?php if (!AMP()) { ?> 

            /* Toolbar */

        <?php if (dom_get("no_js")) { ?> 
            
            .toolbar                                        { position: sticky; top: calc(var(--header-min-height) - var(--header-height)); }

        <?php } else { ?> 

            .toolbar                                        { position: fixed; top: 0px; } <?= include_css_main_toolbar_adaptation() ?> 

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

            
        <?php } if ("material" == dom_get("framework")) { ?> 

            /* MATERIAL DESIGN DEFAULTS */
            
            :root
            {
                --mdc-theme-primary:    var(--theme-color);
                --mdc-theme-secondary:  var(--link-color);
                --mdc-theme-background: var(--background-color);
            }
            
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

            /* SPECTRE DEFAULTS */
            
            .text-primary       { color: var(--theme-color);        }
            .text-secondary     { color: var(--link-color);         }
            .bg-primary         { color: var(--background-color);   }
            
        <?php } ?> 

            /* PRINT */
                
            @media print {

                .toolbar-row-banner                   { display: none }
                .toolbar-row-nav                      { background-color: transparent; align-items: flex-start; justify-content: flex-end; }
                .toolbar-row-nav .toolbar-cell-left   { display: none }
                .toolbar-row-nav .toolbar-cell-right  { display: none }
                .toolbar-row-nav .toolbar-cell-center { background-color: transparent;  padding-right: var(--scrollbar-width); }
                
                .main { margin-top:  0px }
                
                body>.footer, iframe, .cd-top { display: none; height: 0px; }
            }    

        <?php dom_heredoc_flush("raw_css"); ?></style><?php return dom_heredoc_stop(null);
    }
    
    function dom_boilerplate_style()
    {
        return dom_style(include_css_boilerplate(), false, DOM_AUTO, /* needs to be first*/true);
    }

    function scripts_head()
    {   
        $profiler = dom_debug_track_timing(); 

        return           dom_script_ajax_head().
            dom_eol(2) . dom_script(dom_js_scan_and_print_head()).          ((!!dom_get("dom_script_document_events", true)) ? (
            dom_eol(2) . dom_script(dom_js_on_document_events_head()).      "") : "").
                                                                            (!dom_AMP() ? "" : ("".
            dom_eol(2) . comment("AMP Javascript").
            dom_eol(2) . delayed_component("_amp_scripts_head")             ))
        ; 
    }

    function back_to_top_link()
    {
        return dom_eol(2) . a("▲", url_void(), "cd-top");
    }

    function dom_script_google_analytics_snippet()
    {
        if (!defined("TOKEN_GOOGLE_ANALYTICS")) return "";

        if (dom_do_not_track())
        {
            return comment("Google analytics is disabled in accordance to user's 'do-not-track' preferences");
        }

        return dom_script(

            dom_eol(1) . '/*  Google analytics */ '.

            dom_eol(2) . dom_tab() . 'window.ga=function() { ga.q.push(arguments) };'.

                ' ga.q=[];'.
                ' ga.l=+new Date;'.

                ' ga("create",'. ' "'.TOKEN_GOOGLE_ANALYTICS.'",'. ' "auto"'.   ');'.
                ' ga("set",'.    ' "anonymizeIp",'.                ' true'.     ');'.
                ' ga("set",'.    ' "transport",'.                  ' "beacon"'. ');'.
                ' ga("send",'.   ' "pageview"'.                                 ');'.

            dom_eol(1)
            );
    }

    function dom_script_google_analytics()
    {
        if (!defined("TOKEN_GOOGLE_ANALYTICS")) return "";

        return  dom_eol(2) . dom_script_google_analytics_snippet().
                dom_eol(2) . script_src('https://www.google-analytics.com/analytics.js', false, 'async defer');
    }

    function dom_js_scan_and_print_head()
    {
        if (dom_has("ajax")) return '';
        
        return 'var scan_and_print = function() { alert("Images are not loaded yet"); };';
    }

    function dom_js_scan_and_print_body()
    {
        if (dom_has("ajax")) return '';

        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>
        
            /* SCAN AND PRINT UTILITY */
        
            dom_on_loaded(function()
            {
                function scan_and_print_scroll_y(e, y0, y1, duration, f) {

                    var  t = 0;
                    var dt = 20;

                    function frame() {

                        t += dt;
                        
                        window.scroll(0, y0 + (t / duration) * (y1 - y0));

                        if (t >= duration) {

                            clearInterval(id);

                            if (typeof(f) != "undefined") f();
                        }
                    }

                    var id = setInterval(frame, dt);
                };

                scan_and_print = function()
                {
                    console.log("DOM: Print");

                    var e = document.querySelector("html");

                    scan_and_print_scroll_y(e, 0, document.body.clientHeight, 500, function() { 
                    scan_and_print_scroll_y(e, document.body.clientHeight, 0, 500, function() { window.print(); }); });
                };
            });

        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_pwa_install()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>
        
            /* PWA (PROGRESSIVE WEB APP) INSTALL */
                
            function onInitPWA()
            {
                let deferredPrompt = null;
                
                console.log("DOM: Register Before Install Prompt callback");
                
                window.addEventListener("beforeinstallprompt", function(e) 
                {
                    console.log("DOM: Before Install Prompt");
                    e.preventDefault();
                    deferredPrompt = e;

                    document.querySelectorAll(".app-install").forEach(function (e) { 
                        e.classList.remove("hidden");
                        e.classList.add("visible");
                        });
                });
                
                document.querySelectorAll(".app-install").forEach(function (e) { 
                    
                    e.addEventListener("click", function(e)
                    {
                        document.querySelectorAll(".app-install").forEach(function (e) { 
                            e.classList.remove("visible");
                            e.classList.add("hidden");
                            });
                        
                        if (deferredPrompt != null)
                        {
                            deferredPrompt.prompt();
                            
                            deferredPrompt.userChoice.then(function(choiceResult)
                            {
                                if (choiceResult.outcome === "accepted") console.log("DOM: User accepted the A2HS prompt");
                                else                                     console.log("DOM: User dismissed the A2HS prompt");
                                
                                deferredPrompt = null;
                            });
                        }
                        else
                        {
                            console.log("DOM: Install promt callback not received yet");
                        }
                    }); 
                }); 
            }; 

            dom_on_loaded(function() { onInitPWA(); });
            
        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_service_worker()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

            /* SERVICE WORKER */

            <?php if (!dom_has("ajax") && dom_has("push_public_key")) { ?>
            function urlBase64ToUint8Array(base64String) { const padding = "=".repeat((4 - base64String.length % 4) % 4); const base64 = (base64String + padding).replace(/\-/g, "+").replace(/_/g, "/"); const rawData = window.atob(base64); return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0))); }
            <?php } ?>
            function onInitServiceWorker()
            {
                if ("serviceWorker" in navigator)
                {
                    console.log("DOM: Service Worker is supported. Registering...");

                    navigator.serviceWorker.register("<?= dom_path('sw.js') ?>").then(
                        
                    function(registration)
                    {
                        console.log("DOM: ServiceWorker registration successful with scope: ", registration.scope);
                        
                        var registration_installing = registration.installing;
                        var registration_waiting    = registration.waiting;

                        if (registration_installing && registration_installing != null)
                        {
                            console.log("DOM: Installing: State:", registration_installing.state);

                            if (registration_installing.state === "activated" && !registration_waiting)
                            {
                                console.log("DOM: Send Clients claim");
                                registration_installing.postMessage({type: "CLIENTS_CLAIM" });
                            }

                            registration_installing.addEventListener("statechange", function()
                            {
                                console.log("DOM: Installing: New state:", registration_installing.state);

                                if (registration_installing.state === "activated" && !registration_waiting) 
                                {
                                    console.log("DOM: Send Clients claim");                                    
                                    registration_installing.postMessage({ type: "CLIENTS_CLAIM" });
                                }
                            });
                        }

                        navigator.serviceWorker.ready.then(function(registration) 
                        {
                            registration.pushManager.getSubscription().then(function(subscription) 
                            {
                                if (!(subscription === null)) 
                                {
                                    console.log("DOM: User IS subscribed.");
                                }
                                else 
                                {
                                    console.log("DOM: User is NOT subscribed.");
                                }
                            })

                        <?php if (dom_has("push_public_key")) { ?>

                            .then(function()
                            {
                                const subscribeOptions = { userVisibleOnly: true, applicationServerKey: urlBase64ToUint8Array("<?= dom_get("push_public_key") ?>") };
                                return registration.pushManager.subscribe(subscribeOptions);

                            }).then(function(pushSubscription)
                            {
                                console.log("DOM: Received PushSubscription: ", JSON.stringify(pushSubscription));
                                return pushSubscription;
                            })

                        <?php } ?>

                            ;
                            
                            if (registration.sync)
                            {  
                                return registration.sync.register("myFirstSync");
                            }
                            else
                            {  
                                console.log("DOM: ServiceWorker registration sync is undefined");
                            }
                        });
                    },                     
                    function(err) 
                    {
                        console.log("DOM: ServiceWorker registration failed: ", err);

                    }).catch(function(err)
                    {
                        console.log("DOM: Service Worker registration failed: ", err);

                    });

                    /* TODO : REGISTER FOR NOTIFICATIONS ON USER GESTURE */

                    if ("PushManager" in window) 
                    {
                    /*
                        console.log("DOM: Service Worker push notifications are supported. Registering...");

                        new Promise(function(resolve, reject) 
                        {
                            Notification.requestPermission().then(function(permission) 
                            {
                                console.log("DOM: Notifications permissions : " + permission);
                                if (permission !== "granted") return reject(Error("Denied notification permission"));
                                resolve();
                            });

                        })
                        .then(function() 
                        {
                            return navigator.serviceWorker.ready;

                        })
                        .then(function(registration)
                        {
                            return registration.sync.register("syncTest");

                        })
                        .then(function()
                        {
                            console.log("DOM: Sync registered");

                        })
                        .catch(function(err) 
                        {
                            console.log("DOM: It broke");
                            console.log(err.message);
                        });
                    */
                    }
                }
                else
                {
                    console.log("DOM: Service worker not supported");
                } 
            }

            dom_on_loaded(function() { onInitServiceWorker(); });
            
        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_framework_material()
    {
        if ("material" != dom_get("framework")) return "";

        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

    /*  MDC (MATERIAL DESIGN COMPONENTS) FRAMEWORK */
   
        if (typeof window.mdc !== "undefined") { window.mdc.autoInit(); }
   
    /*  Adjust toolbar margin */
   
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

    function dom_js_images_loading()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

            /* IMAGES LOADING */
            
            console.log("DOM: Register images handlers");
            
            var dom_interaction_observer = null;
                
            function dom_on_img_error(e)
            {
                var e = this;

                /* Cleanup any loading state markup */

                e.classList.remove("loading"); 
                e.classList.remove("reloading"); 
                e.classList.remove("loaded"); 
                e.classList.remove("failed"); 

                e.classList.remove("lazy");
                e.classList.remove("lazy-observed");
                e.classList.remove("lazy-loaded");

                /* Make it a lazy loading image with additionnal 'reloading' tag */

                e.setAttribute("data-src", e.getAttribute("src"));
                e.setAttribute("src",      "<?= url_img_loading() ?>");

                e.classList.add("loading");
                e.classList.add("reloading");

                e.classList.add("lazy");
                
                setTimeout(function () { 

                    e.classList.remove("lazy");
                    e.classList.add("lazy-observed");
                    
                    dom_interaction_observer.observe(e); 

                    }, 1000);
            }
                
            function dom_img_observer_callback(changes, observer) { 
            
                for (change of changes) {
                    
                    if (change.isIntersecting)
                    {     
                        if (change.target.hasAttribute("data-src"))
                        {
                            change.target.parentElement.querySelectorAll("source[data-srcset]").forEach(function (source) { 

                                var datasrcset = source.getAttribute("data-srcset");

                                source.removeAttribute("srcset");
                                source.removeAttribute("data-srcset");

                                source.classList.remove("lazy");
                                
                                source.setAttribute("srcset", datasrcset);
                            });

                            var datasrc = change.target.getAttribute("data-src");

                            change.target.removeAttribute("src");
                            change.target.removeAttribute("data-src");
                                                                    <?php if (!dom_get("dom_lazy_unload")) { ?>
                            change.target.classList.remove("lazy-observed"); 
                            change.target.classList.remove("lazy");            <?php } ?> 
                            change.target.classList.remove("loading"); 

                            change.target.classList.add("lazy-loaded"); 
                            change.target.classList.add("loaded"); 

                            change.target.setAttribute("src", datasrc);
                        
                        };                                          <?php if (!dom_get("dom_lazy_unload")) { ?>
                        
                        observer.unobserve(change.target);          <?php } ?> 
                    }                                           
                    else
                    {                                                                       <?php if (!!dom_get("dom_lazy_unload")) { ?>
                        if (change.target.classList.contains("lazy-loaded")) {
    
                            change.target.parentElement.querySelectorAll("source[srcset]").forEach(function (source) {
                                
                                source.removeAttribute("data-srcset");
                                source.setAttribute("data-srcset", source.getAttribute("srcset"));
                                source.removeAttribute("srcset");

                                source.classList.add("lazy");
                            });

                            change.target.removeAttribute("data-src");
                            change.target.setAttribute("data-src", change.target.getAttribute("src"));
                            change.target.removeAttribute("src");
                            change.target.setAttribute("src", "<?= url_img_loading() ?>");

                            change.target.classList.remove("lazy-loaded");

                            change.target.classList.add("loading");
                            change.target.classList.remove("loaded");
                                                                            
                        }                                                                   <?php } ?> 
                    }
                };
            };
                
            function dom_observe_lazy_element(e,i)
            {
                e.classList.remove("lazy");
                e.classList.add("lazy-observed");

                dom_interaction_observer.observe(e);        
            }
      
            function dom_scan_images() 
            {
                console.log("DOM: Scanning images");

                /* Handle images loading errors */
                document.querySelectorAll(".img").forEach(function (e) { e.addEventListener("error", dom_on_img_error); });

                /* Scan for lazy elements and make them observed elements */
                document.querySelectorAll("source.lazy[data-srcset]" ).forEach(dom_observe_lazy_element);
                document.querySelectorAll(   "img.lazy[data-src]"    ).forEach(dom_observe_lazy_element);
                document.querySelectorAll("iframe.lazy[data-src]"    ).forEach(dom_observe_lazy_element);
            }

            dom_on_loaded(function () {

                /* Create images intersection observer */
                var options = { rootMargin: '100px 100px 100px 100px' };
                dom_interaction_observer = new IntersectionObserver(dom_img_observer_callback, options);

                /* First images lookup (Needs to be deffered in order to work) */
                setTimeout(dom_scan_images, 0);

                /* Images lookup after any ajax query result (that might have modified the DOM and inserted new images */
                dom_on_ajax(dom_scan_images);
            
                });

        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_toolbar()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

            /* TOOLBAR */
        
            function onUpdateToolbarHeight()
            {
                var toolbar_row_banners = document.querySelectorAll(".toolbar-row-banner");
                var toolbars            = document.querySelectorAll(".toolbar");

                var toolbar_row_banner  = toolbar_row_banners ? toolbar_row_banners[0] : null;
                var toolbar             = toolbars            ? toolbars[0]            : null;

                if (toolbar != null && toolbar_row_banner != null)
                {
                    var header_max_height = window.getComputedStyle(toolbar_row_banner, null).getPropertyValue("max-height").replace("px","");
                    var header_min_height = window.getComputedStyle(toolbar_row_banner, null).getPropertyValue("min-height").replace("px","");
          
                    var stuck_height = header_max_height - header_min_height;

                    if (window.scrollY > stuck_height) { toolbar.classList.add(   "scrolled"); toolbar.classList.remove("top"); }
                    else                               { toolbar.classList.remove("scrolled"); toolbar.classList.add(   "top"); }
          
                    var toolbar_row_banner_height = Math.max(0, header_max_height - window.scrollY);

                    toolbar_row_banner.style.height = toolbar_row_banner_height + "px";

                }
            }

            dom_on_ready( onUpdateToolbarHeight);
            dom_on_loaded(onUpdateToolbarHeight);
            dom_on_scroll(onUpdateToolbarHeight);

        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_back_to_top()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>
            
            /*  BACK TO TOP BUTTON */
            
            var back_to_top_offset          =  300;
            var back_to_top_offset_opacity  = 1200;
            var back_to_top_scroll_duration =  700;
            
            function onUpdateBackToTopButton()
            {
                var back_to_top = document.querySelector(".cd-top");

                if (window.scrollY > back_to_top_offset) 
                {
                    back_to_top.classList.add("cd-is-visible")
                }
                else
                {
                    back_to_top.classList.remove("cd-is-visible");
                    back_to_top.classList.remove("cd-fade-out");
                }
            
                if (window.scrollY > back_to_top_offset_opacity)
                {
                    back_to_top.classList.add("cd-fade-out");
                }
            }

            dom_on_scroll(onUpdateBackToTopButton);
        
        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_sliders()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>
                
            /* SLIDERS */
            
            function updateSlider()
            {
            }
            
            function initSliders()
            {
                document.querySelectorAll(".slider").forEach(function (e) { 

                    /* TODO */
                });
            }

            dom_on_loaded(initSliders);
            
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

    function dom_js_on_document_events_head()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

            /* DOM INTERNAL READY AND LOADED CALLBACK MECHANISM */

            var dom_event_ready  = false;
            var dom_event_loaded = false;

            var dom_ready_callbacks  = Array();
            var dom_loaded_callbacks = Array();
            var dom_scroll_callbacks = Array();
            var dom_resize_callbacks = Array();
            var dom_ajax_callbacks   = Array();

            function dom_process_callbacks(callbacks, log, clear)
            {
                if (typeof log != "undefined" && log) console.log("DOM: DOCUMENT " + log + " : Processing " + callbacks.length + " CALLBACKS");
                callbacks.forEach(function(callback) { callback(); });
                if (typeof log != "undefined" && clear) callbacks = [];
            }

            function dom_process_ready_callbacks()  { dom_process_callbacks(dom_ready_callbacks,  "READY",  true); }
            function dom_process_loaded_callbacks() { dom_process_callbacks(dom_loaded_callbacks, "LOADED", true); }

            function dom_on_ready(callback)  {  dom_ready_callbacks.push(callback); if (dom_event_ready)                     { dom_process_ready_callbacks();  } }
            function dom_on_loaded(callback) { dom_loaded_callbacks.push(callback); if (dom_event_ready && dom_event_loaded) { dom_process_loaded_callbacks(); } }
            function dom_on_scroll(callback) { dom_scroll_callbacks.push(callback); }
            function dom_on_resize(callback) { dom_resize_callbacks.push(callback); }
            function dom_on_ajax(callback)   {   dom_ajax_callbacks.push(callback); }

            function dom_on_init_event(event)
            {
                var was_not_ready_and_loaded = (!dom_event_ready || !dom_event_loaded);

                if (!dom_event_ready  && event == "ready")  { dom_event_ready  = true; console.log("DOM: DOCUMENT READY"); dom_process_ready_callbacks(); }
                if (!dom_event_loaded && event == "loaded") { dom_event_loaded = true; console.log("DOM: DOCUMENT LOADED"); }

                if (was_not_ready_and_loaded && dom_event_ready && dom_event_loaded) { dom_process_loaded_callbacks(); }
            }

            function dom_on_ajax_reception() { dom_process_callbacks(dom_ajax_callbacks); }
        
        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_js_on_document_events()
    {
        dom_heredoc_start(-2); ?><script><?php dom_heredoc_flush(null); ?>

            /* DOM INTERNAL READY AND LOADED CALLBACK MECHANISM */

            window.addEventListener("load",               function(event) { dom_on_init_event("loaded"); } );
            if (document.readyState != "loading")                         { dom_on_init_event("ready");  }
            else document.addEventListener("DOMContentLoaded", function() { dom_on_init_event("ready");  } );
        
            window.addEventListener("scroll", function() { if (dom_event_ready && dom_event_loaded) { dom_process_callbacks(dom_scroll_callbacks); } });
            window.addEventListener("resize", function() { if (dom_event_ready && dom_event_loaded) { dom_process_callbacks(dom_resize_callbacks); } });

        <?php dom_heredoc_flush("raw_js"); ?></script><?php return dom_heredoc_stop(null);
    }

    function dom_script_third_parties()
    {
        $inline_js = dom_get("dom_inline_js", false);

        $jquery_local_filename = !$inline_js ? false : dom_path('js/jquery-'.dom_get("version_jquery").(is_localhost() ? '' : '.min').'.js');

        return/*((!dom_AMP() && $jquery_local_filename) ?                script_src($jquery_local_filename) :                 
                                                                         script_src('https://code.jquery.com/jquery-'                   . dom_get("version_jquery")                                     .(is_localhost() ? '' : '.min').'.js', false, 'async id="jquery" crossorigin="anonymous"')) // Special case because, for now, relyng on jquery for on_ready and on_loaded core events
            . */dom_if("material"  == dom_get("framework"), dom_eol(2) . script_src('https://unpkg.com/material-components-web@'        . dom_get("version_material")  . '/dist/material-components-web'.(is_localhost() ? '' : '.min').'.js', false, "async"))
            .   dom_if("bootstrap" == dom_get("framework"), dom_eol(2) . script_src('https://cdnjs.cloudflare.com/ajax/libs/popper.js/' . dom_get("version_popper")    . '/umd/popper'                  .(is_localhost() ? '' : '.min').'.js', false, "async"))
            .   dom_if("bootstrap" == dom_get("framework"), dom_eol(2) . script_src('https://stackpath.bootstrapcdn.com/bootstrap/'     . dom_get("version_bootstrap") . '/js/bootstrap'                .(is_localhost() ? '' : '.min').'.js', false, "async"))
            ;
    }
    
    function scripts_body()
    {
        if (dom_has("ajax")) return "";

        return  dom_eol(2).dom_script_third_parties                  ().
                dom_eol(2).dom_script_ajax_body                      ().
                dom_eol(2).dom_script_google_analytics               ().               ((!!dom_get("dom_script_document_events",    true)) ? (
                dom_eol(2).dom_script(dom_js_on_document_events      ()).   "") : ""). ((!!dom_get("dom_script_toolbar",            true)) ? (
                dom_eol(2).dom_script(dom_js_toolbar                 ()).   "") : ""). ((!!dom_get("dom_script_back_to_top",        true)) ? (
                dom_eol(2).dom_script(dom_js_back_to_top             ()).   "") : ""). ((!!dom_get("dom_script_images_loading",     true)) ? (
                dom_eol(2).dom_script(dom_js_images_loading          ()).   "") : ""). ((!!dom_get("support_sliders",               true)) ? (
                dom_eol(2).dom_script(dom_js_sliders                 ()).   "") : ""). ((!!dom_get("support_header_backgrounds",   false)) ? (
                dom_eol(2).dom_script(dom_js_toolbar_banner_rotation ()).   "") : ""). ((!!dom_get("support_service_worker",       false)) ? (
                dom_eol(2).dom_script(dom_js_service_worker          ()).   "") : ""). ((!!dom_get("dom_script_pwa_install",        true)) ? (
                dom_eol(2).dom_script(dom_js_pwa_install             ()).   "") : ""). ((!!dom_get("dom_script_framework_material", true)) ? (
                dom_eol(2).dom_script(dom_js_framework_material      ()).   "") : ""). ((!!dom_get("dom_script_scan_and_print",     true)) ? (
                dom_eol(2).dom_script(dom_js_scan_and_print_body     ()).   "") : "");
    }
    
    #endregion
    #region WIP API : DOM : HTML COMPONENTS : MARKUP : BODY
    ######################################################################################################################################

    function dom_html_comment_bgn()  { return "<!-- ";  }
    function dom_html_comment_end()  { return " //-->"; }
    function dom_html_comment($text) { return dom_html_comment_bgn().$text.dom_html_comment_end(); }

    function comment($text)         { return (dom_has("rss")) ? "" : dom_html_comment($text); }
    
    function dom_placeholder($text) { return dom_html_comment("DOM_PLACEHOLDER_".str_replace(" ", "_", strtoupper($text))); }

    function dom_tag($tag, $html, $attributes = false, $force_display = false, $self_closing = false, $extra_attributes_raw = false)
    {
        $space_pos = strpos($tag, ' ');
        
        return (false && dom_has('rss') && !$force_display) ? '' : (
                
                (
                    '<'.$tag.dom_attributes($attributes).
                    (($extra_attributes_raw === false) ? '' : (' '.$extra_attributes_raw))
                ) . 
                (
                    ($self_closing) ? '/>' : 
                    ('>'.$html.'</'.(($space_pos === false) ? $tag : substr($tag, 0, $space_pos)).'>')
                )

            );
    }
    
    function body($html = "", $html_post_scripts = "", $dark_theme = DOM_AUTO)
    {
        $profiler = dom_debug_track_timing();
        
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
        
        $app_js = dom_path_coalesce("js/app.js","app.js");
        
        $body = ''

        . dom_eol(2) . if_browser('lte IE 9', '<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>')
        
    /*  . "<span id='!'></span>" */ // To match url_void() #!
        
        . dom_eol(2) . dom_if(dom_get("support_metadata_person",       false), script_json_ld($properties_person))
        . dom_eol(2) . dom_if(dom_get("support_metadata_organization", false), script_json_ld($properties_organization))
        
        . dom_eol(2) . $html
        . dom_eol(2) . delayed_component("_body")
        
        . dom_eol(2) . dom_if(dom_AMP(), comment("DOM AMP sidebars"))
        . dom_eol(2) . delayed_component("_amp_sidebars")
        . dom_eol(2) . delayed_component("_amp_scripts_body")

        . dom_eol(2) . comment("DOM Body boilerplate markup")
        . dom_eol(2) . back_to_top_link()

        . dom_eol(2) . comment("DOM Body scripts")
        . dom_eol(2) . scripts_body()
        . dom_eol(2) . ($app_js ? comment('CUSTOM script') : comment('Could not find any app.js default user script'))
        
                                                                    .((!dom_get("dom_htaccess_rewrite_php")) ? (""
        . dom_eol(2) . ($app_js ? dom_script($app_js) : '')         ) : (""
        . dom_eol(2) . ($app_js ? script_src($app_js) : '')         ))

        . dom_eol(2) . $html_post_scripts

        . dom_eol(2) . dom_if(dom_AMP() && dom_get("support_service_worker", false), comment("DOM Body AMP service worker"))
        . dom_eol(2) . dom_if(dom_AMP() && dom_get("support_service_worker", false), '<amp-install-serviceworker src="'.dom_path('sw.js').'" layout="nodisplay" data-iframe-src="'.dom_path("install-service-worker.html").'"></amp-install-serviceworker>')
        ;

        if (DOM_AUTO === $dark_theme) $dark_theme = dom_get("dark_theme", false);
        
        return cosmetic(dom_eol(2)).dom_tag(
            'body',
            $body,
            array_merge(array(
                "id"    => "!",
                "class" => dom_component_class('body').($dark_theme ? dom_component_class('dark') : '')
                ), AMP() ? array() : array(
                "name"  => "!"
                ))
            );
    }
    
    function cosmetic($html)
    {
        return !!dom_get("minify") ? '' : (!!dom_get("beautify", false) ? $html : '');
    }
    
//  HTML tags
        
    function h($h, $html = "", $attributes = false, $anchor = false)
    {
        hook_headline($h, $html);

        return  cosmetic(dom_eol(1)).
                (($h==get("dom_headline_anchor_level",2) || !!$anchor)?anchor(!!$anchor ? $anchor : $html):'').
                dom_tag(
                    'h'.$h,
                    $html,
                    is_array($attributes) ? dom_attributes_add_class($attributes, dom_component_class('headline headline'.$h))
                    : array(
                        "class" => ("$attributes ".dom_component_class('headline headline'.$h)),
                        "id"    => anchor_name(!!$anchor ? $anchor : $html)
                        )
                    );
    }

    function div            ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('div',                        $html,                                                $attributes                                                         );                      }
    function p              ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('p',                          $html,                                                $attributes                                                         );                      }
    function i              ($html = "", $attributes = false) {                             return                         dom_tag('i',                          $html,                                                $attributes                                                         );                      }
    function pre            ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('pre',                        $html,                                                $attributes                                                         );                      }
    function ul             ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('ul',                         $html.cosmetic(dom_eol(1)),                               $attributes                                                         );                      }
    function ol             ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('ol',                         $html.cosmetic(dom_eol(1)),                               $attributes                                                         );                      }
    function li             ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('li',                         $html,                                                $attributes                                                         );                      }

    function dom_table      ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('table',                      $html.cosmetic(dom_eol(1)),    dom_attributes_add_class(  $attributes, dom_component_class('table'))                              );                      }
    function tr             ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('tr',                         $html,                                                $attributes                                                         );                      }
    function td             ($html = "", $attributes = false) {                             return                         dom_tag('td',                         $html,                                                $attributes                                                         );                      }
    function th             ($html = "", $attributes = false) {                             return                         dom_tag('th',                         $html,                                                $attributes                                                         );                      }

    function strong         ($html = "", $attributes = false) {                             return                     dom_tag('strong',                     $html,                                                $attributes                                                         );                      }
    function strike         ($html = "", $attributes = false) {                             return                     dom_tag('s',                          $html,                                                $attributes                                                         );                      }
    function del            ($html = "", $attributes = false) {                             return                     dom_tag('del',                          $html,                                                $attributes                                                         );                      }
    function em             ($html = "", $attributes = false) {                             return                     dom_tag('em',                         $html,                                                $attributes                                                         );                      }
    function span           ($html = "", $attributes = false) {                             return                     dom_tag('span',                       $html,                                                $attributes                                                         );                      }
    function figure         ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('figure',                     $html.cosmetic(dom_eol(1)),                               $attributes                                                         );                      }
    function figcaption     ($html = "", $attributes = false) {                             return                     dom_tag('figcaption',                 $html,                                                $attributes                                                         );                      }

    function details        ($html = "", $attributes = false) {                             return                     dom_tag('details',                    $html,                                                $attributes                                                         );                      }
    function summary        ($html = "", $attributes = false) {                             return                     dom_tag('summary',                    $html,                                                $attributes                                                         );                      }

    function form           ($html = "", $attributes = false) { hook_amp_require("form");   return                     dom_tag('form',                       $html,                                                $attributes                                                         );                      }

    function checkbox       ($id, $html = "", $attributes = false) {                        return                     dom_tag('input',                       $html, array("class"    => ("$attributes " . dom_component_class('checkbox')),       "id"  => $id, "type" => "checkbox") );     }
    function checkbox_label ($id, $html = "", $attributes = false) {                        return                     dom_tag('label',                       $html, array("class"    => ("$attributes " . dom_component_class('checkbox-label')), "for" => $id)      );                      }

    function button         ($html = "", $attributes = false) {                             return                     dom_tag('button',                     $html,                     dom_attributes_add_class(  $attributes, dom_component_class('button'))                             );                      }
    function button_label   ($html = "", $attributes = false) {                             return                     dom_tag('span',                       $html,                     dom_attributes_add_class(  $attributes, dom_component_class('button-label'))                       );                      }

    function h1             ($html = "", $attributes = false, $anchor = false) {            return                     h(1,                               $html,                                                $attributes, $anchor                                                );                      }
    function h2             ($html = "", $attributes = false, $anchor = false) {            return                     h(2,                               $html,                                                $attributes, $anchor                                                );                      }
    function h3             ($html = "", $attributes = false, $anchor = false) {            return                     h(3,                               $html,                                                $attributes, $anchor                                                );                      }
    function h4             ($html = "", $attributes = false, $anchor = false) {            return                     h(4,                               $html,                                                $attributes, $anchor                                                );                      }
    function h5             ($html = "", $attributes = false, $anchor = false) {            return                     h(5,                               $html,                                                $attributes, $anchor                                                );                      }
    function section        ($html = "", $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('section',                    $html,                     dom_attributes_add_class(  $attributes, 'section')                                             );                      }
    function dom_header     ($html = "", $attributes = false) { $profiler = dom_debug_track_timing();   return    cosmetic(dom_eol(1)).dom_tag('header',                     $html.cosmetic(dom_eol(1)),    dom_attributes_add_class(  $attributes, 'header')                                              ).cosmetic(dom_eol(1));     }
                   
    function hr             (            $attributes = false) {                             return    cosmetic(dom_eol(1)).dom_tag('hr',                         false,                                                $attributes, false, true                                            );                      }
    function br             (            $attributes = false) {                             return                     dom_tag('br',                         false,                                                $attributes, false, true                                            );                      }

    function clearfix       () { return div("","clearfix"); }

    function dom_main       ($html = "", $attributes = false) { return content($html, $attributes); }
    function content        ($html = "", $attributes = false) { $profiler = dom_debug_track_timing();   return clearfix().cosmetic(dom_eol(2)).dom_tag('main',     cosmetic(dom_eol(1)).$html.cosmetic(dom_eol(1)),    dom_attributes_add_class(   $attributes,    dom_component_class('main')                 .                           ' ' . 
                                                                                                                                                                                                                                        dom_component_class('content')              . (!!dom_get("toolbar") ? ( ' ' . 
                                                                                                                                                                                                                                        dom_component_class('main-below-toolbar')   ) : '')) ).cosmetic(dom_eol(1)); }
    function dom_footer     ($html = "", $attributes = false) { $profiler = dom_debug_track_timing();   return clearfix().cosmetic(dom_eol(2)).dom_tag('footer',   cosmetic(dom_eol(1)).$html.cosmetic(dom_eol(1)),    dom_attributes_add_class(   $attributes,    dom_component_class('footer')) ); }
    
    function icon           ($icon, $attributes = false) { return      i($icon,      dom_attributes_add_class($attributes, 'material-icons')); }
    function button_icon    ($icon, $label      = false) { return button(icon($icon, dom_component_class('action-button-icon')), array("class" => dom_component_class("action-button"), "aria-label" => (($label === false) ? $icon : $label))); }
    
    if (!function_exists("table")) { function table($html = "", $attributes = false) { return dom_table($html, $attributes); } }

    function dom_supported_ratios()
    {
        return array(
                
            array(21,  9),  array( 9, 21),  
            array(16,  9),  array( 9, 16),  
            array(16, 10),  array(10, 16),  
            array( 5,  4),  array( 4,  5),  
            array( 5,  1),  array( 1,  5),  
            array( 4,  3),  array( 3,  4),  
            array( 3,  2),  array( 2,  3),  
            array( 2,  1),  array( 1,  2),  
            array( 1,  1),  array( 1,  1)   
            );
    }

    function class_aspect_ratio($w = 1200, $h = 675) // 16:9
    {
        $class = "";

        if ((string)(int)$h == (string)$h)
        {
            $class = "aspect-ratio-16-9"; foreach (dom_supported_ratios() as $ratio) 
            
                if (((int)$w/(int)$h)==($ratio[0]/$ratio[1]))  $class = "aspect-ratio-".$ratio[0]."-".$ratio[1]."";

            $class = 'aspect-ratio '.$class;
        }

        return $class;
    }

    function div_aspect_ratio($html, $w = 1200, $h = 675) // 16:9
    {
        $class = class_aspect_ratio($w, $h);

        if ($class != "")
        {
            $class = ' class="'.$class.'"';
            return '<div'.$class.'>'.$html.'</div>';
        }

        return div($html);
        
    }
        
    function iframe($url, $title = false, $classes = false, $w = false, $h = false, $lazy = DOM_AUTO)
    {   
        // TODO See https://benmarshall.me/responsive-iframes/ for frameworks integration   
        // TODO if EXTERNAL LINK add crossorigin="anonymous" (unless AMP)

        $w = ($w === false) ? "1200" : $w;
        $h = ($h === false) ?  "675" : $h;

        hook_amp_require("iframe");
        
        if (AMP()) $lazy = false;

        $src_attributes = ' src="'.$url.'"';
        if ($lazy === true) $src_attributes = ' data-src="'.$url.'" src="'.url_img_loading().'"';
        
        $lazy_attributes = "";

        if ($lazy === DOM_AUTO) $lazy_attributes = ' loading="lazy"';
        if ($lazy === true)     $lazy_attributes = ' lazy loading';

        return div_aspect_ratio('<'.(dom_AMP() ? 'amp-iframe sandbox="allow-scripts"' : 'iframe').

             (!!$title   ? (' title'            .'="'.$title        .'"') : '').
             (!!$classes ? (' class'            .'="'.$classes      .'"') : '') .
             
                            $lazy_attributes.             
                            $src_attributes.

                            ' width'            .'="'.$w            .'"'.
                            ' height'           .'="'.$h            .'"'.
                            ' layout'           .'="'.'responsive'  .'"'.
                            ' frameborder'      .'="'.'0'           .'"'.
                            ' style'            .'="'.'border:0;'   .'"'.
                            ' allowfullscreen'  .'="'.''            .'"'.

                            '>'.

            dom_if(dom_AMP(), '<amp-img layout="fill" src="'.url_img_blank().'" placeholder></amp-img>').
            
            '</'.(dom_AMP() ? 'amp-iframe' : 'iframe').'>', $w, $h);
    }

    function google_calendar($id, $w = false, $h = false)
    {
        $src = $id;

        if (false === stripos($id, "http"))
        {
            $src = 'https://calendar.google.com/calendar/embed'
            
            .'?'    .'showTitle'        .'=0'
            .'&amp;'.'showPrint'        .'=0'
            .'&amp;'.'showCalendars'    .'=0'
            .'&amp;'.'showTz'           .'=0'
            .'&amp;'.'height'           .'='.$h.''
            .'&amp;'.'wkst'             .'=2'
            .'&amp;'.'bgcolor'          .'=%23FFFFFF'
            .'&amp;'.'src'              .'='.$id.'%40group.calendar.google.com'
            .'&amp;'.'color'            .'=%2307bdcb'
            .'&amp;'.'ctz'              .'=Europe%2FParis';
        }
        
        if (dom_AMP()) return a('https://calendar.google.com', $src, DOM_EXTERNAL_LINK);
        
        return iframe($src, "Google Calendar", "google-calendar", $w, $h).a('https://calendar.google.com', $src, DOM_EXTERNAL_LINK);
    }
        
    function google_map($embed_url, $w = false, $h = false, $lazy = DOM_AUTO)
    {
        return iframe($embed_url, "Google Map", "google-map", $w, $h, $lazy);
    }
        
    function google_doc($id, $w = false, $h = false, $lazy = DOM_AUTO)
    {
        $src = $id;

        if (false === stripos($id, "http"))
        {
            $src = "https://docs.google.com/document/$id/pub?embedded=true";
        }

        return iframe($src, "Google Doc", "google-doc", $w, $h, $lazy);
    }
       
    function google_video($id, $w = false, $h = false, $lazy = DOM_AUTO)
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

            return div_aspect_ratio(iframe($url, "Google Video", "google-video", $w, $w, $lazy), $w, $h);
        }
    }
        
    function json_google_photo_album_from_content($url)
    {
        $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36'));
        $context = stream_context_create($options);
        $html    = @file_get_contents($url, false, $context);
        
        if ($html)
        {
            $tag_bgn = ", data:";
            $tag_end = ", sideChannel";
            
            $pos_bgn = strrpos($html, $tag_bgn, 0);
            $pos_end =  strpos($html, $tag_end, $pos_bgn);
            
            if (false !== $pos_bgn && false !== $pos_end)
            {
                $json   = substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));
                $result = json_decode($json, true);
                return $result;
            }
        }
        
        return false;
    }
    
    function google_photo_album($url, $wrapper = "div", $img_wrapper = "self", $link_to_google = true, $randomize = false)
    {
        $results = json_google_photo_album_from_content($url);

        $photos  = dom_at($results, 1, array());

        if ($randomize)
        {
            shuffle($photos);
        }
        
        $images = "";
        
        foreach ($photos as $i => $photo_result)
        {
            $photo_url = dom_at(dom_at($photo_result, 1), 0);
            
            $images .= call_user_func($img_wrapper, img($photo_url, false, "Photo"), $photo_url, $i);
        }

        $album = call_user_func($wrapper, $images);

        if ($link_to_google)
        {
            $album = a($album, $url, DOM_EXTERNAL_LINK);
        }

        return $album;
    }
    
    // Components with BlogPosting microdata

    function article            ($html = "", $attributes = false) { return cosmetic(dom_eol(1)).dom_tag('article', $html, /*'itemscope="" itemtype="https://schema.org/BlogPosting" ' .*/ dom_attributes_add_class($attributes, "article")); }
    
    function span_author        ($html)             { return span ($html/*, array("itemprop" => "author", "itemscope" => "", "itemtype" => "https://schema.org/Person" )*/); }
    function span_name          ($html)             { return span ($html/*, array("itemprop" => "name"                                                                 )*/); }
    function span_datepublished ($date, $timestamp) { return span ($date/*, array("itemprop" => "datePublished", "datetime" => date("c",$timestamp)                    )*/); }
    function div_articlebody    ($html)             { return div  ($html/*, array("itemprop" => "articleBody"                                                          )*/); }
    
    // LINKS

    $__includes = array();

    function dom_href($link, $target = false)
    {
        $extended_link = $link;

        if ($target !== DOM_EXTERNAL_LINK)
        {
            if (!!dom_get("static"))
            {
                if (false === stripos($extended_link, "?")
                &&  false === stripos($extended_link, "&")
                &&  false === stripos($extended_link, "#"))
                {
                    foreach (dom_get("dom_forwarded_flags") as $forward_flag)
                    {
                        if (!!dom_get($forward_flag) && (in_array($forward_flag, array("rss","json","tile","amp"))))
                        {
                            $extended_link = "$extended_link/$forward_flag";
                        }
                    }
                }
            }
            else
            {
                foreach (dom_get("dom_forwarded_flags") as $forward_flag)
                {
                    if (get($forward_flag) !== false
                    &&  false === stripos($extended_link,"?$forward_flag") 
                    &&  false === stripos($extended_link,"&$forward_flag") 
                    &&  0     !== stripos($extended_link,"#"))
                    {
                        $extended_link .= ((false === stripos($extended_link,"?")) ? "?" : "") . ("&$forward_flag=".dom_get($forward_flag));
                    }
                }
            }
        }

        return $extended_link;
    }
  
    function dom_a($html, $url = false, $external_attributes = false, $target = false)
    {
        if ($url                 === false
        &&  $external_attributes === false
        &&  $target              === false) $url = $html;

        if (($external_attributes === DOM_INTERNAL_LINK || $external_attributes === DOM_EXTERNAL_LINK) && $target === false) { $target = $external_attributes; $external_attributes = false; }
        if ($target === false) { $target = ((0 === stripos($url, "http")) || (0 === stripos($url, "//"))) ? DOM_EXTERNAL_LINK : DOM_INTERNAL_LINK; }
        
        $extended_link = dom_href($url, $target);

        $internal_attributes = array("href" => (($url === false) ? url_void() : $extended_link), "target" => $target);

        if ($target == DOM_EXTERNAL_LINK)           $internal_attributes["rel"]         = "noopener noreferrer";
        if ($target == DOM_EXTERNAL_LINK && !AMP()) $internal_attributes["crossorigin"] = "anonymous";

        $attributes = "";
        
        if (is_array($external_attributes))
        {
            foreach ($external_attributes as $type => $attribute)
            {
                if (in_array($type, $internal_attributes))
                {
                    $internal_attributes[$type] .= " ".$attribute;
                }
                else
                {
                    $internal_attributes[$type] = $attribute;
                }
            }

            $attributes = dom_attributes($internal_attributes);
        }
        else
        {
            $attributes =   dom_attributes($internal_attributes).
                            dom_attributes_add_class($external_attributes, "a");
        }

        if ($target == DOM_INTERNAL_LINK)
        {
            dom_hook_link($html, $url);
        }

        return dom_tag('a', $html, $attributes);
    }

    function a_email($email, $text = false, $attributes = false)
    {
        $text = ($text === false) ? $email : $text;
        
        if (dom_AMP())
        {
            return a($text, "mailto:" . $email, $attributes, DOM_EXTERNAL_LINK);
        }
        else
        {
            $script  = "document.getElementById('".md5($text)."').setAttribute('href','mailto:".preg_replace("/\"/","\\\"",$email)."'); document.getElementById('".md5($text)."').innerHTML = '".$text."';";
            
            $crypted_script = ""; for ($i=0; $i < strlen($script); $i++) { $crypted_script = $crypted_script.'%'.bin2hex(substr($script, $i, 1)); }

            return a("", "", array("aria-label" => "$text email", "id" => md5($text)), DOM_EXTERNAL_LINK).dom_script("eval(unescape('".$crypted_script."'))");
        }
    }

    function char_phone()  { return "☎"; }
    function char_email()  { return "✉"; }
    function char_anchor() { return "⚓"; }
  //function char_unsec()  { return " "; }
    function char_unsec()  { return "&nbsp;"; }
    function char_amp()    { return "&amp;"; }
   
    function nbsp($count = 1) { return str_repeat(char_unsec(), $count); }
    
    function anchor_name($name, $tolower = DOM_AUTO) { return dom_to_classname($name, $tolower); }

    function anchor($name, $character = false, $tolower = DOM_AUTO)
    {
        $name = str_replace("#", "", $name); // Fix common mistake
        $id   = anchor_name($name, $tolower);

        $attributes = array("id" => $id, "class" => "anchor");
        if (!AMP()) $attributes["name"] = $id;

        if (false === $character)
        {
            return span("", $attributes);
        }
        else
        {        
            return dom_a((true === $character) ? char_anchor() : $character, "#".$id, $attributes);
        }
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
    
    function video($path, $attributes = false, $alt = false, $lazy = DOM_AUTO)
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
            "video",
            dom_tag("source", '', dom_attributes(array("src" => $path, "type" => ("video/".str_replace(".","",$ext)))), false, true),
            dom_attributes(
                array_merge(
                    dom_AMP() ? array() : array("alt" => $alt), 
                    array("width" => "100%", "controls" => "no")
                    )
                )            
        );
    }
    
    // IMAGES
    
    function picture($html, $attributes = false, $alt = false, $lazy = DOM_AUTO, $lazy_src = false)
    {
        if (false === stripos($html, "<img")
        &&  false === stripos($html, "<amp-img")) $html = img($html, false, $alt, $lazy, $lazy_src);

        if (AMP())
        {
            $tag_bgn = dom_html_comment_bgn();
            $tag_end = dom_html_comment_end();

            $prefered_src = false;

            while (true)
            {
                $pos_bgn = stripos($html, $tag_bgn); if (false === $pos_bgn) break;
                $pos_end = stripos($html, $tag_end); if (false === $pos_end) break;

                $prefered_src = trim(substr($html, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn)));
                $html = substr($html, $pos_end + strlen($tag_end));
            }

            if ($prefered_src !== false)
            {
                return img($prefered_src, $attributes, $alt, false, false, $html);
            }
            else
            {
                return $html;
            }
        }
        else
        {
            return dom_tag('picture', $html, $attributes);
        }
    }

    function source($path)
    {
        if (AMP())
        {
            return dom_html_comment($path);
        }
        else
        {
            $src    = explode('?', $path);
            $src    = $src[0];
            $info   = pathinfo($src);
            $ext    = array_key_exists('extension', $info) ? '.'.$info['extension'] : false;
            $type   = substr($ext,1); // ! TODO Find better solution

            return dom_tag("source", "", array("type" => "image/$type", "srcset" => $path), false, true);
        }
    }
    
    function img($path, $attributes = false, $alt = false, $lazy = DOM_AUTO, $lazy_src = false, $content = '')
    {
        if (is_array($path)) 
        {
            return wrap_each($path, "", "img", true, $attributes, $alt, $lazy);
        }

        if ($path === false) return '';

        $path     = dom_path($path);
        $info     = explode('?', $path);
        $info     = $info[0];
        $info     = pathinfo($info);
        $ext      = array_key_exists('extension', $info) ? '.'.$info['extension'] : false;
        $codename = urlencode(basename($path, $ext));
        $alt      = ($alt === false || $alt === "") ? $codename : $alt;
        
        $lazy_src = ($lazy_src === false) ? url_img_loading() : $lazy_src;

        if (is_array($attributes) && !array_key_exists("class", $attributes)) $attributes["class"] = "";

        $w = (is_array($attributes) && array_key_exists("width",  $attributes)) ? $attributes["width"]  : dom_get("default_image_ratio_w", 300);
        $h = (is_array($attributes) && array_key_exists("height", $attributes)) ? $attributes["height"] : dom_get("default_image_ratio_h", 200);

        if (!!dom_get("no_js") && $lazy === true) $lazy = DOM_AUTO;

        // TODO if EXTERNAL LINK add crossorigin="anonymous"

        if (AMP())
        {
            return dom_tag('amp-img'.               ($content =='' ? (
                ' fallback'.                        '') : '').
                ' layout'.  '='.'"responsive"'.
                ' width'.   '='.$w.
                ' height'.  '='.$h,
                $content,
                dom_attributes(array("src" => $path)).
                dom_attributes_add_class($attributes, "img"),
                false,
                false
                );
        }
        else
        {
            $img_nth = dom_get("dom_img_nth", 1);
            
            if ($img_nth <= dom_get("dom_img_lazy_loading_after"))
            {
                $lazy = false;
            }

            dom_set("dom_img_nth", $img_nth + 1);

                 if (DOM_AUTO === $lazy) $attributes = dom_attributes(array("alt" => $alt, "loading" => "lazy", "src" =>                          $path )).dom_attributes_add_class($attributes, "img");
            else if (true     === $lazy) $attributes = dom_attributes(array("alt" => $alt, "loading" => "auto", "src" => $lazy_src, "data-src" => $path )).dom_attributes_add_class($attributes, "img lazy loading");
            else                         $attributes = dom_attributes(array("alt" => $alt,                      "src" =>                          $path )).dom_attributes_add_class($attributes, "img");

            return dom_tag('img', $content, $attributes, false, $content == '');
        }
    }
    
    function img_svg($path, $attributes = false)
    {
        return img($path, $attributes ? $attributes : array("style" => "width: 100%; height: auto"));
    }

    function svg($label, $x0, $x1, $y0, $y1, $align, $svg_body) 
    {
        return dom_tag('span', 
            '<svg '. 'class="svg" '.
                      'role="img"'.                     (($label!="" && $label!=false)?(' '.
                'aria-label="'.$label.'"'.              ''):('')).' '.
                   'viewBox="'."$x0 $x1 $y0 $y1".'">'.
                $svg_body.
            '</svg>', 
            array('class' => ('span-svg-wrapper'.($align ? ' span-svg-wrapper-aligned' : '')))
            );
    }

    // https://materialdesignicons.com/

    function predefined_svg_brands()
    {
        return array(
            "500px",    
            "flickr",         
            "facebook",       
            "twitter",        
            "linkedin",       
            "instagram",      
            "pinterest",      
            "tumblr",         
            "rss",            
            "printer",        
            "notifications",  
            "messenger",      
            "alert",          
            "amp",            
            "loading",        
            "darkandlight", 
            "leboncoin",      
            "seloger",        
            "numerama",
            "google",
            "youtube",
            "github",
            "deezer",
            "soundcloud",
            "link"
        );
    }

    function predefined_svg_brands_css_boilerplate($fn_color_transform = "self")
    {
        $css = "";

        foreach (predefined_svg_brands() as $svg)
        {
            $fn_color = "color_$svg";
            $colors   = $fn_color();
            $colors   = is_array($colors) ? $colors : array($colors);
            $class    = "palette-$svg";
            $var      = "--color-$svg";
    
            $css .= dom_eol().dom_tab(1);
            for ($i = 0; $i < count($colors); ++$i) $css .= pan("svg path.$class".(($i > 0) ? ("-".($i+1)) : ""), $i == 0 ? 47 : 0)." { fill: var(".$var.(($i > 0) ? ("-".($i+1)) : "")."); } ";
        }

        return $css;
    }

    function brand_color_properties($fn_color_transform = "self", $pan = 35)
    {   
        $css = "";

        $color_contrast_target  = strtolower(dom_get("contrast","AA"));
        $color_contrast_target  = (($color_contrast_target == "a"  ) ? DOM_COLOR_CONTRAST_AA_LARGE
                                : (($color_contrast_target == "aa" ) ? DOM_COLOR_CONTRAST_AA_NORMAL
                                : (($color_contrast_target == "aaa") ? DOM_COLOR_CONTRAST_AAA_NORMAL : $color_contrast_target)));

        foreach (predefined_svg_brands() as $b => $svg)
        {
            $fn_color = "color_$svg";
            $colors   = $fn_color();
            $colors   = is_array($colors) ? $colors : array($colors);
            $class    = "palette-$svg";
    
            $css .= dom_eol().dom_tab(1);
            
            for ($i = 0; $i < count($colors); ++$i)
            {
                $color = $colors[$i];

                if (function_exists($fn_color_transform))
                {   
                    $color = $fn_color_transform($color);
                }
                else
                {
                    $background_color = get($fn_color_transform, $fn_color_transform);

                    if (false !== stripos($background_color, "#"))
                    {
                        $color = dom_correct_auto(
                            $color,
                            $background_color,
                            $color_contrast_target
                            );
                    }
                }

                $css .= pan("--color-".$svg.(($i > 0) ? ("-".($i+1)) : "").":", $i == 0 ? $pan : 0)." ".$color.";";
            }
        }
        
        return $css;
    }

    // !TOOD DEPRECATE FUNCTION SIGNATURE AND REMOVE COLOR PARAM

    function svg_flickr         ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-flickr";          return svg($label === DOM_AUTO ? "Flickr"          : $label,   0,      0,     232.422, 232.422,  $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M43,73.211c-23.71,0-43,19.29-43,43s19.29,43,43,43c23.71,0,43-19.29,43-43S66.71,73.211,43,73.211z"/><path class="'.$class.'-2" d="M189.422,73.211c-23.71,0-43,19.29-43,43s19.29,43,43,43c23.71,0,43-19.29,43-43S213.132,73.211,189.422,73.211z"/>'); }
    function svg_facebook       ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-facebook";        return svg($label === DOM_AUTO ? "Facebook"        : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M5,3H19A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3M18,5H15.5A3.5,3.5 0 0,0 12,8.5V11H10V14H12V21H15V14H18V11H15V9A1,1 0 0,1 16,8H18V5Z" />'); }
    function svg_twitter        ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-twitter";         return svg($label === DOM_AUTO ? "Twitter"         : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M22.46,6C21.69,6.35 20.86,6.58 20,6.69C20.88,6.16 21.56,5.32 21.88,4.31C21.05,4.81 20.13,5.16 19.16,5.36C18.37,4.5 17.26,4 16,4C13.65,4 11.73,5.92 11.73,8.29C11.73,8.63 11.77,8.96 11.84,9.27C8.28,9.09 5.11,7.38 3,4.79C2.63,5.42 2.42,6.16 2.42,6.94C2.42,8.43 3.17,9.75 4.33,10.5C3.62,10.5 2.96,10.3 2.38,10C2.38,10 2.38,10 2.38,10.03C2.38,12.11 3.86,13.85 5.82,14.24C5.46,14.34 5.08,14.39 4.69,14.39C4.42,14.39 4.15,14.36 3.89,14.31C4.43,16 6,17.26 7.89,17.29C6.43,18.45 4.58,19.13 2.56,19.13C2.22,19.13 1.88,19.11 1.54,19.07C3.44,20.29 5.7,21 8.12,21C16,21 20.33,14.46 20.33,8.79C20.33,8.6 20.33,8.42 20.32,8.23C21.16,7.63 21.88,6.87 22.46,6Z" />'); }
    function svg_linkedin       ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-linkedin";        return svg($label === DOM_AUTO ? "Linkedin"        : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3H19M18.5,18.5V13.2A3.26,3.26 0 0,0 15.24,9.94C14.39,9.94 13.4,10.46 12.92,11.24V10.13H10.13V18.5H12.92V13.57C12.92,12.8 13.54,12.17 14.31,12.17A1.4,1.4 0 0,1 15.71,13.57V18.5H18.5M6.88,8.56A1.68,1.68 0 0,0 8.56,6.88C8.56,5.95 7.81,5.19 6.88,5.19A1.69,1.69 0 0,0 5.19,6.88C5.19,7.81 5.95,8.56 6.88,8.56M8.27,18.5V10.13H5.5V18.5H8.27Z" />'); }
    function svg_github         ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-github";          return svg($label === DOM_AUTO ? "Github"          : $label,   0,      0,      16,      16,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path>'); }
    function svg_instagram      ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-instagram";       return svg($label === DOM_AUTO ? "Instagram"       : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M17.25,5.5A1.25,1.25 0 0,1 18.5,6.75A1.25,1.25 0 0,1 17.25,8A1.25,1.25 0 0,1 16,6.75A1.25,1.25 0 0,1 17.25,5.5M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z" />'); }
    function svg_pinterest      ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-pinterest";       return svg($label === DOM_AUTO ? "Pinterest"       : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M13,16.2C12.2,16.2 11.43,15.86 10.88,15.28L9.93,18.5L9.86,18.69L9.83,18.67C9.64,19 9.29,19.2 8.9,19.2C8.29,19.2 7.8,18.71 7.8,18.1C7.8,18.05 7.81,18 7.81,17.95H7.8L7.85,17.77L9.7,12.21C9.7,12.21 9.5,11.59 9.5,10.73C9.5,9 10.42,8.5 11.16,8.5C11.91,8.5 12.58,8.76 12.58,9.81C12.58,11.15 11.69,11.84 11.69,12.81C11.69,13.55 12.29,14.16 13.03,14.16C15.37,14.16 16.2,12.4 16.2,10.75C16.2,8.57 14.32,6.8 12,6.8C9.68,6.8 7.8,8.57 7.8,10.75C7.8,11.42 8,12.09 8.34,12.68C8.43,12.84 8.5,13 8.5,13.2A1,1 0 0,1 7.5,14.2C7.13,14.2 6.79,14 6.62,13.7C6.08,12.81 5.8,11.79 5.8,10.75C5.8,7.47 8.58,4.8 12,4.8C15.42,4.8 18.2,7.47 18.2,10.75C18.2,13.37 16.57,16.2 13,16.2M20,2H4C2.89,2 2,2.89 2,4V20A2,2 0 0,0 4,22H20A2,2 0 0,0 22,20V4C22,2.89 21.1,2 20,2Z" />'); }
    function svg_tumblr         ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-tumblr";          return svg($label === DOM_AUTO ? "Tumblr"          : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M16,11H13V14.9C13,15.63 13.14,16 14.1,16H16V19C16,19 14.97,19.1 13.9,19.1C11.25,19.1 10,17.5 10,15.7V11H8V8.2C10.41,8 10.62,6.16 10.8,5H13V8H16M20,2H4C2.89,2 2,2.89 2,4V20A2,2 0 0,0 4,22H20A2,2 0 0,0 22,20V4C22,2.89 21.1,2 20,2Z" />'); }
    function svg_rss            ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-rss";             return svg($label === DOM_AUTO ? "RSS"             : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M6.18,15.64A2.18,2.18 0 0,1 8.36,17.82C8.36,19 7.38,20 6.18,20C5,20 4,19 4,17.82A2.18,2.18 0 0,1 6.18,15.64M4,4.44A15.56,15.56 0 0,1 19.56,20H16.73A12.73,12.73 0 0,0 4,7.27V4.44M4,10.1A9.9,9.9 0 0,1 13.9,20H11.07A7.07,7.07 0 0,0 4,12.93V10.1Z" />'); }
    function svg_printer        ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-printer";         return svg($label === DOM_AUTO ? "Printer"         : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M18,3H6V7H18M19,12A1,1 0 0,1 18,11A1,1 0 0,1 19,10A1,1 0 0,1 20,11A1,1 0 0,1 19,12M16,19H8V14H16M19,8H5A3,3 0 0,0 2,11V17H6V21H18V17H22V11A3,3 0 0,0 19,8Z" />'); }
    function svg_notifications  ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-printer";         return svg($label === DOM_AUTO ? "Notifications"   : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M14,20A2,2 0 0,1 12,22A2,2 0 0,1 10,20H14M12,2A1,1 0 0,1 13,3V4.08C15.84,4.56 18,7.03 18,10V16L21,19H3L6,16V10C6,7.03 8.16,4.56 11,4.08V3A1,1 0 0,1 12,2Z" />'); }
    function svg_messenger      ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-messenger";       return svg($label === DOM_AUTO ? "Messenger"       : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M12,2C6.5,2 2,6.14 2,11.25C2,14.13 3.42,16.7 5.65,18.4L5.71,22L9.16,20.12L9.13,20.11C10.04,20.36 11,20.5 12,20.5C17.5,20.5 22,16.36 22,11.25C22,6.14 17.5,2 12,2M13.03,14.41L10.54,11.78L5.5,14.41L10.88,8.78L13.46,11.25L18.31,8.78L13.03,14.41Z" />'); }
    function svg_alert          ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-alert";           return svg($label === DOM_AUTO ? "Alert"           : $label,   0,      0,      24,      24,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M13,13H11V7H13M13,17H11V15H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />'); }
    function svg_amp            ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-amp";             return svg($label === DOM_AUTO ? "AMP"             : $label, -22,    -22,     300,     300,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M171.887 116.28l-53.696 89.36h-9.728l9.617-58.227-30.2.047c-2.684 0-4.855-2.172-4.855-4.855 0-1.152 1.07-3.102 1.07-3.102l53.52-89.254 9.9.043-9.86 58.317 30.413-.043c2.684 0 4.855 2.172 4.855 4.855 0 1.088-.427 2.044-1.033 2.854l.004.004zM128 0C57.306 0 0 57.3 0 128s57.306 128 128 128 128-57.306 128-128S198.7 0 128 0z" />'); }
    function svg_loading        ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-loading";         return svg($label === DOM_AUTO ? "Loading"         : $label,   0,      0,      96,      96,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50"><animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 48 48" to="360 48 48" repeatCount="indefinite" /></path>'); }
    function svg_darkandlight   ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-darkandlight";    return svg($label === DOM_AUTO ? "DarkAndLight"    : $label, -12,    -12,     640,     640,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M289.203,0C129.736,0,0,129.736,0,289.203C0,448.67,129.736,578.405,289.203,578.405 c159.467,0,289.202-129.735,289.202-289.202C578.405,129.736,448.67,0,289.203,0z M28.56,289.202 C28.56,145.48,145.481,28.56,289.203,28.56l0,0v521.286l0,0C145.485,549.846,28.56,432.925,28.56,289.202z"/>'); }
    function svg_google         ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-google";          return svg($label === DOM_AUTO ? "Google"          : $label,   0,      0,      48,      48,      $align == DOM_AUTO ? false : !!$align, '<defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><path class="'.$class.'-2" clip-path="url(#b)" d="M0 37V11l17 13z"/><path class="'.$class.'" clip-path="url(#b)" d="M0 11l17 13 7-6.1L48 14V0H0z"/><path class="'.$class.'-3" clip-path="url(#b)" d="M0 37l30-23 7.9 1L48 0v48H0z"/><path class="'.$class.'-4" clip-path="url(#b)" d="M48 48L17 24l-4-3 35-10z"/>'); }
    function svg_youtube        ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-youtube";         return svg($label === DOM_AUTO ? "YouTube"         : $label,   0,      0,      71,      50,      $align == DOM_AUTO ? false : !!$align, '<defs id="defs31" /><sodipodi:namedview pagecolor="#ffffff" bordercolor="#666666" borderopacity="1" objecttolerance="10" gridtolerance="10" guidetolerance="10" inkscape:pageopacity="0" inkscape:pageshadow="2" inkscape:window-width="1366" inkscape:window-height="715" id="namedview29" showgrid="false" fit-margin-top="0" fit-margin-left="0" fit-margin-right="0" fit-margin-bottom="0" inkscape:zoom="1.3588925" inkscape:cx="-71.668263" inkscape:cy="39.237696" inkscape:window-x="-8" inkscape:window-y="-8" inkscape:window-maximized="1" inkscape:current-layer="Layer_1" /><style type="text/css" id="style3">.st1{fill:#FFFFFF;} </style><g id="g5" transform="scale(0.58823529,0.58823529)"><path class="'.$class.'" d="M 118.9,13.3 C 117.5,8.1 113.4,4 108.2,2.6 98.7,0 60.7,0 60.7,0 60.7,0 22.7,0 13.2,2.5 8.1,3.9 3.9,8.1 2.5,13.3 0,22.8 0,42.5 0,42.5 0,42.5 0,62.3 2.5,71.7 3.9,76.9 8,81 13.2,82.4 22.8,85 60.7,85 60.7,85 c 0,0 38,0 47.5,-2.5 5.2,-1.4 9.3,-5.5 10.7,-10.7 2.5,-9.5 2.5,-29.2 2.5,-29.2 0,0 0.1,-19.8 -2.5,-29.3 z" id="path7" inkscape:connector-curvature="0"/><polygon class="st1" points="80.2,42.5 48.6,24.3 48.6,60.7 " id="polygon9" style="fill:#ffffff" /></g>'); }
    function svg_numerama       ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-numerama";        return svg($label === DOM_AUTO ? "Numerama"        : $label,   0,      0,      80,      80,      $align == DOM_AUTO ? false : !!$align, '<g transform="translate(0.000000,80.000000) scale(0.100000,-0.100000)">'.'<path class="'.$class.'" d="M0 505 l0 -275 75 0 75 0 0 200 0 200 140 0 140 0 0 -200 0 -200 80 0 80 0 0 275 0 275 -295 0 -295 0 0 -275z"/><path class="'.$class.'-2" d="M210 285 l0 -275 295 0 295 0 0 275 0 275 -75 0 -75 0 0 -200 0 -200 -140 0 -140 0 0 200 0 200 -80 0 -80 0 0 -275z"/></g>'); }
    function svg_soundcloud     ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-soundcloud";      return svg($label === DOM_AUTO ? "Soundcloud"      : $label,   0,      0,     291.319, 291.319,  $align == DOM_AUTO ? false : !!$align, '<g xmlns="http://www.w3.org/2000/svg"><path style="fill:#FF7700;" d="M72.83,218.485h18.207V103.832c-6.828,1.93-12.982,5.435-18.207,10.041   C72.83,113.874,72.83,218.485,72.83,218.485z M36.415,140.921v77.436l1.174,0.127h17.033v-77.682H37.589   C37.589,140.803,36.415,140.921,36.415,140.921z M0,179.63c0,14.102,7.338,26.328,18.207,33.147V146.52   C7.338,153.329,0,165.556,0,179.63z M109.245,218.485h18.207v-109.6c-5.444-3.396-11.607-5.635-18.207-6.5V218.485z    M253.73,140.803h-10.242c0.519-3.168,0.847-6.382,0.847-9.705c0-32.182-25.245-58.264-56.388-58.264   c-16.896,0-31.954,7.775-42.287,19.955v125.695h108.07c20.747,0,37.589-17.388,37.589-38.855   C291.319,158.182,274.477,140.803,253.73,140.803z"/></g>'); } 
    function svg_link           ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-link";            return svg($label === DOM_AUTO ? "Link"            : $label,   0,      0,      48,      48,      $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M36 24c-1.2 0-2 0.8-2 2v12c0 1.2-0.8 2-2 2h-22c-1.2 0-2-0.8-2-2v-22c0-1.2 0.8-2 2-2h12c1.2 0 2-0.8 2-2s-0.8-2-2-2h-12c-3.4 0-6 2.6-6 6v22c0 3.4 2.6 6 6 6h22c3.4 0 6-2.6 6-6v-12c0-1.2-0.8-2-2-2z"></path><path class="'.$class.'" d="M43.8 5.2c-0.2-0.4-0.6-0.8-1-1-0.2-0.2-0.6-0.2-0.8-0.2h-12c-1.2 0-2 0.8-2 2s0.8 2 2 2h7.2l-18.6 18.6c-0.8 0.8-0.8 2 0 2.8 0.4 0.4 0.8 0.6 1.4 0.6s1-0.2 1.4-0.6l18.6-18.6v7.2c0 1.2 0.8 2 2 2s2-0.8 2-2v-12c0-0.2 0-0.6-0.2-0.8z"></path>'); }
    function svg_leboncoin      ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-leboncoin";       return svg($label === DOM_AUTO ? "Leboncoin"       : $label,   0,      0,     151.0,    151.0,   $align == DOM_AUTO ? false : !!$align, '<g transform="translate(0.000000,151.000000) scale(0.100000,-0.100000)" class="'.$class.'" stroke="none"><path d="M174 1484 c-59 -21 -123 -80 -150 -138 l-24 -51 0 -555 c0 -516 2 -558 19 -595 25 -56 67 -102 112 -125 37 -19 62 -20 624 -20 557 0 588 1 623 19 49 25 86 66 111 121 20 44 21 63 21 600 l0 555 -24 51 c-28 60 -91 117 -154 138 -66 23 -1095 22 -1158 0z m867 -244 c145 -83 270 -158 277 -167 9 -13 12 -95 12 -329 0 -172 -3 -319 -6 -328 -8 -20 -542 -326 -569 -326 -11 0 -142 70 -291 155 -203 116 -273 161 -278 177 -10 38 -7 632 4 648 15 24 532 318 561 319 17 1 123 -54 290 -149z"/><path d="M530 1187 c-118 -67 -213 -126 -213 -132 1 -5 100 -67 220 -137 l218 -126 65 36 c36 20 139 78 228 127 89 50 161 92 162 95 0 8 -439 260 -453 260 -6 -1 -109 -56 -227 -123z"/><path d="M260 721 l0 -269 228 -131 227 -130 3 266 c1 147 -1 270 -5 274 -11 10 -441 259 -447 259 -4 0 -6 -121 -6 -269z"/><path d="M1018 859 l-228 -130 0 -270 c0 -148 3 -269 7 -269 3 0 107 57 230 126 l223 126 0 274 c0 151 -1 274 -2 273 -2 0 -105 -59 -230 -130z"/></g>'); }
    function svg_500px          ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-500px";           return svg($label === DOM_AUTO ? "500px"           : $label,   0,      0,     980,      997,     $align == DOM_AUTO ? false : !!$align, '<path class="'.$class.'" d="M415.7,462.1c-8.1-6.1-16.6-11.1-25.4-15c-8.9-4-17.7-6-26.5-6c-16.3,0-29.1,6.2-38.6,18.4c-9.6,12.4-14.3,26.2-14.3,41.4c0,16.7,4.9,30.4,14.6,41.1c9.7,10.7,23.2,16,40.4,16c8.8,0,17.6-1.8,26.5-5.3c8.8-3.5,17.2-7.9,25.1-13.2c7.9-5.3,15.4-11.3,22.3-18.1c7-6.7,13.2-13.4,18.8-19.9c-5.6-5.9-12.1-12.6-19.5-19.8S423.8,468.1,415.7,462.1L415.7,462.1z M634.1,441.1c-9.3,0-18.3,2-26.8,6c-8.6,3.9-16.7,8.9-24.4,15c-7.7,6-15,12.7-21.9,19.9s-13.3,13.8-18.8,19.9c6,7,12.5,13.9,19.5,20.5c7,6.8,14.3,12.8,22.4,18.1c7.8,5.3,16,9.6,24.7,12.9c8.6,3.3,17.8,4.9,27.5,4.9c17.2,0,30.4-5.6,39.7-16.7c9.3-11.2,13.9-24.8,13.9-41.1c0-16.2-5.1-30.2-15-41.8C664.8,447,651.2,441.1,634.1,441.1L634.1,441.1z M500,10C229.4,10,10,229.4,10,500c0,270.6,219.4,490,490,490c270.6,0,490-219.4,490-490C990,229.4,770.6,10,500,10z M746.8,549.1c-5.5,15.8-13.4,29.6-23.6,41.4c-10.2,11.9-22.9,21.1-37.9,27.9c-15.1,6.7-31.9,10.1-50.5,10.1c-14.4,0-27.9-2.2-40.4-6.6c-12.6-4.4-24.3-10.2-35.2-17.5c-10.9-7.2-21.2-15.5-31-25c-9.7-9.6-19-19.4-27.9-29.6c-9.7,10.2-19.2,20.1-28.5,29.6c-9.3,9.5-19.1,17.9-29.7,25c-10.4,7.2-21.8,13-34.1,17.5c-12.3,4.4-26.1,6.6-41.4,6.6c-19,0-35.9-3.3-50.8-10.1c-14.9-6.7-27.7-15.8-38.3-27.2c-10.7-11.4-18.8-25-24.4-40.7c-5.5-15.8-8.3-32.7-8.3-50.8c0-18.1,2.7-34.9,8-50.5c5.4-15.6,13.2-29,23.3-40.4c10.2-11.4,22.7-20.4,37.6-27.2c14.8-6.7,31.5-10.1,50.1-10.1c15.3,0,29.3,2.3,42.1,7c12.8,4.6,24.6,10.8,35.5,18.4c11,7.6,21.2,16.4,30.7,26.4s18.9,20.5,28.2,31.7c8.9-10.7,18.1-21.1,27.5-31.3c9.6-10.3,19.8-19.2,30.7-26.8c10.9-7.7,22.7-13.8,35.5-18.4c12.8-4.7,26.6-7,41.3-7c18.6,0,35.3,3.2,50.2,9.7c14.9,6.5,27.4,15.4,37.6,26.7c10.2,11.4,18.1,24.7,23.6,40c5.6,15.4,8.4,32,8.4,50.1C755.2,516.4,752.4,533.4,746.8,549.1L746.8,549.1z" />'); }
    function svg_seloger        ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-seloger";         return svg($label === DOM_AUTO ? "Seloger"         : $label,   0,      0,     152.0,    152.0,   $align == DOM_AUTO ? false : !!$align, '<g transform="translate(0.000000,152.000000) scale(0.100000,-0.100000)" class="'.$class.'" stroke="none"><path d="M0 760 l0 -760 760 0 760 0 0 760 0 760 -760 0 -760 0 0 -760z m1020 387 c0 -7 -22 -139 -50 -293 -27 -153 -50 -291 -50 -306 0 -39 25 -48 135 -48 l97 0 -7 -57 c-4 -31 -9 -62 -12 -70 -8 -21 -50 -28 -173 -28 -92 0 -122 4 -152 19 -54 26 -81 76 -81 145 1 51 98 624 109 643 3 4 45 8 95 8 66 0 89 -3 89 -13z m-364 -58 c91 -17 93 -18 81 -86 -5 -32 -12 -62 -16 -66 -4 -4 -60 -3 -125 3 -85 8 -126 8 -150 0 -33 -10 -50 -38 -40 -63 2 -7 55 -46 117 -87 131 -88 157 -120 157 -195 0 -129 -86 -217 -239 -245 -62 -11 -113 -9 -245 12 l-68 10 7 61 c3 34 9 65 11 69 3 4 69 5 148 2 97 -5 148 -3 163 4 24 13 38 56 25 78 -5 9 -57 48 -117 87 -60 40 -117 84 -128 99 -33 44 -34 125 -4 191 31 69 88 112 172 130 41 9 193 7 251 -4z m664 -28 c44 -23 80 -84 80 -135 0 -52 -40 -119 -84 -140 -26 -12 -64 -16 -157 -16 l-123 0 36 38 c31 32 35 40 26 62 -14 37 -4 113 20 147 43 61 134 81 202 44z"/></g>'); }
    function svg_deezer         ($label = DOM_AUTO, $align = DOM_AUTO) { $class = "palette-deezer";          return svg($label === DOM_AUTO ? "Deezer"          : $label,   0,      0,     192.1,    192.1,   $align == DOM_AUTO ? false : !!$align, '<style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;fill:#40AB5D;}.st1{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8192_1_);}.st2{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8199_1_);}.st3{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8206_1_);}.st4{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8213_1_);}.st5{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8220_1_);}.st6{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8227_1_);}.st7{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8234_1_);}.st8{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8241_1_);}.st9{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8248_1_);}</style><g id="g8252" transform="translate(0,86.843818)"><rect id="rect8185" x="155.5" y="-25.1" class="st0" width="42.9" height="25.1"/><linearGradient id="rect8192_1_" gradientUnits="userSpaceOnUse" x1="-111.7225" y1="241.8037" x2="-111.9427" y2="255.8256" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#358C7B"/><stop  offset="0.5256" style="stop-color:#33A65E"/></linearGradient><rect id="rect8192" x="155.5" y="9.7" class="st1" width="42.9" height="25.1"/><linearGradient id="rect8199_1_" gradientUnits="userSpaceOnUse" x1="-123.8913" y1="223.6279" x2="-99.7725" y2="235.9171" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#222B90"/><stop  offset="1" style="stop-color:#367B99"/></linearGradient><rect id="rect8199" x="155.5" y="44.5" class="st2" width="42.9" height="25.1"/><linearGradient id="rect8206_1_" gradientUnits="userSpaceOnUse" x1="-208.4319" y1="210.7725" x2="-185.0319" y2="210.7725" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#FF9900"/><stop  offset="1" style="stop-color:#FF8000"/></linearGradient><rect id="rect8206" x="0" y="79.3" class="st3" width="42.9" height="25.1"/><linearGradient id="rect8213_1_" gradientUnits="userSpaceOnUse" x1="-180.1319" y1="210.7725" x2="-156.7319" y2="210.7725" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#FF8000"/><stop  offset="1" style="stop-color:#CC1953"/></linearGradient><rect id="rect8213" x="51.8" y="79.3" class="st4" width="42.9" height="25.1"/><linearGradient id="rect8220_1_" gradientUnits="userSpaceOnUse" x1="-151.8319" y1="210.7725" x2="-128.4319" y2="210.7725" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#CC1953"/><stop  offset="1" style="stop-color:#241284"/></linearGradient><rect id="rect8220" x="103.7" y="79.3" class="st5" width="42.9" height="25.1"/><linearGradient id="rect8227_1_" gradientUnits="userSpaceOnUse" x1="-123.5596" y1="210.7725" x2="-100.1596" y2="210.7725" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#222B90"/><stop  offset="1" style="stop-color:#3559A6"/></linearGradient><rect id="rect8227" x="155.5" y="79.3" class="st6" width="42.9" height="25.1"/><linearGradient id="rect8234_1_" gradientUnits="userSpaceOnUse" x1="-152.7555" y1="226.0811" x2="-127.5083" y2="233.4639" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#CC1953"/><stop  offset="1" style="stop-color:#241284"/></linearGradient><rect id="rect8234" x="103.7" y="44.5" class="st7" width="42.9" height="25.1"/><linearGradient id="rect8241_1_" gradientUnits="userSpaceOnUse" x1="-180.9648" y1="234.3341" x2="-155.899" y2="225.2108" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="2.669841e-03" style="stop-color:#FFCC00"/><stop  offset="0.9999" style="stop-color:#CE1938"/></linearGradient><rect id="rect8241" x="51.8" y="44.5" class="st8" width="42.9" height="25.1"/><linearGradient id="rect8248_1_" gradientUnits="userSpaceOnUse" x1="-178.1651" y1="257.7539" x2="-158.6987" y2="239.791" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="2.669841e-03" style="stop-color:#FFD100"/><stop  offset="1" style="stop-color:#FD5A22"/></linearGradient><rect id="rect8248" x="51.8" y="9.7" class="st9" width="42.9" height="25.1"/></g>'); }


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

    function url_unsplash()                         { return "https://unsplash.com";                            }
    function url_unsplash_author($id)               { return "https://unsplash.com/@".$id;                      }
    function url_unsplash_img($id,$w,$h)            { return "https://source.unsplash.com/".$id."/".$w."x".$h;  }
    function url_unsplash_img_random($search,$w,$h) { return "https://source.unsplash.com/".$w."x".$h."/?$search";  }

    function url_img_unsplash($id, $w = false, $h = false, $author = false)
    {
        if ($w === false) $w = get("default_image_ratio_w");
        if ($h === false) $h = get("default_image_ratio_h");

        if ($w < 100) { $w *= 100; $h *= 100; } // pure ratio to dimensions

                        $id     = trim($id);
        if (!!$author)  $author = trim($author);

        $copyright  = array($id, $author);
        $copyrights = get("unsplash_copyrights", array());

        if (!in_array($copyright, $copyrights)) set("unsplash_copyrights", array_merge($copyrights, array($copyright)));

        return url_unsplash_img($id,$w,$h);
    }

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

    function url_img_pinterest ($pin      = false) { return dom_at(dom_at(dom_at(dom_at(                            json_pinterest_pin ( $pin),                                                                        "data"),"image"),"original"),"url",                                  url_img_blank()); }
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
        $text = str_replace("<br>.",        "<br>",     $text);

        $n = 1; while ($n > 0) { $text = str_replace("<br><br><br>", "<br><br>", $text, $n); }
        
        $text = transform_lines($text, "---");
        $text = transform_lines($text, "___");
        
        $text = str_replace("<hr><br>",     "<hr>",     $text);
        $text = str_replace("<hr>>",        "<hr> >",   $text);
        $text = str_replace("<br>>",        "<br> >",   $text);
        $text = str_replace("=>",           "→",        $text);
        $text = str_replace(">>",           "→",        $text);

        return $text;
    }

    #region Hook - feed context recording

    $__dom_hook_card_context = array();

    function dom_hook_card_set_context($key, $val)
    {
        global $__dom_hook_card_context;
        if (!array_key_exists($key, $__dom_hook_card_context)) $__dom_hook_card_context[$key] = ""; else $__dom_hook_card_context[$key] .= " ";
        $__dom_hook_card_context[$key] .= $val;
        return $val;
    }

    function dom_hook_card_flush_context($html = "")
    {
        global $__dom_hook_card_context;

        if (count($__dom_hook_card_context) > 0)
        {
            dom_rss_record_item(dom_at($__dom_hook_card_context, "title"), dom_at($__dom_hook_card_context, "text"));
            $__dom_hook_card_context = array();
        }

        return $html;
    }

    #endregion

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
        
        if ($title_icon !== false && false === stripos($title_icon, "<img")
                                  && false === stripos($title_icon, "<amp-img")) $title  = img(            $title_icon, array("class" => dom_component_class('card-title-icon'), "style" => "border-radius: 50%; max-width: 2.5rem; position: absolute;"), $title_main);
        if ($title_link !== false && false === stripos($title_link, "<a"))       $title  = a($title,       $title_link,                  dom_component_class('card-title-link'), DOM_EXTERNAL_LINK);
        if ($title_main !== false && false === stripos($title_main, "<h"))       $title .= h($title_level, $title_main, array("class" => dom_component_class('card-title-main'), "style" => "margin-left: ".(($title_icon !== false) ? 56 : 0)."px"/*,  "itemprop" => "headline name"*/));
        if ($title_main !== false && false !== stripos($title_main, "<h"))       $title .=                 $title_main;
        if ($title_sub  !== false && false === stripos($title_sub,  "<p"))       $title .= p(              $title_sub,  array("class" => dom_component_class('card-title-sub'),  "style" => "margin-left: ".(($title_icon !== false) ? 56 : 0)."px"));

        dom_hook_card_set_context("title", $title_main);

        return (($title !== "") ? dom_header($title, dom_component_class("card-title")) : "");
    }

    function card_media($media = false, $attributes = false)
    {
        return (($media !== false) ? section($media, dom_attributes_add_class($attributes, dom_component_class("card-media"))) : "");
    }

    function card_text($text = false, $cleanup = false)
    {
        if ($text !== false && !!$cleanup)
        {
            $text = dom_clean_social_media_text($text);
        }
        
        dom_hook_card_set_context("text", $text);
        
        return (($text !== false) ? section($text, dom_component_class("card-text")) : "");
    }

    function card_actions($button = false)
    {
             if ($button !== false) $button = button($button, dom_component_class('card-action-button'));
        return (($button !== false)        ? section($button, dom_component_class("card-actions")) : "");
    }
  
    function card($html, $attributes = false, $horizontal = false)
    {
        if (!!get("random_cards_rotate"))
        {
            $attributes = is_array($attributes) ? array_merge($attributes, array("class" => "card",      "style" => "transform: scale3d(1,1,1) rotate(".rand(-get("random_cards_rotate"),get("random_cards_rotate"))."deg);")) 
                                                :                          array("class" => $attributes, "style" => "transform: scale3d(1,1,1) rotate(".rand(-get("random_cards_rotate"),get("random_cards_rotate"))."deg);");
        }
    
        dom_hook_card_flush_context();
        
        return article($html, dom_attributes_add_class($attributes, dom_component_class("card").($horizontal ? dom_component_class("card-horizontal") : ''))).cosmetic(dom_eol());
    }

    function card_from_metadata($metadata, $attributes = false)
    {
        // CARD INFO FROM METADATA

        $source   = dom_at($metadata, "TYPE",     "instagram");
        $lazy     = dom_at($metadata, "LAZY",     DOM_AUTO);
        $userdata = dom_at($metadata, "userdata", false);

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

        $data["content"]        = (dom_has($metadata, "post_url") && $data["content"] != "")    ?   a($data["content"], $metadata["post_url"], false, DOM_EXTERNAL_LINK)                                  : $data["content"];
        $data["content"]        =  dom_has($metadata, "post_figcaption")                        ? cat($data["content"], wrap_each($metadata["post_figcaption"], dom_eol(), "div")) : $data["content"];

        $data["title_main"]     = dom_at($metadata, "post_title");
        $data["title_img_src"]  = dom_at($metadata, "user_img_url");
        $data["title_link"]     = dom_at($metadata, "user_url");  

        if ("" === $data["title_main"]) $data["title_main"] = dom_get("title");

        $data["title_sub"]      =  dom_has($metadata, "post_timestamp") ? span_datepublished(date("d/m/y", dom_at($metadata, "post_timestamp")),           dom_at($metadata, "post_timestamp")  ) 
                                : (dom_has($metadata, "post_date")      ? span_datepublished(              dom_at($metadata, "post_date", ''  ), strtotime(dom_at($metadata, "post_date"))      ) : '');

        $data["title_sub"]      = dom_has($metadata, "user_name")       ? cat($data["title_sub"],' ',span_author(span_name($metadata["user_name"]))) : $data["title_sub"];
        $data["title_sub"]      = dom_has($metadata, "user_url")        ?   a($data["title_sub"], $metadata["user_url"], false, DOM_EXTERNAL_LINK)                              : $data["title_sub"];

        $data["title_sub"]      = ($data["title_sub"] != "") ? cat((is_callable("svg_$source") ? call_user_func("svg_$source") : ''), $data["title_sub"]) : false;

        $data["desc"]           = dom_has($metadata, "post_text") ? div_articlebody((is_callable("add_hastag_links_$source") ? call_user_func("add_hastag_links_$source", dom_at($metadata, "post_text"), $userdata) : '')) : false;

        if (false !==                         dom_at($metadata,"post_url",false)
        &&  ""    !=                                 $metadata["post_url"]
        &&  false !== strpos($data["desc"],          $metadata["post_url"])
        &&  false === strpos($data["desc"], 'href="'.$metadata["post_url"])
        &&  false === strpos($data["desc"], "href='".$metadata["post_url"])
        &&  false === strpos($data["desc"],          $metadata["post_url"]."</a>"))
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
            
            if (dom_at($metadata, "post_text")      !== false) $properties["keywords"]      = implode(' ', dom_array_hashtags(          dom_at($metadata, "post_text")));
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
            card_text   (dom_at($data, "desc", true)).

            card_actions(false),
            
            $attributes
            ).

            dom_if($properties !== false && dom_get("jsonld",true), script_json_ld($properties));
    }

    function img_from_metadata($metadata, $attributes = false)
    {
    //  IMG INFO FROM METADATA
    
        $lazy        = dom_at($metadata, "LAZY", DOM_AUTO);        
        $short_label = extract_start(dom_at($metadata, "post_title"), 8, array("\n","!","?",".",array("#",1),","," "));
        
        return img($metadata["post_img_url"], $attributes, $short_label, $lazy);
    }

    function card_      ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self")                          { return wrap_each(array_card   ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), dom_eol(2), $container);                 }        
    function imgs       ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self")                          { return wrap_each(array_imgs   ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), dom_eol(2), $container, true);           }    
    function cards      ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self", $s = 4, $m = 4, $l = 4)  { return wrap_each(array_cards  ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), dom_eol(2), $container, true, $s,$m,$l); }    
    function cells_card ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false,                      $s = 4, $m = 4, $l = 4)  { return wrap_each(array_cards  ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), dom_eol(2), "cell",     true, $s,$m,$l); }    

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
                    $menu_lis .= cosmetic(dom_eol()) . li("", array("class" => dom_component_class("list-item-separator"), "role" => "separator"));
                }
                else
                {    
                //  if (!is_array($menu_entry)) $menu_entry = array($menu_entry, url_void());
                    if (!is_array($menu_entry)) $menu_entry = array($menu_entry, "#".anchor_name($menu_entry));
                            
                    $item       = dom_get($menu_entry, "item",   dom_get($menu_entry, 0, ""));
                    $link       = dom_get($menu_entry, "link",   dom_get($menu_entry, 1, false));
                    $target     = dom_get($menu_entry, "target", dom_get($menu_entry, 2, $default_target));
                    $attributes = false;
                    
                    $menu_lis .= dom_eol().li(a(span($item), $link, $attributes, $target), array("class" => dom_component_class("list-item"), "role" => "menuitem", "tabindex" => "0"));
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
                cosmetic(dom_eol(1)).
                dom_tag('amp-sidebar class="menu" id="'.DOM_MENU_ID.'" layout="nodisplay"', $html)
                );

            //$html = span("","placeholder-amp-sidebar");
        }

        return $html;
    }

    function menu_switch() { return dom_if(dom_get("framework") == "material",  a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons mdc-top-app-bar__icon--menu", "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle")                                                                )))
                                .   dom_if(dom_get("framework") == "bootstrap", a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons",                             "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  ))) 
                                .   dom_if(dom_get("framework") == "spectre",   a(span("☰", "menu-switch-symbol menu-toggle-content"), url_void(),     array("class" => "menu-switch-link nav-link material-icons",                             "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  ))) 
                                .   dom_if(dom_get("framework") == "NONE",      a(span("☰", "menu-switch-symbol menu-toggle-content")   
                                                                               . a(span("✕", "menu-close-symbol  menu-close-content"), "#".DOM_MENU_ID."-close",  array("class" => "menu-switch-link close nav-link material-icons", "aria-label" => "Menu Toggle"))
                                                                                                                                     , "#".DOM_MENU_ID."-open",   array("class" => "menu-switch-link open nav-link material-icons", "name" => "menu-close",                            "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle")                                                                )))
                            //  .   dom_if(dom_get("framework") == "NONE",   checkbox("menu-button", "", "menu-switch-symbol menu-toggle-content" ,    array("class" => "menu-switch-link nav-link material-icons",                             "role" => "button", "aria-haspopup" => "true", "aria-expanded" => "false", "on" => ("tap:".DOM_MENU_ID.".toggle"), "data-toggle" =>"dropdown", "id" => "navbarDropdownMenuLink"  )).checkbox_label("menu-button", "☰"))
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

        if (false === stripos($html,"<h1"))  $html =  h1($html);
        if (false === stripos($html,"<a"))   $html =   a($html,'.');
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

            $amp_anim     = '<amp-animation id="toolbarStaticShow" layout="nodisplay"><script type="application/json">{ "duration": "0", "fill": "forwards", "animations": [ { "selector": "#toolbar-row-nav-static", "keyframes": { "visibility": "visible" } } ] }</script></amp-animation>'
                          . '<amp-animation id="toolbarStaticHide" layout="nodisplay"><script type="application/json">{ "duration": "0", "fill": "forwards", "animations": [ { "selector": "#toolbar-row-nav-static", "keyframes": { "visibility": "hidden"  } } ] }</script></amp-animation>';

            $amp_observer = '<amp-position-observer target="toolbar-row-nav" intersection-ratios="1" on="enter:toolbarStaticHide.start;exit:toolbarStaticShow.start" layout="nodisplay"></amp-position-observer>';
        }

        return $amp_anim . comment("PRE TOOLBAR").dom_header($html . $amp_observer, dom_attributes_add_class($attributes, dom_component_class("toolbar toolbar-container")));
    }
    
    #endregion
    #region WIP API : DOM : HTML COMPONENTS : ASYNC
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
            if (is_string(dom_get("support_header_backgrounds")))
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
    function google_photo_album_async                         ($ids = false, $wrapper = "div", $img_wrapper = "self")                                { return dom_ajax_call("google_photo_album", $ids, $wrapper, $img_wrapper); }
       

    #endregion
    #region WIP API : DOM : RSS
    ######################################################################################################################################

    function dom_cdata($html) { return "<![CDATA[$html]]>"; }

    function dom_rss_sanitize($html) { return trim(htmlspecialchars(strip_tags($html), ENT_QUOTES, 'utf-8')); }
    
    function dom_rss_item_from_item_info($item_info)
    {
        if (!is_array($item_info)) $item_info = explode(',', $item_info);
        
        if (!is_array(dom_at($item_info,"img_url",false))) $item_info["img_url"] = array(dom_at($item_info,"img_url"));
        
        $rss =  
                        dom_rss_title       (dom_at($item_info,"title",dom_get("title")))
        . dom_eol() .   dom_rss_link        (dom_get("canonical"))
        . dom_eol() .   dom_rss_description (dom_at($item_info,"description",""))
        . dom_eol() .   dom_rss_pubDate     (dom_at($item_info,"timestamp", 0));
        
        foreach ($item_info["img_url"] as $img_url)
        {       
            if (!!$img_url)
            {
                $rss .= dom_eol() . raw('<enclosure url="'     .rawurlencode($img_url)  .'" type="image/'.((false !== stripos($img_url, '.jpg'))?'jpg':'png').'" length="262144" />')
                     .  dom_eol() . raw('<media:content url="' .rawurlencode($img_url)  .'" medium="image" />');
            }
        }
        
        $rss .= dom_eol() . raw('<source url="'.dom_get("canonical")."/?rss".'">RSS</source>')
        //   .  dom_eol() . raw('<guid isPermaLink="true">https://web.cyanide-studio.com/rss/bb2/xml/?&amp;limit_matches=50&amp;limit_leagues=50&amp;days_leagues=7&amp;days_matches=1&amp;id=3518</guid>')
        ;

        return dom_rss_item($rss);
    }
 
    function dom_rss_channel        ($html = "")                        { return cosmetic(dom_eol()).   dom_tag('channel',                      $html,  false,         true); }
    function dom_rss_image          ($html = "")                        { return                        dom_tag('image',                        $html,  false,         true); }
    function dom_rss_url            ($html = "")                        { return                        dom_tag('url',                          $html,  false,         true); }
    function dom_rss_item           ($html = "")                        { return                        dom_tag('item',                         $html,  false,         true); }
    function dom_rss_link           ($html = "")                        { return                        dom_tag('link',                         $html,  false,         true); }
    function dom_rss_title          ($html = "")                        { return                        dom_tag('title',       dom_rss_sanitize($html), false,         true); }
    function dom_rss_description    ($html = "", $attributes = false)   { return                        dom_tag('description', dom_rss_sanitize($html), $attributes,   true); }

    function dom_rss_lastbuilddate  ($date = false)                     { return                        dom_tag('lastBuildDate', (false === $date) ? (!!dom_get("dom_rss_date_granularity_daily") ? date("D, d M Y 00:00:00") : date(DATE_RSS)) : date(DATE_RSS, $date), false, true); }
    function dom_rss_pubDate        ($date = false)                     { return                        dom_tag('pubDate',       (false === $date) ? (!!dom_get("dom_rss_date_granularity_daily") ? date("D, d M Y 00:00:00") : date(DATE_RSS)) : date(DATE_RSS, $date), false, true); }

    function dom_rss_copyright      ($author = false)                   { return                        dom_tag('copyright', "Copyright " . ((false === $author) ? dom_get("author", DOM_AUTHOR) : $author), false, true); }
    
    #endregion
    #region WIP API : DOM : TILE
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
        $tile .= tile_binding($images. dom_eol() . tile_text($item_info["description"]), 'Tile'.'Square'.'150x150'.'PeekImageAndText'.'02');
        $tile .= tile_binding($images. dom_eol() . tile_text($item_info["description"]), 'Tile'.'Wide'.  '310x150'.'PeekImageAndText'.'01');                      
        $tile .= '</visual></tile>';
        
        return $tile;
    }
    
    function tile_binding   ($html, $template)      { return dom_tag('binding', dom_eol().$html.dom_eol(), array("template" => $template), true); }
    function tile_image     ($src,      $id = 1)    { return raw('<image id="'.$id.'" src="'.tile_sanitize($src).'"/>'); }
    function tile_text      ($txt = "", $id = 1)    { return raw('<text id="'.$id.'">'.tile_sanitize($txt).'</text>'); }
    
    #endregion
    #region WIP HELPERS - COLOR
    ######################################################################################################################################
    
    function dom_valid_hex($hex)
    {
        $rrggbb = str_replace("#", "", $hex);

             if (strlen($rrggbb) == 0) $rrggbb = "000000";
        else if (strlen($rrggbb) == 1) $rrggbb = $rrggbb[0].$rrggbb[0].$rrggbb[0].$rrggbb[0].$rrggbb[0].$rrggbb[0];
        else if (strlen($rrggbb) == 2) $rrggbb = $rrggbb[0].$rrggbb[1].$rrggbb[0].$rrggbb[1].$rrggbb[0].$rrggbb[1];
        else if (strlen($rrggbb) == 3) $rrggbb = $rrggbb[0].$rrggbb[0].$rrggbb[1].$rrggbb[1].$rrggbb[2].$rrggbb[2];
        else if (strlen($rrggbb) == 4) $rrggbb = $rrggbb."00";
        else if (strlen($rrggbb) == 5) $rrggbb = $rrggbb."0";
        else if (strlen($rrggbb) >  6) $rrggbb = $rrggbb[0].$rrggbb[1].$rrggbb[2].$rrggbb[3].$rrggbb[4].$rrggbb[5];
        
        return $rrggbb;
    }

    function dom_hex_to_hsl($color)
    {
        $color = dom_valid_hex($color);

        $R = hexdec($color[0].$color[1]);
        $G = hexdec($color[2].$color[3]);
        $B = hexdec($color[4].$color[5]);

        $var_R = ($R / 255);
        $var_G = ($G / 255);
        $var_B = ($B / 255);

        $var_Min = min($var_R, $var_G, $var_B);
        $var_Max = max($var_R, $var_G, $var_B);
        $del_Max = $var_Max - $var_Min;

        $L = ($var_Max + $var_Min)/2;

        if ($del_Max == 0)
        {
            $H = 0;
            $S = 0;
        }
        else
        {
            if ( $L < 0.5 ) $S = $del_Max / ( $var_Max + $var_Min );
            else            $S = $del_Max / ( 2 - $var_Max - $var_Min );

            $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
            $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
            $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

            if      ($var_R == $var_Max) $H = $del_B - $del_G;
            else if ($var_G == $var_Max) $H = ( 1 / 3 ) + $del_R - $del_B;
            else if ($var_B == $var_Max) $H = ( 2 / 3 ) + $del_G - $del_R;

            if ($H<0) $H++;
            if ($H>1) $H--;
        }

        return array('H' => ($H*360), 'S' => $S, 'L' => $L);
    }
    
    function dom_hue_to_rgb($v1, $v2, $vH)
    {
        if( $vH < 0 ) $vH += 1;
        if( $vH > 1 ) $vH -= 1;

        if ((6*$vH) < 1) return ($v1 + ($v2 - $v1) * 6 * $vH);
        if ((2*$vH) < 1) return $v2;
        if ((3*$vH) < 2) return ($v1 + ($v2-$v1) * ( (2/3)-$vH ) * 6);
        
        return $v1;
    }
    
    function dom_hsl_to_hex($hsl = array())
    {
        if (empty($hsl)
        || !isset($hsl["H"])
        || !isset($hsl["S"])
        || !isset($hsl["L"]) )
        {
            return false;
        }

        $H = $hsl['H']/360;
        $S = $hsl['S'];
        $L = $hsl['L'];

        if ($S == 0 )
        {
            $r = $L * 255;
            $g = $L * 255;
            $b = $L * 255;
        }
        else
        {
            if ($L < 0.5)
            {
               $var_2 = $L*(1+$S);
            }
            else
            {
                $var_2 = ($L+$S) - ($S*$L);
            }

            $var_1 = 2 * $L - $var_2;

            $r = round(255 * dom_hue_to_rgb($var_1, $var_2, $H + (1/3) ));
            $g = round(255 * dom_hue_to_rgb($var_1, $var_2, $H         ));
            $b = round(255 * dom_hue_to_rgb($var_1, $var_2, $H - (1/3) ));
        }

        $r = dechex($r);
        $g = dechex($g);
        $b = dechex($b);

        $rr = (strlen("".$r)===1) ? "0".$r:$r;
        $gg = (strlen("".$g)===1) ? "0".$g:$g;
        $bb = (strlen("".$b)===1) ? "0".$b:$b;

        return "#".$rr.$gg.$bb;
   }

    function dom_rotate($color, $rotate = 180)
    {
        $hsl = dom_hex_to_hsl($color);
        
        $hsl['H'] += $rotate;

        while ($hsl['H'] > 360) $hsl['H'] -= 360;
        while ($hsl['H'] < 0)   $hsl['H'] += 360;

        return dom_hsl_to_hex($hsl);
    }

    function dom_complementary($color)
    {
        return dom_rotate($color, 180);
    }

    function dom_int_rgb_to_hash_rrggbb($r, $g, $b)
    {
        return "#". str_pad(dechex($r),2,"0",STR_PAD_LEFT).
                    str_pad(dechex($g),2,"0",STR_PAD_LEFT).
                    str_pad(dechex($b),2,"0",STR_PAD_LEFT);
    }

    function dom_dec_rgb_to_hash_rrggbb($r, $g, $b)
    {
        return dom_int_rgb_to_hash_rrggbb(255*$r, 255*$g, 255*$b);
    }

    function dom_hash_rrggbb_to_int_rgb($rrggbb)
    {        
        $rrggbb = ltrim($rrggbb, "#");

        return "rgb(".hexdec(substr($rrggbb, 0, 2)).",".
                      hexdec(substr($rrggbb, 2, 2)).",".
                      hexdec(substr($rrggbb, 4, 2)).")";
    }

    // (c) https://github.com/gdkraus/wcag2-color-contrast

    // calculates the luminosity of an given RGB color
    // the color code must be in the format of RRGGBB
    // the luminosity equations are from the WCAG 2 requirements
    // http://www.w3.org/TR/WCAG20/#relativeluminancedef

    function dom_calculate_luminosity_dec_rgb($r,$g,$b) {

        if ($r <= 0.03928) { $r = $r / 12.92; } else { $r = pow((($r + 0.055) / 1.055), 2.4); }
        if ($g <= 0.03928) { $g = $g / 12.92; } else { $g = pow((($g + 0.055) / 1.055), 2.4); }
        if ($b <= 0.03928) { $b = $b / 12.92; } else { $b = pow((($b + 0.055) / 1.055), 2.4); }

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    function dom_calculate_luminosity_hex_rgb($r,$g,$b) {

        return dom_calculate_luminosity_dec_rgb(
            hexdec($r) / 255,
            hexdec($g) / 255,
            hexdec($b) / 255
            );
    }

    function dom_calculate_luminosity($color, $fallback = 1.0) {

        $color = ltrim($color,"#");

        if (!ctype_xdigit($color)) return $fallback;

        return dom_calculate_luminosity_hex_rgb(

            substr($color, 0, 2),
            substr($color, 2, 2),
            substr($color, 4, 2)
            );
    }

    // calculates the luminosity ratio of two colors
    // the luminosity ratio equations are from the WCAG 2 requirements
    // http://www.w3.org/TR/WCAG20/#contrast-ratiodef

    function dom_calculate_luminosity_ratio($color1, $color2, $fallback1 = 1.0, $fallback2 = 0.0) {

        $profiler = dom_debug_track_timing();
        
        $l1 = dom_calculate_luminosity($color1, $fallback1);
        $l2 = dom_calculate_luminosity($color2, $fallback2);

        return ($l1 > $l2) ? (($l1 + 0.05) / ($l2 + 0.05)) : (($l2 + 0.05) / ($l1 + 0.05));
    }

    function dom_calculate_luminosity_ratio_dec_rgb($color1_r, $color1_g, $color1_b, $color2_r, $color2_g, $color2_b) {

        $profiler = dom_debug_track_timing();
        
        $l1 = dom_calculate_luminosity_dec_rgb($color1_r, $color1_g, $color1_b);
        $l2 = dom_calculate_luminosity_dec_rgb($color2_r, $color2_g, $color2_b);

        return ($l1 > $l2) ? (($l1 + 0.05) / ($l2 + 0.05)) : (($l2 + 0.05) / ($l1 + 0.05));
    }

    function dom_color_modify_lightness($color, $factor, $debug = false)
    {
        $profiler = dom_debug_track_timing();

        $rrggbb = ltrim($color, "#");        
        if (!ctype_xdigit($rrggbb)) return "#".$rrggbb;

        $r = $r0 = hexdec(substr($rrggbb, 0, 2)) / 255;
        $g = $g0 = hexdec(substr($rrggbb, 2, 2)) / 255;
        $b = $b0 = hexdec(substr($rrggbb, 4, 2)) / 255;

        $l0 = dom_calculate_luminosity_dec_rgb($r,$g,$b);
        
        $percent_min = 0;
        $percent_max = 1;
        $percent     = 0;
        $depth       = 8;

        while ($depth-- > 0)
        {
            $r = max(0, min(1, $r0 + (($factor > 1 ? 1 : 0) - $r0) * $percent));
            $g = max(0, min(1, $g0 + (($factor > 1 ? 1 : 0) - $g0) * $percent));
            $b = max(0, min(1, $b0 + (($factor > 1 ? 1 : 0) - $b0) * $percent));
            
            $l1 = dom_calculate_luminosity_dec_rgb($r,$g,$b);    

            $not_enough = false;
            if ($factor >= 1.0 && $l1 < $l0 * $factor) $not_enough = true;
            if ($factor <  1.0 && $l1 > $l0 * $factor) $not_enough = true;
            
            if ($not_enough) $percent_min = $percent; else $percent_max = $percent;
            $percent = 0.5 * ($percent_min + $percent_max);
        } 

        $rrggbb = str_pad(dechex(255*$r),2,"0",STR_PAD_LEFT).
                  str_pad(dechex(255*$g),2,"0",STR_PAD_LEFT).
                  str_pad(dechex(255*$b),2,"0",STR_PAD_LEFT);
                  
        return "#".$rrggbb;
    }

    function dom_color_lerp($a, $b, $x)
    {
        $rrggbb0 = ltrim($a, "#");
        $rrggbb1 = ltrim($b, "#");

        $r0 = hexdec(substr($rrggbb0, 0, 2)) / 255;
        $g0 = hexdec(substr($rrggbb0, 2, 2)) / 255;
        $b0 = hexdec(substr($rrggbb0, 4, 2)) / 255;

        $r1 = hexdec(substr($rrggbb1, 0, 2)) / 255;
        $g1 = hexdec(substr($rrggbb1, 2, 2)) / 255;
        $b1 = hexdec(substr($rrggbb1, 4, 2)) / 255;

        $r = $r0 + $x * ($r1 - $r0);
        $g = $g0 + $x * ($g1 - $g0);
        $b = $b0 + $x * ($b1 - $b0);

        $rrggbb =   str_pad(dechex(255*$r),2,"0",STR_PAD_LEFT).
                    str_pad(dechex(255*$g),2,"0",STR_PAD_LEFT).
                    str_pad(dechex(255*$b),2,"0",STR_PAD_LEFT);
                    
        return "#".$rrggbb;
    }

    // Try a color correction function

    define("DOM_COLOR_CONTRAST_LINk_FROM_TEXT", 3.0);
    define("DOM_COLOR_CONTRAST_AA_MEDIUMBOLD",  3.0);
    define("DOM_COLOR_CONTRAST_AA_LARGE",       3.0);
    define("DOM_COLOR_CONTRAST_AA_NORMAL",      4.5);
    define("DOM_COLOR_CONTRAST_AAA_MEDIUMBOLD", 4.5);
    define("DOM_COLOR_CONTRAST_AAA_LARGE",      4.5);
    define("DOM_COLOR_CONTRAST_AAA_NORMAL",     7.0);
    define("DOM_COLOR_CONTRAST_DEFAULT",        DOM_COLOR_CONTRAST_AA_NORMAL);

    function dom_correct_color(

        $color,
        $background,
        $contrast_ratio_target,
        $delta,
       &$ratio,
        $debug

        )
    {
        $profiler = dom_debug_track_timing();

        if ($delta == 0) $delta = 1;

        $rrggbb      = ltrim($color,      "#"); if (!ctype_xdigit($rrggbb))      return "#".$rrggbb;
        $back_rrggbb = ltrim($background, "#"); if (!ctype_xdigit($back_rrggbb)) return "#".$rrggbb;

        $r = $r0 = hexdec(substr($rrggbb,      0, 2)) / 255;
        $g = $g0 = hexdec(substr($rrggbb,      2, 2)) / 255;
        $b = $b0 = hexdec(substr($rrggbb,      4, 2)) / 255;

        $back_r  = hexdec(substr($back_rrggbb, 0, 2)) / 255;
        $back_g  = hexdec(substr($back_rrggbb, 2, 2)) / 255;
        $back_b  = hexdec(substr($back_rrggbb, 4, 2)) / 255;

        $ratio       = 0;
        $percent_min = 0;
        $percent_max = 1;
        $percent     = 0;
        $depth       = 8;

        while ($depth-- > 0)
        {
            $r = max(0, min(1, $r0 + (($delta > 0 ? 1 : 0) - $r0) * $percent));
            $g = max(0, min(1, $g0 + (($delta > 0 ? 1 : 0) - $g0) * $percent));
            $b = max(0, min(1, $b0 + (($delta > 0 ? 1 : 0) - $b0) * $percent));
            
            $ratio = dom_calculate_luminosity_ratio_dec_rgb($back_r, $back_g, $back_b, $r, $g, $b);
            if ($ratio < $contrast_ratio_target) $percent_min = $percent; else $percent_max = $percent;
            $percent = 0.5 * ($percent_min + $percent_max);
        } 

        $rrggbb = str_pad(dechex(255*$r),2,"0",STR_PAD_LEFT).
                  str_pad(dechex(255*$g),2,"0",STR_PAD_LEFT).
                  str_pad(dechex(255*$b),2,"0",STR_PAD_LEFT);

        return "#".$rrggbb;
    }

    function dom_correct_lighter(

        $color,
        $background             = "#FFFFFF",
        $contrast_ratio_target  = DOM_COLOR_CONTRAST_DEFAULT,
        $debug                  = false)
    {
        $ratio = false;
        return dom_correct_color($color, $background, $contrast_ratio_target, 1, $ratio, $debug);
    }

    function dom_correct_darker(

        $color,
        $background             = "#FFFFFF",
        $contrast_ratio_target  = DOM_COLOR_CONTRAST_DEFAULT,
        $debug                  = false)
    {
        $ratio = false;
        return dom_correct_color($color, $background, $contrast_ratio_target, -1, $ratio, $debug);
    }

    function dom_correct_auto(

        $color,
        $background             = "#FFFFFF",
        $contrast_ratio_target  = DOM_COLOR_CONTRAST_DEFAULT,
        $debug                  = false)
    {
        $profiler = dom_debug_track_timing();
        
        if (is_array($color))
        {
            $corrected = array();
        
            foreach ($color as $c)
            {
                $corrected[] = dom_correct_auto($c, $background, $contrast_ratio_target, $debug);
            }

            return $corrected;
        }

        $lc = dom_calculate_luminosity($color,      1.0);
        $lb = dom_calculate_luminosity($background, 0.0);

        $delta = ($lc > $lb) ? 1 : -1;
        
        $ratioA = 0;
        $corrected_colorA = dom_correct_color($color, $background, $contrast_ratio_target, $delta, $ratioA, $debug);
        if ($ratioA >= $contrast_ratio_target) return $corrected_colorA;
        
        $ratioB = 0;
        $corrected_colorB = dom_correct_color($color, $background, $contrast_ratio_target, -$delta, $ratioB, $debug);
        if ($ratioB >= $contrast_ratio_target) return $corrected_colorB;

        return $ratioA >= $ratioB ? $corrected_colorA : $corrected_colorB;
    }

    #endregion
    #region BONUS SNIPPETS

    // HEREDOC SNIPPET HELPER

    function HSTART($offset = 0) { return dom_heredoc_start($offset); }
    function HSTOP($out = null)  { return dom_heredoc_stop($out);     }
    function HERE($out  = null)  { return dom_heredoc_flush($out);    }

    #endregion

    ######################################################################################################################################
    #endregion

?>