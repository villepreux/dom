<?php 

    namespace dom;

    #region CONSTANTS
    ######################################################################################################################################

    const auto          = "__DOM_AUTO__";
    const internal_link = "_self";
    const external_link = "_blank";

    const author        = "Antoine Villepreux";
    const version       = "0.8.5";

    define("DOM_CLI", isset($argv));

    #endregion
    #region HELPERS : CONFIG
    ######################################################################################################################################

    function at($a, $k, $d = false)                                                                         { if (is_array($k)) { foreach ($k as $k0) { if (!is_array($a) || !array_key_exists($k0,$a)) return $d; $a = at($a, $k0, $d); } return $a; } else { return (is_array($a) && array_key_exists($k,$a)) ? $a[$k] : $d; } }
    function get_all(                                       $get = true, $post = true, $session = false)    { $a = array(); if ($get) $a = array_merge($a, $_GET); if ($post) $a = array_merge($a, $_POST); if ($session && isset($_SESSION) && is_array($_SESSION)) { $a = array_merge($a, $_SESSION); } return $a; }
    function has($k_or_a, $__or_k = false,                  $get = true, $post = true, $session = false)    { return (is_array($k_or_a)) ? @array_key_exists($__or_k, $k_or_a) : @array_key_exists($k_or_a, get_all($get, $post, $session)); }
    function get($k_or_a, $d_or_k = false, $__or_d = false, $get = true, $post = true, $session = false)    { return (is_array($k_or_a)) ? at($k_or_a, $d_or_k, $__or_d) : at(get_all($get, $post, $session), $k_or_a, $d_or_k); }
    function del($k)                                                                                        { if (has($_GET,$k)) unset($_GET[$k]); if (has($_POST,$k)) unset($_POST[$k]); if (isset($_SESSION) && has($_SESSION,$k)) unset($_SESSION[$k]); }
    function set($k, $v = true, $aname = false)                                                             { if ($aname === false)  { $_GET[$k] = $v; } else if ($aname === "GET")  { $_GET[$k] = $v; } else if ($aname === "POST") { $_POST[$k] = $v; } else if ($aname === "SESSION" && isset($_SESSION)) { $_SESSION[$k] = $v; } return $v; }

    #endregion
    #region HELPERS : SERVER ARGS
    ######################################################################################################################################
    
    if (!is_callable('getallheaders'))
    {
        function getallheaders()
        {
            $headers = array();

            foreach (get_server_vars() as $name => $value)
            {
                if (substr($name, 0, 5) == 'HTTP_')
                {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }
    }

    function server_headers                     ()  { return array_change_key_case(getallheaders(), CASE_LOWER); }

    function header_do_not_track                ()  { return 1 == at(server_headers(), 'dnt',      0); }
    function header_global_privacy_control      ()  { return 1 == at(server_headers(), 'sec-gpc',  0); }

    $__server_vars = array("SERVER","GET");

    function set_server_vars($a, $b = false)    { global $__server_vars; $__server_vars = array(); if ($a !== false) $__server_vars[] = $a; if ($b !== false) $__server_vars[] = $b; }
    function get_server_vars()                  { global $__server_vars; $vars = array(); foreach ($__server_vars as $name) { if ($name == "GET") $vars = array_merge($vars, $_GET); if ($name == "SERVER") $vars = array_merge($vars, $_SERVER); } return $vars; }

    function server_http_accept_language        ($default = "en")                   { return        at(get_server_vars(), 'HTTP_ACCEPT_LANGUAGE',               $default);  }
    function server_server_name                 ($default = "localhost")            { return        at(get_server_vars(), 'SERVER_NAME',                        $default);  }
    function server_server_port                 ($default = "80")                   { return        at(get_server_vars(), 'SERVER_PORT',                        $default);  }
    function server_request_uri                 ($default = "www.example.com")      { return        at(get_server_vars(), 'REQUEST_URI',                        $default);  }
    function server_https                       ($default = "on")                   { return        at(get_server_vars(), 'HTTPS',     is_localhost() ? "off" : $default);  }
    function server_http_host                   ($default = "127.0.0.1")            { return        at(get_server_vars(), 'HTTP_HOST',                          $default);  }
    function server_remote_addr                 ($default = "127.0.0.1")            { return        at(get_server_vars(), 'REMOTE_ADDR',       server_http_host($default)); }
    function server_http_do_not_track           ()                                  { return   1 == at(get_server_vars(), 'HTTP_DNT',                           0);         }

    function do_not_track()
    {
        if (!!get("static")) return true; // PHP do not track detection would not work for static website

        return server_http_do_not_track()
            || header_global_privacy_control()
            || header_do_not_track();
    }
    
    #endregion
    #region HELPERS : DEVELOPMENT ENVIRONMENT
    ######################################################################################################################################

    function is_localhost() { return (false !== stripos(server_http_host(),   "localhost"))
                                  || (false !== stripos(server_http_host(),   "127.0.0.1"))
                                  || (false !== stripos(server_remote_addr(), "::1"      ))
                                  || (false !== stripos(server_remote_addr(), "127.0.0.1")); }

    #endregion
    #region HELPERS : DEBUG : LOG & PROFILING
    ######################################################################################################################################
    
    $__profiling            = array();
    $__profiling_level      = 0;
    $__profiling_timeline   = array();
    $__debug_logs           = array();

    function debug_log($msg = "")
    {
        global $__profiling_timeline, $__profiling_level, $__dom_t0, $__debug_logs;

        $t = microtime(true) - $__dom_t0;

        $t    = mb_str_pad(number_format($t, 2), 6, nbsp(), STR_PAD_LEFT);
        $tab  = str_repeat(nbsp(), 6);
        $tree = str_repeat(nbsp()."|".nbsp().nbsp(), $__profiling_level);

        $__profiling_timeline[] = "$t $tab $tree $msg";
        $__debug_logs[]         = "$t $msg";

        return "";
    }

    function debug_console_line($line = "")
    {
        if ($line == "") $line = nbsp();
        if (0 === stripos($line, "<")) return eol().$line;
        return div($line, "debug-console-line");
    }

    function debug_console($logs = true, $profiling = true, $profiling_totals_only = false)
    {
        $report = array();
        {
            if ($logs)
            {
                global $__debug_logs;

                if (count($__debug_logs) > 0)
                {
                    $report[] = "";
                    $report[] = "LOGS";
                    $report[] = "";

                    $report = array_merge($report, $__debug_logs);
                }
            }

            if ($profiling)
            {
                global $__profiling;

                $totals = array();

                $id_key = "function";

                foreach ($__profiling as $profiling) $totals[$profiling[$id_key].(!!$profiling["tag"] ? ("(".$profiling["tag"].")") : "")] = 0;
                foreach ($__profiling as $profiling) $totals[$profiling[$id_key].(!!$profiling["tag"] ? ("(".$profiling["tag"].")") : "")] += $profiling["dt"];

                if (count($totals) > 0)
                {
                    $report[] = "";
                    $report[] = "PROFILING TOTALS";
                    $report[] = "";

                    arsort($totals);
                    
                    foreach ($totals as $function => $total)
                    {
                        $report[] = mb_str_pad(number_format($total, 2), 6, " ", STR_PAD_LEFT) . ($profiling_totals_only ? "" : " (TOTAL)") . ": " . $function;
                    }
                }

                global $__profiling_timeline;

                if (count($__profiling_timeline) > 0)
                {
                    $report[] = "";
                    $report[] = "PROFILING TIMELINE";
                    $report[] = "";

                    if (!$profiling_totals_only)
                    {
                        $report = array_merge($report, $__profiling_timeline);
                    }
                }
            }
        }

        $html = "";
        {
            $html .= debug_console_line();
            $html .= debug_console_line("PHP Version: ".PHP_VERSION);
            $html .= debug_console_line("DOM Version: ".version);

            if (is_array($report) && count($report) > 0)
            {
                $html .= wrap_each($report, "", "debug_console_line");
            }
        }

        return style("
        
            .debug-console {

                min-height:     100vh; 
                margin:         0; 
                white-space:    nowrap; 
                overflow-x:     auto; 
                background:     black; 
                color:          green; 
                width:          100%;
                font-family:    monospace;
                line-height:    24px;
            }

            .debug-console,
            .debug-console details,
            .debug-console summary {
                
                display:        flex;
                flex-direction: column;
                flex-wrap:      nowrap;
            }

            .debug-console-line,
            .debug-console summary { list-style: none; margin; 0; margin-inline: 0; }
            .debug-console summary::-webkit-details-marker { display: none; height: 0px; margin: 0; padding: 0; }
            .debug-console summary::marker { display: none; height: 0px; margin: 0; padding: 0; }

            ").div($html, "debug-console");
    }
    
    function debug_callstack($shift_current_call = true)
    {
        $callstack = ((PHP_VERSION_ID >= 50400) ? debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 0)  : 
                     ((PHP_VERSION_ID >= 50306) ? debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)     : 
                     ((PHP_VERSION_ID >= 50205) ? debug_backtrace(true)                               : 
                                                  debug_backtrace()                                   )));
                                                  
        if ($shift_current_call) array_shift($callstack);
        return $callstack;
    }
    
    function debug_functions_callstack($shift_current_call = true)
    {
        $callstack = debug_callstack($shift_current_call);
        if ($shift_current_call) array_shift($callstack);

        $functions = array();
        foreach ($callstack as $call) $functions[] = $call["function"];        
        return $functions;
    }

    $__dom_t0 = microtime(true);
    $__debug_track_delta_scope_use_dummy = 0;

    class debug_track_delta_scope
    {
        public $profiling = array();

        function __construct($annotation = false, $function = false)
        {
            $annotation = is_array($annotation) ? json_encode($annotation) : $annotation;

            global $__profiling_level;
            
            global $__dom_t0;
            $t = microtime(true) -  $__dom_t0;

            $functions_callstack = debug_functions_callstack();
            array_shift($functions_callstack); // __construct
            array_shift($functions_callstack); // debug_track_timing
            if (count($functions_callstack) == 0) $functions_callstack[] = "_";
            $functions_callstack_string = str_replace("dom\\", "", implode(".", array_reverse($functions_callstack)));
            $function = str_replace("dom\\", "", !!$function ? $function : $functions_callstack[0]);

            global $__profiling;

            $this->profiling = array(
                "level"     => $__profiling_level,
                "tag"       => $annotation,
                "callstack" => $functions_callstack_string,
                "function"  => $function,
                "dt"        => 0,
                "t"         => $t
                );
            
            global $__profiling;
            $__profiling[] = $this->profiling;
            
            $id_key = "callstack";

            global $__profiling_timeline;
            
            $t = number_format($this->profiling["t"], 2);

          //$__profiling_timeline[] = str_repeat(nbsp(), 6).nbsp().str_repeat(nbsp(), 6).nbsp().str_repeat(nbsp()."|".nbsp().nbsp(), $__profiling_level);
            $__profiling_timeline[] = "<details><summary>";
            $__profiling_timeline[] = mb_str_pad($t, 6, nbsp(), STR_PAD_LEFT).nbsp().str_repeat(nbsp(), 6).nbsp().str_repeat(nbsp()."|".nbsp().nbsp(), $__profiling_level).nbsp()."+-".nbsp().$this->profiling[$id_key] . ((false !== $this->profiling["tag"]) ? ("(".$this->profiling["tag"].")") : "");
            $__profiling_timeline[] = "</summary>";
            $__profiling_timeline[] = str_repeat(nbsp(), 6).nbsp().str_repeat(nbsp(), 6).nbsp().str_repeat(nbsp()."|".nbsp().nbsp(), $__profiling_level).nbsp()."|".nbsp();

            ++$__profiling_level;
        }

        function __destruct()
        {
            global $__profiling_level;
            --$__profiling_level;
            
            global $__dom_t0;
            $t  = microtime(true) - $__dom_t0;
            $dt = $t - $this->profiling["t"];

            $this->profiling["t"]  = $t;
            $this->profiling["dt"] = $dt;

            $id_key = "callstack";
            
            global $__profiling_timeline;
            global $__profiling_level;

            $t  = number_format($this->profiling["t"],  2);
            $dt = number_format($this->profiling["dt"], 2);

            $__profiling_timeline[] =   str_repeat(nbsp(), 6) . nbsp() . 
                                        str_repeat(nbsp(), 6) . nbsp() . 
                                        str_repeat(nbsp()."|".nbsp().nbsp(), $__profiling_level).nbsp()."|".nbsp();
            
            $__profiling_timeline[] =   mb_str_pad(number_format($this->profiling["t"],  2), 6, nbsp(), STR_PAD_LEFT) . nbsp() . 
                                        mb_str_pad(number_format($this->profiling["dt"], 2), 6, nbsp(), STR_PAD_LEFT) . nbsp() . 
                                        str_repeat(nbsp()."|".nbsp().nbsp(), $__profiling_level).nbsp()."+-".nbsp().$this->profiling[$id_key] . ((false !== $this->profiling["tag"]) ? ("(".$this->profiling["tag"].")") : "");
            /*
            $__profiling_timeline[] =   str_repeat(nbsp(), 6) . nbsp() . 
                                        str_repeat(nbsp(), 6) . nbsp() . 
                                        str_repeat(nbsp()."|".nbsp().nbsp(), $__profiling_level)."";*/

            $__profiling_timeline[] = "</details>";
            
            
            global $__profiling;
            $__profiling[] = $this->profiling;

            if ($this->profiling["tag"] != "") 
            {
                $this->profiling["tag"] = "";
                $__profiling[] = $this->profiling;
            }
        }

        function use()
        {
            global $__debug_track_delta_scope_use_dummy;
            ++$__debug_track_delta_scope_use_dummy;
        }
    };

    $__profiling_enabled = false;
    
    function debug_enable_profiling($enable = true)
    {
        global $__profiling_enabled;
        $__profiling_enabled  = $enable;
    }

    function debug_track_timing($annotation = false, $function = false)
    {
        global $__profiling_enabled;
        return $__profiling_enabled ? new debug_track_delta_scope($annotation, $function) : null;
    }

    #endregion
    #region HELPERS : FILE AND FOLDERS PATH FINDER
    ######################################################################################################################################

    function at_root($path = ".")
    {
        foreach (get("root_hints", array()) as $root_hint_file)
        {
            if (file_exists("$path/$root_hint_file")) 
            {
                return true;
            }
        }

        return false;
    }

    $__reentrant_path_guard = false;
        
    function path($path0, $default = false, $search = true, $depth0 = auto, $max_depth = auto, $offset_path0 = ".", $bypass_root_hints = false)
    {
        global $__reentrant_path_guard;

        if ($__reentrant_path_guard)
        {
            bye("RE-ENTRANT CALL TO DOM\PATH", debug_callstack());
        }

        $__reentrant_path_guard = true;

        $profiler = debug_track_timing();

        if ($depth0    === auto) $depth0    = get("path_max_depth", 8);
        if ($max_depth === auto) $max_depth = get("path_max_depth", 8);
        
        $param = "";

        $param_pos = stripos($path0, "?");

        if (false !== $param_pos)
        {
            $param = substr($path0,    $param_pos);
            $path0 = substr($path0, 0, $param_pos);
        }

        $searches = array(array($path0, $depth0, $offset_path0));

        $iterations = 0;

        while (count($searches) > 0 && ++$iterations < 99)
        {
            $path = $searches[0][0]; $depth = $searches[0][1]; $offset_path = $searches[0][2];
            array_shift($searches);
    
            // Minimal early validation for when user is not providing a real url or path but some random text content

            if (false !== stripos($path, "\n") ) { $__reentrant_path_guard = false; return $default; }
            if (false !== stripos($path, "{")  ) { $__reentrant_path_guard = false; return $default; }
            if (false !== stripos($path, "\"") ) { $__reentrant_path_guard = false; return $default; }
        
            // If URL format then keep it as-is

            if (strlen($path) >= 2 && $path[0] == "/" && $path[1] == "/") { $__reentrant_path_guard = false; return $path.$param; }
            if (0 === stripos($path, "http"))                             { $__reentrant_path_guard = false; return $path.$param; }

            // If path exists then directly return it

          //if (@is_dir($path))                                         { $__reentrant_path_guard = false; return $path.$param; }
            if (@file_exists($path))                                    { $__reentrant_path_guard = false; return $path.$param; }
            if (($max_depth == $depth) && url_exists($path))            { $__reentrant_path_guard = false; return $path.$param; }
          //if (($max_depth == $depth) && url_exists(url()."/".$path))  { $__reentrant_path_guard = false; return $path.$param; }

            if (!!get("htaccess_rewrite_php"))
            {
                if (@file_exists("$path.php"))                          { $__reentrant_path_guard = false; return $path.$param; }
                if (($max_depth == $depth) && url_exists("$path.php"))  { $__reentrant_path_guard = false; return $path.$param; }
            }

            // If we have already searched too many times then return fallback

            if ($depth <= 0) 
            {
                { $__reentrant_path_guard = false; return $default; }
            }

            // If beyond root then stop here

            if (!$bypass_root_hints && at_root($offset_path))
            {
                $search = false;
            }

            // If requested then search in parent folder

            if ($search)
            {
                $searches[] = array("../$path", $depth - 1, "../$offset_path");
            }
        }

        //if (false !== stripos($path0, "autoload.php"))    bye("PATH = ", $path0);
        //if ($default == false)                            bye("PATH = ", $path0);

        { $__reentrant_path_guard = false; return $default; }
    }

    function path_coalesce()
    {
        $args = func_get_args();
        return path_coalesce_FUNC_ARGS($args);
    }

    function path_coalesce_FUNC_ARGS($args)
    {
        foreach ($args as $arg)
        {
            $path = path($arg);
            if (!!$path) return $path;
        }

        return false;
    }

    #endregion
    #region HELPERS : PHP FILE INCLUDE

    $__dom_internal_included = false;

    function is_included()
    {
        global $__dom_internal_included;
        return $__dom_internal_included;
    }

    function internal_include($path, $no_display = false)
    {
        if ($no_display) ob_start();
        global $__dom_internal_included; $__dom_internal_included = true;
        if (!!$path) @include($path);
        $__dom_internal_included = false;
        if ($no_display) ob_end_clean();

        update_dependency_graph($path);

        return "";
    }

    function internal_require($path, $no_display = false)
    {
        if ($no_display) ob_start();
        if (!!$path) @require($path);
        if ($no_display) ob_end_clean();
        
        update_dependency_graph($path);

        return "";
    }

    function compile($path)
    {
        return internal_require($path, true);
    }
    
    #endregion
    #region WIP DEPENDENCIES
    ######################################################################################################################################
    
    @internal_include(path("tokens.php"));
    @internal_include(path("vendor/autoload.php"));

    #endregion
    #region SYSTEM : PHP SYSTEM AND CMDLINE HANDLING
    ######################################################################################################################################

    function init_php()
    {        
       global $argv;

        if (is_array($argv) && count($argv) > 1)
        {
            array_shift($argv);

            foreach ($argv as $arg)
            {
                $arg = explode("=", $arg);

                if (count($arg) > 1) { set($arg[0], $arg[1] ); }
                else                 { set($arg[0], true    ); }
            }
        }

        if (is_localhost() || !!get("static"))
        {
            @set_time_limit(24*60*60);
            @ini_set('memory_limit', '-1');
        }

        if (!!get("debug"))
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

    function host_url   ()                                                              { return rtrim("http".((server_https()=='on')?"s":"")."://".server_http_host(),"/"); }
    function url_branch ($params = false, $get = true, $post = true, $session = false)  { $uri = explode('?', server_request_uri(), 2); $uri = $uri[0]; $uri = ltrim($uri, "/"); if ($params) { $uri .= "?"; foreach (get_all($get, $post, $session) as $key => $val) { if (!is_array($val)) $uri .= "&$key=$val"; } } return trim($uri, "/"); }
    function url        ($params = false)                                               { $branch = url_branch($params); return ($branch == "") ? host_url() : host_url()."/".$branch; }

    function live_url($params = false)
    {
        $url_branch = url_branch($params);
        {
            if ($url_branch != "")
            {
                if (0 === stripos($url_branch, get("local_domain", '/'.'/'.'/'.'/'.'/'))) $url_branch = substr($url_branch, strlen(get("local_domain")));
                if (0 === stripos($url_branch, get("live_domain", server_server_name()))) $url_branch = substr($url_branch, strlen(get("live_domain", server_server_name())));
            }

            $url_branch = trim($url_branch, "/");
        }
    
        $url = 'https://'.get("live_domain", server_server_name());
        if ($url_branch != "") $url .= "/$url_branch";

        return $url;
    }

    #endregion
    #region WIP SYSTEM : DEFAULT CONFIG AND AVAILABLE USER OPTIONS
    ######################################################################################################################################

    const manifest_id = "dom.manifest.id";

    function init_options()
    {
        // Cannot be modified at browser URL level

      //set("title",                             "Blog"); // Will be deducted/overriden from document headlines, if any
        del("title");

        set("keywords",                          "");

      //set("url",                               url());                              if (path("DTD/xhtml-target.dtd", path("xhtml-target.dtd")))
      //set("DTD",                              'PUBLIC "-//W3C//DTD XHTML-WithTarget//EN" "'.path("DTD/xhtml-target.dtd", path("xhtml-target.dtd")).'"');

        set("normalize",                        false);/*
        set("normalize",                        "sanitize");
        set("reset",                            "evergreen");*/

        set("icons_path",                       "img/icons/");

        // Default to an AA (light) contrasted theme 
        /*
        set("theme_color",                      "#990011"); 
        set("accent_color",                     "#112299");
        set("background_color",                 "#f2f2f2");
        set("text_color",                       "#0d0d0d");
        set("link_color",                       "#aa4455"); */
        
        set("css_layers_support",               true);
    
        set("default_image_ratio_w",            "300");
        set("default_image_ratio_h",            "200");

        set("default_scrollbar_width",          "17px"); // It's a css env var

        set("image",                            "image.jpg");
        set("geo_region",                       "FR-75");
        set("geo_placename",                    "Paris");
        set("geo_position_x",                   48.862808);
        set("geo_position_y",                    2.348237);

        set("support_service_worker",           true);
        
      //set("fonts",                            "Roboto:300,400,500");
            
        set("twitter_user",                     "me");
        set("twitter_page",                     get("twitter_user", "me"));
        set("linkedin_page",                    "me");
        set("facebook_page",                    "me");
        set("tumblr_blog",                      "blog");
        set("instagram_user",                   "self");
        set("pinterest_user",                   "blog");
        set("pinterest_board",                  "blog");
        set("flickr_user",                      "blog");
        set("messenger_id",                     "me");
            
        set("exclude_pinterest_pins_ids",       "");
        set("exclude_tumblr_slugs",             "");
        set("exclude_instagram_codes",          "");
        set("exclude_instagram_users",          "");
        set("exclude_facebook_post_ids",        "");
        set("exclude_facebook_text_md5s",       "");
            
        set("support_metadata_person",          true);
        set("support_metadata_organization",    true);
            
        set("include_custom_css",               false);

        set("dom-auto-include-css",             true);
            
        set("carousel",                         true);
            
        set("version_normalize",               "11.0.1");
        set("version_sanitize",                "13.0.0");  
        set("version_evergreen",               "10.0.0");  
        set("version_material",                "0.38.2"); // latest => SimpleMenu got broken in 0.30.0 => Got fixed in CSS => latest => Broken in 0.39.0 => 0.38.0
        set("version_bootstrap",                "4.1.1");
        set("version_spectre",                  "x.y.z");
        set("version_popper",                  "1.11.0");
        set("version_jquery",                   "3.6.0"); // Was 3.5.1 / Was 3.2.1
        set("version_prefixfree",               "1.0.7");
        set("version_h5bp",                     "7.1.0");
        
        set("cache_time",                       1*60*60); // 1h

        set("forwarded_flags",                  array("amp","contrast","light","no_js","no_css","rss"));
        set("root_hints",                       array(".git", ".github", ".well-known"));

        set("img_lazy_loading_after",           3);

        // Can be modified at browser URL level

        set("canonical",                        get("canonical",    url()   ));
        set("framework",                        get("framework",    "NONE"  ));
        set("generate",                         get("generate",     false   ));
        set("amp",                              get("amp",          false   ));
        set("cache",                            get("cache",        false   ));
        set("minify",                           get("minify",       true    )); // Performances first
        set("page",                             get("page",         1       ));
        set("n",                                get("n",            12      ));

        // Options that impact others

        if (AMP())              {   del("css_layers_support");  }
        if (!!get("beautify"))  {   set("minify",  false);      }
        if (!!get("gemini"))    {   set("static",  true);
                                    set("noajax",  true);
                                    set("nolazy",  true);        }
    }

    #endregion
    #region CONFIG : INTERNALS
    ######################################################################################################################################

    function init_internals()
    {
        if (!defined("DOM_AJAX_PARAMS_SEPARATOR1")) define("DOM_AJAX_PARAMS_SEPARATOR1", "-_-");
        if (!defined("DOM_AJAX_PARAMS_SEPARATOR2")) define("DOM_AJAX_PARAMS_SEPARATOR2", "_-_");
        
        if (has("rand_seed")) { mt_srand(get("rand_seed")); }
    }

    #endregion
    #region HELPERS : AJAX / ASYNC
    ######################################################################################################################################

    function ajax_url_base_params($get = true, $post = false, $session = false)
    {
        // ! TODO prevent exposing all the vars
        $vars = get_all($get, $post, $session);
        unset($vars["support_header_backgrounds"]); // Can lead to much too long URLs
        return $vars;
    }

    function ajax_url           ($ajax_params, 
                                 $get = true, $post = false, $session = false)      { return './?'.http_build_query(array_merge(ajax_url_base_params($get, $post, $session), array("ajax" => $ajax_params))); }

    function ajax_param_encode2 ($p)                                                { return (is_array($p))                                     ? implode(DOM_AJAX_PARAMS_SEPARATOR2, $p) : $p; }
    function ajax_param_decode2 ($p)                                                { return (false !== strpos($p, DOM_AJAX_PARAMS_SEPARATOR2)) ? explode(DOM_AJAX_PARAMS_SEPARATOR2, $p) : $p; }

    function ajax_param_encode  ($prefix, $params = array())                        {                                               return $prefix . '-' .                     implode(DOM_AJAX_PARAMS_SEPARATOR1, array_map("dom\ajax_param_encode2", $params)); }
    function ajax_param_decode  ($prefix, $params)                                  { $params = substr($params, strlen($prefix)+1); return array_map("dom\ajax_param_decode2", explode(DOM_AJAX_PARAMS_SEPARATOR1, $params)); }

    function ajax_placeholder   ($ajax_params, $html = "")                          { return div($html, ajax_classes($ajax_params)); }
    
    function ajax_classes       ($ajax_params, $extra = false)                      { return "ajax-container ajax-container-".to_classname($ajax_params).(($extra !== false) ? (" ajax-container-".to_classname($extra)) : ""); }
    function ajax_container     ($ajax_params, $placeholder = false, $period = -1,
                                 $get = true, $post = false, $session = false)      { return  (($placeholder === false) ? ajax_placeholder($ajax_params) : $placeholder) . '<script>ajax("'.ajax_url($ajax_params, $get, $post, $session).'", function(content) { document.querySelector(".ajax-container-'.to_classname($ajax_params).'").outerHTML = content; on_ajax_reception(); }, '.$period.'); </script>'; }

    function ajax_call          ($f)                                                { $args = func_get_args(); return ajax_call_FUNC_ARGS($f, $args); }
        
    function ajax_call_FUNC_ARGS($f, $args)
    {            
        $async_params = -1;

        if (is_numeric($f) || is_array($f))
        {
            $async_params = $f;
            array_shift($args);
            $f = $args[0];
        }

        array_shift($args);

        $get = true;

        if (is_string($f) && false !== stripos($f, "-NO-ENV"))
        {
            $f = str_replace("-NO-ENV", "", $f);
            $get = false;
        }
    
        return ajax_call_with_args($f, $async_params, $args, $get);
    }
        
    function ajax_call_with_args($f, $async_params, $args, $get = true, $post = false, $session = false)
    {
        // Async calls disabled
        
        if (has("noajax") || !!get("no_js") || has("rss") || AMP())
        {  
            $n = stripos($f,"/");
            $f = (false === $n) ? $f : substr($f, 0, $n);
 
            if (!is_callable($f)) $f = "dom\\$f";

            return call_user_func_array($f, $args);
        }
        
        // Async calls enabled
        
        $ajax = get("ajax", false);

        if (false === $ajax)
        {
            // Async caller (or client)
        
            foreach ($args as &$arg)
            {
                if (false === $arg) $arg = "FALSE";
                if (true  === $arg) $arg = "TRUE";
            }
            
            $ajax = ajax_param_encode($f, $args);

            $period             = $async_params;
            $placeholder        = "dom\img_loading";
            $placeholder_args   = null;
            
            if (is_array($async_params))
            {             
                $period             = at($async_params, "period",           at($async_params, 0, $period));
                $placeholder        = at($async_params, "placeholder",      at($async_params, 1, $placeholder));
                $get                = at($async_params, "get",              at($async_params, 2, $get));
                $placeholder_args   = at($async_params, "placeholder_args", at($async_params, 3, $placeholder_args));
            }

            return ajax_container($ajax, $placeholder_args !== null ? $placeholder($placeholder_args, ajax_classes($ajax, $f)) : $placeholder(ajax_classes($ajax, $f)), $period, $get, $post, $session);
        }
        else
        {
            // Async listener (or server)
        
            global $call_asyncs_started;
            if (!$call_asyncs_started)    return ""; // We have not started listening yet
            if (0 !== stripos($ajax, $f)) return ""; // This is not the function you are looking for
            
            $args = ajax_param_decode($f, $ajax);
            
            foreach ($args as &$arg)
            {
                if ($arg === "FALSE") $arg = false;
                if ($arg === "TRUE")  $arg = true;
            }    
            
            $n = stripos($f,"/");
            $f = (false === $n) ? $f : substr($f, 0, $n);

            if (!is_callable($f) && is_callable("dom\\$f")) $f = "dom\\$f";

            return call_user_func_array($f, $args);
        }
    }

    #endregion
    #region HELPERS : HEREDOC

    function modify_tab($txt, $tab_offset, $tab = "    ", $line_sep = PHP_EOL)
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

    function heredoc_start($tab_offset = 0, $tab = "    ")
    {
        if (false === get("heredoc")) set("heredoc", array());

        $heredoc_stack = get("heredoc");

        $heredoc_stack[] = array(

            "current_output" => "",
            "tab_offset"     => $tab_offset,
            "tab"            => $tab,
            "next_transform" => false
        );

        set("heredoc", $heredoc_stack);
        
        ob_start();

        return "";
    }

    function heredoc_flush($transform = false, $transform_force_minify = false, $transform_trim = true)
    {
        $heredoc_stack = get("heredoc");
        $output        = ob_get_contents();
 
        if ($transform == false && at($heredoc_stack[count($heredoc_stack)-1], "next_transform") != false)
        {
            $transform = at($heredoc_stack[count($heredoc_stack)-1], "next_transform");
        }

        $heredoc_stack[count($heredoc_stack)-1]["next_transform"] = false;
        {
                 if ($output == "<style>"  ) $heredoc_stack[count($heredoc_stack)-1]["next_transform"] = "raw_css";
            else if ($output == "<script>" ) $heredoc_stack[count($heredoc_stack)-1]["next_transform"] = "raw_js";
            else if ($output == "<html>"   ) $heredoc_stack[count($heredoc_stack)-1]["next_transform"] = "raw_html";
            else if ($output == "<xml>"    ) $heredoc_stack[count($heredoc_stack)-1]["next_transform"] = "raw_xml";
            else if ($output == "<opml>"   ) $heredoc_stack[count($heredoc_stack)-1]["next_transform"] = "raw_xml";
        }

        if (null !== $transform)
        {
            if ($heredoc_stack[count($heredoc_stack)-1]["tab_offset"] != 0) $output = modify_tab($output, $heredoc_stack[count($heredoc_stack)-1]["tab_offset"], $heredoc_stack[count($heredoc_stack)-1]["tab"]);
            
            if (!!$transform) 
            {
                if (!is_callable($transform)) $transform = "dom\\$transform";
                if ( is_callable($transform)) $output = $transform($output, $transform_force_minify, $transform_trim);
            }
        
            $heredoc_stack[count($heredoc_stack)-1]["current_output"] .= $output;
        }   
        
        set("heredoc", $heredoc_stack);

        ob_end_clean();
        ob_start();
    }

    function heredoc_stop($transform = false, $transform_force_minify = false, $transform_trim = true)
    {
        heredoc_flush($transform, $transform_force_minify, $transform_trim);
        ob_end_clean();
        
        $heredoc_stack = get("heredoc");
        $heredoc = array_pop($heredoc_stack);
        set("heredoc", $heredoc_stack);

        return $heredoc["current_output"];
    }

    #endregion
    #region JAVASCRIPT SNIPPETS
    ######################################################################################################################################

    function js_inside_iframe()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            /* DOM Head Javascript boilerplate : Inside iframe detection */

            var __dom_in_iframe = function () {
                try {
                    return window.self !== window.top;
                } catch (Exception) {
                    return true;
                }
            };
            
            if (__dom_in_iframe()) {

                document.getElementsByTagName('html')[0].classList.add('in-iframe');
            }

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_common_head()
    {
        $svg_light = 'data:image/svg+xml;base64,PHN2ZyBjbGFzcz0idmlsbGFwaXJvcnVtLWZhdmljb24iIHdpZHRoPSI1MTIiIGhlaWdodD0iNTEyIiB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4NCiA8c3R5bGUgdHlwZT0idGV4dC9jc3MiPjwhW0NEQVRBWw0KICAgIC52aWxsYXBpcm9ydW0tZmF2aWNvbiB7IA0KICAgICAgICAtLXByaW1hcnktY29sb3I6ICAgIHZhcigtLXRoZW1lLWNvbG9yLCAjYWEyMjAwKTsNCiAgICAgICAgLS1zZWNvbmRhcnktY29sb3I6ICB2YXIoLS1hY2NlbnQtY29sb3IsICM1NWRkZmYpOw0KICAgICAgICB3aWR0aDogdmFyKC0td2lkdGgsIDUxMnB4KTsNCiAgICAgICAgaGVpZ2h0OiB2YXIoLS1oZWlnaHQsIDUxMnB4KTsNCiAgICAgICAgfQ0KICBdXT48L3N0eWxlPg0KIDxkZWZzPg0KICA8bGluZWFyR3JhZGllbnQgc3ByZWFkTWV0aG9kPSJwYWQiIHkyPSIwIiB4Mj0iMSIgeTE9IjAiIHgxPSIwIiBpZD0idmlsbGFwaXJvcnVtLWljb24tZ3JhZGllbnQiPg0KICAgPHN0b3AgY2xhc3M9InN0b3AtcHJpbWFyeSIgb2Zmc2V0PSIwIiAvPg0KICAgPHN0b3AgY2xhc3M9InN0b3Atc2Vjb25kYXJ5IiBvZmZzZXQ9IjEiIC8+DQogIDwvbGluZWFyR3JhZGllbnQ+DQogICAgPHN0eWxlIHR5cGU9InRleHQvY3NzIj48IVtDREFUQVsNCiAgICAuc3RvcC1wcmltYXJ5ICAgeyBzdG9wLWNvbG9yOiB2YXIoLS1wcmltYXJ5LWNvbG9yKTsgICB9DQogICAgLnN0b3Atc2Vjb25kYXJ5IHsgc3RvcC1jb2xvcjogdmFyKC0tc2Vjb25kYXJ5LWNvbG9yKTsgfQ0KICAgIF1dPjwvc3R5bGU+DQogIDxmaWx0ZXIgaGVpZ2h0PSIyMDAlIiB3aWR0aD0iMjAwJSIgeT0iLTUwJSIgeD0iLTUwJSIgaWQ9InZpbGxhcGlyb3J1bS1pY29uLWJsdXIiPg0KICAgPGZlR2F1c3NpYW5CbHVyIHN0ZERldmlhdGlvbj0iMTAiIGluPSJTb3VyY2VHcmFwaGljIi8+DQogIDwvZmlsdGVyPg0KIDwvZGVmcz4NCiA8Zz4NCiAgPGVsbGlwc2UgZmlsdGVyPSJ1cmwoI3ZpbGxhcGlyb3J1bS1pY29uLWJsdXIpIiBzdHJva2U9IiNmZmYiIHJ5PSIyMDAiIHJ4PSIyMDAiIGlkPSJ2aWxsYXBpcm9ydW0taWNvbi1zdmctNSIgY3k9IjI1NiIgY3g9IjI1NiIgc3Ryb2tlLW9wYWNpdHk9Im51bGwiIHN0cm9rZS13aWR0aD0iMCIgZmlsbD0idXJsKCN2aWxsYXBpcm9ydW0taWNvbi1ncmFkaWVudCkiPg0KICAgIDxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgYXR0cmlidXRlVHlwZT0iWE1MIiB0eXBlPSJyb3RhdGUiIGZyb209IjAgMjU2IDI1NiIgdG89IjM2MCAyNTYgMjU2IiBkdXI9IjEwcyIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiLz4NCiAgPC9lbGxpcHNlPg0KICA8cGF0aCBkPSJtNDYuNSwzODkuMDQ5OTg4YzAsMCAzLjU0OTE1NiwxLjk1NTE3IDcsM2MwLjk1NzA5MiwwLjI4OTc5NSA0LjkyMTU3NCwtMS4xMjI5NTUgOCwtM2M4Ljc5MDQzNiwtNS4zNTk4OTQgMjAuNzQ1MTE3LC0xMC43MjEyMjIgMzQsLTE1YzE4LjY5Njg2MSwtNi4wMzU1MjIgMjkuOTI1Nzk3LC02LjQ5NzU1OSAzNSwtN2M3Ljk2MTA2LC0wLjc4ODMgMTIuMjI4MzYzLDAuODUxOTU5IDE1LDJjMi42MTMxMjksMS4wODIzOTcgMy41NDg2MywyLjc2OTkyOCA1LDdjMS42NTQ4MTYsNC44MjMwMjkgMy40OTgyOTEsMTAuOTM3OTU4IDQsMTdjMC43NDIzMjUsOC45NjkzMyAtMC43MzMwOTMsMTYuMDQ0OTUyIDAsMjJjMC41MDM3NjksNC4wOTIyMjQgMC44Nzc2NTUsNi4wNjYwMSAyLDhjMS44MDk3MjMsMy4xMTg0NjkgNiw2IDEwLDhjNCwyIDQuMDUzNDk3LDEuNTQwNDk3IDYsMmM0LjM1MjUwOSwxLjAyNzQ5NiAxMy43NDkxNDYsNi45MzE2MSAyNywxMGMxOC43Mzk1MzIsNC4zMzkzNTUgMzEuODkxNzA4LDcuODA0MjMgMzksNGMxLjk3MTQ4MSwtMS4wNTUxMTUgMS43MTQxMjcsLTMuMjExNjcgNCwtNmMzLjU4NjM4LC00LjM3NDY5NSA3LjQzMjg3NywtNy44MDI0MjkgMTAsLTE0YzIuNDIwMzAzLC01Ljg0MzE0IDMuNzEwMjA1LC05LjA0MjkwOCA0LC0xMGMxLjA0NDgzLC0zLjQ1MDgzNiA0LjYwNjQ0NSwtNS41Mzg2OTYgNiwtOGMyLjAzMTQ2NCwtMy41ODc5MjEgMi4yODg1NzQsLTUuODY4Mjg2IDQsLTEwYzIuNDIwMjg4LC01Ljg0MzE0IDIsLTE0IDIsLTE4bDAsLTMiIGlkPSJ2aWxsYXBpcm9ydW0taWNvbi1kaXNrIiBmaWxsLW9wYWNpdHk9Im51bGwiIHN0cm9rZS1vcGFjaXR5PSJudWxsIiBzdHJva2Utd2lkdGg9IjAiIHN0cm9rZT0iI2ZmZiIgZmlsbD0ibm9uZSIvPg0KIDwvZz4NCjwvc3ZnPg==';
        $svg_dark  = 'data:image/svg+xml;base64,PHN2ZyBjbGFzcz0idmlsbGFwaXJvcnVtLWZhdmljb24iIHdpZHRoPSI1MTIiIGhlaWdodD0iNTEyIiB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4NCiA8c3R5bGUgdHlwZT0idGV4dC9jc3MiPjwhW0NEQVRBWw0KICAgIC52aWxsYXBpcm9ydW0tZmF2aWNvbiB7IA0KICAgICAgICAtLXByaW1hcnktY29sb3I6ICAgIHZhcigtLXRoZW1lLWNvbG9yLCAjZmY2ZWZmKTsNCiAgICAgICAgLS1zZWNvbmRhcnktY29sb3I6ICB2YXIoLS1hY2NlbnQtY29sb3IsICMyMmNjZWUpOw0KICAgICAgICB3aWR0aDogdmFyKC0td2lkdGgsIDUxMnB4KTsNCiAgICAgICAgaGVpZ2h0OiB2YXIoLS1oZWlnaHQsIDUxMnB4KTsNCiAgICAgICAgfQ0KICBdXT48L3N0eWxlPg0KIDxkZWZzPg0KICA8bGluZWFyR3JhZGllbnQgc3ByZWFkTWV0aG9kPSJwYWQiIHkyPSIwIiB4Mj0iMSIgeTE9IjAiIHgxPSIwIiBpZD0idmlsbGFwaXJvcnVtLWljb24tZ3JhZGllbnQiPg0KICAgPHN0b3AgY2xhc3M9InN0b3AtcHJpbWFyeSIgb2Zmc2V0PSIwIiAvPg0KICAgPHN0b3AgY2xhc3M9InN0b3Atc2Vjb25kYXJ5IiBvZmZzZXQ9IjEiIC8+DQogIDwvbGluZWFyR3JhZGllbnQ+DQogICAgPHN0eWxlIHR5cGU9InRleHQvY3NzIj48IVtDREFUQVsNCiAgICAuc3RvcC1wcmltYXJ5ICAgeyBzdG9wLWNvbG9yOiB2YXIoLS1wcmltYXJ5LWNvbG9yKTsgICB9DQogICAgLnN0b3Atc2Vjb25kYXJ5IHsgc3RvcC1jb2xvcjogdmFyKC0tc2Vjb25kYXJ5LWNvbG9yKTsgfQ0KICAgIF1dPjwvc3R5bGU+DQogIDxmaWx0ZXIgaGVpZ2h0PSIyMDAlIiB3aWR0aD0iMjAwJSIgeT0iLTUwJSIgeD0iLTUwJSIgaWQ9InZpbGxhcGlyb3J1bS1pY29uLWJsdXIiPg0KICAgPGZlR2F1c3NpYW5CbHVyIHN0ZERldmlhdGlvbj0iMTAiIGluPSJTb3VyY2VHcmFwaGljIi8+DQogIDwvZmlsdGVyPg0KIDwvZGVmcz4NCiA8Zz4NCiAgPGVsbGlwc2UgZmlsdGVyPSJ1cmwoI3ZpbGxhcGlyb3J1bS1pY29uLWJsdXIpIiBzdHJva2U9IiNmZmYiIHJ5PSIyMDAiIHJ4PSIyMDAiIGlkPSJ2aWxsYXBpcm9ydW0taWNvbi1zdmctNSIgY3k9IjI1NiIgY3g9IjI1NiIgc3Ryb2tlLW9wYWNpdHk9Im51bGwiIHN0cm9rZS13aWR0aD0iMCIgZmlsbD0idXJsKCN2aWxsYXBpcm9ydW0taWNvbi1ncmFkaWVudCkiPg0KICAgIDxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgYXR0cmlidXRlVHlwZT0iWE1MIiB0eXBlPSJyb3RhdGUiIGZyb209IjAgMjU2IDI1NiIgdG89IjM2MCAyNTYgMjU2IiBkdXI9IjEwcyIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiLz4NCiAgPC9lbGxpcHNlPg0KICA8cGF0aCBkPSJtNDYuNSwzODkuMDQ5OTg4YzAsMCAzLjU0OTE1NiwxLjk1NTE3IDcsM2MwLjk1NzA5MiwwLjI4OTc5NSA0LjkyMTU3NCwtMS4xMjI5NTUgOCwtM2M4Ljc5MDQzNiwtNS4zNTk4OTQgMjAuNzQ1MTE3LC0xMC43MjEyMjIgMzQsLTE1YzE4LjY5Njg2MSwtNi4wMzU1MjIgMjkuOTI1Nzk3LC02LjQ5NzU1OSAzNSwtN2M3Ljk2MTA2LC0wLjc4ODMgMTIuMjI4MzYzLDAuODUxOTU5IDE1LDJjMi42MTMxMjksMS4wODIzOTcgMy41NDg2MywyLjc2OTkyOCA1LDdjMS42NTQ4MTYsNC44MjMwMjkgMy40OTgyOTEsMTAuOTM3OTU4IDQsMTdjMC43NDIzMjUsOC45NjkzMyAtMC43MzMwOTMsMTYuMDQ0OTUyIDAsMjJjMC41MDM3NjksNC4wOTIyMjQgMC44Nzc2NTUsNi4wNjYwMSAyLDhjMS44MDk3MjMsMy4xMTg0NjkgNiw2IDEwLDhjNCwyIDQuMDUzNDk3LDEuNTQwNDk3IDYsMmM0LjM1MjUwOSwxLjAyNzQ5NiAxMy43NDkxNDYsNi45MzE2MSAyNywxMGMxOC43Mzk1MzIsNC4zMzkzNTUgMzEuODkxNzA4LDcuODA0MjMgMzksNGMxLjk3MTQ4MSwtMS4wNTUxMTUgMS43MTQxMjcsLTMuMjExNjcgNCwtNmMzLjU4NjM4LC00LjM3NDY5NSA3LjQzMjg3NywtNy44MDI0MjkgMTAsLTE0YzIuNDIwMzAzLC01Ljg0MzE0IDMuNzEwMjA1LC05LjA0MjkwOCA0LC0xMGMxLjA0NDgzLC0zLjQ1MDgzNiA0LjYwNjQ0NSwtNS41Mzg2OTYgNiwtOGMyLjAzMTQ2NCwtMy41ODc5MjEgMi4yODg1NzQsLTUuODY4Mjg2IDQsLTEwYzIuNDIwMjg4LC01Ljg0MzE0IDIsLTE0IDIsLTE4bDAsLTMiIGlkPSJ2aWxsYXBpcm9ydW0taWNvbi1kaXNrIiBmaWxsLW9wYWNpdHk9Im51bGwiIHN0cm9rZS1vcGFjaXR5PSJudWxsIiBzdHJva2Utd2lkdGg9IjAiIHN0cm9rZT0iI2ZmZiIgZmlsbD0ibm9uZSIvPg0KIDwvZz4NCjwvc3ZnPg==';

        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            var dom = function () {}; /* TODO put all dom js utilities in there */

            dom.log_format_logo         = "%c ";
            dom.log_format_dom          = "%cDOM";
            dom.log_format_dom_version  = "%c<?= version ?>";

            dom.log_css_logo            = "margin-right: 4px;                                 padding: 0 4px 0 4px; background: no-repeat center/16px url('<?= $svg_dark ?>');";
            dom.log_css_dom             = "margin-right: 0px; font-weight: bold; color: #000; padding: 0 4px 0 4px; background-color: #ff6eff; border-radius: 6px 0 0 6px; border: 1px solid white;";
            dom.log_css_dom_version     = "margin-right: 4px; font-weight: bold; color: #000; padding: 0 4px 0 4px; background-color: #22ccee; border-radius: 0 6px 6px 0; border: 1px solid white;";

            dom.log = function() {

                console.info(
                    dom.log_format_logo + dom.log_format_dom + dom.log_format_dom_version,
                    dom.log_css_logo,     dom.log_css_dom,     dom.log_css_dom_version,
                    ...arguments
                );
            };

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_ajax_head()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            /* DOM Head Javascript boilerplate */

            var process_ajax = function(url, onsuccess, period, onstart, mindelay)
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
                            ajax(url, onsuccess, period, onstart, mindelay);
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
                                ajax(url, onsuccess, period, onstart, mindelay);
                                }, period);
                            }
                        }
                });*/
            };
            
            var pop_ajax_call = function()
            {
                if ((typeof ajax_pending_calls !== "undefined") && ajax_pending_calls.length > 0)
                {
                    var ajax_pending_call = ajax_pending_calls.pop();
            
                    <?php if (!!get("debug")) { ?> dom.log("Processing ajax pending call: " + ajax_pending_call[0]); dom.log(ajax_pending_call); <?php } ?> 
                    process_ajax(ajax_pending_call[0], ajax_pending_call[1], ajax_pending_call[2], ajax_pending_call[3], ajax_pending_call[4]);
                }
            };

            var ajax_pending_calls = [];

            function ajax(url, onsuccess, period, onstart, mindelay)
            {
                if (typeof ajax_url_query_hook != "undefined")
                {
                    url = ajax_url_query_hook(url);
                }

                ajax_pending_calls.push(new Array(url, onsuccess, period, onstart, mindelay));
                requestAnimationFrame(pop_ajax_call);
            };

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_ajax_body()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            /* DOM Body Javascript boilerplate */
            
            on_loaded(function() {

                while ((typeof ajax_pending_calls !== "undefined") && ajax_pending_calls.length > 0) { pop_ajax_call(); };
                /*setInterval(pop_ajax_call, 1*1000);*/

                });
            
        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    #endregion
    #region HELPERS : DOM COMPONENTS: TAG ATTRIBUTES
    ######################################################################################################################################

    function attributes_as_string($attributes, $pan = 0)
    {
        if (false === $attributes) return "";
        if (""    === $attributes) return "";
        if (" "   === $attributes) return "";
            
        if (is_array($attributes))
        {
            $html = '';
            
            if (is_array($pan)) { $i = 0; foreach ($attributes as $key => $value) { if (is_array($value)) { $value = implode($key == "style" ? ";" : " ", $value); } $value = trim($value); if ($value != "") $html .= pan(' ' . $key . '=' . '"' . trim($value) . '"', $pan[$i], ' ', 1); ++$i; } }
            else                {         foreach ($attributes as $key => $value) { if (is_array($value)) { $value = implode($key == "style" ? ";" : " ", $value); } $value = trim($value); if ($value != "") $html .= pan(' ' . $key . '=' . '"' . trim($value) . '"', $pan,     ' ', 1);       } }
            
            return $html;
        }
        
        if ($attributes != "")
        {
            $attributes = trim($attributes);

            if (false === strpos($attributes, '=')) { return ' class="' . $attributes.'"'; }
            
            return ' '.$attributes;
        }
        
        return $attributes;
    }

    function to_attributes($attributes)
    {   
        if (false === $attributes)
        {
            return array();
        }

        if (is_array($attributes)) 
        {
            return $attributes;
        }

        if (false === stripos($attributes, "="))
        {
            return array("class" => trim($attributes));
        }

        $xml = false;
        if (!$xml) $xml = @simplexml_load_string(                                  $attributes,            null, LIBXML_NOCDATA);
        if (!$xml) $xml = @simplexml_load_string("<div ".str_replace("&", "&amp;", $attributes)."></div>", null, LIBXML_NOCDATA);
        
        if (!!$xml)
        {
            $attributes = at(@json_decode(@json_encode($xml), true), "@attributes", array());
        }
        else
        {
            // TODO: Last resort fallback. Works only for one attribute

            list($key, $val) = explode("=", $attributes);
            $val = trim(trim($val, '"'), "'");
            $key = trim($key);
            $attributes = array($key => $val);
        }

        return $attributes;
    }
    
    function attribute($name, $values = auto)
    {
        return ($values === auto) ? to_attributes($name) : array($name => $values);
    }
    
    function attr($name, $values = auto)
    {
        return attribute($name, $values);
    }

    function attributes_add($attributes1, $attributes2, $value = null)
    {
        $attributes1 = to_attributes($attributes1);
        $attributes2 = to_attributes($attributes2);

        $attributes = array();

        foreach ($attributes1 as $name1 => $values1)
        {
            if (!is_array($values1))
            {
                if ($name1 == "style")
                {
                    $values1 = explode(";", $values1);
                }
                else
                {
                    $values1 = explode(" ", $values1);
                }
            }

            foreach ($attributes2 as $name2 => $values2)
            {
                if ($name2 == $name1)
                {
                    if (!is_array($values2)) 
                    {
                        if ($name2 == "style")
                        {
                            $values2 = explode(";", $values2);
                        }
                        else
                        {
                            $values2 = explode(" ", $values2);
                        }
                    }

                    $values1 = array_values(array_merge($values1, $values2));
                }
            }
            
            $attributes[$name1] = $values1;
        }

        $names1 = array_keys($attributes1);

        foreach ($attributes2 as $name2 => $values2)
        {
            if (!in_array($name2, $names1))
            {
                if (!is_array($values2))
                {
                    if ($name2 == "style")
                    {
                        $values2 = explode(";", $values2);
                    }
                    else
                    {
                        $values2 = explode(" ", $values2);
                    }
                }
                
                $attributes[$name2] = $values2;
            }
        }

        return $attributes;
    }

    function attributes()
    {
        $attributes = array();
        
        foreach (func_get_args() as $attribute)
        {
            $attributes = attributes_add($attributes, $attribute);
        }

        return $attributes;
    }
    
    function attributes_add_class($attributes, $classname, $add_first = false)
    {
        $attributes = attributes_as_string($attributes);

        if ("" === $attributes) return attributes_as_string($classname);
        if ("" === $classname)  return attributes_as_string($attributes);
            
        if (false === stripos($attributes, "class="))
        {
            $attributes .= " class=\"\"";
        }

        $bgn = stripos($attributes, "class=");      if (false === $bgn) return $attributes;
        $bgn = stripos($attributes, '"', $bgn);     if (false === $bgn) return $attributes;
        $end = stripos($attributes, '"', $bgn + 1); if (false === $bgn) return $attributes;

        $classes = substr($attributes, $bgn + 1, $end - $bgn - 1);

        if ($add_first) $classes = $classname.($classes != "" ? " " : "").$classes;
        else            $classes = $classes  .($classes != "" ? " " : "").$classname;

        $attributes = substr($attributes, 0, $bgn + 1) . $classes . substr($attributes, $end);

        return $attributes;
    }

    #endregion
    #region HELPERS : DOM COMPONENTS: FRAWEWORK CLASSES
    
    function frameworks_material_classes_grid_cells() { $a = array(); foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $s) foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $m) foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $l) $a["grid-cell-$s-$m-$m"] = 'mdc-layout-grid__cell--span-'.$s.'-phone mdc-layout-grid__cell--span-'.$m.'-tablet mdc-layout-grid__cell--span-'.$l.'-desktop'; return $a; }

    $__frameworks = array
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
                    
            ,   'h1'                        => 'mdc-typography--headline4'
            ,   'h2'                        => 'mdc-typography--headline6'
            ,   'h3'                        => 'mdc-typography--headline8'
            ,   'h4'                        => 'mdc-typography--headline10'
            ,   'h5'                        => 'mdc-typography--headline12'
            ,   'h6'                        => 'mdc-typography--headline14'
            ,   'h7'                        => 'mdc-typography--headline16'
            ,   'h8'                        => 'mdc-typography--headline18'
            ,   'h9'                        => 'mdc-typography--headline20'
            
            ,   'footer'                    => 'mdc-theme--primary'
            ,   'grid'                      => 'mdc-layout-grid max-width'
            ,   'grid-row'                  => 'mdc-layout-grid__inner'
            ,   'grid-cell'                 => 'mdc-layout-grid__cell'), frameworks_material_classes_grid_cells(), array(
            
                'progressbar'               => 'mdc-linear-progress mdc-linear-progress--indeterminate'
            ,   'progressbar-buffer'        => 'mdc-linear-progress__buffer'
            ,   'progressbar-buffer-dots'   => 'mdc-linear-progress__buffering-dots'
            ,   'progressbar-primary-bar'   => 'mdc-linear-progress__bar mdc-linear-progress__primary-bar'
            ,   'progressbar-secondary-bar' => 'mdc-linear-progress__bar mdc-linear-progress__secondary-bar'   
            ,   'progressbar-bar-inner'     => 'mdc-linear-progress__bar-inner'
            
            ,   'list'                      => 'mdc-list'
            ,   'list-item'                 => 'mdc-list-item'
            ,   'list-item-separator'       => 'mdc-list-divider'
                
            ))
        )
        
       ,"bootstrap" => array
        (
            "classes" => array
            (
                'list-item-separator'   => 'dropdown-divider'     
            )
        )
        
       ,"spectre" => array
        (
            "classes" => array
            (
                'button'                    => 'btn'
                    
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
    
    function init_extend_frameworks_table($component_frameworks_table)
    {
        global $__frameworks;

        foreach ($component_frameworks_table as $framework_name => $framework)
        {
            if (!array_key_exists($framework_name, $__frameworks))
            {
                $__frameworks[$framework_name] = array();
            }

            foreach ($framework as $category => $properties)
            {
                if (!array_key_exists($category, $__frameworks[$framework_name]))
                {
                    $__frameworks[$framework_name][$category] = array();
                }

                foreach ($properties as $classname => $framework_classnames)
                {
                    $__frameworks[$framework_name][$category][$classname] = $framework_classnames;
                }
            }
        }
    }

    function component_class($tag, $classnames = "") 
    {
        global $__frameworks;
        $framework = get("framework", "");
        
        if (false !== $classnames
        &&  (array_key_exists($framework,  $__frameworks)) 
        &&  (array_key_exists($classnames, $__frameworks[$framework]["classes"])))
        {
            $classnames .= " ".$__frameworks[$framework]["classes"][$classnames];
        }
        
        if (false !== $tag
        &&  (array_key_exists($framework, $__frameworks)) 
        &&  (array_key_exists($tag,       $__frameworks[$framework]["classes"])))
        {
            $classnames .= " ".$__frameworks[$framework]["classes"][$tag];
        }
        
        return trim($classnames);
    }

    #endregion
    #region HELPERS : LOCALIZATION
    ######################################################################################################################################
    
    define("DOM_I18N_SHARE", T("Share"));
    
    function server_language()
    {
        return  at("" == get("server-language",  "")           ? false : explode(",", get("server-language")                ), 0,
                at("" == server_http_accept_language('en-US')  ? false : explode(",", server_http_accept_language('en-US')  ), 0,
                "en-US")
                );
    }

    function server_language_short()
    {
        return at("" == server_language() ? false : explode("-", server_language()), 0, "en");
    }

    function content_language()
    {
        return  get( "lang",
                at("" == get("content-language", "")    ? false : explode(",", get("content-language")  ), 0,
                at("" == get("html-language",    "")    ? false : explode(",", get("html-language")     ), 0,
                at("" == server_language()              ? false : explode(",", server_language()        ), 0,
                "en-US")
                )));
    }

    function content_language_short()
    {
        return at("" == content_language() ? false : explode("-", content_language()), 0, "en");
    }
    
    function T()
    {
        if (func_num_args() >= 3) 
        {
            list($label, $lang, $text) = func_get_args();
            $key = strtolower("i18n-$lang-$label");
            set($key, $text);
            debug_log("$key = $text");
            return "";
        }

        if (func_num_args() == 2)
        {
            if (is_array(at(func_get_args(), 1)))
            {
                list($label, $lang_texts) = func_get_args();
                foreach ($lang_texts as $lang => $text) T($label, $lang, $text);
                return "";
            }
            else
            {
                list($lang, $text) = func_get_args();
                debug_log("i18n-$lang / $text");
                return (strtolower(server_language_short()) == strtolower($lang)) ? $text : "";
            }
        }

        if (func_num_args() == 1)
        {
            if (is_array(at(func_get_args(), 0)))
            {
                list($lang_texts) = func_get_args();
                foreach ($lang_texts as $lang => $text) 
                    if (strtolower(server_language_short()) == strtolower($lang)) return $text;
                return at($lang_texts, 0, "");
            }
            else
            {
                list($label) = func_get_args();
                foreach (array(server_language_short(), "en") as $lang) {
                    $key = strtolower("i18n-$lang-$label");
                    if (has($key)) {
                        debug_log("i18n / $label -> $key -> ".get($key, ""));
                        return get($key, "");
                    }
                }
                return $label;
            }
        }

        return "";
    }
    
    #endregion
    #region HELPERS : MISC
    ######################################################################################################################################

    if (!function_exists('array_is_list')) {

        function array_is_list($arr)
        {
            if ($arr === array()) 
            {
                return true;
            }

            return array_keys($arr) === range(0, count($arr) - 1);
        }
    }

    function coalesce()
    {
        $args = func_get_args();
        return coalesce_FUNC_ARGS($args);
    }

    function coalesce_FUNC_ARGS($args, $fallback = false)
    {
        foreach ($args as $arg) if (!!$arg) return $arg;
        return $fallback;
    }

    function to_classname($str, $tolower = auto)
    {
        if ($tolower === auto) $tolower = true;

        // TODO Real implementation
        
        $str =  str_replace("é","e",
                str_replace("è","e",
                str_replace("à","a",$str)));
                
        return preg_replace('/\W+/','', $tolower ? strtolower(strip_tags($str)) : strip_tags($str));
    }

    function AMP()
    {
        return false !== get("amp", false) 
            && 0     !== get("amp", false) 
            && "0"   !== get("amp", false); 
    }

    function url_exists($url)
    {
        $headers = @get_headers($url);
        if (is_array($headers) && false !== stripos($headers[0], "200 OK")) return true;
        
        $headers = @get_headers("$url/");
        if (is_array($headers) && false !== stripos($headers[0], "200 OK")) return true;

        return false;
    }

    function clean_title($title)
    {
        return trim($title, "!?;.,: \t\n\r\0\x0B");
    }

    function content($urls, $options = 7, $auto_fix = true, $debug_error_output = true, $methods_order = [ "file_get_contents", "curl" ], $profiling_annotation = false)
    {
        $profiler = debug_track_timing(!!$profiling_annotation ? $profiling_annotation : $urls);

        if (is_array($urls))
        {
            foreach ($urls as $url)
            {
                $content = content($url, $options, $auto_fix, false);
                
                if (false !== $content)
                {
                    return $content;
                }
            }
            
            return false;
        }

        $url     = $urls;
        $timeout = is_array($options) ? at($options, "timeout", 7 ) : $options;
        $header  = is_array($options) ? at($options, "header",  []) : [];
        
        $token          = at($header, "Authorization",   at($options, "Authorization",   at($header, "token",        at($options, "token"               ))));
        $content_type   = at($header, "Content-Type",    at($options, "Content-Type",    at($header, "content-type", at($options, "content-type"        ))));
        $charset        = at($header, "Charset",         at($options, "Charset",         at($header, "charset",      at($options, "charset",    "utf-8" ))));
        $language       = at($header, "Accept-language", at($options, "Accept-language", at($header, "language",     at($options, "language"            ))));
        $client_id      = at($header, "Client-ID",       at($options, "Client-ID",       at($header, "client-id",    at($options, "client-id"           ))));

        if (!!$token)        $header["Authorization"]   = "Bearer $token";
        if (!!$content_type) $header["Content-Type"]    = $content_type.(!$charset ? "" : "; charset=$charset");
        if (!!$language)     $header["Accept-language"] = $language;
        if (!!$client_id)    $header["Client-ID"]       = $client_id;

        if (0 == count($header)) $header = false;

        $content           = false;
        $curl_debug_errors = array();

        foreach ($methods_order as $method)
        {        
            if ($method == "file_get_contents" && (!$content || $content == ""))
            {      
                if (!!$header)      
                {
                    if (false === stripos($url, "?")) $url .= "?";
                    if (!!$client_id) $url .= "&client_id=$client_id"; // TODO Remove hardcoded key
                    if (!!$token) $url     .= "&access_token=$token";  // TODO Remove hardcoded key

                    $steam_header  = implode("\r\n", array_map(function($key, $val) { return "$key: $val"; }, array_keys($header), array_values($header)));
                    $steam_options = array('http' => array('method' => "GET", 'header' => $steam_header));    
                    $steam_context = @stream_context_create($steam_options);    

                    $content = @file_get_contents($url, FILE_USE_INCLUDE_PATH, $steam_context);
                }
                else
                {
                    $content = @file_get_contents($url);
                }

                update_dependency_graph($url);
            }

            if ($method == "curl" && (!$content || $content == ""))
            {   
                $curl = @curl_init();
                
                if (false !== $curl)
                {
                    if (!!$header)
                    {
                        $curl_header = array_map(function($key, $val) { return "$key: $val"; }, array_keys($header), array_values($header));

                        curl_setopt($curl, CURLOPT_HTTPHEADER, $curl_header);
                    }

                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,  false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,  false);                
                    curl_setopt($curl, CURLOPT_USERAGENT,       'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:77.0) Gecko/20100101 Firefox/77.0');
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER,  true);
                    curl_setopt($curl, CURLOPT_URL,             $url);
                    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,  $timeout);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION,  true);
                    
                    $content = curl_exec($curl);

                    update_dependency_graph($url);

                    if (!!$debug_error_output && !!get("debug") && (!$content || $content == ""))
                    {
                        $curl_debug_errors[] = "CURL ERROR: ".curl_error($curl).(!$content ? " - false result" : " - Empty result");
                        $curl_debug_errors[] = to_string(curl_getinfo($curl));
                    }

                    curl_close($curl);
                }
            }
        }

        if ($auto_fix)
        {    
            if (!$content || $content == "") $content = content(url()."/".$url,  $options, false, false);
            if (!$content || $content == "") $content = content(url().    $url,  $options, false, false);
          //if (!$content || $content == "") $content = content(path( "/".$url), $options, false, false);
          //if (!$content || $content == "") $content = content(path(     $url), $options, false, false);
        }

        if (!!$debug_error_output && !!get("debug") && !$content)
        {
            debug_log("COULD NOT PARSE $url");
            foreach ($curl_debug_errors as $curl_debug_error) debug_log($curl_debug_error);
        }

        return $content;
    }

    function post($api, $url, $params = array(), $header = array(), $method = "GET", $usr = false, $pwd = false, $user_agent = "DOM", &$code = null, &$error = null)
    {
        $curl_user_agent = $user_agent;
    
        $header = array_merge(array(
            
            "Content-Type"      => "application/json",
            "User-Agent"        => $curl_user_agent
        
        ), $header);
    
        $url_params = "";
        {
            if ($method == "GET" && count($params) > 0)
            {
                $url_params = "/?".http_build_query($params, "", null, PHP_QUERY_RFC3986);
                $url_params = "/?".implode("&", array_map(function ($key, $val) { return "$key=$val"; }, array_keys($params), array_values($params)));
            }
        }
    
        $curl_url           = "$api/$url".$url_params;
        $curl_http_header   = array_map(function ($key, $val) { return "$key: $val"; }, array_keys($header), array_values($header));
    
        $curl_options = array();
        {
            $curl_options[CURLOPT_URL            ] = $curl_url;
            $curl_options[CURLOPT_RETURNTRANSFER ] = true;
            $curl_options[CURLOPT_ENCODING       ] = '';
            $curl_options[CURLOPT_MAXREDIRS      ] = 10;
            $curl_options[CURLOPT_TIMEOUT        ] = 0;
            $curl_options[CURLOPT_FOLLOWLOCATION ] = true;
            $curl_options[CURLOPT_HTTP_VERSION   ] = CURL_HTTP_VERSION_1_1;
            $curl_options[CURLOPT_CUSTOMREQUEST  ] = $method;
            $curl_options[CURLOPT_HTTPHEADER     ] = $curl_http_header;
            $curl_options[CURLOPT_SSL_VERIFYPEER ] = 0;
            $curl_options[CURLOPT_SSL_VERIFYHOST ] = 0;
            $curl_options[CURLOPT_USERAGENT      ] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:77.0) Gecko/20100101 Firefox/77.0'/*$curl_user_agent*/;
            
            if ($usr != false && $pwd != false) {
                $curl_options[CURLOPT_USERPWD] = "$usr:$pwd";
            }
            
            if ($method != "GET")
                $curl_options[CURLOPT_POSTFIELDS] = json_encode($params);
        }
    
        $curl       =           curl_init();
        $result_opt =           curl_setopt_array($curl, $curl_options);
        $response   =           curl_exec($curl);
        $code       = (string)  curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error      =           curl_error($curl);

        update_dependency_graph($curl_url);
    
        return $response;
    }

    function array_open_url($urls, $content_type = 'json', $options = 7)
    {
        $content = content($urls, $options);

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

    #endregion
    #region WIP API : UTILITIES : VARIABLES

    function swap(&$x, &$y) { $tmp = $x; $x = $y; $y = $tmp; }

    #endregion
    #region WIP API : UTILITIES : STRINGS MANIPULATION

    function dup($html, $n)                 { $new = ""; for ($i = 0; $i < $n; ++$i) $new .= $html; return $new; }
    function eol($n = 1)                    { if ($n == 0) return ""; if (!!get("minify",false)) return '';  switch (strtoupper(substr(PHP_OS,0,3))) { case 'WIN': return dup("\r\n",$n); case 'DAR': return dup("\r",$n); } return dup("\n",$n); }
    function tab($n = 1)                    { if ($n == 0) return ""; if (!!get("minify",false)) return ' '; return dup(' ', 4*$n); }
    function pan($x, $w, $c = " ", $d = 1)  { if (!!get("minify",false)) return $x;  $x="$x"; while (mb_strlen($x, 'utf-8')<$w) $x=(($d<0)?$c:"").$x.(($d>0)?$c:""); return $x; }
    function precat()                       { $args = func_get_args(); return precat_FUNC_ARGS($args); }
    function precat_FUNC_ARGS($args)        { return wrap_each(array_reverse($args),''); }
    function cat()                          { $args = func_get_args(); return cat_FUNC_ARGS($args); }
    function cat_FUNC_ARGS($args)           { return wrap_each($args,''); }
    function quote($txt, $quote = false)    { return ($quote === false) ? ((false === strpos($txt, '"')) ? ('"'.$txt.'"') : ("'".$txt."'")) : ($quote.$txt.$quote); }
    
    function ellipsis($text = "", $footnote = true)
    {
        $ellipsis = "[…]";
        return " ".span($ellipsis, $text == "" ? false : [ "title" => $text ]).((!$footnote || $text == "") ? "" : (" ".a_footnote("$ellipsis $text $ellipsis")))." ";
    }

    #endregion
    #region Time & Durations utilities

    function age($yyyy, $mm, $dd)
    {
        if ($dd  >    31) swap($yyyy, $dd);
        if ($yyyy < 1000) $yyyy += 2000;

        if (is_integer($mm) && $mm < 10) $mm = "0$mm";
        if (is_integer($dd) && $dd < 10) $dd = "0$dd";

        return (int)date_diff(date_create("$yyyy-$mm-$dd"), date_create())->format('%y');
    }
    
    #endregion
    #region Random utilities

    $__dom_rand_is_seeded = false;

    function rand_seed($seed = auto)
    {
        if (auto === $seed)
        {
            $seed = null;

            if (!!get("rss_date_granularity_daily"))
            {
                $d0 = new \DateTime("1976-06-13");
                $d  = new \DateTime(date('Y-m-d'));

                $seed = $d0->diff($d)->d;
            }
        }

        debug_log("rand_seed: ".(!!$seed ? $seed : "AUTO"));
        mt_srand($seed);

        global $__dom_rand_is_seeded;
        $__dom_rand_is_seeded = true;
    }

    function rand($min = auto, $max = auto)
    {
        global $__dom_rand_is_seeded;

        if (!$__dom_rand_is_seeded)
        {
            rand_seed();            
            $__dom_rand_is_seeded = true;
        }

        if (auto === $min) $min = 0;
        if (auto === $max) $max = mt_getrandmax();

        return mt_rand($min, $max);
    }

    function rand_pick_ARGS($values, $fallback = null)
    {
        if (0 == count($values)) return $fallback;
        return $values[rand(0, count($values) - 1)];
    }

    function rand_pick()
    {        
        $args = func_get_args();
        if (count($args) === 1 && is_array($args[0])) return rand_pick_ARGS(array_values($args[0]));
        return rand_pick_ARGS(array_values($args));
    }

    #endregion
    #region String utilities

    if (!function_exists("mb_str_pad"))
    {
        function mb_str_pad( $input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
        {
            while (mb_strlen($input) < $pad_length) $input = ($pad_type == STR_PAD_RIGHT ? ($input.$pad_string) : ($pad_string.$input));
            return $input;
        }
    }

    function str_replace_all($from, $to, $str)
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
    
    function to_string($x)
    {
      //return is_array($x) ? print_r($x, true) : (string)$x;
        if (is_array($x))
        {
            $rows = "";
            $r = 0;
            foreach ($x as $k => $v) { $rows .= ($r > 0 ? PHP_EOL : '').(($k).': '.(to_string($v))); ++$r; }
            return ($rows);
        }
        else if (is_object($x))
        {
            return json_encode($x);
        }
        
        return (string)$x;
    }

    function to_html($x, $transform = "self", $k = "", $sibblings = array(), $wrapper = "table")
    {
        if (is_array($x))
        {
            if ($wrapper == "grid")
            {
                $rows = "";
                foreach ($x as $k => $v) $rows .= eol().
                    td($k,                                          array("style" => "display: block;", "class" => "key")).
                    td(to_html($v, $transform, $k, $x, $wrapper),   array("style" => "display: block;", "class" => "val"));
                
                return
                    table(
                        tag("tbody", 
                            tr(
                                $rows, 
                                array("style" => "display: grid; grid-template-columns: auto minmax(1px, 1fr); width: 100%;")
                                ),
                            array("style" => "display: block")
                            ),
                        array("style" => "display: block")
                        );
            }
            else /* table */
            {
                $rows = "";
                foreach ($x as $k => $v) $rows .= tr(td($k).td(to_html($v, $transform, $k, $x, $wrapper)));
                return table($rows);
            }
        }
        else if (is_object($x))
        {
            return json_encode($x);
        }

        if (!is_callable($transform)) $transform = "dom\\$transform";

        return $transform(
            (string)$x, 
            (string)$k, 
            $sibblings
            );
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

    #endregion
    #region Array / String utilities

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

                if (!is_callable($transform)) $transform = "dom\\$transform";

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
            
            if (is_callable($fn_url_search))
            {
                $url = call_user_func($fn_url_search, $hashtag, $fn_url_search_userdata);
            }
            else if (is_callable("dom\\$fn_url_search"))
            {
                $url = call_user_func("dom\\$fn_url_search", $hashtag, $fn_url_search_userdata);
            }
            else if (is_callable("url_".$fn_url_search."_search_by_tags"))
            {
                $url = call_user_func("url_".$fn_url_search."_search_by_tags", $hashtag, $fn_url_search_userdata);
            }
            else if (is_callable("dom\\url_".$fn_url_search."_search_by_tags"))
            {
                $url = call_user_func("dom\\url_".$fn_url_search."_search_by_tags", $hashtag, $fn_url_search_userdata);
            }
            
            $hashtag = a('#'.$hashtag, $url, "hashtag", external_link);
            
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

    function unindent($raw, &$indent = null)
    {
        $lines = explode(PHP_EOL, $raw);

        $min_indent = PHP_INT_MAX;

        foreach ($lines as $line)
        {
            $len = strlen($line);
            $indent = 0;

            for ($c = 0; $c < $len; ++$c)
            {
                if ($line[$c] == " " || $line[$c] == "\t") ++$indent;
                else break;
            }

            if ($indent == $len) $indent = $min_indent;

            if ($indent < $min_indent)
            {
                $min_indent = $indent;
            }
        }

        if ($min_indent > 0)
        {
            foreach ($lines as &$line)
            {
                $line = substr($line, $min_indent);
            }
        }

        if ($indent !== null && $min_indent > 0)
        {
            $indent = $min_indent;
        }

        return implode(PHP_EOL, $lines);
    }

    use Michelf\Markdown;
    use Michelf\SmartyPants;

    use League\CommonMark\Environment\Environment;
    use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
    use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
    use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
    use League\CommonMark\MarkdownConverter;
    use League\CommonMark\CommonMarkConverter;
    use League\CommonMark\GithubFlavoredMarkdownConverter;
    
    function markdown($text, $hard_wrap = false, $headline_level_offset = 0, $no_header = false, $anchor = false, $smartypants = false, $markdown = false, $commonmark = true)
    {
        if (!!get("gemini"))
        {
            // TODO

            $text = explode(PHP_EOL, $text);
            foreach ($text as $l => $line) if (0 === stripos($line, "  * ")) $text[$l] = "* ".substr($line, 4);
            $text = implode(PHP_EOL, $text);

            return $text;
        }

        $text = unindent($text);

        $html = "";
        
        if ($markdown)
        {   
          //$html = Markdown::defaultTransform($text);
            $parser = new Markdown;
          //$parser->hard_wrap = true;
            $html = $parser->transform($text);
        }

        if ($commonmark)
        {   
            try
            {
                $config = []; // Define your configuration, if needed
                
                $environment = new Environment($config); // Configure the Environment with all the CommonMark parsers/renderers
                $environment->addExtension(new CommonMarkCoreExtension());
                $environment->addExtension(new FrontMatterExtension()); // Add the extension

                //$converter = new GithubFlavoredMarkdownConverter($config);
                $converter = new MarkdownConverter($environment);

                $html = $converter->convert($text)->getContent();
            }
            catch (\Exception $e)
            {
            }
        }

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
                    $pos_end = min($pos_end, strlen($html)-1);
                }
            }
        }
    
        return $html;
    }

    #endregion
    #region WIP LOREM IPSUM

    function lorem_ipsum($nb_paragraphs = 5, $tag = "p", $flavor = "lorem")
    {
        $profiler = debug_track_timing();

        $html = "";

        if ($flavor == "cat" || $flavor == "kitty")
        {
            if ($nb_paragraphs >= 0.0) $html .= "Cat ipsum dolor sit amet, human is behind a closed door, emergency! ";
            if ($nb_paragraphs >= 0.1) $html .= "abandoned! meeooowwww!!!. Do doodoo in the litter-box, clickityclack on the piano. ";
            if ($nb_paragraphs >= 0.2) $html .= "Be frumpygrumpy chase ball of string. Relentlessly pursues moth spit up on light gray carpet. ";
            if ($nb_paragraphs >= 0.3) $html .= "Instead of adjacent linoleum and chew iPad power cord. ";
            if ($nb_paragraphs >= 0.4) $html .= "Stare at imaginary bug yet kitten is playing with dead mouse and destroy house in 5 seconds. ";
            if ($nb_paragraphs >= 0.5) $html .= "And have a lot of grump in yourself because you can't forget to be grumpy. ";
            if ($nb_paragraphs >= 0.6) $html .= "And not be like king grumpy cat. ";
            if ($nb_paragraphs >= 0.7) $html .= "Purr purr purr until owner pets why owner not pet me hiss scratch meow kitty pounce. ";
            if ($nb_paragraphs >= 0.8) $html .= "Trip, faceplant you didn't see that no you didn't definitely didn't lick, lick, lick. ";
            if ($nb_paragraphs >= 0.9) $html .= "And preen away the embarrassment poop on floor and watch human clean up walk on keyboard. ";

                                       $html  = tag($tag, $html);            
            if ($nb_paragraphs >= 2.0) $html .= tag($tag, "Plan steps for world domination run outside as soon as door open. Immediately regret falling into bathtub claw drapes. Prow?? ew dog you drink from the toilet, yum yum warm milk hotter pls, ouch too hot cat playing a fiddle in hey diddle diddle?, waffles but eat my own ears and destroy dog and ignore the squirrels, you'll never catch them anyway hiss and stare at nothing then run suddenly away. Spread kitty litter all over house love me! so jump up to edge of bath, fall in then scramble in a mad panic to get out or instead of drinking water from the cat bowl, make sure to steal water from the toilet chase laser. I rule on my back you rub my tummy i bite you hard scratch so owner bleeds where is it? i saw that bird i need to bring it home to mommy squirrel! sniff other cat's butt and hang jaw half open thereafter.");
            if ($nb_paragraphs >= 3.0) $html .= tag($tag, "Sitting in a box. Kitty ipsum dolor sit amet, shed everywhere shed everywhere stretching attack your ankles chase the red dot, hairball run catnip eat the grass sniff soft kitty warm kitty little ball of furr poop in a handbag look delicious and drink the soapy mopping up water then puke giant foamy fur-balls tickle my belly at your own peril i will pester for food when you're in the kitchen even if it's salad . Kitty. Scratch me there, elevator butt crash against wall but walk away like nothing happened purr purr purr until owner pets why owner not pet me hiss scratch meow. Sit in box grass smells good but asdflkjaertvlkjasntvkjn (sits on keyboard) drool. Chase dog then run away enslave the hooman so try to jump onto window and fall while scratching at wall.");
            if ($nb_paragraphs >= 4.0) $html .= tag($tag, "Cat gets stuck in tree firefighters try to get cat down firefighters get stuck in tree cat eats firefighters' slippers murder hooman toes chase mice, and really likes hummus. Poop in litter box, scratch the walls lick face hiss at owner, pee a lot, and meow repeatedly scratch at fence purrrrrr eat muffins and poutine until owner comes back so poop on floor and watch human clean up under the bed, yet bite off human's toes yet behind the couch. Curl up and sleep on the freshly laundered towels cat cat moo moo lick ears lick paws but sleep nap prance along on top of the garden fence, annoy the neighbor's dog and make it bark that box? i can fit in that box yet cat snacks stuff and things. Vommit food and eat it again groom yourself 4 hours - checked, have your beauty sleep 18 hours - checked...");
            if ($nb_paragraphs >= 5.0) $html .= tag($tag, "Cat jumps and falls onto the couch purrs and wakes up in a new dimension filled with kitty litter meow meow yummy there is a bunch of cats hanging around eating catnip catch mouse and gave it as a present chase imaginary bugs, or eat a rug and furry furry hairs everywhere oh no human coming lie on counter don't get off counter making bread on the bathrobe for hack, yet cough furball into food bowl then scratch owner for a new one. Avoid the new toy and just play with the box it came in. Sleep on keyboard eat my own ears. Meoooow leave hair everywhere, but bury the poop bury it deep or present belly, scratch hand when stroked. Pretend not to be evil cuddle no cuddle cuddle love scratch scratch, asdflkjaertvlkjasntvkjn (sits on keyboard) have my breakfast spaghetti yarn for hiss at vacuum cleaner, where is it?");
        }
        else
        {
            if ($nb_paragraphs >= 0.0) $html .= "Lorem ipsum dolor sit amet, consectetur adipiscing elit. ";
            if ($nb_paragraphs >= 0.1) $html .= "Quisque enim nibh, finibus ut sapien ac, congue sagittis erat. ";
            if ($nb_paragraphs >= 0.2) $html .= "Nulla gravida odio ac arcu maximus egestas ut ac massa. ";
            if ($nb_paragraphs >= 0.3) $html .= "Maecenas sagittis tincidunt pretium. Suspendisse dictum orci non nibh porttitor posuere. ";
            if ($nb_paragraphs >= 0.4) $html .= "Donec vehicula vulputate enim, vitae vulputate sapien auctor et. ";
            if ($nb_paragraphs >= 0.5) $html .= "Ut imperdiet non augue quis suscipit. Phasellus risus ipsum, varius vitae elit laoreet, convallis pharetra nisl. ";
            if ($nb_paragraphs >= 0.6) $html .= "Aliquam iaculis, neque quis sollicitudin volutpat, quam leo lobortis enim, consectetur volutpat sapien ipsum in mauris. ";
            if ($nb_paragraphs >= 0.7) $html .= "Maecenas rhoncus sit amet est quis tempus. ";
            if ($nb_paragraphs >= 0.8) $html .= "Duis nulla mauris, rhoncus eget vestibulum placerat, posuere in sem. ";
            if ($nb_paragraphs >= 0.9) $html .= "Nulla imperdiet suscipit felis, a blandit ante dictum a. ";

                                       $html  = tag($tag, $html);
            if ($nb_paragraphs >= 2.0) $html .= tag($tag, "Nunc lobortis dapibus justo, non eleifend arcu blandit ut. Fusce viverra massa purus, vel dignissim justo dictum quis. Maecenas interdum turpis in lacinia imperdiet. In vel dui leo. Curabitur vel iaculis leo. Sed efficitur libero sed massa porttitor tristique. Nam sit amet mi elit. Donec pellentesque sit amet tellus ut aliquam. Fusce consequat commodo dui, tempus fringilla diam fermentum eu. Etiam finibus felis egestas velit elementum, at bibendum lectus volutpat. Donec non odio varius, ornare felis mattis, fermentum dui.");
            if ($nb_paragraphs >= 3.0) $html .= tag($tag, "Phasellus ut consectetur justo. Nam eget libero augue. Praesent ut purus dignissim, imperdiet turpis sed, gravida metus. Praesent cursus fringilla justo et maximus. Donec ut porttitor tellus. Ut ac justo imperdiet, accumsan ligula et, facilisis ligula. Sed ac nulla at purus pretium tempor. Suspendisse nec iaculis lectus.");
            if ($nb_paragraphs >= 4.0) $html .= tag($tag, "Nulla varius dui luctus augue blandit, non commodo lectus pulvinar. Aenean lacinia dictum lorem nec molestie. Curabitur hendrerit, tellus quis lobortis pretium, odio felis convallis metus, sed pulvinar massa libero non sapien. Praesent aliquet posuere ex, vitae rutrum magna maximus id. Sed at eleifend libero. Cras maximus lacus eget sem hendrerit hendrerit. Nullam placerat ligula metus, eget elementum risus egestas non. Sed bibendum convallis nisl ac pretium. Sed ac magna mi. Aliquam sollicitudin quam augue, at tempus quam sagittis id. Aliquam convallis consectetur est non vulputate. Phasellus rutrum elit at neque aliquam aliquet. Phasellus tincidunt sem pharetra libero pellentesque fermentum. Donec tellus mauris, pulvinar consequat est vel, faucibus lacinia ante. Proin et posuere sem, nec luctus ligula.");
            if ($nb_paragraphs >= 5.0) $html .= tag($tag, "Ut volutpat ultrices massa id rhoncus. Vestibulum maximus non leo in dapibus. Phasellus pellentesque dolor id dui mollis, eget laoreet est pulvinar. Ut placerat, ex sit amet interdum lobortis, magna dolor volutpat ante, a feugiat tortor ante nec nulla. Pellentesque dictum, velit vitae tristique elementum, ex augue euismod arcu, in varius quam neque efficitur lorem. Fusce in purus nunc. Fusce sed dolor erat.");
        }

        return $html;
    }

    function lorem($nb_paragraphs = 5, $tag = "p") { return lorem_ipsum($nb_paragraphs, $tag); }

    #endregion
    #region WIP HELPERS : HOOKS & PAGINATION
    ######################################################################################################################################

    $__user_hooks = array();

    function add_hook($hook_id, $hook_callback, $hook_userdata)
    {
        global $__user_hooks;

        if (!array_key_exists($hook_id, $__user_hooks)) $__user_hooks[$hook_id] = array();
        $__user_hooks[$hook_id][] = array("id" => $hook_id, "callback" => $hook_callback, "userdata" => $hook_userdata);
    }

    function call_user_hook()
    {
        global $__user_hooks;

        $args = func_get_args();
        $hook_id = array_shift($args);
        
        if (!array_key_exists($hook_id, $__user_hooks)) return true;

        $hooks = $__user_hooks[$hook_id];
        
        foreach ($hooks as $hook)
        {
            $user_args = $args;
            $user_args[] = $hook["userdata"];

            $f = $hook["callback"];
            if (!is_callable($f)) $f = "dom\\$f";

            call_user_func_array($f, $user_args);
        }

        return true;
    }

    $__last_headline_level = false;

    function hook_headline($h, $title, $section = false)
    {
        global $__last_headline_level;
        $__last_headline_level = (int)$h;

        $f = get("hook_headline_filter");
        
        if (!!$f && is_callable($f))
        {
            $hook_result = $f($title, false === $section ? $title : $section);
            
            if (false === $hook_result)
            {
                return [ $title, $section ];
            }
            
            if (is_array($hook_result))
            {
                list($title, $section) = $hook_result;
                
            }
            else
            {
                $section = $hook_result;
            }
        }
                
        if ($h == 1)      $title            = hook_title($title);
        if ($h == 2) list($title, $section) = hook_section($title, $section);
        
        call_user_hook("headline", $title);

        return [ $h, $title, $section ];
    }

    function get_last_headline_level()
    {
        global $__last_headline_level;
        return $__last_headline_level;
    }

    function clean_from_tags($html)
    {   
        while (true)
        {
            $bgn =  stripos($html, ">"); if (false === $bgn) break;
            $end = strripos($html, "<"); if (false === $end) break; if ($end < $bgn) break;

            $html = substr($html, $bgn+1, $end-$bgn-1);
        }
        
        return strip_tags($html);
    }

    function hook_title($title)
    {
        if (!!$title && false === get("title", false))
        {
            $title = trim(clean_from_tags($title));
            set("title", $title);
        }        

        return $title;
    }

    function hook_section($title, $section = false)
    {   
        if (false === $section) $section = $title;

        $f = get("hook_section_filter");
        
        if (!!$f && is_callable($f))
        {
            $hook_result = $f(false === $section ? $title : $section);
            
            if (false === $hook_result)
            {
                return [ $title, $section ];
            }
            
            if (is_array($hook_result))
            {
                list($title, $section) = $hook_result;
                
            }
            else
            {
                $section = $hook_result;
            }
        }
        
        set(
            "hook_sections", 
            array_merge(
                get("hook_sections", array()), 
                array(
                    array(
                        trim(clean_from_tags($title)), 
                        "#".anchor_name(trim(clean_from_tags($section)))
                        )
                    )
                )
            );

        return [ $title, $section ];
    }
    
    function hook_heading($heading)
    {
        if (!!$heading)
        {
            $heading = trim(clean_from_tags($heading));

            if (false === get("heading", false))
            {
                set("heading", $heading);
            } 
        }        
    }
    
    // Images

    $hook_images         = array();
    $hook_image_preloads = array();
    
    function hook_img($src, $alt, $preload)
    {
        if ($src === false) return;

        global $hook_images;

        $found = false; foreach ($hook_images as $image) { if ($src == $image["src"]) { $found = true; break; } }

        if (!$found)
        {
            $hook_images[] = [ "src" => $src, "alt" => $alt ];
        }

        if ($preload)
        {
            global $hook_image_preloads;

            if (!in_array($src, $hook_image_preloads) && false === stripos($src, ".svg"))
            {
                $hook_image_preloads[] = $src;
            }
        }
    }

    function hooked_image_preload($src)
    {
        return link_rel_image_preload($src);
    }

    function link_rel_image_preloads() { return delayed_component("_".__FUNCTION__); }
    function _link_rel_image_preloads()
    {
        global $hook_image_preloads;
        return wrap_each($hook_image_preloads, "", "hooked_image_preload", false);
    }

    // Links
    
    $hook_links             = array();
    $hook_shortcut_links    = array();
    $hook_prefetch_links    = array();
    $hook_external_links    = array();
    
    function hook_link($title, $url, $target)
    {
        if (!is_string($url)) return;

        if (strlen($url) >= 1)
        {
            if ($url    == ".") return;
            if ($url[0] == "#") return;
            if ($url[0] == "?") return;

            if (0 === stripos($url, ".?")         ) return;
            if (0 === stripos($url, "javascript") ) return;
        }

        global $hook_links, $hook_shortcut_links, $hook_prefetch_links, $hook_external_links;

        $found_url = false;
        foreach ($hook_links as $link) { if ($link["url"] == $url) { $found_url = true; break; } }
        if ($found_url) return;

        $title = trim(strip_tags($title));
        if ($title == "" || !$title) $title = parse_url($url, PHP_URL_HOST);
        if ($title == "" || !$title) $title = trim($url, "/");

        if ($title == "" || !$title) return;

        if ($target == internal_link)
        {
            $hook_links[]           = array("title" => $title, "url" => $url);
            $hook_shortcut_links[]  = array("title" => $title, "url" => $url);
            $hook_prefetch_links[]  = array("title" => $title, "url" => $url);
        }

        if ($target == external_link)
        {
            $hook_links[]           = array("title" => $title, "url" => $url);
            $hook_external_links[]  = array("title" => $title, "url" => $url);
        }
    }

    function ul_page_external_links()
    {
        global $hook_external_links;
        return ul(implode("", array_map(function($link) { return li(a(str_replace("www.", "", at($link, "title")), at($link, "url"))); }, $hook_external_links)));
    }

    function hooked_link_rel_prefetch($link)
    {
        return link_rel_prefetch($link["url"]);
    }

    function link_rel_prefetchs() { return delayed_component("_".__FUNCTION__); }
    function _link_rel_prefetchs()
    {
        if (!!get("auto_prefetch"))
        {
            global $hook_prefetch_links;
            return wrap_each($hook_prefetch_links, "", "hooked_link_rel_prefetch", false);
        }
        
        return "";
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
    
    function hook_amp_js($js, $html)
    {
        hook_amp_require("script");
        global $hook_amp_scripts;
        $hook_amp_scripts[] = array($js, $html);
        return "";
    }

    function _amp_scripts_head()
    {
        global $hook_amp_scripts;
        
        if (count($hook_amp_scripts) > 0)
        {
            return eol().'<meta name="amp-script-src" content="'.delayed_component("_amp_sha384_hash_local_script", false, 1, 0).' " />';
        }

        return "";
    }

    function _amp_sha384_hash_local_script()
    {
        $keys = array();
        
        global $hook_amp_scripts;
        
        foreach ($hook_amp_scripts as $js_html) 
        {
            list($js, $html) = $js_html;

            $keys[] = hash("sha384", $js);
        }

        return implode(" ", $keys);
    }

    function _amp_scripts_body()
    {
        $html_script = "";

        global $hook_amp_scripts;

        foreach ($hook_amp_scripts as $js_html)
        {
            list($js, $html) = $js_html;

            $uuid = md5($js);

            $html_script .= eol().
                '<amp-script script="amp_scripts_'.$uuid.'" layout="container">'.$html.'</amp-script>'.
                '<script type="text/plain" target="amp-script" id="amp_scripts_'.$uuid.'">'.
                $js.
                '</script>';
        }

        return $html_script;
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

        return placeholder("AMP_CSS_".(count($hook_amp_css)-1)); // They are aggregated
    }

    function placeholder_replace_amp_css($html)
    {
        global $hook_amp_css;
        foreach ($hook_amp_css as $i => $_) $html = placeholder_replace("AMP_CSS_$i", "", $html);
        return $html;
    }

    function _amp_css($_, $html)
    {
        global $hook_amp_css;
        
        $ordered_css = array();
        foreach ($hook_amp_css as $i => $css) $ordered_css[stripos($html, placeholder("AMP_CSS_$i"))] = $css;
        ksort($ordered_css);
        
        $aggregated_css = "";
        foreach ($ordered_css as $css) $aggregated_css .= eol().css_postprocess($css);

        return $aggregated_css;
    }

    // AMP Requirements

    function hook_amp_require($component)    {    if (AMP())     set("hook_amp_require_$component", true); return ""; }
    function has_amp_requirement($component) { return AMP() && !!get("hook_amp_require_$component");       }
    
    function rss_record_item($title = "", $text = "", $img = "", $url = "", $date = false, $timestamp = false)
    {
        $timestamp = !!$timestamp ? $timestamp : strtotime(!!$date ? $date : (!!get("rss_date_granularity_daily") ? date("D, d M Y 00:00:00", time()) : date(DATE_RSS, time())));
        
        set("rss_items", array_merge(get("rss_items", array()), array(array
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
        if (has("rss"))
        {
            global $hook_feed_nth_item;
            
            if (!has("id") || get("id") == $hook_feed_nth_item)
            {
                if ((at($metadata,"post_title") !== false && at($metadata,"post_title") != "")
                ||  (at($metadata,"post_text")  !== false && at($metadata,"post_text")  != ""))
                {
                    rss_record_item(

                        at($metadata, "post_title",     ""),
                        at($metadata, "post_text",      ""),
                        at($metadata, "post_img_url",   ""),
                        at($metadata, "post_url",       ""),
                        at($metadata, "post_date",      false),
                        at($metadata, "post_timestamp", false)

                        );
                }
            }
            
            ++$hook_feed_nth_item;
        }
        
        return "";
    }
    
    function hook($type, $metadata)
    {
        $source = at($metadata, "TYPE", false);
        
        if ($source != false)
        {        
            if ($type != "thumb")
            {
                hook_feed_item($metadata);
            }
        
            set($source . "_" . $type, (has($source . "_" . $type) ? (get($source . "_" . $type) . "§") : "") . clean_title(at($metadata, "post_title"))); 
        }
    }
    
    #endregion
    #region WIP HELPERS : PAGINATION

    $__next_post_index = 0;
    
    function pagination_add($metadata)
    {
        hook("post", $metadata);

        global $__next_post_index;
             ++$__next_post_index;
    }

    function pagination_is_within()
    {
        if (false === get("page",false)) return true;

        $n = (int)get("n",   10);
        $p = (int)get("page", 1);

        $min = ($p-1) * $n;
        $max =  $p    * $n;

        global $__next_post_index;
        return ($min <= $__next_post_index) && ($__next_post_index < $max);
    }

    #endregion
    #region WIP HELPERS : XML DOM PARSER

    function doc_load_from_html($html)
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        return doc_load_from_html_parse($dom->documentElement);
    }

    function doc_find_classes($dom, $classname, $tag = false)
    {
        $results = array();
        if (is_array($dom)) { $nodes = array($dom); while (count($nodes) > 0) { $node = array_shift($nodes); if (!is_array($node)) continue; if ($node["class"] == $classname && (false === $tag || $node["tag"] == $tag)) { $results[] = $node; } $nodes = array_merge($node["children"], $nodes); } }
        return $results;
    }

    function doc_find_tags($dom, $tags)
    {
        if (!is_array($tags)) $tags = array($tags);
        $results = array();
        if (is_array($dom)) { $nodes = array($dom); while (count($nodes) > 0) { $node = array_shift($nodes); if (!is_array($node)) continue; if (in_array($node["tag"], $tags)) {  $results[] = $node; } $nodes = array_merge($node["children"], $nodes); } }
        return $results;
    }

    function doc_find_class($dom, $classnames, $tag = false)
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

    function doc_find_tag($dom, $tag)
    {
        if (is_array($dom)) { $nodes = array($dom); while (count($nodes) > 0) { $node = array_shift($nodes); if (!is_array($node)) continue; if ($node["tag"] == $tag) { return $node; } $nodes = array_merge($node["children"], $nodes); } }
        return false;
    }

    function doc_remove_classes($dom, $classname)
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
                $children[] = doc_remove_classes($node, $classname);
            }
        }

        $dom["children"] = $children;
        
        return $dom;
    }

    function doc_remove_tags($dom, $tag)
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
                $children[] = doc_remove_tags($node, $tag);
            }
        }

        $dom["children"] = $children;
        
        return $dom;
    }

    function doc_attributes($dom)
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

    function doc_children($dom)             { if (!is_array($dom)) return array(); return $dom["children"]; }
    function doc_tag($dom)                  { if (!is_array($dom)) return "";      return $dom["tag"];      }
    function doc_class($dom)                { if (!is_array($dom)) return "";      return $dom["class"];    }
    function doc_attribute($dom,$attribute) { if (!is_array($dom) || !array_key_exists($attribute, $dom)) return false; return $dom[$attribute]; }

    function doc_inner_html($dom, $excluded_tags = false, $exclude_attributes = true, $hook = false, $hook_userdata = false, $depth = 0)
    {
        if (!is_array($dom)) return $dom;

        if (false === $excluded_tags)  $excluded_tags = array();
        if (!is_array($excluded_tags)) $excluded_tags = array($excluded_tags);
        if (in_array($dom["tag"], $excluded_tags)) return "";

        $hooked = false;
        if ($depth > 0 && is_callable($hook)) $hooked = $hook($dom, $hook_userdata);
        if (false !== $hooked) return $hooked;

        $html = "";
        {
            if ($depth > 0 && "" != $dom["tag"])  $html .= "<".$dom["tag"]; if ($depth > 0 && !$exclude_attributes) foreach (array_keys(doc_attributes($dom)) as $key) 
            if ($depth > 0 && "" != $dom[$key])   $html .= " $key=\"".$dom[$key]."\"";
            if ($depth > 0 && "" != $dom["tag"])  $html .= ">"; foreach ($dom["children"] as $node)
                                                  $html .= doc_inner_html($node, $excluded_tags, $exclude_attributes, $hook, $hook_userdata, $depth + 1);
            if ($depth > 0 && "" != $dom["tag"])  $html .= "</".$dom["tag"].">";
        }

        return $html;
    }
    
    function doc_load_from_html_parse($element)
    {
        $index = -1;
        return doc_load_from_html_parse_ex($element, 0, $index);
    }
    
    function doc_load_from_html_parse_ex($element, $depth, &$index)
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
                        $obj["children"][] = doc_load_from_html_parse_ex($subElement, $depth + 1, $index);
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
        $profiler = debug_track_timing($pin);
        
        if ($token === false && !defined("TOKEN_PINTEREST")) return array();
        
        $token      = ($token === false) ? constant("TOKEN_PINTEREST") : $token;
        $fields     = array("id","link","note","url","image","media","metadata","attribution","board","color","original_link","counts","creator","created_at");
        $end_point  = "https://api.pinterest.com/v1/pins/".$pin."/?access_token=".$token."&fields=".implode('%2C', $fields); // EXTERNAL ACCESS
        
        return array_open_url($end_point, "json");
    }

    function json_pinterest_posts($username = false, $board = false, $token = false)
    {
        $profiler = debug_track_timing($username.": ".$board);
        
        if ($token    === false && !defined("TOKEN_PINTEREST")) return array();
        if ($username === false && !has("pinterest_user"))  return array();
        if ($board    === false && !has("pinterest_board")) return array();
        
        $token      = ($token    === false) ? constant("TOKEN_PINTEREST")   : $token;
        $username   = ($username === false) ? get("pinterest_user")         : $username;
        $board      = ($board    === false) ? get("pinterest_board")        : $board;
        $end_point  = "https://api.pinterest.com/v1/boards/".$username."/".$board."/pins/?access_token=".$token; // EXTERNAL ACCESS
        
        $result = array_open_url($end_point, "json");

        if (at($result, "status") == "failure")
        {
            return array();
        }
        
        return $result;
    }
    
    function json_tumblr_blog($blogname = false, $method = "info", $token = false)
    {
        $profiler = debug_track_timing($blogname);
        
        if ($token    === false && !defined("TOKEN_TUMBLR")) return array();
        if ($blogname === false && !has("tumblr_blog"))  return array();
        
        $blogname   = ($blogname === false) ? get("tumblr_blog")        : $blogname;
        $token      = ($token    === false) ? constant("TOKEN_TUMBLR")  : $token;    
        $end_point  = "https://api.tumblr.com/v2/blog/$blogname.tumblr.com/$method?api_key=$token"; // EXTERNAL ACCESS
        
        return array_open_url($end_point, "json");
    }

    function endpoint_facebook($username = false, $fields_page = false, $fields_post = false, $fields_attachements = false, $token = false)
    {                   
        $profiler = debug_track_timing($username);

        if ($token    === false && !defined("TOKEN_FACEBOOK")) return false;
        if ($username === false && !has("facebook_page"))  return false;

        $username               = ($username            === false) ? get("facebook_page") : $username;
        $fields_page            = ($fields_page         === false) ? array("id","name","about","mission","hometown","website","cover","picture","birthday"/*,"email","first_name","gender","last_name","quotes"*/) : ((!is_array($fields_page)) ? array($fields_page) : $fields_page);
        $fields_attachements    = ($fields_attachements === false) ? array("media","url") : ((!is_array($fields_attachements)) ? array($fields_attachements) : $fields_attachements);
        $fields_post            = ($fields_post         === false) ? array("message","description","caption","full_picture","link","attachments%7B".implode('%2C', $fields_attachements)."%7D") : ((!is_array($fields_post)) ? array($fields_post) : $fields_post);
        $token                  = ($token               === false) ? constant("TOKEN_FACEBOOK") : $token;
        $end_point              = "https://graph.facebook.com/v2.10/".$username."?access_token=".$token."&fields=".implode('%2C', $fields_page); // EXTERNAL ACCESS
        $end_point             .= ($fields_post !== false) ? (",posts"."%7B".implode('%2C', $fields_post)."%7D") : "";

        return $end_point;
    }

    function json_facebook($username = false, $fields_page = false, $fields_post = false, $fields_attachements = false, $token = false)
    {
        $profiler = debug_track_timing($username);        
        $end_point = endpoint_facebook($username, $fields_page, $fields_post, $fields_attachements, $token);
        if ($end_point === false) return array();
        
        $result = array_open_url($end_point, "json");
        /*
        if ((false !== $username) && ((false === $result) || (at(at($result, "meta"),  "code", "") == "200") 
                                                          || (at(at($result, "error"), "code", "") ==  200 ) 
                                                          || (at(at($result, "error"), "code", "") ==   10 )))
        {
            $result = array("data" => array());
        
        //  $json_articles_page = json_facebook_from_content("https://www.facebook.com/pg/".get("facebook_page")."/posts/?ref=page_internal");
        //  $json_articles_page = at($json_articles_page, "require", array());
            $json_articles_page = json_facebook_from_content("https://www.facebook.com/".get("facebook_page"));

            return array_merge(array("DEBUG" => "TEST"), is_array($json_articles_page) ? $json_articles_page : array($json_articles_page));
            
            foreach ($json_articles_page as $entry)
            {
                $ownerName = at($entry, array(3, 1, "ownerName"), false);
                if (!$ownerName) continue;
                
                $permalink = at($entry, array(3, 1, "permalink"), false);
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
        $profiler = debug_track_timing($username.": ".$post_id);
        
        if ($token    === false && !defined("TOKEN_FACEBOOK"))  return array();
        if ($username === false && !has("facebook_page"))   return array();
        
        $username               = ($username            === false) ? get("facebook_page")  : $username;
        $fields_attachements    = ($fields_attachements === false) ? array("media","url") : ((!is_array($fields_attachements)) ? array($fields_attachements) : $fields_attachements);
        $fields_post            = ($fields_post         === false) ? array("message","description","caption","full_picture","link","attachments%7B".implode('%2C', $fields_attachements)."%7D","created_time","from") : ((!is_array($fields_post)) ? array($fields_post) : $fields_post);
        $token                  = ($token               === false) ? constant("TOKEN_FACEBOOK") : $token;
        $end_point              = "https://graph.facebook.com/v2.10/".$post_id."?access_token=".$token."&fields=".implode('%2C', $fields_post); // EXTERNAL ACCESS

        return array_open_url($end_point, "json");
    }
        
    function json_facebook_from_content($url)
    {/*
        $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36'));
        $context = stream_context_create($options);
        $html    = @file_get_contents($url, false, $context);
    */
        $html = array_open_url($url, "html");

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

                $result = doc_load_from_html($html);

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
                
                $result = doc_load_from_html($html);
                
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

        update_dependency_graph($url);

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

        update_dependency_graph($url);
            
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
        $profiler = debug_track_timing($page);
        
        if ($token  === false && !defined("TOKEN_FACEBOOK"))    return array();
        if ($page   === false && !has("facebook_page"))     return array();
        
        $token  = ($token   === false) ? constant("TOKEN_FACEBOOK") : $token;
        $page   = ($page    === false) ? get("facebook_page")       : $page;

        $end_points = array
        (
         /* "https://graph.facebook.com/"      .$page."/instant_articles?access_token=".$token // EXTERNAL ACCESS
        ,*/ "https://graph.facebook.com/v2.10/".$page."/instant_articles?access_token=".$token // EXTERNAL ACCESS
        );

        $result = array_open_url($end_points, "json");
        
        if ((false !== $page) && ((false === $result) || (at(at($result, "meta"),  "code", "") == "200") 
                                                      || (at(at($result, "error"), "code", "") ==  200 )))
        {
            $result = array("data" => array());
        
            $json_articles_page = json_facebook_articles_from_content("https://www.facebook.com/pg/".get("facebook_page")."/notes/?ref=page_internal");
            $json_articles_page = at($json_articles_page, "require");
            
            if (is_array($json_articles_page)) foreach ($json_articles_page as $entry)
            {
                $ownerName = at($entry, array(3, 1, "ownerName"), false);
                if (!$ownerName) continue;
                
                $permalink = at($entry, array(3, 1, "permalink"), false);
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
        $profiler = debug_track_timing($username.": ".$article);

        return json_facebook_article_from_content("https://www.facebook.com".$article);
    }
    
    function json_instagram_from_content($url)
    {
        $html = content($url);
            
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
        $profiler = debug_track_timing($username);

        if ($token    === false && !defined("TOKEN_INSTAGRAM")) return array();
        if ($username === false && !has("instagram_user"))  return array();
        
        $token      = ($token    === false) ? constant("TOKEN_INSTAGRAM")   : $token;
        $username   = ($username === false) ? get("instagram_user")         : $username;
        $tag        = ($tag      === false) ? get("instagram_tag")          : $tag;

        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);

        $end_points = array
        (
            "https://api.instagram.com/v1/users/" . "self"      . "/media/recent/?access_token=$token" // EXTERNAL ACCESS
        ,   "https://api.instagram.com/v1/users/" . $username   . "/media/recent/?access_token=$token" // EXTERNAL ACCESS
        ,   "https://api.instagram.com/v1/tags/"  . $tag        . "/media/recent/?access_token=$token" // EXTERNAL ACCESS
        );

        $result = array_open_url($end_points, "json");

        // DEBUG -->
        /*
        $result = array_merge($result, array("data" => array(array
        (
            "id"    => "666"
        ,   "user"  => array
            (
                "full_name"         => "John Doe"
            ,   "username"          => "Johnny"
            ,   "profile_picture"   => "https://www.example.com/image.jpg"
            )
        ,   "caption" => array
            (
                "text" => "Loremp ipsum est!"
            )
        ,   "created_time"  => date("d/m/Y")
        ,   "link"          => "https://www.example.com"
        ,   "images"        => array
            (
                "low_resolution" => array
                (
                    "url" => "https://www.example.com/image.jpg"
                )
            )

        ))));
        */
        // DEBUG -->

        $could_not_access_account = (false === $result || at(at($result, "meta"), "code", "") == "200");
        
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

                    if ($mode == "username_json")       $json_tag_page = array_open_url($page_url, "json");
                    if ($mode == "username_json_html")  $json_tag_page = json_instagram_from_content($page_url);
                    if ($mode == "tag_json")            $json_tag_page = array_open_url($page_url, "json");
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
                //      if (!!get("debug")) echo comment(to_string($json_tag_page));
                //  }

                    ++$nb_parsed_pages;

                    $edges  = false;
                    $paging = false;

                    if ($mode == "tag_html")            $edges  = at($json_tag_page, array("entry_data","TagPage",0,"graphql","hashtag",    "edge_hashtag_to_media",        "edges"));
                    if ($mode == "tag_html")            $paging = at($json_tag_page, array("entry_data","TagPage",0,"graphql","hashtag",    "edge_hashtag_to_media",        "page_info"));

                    if ($mode == "username_json_html")  $edges  = at($json_tag_page, array("entry_data","TagPage",0,"graphql","user",       "edge_owner_to_timeline_media", "edges"));
                    if ($mode == "username_json_html")  $paging = at($json_tag_page, array("entry_data","TagPage",0,"graphql","user",       "edge_owner_to_timeline_media", "page_info"));

                    if ($mode == "username_html")       $edges  = at($json_tag_page, array("entry_data","TagPage",0,"graphql","user",       "edge_owner_to_timeline_media", "edges"));
                    if ($mode == "username_html")       $paging = at($json_tag_page, array("entry_data","TagPage",0,"graphql","user",       "edge_owner_to_timeline_media", "page_info"));

                    if ($mode == "tag_json")            $edges  = at($json_tag_page, array(                         "graphql","hashtag",    "edge_hashtag_to_media",        "edges"));
                    if ($mode == "tag_json")            $paging = at($json_tag_page, array(                         "graphql","hashtag",    "edge_hashtag_to_media",        "page_info"));

                    if ($mode == "username_json")       $edges  = at($json_tag_page, array(                         "graphql","user",       "edge_owner_to_timeline_media", "edges"));
                    if ($mode == "username_json")       $paging = at($json_tag_page, array(                         "graphql","user",       "edge_owner_to_timeline_media", "page_info"));

                    if (!is_array($edges)) break;

                    $page_url = false;

                    if ($mode == "username_json")       $page_url = !!at($paging,"has_next_page") ? ("https://www.instagram.com/$username"          ."?__a=1&max_id=".at($paging,"end_cursor")) : false;
                    if ($mode == "username_json_html")  $page_url = !!at($paging,"has_next_page") ? ("https://www.instagram.com/$username"          ."?__a=1&max_id=".at($paging,"end_cursor")) : false;
                    if ($mode == "tag_json")            $page_url = !!at($paging,"has_next_page") ? ("https://www.instagram.com/explore/tags/$tag"  ."?__a=1&max_id=".at($paging,"end_cursor")) : false;
                    if ($mode == "tag_html")            $page_url = false;
                    if ($mode == "username_html")       $page_url = false;
                    
                    $result = array("data" => array());
                
                    foreach ($edges as $edge)
                    {
                        $node = at($edge,"node");
                    
                        $post_url = url_instagram_post(at($node, "shortcode"));
                    
                        $owner = at(json_instagram_from_content($post_url), array("entry_data","PostPage",0,"graphql","shortcode_media","owner"));
                        
                        $item = array
                        (
                            "id"    => at($node, "id")
                        ,   "user"  => array
                            (
                                "full_name"         => at($owner, "username")
                            ,   "username"          => at($owner, "username")
                            ,   "profile_picture"   => at($owner, "profile_pic_url")
                            )
                        ,   "caption" => array
                            (
                                "text" => ltrim(at($node, array("edge_media_to_caption","edges",0,"node","text")), "|| ")
                            )
                        ,   "created_time"  => at($node, "taken_at_timestamp")
                        ,   "link"          => $post_url
                        ,   "images"        => array
                            (
                                "low_resolution" => array
                                (
                                    "url" => at($node, "display_url")
                                )
                            )
                        );
            
                        $filtered  = at($item, "id")   == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
                        $excluded  = in_array(at($item,"id"), explode(',',get("exclude_instagram_codes", "")));
                        $excluded  = $excluded || in_array(at(at($item,"user"),"full_name"), explode(',',get("exclude_instagram_users", "")));
                        $item_tags = array_hashtags(get(get($item, "caption"), "text"));           
                        $tagged    = is_array_filtered($item_tags, $tags_in, $tags_out);

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

        $token      = ($token === false) ? constant("TOKEN_FLICKR") : $token;
        $method     = (0 === stripos($method, "flickr.")) ? $method : ("flickr.".$method);
        $end_point  = "https://api.flickr.com/services/rest/?method=".$method."&api_key=".$token."&format=json&nojsoncallback=1"; // EXTERNAL ACCESS

        if (!!$params) foreach ($params as $key => $val) $end_point .= "&".$key."=".urlencode($val);
        
        $json = array_open_url($end_point, "json");

        return $json;
    }
    
    function json_flickr_no_user_fallback($method, $params = array(), $user_id = false, $token = false)
    {
        $profiler = debug_track_timing($user_id);
        
        if ($token === false && !defined("TOKEN_FLICKR")) return array();

        if (false !== $user_id)
        {
            if (0 === stripos($user_id, "http"))
            {        
                $data       = __json_flickr("urls.lookupUser", array("url" => $user_id), $token);
                $user_id    = at(at($data,"user"),"id");
            }
            else if (false === stripos($user_id, "@N"))
            {
                $data       = __json_flickr("people.findByUsername", array("username" => $user_id), $token);
                $user_id    = at(at($data,"user"),"id");
            }
            
            $params = array_merge($params, array("user_id" => $user_id));
        }

        return __json_flickr($method, $params, $token);
    }
    
    function json_flickr($method, $params = array(), $user_id = false, $token = false)
    {
        $profiler = debug_track_timing($user_id);
        
        if ($user_id === false && !has("flickr_user"))  return array();
        $user_id = ($user_id === false) ? get("flickr_user") : $user_id;

        return json_flickr_no_user_fallback($method, $params, $user_id, $token);
    }
    
    // Social networks misc. utilities
    
    function facebook_post_longid($post_id, $page_id = false)
    {
        if (false === $page_id) $page_id = get("facebook_page_id");
        if (false === $post_id) $post_id = get("facebook_post_id", get("facebook_post_id_hero", get("facebook_post_hero")));
        
        if (false === $page_id || false === $post_id) return false;
        
        return $page_id . "_" . $post_id;
    }
    
    #endregion
    #region WIP HELPERS : JSON METADATA FROM SOCIAL NETWORKS 
    ######################################################################################################################################
    
    function sort_cmp_post_timestamp($a,$b)
    {
        return (int)at($a,"post_timestamp",0) < (int)at($b,"post_timestamp",0);
    }
    
    function array_socials_posts($sources = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        $profiler = debug_track_timing();
        
        $posts = array();
        
        $social_index = 0;
        
        if ($sources !== false && !is_array($sources)) $sources = array($sources);
        if ($sources === false)                        $sources = array();
        
        foreach ($sources as $source)
        {   
            $source        = explode(":", $source);
            $social_source = at($source, 0);
            $username      = at($source, 1);

            if (0 === stripos($username,"#")) { $tags_in = substr($username,1); $username = false; }

            // TODO handle the case of username that should contain multiple identifier (ex. pinterest)
            
            $f = "array_".$social_source."_posts";
            if (!is_callable($f)) $f = "dom\\$f";

            if (is_callable($f))
            {
                $source_posts = call_user_func($f, $username, $filter, $tags_in, $tags_out);

                if (is_array($source_posts))
                {
                    $posts = array_merge($posts, $source_posts);
                }
            }
            else if (!!get("debug"))
            {
                echo "UNDEFINED SOCIAL SOURCE: ".to_string($sources).to_string($filter);
            }
            
            ++$social_index;
        }
        
        usort($posts, "dom\sort_cmp_post_timestamp");
     
        return $posts;
    }
    
    function array_socials_thumbs($sources = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        $profiler = debug_track_timing();
        
        $posts = array();
        
        $social_index = 0;
        
        if ($sources !== false && !is_array($sources)) $sources = array($sources);
        if ($sources === false)                        $sources = array();
        
        foreach ($sources as $source)
        {   
            $source        = explode(":", $source);
            $social_source = at($source, 0);
            $username      = at($source, 1);

            if (0 === stripos($username,"#")) { $tags_in = substr($username,1); $username = false; }

            // TODO handle the case of username that should contain multiple identifier (ex. pinterest)
            
            $f = "array_".$social_source."_thumbs";
            if (!is_callable($f)) $f = "dom\\$f";

            if (is_callable($f))
            {
                $source_posts = call_user_func($f, $username, $filter, $tags_in, $tags_out);
                
                if (is_array($source_posts))
                {
                    $posts = array_merge($posts, $source_posts);
                }
            }
            else if (!!get("debug"))
            {
                echo "UNDEFINED SOCIAL SOURCE: ".to_string($sources).to_string($filter);
            }
            
            ++$social_index;
        }
        
        usort($posts, "dom\sort_cmp_post_timestamp");
        
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
        $profiler = debug_track_timing();
        
        $content = json_instagram_medias(($username === false) ? get("instagram_user") : $username, false, false, get("page") * get("n"), $post_filter, $tags_in, $tags_out);
        $posts   = array();

        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);

        foreach (at($content, "data",  array()) as $item)
        {
            if (!pagination_is_within()) continue;
            
            $filtered  = at($item, "id")   == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
            $excluded  = in_array(at($item,"id"), explode(',',get("exclude_instagram_codes", "")));
            $excluded  = $excluded || in_array(at(at($item,"user"),"full_name"), explode(',',get("exclude_instagram_users", "")));
            $item_tags = array_hashtags(get(get($item, "caption"), "text"));           
            $tagged    = is_array_filtered($item_tags, $tags_in, $tags_out);

            if (!$filtered || $excluded || !$tagged) continue;
            
            $images = at(at(at($item,"images"),"low_resolution"),"url");
            $images = at(at(at($item,"videos"),"low_resolution"),"url", $images);

            if (get("carousel") && array_key_exists("carousel_media", $item))
            {
                $sub_items = at($item, "carousel_media", array());
                
                if (count($sub_items) > 0)
                {
                    $images = array();
                                    
                    foreach ($sub_items as $sub_item)
                    {
                    //  $images[] = at(at(at($sub_item,"images"),"standard_resolution"), "url");
                        $images[] = at(at(at($sub_item,"images"),"low_resolution"),      "url");
                    }
                }
            }
            
            $exclude_facebook_text_md5s = explode(',',get("exclude_facebook_text_md5s", ""));
            $exclude_facebook_text_md5s[] = md5($item["caption"]["text"]);
            set("exclude_facebook_text_md5s", implode(',', $exclude_facebook_text_md5s));
            
            $title          = extract_start($item["caption"]["text"]);
            $post_message   = at(at($item,"caption"),"text");
            
            if (get("facebook_posts_no_duplicate_titles") && in_array(clean_title($title), explode('§', get("facebook_posts" )))) continue;
            if (get("facebook_posts_no_duplicate_titles") && in_array(clean_title($title), explode('§', get("instagram_posts")))) continue;
            
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
            ,   "DEBUG_SOURCE"      => ((!!get("debug")) ? $item : "")
            ,   "LAZY"              => true
            );
            
            if (!!$hooks) pagination_add($metadata);

            $posts[] = $metadata;
        }
        
        return $posts;
    }
    
    function array_instagram_post($username = false, $post_id = "", $tags_in = false, $tags_out = false)
    {
        $profiler = debug_track_timing();

    //  if ($post_id === "" || $post_id === false)
        {
            $hack = get("filter", "default");
            set("filter", "HACK");
            
            $posts = array_instagram_posts($username, $post_id, $tags_in, $tags_out, false, false);
            
            set("filter", $hack);
            
            if (count($posts) > 0)
            {
                return array_shift($posts);
            }
            
            return false;
        }
    /*
        $username   = ($username === false) ? get("instagram_user")  : $username;                
        $item       = json_instagram_post($post_id, $username);
        
        $post_title   = extract_start($item["caption"]["text"]);
        $post_message = at(at($item,"caption"),"text");
       
        $images = at(at(at($item,"images"),"low_resolution"),"url");
        $images = at(at(at($item,"videos"),"low_resolution"),"url", $images);

        if (get("carousel") && array_key_exists("carousel_media", $item))
        {
            $sub_items = at($item, "carousel_media", array());
            
            if (count($sub_items) > 0)
            {
                $images = array();
                                
                foreach ($sub_items as $sub_item)
                {
                //  $images[] = at(at(at($sub_item,"images"),"standard_resolution"), "url");
                    $images[] = at(at(at($sub_item,"images"),"low_resolution"),      "url");
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
        ,   "DEBUG_SOURCE"  => ((!!get("debug")) ? $item : "")
        ,   "LAZY"          => true
        );
        
        hook("post", $metadata);

        return $metadata;*/
    }
    
    function array_flickr_posts($username = false, $photo_key = false, $tags_in = false, $tags_out = false)
    {
        $profiler = debug_track_timing();
        
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
            // flickr.photos.search : Parameterless searches have been disabled. Please use flickr.photos.getRecent instead
            // $data   = json_flickr_no_user_fallback("photos.getRecent", array("tags" => $tags_in)); 
            
            $data   = json_flickr_no_user_fallback("photos.search", array("tags" => $tags_in));             
            $photos = at(at($data,"photos"),"photo");
        }
        else
        {        
            if (false !== $photoset_key)
            {
                $data           = json_flickr("photosets.getList", array(), $username);
                $photosets      = at(at($data,"photosets"),"photoset");
                $photoset       = false;
                $photoset_id    = false;
                $photoset_title = false;
                
                foreach ($photosets as $photoset_index => $photoset_nth)
                { 
                    $photoset       =               $photoset_nth;
                    $photoset_id    =        at($photoset_nth, "id");
                    $photoset_title = at(at($photoset_nth, "title"), "_content");

                    if (is_string($photoset_key)) { if ($photoset_title ==       $photoset_key) break; }
                    else                          { if ($photoset_index === (int)$photoset_key) break; }
                }
                
                $data           = json_flickr("photosets.getInfo", array("photoset_id" => $photoset_id), $username);
                $photoset_farm  = at(at($data,"photoset"),"farm");
                
                $data           = json_flickr("photosets.getPhotos", array("photoset_id" => $photoset_id, "media" => "photo"), $username);
                $photos         = at(at($data,"photoset"),"photo");
                $photo_farm     = $photoset_farm;
            }
            else
            {
                $data   = json_flickr("people.getPhotos", array(), $username); 
                $photos = at(at($data,"photos"),"photo");
            }
        }
        
        $posts = array();
        
        if (is_array($photos)) foreach ($photos as $photo_nth)
        { 
            if (!pagination_is_within()) continue;
            
            $photo          =        $photo_nth;
            $photo_id       = at($photo_nth, "id",      $photo_id);
            $photo_secret   = at($photo_nth, "secret",  $photo_secret);
            $photo_server   = at($photo_nth, "server",  $photo_server);
            $photo_farm     = at($photo_nth, "farm",    $photo_farm);
            $photo_title    = at($photo_nth, "title",   $photo_title);
            $photo_owner    = at($photo_nth, "owner",   $username);
            $photo_size     = "b";
            $photo_url      = "https://farm".$photo_farm.".staticflickr.com/".$photo_server."/".$photo_id."_".$photo_secret."_".$photo_size.".jpg";

            $data = json_flickr("photos.getInfo", array("photo_id" => $photo_id), $photo_owner);

            $photo_description = trim(at(at(at($data,"photo"),"description"), "_content", $photo_title), " ");
            $photo_description = (false === $photo_description || "" == $photo_description) ? $photo_title : $photo_description;
            $photo_timestamp   = at(at(at($data,"photo"),"dates"),"posted");
            $photo_page        = false;
            $photo_urls        = at(at(at($data,"photo"),"urls"),"url", array());
            
            foreach ($photo_urls as $url)
            {
                if (at($url,"type") == "photopage")
                {
                    $photo_page = at($url,"_content");
                    break;
                }
            }
            
            $filtered = (false !== stripos($photo_title, $photo_key)) || "" == "$photo_key" || false == "$photo_key";
            $excluded = in_array($photo_title, explode(',',get("exclude_flickr_codes", "")));
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
            ,   "DEBUG_SOURCE"      => ((!!get("debug")) ? $data : "")
            ,   "LAZY"              => true
            );
            
            pagination_add($metadata);

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

        $profiler = debug_track_timing();
          
        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);
    //  $content    = json_instagram_medias(($username === false) ? get("instagram_user") : $username, false, false, false,                          $post_filter, $tags_in, $tags_out);
        $content    = json_instagram_medias(($username === false) ? get("instagram_user") : $username, false, false, get("page") * get("n"), $post_filter, $tags_in, $tags_out);
                
        $thumbs     = array();

        foreach (at($content, "data",  array()) as $item)
        {
            $item_tags = array_hashtags(get(get($item, "caption"), "text"));
            
            $filtered = $item["id"]   == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
            $excluded = in_array(get($item,"id"),   explode(',',get("exclude_instagram_codes", "")));
            $tagged   = is_array_filtered($item_tags, $tags_in, $tags_out);
            
            if (!$filtered || $excluded || !$tagged) continue;
            
            $metadata = array
            (
                "TYPE"          => "instagram"
            ,   "post_url"      => at($item,"link")
            ,   "post_img_url"  => at(at(at($item,"images"),"thumbnail"),"url")
            ,   "DEBUG_SOURCE"  => ((!!get("debug")) ? array_merge($item, array("tags_in" => $tags_in), array("tags_out" => $tags_out), array("tags" => $item_tags)) : "")
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
        $profiler = debug_track_timing();
        
        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);
        $content    = json_tumblr_blog($blogname, "posts"); // if ($content["meta"]["msg"] != 'OK') return array();
        $posts      = array();
        
        foreach (at(at($content, "response"), "posts", array()) as $item)
        {   
            if (!pagination_is_within()) continue;
            
            $post_title = at($item, "title", extract_start(at($item, "summary", at(at(at($content, "response"), "blog"), "title"))));
            
            $filtered = $item["id"] == "$post" || "" == "$post" || false == "$post";
            $excluded = in_array(get($item,"slug"), explode(',',get("exclude_tumblr_slugs", "")));
            $tagged   = is_array_filtered(at($item, "tags", array()), $tags_in, $tags_out);            
            $indirect = ((false !== stripos(get($item, "link_url"),      "instagram.com")) 
                      || (false !== stripos(get($item, "permalink_url"), "instagram.com"))) && (has("instagram_posts") /*|| (get("filter", "default") == "default")*/);
                    
            $indirect = $indirect || in_array(clean_title($post_title), explode('§', get("facebook_posts")));
            $indirect = $indirect || in_array(clean_title($post_title), explode('§', get("instagram_posts")));

            if (!$filtered || $excluded || !$tagged || $indirect) continue;
            
            $post_source_url = (get("check_target_url", false)) ? ((false === array_open_url(get($item, "link_url"), "json")) ? get($item, "post_url") : get($item, "link_url")) : at($item, "link_url", at($item, "post_url"));
    
            if (at($item, "type") == "photo")
            {
                if (!!get("carousel"))
                {
                    $post_photo_captions = array();
                    $post_photo_imgs     = array();
                    
                    foreach (get($item, "photos", array()) as $photo)
                    {
                        $post_photo_captions [] =        at($photo, "caption");
                        $post_photo_imgs     [] = at(at($photo, "original_size", array()), "url");
                    }
                }
                else
                {
                    $post_photo_captions = "";
                    $post_photo_imgs     = false;
                    
                    foreach (get($item, "photos", array()) as $photo)
                    {
                        $post_photo_captions =        at($photo, "caption");
                        $post_photo_imgs     = at(at($photo, "original_size", array()), "url");
                        
                        break;
                    }
                }
                
                $metadata = array
                (
                    "TYPE"              => "tumblr"
                ,   "userdata"          => $blogname
                ,   "user_name"         => get($item, "blog_name")
                ,   "user_url"          => at(at(at($content, "response"), "blog"), "url")
                ,   "user_img_url"      => url_tumblr_avatar($blogname,64)
                ,   "post_title"        => $post_title
                ,   "post_text"         => at($item, "caption")
                ,   "post_timestamp"    => strtotime(get($item, "date"))
                ,   "post_url"          => $post_source_url
                ,   "post_img_url"      => $post_photo_imgs
                ,   "post_figcaption"   => $post_photo_captions
                ,   "DEBUG_SOURCE"      => ((!!get("debug")) ? $item : "")
                ,   "LAZY"              => true
                );
                
                pagination_add($metadata);  

                $posts[] = $metadata;
            }
            else if (at($item, "type") == "video")
            {
                $post_video = array();
                {
                    $item_videos = at($item, "player", array());                    
                    if (count($item_videos) > 0) $post_video = $item_videos[count($item_videos) - 1];
                }
                
                $metadata = array
                (
                    "TYPE"              => "tumblr"
                ,   "userdata"          => $blogname
                ,   "post_text"         => at($item, "caption")
                ,   "post_timestamp"    => strtotime(get($item, "date"))
                ,   "post_url"          => $post_source_url
                ,   "post_embed"        => at($post_video, "embed_code")
                ,   "post_figcaption"   => at($post_video, "caption")
                ,   "DEBUG_SOURCE"      => ((!!get("debug")) ? $item : "")
                );
                
                pagination_add($metadata);  

                $posts[] = $metadata;
            }
        }

        return $posts;        
    }
    
    
    function array_pinterest_posts($username_and_board, $pin_filter = "", $tags_in = false, $tags_out = false)
    {
        $profiler = debug_track_timing();
        
        if (!is_array($username_and_board)) $username_and_board = array($username_and_board, false);
        
        $username = $username_and_board[0];
        $board    = $username_and_board[1];
        
        $username   = ($username === false) ? get("pinterest_user")  : $username;
        $board      = ($board    === false) ? get("pinterest_board") : $board;
        $content    = json_pinterest_posts($username, $board);
        
        $pins = array();
        
        foreach (at($content, "data", array()) as $item)
        {
            if (!pagination_is_within()) continue;
            
            $pin_id     = $item["id"];
            $item       = json_pinterest_pin($pin_id);
            $item       = $item["data"];
            $item["id"] = $pin_id;

            $filtered = $item["id"] == "$pin_filter" || "" == "$pin_filter" || false == "$pin_filter";
            $excluded = in_array(get($item,"id"), explode(',',get("exclude_pinterest_pins_ids", "")));
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
            ,   "post_url"          =>($item["original_link"] != "" && false === stripos($item["original_link"], get("canonical")) && false === stripos($item["original_link"], str_replace("https://","",get("canonical")))) ? $item["original_link"] : url_pinterest_pin($item["id"])
            ,   "post_img_url"      => $item["image"]["original"]["url"]
            ,   "DEBUG_SOURCE"      => ((!!get("debug")) ? $item : "")
            ,   "LAZY"              => true
            );
            
            pagination_add($metadata);

            $pins[] = $metadata;
        }

        return $pins;
    }

    function array_tumblr_blog($blogname = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        $profiler = debug_track_timing();
        
        $blogname   = ($blogname === false) ? get("tumblr_blog") : $blogname;
        $content    = json_tumblr_blog($blogname, "info");
        $item       = at(at($content, "response"), "blog", array());
        
        $metadata = array
        (
            "TYPE"              => "tumblr"
        ,   "userdata"          => $blogname
        ,   "user_name"         => at($item, "name")
        ,   "user_url"          => at($item, "url")
        ,   "user_img_url"      => url_tumblr_avatar($blogname, 64)
        ,   "post_title"        => at($item, "title")
        ,   "post_text"         => at($item, "description")
        ,   "post_timestamp"    => at($item, "updated")
        ,   "post_url"          => at($item, "url")
        ,   "post_img_url"      => url_img_tumblr($blogname)
        ,   "DEBUG_SOURCE"      => ((!!get("debug")) ? $item : "")
        ,   "LAZY"              => true
        );

        return $metadata;
    }

    function instagram_posts_presence() { return has("instagram_posts") || (get("filter", "default") == "default"); }
    function facebook_posts_presence()  { return has("facebook_posts")  || (get("filter", "default") == "default"); }
    
    function array_facebook_posts($username = false, $post = "", $tags_in = false, $tags_out = false, $limit = false, $videos = true)
    {
        $profiler = debug_track_timing();

        $username   = ($username === false) ? get("facebook_page")  : $username;        
        $content    = json_facebook($username, array("id","name","about","mission","hometown","website","cover","picture"));
        $posts      = array();
        /*
        return array(array
        (
            "TYPE"              => "facebook"
        ,   "user_name"         => get("name")
        ,   "user_url"          => get("url")
        ,   "user_img_url"      => "image.jpg"
        ,   "post_title"        => get("title")
        ,   "post_text"         => get("description")
        ,   "post_timestamp"    => strtotime(date("Y/m/d", time()))
        ,   "post_url"          => get("url")
        ,   "post_img_url"      => "image.jpg"
        ,   "DEBUG_SOURCE"      => array("content" => $content)
        ,   "LAZY"              => true
        ));*/

        $articles   = array_facebook_articles(get("facebook_page"));
        
        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);

        $post_exclude_article_body = in_array("ARTICLE", $tags_out);
        if ($post_exclude_article_body) { unset($tags_out[array_search("ARTICLE",$tags_out)]); }
            
        foreach (at(at($content, "posts"), "data", array()) as $item_index => $item)
        {
            if (!pagination_is_within())                                                                                                    continue;
            if ($item["id"] != "$post" && "" != "$post" && false != "$post")                                                                    continue;
            if (in_array(    get($item,"id"),        explode(',',get("exclude_facebook_post_ids",  ""))))                               continue;
            if (in_array(md5(get($item, "message")), explode(',',get("exclude_facebook_text_md5s", ""))))                               continue;
            if ((false !== stripos(get($item, "caption"), "instagram.com"))                            && (instagram_posts_presence()))     continue;
            if ((false !== stripos(at(at(at($item,"attachments"),"data"),"url", 
                            at(at(at(at($item,"attachments"),"data"),0),"url")), "instagram.com")) && (instagram_posts_presence())) continue;

            $item_post          = json_facebook_post(at($item,"id"), $username);            
            $post_message       = at($item_post, "description", get($item, "message"));
            $post_title         = extract_start($post_message);
                
            $post_article       = false; foreach ($articles as $article) if ($article["post_title"] == $post_title) $post_article = $article;
            
            $post_article_tags  = ($post_article !== false) ? array("ARTICLE") : array();
            
            if (!is_array_filtered(array_merge($post_article_tags, array_hashtags($post_message)), $tags_in, $tags_out)) continue;
            
            if (0 === strpos($post_message, get("instagram_user")) && instagram_posts_presence()) continue;
            if (0 === strpos($post_message, get("instagram_user"))) $post_message = substr($post_message, strlen(get("instagram_user")));
            
        //  if (get("facebook_posts_no_duplicate_titles") && in_array(clean_title($post_title), explode('§', get("facebook_posts" )))) continue;
        //  if (get("facebook_posts_no_duplicate_titles") && in_array(clean_title($post_title), explode('§', get("instagram_posts")))) continue;
            
            $embedding_other_post             = (false !== strpos($post_message, "<iframe"));
            $post_img_url_page_cover_fallback = ($embedding_other_post) ? false : at(at($content,"cover"),"source");
            
            $post_img_url =                 at($item_post, "full_picture", 
                at(at(at(at(at($item_post, "attachments"), "data"),     "media", 
                       at(at(at(at($item_post, "attachments"), "data"), 0), "media")), "image"), "src", 
                               
                               $post_img_url_page_cover_fallback));

            $link = at($item_post, "link", false);
            
            if (false !== $link)
            {
                $video_id = rtrim($link, "/");
                $pos = strripos($video_id, "/");
                if (false !== $pos) $video_id = substr($video_id, $pos + 1);
                
                $video = json_facebook_post($video_id, $username, array("embed_html", "embeddable"), array());
                
                if (false !== $video)
                {
                    $embed_html = at($video,"embed_html");
                    
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
                    $post_message .= '<br><hr><div class="facebook_article_link"><a href="#'.md5($post_article["post_url"]).'">'.T("en", "Read article").'</a></div>';
                }
                else 
                {
                    $post_message = anchor(md5($post_article["post_url"])).$post_message.'<br><hr><div class="facebook_article">' . $post_article["post_text"] . "</div>";
                }
            }
            
            $metadata = array
            (
                "TYPE"              => "facebook"
            ,   "user_name"         => get(get($item_post, "from", array()), "name")
            ,   "user_url"          => url_facebook_page($content["id"])
            ,   "user_img_url"      => $content["picture"]["data"]["url"]
            ,   "post_title"        => $post_title
            ,   "post_text"         => $post_message
            ,   "post_timestamp"    => strtotime(get($item_post, "created_time"))
            ,   "post_url"          => at($item_post,"link",url_facebook_page(at($item_post,"id")))
            ,   "post_img_url"      => $post_img_url
            ,   "DEBUG_SOURCE"      => ((!!get("debug")) ? array("articles" => $articles, "post" => $item_post) : "")
            ,   "LAZY"              => true
            );
            
            pagination_add($metadata);
            
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
        $profiler = debug_track_timing();
           
        $username   = ($username === false) ? get("facebook_page")  : $username;        
        $content    = json_facebook_articles($username, false, get("page") * get("n"));
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
            
        foreach (at($content, "data", array()) as $item_index => $item)
        {
            if (!pagination_is_within())                                                                        continue;            
            if ($item["id"] != "$post" && "" != "$post" && false != "$post")                                        continue;            
            if (in_array(    get($item,"id"),        explode(',',get("exclude_facebook_article_ids", "")))) continue;
            
            $item_post = json_facebook_article(at($item,"id"), $username);      
            
            if (!is_array($item_post)) continue;
            
            $post_message = at($item_post, "body", "");
            $post_message = strip_tags(str_replace("<div","<p", str_replace("</div>","</p>", $post_message)), "<p><ul><li><h1><h2><h3>");
            
            if (!is_array_filtered(array_hashtags($post_message), $tags_in, $tags_out)) continue;
            
            $post_title = extract_start(at($item_post, "title", ""));

            $metadata = array
            (
                "TYPE"              => "facebook"
            ,   "post_title"        => $post_title
            ,   "post_text"         => $post_message
            ,   "post_url"          => "https://www.facebook.com".at($item,"id")
            ,   "DEBUG_SOURCE"      => ((!!get("debug")) ? array_merge($item, $item_post) : "")
            ,   "LAZY"              => true
            );
            
          //pagination_add($metadata);
            
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
        $profiler = debug_track_timing();
        
        $tags_in    = explode(',',$tags_in);
        $tags_out   = explode(',',$tags_out);
            
        $username   = ($username === false) ? get("facebook_page")  : $username;        
        $content    = json_facebook($username, array("id","name","about","mission","hometown","website","cover","picture"));
        $thumbs     = array();

        foreach (at(at($content, "posts"), "data", array()) as $item)
        {
            $filtered = $item["id"] == "$post_filter" || "" == "$post_filter" || false == "$post_filter";
            $excluded =              in_array(    get($item,"id"),        explode(',',get("exclude_facebook_post_ids",  "")));
            $excluded = $excluded || in_array(md5(get($item, "message")), explode(',',get("exclude_facebook_text_md5s", "")));
            $tagged   = is_array_filtered(array_hashtags(get($item, "message")), $tags_in, $tags_out);    
            $indirect = (false !== stripos(get($item, "caption"), "instagram.com")) && (instagram_posts_presence());
            
            if ((false !== stripos(at(at(at($item,"attachments"),"data"),"url", 
                            at(at(at(at($item,"attachments"),"data"),0),"url")), "instagram.com")) && (instagram_posts_presence())) continue;
               
            if (!$filtered || $excluded || !$tagged || $indirect) continue;
            
            $item_post    = json_facebook_post(at($item,"id"), $username);                   
            $post_message = at($item_post, "message", get($item, "description"));
            
            if (0 === strpos($post_message, get("instagram_user")) && instagram_posts_presence()) continue;
            
            $post_title = extract_start($post_message);
            
            if (in_array(clean_title($post_title), explode('§', get("facebook_posts")))) continue;
            
            $metadata = array
            (
                "TYPE"          => "facebook"
            ,   "post_url"      => at($item_post,"link",url_facebook_page($item_post["id"]))
            ,   "post_img_url"  => at($item_post,"full_picture",at(at(at(at(at($item_post,"attachments"),"data"),"media", at(at(at(at($item_post,"attachments"),"data"),0),"media")),"image"),"src", at(at($content,"cover"),"source")))
            ,   "DEBUG_SOURCE"  => ((!!get("debug")) ? $item_post : "")
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
        $profiler = debug_track_timing();
        
        $username   = ($username === false) ? get("facebook_page")  : $username;
        $item       = json_facebook($username, array("id","name","about","mission","birthday","hometown","website","cover","picture"));
        
        $metadata = array
        (
            "TYPE"              => "facebook"
        ,   "user_name"         => at($item, "name")
        ,   "user_url"          => url_facebook_page($username)
        ,   "user_img_url"      => false
        ,   "post_title"        => extract_start(at($item,"mission"))
        ,   "post_text"         => p(at($item,"mission")).p(at($item,"about"))
        ,   "post_timestamp"    => strtotime(at($item,"birthday"))
        ,   "post_url"          => url_facebook_page_about(at($item,"id"))
        ,   "post_img_url"      => at(at($item,"cover"),"source")
        ,   "DEBUG_SOURCE"      => ((!!get("debug")) ? $item : "")
        ,   "LAZY"              => true
        );

        return $metadata;
    }
    
    function array_facebook_post($username = false, $post_id = "", $tags_in = false, $tags_out = false)
    {
        $profiler = debug_track_timing();

        if ($post_id === "" || $post_id === false)
        {
            $hack = get("filter", "default");
            set("filter", "HACK");
            
            $posts = array_facebook_posts($username, $post_id, $tags_in, $tags_out, false, false);
            
            set("filter", $hack);
            
            if (count($posts) > 0)
            {
                return array_shift($posts);
            }
            
            return false;
        }

        $username   = ($username === false) ? get("facebook_page")  : $username;                
        $item       = json_facebook_post($post_id, $username);
        
        $post_title   = extract_start(get($item, "message"));
        $post_message = get($item, "message");
        
        if (0 === strpos($post_title,   get("instagram_user"))) $post_title   = substr($post_title,   strlen(get("instagram_user")));
        if (0 === strpos($post_message, get("instagram_user"))) $post_message = substr($post_message, strlen(get("instagram_user")));
           
        $metadata = array
        (
            "TYPE"          => "facebook"
        ,   "post_title"    => $post_title
        ,   "post_text"     => $post_message
        ,   "post_url"      => at($item,"link",url_facebook_page(at($item,"id")))
        ,   "post_img_url"  => at($item,"full_picture",at(at(at(at(at($item,"attachments"),"data"),"media", at(at(at(at($item,"attachments"),"data"),0),"media")),"image"),"src"))
        ,   "DEBUG_SOURCE"  => ((!!get("debug")) ? $item : "")
        ,   "LAZY"          => true
        );
        
        hook("post", $metadata);

        return $metadata;
    }

    function _array_rss_posts($type, $url, $post_img_url_fallback)
    {
        $posts = array();
        
        foreach (at(array_open_url($url, "xml"), array("channel","item"), array()) as $item)
        {   
            $cats = at($item, "category", array());
            if (!is_array($cats)) $cats = [ $cats ];

            $metadata = array
            (
                "TYPE"              => $type
            ,   "post_title"        => extract_start(at($item, "title"))
            ,   "post_url"          => at($item, "link")
            ,   "post_date"         => at($item, "pubDate")
            ,   "post_text"         => p(at($item, "description", at($item, "title"))).p(implode(" ", array_map(function($cat) { return "#$cat"; }, $cats)))
            ,   "post_img_url"      => $post_img_url_fallback
            );

            $html = at($item, "description", false);
            
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
                }/*
                else
                {
                    $metadata["post_text"] = $html;
                }*/
            }

            //die("<pre>".print_r($metadata, true));
            
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
        
        return _array_rss_posts("googlenews", $feeds[$feed], "https://upload.wikimedia.org/wikipedia/commons/0/0b/Google_News_icon.png");
    }

    function array_numerama_posts($id = false, $filter = "", $tags_in = false, $tags_out = false)
    {
        return _array_rss_posts("numerama", "https://www.numerama.com/feed/rss/", "https://www.numerama.com/wp-content/uploads/2023/02/numerama.jpg");
    }

    // Get array of cards

    function array_imgs_from_metadata  ($metadatas, $attributes = false) { if (!is_array($metadatas)) return  img_from_metadata($metadatas, $attributes); $imgs  = array(); foreach ($metadatas as $metadata) { $imgs  [] =  img_from_metadata($metadata, $attributes); } return $imgs;  }
    function array_cards_from_metadata ($metadatas, $attributes = false) { if (!is_array($metadatas)) return card_from_metadata($metadatas, $attributes); $cards = array(); foreach ($metadatas as $metadata) { $cards [] = card_from_metadata($metadata, $attributes); } return $cards; }

    $__card_headline = 2;
    function get_card_headline() { global $__card_headline; return $__card_headline; }
    
    function array_imgs  ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $attributes = false)  {                                                                          return  array_imgs_from_metadata(call_user_func("dom\array_".$source."_".$type, $ids, $filter, $tags_in, $tags_out), ($type == "thumbs") ? attributes_add_class($attributes, component_class("img", 'img-thumb'))           : $attributes); }
    function array_card  ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $attributes = false)  { global $__card_headline; $__card_headline = 1+get_last_headline_level(); return        card_from_metadata(call_user_func("dom\array_".$source."_".$type, $ids, $filter, $tags_in, $tags_out),                                                                                                          $attributes); }
    function array_cards ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $attributes = false)  { global $__card_headline; $__card_headline = 1+get_last_headline_level(); return array_cards_from_metadata(call_user_func("dom\array_".$source."_".$type, $ids, $filter, $tags_in, $tags_out), ($type == "thumbs") ? attributes_add_class($attributes, component_class("article", 'card card-thumb')) : $attributes); }
    
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
        return trim(str_replace_all(array("\r\n","\r","\t","\n",'  ','    ','     '), ' ', $html));
    }

    function minify_js($js)
    {
        if (false !== stripos(str_replace("https://", "https:XX", str_replace("http://", "http:XX", $js)), "//")) return $js;
        
        $js = str_replace_all("\n  ",   "\n ",  $js);
        $js = str_replace_all(PHP_EOL,  " ",    $js);
        $js = str_replace_all("\n",     " ",    $js);
        
        return $js;
    }

    function minify_css($css)
    {
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!',                        '',  $css);
        $css =  str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '),  ' ', $css);
        $css =  str_replace('  ',                                                   ' ', $css);

        return $css;
    }

    function minify_php($php)
    {
        // No minification because of heredoc syntax that can be present in php code
        return $php;
    }

    #endregion
    #region WIP API : CACHE SYSTEM
    ######################################################################################################################################

    function cache_start()
    {
        if (!!get("cache"))
        {
            $cache_dir = path("cache");

            if ($cache_dir)
            {
                if (has("cache_reset") && is_dir("/cache")) foreach (array_diff(scandir($cache_dir), array('.','..')) as $basename) @unlink("$cache_dir/$basename");

                $cache_basename         = md5(url(true) . version);
                $cache_filename         = "$cache_dir/$cache_basename";
                $cache_file_exists      = (file_exists($cache_filename)) && (filesize($cache_filename) > 0);
                $cache_file_uptodate    = $cache_file_exists && ((time() - get("cache_time", 1*60*60)) < filemtime($cache_filename));
                
                set("cache_filename", $cache_filename);
                
                if ($cache_file_exists && $cache_file_uptodate) 
                {   
                    $cache_file = @fopen($cache_filename, 'r');
                    
                    if (!!$cache_file)
                    {
                        echo fread($cache_file, filesize($cache_filename));
                        fclose($cache_file);            

                        echo eol().comment("Cached copy, $cache_filename, generated ".date('Y-m-d H:i', filemtime($cache_filename)));
                    }
                    else
                    {
                        echo eol().comment("Could not read cached copy, $cache_filename, generated ".date('Y-m-d H:i', filemtime($cache_filename)));
                    }

                    exit;
                }
            }
            else
            {
                // Could not find cache directory
                set("cache", false);
            }
        
            ob_start();
        }        
    }

    function cache_stop()
    {
        if (!!get("cache"))
        {
            $cache_file = @fopen(get("cache_filename"), 'w');
            
            if (!!$cache_file)
            {   
                fwrite($cache_file, ob_get_contents());
                fclose($cache_file);            
            }
            else if (!has("ajax"))
            {
                if ("html" == get("doctype",false)) echo eol().comment("Could not generate cache! " . get("cache_filename"));
            }
            
            ob_end_flush();
        }
    }
    
    #endregion
    #region WIP API : PHP DOCUMENT
    ######################################################################################################################################

    function html_refresh_page($url)
    {
        return "<html><head><meta http-equiv=\"refresh\" content=\"0; URL='".href($url)."'\" /></head></html>";
    }

    function redirect($url)
    {   
        if ("dependency-graph" == get("doctype")) die("[]");
        if (!!get("static")) die(html_refresh_page($url));
        \header("Location: ".href($url));
        exit;
    }

    function redirect_https()
    {
        if (has("ajax")) return;

        if (!is_localhost() && server_https() != "on")
        {
            $url  = "https://";
            $url .=  server_server_name();
            $url .= (server_server_port("80") != "80" 
                  && server_server_port("80") != "443") ? (":".server_server_port()) : "";
            $url .=  server_request_uri();

            redirect($url);
        }
    }

    if (!has("main") && !has("main-include"))
    {
        init_php();
        init_options();
        init_internals();
    }

    function update_dependency_graph($files = auto)
    {
        $dependency_graph = get("dependency-graph", []);

        if (auto === $files)
        {
            $files = get_included_files();
        }
        else if (!is_array($files))
        {
            $files = [ $files ];
        }

        $dependency_graph = array_unique(array_merge($dependency_graph, $files));

        set("dependency-graph", $dependency_graph);
    }

    if ("dependency-graph" == get("doctype", false))
    {
        update_dependency_graph();
    }
    
    function init($doctype = false, $encoding = false, $content_encoding_header = true, $attachement_basename = false, $attachement_length = false)
    {
        if (has("main") || has("main-include")) return;

        if (!!get("profiling")) debug_enable_profiling();

        if ($doctype    === false) { $doctype   = "html";  }
        if ($encoding   === false) { $encoding  = "utf-8"; }

        $rss = (has("rss") && (get("rss") == ""
                           ||  get("rss") ==  false
                           ||  get("rss") === true)) ? "rss" : get("rss");

        $doctype                = get("doctype",        has("rss") ? $rss         : $doctype    );
        $encoding               = get("encoding",       has("iso") ? "ISO-8859-1" : $encoding   );
        $attachement_basename   = get("attachement",    $attachement_basename                   );

        if ($doctype    === false) { $doctype   = "html"; }
        if ($encoding   === false) { $encoding  = "utf-8"; }

        $binary_types = array("png","jpg");
        $binary = in_array($doctype, $binary_types);

        set("doctype",  $doctype);
        set("encoding", $encoding);
        set("binary",   $binary);        

        $types = array
        (
            "xml"       => 'text/xml'    
        ,   "rss"       => 'text/xml'
        ,   "tile"      => 'text/xml'
        ,   "png"       => 'image/png'
        ,   "jpg"       => 'image/jpeg'
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
                foreach (array("html","xml","png","jpg","json","csv","zip") as $t)
                {
                    if (false !== stripos($doctype, "/$t")) $type = $t;
                }
            }
        }

        if (!!get("gemini") && !get("debug"))
        {
            \header('Content-Encoding: '.$encoding);
            \header('Content-Disposition: inline');/*
            \header('Content-type: text/plain; charset='.$encoding);*/
            \header('Content-type: text/gemini; charset='.$encoding);
        }
        else
        {
            if (!$binary && $content_encoding_header !== false)  \header('Content-Encoding: ' . $encoding      . '');
            if (array_key_exists($type, $types))                 \header('Content-type: '     . $types[$type]  . '; charset=' . $encoding);

            if ($attachement_basename !== false)
            {
                if (array_key_exists($type, $dispositions))     @\header('Content-Disposition: ' . $dispositions[$type]                                                                               . '');
                if ($attachement_length !== false)              @\header('Content-Length: '      . (($attachement_length !== true) ? $attachement_length : filesize($attachement_basename . '.zip"')) . '');
            }

            @\header('Permissions-Policy: interest-cohort=()');
        }

        generate_all_preprocess();
        init_footnotes();

        if (!$binary) cache_start();
    }

    function placeholder_replace($placeholder, $replaced_by, $in, $container_tag = false, $container_attributes = false)
    {
        $profiler = debug_track_timing($placeholder);

        if (!get("static") || !!get("fast")) // TODO Taking this shortcut for now as below code is too slow
        {
            return str_replace(placeholder($placeholder), $replaced_by, $in);
        }

        for ($tab = 9; $tab >= 0; --$tab)
        {
            if (false !== stripos($in, tab($tab).placeholder($placeholder)))
            {
                $in = str_replace(
                    tab($tab).placeholder($placeholder), 
                    $replaced_by == "" ? "" : cosmetic_indent($replaced_by, $tab, $container_tag, $container_attributes, false), 
                    $in);

                break;
            }
        }

        return $in;
    }

    function placeholder_replace_once($placeholder, $replaced_by_cb, $in, $container_tag = false, $container_attributes = false)
    {
        $profiler = debug_track_timing($placeholder);

        if (!get("static") || !!get("fast")) // TODO Taking this shortcut for now as below code is too slow
        {            
            $ph = placeholder($placeholder);

          //return str_replace($ph, $replaced_by, $in);
            $pos = strpos($in, $ph);
            if (false === $pos) return $in;
            return substr($in, 0, $pos).($replaced_by_cb()).substr($in, $pos + strlen($ph));
        }

        for ($tab = 9; $tab >= 0; --$tab)
        {
            $ph = tab($tab).placeholder($placeholder);

            if (false !== ($pos = stripos($in, $ph)))
            {
                $replaced_by = $replaced_by_cb();
                $by = $replaced_by == "" ? "" : cosmetic_indent($replaced_by, $tab, $container_tag, $container_attributes, false);

              //$in = str_replace($ph, $by, $in);
                $in = substr($in, 0, $pos).$by.substr($in, $pos + strlen($ph));

                break;
            }
        }

        return $in;
    }

    function output($doc = "")
    {
        if (!!get("binary"))
        {
            die($doc);
        }

        if (has("main"))
        {
            die();
        }

        if (has("main-include"))
        {
            return;
        }

        if ("html" == get("doctype", false))
        {
            if (false === stripos($doc, "<html") && !has("ajax")) $doc = html($doc);
        }

        if (false !== stripos($doc, "DOM_HOOK_RSS_1"      )) $doc = placeholder_replace("DOM_HOOK_RSS_1"       , _rss      (true), $doc);
        if (false !== stripos($doc, "DOM_HOOK_JSONFEED_1" )) $doc = placeholder_replace("DOM_HOOK_JSONFEED_1"  , _jsonfeed (true), $doc);
        if (false !== stripos($doc, "DOM_HOOK_TILE_1"     )) $doc = placeholder_replace("DOM_HOOK_TILE_1"      , _tile     (true), $doc);
        
        if (false !== stripos($doc, "DOM_HOOK_RSS_0"      )) $doc = placeholder_replace("DOM_HOOK_RSS_0"       , _rss      (false), $doc);
        if (false !== stripos($doc, "DOM_HOOK_JSONFEED_0" )) $doc = placeholder_replace("DOM_HOOK_JSONFEED_0"  , _jsonfeed (false), $doc);
        if (false !== stripos($doc, "DOM_HOOK_TILE_0"     )) $doc = placeholder_replace("DOM_HOOK_TILE_0"      , _tile     (false), $doc);
    
        $doc = placeholder_replace("DOM_HOOK_RSS_1"       , "", $doc);
        $doc = placeholder_replace("DOM_HOOK_JSONFEED_1"  , "", $doc);
        $doc = placeholder_replace("DOM_HOOK_TILE_1"      , "", $doc);
    
        $doc = placeholder_replace("DOM_HOOK_RSS_0"       , "", $doc);
        $doc = placeholder_replace("DOM_HOOK_JSONFEED_0"  , "", $doc);
        $doc = placeholder_replace("DOM_HOOK_TILE_0"      , "", $doc);

        $doc = placeholder_replace_amp_css($doc);

        $doc .= generate_all();

        if (get("compression") == "gzip" && !has("main") && !has("main-include")) ob_start("ob_gzhandler");

        echo $doc;
        
        cache_stop();
    
        generate_all_postprocess();

        if (get("compression") == "gzip" && !has("main") && !has("main-include")) ob_end_flush();
    }

    #endregion
    #region WIP DOCUMENTS GENERATION

    function string_ms_browserconfig()
    {
        $eol = !get("minify") ? eol()           : "";
        $tab = !get("minify") ? eol().tab() : "";

        $icon_dims = array(array(70,70),array(150,150),array(310,310),array(310,150));
        $pollings  = 5;

        $xml_icons = "";
        { 
            foreach ($icon_dims as $dim)
            {
                $w = $dim[0];
                $h = $dim[1];

                $path = path(get("icons_path")."ms-icon-".$w."x".$h.".png");

                if ($path)
                {
                    $xml_icons .= $tab.tag((($w==$h)?"square":"wide").$w."x".$h."logo", false, array("src" => $path), true, true);
                }
            }
        }

        $xml_polling = "";
        for ($i = 0; $i < $pollings; ++$i) $xml_polling .= $tab.tag('polling-uri'.(($i>0)?($i+1):""), false, array("src" => htmlentities(get("canonical").'/?rss=tile&id='.($i+1))), true, true);

        return '<?xml version="1.0" encoding="utf-8"?>'.tag('browserconfig', tag('msapplication', 
        
            $eol.tag('tile',            $xml_icons      . $tab . tag('TileColor', get("theme_color"))                                       . $eol).
            $eol.tag('notification',    $xml_polling    . $tab . tag('frequency', 30) . $tab . tag('cycle', 1)                                  . $eol).
            $eol.tag('badge',           $tab . tag('polling-uri', false, array("src"=>'/badge.xml'), true, true) . $tab . tag('frequency', 30)  . $eol).
            $eol
            ));
    }

    function string_ms_badge()
    {
        return tag("badge", false, array("value" => "available"), true, true);
    }

    $__cached_getimagesize = array();

    function cached_getimagesize($src)
    {
        $profiler = debug_track_timing($src);

        if (!is_string($src)) return 0;

        global $__cached_getimagesize;

        if (!array_key_exists($src, $__cached_getimagesize)) 
        {
            $size = false; // We need [width, height, mime]
            
            if ($size === false)
            {
                //"https://source.unsplash.com/_noSmX8Kgoo/300x200?.jpg

                if (false !== stripos($src, "source.unsplash.com"))
                {
                    $ext  = "png";
                    $pos  = strripos($src, "."); if (false !== $pos) $ext = substr($src, $pos + 1);
                    $mime = "image/$ext";

                    $pos_end = strripos($src, "?");
                    if (false === $pos_end) $pos_end = strlen($src);

                    if (false !== $pos_end)
                    {
                        $pos_bgn = strripos($src, "/");

                        if (false !== $pos_bgn)
                        {
                            $width_height = substr($src, $pos_bgn + 1, $pos_end - $pos_bgn - 1);
                            $width_height = explode("x", $width_height);

                            if (count($width_height) == 2)
                            {
                                $size = array("width" => $width_height[0], "height" => $width_height[1], "mime" => $mime);
                            }
                        }                        
                    }
                }
            }
            
            if ($size === false)
            {
                $size = is_localhost() ? getimagesize($src) : @getimagesize($src);
            }
            
            /*if ($size === false)
            {
                $size = array(get("default_image_ratio_w", 300), get("default_image_ratio_h", 200));
            }*/

            $__cached_getimagesize[$src] = $size;
        }
        
        return $__cached_getimagesize[$src];
    }

    function json_manifest()
    {
        $shortcuts_count_max   = 4; // More than that, google chrome is echoing a warning
        $screenshots_count_max = 5;

        $short_title = get("title");
        $pos = stripos($short_title, " ");
        if (false !== $pos) $short_title = substr($short_title, 0, $pos);
        if (strlen($short_title) > 10) $short_title = substr($short_title, 0, 10);
        
        $icons = array();

        foreach (array(36 => 0.75, 48 => 1.0, 72 => 1.5, 96 => 2.0, 144 => 3.0, 192 => 4.0, 512 => 4.0) as $w => $density)
        {
            $filename = path(get("icons_path")."android-icon-$w"."x"."$w.png");

            if ($filename)
            {
              //$icons[] = array("src"=> $filename, "sizes"=> "$w"."x"."$w", "type"=> "image/png", "density"=> "$density", "purpose"=> "maskable any");
                $icons[] = array("src"=> $filename, "sizes"=> "$w"."x"."$w", "type"=> "image/png", "density"=> "$density", "purpose"=> "any");
                $icons[] = array("src"=> $filename, "sizes"=> "$w"."x"."$w", "type"=> "image/png", "density"=> "$density", "purpose"=> "maskable");


            }
        }

        //$start_url = ((is_localhost() ? get("canonical") : "")."/");
        $start_url = url();

        if (false === stripos($start_url, "?")) $start_url .= "?";
        $start_url .= "&utm_source=homescreen";

        $shortcuts = array();

        $fallback_icons = parse_icons(get("icons_path")."android-icon", 96);

        if (is_array($fallback_icons))
        {
            foreach ($fallback_icons as &$icon)
            {
                $icon = array(
                    "src"   => $icon["path"],
                    "type"  => $icon["attributes"]["type"],
                    "sizes" => $icon["attributes"]["sizes"]
                    );
            }
        }
        else
        {
            $fallback_icons = array();
        }

        // TODO add a way to specify specific shortcuts icons

        global $hook_shortcut_links;

        foreach ($hook_shortcut_links as $link)
        {
            $title = $link["title"];
            $url   = /*$start_url."/".*/$link["url"];

            if (false === stripos($url, "?")) $url .= "?";
            $url .= "&utm_source=homescreen";

            $shortcut = array("name" => $title, "url" => $url);
            if (count($fallback_icons) > 0) $shortcut["icons"] = $fallback_icons;

            $shortcuts[] = $shortcut;
        }

        $shortcuts = array_slice($shortcuts, 0, $shortcuts_count_max);

        global $hook_images;
            
        $screenshots = false;
        {
            if (!$screenshots) { $screenshots = get("screenshots"); }
            if (!$screenshots) { $screenshots = get("support_header_backgrounds"); }
            if (!$screenshots) { $screenshots = array(); foreach ($hook_images as $image) $screenshots[] = $image["src"]; }

            if (!!$screenshots)
            {
                if (!is_array($screenshots)) $screenshots = explode(",", $screenshots);

                foreach ($screenshots as $s => &$src)
                {
                    $src = array("src" => $src, "sizes" => array(), "type" => false);
                }

                if (count($screenshots) > 0)
                {
                    foreach ($screenshots as $s => &$img)
                    {
                        $size = cached_getimagesize($img["src"]);
                        
                        if (false === $size || !is_array($size) || count($size) < 2)
                        {
                            unset($screenshots[$s]);
                        }
                        else
                        {
                            list($w,$h) = array_values($size);
                            
                            if ($w < 320 || $h < 320 || $w > 3840 || $h > 3840
                            || ((($w > $h) ? ($w / $h) : ($h / $w)) > 2.3))
                            {
                                unset($screenshots[$s]);
                            }
                            else
                            {
                                $img["sizes"] = $w."x".$h;
                                $img["type"]  = $size["mime"];
                            }
                        }
                    }
                }

                $screenshots = array_values($screenshots);
            }
        }
        
        $screenshots = array_slice($screenshots, 0, $screenshots_count_max);
        
        $widest_screenshot_index = false;
        $widest_screenshot_ratio = 0;

        foreach ($screenshots as $s => $screenshot)
        {
            list($w, $h) = explode("x", at($screenshot, "sizes"));
            $ratio = $w / $h;
            
            if ($ratio > $widest_screenshot_ratio)
            {
                $widest_screenshot_index = $s;
                $widest_screenshot_ratio = $ratio;
            }
        }

        if (false !== $widest_screenshot_index)
        {
            $screenshots[$widest_screenshot_index]["form_factor"] = "wide";
        }

        $json = array();

        if (!!get(\dom\manifest_id))
        {
            $json = array_merge($json, array("id" => get(\dom\manifest_id)));
        }

        $json = array_merge($json, array(

            "name"             => get("title"),
            "short_name"       => $short_title,
            "description"      => get("description"),
            
            "background_color" => get("manifest_background_color",   get("background_color")),
            "theme_color"      => get("manifest_theme_color",        get("theme_color")),

            "shortcuts"        => $shortcuts,
            "screenshots"      => $screenshots,
           
            "start_url"        => $start_url,
            "display"          => "standalone",

            "orientation"      => "portrait-primary",

            "launch_handler" => array(

                "client_mode" => [ "navigate-existing", "auto" ]

                ),
            
            "related_applications" => array( 

                array( "platform"=> "web", "url"=> get("canonical") ) 

                ),                  
           
            "icons"=> $icons
            
            ));

        foreach ($json as $key => $value)
        {
            if ($value === false || $value === null) unset($json[$key]);
        }

        return $json;
    }

    function string_manifest()
    {
        return  (!get("minify") && defined("JSON_PRETTY_PRINT")) 
              ? json_encode(json_manifest(), JSON_PRETTY_PRINT)
              : json_encode(json_manifest());
    }

    function string_robots()
    {
        return implode(PHP_EOL, [

            "User-agent: AdsBot-Google",
            "User-agent: Amazonbot",
            "User-agent: anthropic-ai",
            "User-agent: Applebot",
            "User-agent: AwarioRssBot",
            "User-agent: AwarioSmartBot",
            "User-agent: Bytespider",
            "User-agent: CCBot",
            "User-agent: ChatGPT-User",
            "User-agent: ClaudeBot",
            "User-agent: Claude-Web",
            "User-agent: cohere-ai",
            "User-agent: DataForSeoBot",
            "User-agent: FacebookBot",
            "User-agent: Google-Extended",
            "User-agent: GoogleOther",
            "User-agent: GPTBot",
            "User-agent: ImagesiftBot",
            "User-agent: magpie-crawler",
            "User-agent: omgili",
            "User-agent: omgilibot",
            "User-agent: peer39_crawler",
            "User-agent: peer39_crawler/1.0",
            "User-agent: PerplexityBot",
            "User-agent: YouBot",
            "Disallow: /",

            ]);
    }

    function string_human()
    {
        heredoc_start(-3); ?><html><?php heredoc_flush(null); ?> 
        
            /* SITE */

            Standards  : HTML5, CSS3
            Language   : French
            Doctype    : HTML5
            Components : DOM.php, Optionnal: MCW, Bootstrap, Spectre, Amp and others
            IDE        : Visual Studio Code
            
        <?php heredoc_flush("raw"); ?></html><?php return heredoc_stop(null);
    }

    function string_gpc()
    {
        return json_encode([

            "gpc"           => true,
            "version"       => 1,
            "lastUpdate"    => date("Y-m-d")
        ]);
    }

    function string_dnt_policy()
    {
        return content("https://raw.githubusercontent.com/EFForg/dnt-policy/master/dnt-policy-1.0.txt");
    }

    function string_loading_svg($force_minify = false, $color = "#FF8800")
    {
        heredoc_start(-2); ?><html><?php heredoc_flush(null); ?> 
        
            <svg viewBox="0 0 100 100" width="100" height="100" class="lds-spinner" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; animation-play-state: running; animation-delay: 0s; background: none;">

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
    
        <?php heredoc_flush("raw_html", $force_minify); ?></html><?php return heredoc_stop(null);
    }

    function string_offline_html($force_minify = false)
    {
        heredoc_start(-3); ?><html><?php heredoc_flush(null); ?> 
        
            <!doctype html><html>
                <head>
                    <title>Please wait...</title>
                    <meta charset="utf-8" /><meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1" />
                    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
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

        <?php heredoc_flush("raw_html", $force_minify); ?></html><?php return heredoc_stop(null);
    }

    function string_service_worker_install_js($force_minify = false)
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            var swsource = "sw.js";

            if ("serviceWorker" in navigator)
            {
                navigator.serviceWorker.register(swsource).then(function(reg)
                {
                    dom.log("AMP", "ServiceWorker scope: ", reg.scope);
                })
                .catch(function(err)
                {
                    dom.log("AMP", "ServiceWorker registration failed: ", err);
                });
            };

        <?php heredoc_flush("raw_js", $force_minify); ?></script><?php return heredoc_stop(null);
    }

    function string_service_worker_install()
    {
        heredoc_start(-3); ?><html><?php heredoc_flush(null); ?> 

            <!doctype html><html>
                <head>
                    <title>Installing service worker</title>
                    <script type="text/javascript"><?= string_service_worker_install_js(true) ?></script>
                </head>
                <body>
                </body>
            </html>

        <?php heredoc_flush("raw_html"); ?></script><?php return heredoc_stop(null);
    }

    /**
     * FONT STACKS 
     * https://modernfontstacks.com/ by Dan Klammer (https://twitter.com/danklammer)
     */

    function array_system_font_stack_systemui(              $quote = '"') { return [ 'system-ui', 'sans-serif' ];                                                                                                                                                                                          } // System UI fonts are those native to the operating system interface. They are highly legible and easy to read at small sizes, contains many font weights, and is ideal for UI elements.
    function array_system_font_stack_transitional(          $quote = '"') { return [ 'Charter', $quote.'Bitstream Charter'.$quote, $quote.'Sitka Text'.$quote, 'Cambria', 'serif' ];                                                                                                                       } // Transitional typefaces are a mix between Old Style and Modern typefaces that was developed during The Enlightenment. One of the most famous examples of a Transitional typeface is Times New Roman, which was developed for the Times of London newspaper.
    function array_system_font_stack_oldstyle(              $quote = '"') { return [ $quote.'Iowan Old Style'.$quote, $quote.'Palatino Linotype'.$quote, $quote.'URW Palladio L'.$quote, 'P052', 'serif' ];                                                                                                } // Old Style typefaces are characterized by diagonal stress, low contrast between thick and thin strokes, and rounded serifs, and were developed in the Renaissance period. One of the most famous examples of an Old Style typeface is Garamond.
    function array_system_font_stack_humanist(              $quote = '"') { return [ 'Seravek', $quote.'Gill Sans Nova'.$quote, 'Ubuntu', 'Calibri', $quote.'DejaVu Sans'.$quote, 'source-sans-pro', 'sans-serif' ];                                                                                       } // Humanist typefaces are characterized by their organic, calligraphic forms and low contrast between thick and thin strokes. These typefaces are inspired by the handwriting of the Renaissance period and are often considered to be more legible and easier to read than other sans-serif typefaces.
    function array_system_font_stack_geometric(             $quote = '"') { return [ 'Avenir', 'Montserrat', 'Corbel', $quote.'URW Gothic'.$quote, 'source-sans-pro', 'sans-serif' ];                                                                                                                      } // Geometric Humanist typefaces are characterized by their clean, geometric forms and uniform stroke widths. These typefaces are often considered to be modern and sleek in appearance, and are often used for headlines and other display purposes. Futura is a famous example of this classification.
    function array_system_font_stack_classical(             $quote = '"') { return [ 'Optima', 'Candara', $quote.'Noto Sans'.$quote, 'source-sans-pro', 'sans-serif' ];                                                                                                                                    } // Classical Humanist typefaces are characterized by how the strokes subtly widen as they reach the stroke terminals without ending in a serif. These typefaces are inspired by classical Roman capitals and the stone-carving on Renaissance-period tombstones.
    function array_system_font_stack_neogrotestque(         $quote = '"') { return [ 'Inter', 'Roboto', $quote.'Helvetica Neue'.$quote, $quote.'Arial Nova'.$quote, $quote.'Nimbus Sans'.$quote, 'Arial', 'sans-serif' ];                                                                                  } // Neo-Grotesque typefaces are a style of sans-serif that was developed in the late 19th and early 20th centuries and is characterized by its clean, geometric forms and uniform stroke widths. One of the most famous examples of a Neo-Grotesque typeface is Helvetica.
    function array_system_font_stack_monospaceslabserif(    $quote = '"') { return [ $quote.'Nimbus Mono PS'.$quote, $quote.'Courier New'.$quote, 'monospace' ];                                                                                                                                           } // Monospace Slab Serif typefaces are characterized by their fixed-width letters, which have the same width regardless of their shape, and its simple, geometric forms. Used to emulate typewriter output for reports, tabular work and technical documentation.
    function array_system_font_stack_monospacecode(         $quote = '"') { return [ 'ui-monospace', $quote.'Cascadia Code'.$quote, $quote.'Source Code Pro'.$quote, 'Menlo', 'Consolas', $quote.'DejaVu Sans Mono'.$quote, 'monospace' ];                                                                 } // Monospace Code typefaces are specifically designed for use in programming and other technical applications. These typefaces are characterized by their monospaced design, which means that all letters and characters have the same width, and their clear, legible forms.
    function array_system_font_stack_industrial(            $quote = '"') { return [ 'Bahnschrift', $quote.'DIN Alternate'.$quote, $quote.'Franklin Gothic Medium'.$quote, $quote.'Nimbus Sans Narrow'.$quote, 'sans-serif-condensed', 'sans-serif' ];                                                     } // Industrial typefaces originated in the late 19th century and was heavily influenced by the advancements in technology and industry during that time. Industrial typefaces are characterized by their bold, sans-serif letterforms, simple and straightforward appearance, and the use of straight lines and geometric shapes.
    function array_system_font_stack_rounded(               $quote = '"') { return [ 'ui-rounded', $quote.'Hiragino Maru Gothic ProN'.$quote, 'Quicksand', 'Comfortaa', 'Manjari', $quote.'Arial Rounded MT'.$quote, $quote.'Arial Rounded MT Bold'.$quote, 'Calibri', 'source-sans-pro', 'sans-serif' ];  } // Rounded typefaces are characterized by the rounded curved letterforms and give a softer, friendlier appearance. The rounded edges give the typeface a more organic and playful feel, making it suitable for use in informal or child-friendly designs. The rounded sans-serif style has been popular since the 1950s, and it continues to be widely used in advertising, branding, and other forms of graphic design.
    function array_system_font_stack_slabserif(             $quote = '"') { return [ 'Rockwell', $quote.'Rockwell Nova'.$quote, $quote.'Roboto Slab'.$quote, $quote.'DejaVu Serif'.$quote, $quote.'Sitka Small'.$quote, 'serif' ];                                                                         } // Slab Serif typefaces are characterized by the presence of thick, block-like serifs on the ends of each letterform. These serifs are usually unbracketed, meaning they do not have any curved or tapered transitions to the main stroke of the letter.
    function array_system_font_stack_antique(               $quote = '"') { return [ 'Superclarendon', $quote.'Bookman Old Style'.$quote, $quote.'URW Bookman'.$quote, $quote.'URW Bookman L'.$quote, $quote.'Georgia Pro'.$quote, 'Georgia', 'serif' ];                                                   } // Antique typefaces, also known as Egyptians, are a subset of serif typefaces that were popular in the 19th century. They are characterized by their block-like serifs and thick uniform stroke weight.
    function array_system_font_stack_didone(                $quote = '"') { return [ 'Didot', $quote.'Bodoni MT'.$quote, $quote.'Noto Serif Display'.$quote, $quote.'URW Palladio L'.$quote, 'P052', 'Sylfaen', 'serif' ];                                                                                 } // Didone typefaces, also known as Modern typefaces, are characterized by the high contrast between thick and thin strokes, vertical stress, and hairline serifs with no bracketing. The Didone style emerged in the late 18th century and gained popularity during the 19th century.
    function array_system_font_stack_handwritten(           $quote = '"') { return [ $quote.'Segoe Print'.$quote, $quote.'Bradley Hand'.$quote, 'Chilanka', 'TSCu_Comic', 'casual', 'cursive' ];                                                                                                           } // Handwritten typefaces are designed to mimic the look and feel of handwriting. Despite the vast array of handwriting styles, this font stack tend to adopt a more informal and everyday style of handwriting.
    function array_system_font_stack_emoji(                 $quote = '"') { return [ $quote.'Apple Color Emoji'.$quote, $quote.'Segoe UI Emoji'.$quote, $quote.'Segoe UI Symbol'.$quote, $quote.'Noto Color Emoji'.$quote ];                                                                               } // Emoji Support: Looking to add native emojis to your page? Append these fonts at the end of your font stack

    // Old DOM font stacks. Condensed is still needed

    function array_system_font_stack_symbols(               $quote = '"') { return [ $quote.'Noto Sans Symbols'.$quote, 'sans-serif' ]; }
    function array_system_font_stack_regular(               $quote = '"') { return [ 'Inter', 'Roboto', '-apple-system', 'system-ui', 'BlinkMacSystemFont', 'ui-sans-serif', $quote.'Segoe UI'.$quote, $quote.'San Francisco'.$quote, 'Helvetica', 'Arial', 'sans-serif', $quote.'Apple Color Emoji'.$quote, $quote.'Segoe UI Emoji'.$quote, $quote.'Segoe UI Symbol'.$quote ]; }
    function array_system_font_stack_condensed(             $quote = '"') { return [ $quote.'Arial Narrow'.$quote, $quote.'AvenirNextCondensed-Bold'.$quote, $quote.'Futura-CondensedExtraBold'.$quote, 'HelveticaNeue-CondensedBold', $quote.'Ubuntu Condensed'.$quote, $quote.'Liberation Sans Narrow'.$quote, $quote.'Franklin Gothic Demi Cond'.$quote, 'sans-serif-condensed', 'Arial', $quote.'Trebuchet MS'.$quote, $quote.'Lucida Grande'.$quote, 'Tahoma', 'Verdana', 'sans-serif' ]; }

    function string_system_font_stack($quote = '"', $type = false)
    {
        if ($quote === true || $quote === false || (is_string($quote) && strlen($quote) > 1)) { $type = $quote; $quote = '"'; }
        if ($type == true)  $type = "condensed";
        if ($type == false) $type = "neogrotestque";

        $fn = "\dom\array_system_font_stack_$type";
        if (is_callable($fn)) return implode(", ", get("font_stack_$type", $fn($quote)));

        return string_system_font_stack($quote, "humanist");
    }

    #region DEPRECATED

    function string_system_font_stack_symbols(      $quote = '"') { return string_system_font_stack($quote, "symbols"); }
    function string_system_font_stack_regular(      $quote = '"') { return string_system_font_stack($quote, "regular"); }
    function string_system_font_stack_condensed(    $quote = '"') { return string_system_font_stack($quote, "condensed"); }

    #endregion DEPRECATED

    function string_loading_svg_src_base64($force_minify = false)
    {
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPgo8c3ZnIHdpZHRoPSI0MHB4IiBoZWlnaHQ9IjQwcHgiIHZpZXdCb3g9IjAgMCA0MCA0MCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjEuNDE0MjE7IiB4PSIwcHgiIHk9IjBweCI+CiAgICA8ZGVmcz4KICAgICAgICA8c3R5bGUgdHlwZT0idGV4dC9jc3MiPjwhW0NEQVRBWwogICAgICAgICAgICBALXdlYmtpdC1rZXlmcmFtZXMgc3BpbiB7CiAgICAgICAgICAgICAgZnJvbSB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIC13ZWJraXQtdHJhbnNmb3JtOiByb3RhdGUoLTM1OWRlZykKICAgICAgICAgICAgICB9CiAgICAgICAgICAgIH0KICAgICAgICAgICAgQGtleWZyYW1lcyBzcGluIHsKICAgICAgICAgICAgICBmcm9tIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKC0zNTlkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICB9CiAgICAgICAgICAgIHN2ZyB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybS1vcmlnaW46IDUwJSA1MCU7CiAgICAgICAgICAgICAgICAtd2Via2l0LWFuaW1hdGlvbjogc3BpbiAxLjVzIGxpbmVhciBpbmZpbml0ZTsKICAgICAgICAgICAgICAgIC13ZWJraXQtYmFja2ZhY2UtdmlzaWJpbGl0eTogaGlkZGVuOwogICAgICAgICAgICAgICAgYW5pbWF0aW9uOiBzcGluIDEuNXMgbGluZWFyIGluZmluaXRlOwogICAgICAgICAgICB9CiAgICAgICAgXV0+PC9zdHlsZT4KICAgIDwvZGVmcz4KICAgIDxnIGlkPSJvdXRlciI+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwwQzIyLjIwNTgsMCAyMy45OTM5LDEuNzg4MTMgMjMuOTkzOSwzLjk5MzlDMjMuOTkzOSw2LjE5OTY4IDIyLjIwNTgsNy45ODc4MSAyMCw3Ljk4NzgxQzE3Ljc5NDIsNy45ODc4MSAxNi4wMDYxLDYuMTk5NjggMTYuMDA2MSwzLjk5MzlDMTYuMDA2MSwxLjc4ODEzIDE3Ljc5NDIsMCAyMCwwWiIgc3R5bGU9ImZpbGw6YmxhY2s7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNNS44NTc4Niw1Ljg1Nzg2QzcuNDE3NTgsNC4yOTgxNSA5Ljk0NjM4LDQuMjk4MTUgMTEuNTA2MSw1Ljg1Nzg2QzEzLjA2NTgsNy40MTc1OCAxMy4wNjU4LDkuOTQ2MzggMTEuNTA2MSwxMS41MDYxQzkuOTQ2MzgsMTMuMDY1OCA3LjQxNzU4LDEzLjA2NTggNS44NTc4NiwxMS41MDYxQzQuMjk4MTUsOS45NDYzOCA0LjI5ODE1LDcuNDE3NTggNS44NTc4Niw1Ljg1Nzg2WiIgc3R5bGU9ImZpbGw6cmdiKDIxMCwyMTAsMjEwKTsiLz4KICAgICAgICA8L2c+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwzMi4wMTIyQzIyLjIwNTgsMzIuMDEyMiAyMy45OTM5LDMzLjgwMDMgMjMuOTkzOSwzNi4wMDYxQzIzLjk5MzksMzguMjExOSAyMi4yMDU4LDQwIDIwLDQwQzE3Ljc5NDIsNDAgMTYuMDA2MSwzOC4yMTE5IDE2LjAwNjEsMzYuMDA2MUMxNi4wMDYxLDMzLjgwMDMgMTcuNzk0MiwzMi4wMTIyIDIwLDMyLjAxMjJaIiBzdHlsZT0iZmlsbDpyZ2IoMTMwLDEzMCwxMzApOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksMjguNDkzOUMzMC4wNTM2LDI2LjkzNDIgMzIuNTgyNCwyNi45MzQyIDM0LjE0MjEsMjguNDkzOUMzNS43MDE5LDMwLjA1MzYgMzUuNzAxOSwzMi41ODI0IDM0LjE0MjEsMzQuMTQyMUMzMi41ODI0LDM1LjcwMTkgMzAuMDUzNiwzNS43MDE5IDI4LjQ5MzksMzQuMTQyMUMyNi45MzQyLDMyLjU4MjQgMjYuOTM0MiwzMC4wNTM2IDI4LjQ5MzksMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxMDEsMTAxLDEwMSk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMy45OTM5LDE2LjAwNjFDNi4xOTk2OCwxNi4wMDYxIDcuOTg3ODEsMTcuNzk0MiA3Ljk4NzgxLDIwQzcuOTg3ODEsMjIuMjA1OCA2LjE5OTY4LDIzLjk5MzkgMy45OTM5LDIzLjk5MzlDMS43ODgxMywyMy45OTM5IDAsMjIuMjA1OCAwLDIwQzAsMTcuNzk0MiAxLjc4ODEzLDE2LjAwNjEgMy45OTM5LDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoMTg3LDE4NywxODcpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTUuODU3ODYsMjguNDkzOUM3LjQxNzU4LDI2LjkzNDIgOS45NDYzOCwyNi45MzQyIDExLjUwNjEsMjguNDkzOUMxMy4wNjU4LDMwLjA1MzYgMTMuMDY1OCwzMi41ODI0IDExLjUwNjEsMzQuMTQyMUM5Ljk0NjM4LDM1LjcwMTkgNy40MTc1OCwzNS43MDE5IDUuODU3ODYsMzQuMTQyMUM0LjI5ODE1LDMyLjU4MjQgNC4yOTgxNSwzMC4wNTM2IDUuODU3ODYsMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxNjQsMTY0LDE2NCk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMzYuMDA2MSwxNi4wMDYxQzM4LjIxMTksMTYuMDA2MSA0MCwxNy43OTQyIDQwLDIwQzQwLDIyLjIwNTggMzguMjExOSwyMy45OTM5IDM2LjAwNjEsMjMuOTkzOUMzMy44MDAzLDIzLjk5MzkgMzIuMDEyMiwyMi4yMDU4IDMyLjAxMjIsMjBDMzIuMDEyMiwxNy43OTQyIDMzLjgwMDMsMTYuMDA2MSAzNi4wMDYxLDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoNzQsNzQsNzQpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksNS44NTc4NkMzMC4wNTM2LDQuMjk4MTUgMzIuNTgyNCw0LjI5ODE1IDM0LjE0MjEsNS44NTc4NkMzNS43MDE5LDcuNDE3NTggMzUuNzAxOSw5Ljk0NjM4IDM0LjE0MjEsMTEuNTA2MUMzMi41ODI0LDEzLjA2NTggMzAuMDUzNiwxMy4wNjU4IDI4LjQ5MzksMTEuNTA2MUMyNi45MzQyLDkuOTQ2MzggMjYuOTM0Miw3LjQxNzU4IDI4LjQ5MzksNS44NTc4NloiIHN0eWxlPSJmaWxsOnJnYig1MCw1MCw1MCk7Ii8+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4K";
    }

    function string_loading_html($force_minify = false)
    {
        heredoc_start(-3); ?><html><?php heredoc_flush(null); ?> 

            <!doctype html><html>
                <head>
                    <title>Please wait...</title>
                    <meta charset="utf-8" /><meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1" />
                    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
                    <meta name="format-detection" content="telephone=no" />
                    <meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1" />
                    <meta http-equiv="refresh" content="3">
                </head>
                <body style="margin: 0; width: 100vw; text-align: center; color: #DDD; background-color: rgb(30,30,30); font-family: <?= string_system_font_stack("\'") ?>; padding-top: calc(50vh - 2em - 64px);">
                    <p>OFFLINE<br>Please wait...</p>
                    <p><img alt="Please wait..." src="<?= string_loading_svg_src_base64($force_minify) ?>" /></p>
                </body>
            </html>

        <?php heredoc_flush("raw_html", $force_minify); ?></html><?php return heredoc_stop(null);
    }

    function string_service_worker()
    {
        heredoc_start(-3); ?><script><?php heredoc_flush(null); ?> 
    
          /*importScripts("https://storage.googleapis.com/workbox-cdn/releases/6.1.2/workbox-sw.js");*/
            importScripts("https://storage.googleapis.com/workbox-cdn/releases/6.4.1/workbox-sw.js");

            if (workbox)
            {   
                const strategy = new workbox.strategies.CacheFirst();
                const urls     = [ "<?= path("offline.html") ?>" ];

                workbox.recipes.warmStrategyCache({urls, strategy});

                workbox.recipes.offlineFallback();
                workbox.recipes.pageCache();
                workbox.recipes.staticResourceCache();
                workbox.recipes.imageCache();
                workbox.recipes.googleFontsCache();
            } 
            else 
            {
                dom.log("Could not load workbox framework!");
            }

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    $__generated = array(

        array("path" => "manifest.json",                 "generate" => false, "function" => "string_manifest"),
        array("path" => "browserconfig.xml",             "generate" => false, "function" => "string_ms_browserconfig"),
        array("path" => "badge.xml",                     "generate" => false, "function" => "string_ms_badge"),
        array("path" => "robots.txt",                    "generate" => false, "function" => "string_robots"),
        array("path" => "human.txt",                     "generate" => false, "function" => "string_human"),
        array("path" => ".well-known/gpc.json",          "generate" => false, "function" => "string_gpc"),
        array("path" => ".well-known/dnt-policy.txt",    "generate" => false, "function" => "string_dnt_policy"),
        array("path" => "loading.svg",                   "generate" => false, "function" => "string_loading_svg"),
        array("path" => "offline.html",                  "generate" => false, "function" => "string_offline_html"),
        array("path" => "sw.js",                         "generate" => false, "function" => "string_service_worker"),
        array("path" => "install-service-worker.html",   "generate" => false, "function" => "string_service_worker_install")

        );

    function generate_all_preprocess()
    {
        global $__generated;

        foreach ($__generated as &$generated)
        { 
            if (true == get("generate", auto))
            {
                $generated["generate"] = true;
            }
            else if (false == get("generate", auto))
            {
                $generated["generate"] = false;
            }
            else if (auto == get("generate", auto))
            {
                // Unless generation is requested, do not generate each file that is already accessible
                // Even if it accesses a parent/inherited file
    
                $generated["generate"] = true;
    
                     if (path($generated["path"]))        $generated["generate"] = false;
                else if (path($generated["path"].".php")) $generated["generate"] = false;
            }
        }
    }

    function generate_all()
    {
        global $__generated;

        foreach ($__generated as $generated)
        { 
            if ($generated["generate"])
            {
                $dst_path = $generated["path"];
                
                if (!!get("generate_dst"))
                {
                    $dst_path = get("generate_dst")."/".$generated["path"];
                }

                $old_content = @file_get_contents($dst_path);

                update_dependency_graph($dst_path);

                $fn = $generated["function"];
                if (!is_callable($fn)) $fn = "dom\\$fn";

                $new_content = $fn();
                $new_content = utf8_encode($new_content);

                if ($new_content != $old_content)
                {
                    $dst_path = str_replace("\\", "/", $dst_path);
                    
                    // Build intermediate folders if needed
                    {
                        $intermediate_folders = explode("/", $dst_path);

                        $cwd = getcwd();
                        
                        while (count($intermediate_folders) > 1)
                        {
                            $dir = array_shift($intermediate_folders);
                            if (!is_dir($dir)) @mkdir($dir);
                            @chdir($dir);
                        }

                        chdir($cwd);
                    }

                    //die(print_r(["666" => [ "generated" => $generated, "new_content" => $new_content,  "old_content" => $old_content ] ], true));
                    file_put_contents($dst_path, $new_content);
                }

                /*$f = @fopen($dst_path, "w+");

                if (!$f)
                {
                    error_log("COULD NOT OPEN ".getcwd()."/$dst_path");
                    continue;
                }

                $old_content = stream_get_contents($f);
                rewind($f);

                $fn = $generated["function"];
                if (!is_callable($fn)) $fn = "dom\\$fn";

                $new_content = $fn();

                if ($new_content != $old_content)
                {
                    //die(print_r(["666" => [ "generated" => $generated, "new_content" => $new_content,  "old_content" => $old_content ] ], true));
                    fwrite($f, utf8_encode($new_content));
                }

                fclose($f);*/
            }
        }
    }

    function generate_all_postprocess()
    {
    }

    #endregion
    #region WIP API : CSS snippets
    ######################################################################################################################################
    
    function css_gradient($from = "var(--text-color)", $to = "var(--theme-color)")
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
    
    function css_gradient_unset()
    {
        return "-webkit-text-fill-color: unset;";
    }

    #endregion
    #region WIP API : DOM : URLS
    ######################################################################################################################################

    function url_pinterest_board            ($username = false, $board = false) { $username = ($username === false) ? get("pinterest_user")     : $username; 
                                                                                  $board    = ($board    === false) ? get("pinterest_board")    : $board;      return "https://www.pinterest.com/$username/$board/";                      }
    function url_instagram_user             ($username = false)                 { $username = ($username === false) ? get("instagram_user")     : $username;   return "https://www.instagram.com/$username/";                             }
    function url_instagram_post             ($short_code)                       {                                                                              return "https://instagram.com/p/$short_code/";                             }
    function url_flickr_user                ($username = false)                 { $username = ($username === false) ? get("flickr_user")        : $username;   return "https://www.flickr.com/photos/$username/";                         }
    function url_500px_user                 ($username = false)                 { $username = ($username === false) ? get("500px_user")         : $username;   return "https://www.500px.com/$username/";                                 }
    function url_pixelfed_user              ($username = false)                 { $username = ($username === false) ? get("pixelfed_user")      : $username;   return "https://pixelfed.social/$username/";                               }
    function url_mastodon_user              ($username = false,     
                                             $instance = "mastodon.social")     { $username = ($username === false) ? get("mastodon_user")      : $username;
                                                                                  $instance = ($instance === false) ? get("mastodon_domain")    : $instance;   return "https://$instance/@$username/";                                    }
    function url_github_user                ($username = false)                 { $username = ($username === false) ? get("github_user")        : $username;   return "https://github.com/$username";                                     }
    function url_lastfm_user                ($username = false)                 { $username = ($username === false) ? get("lastfm_user")        : $username;   return "https://last.fm/user/$username";                                   }
    function url_codepen_user               ($username = false)                 { $username = ($username === false) ? get("codepen_user")       : $username;   return "https://codepen.io/$username";                                     }
                                                
    function url_twitter_user               ($username = false)                 { $username = ($username === false) ? get("twitter_user")       : $username;   return "https://twitter.com/$username";                                    }
    function url_facebook_user              ($username = false)                 { $username = ($username === false) ? get("facebook_user")      : $username;   return "https://www.facebook.com/$username";                               }
                                                
    function url_flickr_page                ($page     = false)                 { $page     = ($page     === false) ? get("flickr_page")        : $page;       return "https://www.flickr.com/photos/$page/";                             }
    function url_pinterest_pin              ($pin)                              {                                                                              return "https://www.pinterest.com/pin/$pin/";                              }    
    function url_facebook_page              ($page     = false)                 { $page     = ($page     === false) ? get("facebook_page")      : $page;       return "https://www.facebook.com/$page";                                   }
    function url_twitter_page               ($page     = false)                 { $page     = ($page     === false) ? get("twitter_page")       : $page;       return "https://twitter.com/$page";                                        }
    function url_linkedin_page              ($page     = false)                 { $page     = ($page     === false) ? get("linkedin_page")      : $page;       return "https://www.linkedin.com/in/$page";                                }
    function url_github_repository          ($username = false, $repo = false)  { $username = ($username === false) ? get("github_user")        : $username; 
                                                                                  $repo     = ($repo     === false) ? get("github_repository")  : $repo;       return "https://github.com/$username/$repo#readme";                        }
    function url_facebook_page_about        ($page     = false)                 { $page     = ($page     === false) ? get("facebook_page")      : $page;       return "https://www.facebook.com/$page/about";                             }
    function url_tumblr_blog                ($blogname = false)                 { $blogname = ($blogname === false) ? get("tumblr_blog")        : $blogname;   return "https://$blogname.tumblr.com";                                     }
    function url_tumblr_avatar              ($blogname = false, $size = 64)     { $blogname = ($blogname === false) ? get("tumblr_blog")        : $blogname;   return "https://api.tumblr.com/v2/blog/$blogname.tumblr.com/avatar/$size"; }
    function url_messenger                  ($id       = false)                 { $id       = ($id       === false) ? get("messenger_id")       : $id;         return "https://m.me/$id";                                                 }
    function url_whatsapp                   ($phone    = false)                 { $phone    = ($phone    === false) ? get("phone")              : $phone;      return "https://wa.me/".trim(str_replace([" ","+","(",")"], "", $phone));  }
    
    function url_amp                        ($on = true)                        {                                                                                  return (is_dir("./amp") ? "./amp" : ("?amp=".(!!$on?"1":"0"))).(is_localhost()?"#development=1":"");   }

    function url_facebook_search_by_tags    ($tags, $userdata = false)          { return "https://www.facebook.com/hashtag/"            . urlencode($tags); }
    function url_pinterest_search_by_tags   ($tags, $userdata = false)          { return "https://www.pinterest.com/search/pins/?q="    . urlencode($tags); }
    function url_instagram_search_by_tags   ($tags, $userdata = false)          { return "https://www.instagram.com/explore/tags/"      . urlencode($tags); }
    function url_tumblr_search_by_tags      ($tags, $userdata = false)          { return "https://".$userdata.".tumblr.com/tagged/"     . urlencode($tags); }
    function url_flickr_search_by_tags      ($tags, $userdata = false)          { return "https://www.flickr.com/search/?text="         . urlencode($tags); }
    
    function url_leboncoin                  ($url = false)                      { return ($url === false) ? get("leboncoin_url", get("leboncoin", "https://www.leboncoin.fr")) : $url; }
    function url_seloger                    ($url = false)                      { return ($url === false) ? get("seloger_url",   get("seloger",   "https://www.seloger.com"))  : $url; }

    function top_id                         ()                                  { return "!"; }

    function url_empty                      ()                                  { return ""; }
    function url_top                        ()                                  { return "#".top_id(); }
    function url_void                       ()                                  { return "javascript:void(0)"; } // ! As #! jumps on the page. But a without url also triggers warnings
    function url_print                      ()                                  { return AMP() ? url_void() : "javascript:scan_and_print();"; }
    
    #endregion
    #region WIP API : DOM : COLORS
    ######################################################################################################################################

    // https://paulund.co.uk/social-media-colours

    function color_facebook         () { return '#3B5998'; }
    function color_discord          () { return '#5865F2'; }
    function color_twitter          () { return '#00ACED'; }
    function color_linkedin         () { return '#0077B5'; }
    function color_google           () { return array('#DB4437', '#F4B400', '#0F9D58', '#4285F4'); } function color_googlenews() { return color_google(); }
    function color_spotify          () { return '#1ED760'; }
    function color_deezer           () { return array('#DB4437', '#F4B400', '#0F9D58', '#4285F4'); }
    function color_soundcloud       () { return '#f79810'; }
    function color_link             () { return 'currentcolor'; }
    function color_youtube          () { return '#BB0000'; }
    function color_instagram        () { return '#517FA4'; }
    function color_pinterest        () { return '#CB2027'; }
    function color_500px            () { return '#222222'; }
    function color_pixelfed         () { return array('#EB0256','#FF257E','#A63FDB','#FFB000','#FF7725','#FF5C34','#9EE85D','#0ED061','#17C934','#03FF6E','#00FFF0','#21EFE3','#2598FF','#0087FF'); }
    function color_mastodon         () { return '#6364FF'; }
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
    function color_shareopenly      () { return '#FFFFFF'; }
   
    #endregion
    #region WIP API : DOM : HTML COMPONENTS : SPECIAL TAGS
    ######################################################################################################################################

    /**
     * Special helper / low level components
     */
    
    function self($html) { return $html; }

    function include_file($filename, $silent_errors = auto)
    {
        if ($silent_errors === auto)
        {
            $silent_errors = is_localhost() ? false : true;
        }

        ob_start();

        $content = "";

        if ($silent_errors) { @internal_include($filename); $content = @ob_get_clean(); }
        else                {  internal_include($filename); $content =  ob_get_clean(); }
        
        if (false !== $content) { return $content; }

        if ($silent_errors) { $content = @file_get_contents($filename); }
        else                { $content =  file_get_contents($filename); }

        update_dependency_graph($filename);
        
        if (false !== $content) { return $content; }

        return "";
    }
    
    function raw            ($html, $force_minify = false, $trim = true)  { return $html; }

    function raw_svg        ($html, $force_minify = false, $trim = true)  { if (!!get("gemini")) return ""; if (!!get("no_html")) return ''; if ((!!get("minify") || !!get("minify_html" ) || $force_minify) && $force_minify != "unminify") { $html =   minify_html   ($html); } return !$trim ? $html : trim($html ); }
    function raw_html       ($html, $force_minify = false, $trim = true)  { if (!!get("gemini")) return ""; if (!!get("no_html")) return ''; if ((!!get("minify") || !!get("minify_html" ) || $force_minify) && $force_minify != "unminify") { $html = /*minify_html*/ ($html); } return !$trim ? $html : trim($html ); }
    function raw_js         ($js,   $force_minify = false, $trim = true)  { if (!!get("gemini")) return ""; if (!!get("no_js"))   return ''; if ((!!get("minify") || !!get("minify_js"   ) || $force_minify) && $force_minify != "unminify") { $js   =   minify_js     ($js);   } return !$trim ? $js   : trim($js   ); }
    function raw_css        ($css,  $force_minify = false, $trim = true)  { if (!!get("gemini")) return ""; if (!!get("no_css"))  return ''; if ((!!get("minify") || !!get("minify_css"  ) || $force_minify) && $force_minify != "unminify") { $css  =   minify_css    ($css);  } return !$trim ? $css  : trim($css  ); }
    function raw_php        ($php,  $force_minify = false, $trim = true)  { if (!!get("gemini")) return "";                                  if ((!!get("minify") || !!get("minify_php"  ) || $force_minify) && $force_minify != "unminify") { $php  =   minify_php    ($php);  } return !$trim ? $php  : trim($php  ); }
    function raw_xml        ($xml,  $force_minify = false, $trim = true)  { if (!!get("gemini")) return ""; if (!!get("no_xml"))  return ''; if ((!!get("minify") || !!get("minify_xml"  ) || $force_minify) && $force_minify != "unminify") { $xml  = /*minify_xml*/  ($xml);  } return !$trim ? $xml  : trim($xml  ); }
    
    function include_html   ($filename, $force_minify = false, $silent_errors = auto, $trim = true) { return (has("rss") || !!get("no_html")) ? '' : raw_html   (include_file($filename, $silent_errors), $force_minify, $trim); }
    function include_css    ($filename, $force_minify = false, $silent_errors = auto, $trim = true) { return (has("rss") || !!get("no_css"))  ? '' : raw_css    (include_file($filename, $silent_errors), $force_minify, $trim); }
    function include_js     ($filename, $force_minify = false, $silent_errors = auto, $trim = true) { return (has("rss") || !!get("no_js"))   ? '' : raw_js     (include_file($filename, $silent_errors), $force_minify, $trim); }
        
    // DOM powered html transform

    function html_decode($html)
    {
        return xml_decode($html);/*
        $doc = new \DOMDocument();
        @$doc->loadHTML($html, LIBXML_NOWARNING);
        $sxml = @simplexml_import_dom($doc);
        return xml_decode($sxml);*/
    }
    
    function xml_decode($xml)
    {
        libxml_use_internal_errors(true);

        $e = is_string($xml) ? simplexml_load_string($xml) : $xml;
        
        foreach (libxml_get_errors() as $error) 
        {
            debug_log(json_encode($error));
        }
    
        libxml_clear_errors();

        if (!is_object($e)) return $e;

        $a = array("name" => $e->getName(), "attributes" => array(), "children" => array(), "value" => strval($e));

        foreach ($e->attributes() as $attribute => $value)
        {
            $a["attributes"][] = array("name" => $attribute, "value" => strval($value));
        }

        foreach ($e->children() as $child)
        {
            $a["children"][] = html_decode($child);
        }

        return $a;
    }

    $__raw_dom_parse_debug_i = 0;

    function raw_dom_parse($tree, $parent_node_name = "document", $debug_comments = false)
    {  
        $html = "";

        if (is_array($tree))
        {
            $node               = $tree["value"];
            $node_name          = $tree["name"];
            $node_attributes    = $tree["attributes"];
            $children           = $tree["children"];

            $func_name = str_replace("-", "_", $node_name);
            
            $children_html = "";

            foreach ($children as $child)
            {
                $children_html .= raw_dom_parse($child, $node_name, $debug_comments);
            }

            $was_callable = false;

            foreach (array("dom\\$parent_node_name"."_$func_name", "dom\\$func_name", $parent_node_name."_".$func_name, $func_name) as $dom_func)
            {
                if (is_callable($dom_func))
                {
                    $attributes = array();

                    foreach ($node_attributes as $node_attribute)
                    {
                        $attributes[$node_attribute["name"]] = at($attributes, $node_attribute["name"], array());
                        $attributes[$node_attribute["name"]][] = $node_attribute["value"];
                    }

                    foreach ($attributes as $name => $value)
                    {
                        $attributes[$name] = implode(" ", $value);
                    }

                    $is_regular_params = false;
                    {
                        if (count($attributes) >= 1 && count($attributes) <= 9)
                        {
                            $is_regular_params = true;

                            foreach ($attributes as $name => $value)
                            {
                                if (strlen($name) != 2 || $name[0] != '_' || !is_numeric($name[1]))
                                {
                                    $is_regular_params = false;
                                    break;
                                }
                            }

                            if ($is_regular_params)
                            {
                                $content_index = 0;

                                foreach ($attributes as $name => $value)
                                {
                                    if ($value == "%")
                                    {
                                        $content_index = (int)$name[1];
                                        break;
                                    }
                                }

                                $attributes = array_values($attributes);

                                $attributes = array_merge(
                                    array_slice($attributes, 0, $content_index),
                                    array($children_html.$node),
                                    array_slice($attributes, $content_index + 1)
                                );
                            }
                        }
                    }

                    if ($is_regular_params)
                    {
                        $html .= call_user_func_array($dom_func, $attributes);
                    }
                    else
                    {
                        if ($children_html.$node === "")
                        {
                            $html .= $dom_func(...array_values($attributes));
                        }
                        else
                        {
                            $html .= $dom_func($children_html.$node, ...array_values($attributes));
                        }
                    }

                    $was_callable = true;
                    break;
                }
            }
            
            if (!$was_callable)
            {
                //ob_end_clean();  die("<pre>".htmlentities("dom\\$parent_node_name"."_$func_name")."</pre>");
            }
        }
        else
        {
            $html = $tree;
        }

        return $html;
    }

    function raw_dom($html, $debug_comments = false)
    {
        return raw_dom_parse(html_decode($html), "document", $debug_comments);
    }

    /*
     * CSS tags
     */
     
    $hook_css_vars = array(); function hook_css_var($var) { global $hook_css_vars; $hook_css_vars[$var] = $var; return "DOM_HOOK_CSS_VAR_".$var; }
    $hook_css_envs = array(); function hook_css_env($var) { global $hook_css_envs; $hook_css_envs[$var] = $var; return "DOM_HOOK_CSS_ENV_".$var; }

    function css_postprocess($css)
    {
        global $hook_css_vars;
        global $hook_css_envs;
    
        foreach ($hook_css_vars as $var) $css = str_replace("DOM_HOOK_CSS_VAR_".$var, get($var), $css);
        foreach ($hook_css_envs as $var) $css = str_replace("DOM_HOOK_CSS_ENV_".$var, get($var), $css);
    
        return $css;
    }

    function css_name($name) { return trim(str_replace("_","-",$name)); }

    function css_var($var, $val = false, $pre_processing = false, $pan = auto) { if (auto === $pan) $pan = get("env_var_default_tab", 32); if (false === $val) return 'var(--'.css_name($var).')';                                                 return pan('--'.css_name($var) . ': ', $pan) . $val . '; '; }
    function css_env($var, $val = false, $pre_processing = false, $pan = auto) { if (auto === $pan) $pan = get("env_var_default_tab", 32); if (false === $val) return ($pre_processing ? hook_css_env($var) : get($var)); set($var, $val); return pan('--'.css_name($var) . ': ', $pan) . $val . '; '/*.((false !== stripos($var,"_unitless")) ? "" : css_env($var."_unitless", str_replace(array("px","%","vw","vh","cm","em","rem","pt","deg","rad"), array("","","","","","","","","",""), $val)))*/; }

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
            $var = ($pre_processing ? hook_css_env($var) : get($var,$var));

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
            $var = ($pre_processing ? hook_css_env($var) : get($var,$var));

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
    
    function env        ($var, $val = false, $pre_processing = false, $pan = auto) { return css_env      ($var, $val, $pre_processing, $pan); }
    function env_add    ($vars,              $pre_processing = false, $pan = auto) { return css_env_add  ($vars,      $pre_processing, $pan); }
    function env_mul    ($vars,              $pre_processing = false, $pan = auto) { return css_env_mul  ($vars,      $pre_processing, $pan); }
    
    
    /*
     * Special HTML components
     */
    
    function if_browser($condition, $html) { return (has("rss") || !!get("gemini")) ? '' : ('<!--[if '.$condition.']>' . $html . '<![endif]-->'); }

    #endregion
    #region WIP API : DOM : HTML COMPONENTS : DOCUMENT ROOT
    ######################################################################################################################################

    function jsonfeed($json = false)
    {
        return placeholder("DOM_HOOK_JSONFEED_".($json ? 1 : 0));
    }

    function _jsonfeed($json = false)
    {
        $profiler = debug_track_timing();
        
    //  TODO : https://jsonfeed.org/mappingrssandatom => Only html hooks ? hooks => array => json => json feed
    //  TODO : https://daringfireball.net/feeds/json
    
        if ("json" == get("rss", false))
        {
            if ($json === false)
            {
                $json = json_encode(rss_sanitize(get("rss_items", array())));
            }
            
            return $json;
        }
    }
    
    function rss($xml = false)
    {
        return placeholder("DOM_HOOK_RSS_".($xml ? 1 : 0));
    }

    function _rss($xml = false)
    {
        $profiler = debug_track_timing();
        
        if ("rss" == get("doctype", "html"))
        {
            if ($xml === false)
            {
                $xml = rss_channel(
                
                            rss_title           (get("title"))
                . eol() .   rss_description     (get("keywords", get("title")))
                . eol() .   rss_link            (get("url")."/"."rss")
                . eol() .   rss_lastbuilddate   ()
                . eol() .   rss_copyright       ()

                . eol() .   rss_image(
                            
                                        rss_url     (get("url")."/".get("image"))
                            . eol() .   rss_title   (get("title"))
                            . eol() .   rss_link    (get("url")."/"."rss")
                            )

                . eol() .   wrap_each(get("rss_items", array()), eol(), "rss_item_from_item_info", false)
                );
            }

            $path_css = path("css/rss.css");

            return  ''
          /*.       '<?xml version="1.0" encoding="'.get("encoding", "utf-8").'" ?>'    */
            .       '<?xml version="1.0" encoding="'.strtoupper(get("encoding", "utf-8")).'"?>'
            .       (!!$path_css ? ('<?xml-stylesheet href="'.$path_css.'" type="text/css" ?>') : '')
          /*.       '<rss version="2.0" xmlns:atom="https://www.w3.org/2005/Atom" xmlns:media="https://search.yahoo.com/mrss/">'    */
            .       '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">'
            . eol()   
            . eol() . $xml
            . eol()   
            . eol() . '</rss>';
        }
    }

    function tile($xml = false)
    {
        return placeholder("DOM_HOOK_TILE_".($xml ? 1 : 0));
    }

    function _tile($xml = false)
    {
        $profiler = debug_track_timing();
        
        if ("tile" == get("doctype", "html"))
        {
            if ($xml === false)
            {
                foreach (get("rss_items", array()) as $item_info)
                {
                    $xml = tile_item_from_item_info($item_info);
                    break;
                }
            }

            return '<?xml version="1.0" encoding="'.get("encoding", "utf-8").'" ?>'
            . eol()   
            . eol() . $xml
            . eol();
        }
    }
    function diff($old, $new){
        $matrix = array();
        $maxlen = 0;
        foreach($old as $oindex => $ovalue){
            $nkeys = array_keys($new, $ovalue);
            foreach($nkeys as $nindex){
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                    $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if($matrix[$oindex][$nindex] > $maxlen){
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }   
        }
        if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
        return array_merge(
            diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }
    
    function html_diff($old, $new){
        $ret = '';
        $diff = diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
        foreach($diff as $k){
            if(is_array($k))
                $ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
                    (!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
            else $ret .= $k . ' ';
        }
        return $ret;
    }

    function parse_delayed_components($html)
    {
        $profiler = debug_track_timing();

    //  Lazy html generation

        if ("html" == get("doctype", "html") && !has("ajax"))
        {
            while (true)
            {
                $delayed_components = get("delayed_components", array());
                del("delayed_components");

                if (count($delayed_components) <= 0) break;

                $priorities = array();

                foreach ($delayed_components as $index => $delayed_component_and_param)
                {
                    $priorities[(int)$delayed_component_and_param[2]] = true;
                }

                ksort($priorities);

                foreach ($priorities as $priority => $_)
                {
                    foreach ($delayed_components as $index => $delayed_component_and_param)
                    {
                        if ($priority != $delayed_component_and_param[2]) continue;

                        $delayed_component = $delayed_component_and_param[0];
                        $param             = $delayed_component_and_param[1];
    
                        $fn_delayed_component = $delayed_component;
                        if (!is_callable($fn_delayed_component)) $fn_delayed_component = "dom\\$fn_delayed_component";

                        $iterations = 0;

                        while (true)
                        {
                            $fn_get_placeholder_content_cb = function() use ($fn_delayed_component, $param, $html)
                            {
                                $content = "";
                                {
                                    if (is_array($param))
                                    {   
                                        $content = call_user_func_array($fn_delayed_component, array_merge($param, array($html)));
                                    }
                                    else
                                    {   
                                        $content = call_user_func($fn_delayed_component, $param, $html);
                                    }
                                }

                                return $content;
                            };

                          //$content = $fn_get_placeholder_content_cb();
                          //$html = placeholder_replace($delayed_component.$index, $content, $html, "div");
                          //break;

                            $new_html = placeholder_replace_once($delayed_component.$index, $fn_get_placeholder_content_cb, $html, "div");
                            if ($new_html == $html) break;

                            if (++$iterations > 99)
                            {
                                ob_clean();
                                die("<pre> DIFF ".html_diff($new_html, $html)."</pre>");
                            }

                            $html = $new_html;
                        }
                    }
                }
            }
        }

        return $html;
    }

    function html($html = "", $attributes = false)
    {
        debug_log();
        $debug_console = !get("debug") ? "" : debug_console();

        $profiler = debug_track_timing();

        // TODO DO THIS

        $no_head = (false === stripos($html, "<head>") && false === stripos($html, "<head "));
        $no_body = (false === stripos($html, "<body>") && false === stripos($html, "<body "));

             if ($no_head && $no_body)  { $html = head().body($html); }
        else if ($no_head)              { $html = head().     $html;  }
        else if ($no_body)              { $html =        body($html); }
        
        if (has("ajax")) $_POST = array();

        if (!!get("gemini"))
        {
            $html = parse_delayed_components($html);
            
            if (!!get("debug")) $html = "<html><head><meta name=\"color-scheme\" content=\"light dark\"></head><body><pre>$html";
            if (!!get("debug")) $html .= $debug_console;

            return $html;
        }
        else if ("html" == get("doctype", "html"))
        {
            if (!has("ajax"))
            {
                // Lazy html generation

                $html = parse_delayed_components($html);

                // Clean html
                                        $attributes = attributes_add($attributes, attributes(attr("lang",   get("html-language", content_language()))   ));
                if (get("modernizr"))   $attributes = attributes_add($attributes, attributes(attr("class",  "no-js")                                    ));
                if (AMP())              $attributes = attributes_add($attributes, attributes(attr("amp",    "amp")                                      ));

                //  Return html

                return  raw_html('<!doctype html>'.comment("Welcome my fellow web developer!").'<html'.attributes_as_string($attributes).'>'.' ').
                        $html.eol().$debug_console.
                        raw_html('</html>'.comment("DOM.PHP ".version));
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
        $profiler = debug_track_timing();        

        $f = get("doctype", "html");
        if (!is_callable($f)) $f = "dom\\$f";

        return call_user_func($f, $html);
    }

    #endregion
    #region WIP API : DOM : HTML COMPONENTS : MARKUP : HEAD, SCRIPTS & STYLES
    ######################################################################################################################################

    /**
     * 11 <meta http-equiv> default-style x-dns-prefetch-control accept-ch delegate-ch content-security-policy origin-trial content-type
     */
    function head_pragma_directives()
    {
        return  eol().comment("DOM Head pragma directives") 
            .   meta_charset('utf-8')
            .   eol()
            .   (AMP() ? '' : meta_http_equiv('Content-type', 'text/html;charset=utf-8'))
            .   meta('viewport', 'width=device-width, minimum-scale=1, initial-scale=1')
            ;        
    }

    /* 10 Title */
    function head_title()
    {
        return title();
    }

    /* 9 */
    function head_preconnect_hints()
    {
        return  eol().comment("DOM Head preconnect hints"). 
                (!get("unsplash-preconnect") ? '' : '<link rel="preconnect" href="https://source.unsplash.com">');
    }

    /* 8 */
    function head_asynchronous_scripts($scripts = true)
    {
        return  (AMP() ? "" : (eol().comment("DOM Head Asynchronous scripts"))). 
                scripts_head($scripts);
    }

    /* 7 */
    function head_import_styles()
    {
        return eol().comment("DOM Head Import styles");
    }

    /* 6 */
    function head_synchronous_scripts($scripts = true)
    {
        return eol().comment("DOM Head Synchronous scripts");
    }

    /* 5 */
    function head_synchronous_styles($async_css = false, $styles = true)
    {
        $path_css = !get("dom-auto-include-css") ? false : path_coalesce(
            "./css/main.css",
            "./main.css",
            "./css/screen.css",
            "./screen.css"
            );

        return 
            eol().comment("DOM Head Synchronous styles").
            (!AMP() ? ("".
                eol().comment("Placeholder for 3rd parties who look for a css <link> in order to insert something before").
                eol().'<link rel="stylesheet" type="text/css" media="screen"/>'.
            "") : "").
            (AMP() ? "" : (eol().comment("DOM Head styles"))).
            link_styles($async_css). // if $async_css == false otherwise move to #2
            (!$styles ? "" : styles()).
            (!$path_css ? "" : (
                (AMP() ? "" : (eol(). comment("DOM Head project-specific main stylesheet"))).   (!get("htaccess_rewrite_php") ? (
                style_file($path_css).                                                          "") : (
                link_style($path_css).                                                          "")).
            ""));
    }

    /* 4 */
    function head_preload_hints()
    {
        return  eol().comment("Preloaded images").
                link_rel_image_preloads();
    }

    /* 3 */
    function head_deferred_scripts($scripts = true)
    {
        if (!$scripts) return "";

        return  eol().comment("DOM Head Deferred scripts").
                //script_google_analytics should be call here if needed
                "";
    }

    /* 2 */
    function head_prefetch_and_prerender_hints()
    {
        return  eol().comment("DOM Head Prefetch and prerender hints").
                eol().comment("Prefetched pages").
                link_rel_prefetchs();   
    }

    /* 1 */
    function head_everything_else($scripts = true)
    {
        return  eol().comment("DOM Head Metadata").
                      metas().
                eol().link_rel_manifest().      (!get("webmentions") ? "" : (
                eol().link_rel_webmentions().   "")).
                eol().link_rel_webauth().
                eol().link_rel_shareopenly().
                eol().link_rel("sitemap", path_coalesce("sitemap.xml", "/sitemap.xml", "sitemap", "/sitemap/"), "application/xml").
                "";
    }
    
    function head_boilerplate($async_css = false, $styles = true, $scripts = true)
    {
        $profiler = debug_track_timing();

        return // Head ordering : https://rviscomi.github.io/capo.js/user/rules/

            eol().head_pragma_directives().
            eol().head_title(). 
            eol().head_preconnect_hints().
            eol().head_asynchronous_scripts($scripts).
            eol().head_import_styles().
            eol().head_synchronous_scripts($scripts).
            eol().head_synchronous_styles($async_css, $styles).
            eol().head_preload_hints().
            eol().head_deferred_scripts($scripts).
            eol().head_prefetch_and_prerender_hints().
            eol().head_everything_else($scripts).            
            "";
    }

    function head($html = false, $async_css = false, $styles = true, $scripts = true)
    { 
        $profiler = debug_track_timing();

        if (false === $html)
        {
            $html = head_boilerplate($async_css, $styles, $scripts);
        }

        $html = css_postprocess($html);

        if (get("support_service_worker", false))
        {
            hook_amp_require("install-serviceworker");
        }

        $amp_scripts = "";

        if (AMP())
        {
            $amp_scripts =
                
                eol() . comment("DOM AMP Styles").
                eol() . '<style amp-custom>'.delayed_component("_amp_css").eol().'</style>'.                        
                eol() . "<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>".

                eol() . comment("DOM AMP Scripts").
                eol() . '<script async src="https://cdn.ampproject.org/v0.js"></script>'.

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

        if (!!get("gemini")) return "";
        
        return tag('head', $html.$amp_scripts); 
    }

    /**
     * SHARING - https://shareopenly.org
     */

    function link_rel_shareopenly()
    {
        $text = str_replace("TEXT", "{text}", 
                str_replace("URL",  "{url}", urlencode("Bonjour! TEXT - URL")));

        return link_rel("share-url", "https://".get("mastodon_domain", "mastodon.social")."/share?text=$text");
    }

    function a_shareopenly($html = auto, $url = auto, $attributes = false)
    {
        $html = $html !== auto ? $html : DOM_I18N_SHARE;
        $url  = $url  !== auto ? $url  : live_url();

        $url  = urlencode($url);
        $text = urlencode("Happy to share!");

        return a($html, "https://shareopenly.org/share/?url=$url&text=$text", $attributes);
    }

    /* DELAYED COMPONENTS */

    function delayed_component($callback, $arg = false, $priority = 1, $eol = 1)
    {
        // ! DIRTY HACK
        $callback = str_replace("_dom\\", "_", $callback);

        $delayed_components = get("delayed_components", array());
        $index = count($delayed_components);
        set("delayed_components", array_merge($delayed_components, array(array($callback, $arg, $priority))));
        return placeholder($callback.$index, $eol);
    }
    
    function script_amp_install_serviceworker   () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_iframe                  () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_sidebar                 () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_position_observer       () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_animation               () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_form                    () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_youtube                 () { return delayed_component("_".__FUNCTION__, false, 2); }
    function script_amp_script                  () { return delayed_component("_".__FUNCTION__, false, 2); }

    function _script_amp_install_serviceworker  () { return has_amp_requirement("install-serviceworker") ? (eol(1) . '<script async custom-element="amp-install-serviceworker' . '" src="https://cdn.ampproject.org/v0/amp-install-serviceworker' . '-0.1.js"></script>') : ""; }
    function _script_amp_iframe                 () { return has_amp_requirement("iframe")                ? (eol(1) . '<script async custom-element="amp-iframe'                . '" src="https://cdn.ampproject.org/v0/amp-iframe'                . '-0.1.js"></script>') : ""; }
    function _script_amp_sidebar                () { return has_amp_requirement("sidebar")               ? (eol(1) . '<script async custom-element="amp-sidebar'               . '" src="https://cdn.ampproject.org/v0/amp-sidebar'               . '-0.1.js"></script>') : ""; }
    function _script_amp_position_observer      () { return has_amp_requirement("position-observer")     ? (eol(1) . '<script async custom-element="amp-position-observer'     . '" src="https://cdn.ampproject.org/v0/amp-position-observer'     . '-0.1.js"></script>') : ""; }
    function _script_amp_animation              () { return has_amp_requirement("animation")             ? (eol(1) . '<script async custom-element="amp-animation'             . '" src="https://cdn.ampproject.org/v0/amp-animation'             . '-0.1.js"></script>') : ""; }
    function _script_amp_form                   () { return has_amp_requirement("form")                  ? (eol(1) . '<script async custom-element="amp-form'                  . '" src="https://cdn.ampproject.org/v0/amp-form'                  . '-0.1.js"></script>') : ""; }
    function _script_amp_youtube                () { return has_amp_requirement("youtube")               ? (eol(1) . '<script async custom-element="amp-youtube'               . '" src="https://cdn.ampproject.org/v0/amp-youtube'               . '-0.1.js"></script>') : ""; }
    function _script_amp_script                 () { return has_amp_requirement("script")                ? (eol(1) . '<script async custom-element="amp-script'                . '" src="https://cdn.ampproject.org/v0/amp-script'                . '-0.1.js"></script>') : ""; }

    function title  ($title = false) { return delayed_component("_".__FUNCTION__, $title); }
    function _title ($title = false) { return ($title === false) ? tag('title', get("title") . ((get("heading") != '') ? (' - '.get("heading")) : '')) : tag('title', $title); }

    function link_rel_prefetch($url)
    {
        return link_rel("prefetch", $url);
    }

    function link_rel_image_preload($url)
    {
        $mime = "image/png";
        {
            $size = cached_getimagesize($url);

            if (is_array($size) && array_key_exists("mime", $size))
            {
                $mime = $size["mime"];
            }
            else
            {
                $ext  = "png";
                $pos  =  stripos($url, "?"); if (false !== $pos) $ext = substr($url, 0, $pos);
                $pos  = strripos($url, "."); if (false !== $pos) $ext = substr($url, $pos + 1);
                $mime = "image/$ext";
            }
        }

        return link_rel("preload", $url, array("as" => "image", "type" => $mime));
    }

    /* COOKIES */

    function js_storage()
    {
        if (has("ajax")) return '';

        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            dom.on_ready(function() {
                
                window.addEventListener("storage", function() {
                    
                    dom.log("Storage", JSON.parse(window.localStorage.getItem("dom")));
                });
                
            });
            
            function dom_storage_get(name) {
                
                var dom_storage = window.localStorage.getItem("dom");

                /*dom.log("Storage before get", JSON.parse(window.localStorage.getItem("dom")));*/

                if (!dom_storage)
                {
                    return false;
                }

                var jsonObject = {}; try { jsonObject = JSON.parse(dom_storage); } catch { jsonObject = {}; }
                if (typeof jsonObject == "undefined" || jsonObject == false || jsonObject == null) jsonObject = {};
                
                /*dom.log("JSONOBJ", jsonObject, name, jsonObject[name]);*/
                if (jsonObject[name] == undefined) return false;

                return jsonObject[name];
            }

            function dom_storage_set(name, value) {

                var dom_storage = window.localStorage.getItem("dom");

                if (!dom_storage)
                {
                    dom_storage = "{}";
                    window.localStorage.setItem("dom", dom_storage);                    
                }

                var jsonObject = {}; try { jsonObject = JSON.parse(dom_storage); } catch { jsonObject = {}; }
                if (typeof jsonObject == "undefined" || jsonObject == false || jsonObject == null) jsonObject = {};
                
                jsonObject[name] = value;
                dom_storage = JSON.stringify(jsonObject);
                window.localStorage.setItem("dom", dom_storage);

                /*dom.log("Storage after set", JSON.parse(window.localStorage.getItem("dom")));*/
            }

            dom.set = dom_storage_set;
            dom.get = dom_storage_get;

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

/* WEB MENTIONS */

    /**
     * Class MentionClient supports webmention, pingback and endpoint discovery.
     * From: https://indieweb.org/Webmention / https://github.com/indieweb/mention-client-php
     * @package IndieWeb
     */
    class MentionClient
    {
        private static $_debugEnabled = false;

        private $_sourceBody;

        /**
         * @var array set of links to be checked for mentions.
         */
        private $_links = array();

        private $_headers = array();
        private $_body = array();
        private $_rels = array();
        private $_supportsPingback = array();
        private $_supportsWebmention = array();
        private $_pingbackServer = array();
        private $_webmentionServer = array();

        private static $_proxy = false;
        private static $_userAgent = false;

        public $usemf2 = true; // for testing, can set this to false to avoid using the Mf2 parser

        /**
         * @param string $proxy_string
         * @codeCoverageIgnore
         */
        public function setProxy($proxy_string)
        {
            self::$_proxy = $proxy_string;
        }

        /**
         * @param string $user_agent
         * @codeCoverageIgnore
         */
        public static function setUserAgent($user_agent)
        {
            self::$_userAgent = $user_agent;
        }

        /**
         * Looks for pingback URL target. sets attributes on $this->c .
         * @param string $target URL
         * @return mixed setting $this->c('pingbackServer', $target);
         */
        public function discoverPingbackEndpoint($target)
        {

            if ($this->c('supportsPingback', $target) === null) {
                $this->c('supportsPingback', $target, false);

                // First try a HEAD request and look for X-Pingback header
                if (!$this->c('headers', $target)) {
                    $head = static::_head($target);
                    $target = $head['url'];
                    $this->c('headers', $target, $head['headers']);
                }

                $headers = $this->c('headers', $target);
                if (array_key_exists('X-Pingback', $headers)) {
                    self::_debug("discoverPingbackEndpoint: Found pingback server in header");
                    $this->c('pingbackServer', $target, $headers['X-Pingback']);
                    $this->c('supportsPingback', $target, true);
                } else {
                    self::_debug("discoverPingbackEndpoint: No pingback server found in header, looking in the body now");
                    if (!$this->c('body', $target)) {
                        $body = static::_get($target);
                        $target = $body['url'];
                        $this->c('body', $target, $body['body']);
                        $this->_parseBody($target, $body['body']);
                    }
                    if ($rels = $this->c('rels', $target)) {
                        // If the mf2 parser is present, then rels will have been set, and use that instead
                        if (count($rels)) {
                            if (array_key_exists('pingback', $rels)) {
                                $this->c('pingbackServer', $target, $rels['pingback'][0]);
                                $this->c('supportsPingback', $target, true);
                            }
                        }
                    } else {
                        $body = $this->c('body', $target);
                        if (preg_match("/<link rel=\"pingback\" href=\"([^\"]+)\" ?\/?>/i", $body, $match)) {
                            $this->c('pingbackServer', $target, $match[1]);
                            $this->c('supportsPingback', $target, true);
                        }
                    }
                }

                self::_debug("discoverPingbackEndpoint: pingback server: " . $this->c('pingbackServer', $target));
            }

            return $this->c('pingbackServer', $target);
        }

        /**
         * Sends pingback to endpoints
         * @param $endpoint string URL for pingback listener
         * @param $source string originating post URL
         * @param $target string URL like permalink of target post
         * @return bool Successful response MUST contain a single string
         */
        public static function sendPingbackToEndpoint($endpoint, $source, $target)
        {
            self::_debug("sendPingbackToEndpoint: Sending pingback now!");

            $payload = static::xmlrpc_encode_request('pingback.ping', array($source,  $target));

            $response = static::_post($endpoint, $payload, array(
                'Content-type: application/xml'
            ));

            if ($response['code'] != 200 || empty($response['body']))
                return false;

            // collapse whitespace just to be safe
            $body = strtolower(preg_replace('/\s+/', '', $response['body']));

            // successful response MUST contain a single string
            return $body && strpos($body, '<fault>') === false && strpos($body, '<string>') !== false;
        }

        /**
         * Public function to send pingbacks to $targetURL
         * @param $sourceURL string URL for source of pingback
         * @param $targetURL string URL for destination of pingback
         * @return bool runs sendPingbackToEndpoint().
         * @see MentionClient::sendPingbackToEndpoint()
         */
        public function sendPingback($sourceURL, $targetURL)
        {

            // If we haven't discovered the pingback endpoint yet, do it now
            if ($this->c('supportsPingback', $targetURL) === null) {
                $this->discoverPingbackEndpoint($targetURL);
            }

            $pingbackServer = $this->c('pingbackServer', $targetURL);
            if ($pingbackServer) {
                self::_debug("sendPingback: Sending to pingback server: " . $pingbackServer);
                return self::sendPingbackToEndpoint($pingbackServer, $sourceURL, $targetURL);
            } else {
                return false;
            }
        }

        /**
         * Parses body of html. Protected method.
         * @param $target string the URL of the target page
         * @param $html string the HTML of page
         */
        protected function _parseBody($target, $html)
        {
            if (class_exists('\Mf2\Parser') && $this->usemf2) {
                $parser = new \Mf2\Parser($html, $target);
                list($rels, $alternates) = $parser->parseRelsAndAlternates();
                $this->c('rels', $target, $rels);
            }
        }

        /**
         * finds webmention endpoints in the body. protected function
         * @param $body
         * @param string $targetURL
         * @return bool
         */
        protected function _findWebmentionEndpointInHTML($body, $targetURL = false)
        {
            $endpoint = false;

            $body = preg_replace('/<!--(.*)-->/Us', '', $body);
            if (
                preg_match('/<(?:link|a)[ ]+href="([^"]*)"[ ]+rel="[^" ]* ?webmention ?[^" ]*"[ ]*\/?>/i', $body, $match)
                || preg_match('/<(?:link|a)[ ]+rel="[^" ]* ?webmention ?[^" ]*"[ ]+href="([^"]*)"[ ]*\/?>/i', $body, $match)
            ) {
                $endpoint = $match[1];
            }
            if ($endpoint !== false && $targetURL && function_exists('\Mf2\resolveUrl')) {
                // Resolve the URL if it's relative
                $endpoint = \Mf2\resolveUrl($targetURL, $endpoint);
            }
            return $endpoint;
        }

        /**
         * @param $link_header
         * @param string $targetURL
         * @return bool
         */
        protected function _findWebmentionEndpointInHeader($link_header, $targetURL = false)
        {
            $endpoint = false;
            if (preg_match('~<((?:https?://)?[^>]+)>; rel="?(?:https?://webmention.org/?|webmention)"?~', $link_header, $match)) {
                $endpoint = $match[1];
            }
            if ($endpoint && $targetURL && function_exists('\Mf2\resolveUrl')) {
                // Resolve the URL if it's relative
                $endpoint = \Mf2\resolveUrl($targetURL, $endpoint);
            }
            return $endpoint;
        }

        /**
         * Finds webmention endpoints at URL. Examines header request.
         * Also modifies $this->c to indicate if $target accepts webmention
         * @param $target string the URL to examine for endpoints.
         * @return mixed
         */
        public function discoverWebmentionEndpoint($target)
        {
            if ($this->c('supportsWebmention', $target) === null) {

                $this->c('supportsWebmention', $target, false);

                // First try a HEAD request and look for Link header
                if (!$this->c('headers', $target)) {
                    $head = static::_head($target);
                    $target = $head['url'];
                    $this->c('headers', $target, $head['headers']);
                }

                $headers = $this->c('headers', $target);

                $link_header = false;

                if (array_key_exists('Link', $headers)) {
                    if (is_array($headers['Link'])) {
                        $link_header = implode(", ", $headers['Link']);
                    } else {
                        $link_header = $headers['Link'];
                    }
                }

                if ($link_header && ($endpoint = $this->_findWebmentionEndpointInHeader($link_header, $target))) {
                    self::_debug("discoverWebmentionEndpoint: Found webmention server in header");
                    $this->c('webmentionServer', $target, $endpoint);
                    $this->c('supportsWebmention', $target, true);
                } else {
                    self::_debug("discoverWebmentionEndpoint: No webmention server found in header, looking in body now");
                    if (!$this->c('body', $target)) {
                        $body = static::_get($target);
                        $target = $body['url'];
                        $this->c('body', $target, $body['body']);
                        $this->_parseBody($target, $body['body']);
                    }
                    if ($rels = $this->c('rels', $target)) {
                        // If the mf2 parser is present, then rels will have been set, so use that instead
                        if (count($rels)) {
                            if (array_key_exists('webmention', $rels)) {
                                $endpoint = $rels['webmention'][0];
                                $this->c('webmentionServer', $target, $endpoint);
                                $this->c('supportsWebmention', $target, true);
                            } elseif (array_key_exists('http://webmention.org/', $rels) || array_key_exists('http://webmention.org', $rels)) {
                                $endpoint = $rels[array_key_exists('http://webmention.org/', $rels) ? 'http://webmention.org/' : 'http://webmention.org'][0];
                                $this->c('webmentionServer', $target, $endpoint);
                                $this->c('supportsWebmention', $target, true);
                            }
                        }
                    } else {
                        if ($endpoint = $this->_findWebmentionEndpointInHTML($this->c('body', $target), $target)) {
                            $this->c('webmentionServer', $target, $endpoint);
                            $this->c('supportsWebmention', $target, true);
                        }
                    }
                }

                self::_debug("discoverWebmentionEndpoint: webmention server: " . $this->c('webmentionServer', $target));
            }

            return $this->c('webmentionServer', $target);
        }

        /**
         * Static function can send a webmention to an endpoint via static::_post
         * @param $endpoint string URL of endpoint detected
         * @param $source string URL of originating post (other server will check probably)
         * @param $target string URL of target post
         * @param array $additional extra optional stuff that will be included in payload.
         * @return array
         */
        public static function sendWebmentionToEndpoint($endpoint, $source, $target, $additional = array())
        {

            self::_debug("sendWebmentionToEndpoint: Sending webmention now!");

            $payload = http_build_query(array_merge(array(
                'source' => $source,
                'target' => $target
            ), $additional));

            return static::_post($endpoint, $payload, array(
                'Content-type: application/x-www-form-urlencoded',
                'Accept: application/json, */*;q=0.8'
            ));
        }

        /**
         * Sends webmention to a target url. may use
         * @param $sourceURL
         * @param $targetURL
         * @param array $additional
         * @return array|bool
         * @see MentionClient::sendWebmentionToEndpoint()
         */
        public function sendWebmention($sourceURL, $targetURL, $additional = array())
        {

            // If we haven't discovered the webmention endpoint yet, do it now
            if ($this->c('supportsWebmention', $targetURL) === null) {
                $this->discoverWebmentionEndpoint($targetURL);
            }

            $webmentionServer = $this->c('webmentionServer', $targetURL);
            if ($webmentionServer) {
                self::_debug("sendWebmention: Sending to webmention server: " . $webmentionServer);
                return self::sendWebmentionToEndpoint($webmentionServer, $sourceURL, $targetURL, $additional);
            } else {
                return false;
            }
        }

        /**
         * Scans outgoing links in block of text $input.
         * @param $input string html block.
         * @return array array of unique links or empty.
         */
        public static function findOutgoingLinks($input)
        {
            // Find all outgoing links in the source
            if (is_string($input)) {
                preg_match_all("/<a[^>]+href=.(https?:\/\/[^'\"]+)/i", $input, $matches);
                return array_unique($matches[1]);
            } elseif (is_array($input) && array_key_exists('items', $input) && array_key_exists(0, $input['items'])) {
                $links = array();

                // Find links in the content HTML
                $item = $input['items'][0];

                if (array_key_exists('content', $item['properties'])) {
                    if (is_array($item['properties']['content'][0])) {
                        $html = $item['properties']['content'][0]['html'];
                        $links = array_merge($links, self::findOutgoingLinks($html));
                    } else {
                        $text = $item['properties']['content'][0];
                        $links = array_merge($links, self::findLinksInText($text));
                    }
                }

                // Look at all properties of the item and collect all the ones that look like URLs
                $links = array_merge($links, self::findLinksInJSON($item));

                return array_unique($links);
            } else {
                return array();
            }
        }

        /**
         * find all links in text.
         * @param $input string text block
         * @return mixed array of links in text block.
         */
        public static function findLinksInText($input)
        {
            preg_match_all('/https?:\/\/[^ ]+/', $input, $matches);
            return array_unique($matches[0]);
        }

        /**
         * find links in JSON input string.
         * @param $input string JSON object.
         * @return array of links in JSON object.
         */
        public static function findLinksInJSON($input)
        {
            $links = array();
            // This recursively iterates over the whole input array and searches for
            // everything that looks like a URL regardless of its depth or property name
            foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($input)) as $key => $value) {
                if (substr($value, 0, 7) == 'http://' || substr($value, 0, 8) == 'https://')
                    $links[] = $value;
            }
            return $links;
        }

        /**
         * Tries to send webmention and pingbacks to each link on $sourceURL. Depends on Microformats2
         * @param $sourceURL string URL to examine to send mentions to
         * @param bool $sourceBody if true will search for outgoing links with this (string).
         * @return int
         * @see \Mf2\parse
         */
        public function sendMentions($sourceURL, $sourceBody = false)
        {
            if ($sourceBody) {
                $this->_sourceBody = $sourceBody;
                $this->_links = self::findOutgoingLinks($sourceBody);
            } else {
                $body = static::_get($sourceURL);
                $this->_sourceBody = $body['body'];
                $parsed = \Mf2\parse($this->_sourceBody, $sourceURL);
                $this->_links = self::findOutgoingLinks($parsed);
            }

            $totalAccepted = 0;

            foreach ($this->_links as $target) {
                self::_debug("sendMentions: Checking $target for webmention and pingback endpoints");

                if ($this->sendFirstSupportedMention($sourceURL, $target)) {
                    $totalAccepted++;
                }
            }

            return $totalAccepted;
        }

        /**
         * @param $source
         * @param $target
         * @return bool|string
         */
        public function sendFirstSupportedMention($source, $target)
        {

            $accepted = false;

            // Look for a webmention endpoint first
            if ($this->discoverWebmentionEndpoint($target)) {
                $result = $this->sendWebmention($source, $target);
                if (
                    $result &&
                    ($result['code'] == 200
                        || $result['code'] == 201
                        || $result['code'] == 202)
                ) {
                    $accepted = 'webmention';
                }
                // Only look for a pingback server if we didn't find a webmention server
            } else if ($this->discoverPingbackEndpoint($target)) {
                $result = $this->sendPingback($source, $target);
                if ($result) {
                    $accepted = 'pingback';
                }
            }

            return $accepted;
        }

        /**
         * Enables debug messages to appear during activity. Not recommended for production use.
         * @codeCoverageIgnore
         */
        public static function enableDebug()
        {
            self::$_debugEnabled = true;
        }
        /**
         * @codeCoverageIgnore
         */
        private static function _debug($msg)
        {
            if (self::$_debugEnabled)
                //echo "\t" . $msg . "\n";
                error_log("\t" . $msg . "\n");
        }

        /**
         * @param $url
         * @return array
         * @codeCoverageIgnore
         */
        protected static function _head($url, $headers = array())
        {
            if (self::$_userAgent)
                $headers[] = 'User-Agent: ' . self::$_userAgent;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            // ADD ---------------------------------------------->
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);   
            // ADD ---------------------------------------------->
            if (self::$_proxy) curl_setopt($ch, CURLOPT_PROXY, self::$_proxy);
            $response = curl_exec($ch);

            update_dependency_graph($url);

            return array(
                'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'headers' => self::_parse_headers(trim($response)),
                'url' => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)
            );
        }

        /**
         * Protected static function
         * @param $url string URL to grab through curl.
         * @return array with keys 'code' 'headers' and 'body'
         * @codeCoverageIgnore
         */
        protected static function _get($url, $headers = array())
        {
            if (self::$_userAgent)
                $headers[] = 'User-Agent: ' . self::$_userAgent;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            // ADD ---------------------------------------------->
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);   
            // ADD ---------------------------------------------->
            if (self::$_proxy) curl_setopt($ch, CURLOPT_PROXY, self::$_proxy);
            $response = curl_exec($ch);
            
            update_dependency_graph($url);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            return array(
                'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'headers' => self::_parse_headers(trim(substr($response, 0, $header_size))),
                'body' => substr($response, $header_size),
                'url' => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)
            );
        }

        /**
         * @param $url
         * @param $body
         * @param array $headers
         * @return array
         * @codeCoverageIgnore
         */
        protected static function _post($url, $body, $headers = array())
        {
            if (self::$_userAgent)
                $headers[] = 'User-Agent: ' . self::$_userAgent;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, true);
            // ADD ---------------------------------------------->
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);   
            // ADD ---------------------------------------------->
            if (self::$_proxy) curl_setopt($ch, CURLOPT_PROXY, self::$_proxy);
            $response = curl_exec($ch);

            update_dependency_graph($url);

            self::_debug($response);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            return array(
                'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'headers' => self::_parse_headers(trim(substr($response, 0, $header_size))),
                'body' => substr($response, $header_size)
            );
        }

        /**
         * Protected static function to parse headers.
         * @param $headers
         * @return array
         */
        protected static function _parse_headers($headers)
        {
            $retVal = array();
            $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $headers));
            foreach ($fields as $field) {
                if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                    $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', function ($m) {
                        return strtoupper($m[0]);
                    }, strtolower(trim($match[1])));
                    // If there's already a value set for the header name being returned, turn it into an array and add the new value
                    $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', function ($m) {
                        return strtoupper($m[0]);
                    }, strtolower(trim($match[1])));
                    if (isset($retVal[$match[1]])) {
                        if (!is_array($retVal[$match[1]]))
                            $retVal[$match[1]] = array($retVal[$match[1]]);
                        $retVal[$match[1]][] = $match[2];
                    } else {
                        $retVal[$match[1]] = trim($match[2]);
                    }
                }
            }
            return $retVal;
        }

        /**
         * Static function for XML-RPC encoding request.
         * @param $method string goes into MethodName XML tag
         * @param $params array set of strings that go into param/value XML tags.
         * @return string
         */
        public static function xmlrpc_encode_request($method, $params)
        {
            $xml  = '<?xml version="1.0"?>';
            $xml .= '<methodCall>';
            $xml .= '<methodName>' . htmlspecialchars($method) . '</methodName>';
            $xml .= '<params>';
            foreach ($params as $param) {
                $xml .= '<param><value><string>' . htmlspecialchars($param) . '</string></value></param>';
            }
            $xml .= '</params></methodCall>';

            return $xml;
        }

        /**
         * Caching key/value system for MentionClient
         * @param $type
         * @param $url
         * @param mixed $val If not null, is set to default value
         * @return mixed
         */
        public function c($type, $url, $val = null)
        {
            // Create the empty record if it doesn't yet exist
            $key = '_' . $type;

            if (!array_key_exists($url, $this->{$key})) {
                $this->{$key}[$url] = null;
            }

            if ($val !== null) {
                $this->{$key}[$url] = $val;
            }

            return $this->{$key}[$url];
        }
    }

    function webmentions_send($targetURL, $sourceURL = auto, $on_static_build_only = true)
    {
        if ($on_static_build_only && !get("static")) return true;

        if ($sourceURL === auto) $sourceURL = live_url();
        $client = new MentionClient();
        //$client->enableDebug();
        return $client->sendWebmention($sourceURL, $targetURL);
    }

    function webmention($label, $url)
    { 
        $response = webmentions_send($url); 

        if (!is_localhost()) { return ""; }
        if (true  === $response) { return ""; } 
        if (false === $response) { return p("Could not send webmention to $label"); } 

        $summary = (int)$response;

        if (is_array($response)) 
        {                
            $body    = json_decode(at($response, "body", []), true);
            $summary = at($body, "summary", at($body, "status", "unknown response"));
        }

        return p("Web-mention(s) sent to $label: $summary");
    }

    function webmentions_api_token()
    {       
        $token = false;

             if (defined("TOKEN_WEBMENTIONS_IO"))   $token = constant("TOKEN_WEBMENTIONS_IO");
        else if (defined("TOKEN_WEBMENTION_IO"))    $token = constant("TOKEN_WEBMENTION_IO");
        else                                        $token = get("webmentions_token", $token);

        return $token;
    }

    function webmentions_domain()
    {       
        $domain = false;

        if (defined("TOKEN_WEBMENTIONS_DOMAIN"))    $domain = constant("TOKEN_WEBMENTIONS_DOMAIN");
        else                                        $domain = get("webmentions_domain", $domain);

        return $domain;
    }

    function link_rel_webmentions()
    {
        // ie. Sets webmention' endpoint as https://webmention.io/villapirorum.netlify.app/webmention
        // So others can mention you with https://webmention.io/villapirorum.netlify.app/webmention/?source=https://villepreux.free.fr&target=https://villapirorum.netlify.app/now

        return  link_rel("webmention", 'https://webmention.io/'.webmentions_domain().'/webmention').
                link_rel("pingback",   'https://webmention.io/'.webmentions_domain().'/xmlrpc').
              //link_rel("pingback",   'https://webmention.io/webmention?forward=https://'.webmentions_domain().'/webmentions/endpoint').
                "";
    }

    function link_rel_webauth()
    {
        return/*link_rel("indieauth-metadata",      "https://indieauth.com/indieauth/metadata").*/ // TODO NEW WAY TO DO IT
                link_rel("authorization_endpoint",  "https://indieauth.com/auth").
                link_rel("token_endpoint",          "https://tokens.indieauth.com/token");
    }

    function js_webmentions()
    {
        if (has("ajax"))    return '';
      //if (is_localhost()) return ''; // CORS would block the calls

        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            async function fetch_mentions_endpoint(url) {

                dom.log("Webmentions", "Fetch webmentions count...");
            
                try
                {
                    const response = await fetch(url);
                
                    if (!response || !response.ok) {

                        dom.log("Webmentions", url, "RESULT IS NOT OK", response);
                    }
                
                    return (response && response.ok) ? response.json() : null;
                }
                catch (e)
                {
                    dom.log("Webmentions", url, "FAILED", e);
                
                    return null;
                }
            }

            on_first_interraction(function() {

                dom.log("Webmentions", "Parse webmention counters");

                document.querySelectorAll("[data-webmention-count]").forEach(function(e) {

                    var url = e.getAttribute("data-url");

                    dom.log("Webmentions", "Parse webmention counter", url);

                         if (url == false || url == "")   url = 'https://<?= webmentions_domain() ?>';
                    else if (url.indexOf("https://") < 0
                         &&  url.indexOf("http://")  < 0) url = 'https://<?= webmentions_domain() ?>/' + url;
                         
                    /*url = encodeURIComponent(url);*/
                    
                    fetch_mentions_endpoint("https://webmention.io/api/count?target="+url).then(function(data) {

                        dom.log("Webmentions", "Count received", data);
                        if (data) e.innerHTML = data.count;
                    });
                });

                dom.log("Webmentions", "Parse webmentions");

                document.querySelectorAll("[data-webmentions]").forEach(function(e) {

                    var url = e.getAttribute("data-url");

                    dom.log("Webmentions", "Parse webmentions", url);

                         if (url == false || url == "")   url = 'https://<?= webmentions_domain() ?>';
                    else if (url.indexOf("https://") < 0
                         &&  url.indexOf("http://")  < 0) url = 'https://<?= webmentions_domain() ?>/' + url;
                         
                    /*url = encodeURIComponent(url);*/

                    fetch_mentions_endpoint("https://webmention.io/api/mentions.jf2?target="+url).then(function(data) {

                        dom.log("Webmentions", "mentions received", data);

                        if (data.children.length > 0) e.innerHTML = "";
                        
                        data.children.forEach(function (mention_data) {
                            
                            var mention = '';
                            {
                                mention = `<?= mention_card(

                                    '$mention_data.type',

                                    '$mention_data.author.type',
                                    '$mention_data.author.name',
                                    '$mention_data.author.photo',
                                    '$mention_data.author.url',

                                    '$mention_data.url',
                                    '$mention_data.published',
                                    '$mention_data.wm-received',
                                    '$mention_data.wm-id',
                                    '$mention_data.wm-source',
                                    '$mention_data.wm-target',
                                    '$mention_data.wm-protocol',
                                    '$mention_data.name',

                                    '$mention_data.content.html',
                                    '$mention_data.content.text',

                                    '$mention_data.in-reply-to',
                                    '$mention_data.wm-property',
                                    '$mention_data.wm-private',

                                    true

                                    ) ?>`.trim();
                               
                                mention = mention.replaceAll("$mention_data.type",          mention_data.type           );

                                mention = mention.replaceAll("$mention_data.author.type",   mention_data.author.type    );
                                mention = mention.replaceAll("$mention_data.author.name",   mention_data.author.name    );
                                mention = mention.replaceAll("$mention_data.author.photo",  mention_data.author.photo   );
                                mention = mention.replaceAll("$mention_data.author.url",    mention_data.author.url     );

                                mention = mention.replaceAll("$mention_data.url",           mention_data.url            );
                                mention = mention.replaceAll("$mention_data.published",     mention_data.published      );
                                mention = mention.replaceAll("$mention_data.wm-received",   mention_data.wm_received    );
                                mention = mention.replaceAll("$mention_data.wm-id",         mention_data.wm_id          );
                                mention = mention.replaceAll("$mention_data.wm-source",     mention_data.wm_source      );
                                mention = mention.replaceAll("$mention_data.wm-target",     mention_data.wm_target      );
                                mention = mention.replaceAll("$mention_data.wm-protocol",   mention_data.wm_protocol    );
                                mention = mention.replaceAll("$mention_data.name",          mention_data.name           );
                                
                                mention = mention.replaceAll("$mention_data.content.html",  mention_data.content.html   );
                                mention = mention.replaceAll("$mention_data.content.text",  mention_data.content.text   );

                                mention = mention.replaceAll("$mention_data.in-reply-to",   mention_data.in_reply_to    );
                                mention = mention.replaceAll("$mention_data.wm-property",   mention_data.wm_property    );
                                mention = mention.replaceAll("$mention_data.wm-private",    mention_data.wm_private     );
                            }

                            e.innerHTML += mention;
                        });
                    });
                });
            });

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function webmentions_url($url = auto)
    {
      //if ($url === auto) $url = get("canonical");
        if ($url === auto)
        {
            $url_branch = url_branch();

            if ($url_branch != "")
            {
                if (0 === stripos($url_branch, get("local_domain")))                        $url_branch = substr($url_branch, strlen(get("local_domain")));
                if (0 === stripos($url_branch, get("live_domain", server_server_name())))   $url_branch = substr($url_branch, strlen(get("live_domain", server_server_name())));
            }

            $url_branch = trim($url_branch, "/");
        
            $url = 'https://'.webmentions_domain();

            if ($url_branch != "") $url .= "/$url_branch";
        }

        return $url;
    }

    function webmentions_counter($placeholder = "⧗", $prefix = "Web-Mention(s): ", $suffix = "", $url = auto, $tag = "span")
    {
        return tag($tag, $prefix.span($placeholder, [ "data-webmention-count" => true, "data-url" => webmentions_url($url) ]).$suffix);
    }

    function mention_card(

        $type,          /* "entry" */

        $author_type,   /* "card" */
        $author_name,   /* "Webmention Rocks!" */
        $author_photo,  /* "https://webmention.io/avatar/webmention.rocks/e08155b03da96cb1bdfd161ea24efdfad8d85d06afcee540ec246f1f613eb5a9.png" */
        $author_url,    /* ... */

        $url,           /* "https://webmention.rocks/receive/1" */
        $published,     /* "2024-03-23T18:07:25-07:00" */
        $wm_received,   /* "2024-03-24T01:07:27Z" */
        $wm_id,         /* 1797069 */
        $wm_source,     /* "https://webmention.rocks/receive/1/aaeffae4d5674c871a72b8ee3b22bf48" */
        $wm_target,     /* "https://villapirorum.netlify.app/web" */
        $wm_protocol,   /* "webmention" */
        $name,          /* "Receiver Test #1" */

        $content_html,  /* "<p>This test verifies that you accept a Webmention request that contains a valid source and target URL. To pass this test, your Webmention endpoint must return either HTTP 200, 201 or 202 along with the <a href=\"https://www.w3.org/TR/webmention/#receiving-webmentions\">appropriate headers</a>.</p>\n        <p>If your endpoint returns HTTP 201, then it MUST also return a <code>Location</code> header. If it returns HTTP 200 or 202, then it MUST NOT include a <code>Location</code> header.</p>" */
        $content_text,  /* "This test verifies that you accept a Webmention request that contains a valid source and target URL. To pass this test, your Webmention endpoint must return either HTTP 200, 201 or 202 along with the appropriate headers.\n        If your endpoint returns HTTP 201, then it MUST also return a Location header. If it returns HTTP 200 or 202, then it MUST NOT include a Location header. */

        $in_reply_to,   /* "https://villapirorum.netlify.app/web" */
        $wm_property,   /* "in-reply-to" */
        $wm_private,    /* false */

        $filled_with_placeholders = false

        ) 
    {
        return article(
            header(
                p(
                    img(
                        $author_photo, 
                        24, 24, 
                        [ "style" => "width: 48px; height: 48px; border-radius: 50%" ],
                        "Mention author photo", 
                        $lazy                           = auto, 
                        $lazy_src                       = auto, 
                        $content                        = auto, 
                        $precompute_size                = auto, 
                        $src_attribute                  = auto, 
                        $preload_if_among_first_images  = !$filled_with_placeholders
                        ).
                    nbsp().span($author_name).
                    nbsp().span($published), 
                    
                    [ "style" => "display: flex; gap: var(--gap); align-items: center;" ])
                ).
            section(
                p($content_text)
                )
            , "card");
    }
    
    function section_webmentions($url = auto)
    {
        if (!!get("no_js")) return "";

        return section(

            p("These are ".a("webmentions", "https://indieweb.org/Webmention")." via the ".a("IndieWeb", "https://indieweb.org/")." and ".a("webmention.io", "https://webmention.io")).
            div(
                p("No known mention, yet").
                noscript(p("Loading web mentions relies on JavaScript. Try enabling JavaScript and reloading.")), 
                [ "data-webmentions" => true, "data-url" => webmentions_url($url) ]
                ).
            p(form(
                label("URL of your site:", "form-webmention-source", "sr-only")." ".
                input("", "url",    "form-webmention-source",   [ "placeholder" => "https://example.com", "required" => "" ])." ".
                input("", "hidden", "target",                   [ "name" => "target", "value" => "https://www.zachleat.com/web/google-fonts-display/" ]).
                input("", "submit", "submit",                   [ "value" => "Send Webmention", "class" => "button"]),
                [ "action" => "https://webmention.io/villapirorum.netlify.app/webmention", "method" => "post" ]
                )).
            "", [ "style" => "padding-bottom: var(--gap)", "class" => "webmentions requires-js" ]);
    }

    /**
     * type : atom|html
     */
    function webmentions_feed_url($type = "atom")
    {
        return "https://webmention.io/api/mentions.$type?token=".webmentions_api_token();
    }

    function link_rel_manifest($path_manifest = false, $type = false, $pan = 17)
    {
        $profiler = debug_track_timing();

        if (!$path_manifest) $path_manifest = path("manifest.json");
        if (!$path_manifest) return "";

        return link_rel("manifest", $path_manifest, $type, $pan);
    }

    function parse_icons($name = "favicon", $size = false, $media = false, $ext = "png", $type = auto, $alternate = false)
    {
        if ($name === false || $name === auto) $name = "favicon";
        if ($ext  === false || $ext  === auto) $ext  = "png";
        if ($type === false || $type === auto) $type = false;

        if (is_array($name)) { $icons = array(); foreach ($name as $i => $_) { $icon = parse_icons($_,    $size, $media, $ext, $type, $alternate); if (null !== $icon) $icons[] = $icon; } return $icons; }
        if (is_array($size)) { $icons = array(); foreach ($size as $i => $_) { $icon = parse_icons($name, $_,    $media, $ext, $type, $alternate); if (null !== $icon) $icons[] = $icon; } return $icons; }
        if (is_array($ext))  { $icons = array(); foreach ($ext  as $i => $_) { $icon = parse_icons($name, $size, $media, $_,   $type, $alternate); if (null !== $icon) $icons[] = $icon; } return $icons; }
        if (is_array($type)) { $icons = array(); foreach ($type as $i => $_) { $icon = parse_icons($name, $size, $media, $ext, $_   , $alternate); if (null !== $icon) $icons[] = $icon; } return $icons; }

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
                    return array_merge(
                        parse_icons($name, $w."x".$h, array_merge($media, array("orientation" => "portrait")),  $ext, $type, $alternate),
                        parse_icons($name, $h."x".$w, array_merge($media, array("orientation" => "landscape")), $ext, $type, $alternate));
                }
            }
        }

        $info   = pathinfo($name);
        $dir    = at($info, 'dirname',   false);
        $ext    = at($info, 'extension', $ext);
        $name   = at($info, 'filename',  $name);
        $name   = (!!$dir)  ? "$dir/$name"  : $name;
        $name   = (!!$size) ? "$name-$size" : $name;

        $attributes = array();

        if (!!$size)                            $attributes["sizes"] = $size;
        if (false === stripos($type, "apple"))  $attributes["type"]  = "image/$ext".(($ext=="svg")?"+xml":"");
        if (!!$media)                           $attributes["media"] = "(device-width: ".$media_clean["width"]."px) and (device-height: ".$media_clean["height"]."px) and (-webkit-device-pixel-ratio: ".$media_clean["ratio"].") and (orientation: ".$media_clean["orientation"].")";

        $path = path($name.".".$ext);

        if (!$path) return null;

        return array(array("type" => $type, "path" => $path, "attributes" => $attributes));
    }

    function link_rel_icon($name = "favicon", $size = false, $media = false, $ext = "png", $type = auto, $alternate = false)
    {
        if ($name === false || $name === auto) $name = "favicon";
        if ($ext  === false || $ext  === auto) $ext  = "png";
        if ($type === false || $type === auto) $type = false;

        if (is_array($name)) { $html = ""; foreach ($name as $i => $_) { $html_icon = link_rel_icon($_,    $size, $media, $ext, $type, $alternate); $html .= /*(($i > 0 && $html_icon != "") ? eol() : "").*/$html_icon; } return $html; }
        if (is_array($size)) { $html = ""; foreach ($size as $i => $_) { $html_icon = link_rel_icon($name, $_,    $media, $ext, $type, $alternate); $html .= /*(($i > 0 && $html_icon != "") ? eol() : "").*/$html_icon; } return $html; }
        if (is_array($ext))  { $html = ""; foreach ($ext  as $i => $_) { $html_icon = link_rel_icon($name, $size, $media, $_,   $type, $alternate); $html .= /*(($i > 0 && $html_icon != "") ? eol() : "").*/$html_icon; } return $html; }
        if (is_array($type)) { $html = ""; foreach ($type as $i => $_) { $html_icon = link_rel_icon($name, $size, $media, $ext, $_   , $alternate); $html .= /*(($i > 0 && $html_icon != "") ? eol() : "").*/$html_icon; } return $html; }

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
                    return  link_rel_icon($name, $w."x".$h, array_merge($media, array("orientation" => "portrait")),  $ext, $type, $alternate).
                            link_rel_icon($name, $h."x".$w, array_merge($media, array("orientation" => "landscape")), $ext, $type, $alternate);
                }
            }
        }

        $info   = pathinfo($name);
        $dir    = at($info, 'dirname',   false);
        $ext    = at($info, 'extension', $ext);
        $name   = at($info, 'filename',  $name);
        $name   = (!!$dir)  ? "$dir/$name"  : $name;
        $name   = (!!$size) ? "$name-$size" : $name;

        $attributes = array();

        if (!!$size)                            $attributes["sizes"] = $size;
        if (false === stripos($type, "apple"))  $attributes["type"]  = "image/$ext".(($ext=="svg")?"+xml":"");
        if (!!$media)                           $attributes["media"] = "(device-width: ".$media_clean["width"]."px) and (device-height: ".$media_clean["height"]."px) and (-webkit-device-pixel-ratio: ".$media_clean["ratio"].") and (orientation: ".$media_clean["orientation"].")";

        $path = path($name.".".$ext);

        if (!$path) return "";

        return link_rel($type, $path, $attributes);
    }

    function metas() { return delayed_component("_".__FUNCTION__, false); }
    function _metas()
    {
        $profiler = debug_track_timing();
        
        return  ""
            /*
            .   eol().comment("Preloaded images")
            .   link_rel_image_preloads()
            */
            /*
            .   meta_charset('utf-8')
            
            .   eol() */
            .                 meta_http_equiv('x-ua-compatible',   'ie=edge,chrome=1')/*
            .   (AMP() ? '' : meta_http_equiv('Content-type',      'text/html;charset=utf-8'))*/
            .                 meta_http_equiv('content-language',  get("content-language", content_language()))
            .   eol()       
            .   meta(array("title" => get("title") . ((get("heading") != '') ? (' - '.get("heading")) : '')))
            .   eol()       
            .   meta('keywords', get("title").((!!get("keywords") && "" != get("keywords")) ? (', '.get("keywords")) : "")    )
            
            .   eol()
            .   meta('format-detection',                    'telephone=no')/*
            .   meta('viewport',                            'width=device-width, minimum-scale=1, initial-scale=1')*/
          //.   meta('robots',                              'NOODP') // Deprecated
          //.   meta('googlebot',                           'NOODP')
            .   meta('description',                         get("description", get("title")))
            .   meta('author',                              get("author", author))                                                      .(!get("mastodon_user") ? "" : (""
            .   meta('fediverse:creator',                   "@".get("mastodon_user")."@".get("mastodon_domain", "mastodon.social"))     ))
            .   meta('copyright',                           get("author", author).' 2000-'.date('Y'))
            .   meta('generator',                           "DOM ".version)
            .   meta('title',                               get("title"))
            .   meta('theme-color',                         get("theme_color"))

            .   eol()
            .   meta('view-transition',                     'same-origin')

            .   eol()
            .   meta('color-scheme',                        'dark light')
            
            .   eol()       
            .   meta('DC.title',                            get("title"))
            .   meta('DC.format',                           'text/html')
            .   meta('DC.language',                         get("dc-language", content_language()))
            
            .   eol()       
            .   meta('geo.region',                          get("geo_region"))
            .   meta('geo.placename',                       get("geo_placename"))
            .   meta('geo.position',                        get("geo_position_x").';'. get("geo_position_y"))
            .   meta('ICBM',                                get("geo_position_x").', '.get("geo_position_y"))              
            
            .   eol()       
            .   meta('twitter:card',                        'summary_large_image')      . (has('twitter_page') ? (""
            .   meta('twitter:site',                        get("twitter_page"))        ) : "")
            .   meta('twitter:url',                         get("canonical"))
            .   meta('twitter:title',                       get("title"))
            .   meta('twitter:description',                 get("description", get("title")))
            .   meta('twitter:image',                       path(get("canonical").'/'.get("image")))
            
            .   eol()       
            .   meta_property('og:site_name',               get("og_site_name", get("title")))
            .   meta_property('og:image',                   path(get("canonical").'/'.get("image")))
            .   meta_property('og:title',                   get("title"))
            .   meta_property('og:description',             get("description", get("title")))
            .   meta_property('og:url',                     get("canonical"))            
            .   meta_property('og:type',                    'website')
            
            .   eol()       
            .   meta('application-name',                    get("title"))                                   . ((has("pinterest_site_verification") || has("google_site_verification")) ? (""
            
            .   eol()                                                                               ) : "") . (has("pinterest_site_verification") ? (""
            .   meta('p:domain_verify',                     get("pinterest_site_verification"))     ) : "") . (has("google_site_verification")    ? (""
            .   meta('google-site-verification',            get("google_site_verification"))        ) : "")
            
            .   eol()
            .   meta('msapplication-TileColor',             get("theme_color"))
            .   meta('msapplication-TileImage',             path(get("icons_path").'ms-icon-144x144.png'))
            
            .   eol()
            .   (path(get("icons_path").'ms-icon-70x70.png'    ) ? (meta('msapplication-square70x70logo',     path(get("icons_path").'ms-icon-70x70.png'    ))) : '')
            .   (path(get("icons_path").'ms-icon-150x150.png'  ) ? (meta('msapplication-square150x150logo',   path(get("icons_path").'ms-icon-150x150.png'  ))) : '')
            .   (path(get("icons_path").'ms-icon-310x150.png'  ) ? (meta('msapplication-wide310x150logo',     path(get("icons_path").'ms-icon-310x150.png'  ))) : '')
            .   (path(get("icons_path").'ms-icon-310x310.png'  ) ? (meta('msapplication-square310x310logo',   path(get("icons_path").'ms-icon-310x310.png'  ))) : '')
            
            .   eol()
            .   meta('msapplication-notification',    'frequency=30;'
                                                    . 'polling-uri' .'='.urlencode('/?rss=tile&id=1').';'
                                                    . 'polling-uri2'.'='.urlencode('/?rss=tile&id=2').';'
                                                    . 'polling-uri3'.'='.urlencode('/?rss=tile&id=3').';'
                                                    . 'polling-uri4'.'='.urlencode('/?rss=tile&id=4').';'
                                                    . 'polling-uri5'.'='.urlencode('/?rss=tile&id=5').';'.' cycle=1')
                                                               
            // TODO FIX HREFLANG ALTERNATE
            // TODO FIX URL QUERY ARGS (incompatible with static sites)

            .   eol().comment("Alternate URLs")   
                // /rss.xml and not /rss because /rss is /rss/index.html, which is not a RSS feed. Even if it contains a refresh redirection to /rss.xml
            .   link_rel("alternate",   get("canonical").(!!get("static") ? "/rss.xml" : "/?rss"     ), array("type" => "application/rss+xml", "title" => "RSS"))       . (!!get("static") ? '' : (''
            .   link_rel("alternate",   get("canonical").(!!get("static") ? "/en"      : "/?lang=en" ), array("hreflang" => "en-US"))
            .   link_rel("alternate",   get("canonical").(!!get("static") ? "/fr"      : "/?lang=fr" ), array("hreflang" => "fr-FR"))                               ))  . (AMP() ? '' : (''
            .   link_rel("amphtml",     get("canonical").(!!get("static") ? "/amp"     : "/?amp=1"   ))                                                             ))
            .   link_rel("canonical",   get("canonical"))
            
            .   eol().comment("Icons")
            .   link_rel_icon("img/icon.svg")
            .   link_rel_icon(get("image"), false, false, false, false, /*alternate*/true)

            .   eol().comment("'Fav' Icons")
            .   link_rel_icon(array(
            
                    get("icons_path")."favicon",
                    get("icons_path")."android-icon",
                    get("icons_path")."apple-icon",
                    get("icons_path")."apple-touch-icon"),

                    array(16,32,57,60,72,76,96,114,120,144,152,180,192,196,310,512),
                    
                    false, false, false, false, /*alternate*/true)

            .   eol().comment("Apple-splash icons")
            .   link_rel_icon(get("icons_path")."apple-splash", "2048x2732" , array(1024, 1366, 2)  )
            .   link_rel_icon(get("icons_path")."apple-splash", "1668x2388" , array( 834, 1194, 2)  )
            .   link_rel_icon(get("icons_path")."apple-splash", "1668x2224" , array( 834, 1112, 2)  )
            .   link_rel_icon(get("icons_path")."apple-splash", "1536x2048" , array( 768, 1024, 2)  )
            .   link_rel_icon(get("icons_path")."apple-splash", "828x1792"  , array( 414,  896, 2)  )
            .   link_rel_icon(get("icons_path")."apple-splash", "750x1334"  , array( 375,  667, 2)  )
            .   link_rel_icon(get("icons_path")."apple-splash", "640x1136"  , array( 320,  568, 2)  )
            .   link_rel_icon(get("icons_path")."apple-splash", "1242x2688" , array( 414,  896, 3)  )
            .   link_rel_icon(get("icons_path")."apple-splash", "1125x2436" , array( 375,  812, 3)  )
            .   link_rel_icon(get("icons_path")."apple-splash", "1242x2208" , array( 414,  736, 3)  )
            /*
            .   eol().comment("Prefetched pages")
            .   link_rel_prefetchs()*/
            ;
    }
    
    function meta($p0, $p1 = false, $pan = 0)   { return (($p1 === false) ? (eol().'<meta'.attributes_as_string($p0,$pan).' />') : meta_name($p0,$p1)); }
                            
    function meta_charset($charset)             { return meta(array("charset"    => $charset)); }
    function meta_http_equiv($equiv,$content)   { return meta(array("http-equiv" => $equiv,    "content" => $content), false, array(40,80)); }
    function meta_name($name,$content)          { return meta(array("name"       => $name,     "content" => $content), false, array(40,80)); }
    function meta_property($property,$content)  { return meta(array("property"   => $property, "content" => $content), false, array(40,80)); }

    function link_HTML($attributes, $pan = 0)               { if (!!get("no_html"))  return ''; return tag('link', '', attributes_as_string($attributes,$pan), false, true); }
    function link_rel($rel, $link, $type = false, $pan = 0) { if (!$link || $link == "") return ''; return link_HTML(array_merge(array("rel" => $rel, "href" => $link), ($type !== false) ? (is_array($type) ? $type : array("type" => $type)) : array()), $pan); }
    
    function manifest($filename = "manifest.json") 
    {
        return link_rel("manifest", $filename)
              //. ((!AMP() && !is_localhost()) 
              //? (eol(2) . '<script async src="https://cdn.jsdelivr.net/npm/pwacompat@2.0.6/pwacompat.min.js" integrity="sha384-GOaSLecPIMCJksN83HLuYf9FToOiQ2Df0+0ntv7ey8zjUHESXhthwvq9hXAZTifA" crossorigin="anonymous"></script>') 
              //: "")
                ; 
    }

    function link_style($link, $media = "screen", $async = false, $attributes = false)
    {
        if (!!get("no_css"))             return '';
        if (!!get("include_custom_css")) return style_file($link, false, true);

        $is_external_url = ((0 === stripos($link, "http"))
                         || (0 === stripos($link, "//"  )));

        if (AMP() && !$is_external_url)  return style_file($link, false, true);

        if ($async && !AMP())
        {
            $method = 2;

            if ($method == 1)
            {
                $attributes = attributes_add($attributes, array("type" => "text/css", "media" => "nope!", "onload" => "this.media='$media'"));
                return link_rel("stylesheet", $link, $attributes);
            }
            else // https://web.dev/defer-non-critical-css/#optimize
            {
                $attributes = attributes_add($attributes, array("as" => "style", "onload" => "this.onload=null;this.rel='stylesheet'"));
                return link_rel("preload", $link, $attributes).tag("noscript", link_rel("stylesheet", $link));
            }
        }
        else
        {
            $attributes = attributes_add($attributes, array("type" => "text/css", "media" => $media));
            return link_rel("stylesheet", $link, $attributes);
        }
    }

    function css_layer_order($layers)
    {
        if (!get("css_layers_support")) return "";
        if (func_num_args() > 0) $layers = func_get_args();
        else if (!is_array($layers)) $layers = array(explode(",", trim(str_replace(" ", "", $layers))));
        return "@layer ".implode(", ", $layers).";";
    }
    
    function css_layer_bgn($layer)
    {
        if (false === $layer || !get("css_layers_support")) return "";
        return "@layer $layer {".eol(2);
    }
    
    function css_layer_end($layer = false)
    {
        if (!get("css_layers_support")) return "";
        return eol()."}".(!$layer ? "" : " /* @layer $layer */");
    }
    
    function css_layer($layer, $css)
    {
        if (false === $layer || !get("css_layers_support")) return $css;
        return "@layer $layer {".eol(2).$css.eol()."}";
    }

    function style_css_as_is($css = "", $attributes = false)
    {
        if (!$css || $css == "") return '';
        $css = eol().$css.eol();
        if (AMP()) return hook_amp_css($css);
        return tag('style',  $css, $attributes);
    }

    function style($css = "", $force_minify = false, $attributes = false) // TODO attributes at 2nd position
    {
        $profiler = debug_track_timing();
        if (!$css || $css == "") return '';
        return style_css_as_is(raw_css($css, $force_minify), $attributes);
    }

    function style_file($filename = "", $force_minify = false, $silent_errors = auto, $attributes = false)
    {
        $profiler = debug_track_timing();
        if (!$filename || $filename == "") return '';
        $filename = path($filename);
        if (!$filename || $filename == "") return '';        
        return style_css_as_is(include_css($filename, $force_minify, $silent_errors), $attributes);
    }

    function style_css_or_file($filename_or_code = "", $force_minify = false, $silent_errors = auto)
    {
        $profiler = debug_track_timing();
        if (!$filename_or_code || $filename_or_code == "") return '';
        $filename = path($filename_or_code);
        return style_css_as_is($filename 
            ? include_css($filename, $force_minify, $silent_errors) 
            : raw_css($filename_or_code, $force_minify)
            );
    }

    function script_js_as_is($js = "", $type = "text/javascript", $attributes = false)
    {
        if (!!get("no_js"))    return '';
        if (!$js || $js == "") return ''; 
        $js  = eol().$js.eol();
        if (AMP()) return hook_amp_js(at($js, "js", at($js, 0, $js)), at($js, "html", at($js, 1, "")));

        $attributes = attributes_add($attributes, array("type" => $type));

        return tag('script', $js, $attributes);
    }

    function script($js = "", $type = "text/javascript",  $force_minify = false)
    {
        $profiler = debug_track_timing();         
        if (!$js || $js == "") return ''; 
        return script_js_as_is(raw_js($js, $force_minify), $type);
    }

    function script_file($filename = "", $type = auto, $force = auto,  $force_minify = auto, $silent_errors = auto, $attributes = auto)
    {
        $profiler = debug_track_timing(); 

        if (!$filename || $filename == "") return ''; 
        $filename = path($filename);
        if (!$filename || $filename == "") return ''; 

        if (auto === $type)         $type           = "text/javascript";
        if (auto === $force)        $force          = false;
        if (auto === $force_minify) $force_minify   = false;
        if (auto === $attributes)   $attributes     = false;

        return script_js_as_is(include_js($filename, $force_minify, $silent_errors), $type, $attributes);
    }

    function script_js_or_file($filename_or_code = "", $type = "text/javascript", $force = false,  $force_minify = false, $silent_errors = auto)
    {
        $profiler = debug_track_timing(); 

        if (!!get("no_js")) return '';
        
        if (!$filename_or_code || $filename_or_code == "") return ''; 
        $filename = path($filename_or_code);
        $js  = eol().($filename ? include_js($filename, $force_minify, $silent_errors) : raw_js($filename_or_code, $force_minify)).eol();
        if (AMP()) return hook_amp_js(at($js, "js", at($js, 0, $js)), at($js, "html", at($js, 1, "")));
        return tag('script', $js, array("type" => $type));
    }

    function script_src($src,               $type = "text/javascript", $extra = false, $force = false)  { if (!!get("no_js")) return ''; return ((!$force && AMP()) ? '' : tag('script', '', ($type === false) ? array("src" => $src) : array("type" => $type, "src" => $src), false, false, $extra)); }
    function script_module($src,            $type = "module",          $extra = false, $force = false)  { return script_src($src, $type, $extra, $force); }
    function script_json_ld($properties)                                                                { return script((((!get("minify",false)) && defined("JSON_PRETTY_PRINT")) ? json_encode($properties, JSON_PRETTY_PRINT) : json_encode($properties)), "application/ld+json", true); }
    
    function script_common_head()   { return /*AMP() ? "" : */script(js_common_head()); }
    function script_ajax_head()     { return /*AMP() ? "" : */script(js_ajax_head());   }
    function script_ajax_body()     { return /*AMP() ? "" : */script(js_ajax_body());   }
    function script_inside_iframe() { return script(js_inside_iframe()); }
    
    function schema($type, $properties = array(), $parent_schema = false)
    {
        return array_merge(($parent_schema === false) ? array() : $parent_schema, array("@context" => "https://schema.org", "@type" => $type), $properties);
    }
    
    function link_style_google_fonts($fonts = false, $async = true)
    {    
        if ($fonts === false) $fonts = get("fonts");
        if (!!$fonts)         $fonts = str_replace(' ','+', trim($fonts, ", /|"));

        return            (!!$fonts ? link_style("https://fonts.googleapis.com/css?family=$fonts",          "screen", $async) : '')
                . eol() . (true     ? link_style("https://fonts.googleapis.com/icon?family=Material+Icons", "screen", $async) : '');
    }
    
    function link_styles($async = false, $fonts = false)
    {
        $profiler = debug_track_timing();

        if ($fonts === false) $fonts = get("fonts");

        $inline_css = get("inline_css", true);

        $path_normalize         = !$inline_css ? false : path("css/normalize.min.css");
        $path_sanitize          = !$inline_css ? false : path("css/sanitize.min.css");
        $path_evergreen         = !$inline_css ? false : path("css/evergreen.min.css");
        $path_material          = !$inline_css ? false : path("css/material-components-web.min.css");
        $path_bootstrap         = !$inline_css ? false : path("css/bootstrap.min.css");
        $path_google_fonts      = !$inline_css ? false : path("css/google-fonts.css");
        $path_material_icons    = !$inline_css ? false : path("css/material-icons.css");

        return                                                                                                                                                                                                                                                                  (("normalize" == get("normalize")) ? (""
            .   ($path_normalize      ? link_style($path_normalize      , "screen", false)  : link_style('https://cdnjs.cloudflare.com/ajax/libs/normalize/'         . get("version_normalize") . '/normalize.min.css',                     "screen", false     ))  ) : "").    (("sanitize"  == get("normalize")) ? (""
            .   ($path_sanitize       ? link_style($path_sanitize       , "screen", false)  :(link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . get("version_sanitize")  . '/sanitize.min.css',                      "screen", false     )  
                                                                                             .link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . get("version_sanitize")  . '/assets.min.css',                        "screen", false     )  
                                                                                             .link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . get("version_sanitize")  . '/forms.min.css',                         "screen", false     )  
                                                                                             .link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . get("version_sanitize")  . '/reduce-motion.min.css',                 "screen", false     )  
                                                                                             .link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . get("version_sanitize")  . '/system-ui.min.css',                     "screen", false     )  
                                                                                             .link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . get("version_sanitize")  . '/typography.min.css',                    "screen", false     )  
                                                                                             .link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . get("version_sanitize")  . '/ui-monospace.min.css',                  "screen", false     ))) ) : "").    (("evergreen" == get("reset"    )) ? (""
            .   ($path_evergreen      ? link_style($path_evergreen      , "screen", false)  : link_style('https://cdnjs.cloudflare.com/ajax/libs/10up-sanitize.css/' . get("version_evergreen") . '/evergreen.min.css',                     "screen", false     ))  ) : "").    (("material"  == get("framework")) ? (""
            .   ($path_material       ? link_style($path_material       , "screen", false)  : link_style('https://unpkg.com/material-components-web@'                . get("version_material")  . '/dist/material-components-web.min.css',  "screen", false     ))  ) : "").    (("bootstrap" == get("framework")) ? (""
            .   ($path_bootstrap      ? link_style($path_bootstrap      , "screen", false)  : link_style('https://stackpath.bootstrapcdn.com/bootstrap/'             . get("version_bootstrap") . '/css/bootstrap.min.css',                 "screen", false     ))  ) : "").    (("spectre"   == get("framework")) ? (""
            .                                                                                 link_style('https://unpkg.com/spectre.css/dist/spectre.min.css')
            .                                                                                 link_style('https://unpkg.com/spectre.css/dist/spectre-exp.min.css')
            .                                                                                 link_style('https://unpkg.com/spectre.css/dist/spectre-icons.min.css')                                                                                                ) : "").    (!!$fonts                              ? (""
            .   ($path_google_fonts   ? link_style($path_google_fonts   , "screen", $async) : link_style('https://fonts.googleapis.com/css?family='.str_replace(' ','+', trim($fonts," /|")),                                               "screen", $async    ))  ) : "").    (("material"  == get("framework")) ? ("" 
            .   ($path_material_icons ? link_style($path_material_icons , "screen", $async) : link_style('https://fonts.googleapis.com/icon?family=Material+Icons',                                                                         "screen", $async    ))  ) : "")
            ;
    }
    
    define("IMPORTANT", !!AMP() ? '' : ' !important');

    function css_line($selectors = "", $styles = "", $tab = 1, $pad = 54)
    {
        return $selectors == "" ? eol() : str_pad(eol().tab(1).$selectors, $pad)."{ ".$styles." }";
    }

    function predefined_brands_color_properties($tab = 2) { return delayed_component("_".__FUNCTION__, $tab, 3); }
    function _predefined_brands_color_properties($tab = 2)
    {
        $css = "";

        foreach (brands() as $brand)
        {
            $fn       = "dom\color_$brand"; // For php 5.6 compatibility
            $colors   = $fn();
            $colors   = is_array($colors) ? $colors : array($colors);
            $var      = "--color-$brand";

            $css .= eol().tab($tab);

                 $i = 0;                            $css .=               pan($var.                               ":", $i == 0 ? 31 : 0)." ".$colors[$i].";";
            for ($i = 0; $i < count($colors); ++$i) $css .= ($i>0?" ":"").pan($var.(($i > 0) ? ("-".($i+1)) : "").":", $i == 0 ? 31 : 0)." ".$colors[$i].";";
        }

        //set("debug", true);
        //debug_log($css);
        
        return raw_css($css);
    }

    function css_layers()
    {
        return css_layer_order(
    
                "normalize",
                "reset",
    
                "base-colors",
                "base",
                "base-components"

                );
    }
    
    function css_root($vars, $layer = false, $root = ":root")
    {
        HSTART(-2); ?><style><?php HERE(null); ?> 

            <?= $root ?> { 

                <?= implode(PHP_EOL.tab(4), explode(PHP_EOL, trim($vars))) ?> 
            }
    
        <?php HERE("raw_css"); ?></style><?php return css_layer($layer, HSTOP(null));
    }
    
    #region Third Parties CSS

    function css_reset($layer = "reset")
    {
        heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 

            /***
                The new CSS reset - version 1.11.2 (last updated 15.11.2023)
                GitHub page: https://github.com/elad2412/the-new-css-reset
            ***/

            /*
                Remove all the styles of the "User-Agent-Stylesheet", except for the 'display' property
                - The "symbol *" part is to solve Firefox SVG sprite bug
                - The "html" element is excluded, otherwise a bug in Chrome breaks the CSS hyphens property (https://github.com/elad2412/the-new-css-reset/issues/36)
            */
            *:where(:not(html, iframe, canvas, img, svg, video, audio):not(svg *, symbol *)) {
                all: unset;
                display: revert;
            }

            /* Preferred box-sizing value */
            *,
            *::before,
            *::after {
                box-sizing: border-box;
            }

            /* Fix mobile Safari increase font-size on landscape mode */
            html {
                -moz-text-size-adjust: none;
                -webkit-text-size-adjust: none;
                text-size-adjust: none;
            }

            /* Reapply the pointer cursor for anchor tags */
            a, button {
                cursor: revert;
            }

            /* Remove list styles (bullets/numbers) */
            ol, ul, menu, summary {
                list-style: none;
            }

            /* For images to not be able to exceed their container */
            img {
                max-inline-size: 100%;
                max-block-size: 100%;
            }

            /* removes spacing between cells in tables */
            table {
                border-collapse: collapse;
            }

            /* Safari - solving issue when using user-select:none on the <body> text input doesn't working */
            input, textarea {
                -webkit-user-select: auto;
                user-select: auto;
            }

            /* revert the 'white-space' property for textarea elements on Safari */
            textarea {
                white-space: revert;
            }

            /* minimum style to allow to style meter element */
            meter {
                -webkit-appearance: revert;
                appearance: revert;
            }

            /* preformatted text - use only for this feature */
            :where(pre) {
                all: revert;
                box-sizing: border-box;
            }

            /* reset default text opacity of input placeholder */
            ::placeholder {
                color: unset;
            }

            /* fix the feature of 'hidden' attribute.
            display:revert; revert to element instead of attribute */
            :where([hidden]) {
                display: none;
            }

            /* revert for bug in Chromium browsers
            - fix for the content editable attribute will work properly.
            - webkit-user-select: auto; added for Safari in case of using user-select:none on wrapper element*/
            :where([contenteditable]:not([contenteditable="false"])) {
                -moz-user-modify: read-write;
                -webkit-user-modify: read-write;
                overflow-wrap: break-word;
                -webkit-line-break: after-white-space;
                line-break: after-white-space;
                -webkit-user-select: auto;
                user-select: auto;
            }

            /* apply back the draggable feature - exist only in Chromium and Safari */
            :where([draggable="true"]) {
                -webkit-user-drag: element;
            }

            /* Revert Modal native behavior */
            :where(dialog:modal) {
                all: revert;
                box-sizing: border-box;
            }

            /* Remove details summary webkit styles */
            ::-webkit-details-marker {
                display: none;
            }


        <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
    }

    function css_normalize_remedy($layer = "normalize")
    {
        heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 
            
            /* CSS Remedy */
            
            /* @docs
            label: Core Remedies
            version: 0.1.0-beta.2
            
            note: |
            These remedies are recommended
            as a starter for any project.
            
            category: file
            */
            
            
            /* @docs
            label: Box Sizing
            
            note: |
            Use border-box by default, globally.
            
            category: global
            */
            *, ::before, ::after { box-sizing: border-box; }
            
            
            /* @docs
            label: Line Sizing
            
            note: |
            Consistent line-spacing,
            even when inline elements have different line-heights.
            
            links:
            - https://drafts.csswg.org/css-inline-3/#line-sizing-property
            
            category: global
            */
            <?= "html { line-sizing: normal; }" /* Inside phh string as this draft property is currently a VS-CODE WARNING */ ?> 
            
            
            /* @docs
            label: Body Margins
            
            note: |
            Remove the tiny space around the edge of the page.
            
            category: global
            */
            body { margin: 0; }
            
            
            /* @docs
            label: Hidden Attribute
            
            note: |
            Maintain `hidden` behaviour when overriding `display` values.
            
            category: global
            */
            [hidden] { display: none; }
            
            
            /* @docs
            label: Heading Sizes
            
            note: |
            Switch to rem units for headings
            
            category: typography
            */
            
            :root {

                --h1-font-size: 2.00rem; /*2.00rem;*/
                --h2-font-size: 1.50rem; /*1.50rem;*/
                --h3-font-size: 1.20rem; /*1.17rem;*/
                --h4-font-size: 1.10rem; /*1.00rem;*/
                --h5-font-size: 1.05rem; /*0.83rem;*/
                --h6-font-size: 1.00rem; /*0.67rem;*/
                
                --text-font-weight: 400;

                --h1-font-weight: 600;
                --h2-font-weight: 600;
                --h3-font-weight: 500;
                --h4-font-weight: 500;
                --h5-font-weight: 500;
                --h6-font-weight: 400;
            }

            body { font-weight: var(--text-font-weight); }

            h1 { font-size: var(--h1-font-size); font-weight: var(--h1-font-weight); }
            h2 { font-size: var(--h2-font-size); font-weight: var(--h2-font-weight); }
            h3 { font-size: var(--h3-font-size); font-weight: var(--h3-font-weight); }
            h4 { font-size: var(--h4-font-size); font-weight: var(--h4-font-weight); }
            h5 { font-size: var(--h5-font-size); font-weight: var(--h5-font-weight); }
            h6 { font-size: var(--h6-font-size); font-weight: var(--h6-font-weight); }
            
            
            /* @docs
            label: H1 Margins
            
            note: |
            Keep h1 margins consistent, even when nested.
            
            category: typography
            */
            h1 { margin: 0.67em 0; }
            
            
            /* @docs
            label: Pre Wrapping
            
            note: |
            Overflow by default is bad...
            
            category: typography
            */
            pre { white-space: pre-wrap; }
            
            
            /* @docs
            label: Horizontal Rule
            
            note: |
            1. Solid, thin horizontal rules
            2. Remove Firefox `color: gray`
            3. Remove default `1px` height, and common `overflow: hidden`
            
            category: typography
            */
            hr {
            border-style: solid;
            border-width: 1px 0 0;
            color: inherit;
            height: 0;
            overflow: visible;
            }
            
            
            /* @docs
            label: Responsive Embeds
            
            note: |
            1. Block display is usually what we want
            2. The `vertical-align` removes strange space-below in case authors overwrite the display value
            3. Responsive by default
            4. Audio without `[controls]` remains hidden by default
            
            category: embedded elements
            */
            img, svg, video, canvas, audio, iframe, embed, object {
                display: block;
                max-width: 100%;
            }
            img, svg, video, canvas, audio, iframe, embed, object {
                vertical-align: middle;
            }
            audio:not([controls]) { display:none; }
            
            
            /* @docs
            label: Responsive Images
            
            note: |
            These new elements display inline by default,
            but that's not the expected behavior for either one.
            This can interfere with proper layout and aspect-ratio handling.
            
            1. Remove the unnecessary wrapping `picture`, while maintaining contents
            2. Source elements have nothing to display, so we hide them entirely
            
            category: embedded elements
            */
            picture { display: contents; }
            source  { display: none; }
            
            
            /* @docs
            label: Aspect Ratios
            
            note: |
            Maintain intrinsic aspect ratios when `max-width` is applied.
            `iframe`, `embed`, and `object` are also embedded,
            but have no intrinsic ratio,
            so their `height` needs to be set explicitly.
            
            category: embedded elements
            */
            img, svg, video, canvas {
            height: auto;
            }
            
            
            /* @docs
            label: Audio Width
            
            note: |
            There is no good reason elements default to 300px,
            and audio files are unlikely to come with a width attribute.
            
            category: embedded elements
            */
            audio { width: 100%; }
            
            /* @docs
            label: Image Borders
            
            note: |
            Remove the border on images inside links in IE 10 and earlier.
            
            category: legacy browsers
            */
            img { border-style: none; }
            
            
            /* @docs
            label: SVG Overflow
            
            note: |
            Hide the overflow in IE 10 and earlier.
            
            category: legacy browsers
            */
            svg { overflow: hidden; }
            
            
            /* @docs
            label: HTML5 Elements
            
            note: |
            Default block display on HTML5 elements.
            For oldIE to apply this styling one needs to add some JS as well (i.e. `document.createElement("main")`)
            
            links:
            - https://www.sitepoint.com/html5-older-browsers-and-the-shiv/
            
            category: legacy browsers
            */
            article, aside, details, figcaption, figure, footer, header, hgroup, main, nav, section {
            display: block;
            }
            
            
            /* @docs
            label: Checkbox & Radio Inputs
            
            note: |
            1. Add the correct box sizing in IE 10
            2. Remove the padding in IE 10
            
            category: legacy browsers
            */
            [type='checkbox'],
            [type='radio'] {
            box-sizing: border-box;
            padding: 0;
            }
            
                        
            /* @docs
            label: Reminders
            version: 0.1.0-beta.2
            
            note: |
            All the remedies in this file are commented out by default,
            because they could cause harm as general defaults.
            These should be used as reminders
            to handle common styling issues
            in a way that will work for your project and users.
            Read, explore, uncomment, and edit as needed.
            
            category: file
            */
            
            
            /* @docs
            label: List Style
            
            note: |
            List styling is not usually desired in navigation,
            but this also removes list-semantics for screen-readers
            
            links:
            - https://github.com/mozdevs/cssremedy/issues/15
            
            category: navigation
            */
            nav ul {
            list-style: none;
            }
            
            
            /* @docs
            label: List Voiceover
            
            note: |
            1. Add zero-width-space to prevent VoiceOver disable
            2. Absolute position ensures no extra space
            
            links:
            - https://unfetteredthoughts.net/2017/09/26/voiceover-and-list-style-type-none/
            
            category: navigation
            */
            nav li:before {
            content: "\200B";
            position: absolute;
            }
            
            
            /* @docs
            label: Reduced Motion
            
            note: |
            1. Immediately jump any animation to the end point
            2. Remove transitions & fixed background attachment
            
            links:
            - https://github.com/mozdevs/cssremedy/issues/11
            
            category: accessibility
            */
            @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {

                animation-delay: -1ms !important;
                animation-duration: 1ms !important;
                animation-iteration-count: 1 !important;
                background-attachment: initial !important;
                scroll-behavior: auto !important;
                transition-delay: 0s !important;
                transition-duration: 0s !important;
            }
            }
            
            
            /* @docs
            label: Line Heights
            
            note: |
            The default `normal` line-height is tightly spaced,
            but takes font-metrics into account,
            which is important for many fonts.
            Looser spacing may improve readability in latin type,
            but may cause problems in some scripts --
            from cusrive/fantasy fonts to
            [Javanese](https://jsbin.com/bezasax/1/edit?html,css,output),
            [Persian](https://jsbin.com/qurecom/edit?html,css,output),
            and CJK languages.
            
            links:
            - https://github.com/mozdevs/cssremedy/issues/7
            - https://jsbin.com/bezasax/1/edit?html,css,output
            - https://jsbin.com/qurecom/edit?html,css,output
            
            todo: |
            - Use `:lang(language-code)` selectors?
            - Add typography remedies for other scripts & languages...
            
            category: typography
            */
            html { line-height: 1.5; }
            h1, h2, h3, h4, h5, h6 { line-height: 1.25; }
            caption, figcaption, label, legend { line-height: 1.375; } 
    
        <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
    }

    function css_normalize_normalize($layer = "normalize")
    {
        heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 
            
            /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */
            
            /* Document
             ========================================================================== */
            
            /**
             * 1. Correct the line height in all browsers.
             * 2. Prevent adjustments of font size after orientation changes in iOS.
             */
            
            html {
                line-height: 1.15; /* 1 */
                -webkit-text-size-adjust: 100%; /* 2 */
            }
            
            /* Sections
            ========================================================================== */
            
            /**
            * Remove the margin in all browsers.
            */
            
            body {
            margin: 0;
            }
            
            /**
            * Render the `main` element consistently in IE.
            */
            
            main {
            display: block;
            }
            
            /**
            * Correct the font size and margin on `h1` elements within `section` and
            * `article` contexts in Chrome, Firefox, and Safari.
            */
            
            h1 {
            font-size: 2em;
            margin: 0.67em 0;
            }
            
            /* Grouping content
            ========================================================================== */
            
            /**
            * 1. Add the correct box sizing in Firefox.
            * 2. Show the overflow in Edge and IE.
            */
            
            hr {
            box-sizing: content-box; /* 1 */
            height: 0; /* 1 */
            overflow: visible; /* 2 */
            }
            
            /**
            * 1. Correct the inheritance and scaling of font size in all browsers.
            * 2. Correct the odd `em` font sizing in all browsers.
            */
            
            pre {
            font-family: monospace, monospace; /* 1 */
            font-size: 1em; /* 2 */
            }
            
            /* Text-level semantics
            ========================================================================== */
            
            /**
            * Remove the gray background on active links in IE 10.
            */
            
            a {
            background-color: transparent;
            }
            
            /**
            * 1. Remove the bottom border in Chrome 57-
            * 2. Add the correct text decoration in Chrome, Edge, IE, Opera, and Safari.
            */
            
            abbr[title] {
            border-bottom: none; /* 1 */
            text-decoration: underline; /* 2 */
            text-decoration: underline dotted; /* 2 */
            }
            
            /**
            * Add the correct font weight in Chrome, Edge, and Safari.
            */
            
            b,
            strong {
            font-weight: bolder;
            }
            
            /**
            * 1. Correct the inheritance and scaling of font size in all browsers.
            * 2. Correct the odd `em` font sizing in all browsers.
            */
            
            code,
            kbd,
            samp {
            font-family: monospace, monospace; /* 1 */
            font-size: 1em; /* 2 */
            }
            
            /**
            * Add the correct font size in all browsers.
            */
            
            small {
            font-size: 80%;
            }
            
            /**
            * Prevent `sub` and `sup` elements from affecting the line height in
            * all browsers.
            */
            
            sub,
            sup {
            font-size: 75%;
            line-height: 0;
            position: relative;
            vertical-align: baseline;
            }
            
            sub {
            bottom: -0.25em;
            }
            
            sup {
            top: -0.5em;
            }
            
            /* Embedded content
            ========================================================================== */
            
            /**
            * Remove the border on images inside links in IE 10.
            */
            
            img {
            border-style: none;
            }
            
            /* Forms
            ========================================================================== */
            
            /**
            * 1. Change the font styles in all browsers.
            * 2. Remove the margin in Firefox and Safari.
            */
            
            button:not(.transparent),
            input,
            optgroup,
            select,
            textarea {
            font-family: inherit; /* 1 */
            font-size: 100%; /* 1 */
            line-height: 1.15; /* 1 */
            margin: 0; /* 2 */
            }
            
            /**
            * Show the overflow in IE.
            * 1. Show the overflow in Edge.
            */
            
            button,
            input { /* 1 */
            overflow: visible;
            }
            
            /**
            * Remove the inheritance of text transform in Edge, Firefox, and IE.
            * 1. Remove the inheritance of text transform in Firefox.
            */
            
            button,
            select { /* 1 */
            text-transform: none;
            }
            
            /**
            * Correct the inability to style clickable types in iOS and Safari.
            */
            
            button:not(.transparent),
            [type="button"]:not(.transparent),
            [type="reset"]:not(.transparent),
            [type="submit"]:not(.transparent) {
            -webkit-appearance: button;
            appearance: button;/* added by DOM */
            }
            
            /**
            * Remove the inner border and padding in Firefox.
            */
            
            button::-moz-focus-inner,
            [type="button"]::-moz-focus-inner,
            [type="reset"]::-moz-focus-inner,
            [type="submit"]::-moz-focus-inner {
            border-style: none;
            padding: 0;
            }
            
            /**
            * Restore the focus styles unset by the previous rule.
            */
            
            button:-moz-focusring,
            [type="button"]:-moz-focusring,
            [type="reset"]:-moz-focusring,
            [type="submit"]:-moz-focusring {
            outline: 1px dotted ButtonText;
            }
            
            /**
            * Correct the padding in Firefox.
            */
            
            fieldset {
            padding: 0.35em 0.75em 0.625em;
            }
            
            /**
            * 1. Correct the text wrapping in Edge and IE.
            * 2. Correct the color inheritance from `fieldset` elements in IE.
            * 3. Remove the padding so developers are not caught out when they zero out
            *    `fieldset` elements in all browsers.
            */
            
            legend {
            box-sizing: border-box; /* 1 */
            color: inherit; /* 2 */
            display: table; /* 1 */
            max-width: 100%; /* 1 */
            padding: 0; /* 3 */
            white-space: normal; /* 1 */
            }
            
            /**
            * Add the correct vertical alignment in Chrome, Firefox, and Opera.
            */
            
            progress {
            vertical-align: baseline;
            }
            
            /**
            * Remove the default vertical scrollbar in IE 10+.
            */
            
            textarea {
            overflow: auto;
            }
            
            /**
            * 1. Add the correct box sizing in IE 10.
            * 2. Remove the padding in IE 10.
            */
            
            [type="checkbox"],
            [type="radio"] {
            box-sizing: border-box; /* 1 */
            padding: 0; /* 2 */
            }
            
            /**
            * Correct the cursor style of increment and decrement buttons in Chrome.
            */
            
            [type="number"]::-webkit-inner-spin-button,
            [type="number"]::-webkit-outer-spin-button {
            height: auto;
            }
            
            /**
            * 1. Correct the odd appearance in Chrome and Safari.
            * 2. Correct the outline style in Safari.
            */
            
            [type="search"] {
            -webkit-appearance: textfield; /* 1 */
            appearance: textfield; /* added by DOM */
            outline-offset: -2px; /* 2 */
            }
            
            /**
            * Remove the inner padding in Chrome and Safari on macOS.
            */
            
            [type="search"]::-webkit-search-decoration {
            -webkit-appearance: none;
            appearance: none; /* added by DOM */
            }
            
            /**
            * 1. Correct the inability to style clickable types in iOS and Safari.
            * 2. Change font properties to `inherit` in Safari.
            */
            
            ::-webkit-file-upload-button {
            -webkit-appearance: button; /* 1 */
            appearance: button; /* added by DOM */
            font: inherit; /* 2 */
            }
            
            /* Interactive
            ========================================================================== */
            
            /*
            * Add the correct display in Edge, IE 10+, and Firefox.
            */
            
            details {
                display: block;
            }
            
            /*
            * Add the correct display in all browsers.
            */
            
            summary {
                display: list-item;
            }
            
            /* Misc
            ========================================================================== */
            
            /**
            * Add the correct display in IE 10+.
            */
            
            template {
                display: none;
            }
            
            /**
            * Add the correct display in IE 10.
            */
            
            [hidden] {
                display: none;
            }

        <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
    }

    #endregion
    #region Color vars

    function user_color($scheme, $vars, $default)
    {
        $light = !!get("light", get("light_default", false));

        if (($light && $scheme == "light") || (!$light && $scheme == "dark"))
        {
            if (!is_array($vars)) $vars = array($vars);
            $vars[] = "theme_color";

            while (count($vars) > 0)
            {
                $var = array_shift($vars);

                $val = get($scheme."-".$var); if ($val !== false) return $val;
                $val = get($scheme."_".$var); if ($val !== false) return $val;
                $val = get(            $var); if ($val !== false) return $val;
            }
        }
        
        return $default;
    }

    function css_vars_color_scheme_light_base($tab = 1)
    {
        heredoc_start(-2 + $tab); ?><style>:root {<?php heredoc_flush(null); ?> 

            --light-theme-color:                        <?= user_color("light", "theme_color",  "#8A0009") ?>;
            --light-accent-color:                       <?= user_color("light", "accent_color", "#112299") ?>;

            --light-link-color:                         <?= user_color("light", [                      "link_color", "theme_color"  ], "var(--light-theme-color,  var(--theme-color,  #aa4455))") ?>;
            --light-link-color-accent:                  <?= user_color("light", [ "link_color_accent", "link_color", "accent_color" ], "var(--light-accent-color, var(--accent-color, #cc1133))") ?>;

            --light-text-on-background-darker-color:    <?= "#000000" ?>;
            --light-text-on-background-color:           <?= "#0d0d0d" ?>;
            --light-text-on-background-lighter-color:   <?= "#1a1a1a" ?>;

            --light-background-darker-color:            <?= "#e5e5e5" ?>;
            --light-background-color:                   <?= "#f2f2f2" ?>;
            --light-background-lighter-color:           <?= "#ffffff" ?>;
            
            --light-text-on-theme-darker-color:         <?= "#e5e5e5" ?>;
            --light-text-on-theme-color:                <?= "#f2f2f2" ?>;
            --light-text-on-theme-lighter-color:        <?= "#ffffff" ?>;

            --light-text-on-accent-darker-color:        <?= "#e5e5e5" ?>;
            --light-text-on-accent-color:               <?= "#f2f2f2" ?>;
            --light-text-on-accent-lighter-color:       <?= "#ffffff" ?>;

        <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
    }

    function css_vars_color_scheme_dark_base($tab = 1)
    {
        heredoc_start(-2 + $tab); ?><style>:root {<?php heredoc_flush(null); ?> 
        
            --dark-theme-color:                         <?= user_color("dark", "theme_color",  "#FFB7F8") ?>; /* #ff6eff */
            --dark-accent-color:                        <?= user_color("dark", "accent_color", "#64DEFE") ?>; /* #22ccee */

            --dark-link-color:                          <?= user_color("dark", [                      "link_color", "theme_color"  ], "var(--dark-theme-color,  var(--theme-color,  #FFBEC7))") /* TODO on  lighter background => #FF91FF; */ ?>;
            --dark-link-color-accent:                   <?= user_color("dark", [ "link_color_accent", "link_color", "accent_color" ], "var(--dark-accent-color, var(--accent-color, #64DEFE))") ?>;

            --dark-text-on-background-darker-color:     <?= "#e5e5e5" ?>;
            --dark-text-on-background-color:            <?= "#f2f2f2" ?>;
            --dark-text-on-background-lighter-color:    <?= "#ffffff" ?>;
            
            --dark-background-darker-color:             <?= "#101012" ?>; /* #000000 no need to be that dark anymore with new default font sizez */
            --dark-background-color:                    <?= "#161618" ?>; /* #0d0d0d no need to be that dark anymore with new default font sizez */
            --dark-background-lighter-color:            <?= "#181819" ?>; /* #1a1a1a no need to be that dark anymore with new default font sizez */
            
            --dark-text-on-theme-darker-color:          <?= "#000000" ?>;
            --dark-text-on-theme-color:                 <?= "#0d0d0d" ?>;
            --dark-text-on-theme-lighter-color:         <?= "#1a1a1a" ?>;
            
            --dark-text-on-accent-darker-color:         <?= "#000000" ?>;
            --dark-text-on-accent-color:                <?= "#0d0d0d" ?>;
            --dark-text-on-accent-lighter-color:        <?= "#1a1a1a" ?>;

        <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
    }

    function css_vars_color_scheme_print_base($tab = 1)
    {
        heredoc_start(-2 + $tab); ?><style>:root {<?php heredoc_flush(null); ?> 
    
            --print-theme-color:                        <?= "#222222" ?>;
            --print-accent-color:                       <?= "#000000" ?>;

            --print-link-color:                         <?= "#1b35ff" ?>;
            --print-link-color-accent:                  <?= "#0000ff" ?>;

            --print-text-on-background-darker-color:    <?= "#eeeeee" ?>;
            --print-text-on-background-color:           <?= "#f7f7f7" ?>;
            --print-text-on-background-lighter-color:   <?= "#ffffff" ?>;
            
            --print-background-darker-color:            <?= "#dddddd" ?>;
            --print-background-color:                   <?= "#eeeeee" ?>;
            --print-background-lighter-color:           <?= "#ffffff" ?>;
            
            --print-text-on-theme-darker-color:         <?= "#dddddd" ?>;
            --print-text-on-theme-color:                <?= "#eeeeee" ?>;
            --print-text-on-theme-lighter-color:        <?= "#ffffff" ?>;
            
            --print-text-on-accent-darker-color:        <?= "#dddddd" ?>;
            --print-text-on-accent-color:               <?= "#eeeeee" ?>;
            --print-text-on-accent-lighter-color:       <?= "#ffffff" ?>;

        <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
    }

    function css_vars_color_scheme_light_brands($tab = 1)
    {
        heredoc_start(-2 + $tab); ?><style>:root {<?php heredoc_flush(null); ?> 

            <?= brand_color_css_properties("#dddddd", 35, "light") ?> 

        <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
    }

    function css_vars_color_scheme_dark_brands($tab = 1)
    {
        heredoc_start(-2 + $tab); ?><style>:root {<?php heredoc_flush(null); ?> 
    
            <?= brand_color_css_properties("#222222", 35, "dark") ?> 

        <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
    }

    function brands_css_vars_color_scheme($theme, $tab = 1) { return delayed_component("_".__FUNCTION__, "$theme,$tab", 3); }
    function _brands_css_vars_color_scheme($theme_tab)
    {
        $theme_tab = explode(",", $theme_tab);
        $theme = $theme_tab[0];
        $tab   = $theme_tab[1];

        $css = "";
        //$css = ":root {";
            
            foreach (brands() as $brand) {
                
                $fn     = "dom\\color_$brand"; // For php 5.6 compatibility
                $colors = $fn();
                $colors = is_array($colors) ? $colors : array($colors);

                                                $css .= eol()."--color-$brand:            var(--$theme-color-$brand);";
                foreach ($colors as $c => $_) { $css .= eol()."--color-$brand-".($c+1).": var(--$theme-color-$brand-".($c+1).");"; }

                } 
                
        //$css .= "}";

        return raw_css($css);
    }

    function css_vars_color_scheme($theme, $tab = 1)
    {
        $other = $theme == "dark" ? "light" : "dark";

        heredoc_start(-2 + $tab); ?><style>:root {<?php heredoc_flush(null); ?> 

            --theme-color:                      var(--<?= $theme ?>-theme-color,                            #990011);
            --accent-color:                     var(--<?= $theme ?>-accent-color,                           #112299);
            
            --link-color:                       var(--<?= $theme ?>-link-color,                             #aa4455);
            --link-color-accent:                var(--<?= $theme ?>-link-color-accent,                      #cc1133);

            --background-darker-color:          var(--<?= $theme ?>-background-darker-color,                #e5e5e5);
            --background-color:                 var(--<?= $theme ?>-background-color,                       #f2f2f2);
            --background-lighter-color:         var(--<?= $theme ?>-background-lighter-color,               #ffffff);

            --text-on-background-darker-color:  var(--<?= $theme ?>-text-on-background-darker-color,        #000000);
            --text-on-background-color:         var(--<?= $theme ?>-text-on-background-color,               #0d0d0d);
            --text-on-background-lighter-color: var(--<?= $theme ?>-text-on-background-lighter-color,       #1a1a1a);

            --text-on-theme-color-down:         var(--<?= $theme ?>-text-on-theme-<?= $other ?>er-color,    #e5e5e5);
            --text-on-theme-color:              var(--<?= $theme ?>-text-on-theme-color,                    #f2f2f2);
            --text-on-theme-color-accent:       var(--<?= $theme ?>-text-on-theme-<?= $theme ?>er-color,    #ffffff);

            --text-on-accent-color-down:        var(--<?= $theme ?>-text-on-accent-<?= $other ?>er-color,   #e5e5e5);
            --text-on-accent-color:             var(--<?= $theme ?>-text-on-accent-color,                   #f2f2f2);
            --text-on-accent-color-accent:      var(--<?= $theme ?>-text-on-accent-<?= $theme ?>er-color,   #ffffff);

            --text-darker-color:                <?= "var(--text-on-background-darker-color   );" ?>
            --text-color:                       <?= "var(--text-on-background-color          );" ?>
            --text-lighter-color:               <?= "var(--text-on-background-lighter-color  );" ?>

            --transparent-fill-color:       transparent;

            <?= brands_css_vars_color_scheme($theme, $tab) ?> 

        <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
    }

    #endregion
    #region vars definitions in appropriate dark/light sections

    function css_base_colors_vars_schemes($layer = "base-colors")
    {
        return css_root(

            eol(1)."color-scheme: light dark;".

            eol(2).css_vars_color_scheme_light_base().
            eol(2).css_vars_color_scheme_light_brands().            (is_callable("dom\\css_vars_color_scheme_light_brands_toolbar") ? (
            eol(2).css_vars_color_scheme_light_brands_toolbar().    "") : "").

            eol(2).css_vars_color_scheme_dark_base().
            eol(2).css_vars_color_scheme_dark_brands().             (is_callable("dom\\css_vars_color_scheme_light_brands_toolbar") ? (
            eol(2).css_vars_color_scheme_dark_brands_toolbar().     "") : "").

            eol(2).css_vars_color_scheme_print_base()./*
            eol(2).css_vars_color_scheme_print_brands().
            eol(2).css_vars_color_scheme_print_brands_toolbar().*/

            "", $layer);
    }

    function css_base_colors_vars($layer = "base-colors")
    {  
        heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 

            /* Allow customization of default colors, via custom properties */

            /* Provide good, AAA contrasted in all situations, defaults */
            
            <?= css_root(css_vars_color_scheme("light")) ?> 

            /* Handling of dark theme variation */
            
            @media (prefers-color-scheme: dark) {

                <?= css_root(css_vars_color_scheme("dark", 2)) ?> 
            }

            /* Provide a way to dynamically change theme via a data-colorscheme attribute */

            [data-colorscheme='light'] {
                --theme: "light";
                <?= css_vars_color_scheme("light", 1) ?> 
            }

            [data-colorscheme='dark'] {
                --theme: "dark";
                <?= css_vars_color_scheme("dark", 1) ?> 
            }

            /* Print */

            :root {
                --style-media: "screen";
            }

            @media print {

                :root {
                    --style-media: "print";
                    <?= css_vars_color_scheme("print", 2) ?> 
                }
            }

        <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
    }

    function css_light_dark_switch($css_light, $css_dark)
    {  
        heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 

            <?= $css_light ?> 

            @media (prefers-color-scheme: dark) {

                <?= $css_dark ?> 
            }

            [data-colorscheme="light"] {

                --theme: "light";
                <?= $css_light ?> 
            }

            [data-colorscheme="dark"] {

                --theme: "dark";
                <?= $css_dark ?> 
            }

        <?php heredoc_flush("raw_css"); ?></style><?php return heredoc_stop(null);
    }

    #endregion
    #region Base CSS

    function css_base_colors($layer = "base-colors")
    {  
        heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 
    
            /* Semantic colors vars */
    
            :root {
              
                --h1-color:                     var(--theme-color,              #990011);
                --h2-color:                     var(--theme-color,              #990011);
                --h3-color:                     var(--text-color,               #0d0d0d);
                --h4-color:                     var(--text-color,               #0d0d0d);
                --h5-color:                     var(--text-color,               #0d0d0d);
                --h6-color:                     var(--text-color,               #0d0d0d);
                    
              /*--link-color:                   var(--link-color,               #aa4455);*/
                --link-hover-color:             var(--link-color-accent,        #cc1133);
    
                --border-color:                 var(--theme-color,              #990011);
        
                --forms-background-color:       var(--background-lighter-color, #ffffff);
                --forms-accent-color:           var(--theme-color,              #990011);
                
                --scrollbar-background-color:   var(--background-lighter-color, #ffffff);
                --scrollbar-accent-color:       var(--theme-color,              #990011);

                --linear-gradient:              linear-gradient(90deg, var(--theme-color), var(--accent-color));
            }
    
            /* Colors */
            
            body            { background-color: var(--background-darker-color, #eee); color: var(--text-on-background-darker-color, #000000); }
            header, .header { background-color: var(--background-color,        #ddd); color: var(--text-on-background-color,        #0d0d0d); }
            footer, .footer { background-color: var(--background-darker-color, #eee); color: var(--text-on-background-darker-color, #000000); }
    
            input, select { color: var(--text-color); background: var(--background-lighter-color); }

            /* Articles */

            article, .article, 
            blockquote, aside           { background-color: var(--background-color);
                                                     color: var(--text-color);
                                              border-color: var(--border-color); }

            :is(article, .article) :is(
                header, .header, 
                footer, .footer, 
                blockquote, aside)      { background-color: var(--background-lighter-color); --dark-link-color: #FF91FF; }

            /* Cards */

            .card                       { background-color: var(--background-color);         color: var(--text-color); }
            .card-title                 { background-color: var(--background-lighter-color); color: var(--text-color); --dark-link-color: #FF91FF; }

         /* .card                       { border:        1px solid var(--border-color); } */
            .card                       { box-shadow:    2px 2px 8px 4px #00000033;     } /*
            .card-title                 { border-bottom: 1px solid var(--border-color); } */

            .card                           {             border-radius: var(--border-radius) } /* card has no overflow hidden, so we need children to have round radius */
            .card:not(.hz) > *:first-child  {    border-top-left-radius: var(--border-radius);    border-top-right-radius: var(--border-radius); }
            .card.hz       > *:first-child  {    border-top-left-radius: var(--border-radius);  border-bottom-left-radius: var(--border-radius); }
            .card:not(.hz) > *:last-child   { border-bottom-left-radius: var(--border-radius); border-bottom-right-radius: var(--border-radius); }
            .card.hz       > *:last-child   {   border-top-right-radius: var(--border-radius); border-bottom-right-radius: var(--border-radius); }

            /* Cards inside articles */

            :is(article, .article) .card               { background-color: var(--background-lighter-color); --dark-link-color: #FF91FF; }
            :is(article, .article) .card .card-title   { background-color: var(--background-lighter-color); --dark-link-color: #FF91FF; }

            /* Headlines */
         
            h1              { color: var(--h1-color) }
            h2              { color: var(--h2-color) }
            h3              { color: var(--h3-color) }
            h4              { color: var(--h4-color) }
            h5              { color: var(--h5-color) }
            h6              { color: var(--h6-color) }
            
            /* Links */
    
            :is(a, button.link)         { font-weight: 600; color: var(--link-color,       #990011); }
            :is(a, button.link):visited { font-weight: 600; color: var(--link-color,       #990011); }
            :is(a, button.link):hover   { font-weight: 600; color: var(--link-hover-color, #ff00ff); }

            button:not(.transparent) { font-weight: 600; border: none; box-shadow: 2px 2px 4px 2px #00000055; }

            /* Others */
    
            u, del          { text-decoration-color: red; }
            
            kbd {
                border-color:       var(--background-darker-color, var(--border-color, currentColor));/*
                box-shadow-color:   var(--background-darker-color);*/
                box-shadow:         var(--background-darker-color);
                box-shadow:         inset 0 -1px 0 0 var(--background-darker-color);
            }
    
            code {
                color:            var(--text-color);
                background-color: var(--background-darker-color);
                border-color:     var(--background-lighter-color, var(--border-color, currentColor));
            }
    
            strong { color: var(--accent-color) }
    
            :is(button, [type="button"], [type="submit"]).transparent {
                background-color: transparent;
                border: none;
                padding: unset;
                font-size: unset;
            }   
            :is(button, [type="button"], [type="submit"]):not(.transparent) {
                background-color: var(--accent-color);
                color: var(--text-on-accent-color);
            }    
            :is(button, [type="button"], [type="submit"]):not(.transparent):hover {
                background-color: var(--accent-color);
                color: var(--text-on-accent-color-accent);
            }
            :is(button, [type="button"], [type="submit"]).transparent:hover {
                cursor: pointer;
            }

            figcaption { color: var(--text-lighter-color) }
    
            svg { fill: var(--color, currentColor) }

            /*Disable because causes a bug on some pages where nothing can be selected anymore !!*/
            /*::selection { background-color: var(--accent-color); color: var(--text-on-accent-color); }*/
    
            /* Forms */
    
            :root           { accent-color:     var(--forms-accent-color); }
            :focus-visible  { outline-color:    var(--forms-accent-color); }
            ::marker        { color:            var(--forms-accent-color); }
    
            :is(::-webkit-calendar-picker-indicator,
                ::-webkit-clear-button,
                ::-webkit-inner-spin-button, 
                ::-webkit-outer-spin-button,
                ::-webkit-input-placeholder) { color: var(--forms-accent-color); }
        
            /* Scrollbars */

            /* Dimensions setting cannot be dissociated from color setting */
            
            *                           {  scrollbar-width: var(--scrollbar-width, 17px); }
            *::-webkit-scrollbar        {            width: var(--scrollbar-width, 17px); }
        
         /* *                           {  scrollbar-color: var(--scrollbar-accent-color, #990011) var(--scrollbar-background-color, #ffffff); }
         */ *::-webkit-scrollbar-thumb  { background-color: var(--scrollbar-accent-color, #990011); }
            *::-webkit-scrollbar-track  { background-color: var(--scrollbar-background-color, #ffffff); }
        
            /* Editable styles */
            
            style[contenteditable="true"] {
                --dark-link-color: #FF91FF;
                background-color: var(--background-lighter-color);
                border-color: var(--border-color);
            }

            /* Utilities */

            .visually-hidden:not(:focus):not(:active) {
                clip:           rect(0 0 0 0);
                clip-path:      inset(50%);
                height:         1px;
                overflow:       hidden;
                position:       absolute;
                white-space:    nowrap;
                width:          1px;
            }

            /* CD-TOP */

            .back-to-top {
                display: flex;
                align-items: center;
                justify-content: center;
                width: calc(2 * var(--line-height));
                height: auto;
                aspect-ratio: 1;
                --dark-link-color: #FF91FF;
                background-color: var(--background-lighter-color);
                border: 3px solid var(--border-color);
                border-radius: 50%;
            }
            a.back-to-top:hover { text-decoration: none }

            .back-to-top            { opacity:   0%; }
            .back-to-top:hover,
            .back-to-top-is-visible { opacity: 100%; }
            .back-to-top-fade-out   { opacity:  50%; }
    
        <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
    }
    
    function css_base_layout($layer = "base")
    {
        heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 
          
            /* My own base/remedy css  */
    
            :root {
    
                --root-font-size:           clamp(1.00rem, 0.59rem + 1.47vw, 1.25rem);
                --line-height:              clamp(1.35rem, 1.60rem + 1.70vw, 1.50rem);
    
                --max-text-width:           48rem;
                --left-text-margin-ratio:   0.5;
                --right-text-margin-ratio:  calc(1.0 - var(--left-text-margin-ratio));

                --gap:                      16px; /* No rem nor em since we want to keep that spacing when user changes font size at browser level */
                --scrollbar-width:          17px;        
                --scroll-margin:            var(--gap);
                --margin-gap:               var(--gap);
                
                --grid-default-min-width:   calc(var(--line-height) + var(--gap));

            }

            /**
             * Current "standard" hack to get viewport dimentions without unit
             */

            @property --100vw { syntax: “<length>”; initial-value: 0px; inherits: false; } 
            :root { --100vw: 100vw; --unitless-viewport-width: tan(atan2(var(--100vw), 1px)); }

            /**
             * Fluid font size
             */

            :root { 
               
                    --fluid-font-size-min-viewport-width:  320; 
                    --fluid-font-size-max-viewport-width: 1600;

                    --fluid-font-size-min: 1.0rem; 
                    --fluid-font-size-max: 1.5rem; 

                    --fluid-font-size-viewport-ratio: clamp(0, calc((var(--unitless-viewport-width) - var(--fluid-font-size-min-viewport-width)) / (var(--fluid-font-size-max-viewport-width) - var(--fluid-font-size-min-viewport-width))), 1);
                    --fluid-font-size-eased-viewport-ratio: sin(var(--fluid-font-size-viewport-ratio) * 3.14159 / 2);
                    --fluid-font-size: clamp(var(--fluid-font-size-min), var(--fluid-font-size-min) + ( var(--fluid-font-size-eased-viewport-ratio) * (var(--fluid-font-size-max) - var(--fluid-font-size-min)) ), var(--fluid-font-size-max));

                    --root-font-size: var(--fluid-font-size);
                }


    
            /* Sanitize ++ */

            * { 
                min-width:  0; 
                min-height: 0;
            }
    
            html {
                height: 100%;
                height: -webkit-fill-available;
                block-size: -webkit-fill-available;
                block-size: stretch;
                margin: 0px;
                padding: 0px;
                }
            body {
                min-height: 100%;
                min-height: -webkit-fill-available;
                min-block-size: -webkit-fill-available;
                min-block-size: stretch;
                margin: 0px;
                padding: 0px;
                /* Needed if we want this snippet to work with, say, a h1 element with top margin at the beginning of the body */
                position: absolute;
                top: 0;
                width: 100%;
                }

            main, header, .header, footer, .footer, article, .article, aside, blockquote, nav, section, details, figcaption, figure, hgroup {
                display: flow-root;
            }
            /*abbr, b, bdi, bdo, br, cite, code, data, del, dfn, em, i, ins,
            kbd, mark, meter, progress, q, s, samp, small, span, strong, 
            sub, sup, time, u, var, wbr { display: inline-block; }*/
    
            /* TODO Do not set margins */
            table, fieldset { margin-block: var(--gap) }

            /* Navigation */
                
            :where(nav, [role="navigation"]) li:before {
                content: "\200B";
                position: absolute;
                }
        
            :where(nav, [role="navigation"]) ul, [role="navigation"] {
                list-style: none;
                padding-inline-start: 0; /* Remove that arbitrary 40px padding, especialy within nav, where we already removed list item style */
                }
            [role="navigation"] ul[role="menu"], nav ul,
            [role="navigation"] { display: flex; gap: var(--gap); flex-wrap: wrap; } /* BEWARE: Do not break default flow. Do not make it nowrap */

            /* Inputs */
    
            :is(input, button):not(.transparent) {
                padding: 0.25em 0.5rem;
            }
                
            /* Tables */
            
            table {
                border-collapse: collapse;
                /*width: 100%;*/ /* 100% would overflow when margins/paddings */
            }
            th, td {
                padding: 0.25rem;
                text-align: left;
                border: 1px solid currentColor;
            }
    
            /* Editable styles */
            
            style[contenteditable="true"] {
                display: inline-block;
                width: 100%;
                font-family: monospace;
                white-space: pre-wrap;
                font-size: min(2vw, 0.7em);
                border-width: 1px;
                border-style: dotted;
                padding: var(--gap);
            }
    
            /* Typography */

            html                    { hanging-punctuation: first allow-end last; font-size: var(--root-font-size); line-height: var(--line-height); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

            body                    { text-underline-offset: 0.24em; } /* .24 and not .25 to accomodate line heights of 1.25em with hidden overflow */
    
            body                    { word-break: break-word; text-wrap: balance; }
            .grid *                 { word-break: normal; /*overflow: hidden;*/ text-overflow: ellipsis;  } /* TODO: WHy that ? */
        
            body,h1,h2,h3,h4,h5,h6  { font-family: <?= string_system_font_stack() ?>; } /* TODO: Aren't headlines inheriting it? */
    
                  nav a, [role="navigation"] a          { text-decoration: none }
            a:not(nav a, [role="navigation"] a)         { text-decoration-thickness: 0.5px }
            a:not(nav a, [role="navigation"] a):hover   { text-decoration-thickness: 1.5px }

          /*ins, abbr, acronym      { } */
            u                       { text-decoration-style: wavy; }
    
            kbd {
                display:        inline-block;
                border:         2px solid currentColor;
                border-radius:  0.25rem;
                padding:        0.1em 0.2rem;
                font-size:      0.825em;
            }
    
            code {
                border:         2px solid currentColor;
                border-radius:  0.1rem;
                padding:        0.1em 0.2rem;
                line-height:    calc(var(--line-height) + 0.2em);
                width:          fit-content;
            }

            pre { 
                white-space: pre-wrap; /* Otherwise overflow everywhere */
                font-size: clamp(.5em, 3.5vw, 1em);
            }
        
            /* Layout */
            
            /* Main content inflate (makes footer sticky) */
    
            body        { display: flex; flex-direction: column; gap: 0; min-height: 100vh; } 

            /* V1 */
            /*
            body > main { flex-grow: 1; }
            */
            /* V2 */
            /*
            body {
                justify-content: center;
                align-items:     center;
            }
            body > :is(header, footer, main)
            {
                width: 100%;
            }*/
            /* V3 */
            
            body {
                justify-content: center;
                align-items:     center;
            }
            :is(body > header) + :is(body > main)  {
                flex-grow: 1; 
            }
            body > :is(header, footer, main)
            {
                width: 100%;
            }


    
            /* Main */
    
            main { 
                
                width:          100%;
                text-align:     left;
                padding-top:    unset; /*
                margin-block:   var(--gap); */
            }

            main > :is(header, .header, footer, .footer, article, .article, aside, blockquote, nav, section, details, figcaption, figure, hgroup) {

                margin-block: var(--gap);
            }
    
            /* Was bad looking */
    
            summary { cursor: pointer; } /*
            details { padding-block: var(--gap); } */
        
            :is(h1,h2,h3,h4,h5,h6), [id] {
                scroll-margin: var(--scroll-margin) 0 0 0;
            }

            /* Headlines */
    
            h1 {
                margin-inline: var(--gap);
                margin-block-start: 1.2em;
                margin-block-end: 1.0em;
                scroll-margin: 4em;
            }          
            h2, h3 {
                margin-block-start: 1.2em;
                margin-block-end: 1.0em;
                scroll-margin: 4em;
            }            
            h4, h5, h6 {
                margin-block-start: 1.0em;
                margin-block-end: 0.8em;
            }
    
            h2 { text-transform: uppercase; }

            summary :is(h1,h2,h3,h4,h5,h6) { display: inline-block; }
            
    
            /* Blockquote */
    
            blockquote  { border-left: 3px solid var(--border-color, currentColor); }
            aside       { border:      3px solid var(--border-color, currentColor); }
    
            /* Text limited width & heroes full width */
    
                  :where(main, header, .header, nav, footer, .footer, article, .article, aside, blockquote, section, details, figcaption, figure, hgroup, [role="document"], [role="banner"], [role="menubar"]) >
            *:where(:not(main, header, .header, nav, footer, .footer, article, .article, aside, blockquote, section, details, figcaption, figure, hgroup, [role="document"], [role="banner"], [role="menubar"], span, a)) {

                --margin-inline: var(--gap);    
                  margin-inline: var(--margin-inline);
            }
    
            :is(main, header, .header, footer, .footer) > * {

                --max-text-width-margin-inline: clamp(var(--gap), calc(var(--left-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--left-text-margin-ratio) * 100%)) 
                                                clamp(var(--gap), calc(var(--right-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--right-text-margin-ratio) * 100%));

                --margin-inline: var(--max-text-width-margin-inline);    
                  margin-inline: var(--margin-inline);
            }

            /* Articles */
    
            body > :is(main, header, .header, footer, .footer) > :is(article, .article) {

                --mobile-no-margin-breakpoint: 400px;
                --margin-gap: clamp(0px, calc(100vw - var(--mobile-no-margin-breakpoint)), var(--gap));
    
                --max-text-width-margin-inline: clamp(var(--margin-gap), calc(var(--left-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--left-text-margin-ratio) * 100%)) 
                                                clamp(var(--margin-gap), calc(var(--right-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--right-text-margin-ratio) * 100%));

                --margin-inline: var(--max-text-width-margin-inline);
                  margin-inline: var(--margin-inline);
            }
    
            body > :is(main, header, .header, footer, .footer) > :is(article, .article) > :is(.grid, .flex) {

                margin-inline: var(--margin-gap);
                padding-block: var(--gap);
            }

            /* Others */

            :is(main, header, .header, footer, .footer, article, .article, section, figure) > :is(img, figure, picture, svg, video, canvas, audio, iframe, embed, object) { 
              
                --margin-inline: 0;    
                  margin-inline: var(--margin-inline);
            }

            /* Cards */

            :is(.card-title, .card-media, .card-text, .card-actions) {

                overflow: hidden;
            }

            .card-media > * {

                --margin-inline: 0;    
                margin-inline: var(--margin-inline);
            }
            /* Disabled until I remember why I did this */ /* 
            .card-media > iframe {

                --margin-inline: calc(0.5 * var(--gap));
                margin-inline: var(--margin-inline);
                width: calc(100% - calc(2 * var(--margin-inline)));
            }*/

            .card-title h1 {      

                --margin-inline: .5rem;
    
                margin-inline:      var(--margin-inline);
                margin-block-start: var(--margin-inline);
                margin-block-end:   var(--margin-inline);
            }
              
            /* Images */
    
            video, iframe, img, amp-img, picture, figure, canvas {
                  width: 100%;
                  height: auto;
                  vertical-align: middle;
                  display: inline-block;
                }
                
            video, iframe, img, amp-img {
                max-width: 100%;
                aspect-ratio: calc(var(--width, 16) / var(--height, 10));
                object-fit: cover; 
                }
    
            :is(video, iframe, img, amp-img).loading { object-fit: none; }
    
            figure { margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px;  }
    
            img[src*=".jpg"], picture, iframe {
                background-image:       url(<?= path("img/loading.svg") ?>);
                background-repeat:      no-repeat;
                background-position:    center;
            }
    
            /* Figures */
    
            figcaption { text-align: center; }
    
            /* UTILITY CLASSES */
    
            /* Should it be part of this base (dom framework independant) css ? */
        
            :is(a, button.link):not(:has(img,picture,video,audio,svg,iframe))[href^="//"]:after, 
            :is(a, button.link):not(:has(img,picture,video,audio,svg,iframe))[href^="http"]:after, 
            :is(a, button.link):not(:has(img,picture,video,audio,svg,iframe)).external:after {

                display: inline-block;
                content: '';

                background-color: currentColor;
                mask: url('data:image/svg+xml;utf8,<svg height="1024" width="768" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M640 768H128V257.90599999999995L256 258V128H0v768h768V576H640V768zM384 128l128 128L320 448l128 128 192-192 128 128V128H384z"/></svg>');
                mask-size: cover;
                                
                position: relative;
                width:  .60em;
                height: .75em;
                top:    0.0em;
                left:   0.2em;

                margin-right: 0.33em;
                
                opacity: .4;
            }    
            a:not(:has(img,picture,video,audio,svg,iframe))[href^="//"]:hover:after, 
            a:not(:has(img,picture,video,audio,svg,iframe))[href^="http"]:hover:after, 
            a:not(:has(img,picture,video,audio,svg,iframe)).external:hover:after {

                opacity: 1.0;
            }

            @media print {
                        
                a:not(:has(img,picture,video,audio,svg,iframe))[href^="//"]:after, 
                a:not(:has(img,picture,video,audio,svg,iframe))[href^="http"]:after, 
                a:not(:has(img,picture,video,audio,svg,iframe)).external:after {

                    content: attr(href);
                }
            }

            /* Service worker install "call to action" */
            
            .app-install, .app-install.hidden   { display: none }
            .app-install.visible                { display: inline-block }

            /* Grid & Flex */
    
            .grid { 

                display: grid;
                grid-gap: var(--gap);
                grid-template-columns: repeat(auto-fit, minmax(var(--grid-default-min-width), 1fr));
                
                /*overflow: hidden;*/ /* if overflow is hidden, then needs to have a padding equivalent to elements box shadow size */
            }

            .flex {

                display: flex;
                flex-wrap: wrap;
                gap: var(--gap);
            }

            /* Icons */
    
            .icon {
    
                display: inline-block;
                height: auto;
                vertical-align: middle;
            }    
            .icon svg {
    
                width: var(--line-height);
            }

            /* Back to to button. TODO: remove? */

            .back-to-top {
                position: fixed;
                bottom: var(--gap);
                right: var(--gap);
            }

            /* Misc. */

            .hidden {
                display: none;
            }

            /* Print styles */

            @media print {
                    
                :root {
        
                    --max-text-width:   100%;
                    --scrollbar-width:   0px;            
                    --scroll-margin:     0px;
                }
            }
    
        <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
    }
    
    #endregion

    function styles()
    {
        return
            
            eol().comment("Layers").      style(css_layers()                   ).
            eol().comment("Normalize").   style(css_normalize_normalize()      ).
            eol().comment("Remedy").      style(css_normalize_remedy()         ).
            eol().comment("Base-Layout"). style(css_base_layout()              ).
            eol().comment("Base-Colors"). style(css_base_colors_vars_schemes() ).
                                          style(css_base_colors_vars()         ).
                                          style(css_base_colors()              ).

            // TODO: We cannot be dependent here of a plugin
            eol().comment("Base-Toolbar-Layout"). (is_callable("dom\\css_toolbar_layout") ? style(css_toolbar_layout()) : "").
            eol().comment("Base-Toolbar-Colors"). (is_callable("dom\\css_toolbar_colors") ? style(css_toolbar_colors()) : "").
            eol().comment("Base-Brands").         (is_callable("dom\\css_brands")         ? style(css_brands())         : "").
                              
            "";
    }

    function scripts_head($scripts)
    {   
        $profiler = debug_track_timing();

        if (!$scripts) return ""; // TODO while no test of no_js here ?

        return  script_common_head($scripts).
                script_ajax_head().
                script_inside_iframe().

                script(js_scan_and_print_head()     ).      ((!!get("script_document_events", true)) ? (
                script(js_on_document_events_head() ).
                script(js_storage()                 ).      "") : "").

                (!AMP() ? "" : (eol().comment("DOM AMP Javascript"))).
                (!AMP() ? "" : (delayed_component("_amp_scripts_head")))
        ; 
    }

    function back_to_top_link()
    {
        return eol().a("▲", url_top(), "back-to-top");
    }

    function script_google_analytics_snippet()
    {
        if (!defined("TOKEN_GOOGLE_ANALYTICS")) return "";

        if (do_not_track())
        {
            return comment("Google analytics is disabled in accordance to user's 'do-not-track' preferences");
        }

        return
            script_src("https://www.googletagmanager.com/gtag/js?id=".constant("TOKEN_GOOGLE_ANALYTICS"), false, 'async').
            script(

                eol(1) . '/*  Google analytics */ '.

                eol(2) . tab() . /*'window.ga=function() { ga.q.push(arguments) };'.

                    ' ga.q=[];'.
                    ' ga.l=+new Date;'.

                    ' ga("create",'. ' "'.constant("TOKEN_GOOGLE_ANALYTICS").'",'. ' "auto"'.   ');'.
                    ' ga("set",'.    ' "anonymizeIp",'.                           ' true'.     ');'.
                    ' ga("set",'.    ' "transport",'.                             ' "beacon"'. ');'.
                    ' ga("send",'.   ' "pageview"'.                                            ');'.*/

                    ' window.dataLayer = window.dataLayer || [];'.
                    ' function gtag(){dataLayer.push(arguments);}'.
                    ' gtag(\'js\', new Date());'.
                    ' '.
                    ' gtag(\'config\', \''.constant("TOKEN_GOOGLE_ANALYTICS").'\');'.

                eol(1)
                );
    }

    function script_google_analytics()
    {
        if (!defined("TOKEN_GOOGLE_ANALYTICS")) return "";

        if (do_not_track())
        {
            return comment("Google analytics is disabled in accordance to user's 'do-not-track' preferences");
        }

        return  eol(2) . script_google_analytics_snippet().
                eol(2) . script_src('https://www.google-analytics.com/analytics.js', false, 'async defer');
    }

    function js_scan_and_print_head()
    {
        if (has("ajax")) return '';
        
        return 'var scan_and_print = function() { alert("Images are not loaded yet"); };';
    }

    function js_scan_and_print_body()
    {
        if (has("ajax")) return '';

        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 
        
            /* SCAN AND PRINT UTILITY */
        
            on_loaded(function()
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
                    dom.log("Print");

                    var e = document.querySelector("html");

                    scan_and_print_scroll_y(e, 0, document.body.clientHeight, 500, function() { 
                    scan_and_print_scroll_y(e, document.body.clientHeight, 0, 500, function() { window.print(); }); });
                };
            });

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_pwa_install()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 
        
            /* PWA (PROGRESSIVE WEB APP) INSTALL */
  
            function onInitPWA()
            {
                let deferredPrompt = null;
                
                dom.log("Register Before Install Prompt callback");
                
                window.addEventListener("beforeinstallprompt", function(e) 
                {
                    dom.log("Before Install Prompt");
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
                                if (choiceResult.outcome === "accepted") dom.log("User accepted the A2HS prompt");
                                else                                     dom.log("User dismissed the A2HS prompt");
                                
                                deferredPrompt = null;
                            });
                        }
                        else
                        {
                            dom.log("Install promt callback not received yet");
                        }
                    }); 
                }); 
            }; 

            on_loaded(function() { onInitPWA(); });
            
        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_service_worker()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            /* SERVICE WORKER */

            <?php if (!has("ajax") && has("push_public_key")) { ?>
            function urlBase64ToUint8Array(base64String) { const padding = "=".repeat((4 - base64String.length % 4) % 4); const base64 = (base64String + padding).replace(/\-/g, "+").replace(/_/g, "/"); const rawData = window.atob(base64); return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0))); }
            <?php } ?>
            function onInitServiceWorker()
            {
                if ("serviceWorker" in navigator)
                {
                    dom.log("Service Worker is supported. Registering...");

                    navigator.serviceWorker.register("<?= path('sw.js') ?>").then(
                        
                    function(registration)
                    {
                        dom.log("ServiceWorker registration successful with scope: ", registration.scope);
                        
                        var registration_installing = registration.installing;
                        var registration_waiting    = registration.waiting;

                        if (registration_installing && registration_installing != null)
                        {
                            dom.log("Installing: State:", registration_installing.state);

                            if (registration_installing.state === "activated" && !registration_waiting)
                            {
                                v("Send Clients claim");
                                registration_installing.postMessage({type: "CLIENTS_CLAIM" });
                            }

                            registration_installing.addEventListener("statechange", function()
                            {
                                dom.log("Installing: New state:", registration_installing.state);

                                if (registration_installing.state === "activated" && !registration_waiting) 
                                {
                                    dom.log("Send Clients claim");                                    
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
                                    dom.log("User IS subscribed.");
                                }
                                else 
                                {
                                    dom.log("User is NOT subscribed.");
                                }
                            })

                            <?php if (has("push_public_key")) { ?>

                            .then(function()
                            {
                                const subscribeOptions = { userVisibleOnly: true, applicationServerKey: urlBase64ToUint8Array("<?= get("push_public_key") ?>") };
                                return registration.pushManager.subscribe(subscribeOptions);

                            }).then(function(pushSubscription)
                            {
                                dom.log("Received PushSubscription: ", JSON.stringify(pushSubscription));
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
                                dom.log("ServiceWorker registration sync is undefined");
                            }
                        });
                    },                     
                    function(err) 
                    {
                        dom.log("ServiceWorker registration failed: ", err);

                    }).catch(function(err)
                    {
                        dom.log("Service Worker registration failed: ", err);

                    });

                    /* TODO : REGISTER FOR NOTIFICATIONS ON USER GESTURE */

                    if ("PushManager" in window) 
                    {
                    /*
                        dom.log("Service Worker push notifications are supported. Registering...");

                        new Promise(function(resolve, reject) 
                        {
                            Notification.requestPermission().then(function(permission) 
                            {
                                dom.log("Notifications permissions : " + permission);
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
                            dom.log("Sync registered");

                        })
                        .catch(function(err) 
                        {
                            dom.log("It broke");
                            dom.log(err.message);
                        });
                    */
                    }
                }
                else
                {
                    dom.log("Service worker not supported");
                } 
            }

            on_loaded(function() { onInitServiceWorker(); });
            
        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_framework_material()
    {
        if ("material" != get("framework")) return "";

        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

        /* MDC (MATERIAL DESIGN COMPONENTS) FRAMEWORK */
   
        if (typeof window.mdc !== "undefined") { window.mdc.autoInit(); }
   
        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_images_loading()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            /* IMAGES LOADING */
            
            dom.log("Register images handlers");
            
            var interaction_observer = null;
                
            function on_img_error(e)
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

                if (e.getAttribute("src") != "<?= url_img_loading() ?>")
                {
                    e.setAttribute("data-src", e.getAttribute("src"));
                    e.setAttribute("src",      "<?= url_img_loading() ?>");
                }

                e.classList.add("loading");
                e.classList.add("reloading");

                e.classList.add("lazy");
                
                setTimeout(function () { 

                    e.classList.remove("lazy");
                    e.classList.add("lazy-observed");
                    
                    interaction_observer.observe(e); 

                    }, 1000);
            }
                
            function img_observer_callback(changes, observer) { 
            
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
                                                                                <?php if (!get("lazy-unload")) { ?> 
                            change.target.classList.remove("lazy-observed"); 
                            change.target.classList.remove("lazy");            <?php } ?> 
                            change.target.classList.remove("loading"); 
                            change.target.classList.remove("reloading"); 

                            change.target.classList.add("lazy-loaded"); 
                            change.target.classList.add("loaded"); 

                            change.target.setAttribute("src", datasrc);
                        
                        };                                          <?php if (!get("lazy-unload")) { ?> 
                        
                        observer.unobserve(change.target);          <?php } ?> 
                    }                                           
                    else
                    {                                                                       <?php if (!!get("lazy-unload")) { ?>
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
                
            function observe_lazy_element(e,i)
            {
                e.classList.remove("lazy");
                e.classList.add("lazy-observed");

                interaction_observer.observe(e);        
            }
      
            function scan_images() 
            {   /*
                dom.log("Scanning images");*/

                /* Handle images loading errors */
                document.querySelectorAll("img").forEach(function (e) { e.addEventListener("error", on_img_error); });

                /* Scan for lazy elements and make them observed elements */
                document.querySelectorAll("source.lazy[data-srcset]" ).forEach(observe_lazy_element);
                document.querySelectorAll(   "img.lazy[data-src]"    ).forEach(observe_lazy_element);
                document.querySelectorAll("iframe.lazy[data-src]"    ).forEach(observe_lazy_element);
            }

            on_loaded(function () {

                /* Create images intersection observer */
                /*var options = { rootMargin: '100px 100px 100px 100px' };*/
                var options = { rootMargin: '0px 0px 0px 0px' };
                interaction_observer = new IntersectionObserver(img_observer_callback, options);

                /* First images lookup (Needs to be deffered in order to work) */
                setTimeout(scan_images, 0);

                /* Images lookup after any ajax query result (that might have modified the DOM and inserted new images */
                <?php if (get("script-images-loading-auto-scan-on-ajax", true)) { ?>
                on_ajax(scan_images);
                <?php } ?>
            
                });

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_back_to_top()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 
            
            /*  BACK TO TOP BUTTON */
            
            var back_to_top_offset          =  300;
            var back_to_top_offset_opacity  = 1200;
            var back_to_top_scroll_duration =  700;
            
            function onUpdateBackToTopButton()
            {
                //dom.log("onUpdateBackToTopButton");

                var back_to_top = document.querySelector(".back-to-top");

                if (back_to_top)
                {
                    if (window.scrollY > back_to_top_offset) 
                    {
                        back_to_top.classList.add("back-to-top-is-visible")
                    }
                    else
                    {
                        back_to_top.classList.remove("back-to-top-is-visible");
                        back_to_top.classList.remove("back-to-top-fade-out");
                    }
                
                    if (window.scrollY > back_to_top_offset_opacity)
                    {
                        back_to_top.classList.add("back-to-top-fade-out");
                    }
                }
            }

            on_ready(onUpdateBackToTopButton);
            on_scroll(onUpdateBackToTopButton);
        
        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_sliders()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 
                
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

            on_loaded(initSliders);
            
        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_on_document_events_head()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            /* DOM INTERNAL READY AND LOADED CALLBACK MECHANISM */

            var event_ready  = false;
            var event_loaded = false;

            dom.ready_callbacks  = Array();
            dom.loaded_callbacks = Array();
            dom.scroll_callbacks = Array();
            dom.resize_callbacks = Array();
            dom.ajax_callbacks   = Array();

            function process_callbacks(callbacks, log, clear, event)
            {
                if (typeof log != "undefined" && !!log) dom.log("DOCUMENT " + log + " : Processing " + callbacks.length + " CALLBACKS");
                callbacks.every(function(callback) { return (false !== callback(event)); });
                if (typeof clear != "undefined" && !!clear) callbacks = [];
            }

            dom.clear_callbacks = function ()
            {
                dom.ready_callbacks  = Array();
                dom.loaded_callbacks = Array();
                dom.scroll_callbacks = Array();
                dom.resize_callbacks = Array();
                dom.ajax_callbacks   = Array();
            };

            function process_ready_callbacks()  { process_callbacks(dom.ready_callbacks,  "READY",  true); }
            function process_loaded_callbacks() { process_callbacks(dom.loaded_callbacks, "LOADED", true); }

            function on_ready(callback,  first) { /*                       */   if (!!first)  dom.ready_callbacks.unshift(callback); else  dom.ready_callbacks.push(callback); if (event_ready)                 { process_ready_callbacks();  }   /*    */ }
            function on_loaded(callback, first) { /*  on_ready(function () */ { if (!!first) dom.loaded_callbacks.unshift(callback); else dom.loaded_callbacks.push(callback); if (event_ready && event_loaded) { process_loaded_callbacks(); } } /* ); */ }
            function on_scroll(callback, first) { /* on_loaded(function () */ { if (!!first) dom.scroll_callbacks.unshift(callback); else dom.scroll_callbacks.push(callback);                                                                  } /* ); */ }
            function on_resize(callback, first) { /* on_loaded(function () */ { if (!!first) dom.resize_callbacks.unshift(callback); else dom.resize_callbacks.push(callback);                                                                  } /* ); */ }
            function on_ajax(callback,   first) { /*  on_ready(function () */ { if (!!first)   dom.ajax_callbacks.unshift(callback); else   dom.ajax_callbacks.push(callback);                                                                  } /* ); */ }
            
            function on_first_interraction(callback)
            {
                return on_loaded(function() { 

                    if (window.location.hash != "") {

                        callback();

                    } else {

                        var scrolled = false;
                        
                        on_scroll(function() {

                            if (!scrolled) {

                                scrolled = true;
                                callback();
                            }
                        });
                    }
                }); 
            }

            function on_init_event(event)
            {
                var was_not_ready_and_loaded = (!event_ready || !event_loaded);

                if (!event_ready  && event == "ready")  { event_ready  = true; dom.log("DOCUMENT READY"); process_ready_callbacks(); }
                if (!event_loaded && event == "loaded") { event_loaded = true; dom.log("DOCUMENT LOADED"); }

                if (was_not_ready_and_loaded && event_ready && event_loaded) { process_loaded_callbacks(); }
            }

            function on_ajax_reception(event) { process_callbacks(dom.ajax_callbacks, undefined, undefined, event); }

            dom.on_ready                = on_ready;
            dom.on_loaded               = on_loaded;
            dom.on_scroll               = on_scroll;
            dom.on_resize               = on_resize;
            dom.on_ajax                 = on_ajax;
            dom.on_first_interraction   = on_first_interraction;

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function js_on_document_events()
    {
        heredoc_start(-2); ?><script><?php heredoc_flush(null); ?> 

            /* DOM INTERNAL READY AND LOADED CALLBACK MECHANISM */

            window.addEventListener("load",               function(event) { on_init_event("loaded"); } );
            if (document.readyState != "loading")                         { on_init_event("ready");  }
            else document.addEventListener("DOMContentLoaded", function() { on_init_event("ready");  } );
        
            window.addEventListener("scroll", function(event) { if (event_ready && event_loaded) { process_callbacks(dom.scroll_callbacks, undefined, undefined, event); } });
            window.addEventListener("resize", function(event) { if (event_ready && event_loaded) { process_callbacks(dom.resize_callbacks, undefined, undefined, event); } });

        <?php heredoc_flush("raw_js"); ?></script><?php return heredoc_stop(null);
    }

    function script_third_parties()
    {
        $inline_js = get("inline_js", false);

        return  ("material"  == get("framework") ? (script_src('https://unpkg.com/material-components-web@'        . get("version_material")  . '/dist/material-components-web'.(is_localhost() ? '' : '.min').'.js', false, "async")) : "")
            .   ("bootstrap" == get("framework") ? (script_src('https://cdnjs.cloudflare.com/ajax/libs/popper.js/' . get("version_popper")    . '/umd/popper'                  .(is_localhost() ? '' : '.min').'.js', false, "async")) : "")
            .   ("bootstrap" == get("framework") ? (script_src('https://stackpath.bootstrapcdn.com/bootstrap/'     . get("version_bootstrap") . '/js/bootstrap'                .(is_localhost() ? '' : '.min').'.js', false, "async")) : "")
            ;
    }
    
    $hook_need_lazy_loding = array();

    function scripts_body()
    {
        if (has("ajax")) return "";

        global $hook_need_lazy_loding;
        $images_loading = !!get("script-images-loading", true) && (count($hook_need_lazy_loding) > 0 || !!get("script-images-loading", false));

        return  script_third_parties              ().
                script_ajax_body                  ().
                script_google_analytics           ().               ((!!get("script_document_events",       true)) ? (
                script(js_on_document_events      ()).   "") : ""). ((!!get("script_back_to_top",          false)) ? (
                script(js_back_to_top             ()).   "") : ""). (($images_loading                            ) ? (
                script(js_images_loading          ()).   "") : ""). ((!!get("support_sliders",              true)) ? (
                script(js_sliders                 ()).   "") : ""). ((!!get("support_service_worker",      false)) ? (
                script(js_service_worker          ()).   "") : ""). ((!!get("script_pwa_install",           true)) ? (
                script(js_pwa_install             ()).   "") : ""). ((!!get("script_framework_material",    true)) ? (
                script(js_framework_material      ()).   "") : ""). ((!!get("script_scan_and_print",        true)) ? (
                script(js_scan_and_print_body     ()).   "") : ""). ((!!get("webmentions",                 false)) ? (
                script(js_webmentions             ()).   "") : "")
                ;
    }
    
    #endregion
    #region WIP API : DOM : HTML COMPONENTS : MARKUP : BODY
    ######################################################################################################################################

    function html_comment_bgn()  { return "<!-- ";  }
    function html_comment_end()  { return " //-->"; }
    function html_comment($text) { return html_comment_bgn().$text.html_comment_end(); }

    function comment($text)          { return (has("rss") || !!get("gemini")) ? "" : html_comment($text); }
    
    function placeholder($text, $eol = 0)  { return eol($eol).html_comment("DOM_PLACEHOLDER_".str_replace(" ", "_", strtoupper($text))); }

    function cosmetic_indent($html, $tabs = 1, $container_tag = false, $container_attributes = false, $wrapper_eol = true)
    {
        // TODO THIS FUNCTION IS OUR CURRENT BOTTLENECK : WAY TOO SLOW

        if (!get("minify") && $html != "" && in_array($container_tag, array(

          /*"head",
            "script",
            "style",
            "body",
            "main",
            "article",
            "header",
            "footer",
            "section",
            "div",
            "picture",
            "pre",
            "ul",
            "ol",
            "table"*/

            )))
        {
            $dom = array();

            while (true)
            {
                $tag_bgn = "<pre>";
                $tag_end = "</pre>";
                $pos_bgn = stripos($html, $tag_bgn);
                $pos_end = stripos($html, $tag_end);

                if (false === $pos_bgn || false === $pos_end)
                {
                    $dom[] = array("html", $html);
                    break;
                }
                else
                {                
                    $dom[] = array("html", substr($html, 0, $pos_bgn));
                    $dom[] = array("pre",  substr($html, $pos_bgn, $pos_end + strlen($tag_end) - $pos_bgn));
                    
                    $html = substr($html, $pos_end + strlen($tag_end));
                }
            }

            $html_reconstructed = "";

            foreach ($dom as $section)
            {
                $html = $section[1];

                if ($section[0] == "html")
                {
                    $eol = "{{PHP_EOL}}";

                    $html = str_replace(eol(),      $eol, $html);
                    $html = str_replace(PHP_EOL,    $eol, $html);
                    $html = str_replace("\r\n",     $eol, $html);
                    $html = str_replace($eol,       "\n", $html);

                    $html = tab($tabs).str_replace("\n", eol().tab($tabs), trim($html));

                    if ($wrapper_eol) $html = eol().$html.eol();
                }
                
                $html_reconstructed .= $html;
            }

            $html = $html_reconstructed;
        }

        return $html;
    }

    function gemini_tag($tag, $html, $attributes)
    {
        $WIP =  "";//PHP_EOL."THIS PAGE IS WORK IN PROGRESS! ";

        $attributes = to_attributes($attributes);

        debug_log(json_encode(["tag" => $tag, "html" => $html, "attributes" => $attributes]));

        if (has($attributes, "hidden")) return "";
        if (in_array($tag, [ "head", "meta", "link", "style", "script", "iframe", "svg", "video", "channel" ])) return "";
        if (0 === stripos($tag, "amp-")) return "";

        if (in_array($tag, [ "hr", "br" ])) return PHP_EOL;

        if ($tag == "picture")  return "<pic>";
        if ($tag == "img")      return "<img>";

        if ("" == trim($html)) return "";

        if (in_array($tag, [ "pre"        ])) return "```".PHP_EOL.$html.PHP_EOL."```";
        if (in_array($tag, [ "blockquote" ])) return "> ".implode(" ", explode(PHP_EOL, $html));

        if ($tag == "h1") return PHP_EOL.PHP_EOL."# ".   "<h1>".            implode(" ", explode(PHP_EOL, $html)).$WIP;
        if ($tag == "h2") return PHP_EOL.PHP_EOL."## ".  "<h2>".            implode(" ", explode(PHP_EOL, $html));
        if ($tag == "h3") return PHP_EOL.PHP_EOL."### ". "<h3>".            implode(" ", explode(PHP_EOL, $html));
        if ($tag == "h4") return PHP_EOL.PHP_EOL."".     "<h4>". strtoupper(implode(" ", explode(PHP_EOL, $html)) );
        if ($tag == "h5") return PHP_EOL.PHP_EOL."".     "<h5>".            implode(" ", explode(PHP_EOL, $html));
        if ($tag == "h6") return PHP_EOL.PHP_EOL."".     "<h6>".            implode(" ", explode(PHP_EOL, $html));
        if ($tag == "h7") return PHP_EOL.PHP_EOL."".     "<h7>".            implode(" ", explode(PHP_EOL, $html));
        if ($tag == "h8") return PHP_EOL.PHP_EOL."".     "<h8>".            implode(" ", explode(PHP_EOL, $html));
        if ($tag == "h9") return PHP_EOL.PHP_EOL."".     "<h9>".            implode(" ", explode(PHP_EOL, $html));

        if ($tag == "li") 
        {
            $html = trim(/*implode(" ", explode(PHP_EOL,*/ $html/*))*/, "\t\r\n* ");

            if ($html == "")                                return PHP_EOL.  "⇨";
            if (trim(at(explode(PHP_EOL, $html), 0)) == "") return PHP_EOL."* ⇨".PHP_EOL.$html."<li>";

            return PHP_EOL."* $html<li>";
        }

        $is_block_tag = in_array($tag, array(

            "body",
            "header", "main", "footer",
            "article","section", "div",
            "table",
            "p", "ul", "ol", // TODO intricated lists
            "figure",
        ));
        
        if ($tag == "a")
        {
            $html = strip_tags(trim($html));
            $html = $html == "#" ? "" : $html;
            $html = $html == "☰" ? "" : $html;
            $html = $html == "" ? "" : "<a>$html";
        }

        //$debug = false;

        if (false !== stripos(is_array(at($attributes, "class")) ? implode(" ", at($attributes, "class", array())) : at($attributes, "class", ""), 'toolbar-title ')
        ||                   (is_array(at($attributes, "class")) ? implode(" ", at($attributes, "class", array())) : at($attributes, "class", "")) == 'toolbar-title' )
        {
            $html .= "<toolbar-title>";
        }

        if (false !== stripos(is_array(at($attributes, "class")) ? implode(" ", at($attributes, "class", array())) : at($attributes, "class", ""), 'toolbar ')
        ||                   (is_array(at($attributes, "class")) ? implode(" ", at($attributes, "class", array())) : at($attributes, "class", "")) == 'toolbar' )
        {
            $html .= "<toolbar>";
        }

        if (/*$tag  == "a" ||*/ $is_block_tag)
        {
            global $hook_images;

            if (count($hook_images) > 0)
            {
                $html .= PHP_EOL.PHP_EOL.implode(PHP_EOL, array_map(function($image) { 
             
                    $src = at($image, "src");
                    $alt = at($image, "alt", at($image, "title", "")) != "" ? at($image, "alt", at($image, "title", "")) : "<img>";

                    // Currently do not point to local images. As hosting disk space is so limited
                         if (0 === stripos($src, "gemini://")) $src = str_replace("gemini://", host_url()."/", $src);
                    else if (0 !== stripos($src, "http"))      $src = url()."/$src";
       
                    return "<a>=> $src $alt";
                
                    }, $hook_images)).PHP_EOL.PHP_EOL;

                $hook_images = array();
            }
        }

        if ($is_block_tag)
        {
            global $hook_links;

            if (count($hook_links) > 0)
            {
                $html .= PHP_EOL.PHP_EOL.implode(PHP_EOL, array_map(function($link) { 
                    
                    return "<a>=> ".at($link, "url")." ".at($link, "title");  
                
                    }, $hook_links)).PHP_EOL.PHP_EOL;

                $hook_links = array();
            }            
        }

        if ($is_block_tag)
        {
            $html = PHP_EOL.PHP_EOL.PHP_EOL.$html.PHP_EOL.PHP_EOL;

            $html = str_replace_all(
                
                [ PHP_EOL.PHP_EOL.PHP_EOL, "\r\n\r\n\r\n", "\n\n\n" ],
                PHP_EOL.PHP_EOL, 
                $html
            );
        }

        if ($tag == "body")
        {
            $html = trim(str_replace_all([
                
                "<toolbar>", "<toolbar-title>",
                "<img>", "<pic>", "<a>", "<li>", 
                "<h1>", "<h2>", "<h3>", "<h4>", "<h5>", "<h6>", "<h7>", "<h8>", "<h9>",
            
                ], "", str_replace_all(
                
                [ PHP_EOL.PHP_EOL.PHP_EOL, "\r\n\r\n\r\n", "\n\n\n" ],
                PHP_EOL.PHP_EOL, 
                $html
            )));
        }

        return $html;
    }

    function tag($tag, $html, $attributes = false, $force_display = false, $self_closing = false, $extra_attributes_raw = false)
    {
        if (!!get("gemini"))
        {
            return gemini_tag($tag, $html, $attributes);
        }

        $space_pos = strpos($tag, ' ');

        $html = cosmetic_indent($html, 1, $tag, $attributes);
        
        $prefix = "";

        $is_block_tag = in_array($tag, array(
            // HTML
            "head",
            "title",
            "meta",
            "link",
            "script",
            "style",
            "body",
            "header",
            "main",
            "footer",
            "article",
            "section",
            "div",
            "table",
            "h1","h2","h3","h4","h5","h6","h7","h8","h9",
            "p",
            "pre",
            "ul",
            "ol",
            "li",
            "tr",
            "figure",
            "hr",
            // AMP
            "amp-iframe",
            "amp-sidebar",
            "amp-form",
            "amp-youtube",
            "amp-script",
            // RSS
            "channel"

        ));

        if (!get("minify") && $is_block_tag)
        {
            $prefix = eol();
        }

        return (false && has('rss') && !$force_display) ? '' : (

                $prefix.                
                (
                    '<'.$tag.attributes_as_string($attributes).
                    (($extra_attributes_raw === false) ? '' : (' '.$extra_attributes_raw))
                ) . 
                (
                    ($self_closing) ? '/>' : 
                    ('>'.$html.'</'.(($space_pos === false) ? $tag : substr($tag, 0, $space_pos)).'>')
                )

            );
    }
    
    function body($html = "", $html_post_scripts = "", $dark_theme = auto)
    {
        $profiler = debug_track_timing();

        $attributes = false;

        if (is_array($html_post_scripts))                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       
        {
            $attributes         = $html_post_scripts;
            $html_post_scripts  = at($attributes, "script", "");
        }
        
        $properties_organization = array
        (
            "@context"  => "https://schema.org", 
            "@type"     => "Organization",

            "url"       => get("canonical"),
            "logo"      => get("canonical").'/'.get("image")
        );
        
        $properties_person_same_as = array();
        
        if (has("facebook_page"))   $properties_person_same_as[] = url_facebook_page   (get("facebook_page"));
        if (has("instagram_user"))  $properties_person_same_as[] = url_instagram_user  (get("instagram_user"));
        if (has("tumblr_blog"))     $properties_person_same_as[] = url_tumblr_blog     (get("tumblr_blog"));
        if (has("pinterest_user"))  $properties_person_same_as[] = url_pinterest_board (get("pinterest_user"), get("pinterest_board"));
            
        $properties_person = array
        (
            "@context"  => "https://schema.org", 
            "@type"     => "Person",
            "name"      => get("publisher"),
            "url"       => get("canonical"),
            "sameAs"    => $properties_person_same_as
        );
        
        $app_js = path_coalesce("js/app.js","app.js");
        
        $body = ''

        . if_browser('lte IE 9', eol().p('You are using an '.strong('outdated').' browser. Please '.a('upgrade your browser', "https://browsehappy.com/").' to improve your experience and security.', 'browserupgrade'))
        
        . (get("support_metadata_person",       false) ? script_json_ld($properties_person)         : "")
        . (get("support_metadata_organization", false) ? script_json_ld($properties_organization)   : "")
        
        . eol()
        . $html

        . (AMP() ? (eol().comment("DOM AMP sidebars").eol(2))   : "")
        . (AMP() ? delayed_component("_amp_sidebars")           : "")
        . (AMP() ? delayed_component("_amp_scripts_body")       : "")

        . eol().comment("DOM Body boilerplate markup")
      /*. back_to_top_link()*/

        . eol() . comment("DOM Body scripts")
        . scripts_body()            . (is_callable("dom\\scripts_body_toolbar") ? (""
        . scripts_body_toolbar()    ) : "")

        . eol() . ($app_js ? comment('CUSTOM script') : comment('Could not find any app.js default user script'))
                                                                    .((!get("htaccess_rewrite_php")) ? (""
        . ($app_js ? script_file($app_js) : '')         ) : (""
        . ($app_js ? script_src($app_js)  : '')         ))

        . eol() 
        . comment("Post scripts")
        . $html_post_scripts

        . eol()
        . comment("Hidden markup")

        /**
         * Indieweb
         * 
         * Some sites we know support it in the profiles :
         * Eventful
         * Facebook NO MORE
         * Flickr
         * Identica
         * LastFM // but no linkback
         * Soup
         * typepad
         * Twitter NO MORE
         * Wordpress
         */

      //. ((function () { $url = url_pinterest_board   (); if (!$url || !has("pinterest_user") ) return ""; return eol().a("Pinterest", $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
      //. ((function () { $url = url_instagram_user    (); if (!$url || !has("instagram_user") ) return ""; return eol().a("Instagram", $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
      //. ((function () { $url = url_flickr_user       (); if (!$url || !has("flickr_user")    ) return ""; return eol().a("Flickr",    $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
      //. ((function () { $url = url_500px_user        (); if (!$url || !has("500px_user")     ) return ""; return eol().a("500px",     $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
        . ((function () { $url = url_pixelfed_user     (); if (!$url || !has("pixelfed_user")  ) return ""; return eol().a("Pixelfed",  $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
        . ((function () { $url = url_mastodon_user     (); if (!$url || !has("mastodon_user")  ) return ""; return eol().a("Mastodon",  $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
      //. ((function () { $url = url_facebook_user     (); if (!$url || !has("facebook_user")  ) return ""; return eol().a("Facebook",  $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
      //. ((function () { $url = url_twitter_user      (); if (!$url || !has("twitter_user")   ) return ""; return eol().a("Twitter",   $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
      //. ((function () { $url = url_linkedin_page     (); if (!$url || !has("linkedin_page")  ) return ""; return eol().a("Linkedin",  $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
        . ((function () { $url = url_github_user       (); if (!$url || !has("github_user")    ) return ""; return eol().a("Github",    $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
      //. ((function () { $url = url_lastfm_user       (); if (!$url || !has("lastfm_user")    ) return ""; return eol().a("LastFM",    $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
      //. ((function () { $url = url_tumblr_blog       (); if (!$url || !has("tumblr_blog")    ) return ""; return eol().a("Tumblr",    $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())
      //. ((function () { $url = url_messenger         (); if (!$url || !has("messenger_id")   ) return ""; return eol().a("Messenger", $url, array("hidden" => "hidden", "rel" => "me"), false, false, false); })())

        . eol()

        . ((AMP() && get("support_service_worker", false)) ? (eol().comment("DOM Body AMP service worker")) : "")
        . ((AMP() && get("support_service_worker", false)) ? (eol().'<amp-install-serviceworker src="'.path('sw.js').'" layout="nodisplay" data-iframe-src="'.path("install-service-worker.html").'"></amp-install-serviceworker>') : "")
        ;

        if (auto === $dark_theme) $dark_theme = get("dark_theme", false);

        $attributes = array_merge(array(
            "id"    => top_id(),
            "class" => (component_class('body', 'body').($dark_theme ? component_class('body','dark') : ''))
            ), AMP() ? array() : array(
            "name"  => "!"
            ));

        return eol().tag(
            'body',
            $body,
            $attributes
            );
    }
    
    function cosmetic($html)
    {
        return !!get("minify") ? '' : $html;
    }
    
//  HTML tags
        
    function h($h, $html = "", $attributes = false, $anchor = false, $headline_hook = true)
    {
        $h += is_integer(get("main",         0)) ? get("main",         0) : 0;
        $h += is_integer(get("main-include", 0)) ? get("main-include", 0) : 0;
        
        if ($headline_hook)
        {
            list($h, $html, $anchor) = hook_headline($h, $html, $anchor);
        }
        
        $attributes = attributes_add($attributes, attr("class", component_class("h$h")                   ));
        $attributes = attributes_add($attributes, attr("id",    anchor_name(!!$anchor ? $anchor : $html) ));

        return tag('h'.$h, $html, $attributes);
    }

    function noscript       ($html = "", $attributes = false) {                             return  tag('noscript',                   $html,                                                $attributes                                                         );                      }
    function aside          ($html = "", $attributes = false) {                             return  tag('aside',                      $html,                                                $attributes                                                         );                      }
    function nav            ($html = "", $attributes = false) {                             return  tag('nav',                        $html,                                                $attributes                                                         );                      }
    function div            ($html = "", $attributes = false) {                             return  tag('div',                        $html,                                                $attributes                                                         );                      }
    function p              ($html = "", $attributes = false) {                             return  tag('p',                          $html,                                                $attributes                                                         );                      }
    function i              ($html = "", $attributes = false) {                             return  tag('i',                          $html,                                                $attributes                                                         );                      }
    function pre            ($html = "", $attributes = false) {                             return  tag('pre',                        $html,                                                $attributes                                                         );                      }
    function code           ($html = "", $attributes = false) {                             return  tag('code',                       $html,                                                $attributes                                                         );                      }
    function ul             ($html = "", $attributes = false) {                             return  tag('ul',                         $html,                                                $attributes                                                         );                      }
    function ol             ($html = "", $attributes = false) {                             return  tag('ol',                         $html,                                                $attributes                                                         );                      }
    function li             ($html = "", $attributes = false) {                             return  tag('li',                         $html,                                                $attributes                                                         );                      }
    
    function dlist          ($html = "", $attributes = false) {                             return  tag('dl',                         $html,                                                $attributes                                                         );                      }
    function dterm          ($html = "", $attributes = false) {                             return  tag('dt',                         $html,                                                $attributes                                                         );                      }
    function ddef           ($html = "", $attributes = false) {                             return  tag('dd',                         $html,                                                $attributes                                                         );                      }
    
    function table          ($html = "", $attributes = false) {                             return  tag('table',                      $html,                                attributes_add( $attributes, attributes(attr("class", component_class('table'))))   );                      }
    function thead          ($html = "", $attributes = false) {                             return  tag('thead',                      $html,                                                $attributes                                                         );                      }
    function tbody          ($html = "", $attributes = false) {                             return  tag('tbody',                      $html,                                                $attributes                                                         );                      }
    function tr             ($html = "", $attributes = false) {                             return  tag('tr',                         $html,                                                $attributes                                                         );                      }
    function td             ($html = "", $attributes = false) {                             return  tag('td',                         $html,                                                $attributes                                                         );                      }
    function th             ($html = "", $attributes = false) {                             return  tag('th',                         $html,                                                $attributes                                                         );                      }

    function strong         ($html = "", $attributes = false) {                             return  tag('strong',                     $html,                                                $attributes                                                         );                      }
    function strike         ($html = "", $attributes = false) {                             return  tag('s',                          $html,                                                $attributes                                                         );                      }
  //function del            ($html = "", $attributes = false) {                             return  tag('del',                        $html,                                                $attributes                                                         );                      }
    function em             ($html = "", $attributes = false) {                             return  tag('em',                         $html,                                                $attributes                                                         );                      }
    function span           ($html = "", $attributes = false) {                             return  tag('span',                       $html,                                                $attributes                                                         );                      }
    function figure         ($html = "", $attributes = false) {                             return  tag('figure',                     $html,                                                $attributes                                                         );                      }
    function figcaption     ($html = "", $attributes = false) {                             return  tag('figcaption',                 $html,                                                $attributes                                                         );                      }

    function hgroup         ($html = "", $attributes = false) {                             return  tag('hgroup',                     $html,                                                $attributes                                                         );                      }

    function blockquote     ($html = "", $attributes = false) {                             return  tag('blockquote',                 $html,                                                $attributes                                                         );                      }

    function details        ($html = "", $attributes = false) {                             return  tag('details',                    $html,                                                $attributes                                                         );                      }
    function summary        ($html = "", $attributes = false) {                             return  tag('summary',                    $html,                                                $attributes                                                         );                      }

    function form           ($html = "", $attributes = false) { hook_amp_require("form");   return  tag('form',                       $html,                                                $attributes                                                         );                      }

    function checkbox       ($id, $html = "", $attributes = false) {                        return  tag('input',                      $html, attributes_add( $attributes, attributes(attr("class", component_class('checkbox')),                attr("id" , $id), attr("type", "checkbox") ) ));  }
    function checkbox_label ($id, $html = "", $attributes = false) {                        return  tag('label',                      $html, attributes_add( $attributes, attributes(attr("class", component_class('label','checkbox-label')),  attr("for", $id)                           ) ));  }

    function radio          ($group, $id, $html = "", $attributes = false) {                        return  tag('input',                      $html, attributes_add( $attributes, attributes(attr("class", component_class('radio')),               attr("name" , $group), attr("id" , $id), attr("type", "radio") ) ));  }
    function radio_label    ($group, $id, $html = "", $attributes = false) {                        return  tag('label',                      $html, attributes_add( $attributes, attributes(attr("class", component_class('label','radio-label')), attr("for", $id)                           ) ));  }

    function button         ($html = "", $attributes = false) {                             return  tag('button',                     $html,                     attributes_add_class(  $attributes, component_class('button'))                             );                      }
    function button_label   ($html = "", $attributes = false) {                             return  tag('span',                       $html,                     attributes_add_class(  $attributes, component_class('label','button-label'))                       );                      }
    
    function input          ($html = "", $type = "", $id = "", $attributes = false) {       return  tag('input',                      "",                                   attributes_add( $attributes, attributes(attr("type",    $type),
                                                                                                                                                                                                                    attr("value",   $html),
                                                                                                                                                                                                                    attr("id",      $id))), false, true                 );                      }

    function label          ($html = "", $for  = "", $attributes = false) {                 return  tag('label',                      $html,                            attributes_add( $attributes, attributes(attr("for", $for))) );                      }

    function select         ($html = "", $default = false, $id = "", $attributes = false) { return  tag('select',                     $html,                            attributes_add( $attributes, $default !== false ? attributes(attr("id", $id), attr("value", $default))
                                                                                                                                                                                                                        : attributes(attr("id", $id))  )          );                      }

    function option         ($html = "", $value = "", $attributes = false) {                return  tag('option',                     $html, attributes_add( $attributes, attributes(attr("value", $value)))                             );                      }

    function h1             ($html = "", $attributes = false, $anchor = false) {            return  h(1,                              $html,                                            $attributes, $anchor                                                );                      }
    function h2             ($html = "", $attributes = false, $anchor = false) {            return  h(2,                              $html,                                            $attributes, $anchor                                                );                      }
    function h3             ($html = "", $attributes = false, $anchor = false) {            return  h(3,                              $html,                                            $attributes, $anchor                                                );                      }
    function h4             ($html = "", $attributes = false, $anchor = false) {            return  h(4,                              $html,                                            $attributes, $anchor                                                );                      }
    function h5             ($html = "", $attributes = false, $anchor = false) {            return  h(5,                              $html,                                            $attributes, $anchor                                                );                      }
    function h6             ($html = "", $attributes = false, $anchor = false) {            return  h(6,                              $html,                                            $attributes, $anchor                                                );                      }
    function section        ($html = "", $attributes = false) {                             return  tag('section',                    $html,                                            $attributes,                                                        );                      }
    function header         ($html = "", $attributes = false) {                             return  tag('header',                     $html,                                            $attributes,                                                        );                      }
    function _header        ($html = "", $attributes = false) {                             return  tag('header',                     $html,                                            $attributes,                                                        );                      }
                   
    function hr             (            $attributes = false) {                             return  tag('hr',                         false,                                                $attributes, false, true                                            );                      }
    function br             (            $attributes = false) {                             return  tag('br',                         false,                                                $attributes, false, true                                            );                      }

    function clearfix       () { return div("","clearfix"); }

    function excerpt        ($html = "", $attributes = false) {                             return div($html, attributes_add($attributes, attributes(attr("class", "excerpt")))); }

    $__dom_is_first_main = true;

    function main($html = "", $attributes = false)
    {
        if (has("main")) die($html);
        if (has("main-include")) set("main-include", $html);

        $profiler = debug_track_timing();
        
        $attributes = attributes_add_class($attributes, component_class("main").' '.component_class("main","content"));

        global $__dom_is_first_main;
        
        if ($__dom_is_first_main)
        {
            $__dom_is_first_main = false;
            $attributes = attributes_add($attributes, attr("id", "main"));
        }

        return tag("main", cosmetic(eol(1)).$html.cosmetic(eol(1)), $attributes); 
    }       

    function footer     ($html = "", $attributes = false) { $profiler = debug_track_timing(); return tag('footer', $html, attributes_add_class(   $attributes,    component_class('footer')) ); }
    
    function icon           ($icon, $attributes = false) { return      i($icon,      attributes_add_class($attributes, 'material-icons')); }
    function button_icon    ($icon, $label      = false) { return button(icon($icon, component_class('i','action-button-icon')), array("class" => component_class("button","action-button"), "aria-label" => (($label === false) ? $icon : $label))); }

    function supported_ratios()
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
            $class = "aspect-ratio-16-9"; foreach (supported_ratios() as $ratio) 
            
                if (((int)$w/(int)$h)==($ratio[0]/$ratio[1]))  $class = "aspect-ratio-".$ratio[0]."-".$ratio[1]."";

            $class = 'aspect-ratio '.$class;
        }

        return $class;
    }
        
    function iframe($url, $title = false, $classes = false, $w = false, $h = false, $lazy = auto, $extra_styles = false, $extra_attributes = false)
    {   
        // TODO See https://benmarshall.me/responsive-iframes/ for frameworks integration   
        // TODO if EXTERNAL LINK add crossorigin="anonymous" (unless AMP)
        // TODO replace $classes by $attributes

        if (!get("script-images-loading") && $lazy === true) $lazy = auto;

        $w = ($w === false) ? "300" : $w;
        $h = ($h === false) ? "200" : $h;

        hook_amp_require("iframe");
        
        if (AMP()) $lazy = false;

        $src_attributes = ' src="'.$url.'"';
        if ($lazy === true) $src_attributes = ' data-src="'.$url.'" src="'.url_img_loading().'"';
        
        $lazy_attributes = "";

        if ($lazy === auto) $lazy_attributes = ' loading="lazy" decoding="async"';
        if ($lazy === true) $classes = (!!$classes) ? ($classes . ' lazy loading iframe') : 'lazy loading iframe';
        
        global $hook_need_lazy_loding;
        if ($lazy === true) $hook_need_lazy_loding[] = $url;

        if (!!get("gemini")) return "";
     
        return '<'.(AMP() ? 'amp-iframe sandbox="allow-scripts"' : 'iframe').

             (!!$title   ? (' title'            .'="'.$title        .'"') : '').
             (!!$classes ? (' class'            .'="'.$classes      .'"') : '') .
             
                            $lazy_attributes.
                            $src_attributes.

                            ' width'            .'="'.$w            .'"'.
                            ' height'           .'="'.$h            .'"'.
                            ' layout'           .'="'.'responsive'  .'"'.
                            ' frameborder'      .'="'.'0'           .'"'.
                            ' overflow'         .'="'.'hidden'      .'"'.
                            ' allowfullscreen'  .'="'.''            .'"'.
                            ' style'            .'="'."border: 0; max-width: 100%; --width: $w; --height: $h;".(!$extra_styles ? "" : " $extra_styles") .'"'.

                            (!$extra_attributes ? "" : " $extra_attributes").

                            '>'.

            (AMP() ? ('<amp-img layout="fill" src="'.url_img_blank().'" placeholder></amp-img>') : "").
            
            '</'.(AMP() ? 'amp-iframe' : 'iframe').'>';
    }

    function google_calendar($id, $w = false, $h = false, $background_color = "FFFFFF", $mode = "MONTH" /*WEEK AGENDA*/)
    {
        $src = $id;

        if (false === stripos($id, "http"))
        {
            $src = 'https://calendar.google.com/calendar/embed'
            
            .'?'    .'showTitle'        .'=0'
            .'&amp;'.'showPrint'        .'=0'
            .'&amp;'.'showCalendars'    .'=0'
            .'&amp;'.'showTz'           .'=0'       .(($h !== false) ? (''
            .'&amp;'.'height'           .'='.$h.''  ) : '').''
            .'&amp;'.'wkst'             .'=2'
            .'&amp;'.'bgcolor'          .'=%23'.$background_color
            .'&amp;'.'src'              .'='.$id.'%40group.calendar.google.com'
            .'&amp;'.'color'            .'=%2307bdcb'
            .'&amp;'.'mode'             .'='.$mode
            .'&amp;'.'ctz'              .'=Europe%2FParis';
        }
        
        if (AMP()) return a('https://calendar.google.com', $src, external_link);
        
        return iframe($src, "Google Calendar", "google-calendar", $w, $h).a('https://calendar.google.com', $src, external_link);
    }
       
    $__dom_lazy_load_index = 0;

    function script_lazy_load($url, $query_selector, $src_attribute = "src")
    {
        if (AMP()) $query_selector .= " iframe";

        global $__dom_lazy_load_index;
        ++$__dom_lazy_load_index;

        return script('

          /*dom.log("Lazy loading");*/
                
            dom.on_loaded(function() { 

              /*dom.log("Lazy loading", "Pending", "'.$query_selector.'", "'.$src_attribute.'", "'.$url.'");*/
                
                var lazy_loaded_'.$__dom_lazy_load_index.' = false;

                function update_attribute_'.$__dom_lazy_load_index.'() {

                    if (!lazy_loaded_'.$__dom_lazy_load_index.') {

                        lazy_loaded_'.$__dom_lazy_load_index.' = true;
                        document.querySelector("'.$query_selector.'").'.$src_attribute.' = "'.$url.'";

                        dom.log("Lazy loading", "Apply", "'.$query_selector.'", "'.$src_attribute.'", "'.$url.'");
                    }    
                }
    
                if (window.location.hash != "") {

                    update_attribute_'.$__dom_lazy_load_index.'();

                } else {                

                    dom.on_scroll(update_attribute_'.$__dom_lazy_load_index.'); 
                }            
            });        
        ');
    }

    function codepen($url, $title, $w = false, $h = false, $lazy = auto)
    {
        return (!!get("no_js") || AMP()) 
        
            ? iframe($url, $title, "codepen", $w, $h, $lazy)

            : ( iframe(path("empty.html"), $title, "codepen", $w, $h, $lazy).
                script_lazy_load($url, ".codepen")
            );
    }

    function user_codepen($id, $title, $w = false, $h = false, $lazy = auto)
    {
        return codepen(url_codepen_user()."/embed/preview/$id?default-tab=result", $title, $w, $h, $lazy);
    }
        
    function google_map($embed_url, $w = false, $h = false, $lazy = auto)
    {
        return (!!get("no_js") || AMP()) 
        
            ? iframe($embed_url, "Google Map", "google-map", $w, $h, $lazy) 
            
            : ( iframe(path("empty.html"), "Google Map", "google-map", $w, $h, $lazy).
                script_lazy_load($embed_url, ".google-map")
            );
    }
        
    function google_map_lat_lon($lat, $lon, $w = false, $h = false, $lazy = auto)
    {
        return google_map("https://maps.google.com/maps?q=$lat,$lon&hl=fr;&output=embed");
    }
 
    function google_doc($id, $w = false, $h = false, $lazy = auto)
    {
        $src = $id;

        if (false === stripos($id, "http"))
        {
            $src = "https://docs.google.com/document/$id/pub?embedded=true";
        }

        return iframe($src, "Google Doc", "google-doc", $w, $h, $lazy);
    }
        
    function google_calc($id, $w = false, $h = false, $lazy = auto)
    {
        $src = $id;

        if (false === stripos($id, "http"))
        {
            $src = "https://docs.google.com/spreadsheets/$id/pubhtml".
                "?gid".      "=0".
                "&single".   "=true".
                "&widget".   "=false".
                "&chrome".   "=false".
                "&headers".  "=false";
        }

        return iframe($src, "Google Spreadsheet", "google-calc", $w, $h, $lazy);
    }
       
    function google_video($id, $w = false, $h = false, $lazy = auto)
    {
        $w = ($w === false) ? "1200" : $w;
        $h = ($h === false) ?  "675" : $h;

        if (AMP())
        {
            hook_amp_require("youtube");
            return '<amp-youtube data-videoid="'.$id.'" layout="responsive" width="'.$w.'" height="'.$h.'"></amp-youtube>';        
        }
        else
        {        
            $url = "https://www.youtube.com/embed/$id?wmode=opaque&amp;enablejsapi=1";

            return iframe($url, "Google Video", "google-video", $w, $h, $lazy);
        }
    }

    function offset_html_headlines($html, $offset)
    {
        $levels = [1,2,3,4,5,6,7,8,9];
        if ($offset > 0) $levels = array_reverse($levels);

        foreach ($levels as $h)
        {
            $html = str_replace_all("<h$h",  "<h" .($h+$offset), $html);
            $html = str_replace_all("</h$h", "</h".($h+$offset), $html);
        }

        return $html;
    }
  
    function wikipedia($page)
    {
        return         
            div(            
                style((function () { HSTART(); ?><style><?php HERE() ?>

                    .wikipedia-api-parse :is(.noarchive, .metadata, .mw-editsection, .navbox-container, .bandeau-portail) { display: none }
                    .wikipedia-api-parse figure { width: 30%; margin: var(--gap) var(--gap) var(--gap) 0; }
                    .wikipedia-api-parse figcaption { padding: calc(0.5 * var(--gap)) var(--gap); }
                    .wikipedia-api-parse .gallery { display: flex; gap: var(--gap); padding-inline-start: 0; }
                    .wikipedia-api-parse .gallery li { list-style: none }
                    .wikipedia-api-parse :is(td,th) { border: none; white-space: normal }
                    .wikipedia-api-parse th { white-space: pre }
                    .wikipedia-api-parse table, 
                    .wikipedia-api-parse table * { background: none !important; border: none !important; color: currentColor !important; box-shadow: none !important }
                    .wikipedia-api-parse table .NavEnd { display: none }
                    .wikipedia-api-parse table img { width: auto }
                    .wikipedia-api-parse .flagicon { max-width: 64px; display: inline-block; }
                    .wikipedia-api-parse .mw-halign-right { float: right; }

                <?= HERE("raw_css") ?></style><?php return HSTOP(); })()).

                str_replace_all('href="/', 'href="https://fr.wikipedia.org/', 
                str_replace_all('href="',  'target="_blank" href="', 
                    offset_html_headlines(
                        at(at(
                            array_open_url("https://fr.wikipedia.org/w/api.php?action=parse&formatversion=2&page=$page&prop=text&format=json&redirects=1"), 
                            "parse"), "text"),
                        1
                        )
                    )), 
                "wikipedia-api-parse"
                );
    }
        
    function json_google_photo_album_from_content($url)
    {
        $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36'));
        $context = stream_context_create($options);
        $html    = @file_get_contents($url, false, $context);

        update_dependency_graph($url);
        
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
    
    function google_photo_album($url, $wrapper = "div", $img_wrapper = "self", $link_to_google = true, $randomize = false, $img_precompute_size = true)
    {
        $photo_urls = array();
        {
            if (is_array($url))
            {
                $photo_urls = $url;
            }
            else
            {
                $results = json_google_photo_album_from_content($url);
                $photos  = at($results, 1, array());

                foreach ($photos as $i => $photo_result)
                {
                    $photo_urls[] = at(at($photo_result, 1), 0);
                }    
            }
        }

        if ($randomize)
        {
            shuffle($photo_urls);
        }

        $images = "";
        
        foreach ($photo_urls as $i => $photo_url)
        {
            if (!is_callable($img_wrapper)) $img_wrapper = "dom\\$img_wrapper";
            
            $size = cached_getimagesize($photo_url);
            list($w, $h) = (is_array($size) ? array_values($size) : array(false, false));

            $images .= call_user_func($img_wrapper, img($photo_url, $w, $h, false, "Photo", auto, false, '', $img_precompute_size), $photo_url, $i + 1, $w, $h);
        }

        if (!is_callable($wrapper)) $wrapper = "dom\\$wrapper";
            
        $album = call_user_func($wrapper, $images);

        if ($link_to_google)
        {
            $album = a($album, $url, external_link);
        }

        return $album;
    }

    function embed_instagram_card($id, $account_codename = false, $account_label = false)
    {
        HSTART(); ?><html><?php HERE() ?>
        
            <blockquote 
                class="instagram-media card"
                data-instgrm-captioned
                data-instgrm-permalink="https://www.instagram.com/tv/<?= $id ?>/?utm_source=ig_embed&amp;utm_campaign=loading"
                data-instgrm-version="13" 
                
                style="
                    background:#FFF;
                    border:0;
                    border-radius:3px;
                    box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15);
                    margin: 1px;
                    max-width:540px;
                    min-width:326px;
                    padding:0;
                    width:99.375%;
                    width:-webkit-calc(100% - 2px);
                    width:calc(100% - 2px);
                    aspect-ratio: 1 / 2;
                    ">
                    
                <div style="padding:16px;">
                
                    <a  href="https://www.instagram.com/tv/<?= $id ?>/?utm_source=ig_embed&amp;utm_campaign=loading"
                        style="
                            background:#FFFFFF;
                            line-height:0;
                            padding:0 0;
                            text-align:center;
                            text-decoration:none;
                            width:100%;"
                            
                        target="_blank">
                            
                        <div style="
                            display: flex;
                            flex-direction: row;
                            align-items: center;">
                            
                            <div style="
                                background-color: #F4F4F4;
                                border-radius: 50%;
                                flex-grow: 0;
                                height: 40px;
                                margin-right: 14px;
                                width: 40px;"></div>
                                
                            <div style="
                                display: flex;
                                flex-direction: column;
                                flex-grow: 1;
                                justify-content: center;">
                                
                                <div style="
                                    background-color: #F4F4F4;
                                    border-radius: 4px; flex-grow: 0;
                                    height: 14px;
                                    margin-bottom: 6px;
                                    width: 100px;"></div>
                                    
                                <div style="
                                    background-color: #F4F4F4;
                                    border-radius: 4px;
                                    flex-grow: 0;
                                    height: 14px;
                                    width: 60px;"></div>
                                        
                            </div>
                            
                        </div>
                    
                        <div style="padding: 19% 0;"></div>
                        
                        <div style="
                            display:block;
                            height:50px;
                            margin:0 auto 12px;
                            width:50px;">
                                
                            <svg 
                                width="50"
                                height="50"
                                viewBox="0 0 60 60"
                                version="1.1"
                                xmlns="https://www.w3.org/2000/svg"
                                xmlns:xlink="https://www.w3.org/1999/xlink">
                                
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-511.000000, -20.000000)" fill="#000000"><g><path d="M556.869,30.41 C554.814,30.41 553.148,32.076 553.148,34.131 C553.148,36.186 554.814,37.852 556.869,37.852 C558.924,37.852 560.59,36.186 560.59,34.131 C560.59,32.076 558.924,30.41 556.869,30.41 M541,60.657 C535.114,60.657 530.342,55.887 530.342,50 C530.342,44.114 535.114,39.342 541,39.342 C546.887,39.342 551.658,44.114 551.658,50 C551.658,55.887 546.887,60.657 541,60.657 M541,33.886 C532.1,33.886 524.886,41.1 524.886,50 C524.886,58.899 532.1,66.113 541,66.113 C549.9,66.113 557.115,58.899 557.115,50 C557.115,41.1 549.9,33.886 541,33.886 M565.378,62.101 C565.244,65.022 564.756,66.606 564.346,67.663 C563.803,69.06 563.154,70.057 562.106,71.106 C561.058,72.155 560.06,72.803 558.662,73.347 C557.607,73.757 556.021,74.244 553.102,74.378 C549.944,74.521 548.997,74.552 541,74.552 C533.003,74.552 532.056,74.521 528.898,74.378 C525.979,74.244 524.393,73.757 523.338,73.347 C521.94,72.803 520.942,72.155 519.894,71.106 C518.846,70.057 518.197,69.06 517.654,67.663 C517.244,66.606 516.755,65.022 516.623,62.101 C516.479,58.943 516.448,57.996 516.448,50 C516.448,42.003 516.479,41.056 516.623,37.899 C516.755,34.978 517.244,33.391 517.654,32.338 C518.197,30.938 518.846,29.942 519.894,28.894 C520.942,27.846 521.94,27.196 523.338,26.654 C524.393,26.244 525.979,25.756 528.898,25.623 C532.057,25.479 533.004,25.448 541,25.448 C548.997,25.448 549.943,25.479 553.102,25.623 C556.021,25.756 557.607,26.244 558.662,26.654 C560.06,27.196 561.058,27.846 562.106,28.894 C563.154,29.942 563.803,30.938 564.346,32.338 C564.756,33.391 565.244,34.978 565.378,37.899 C565.522,41.056 565.552,42.003 565.552,50 C565.552,57.996 565.522,58.943 565.378,62.101 M570.82,37.631 C570.674,34.438 570.167,32.258 569.425,30.349 C568.659,28.377 567.633,26.702 565.965,25.035 C564.297,23.368 562.623,22.342 560.652,21.575 C558.743,20.834 556.562,20.326 553.369,20.18 C550.169,20.033 549.148,20 541,20 C532.853,20 531.831,20.033 528.631,20.18 C525.438,20.326 523.257,20.834 521.349,21.575 C519.376,22.342 517.703,23.368 516.035,25.035 C514.368,26.702 513.342,28.377 512.574,30.349 C511.834,32.258 511.326,34.438 511.181,37.631 C511.035,40.831 511,41.851 511,50 C511,58.147 511.035,59.17 511.181,62.369 C511.326,65.562 511.834,67.743 512.574,69.651 C513.342,71.625 514.368,73.296 516.035,74.965 C517.703,76.634 519.376,77.658 521.349,78.425 C523.257,79.167 525.438,79.673 528.631,79.82 C531.831,79.965 532.853,80.001 541,80.001 C549.148,80.001 550.169,79.965 553.369,79.82 C556.562,79.673 558.743,79.167 560.652,78.425 C562.623,77.658 564.297,76.634 565.965,74.965 C567.633,73.296 568.659,71.625 569.425,69.651 C570.167,67.743 570.674,65.562 570.82,62.369 C570.966,59.17 571,58.147 571,50 C571,41.851 570.966,40.831 570.82,37.631"></path></g></g></g>
                                
                            </svg>
                                
                        </div>
                    
                        <div style="padding-top: 8px;">
                        
                            <div style="
                                color:#3897f0;
                                font-family:Arial,sans-serif;
                                font-size:14px;
                                font-style:normal;
                                font-weight:550;
                                line-height:18px;">
                                
                                Voir cette publication sur Instagram
                                
                            </div>
                            
                        </div>
                        
                        <div style="padding: 12.5% 0;"></div>
                    
                        <div style="
                            display: flex;
                            flex-direction: row;
                            margin-bottom: 14px;
                            align-items: center;">
                            
                            <div>
                            
                                <div style="
                                    background-color: #F4F4F4;
                                    border-radius: 50%;
                                    height: 12.5px;
                                    width: 12.5px;
                                    transform: translateX(0px) translateY(7px);"></div>
                                
                                <div style="
                                    background-color: #F4F4F4;
                                    height: 12.5px;
                                    transform: rotate(-45deg) translateX(3px) translateY(1px);
                                    width: 12.5px;
                                    flex-grow: 0;
                                    margin-right: 14px;
                                    margin-left: 2px;"></div>
                                    
                                <div style="
                                    background-color: #F4F4F4;
                                    border-radius: 50%;
                                    height: 12.5px;
                                    width: 12.5px;
                                    transform: translateX(9px) translateY(-18px);"></div>
                                
                            </div>
                            
                            <div style="margin-left: 8px;">
                                
                                <div style="
                                    background-color: #F4F4F4;
                                    border-radius: 50%;
                                    flex-grow: 0;
                                    height: 20px;
                                    width: 20px;"></div>
                                
                                <div style="
                                    width: 0;
                                    height: 0;
                                    border-top: 2px solid transparent;
                                    border-left: 6px solid #f4f4f4;
                                    border-bottom: 2px solid transparent;
                                    transform: translateX(16px) translateY(-4px) rotate(30deg)"></div>
                                
                            </div>
                            
                            <div style="margin-left: auto;">
                            
                                <div style="
                                    width: 0px;
                                    border-top: 8px solid #F4F4F4;
                                    border-right: 8px solid transparent;
                                    transform: translateY(16px);"></div>
                                
                                <div style="
                                    background-color: #F4F4F4;
                                    flex-grow: 0;
                                    height: 12px;
                                    width: 16px;
                                    transform: translateY(-4px);"></div>
                                    
                                <div style="
                                    width: 0;
                                    height: 0;
                                    border-top: 8px solid #F4F4F4;
                                    border-left: 8px solid transparent;
                                    transform: translateY(-4px) translateX(8px);"></div>
                                
                            </div>
                            
                        </div>
                            
                        <div style="
                            display: flex;
                            flex-direction: column;
                            flex-grow: 1;
                            justify-content: center;
                            margin-bottom: 24px;">
                            
                            <div style="
                                background-color: #F4F4F4;
                                border-radius: 4px;
                                flex-grow: 0;
                                height: 14px;
                                margin-bottom: 6px;
                                width: 224px;"></div>
                            
                            <div style="
                                background-color: #F4F4F4;
                                border-radius: 4px;
                                flex-grow: 0;
                                height: 14px;
                                width: 144px;"></div>
                            
                        </div>
                        
                    </a>
                        
                    <p style="
                        color:#c9c8cd;
                        font-family:Arial,sans-serif;
                        font-size:14px;
                        line-height:17px;
                        margin-bottom:0;
                        margin-top:8px;
                        overflow:hidden;
                        padding:8px 0 7px;
                        text-align:center;
                        text-overflow:ellipsis;
                        white-space:nowrap;">
                            
                        <a  href="https://www.instagram.com/tv/<?= $id ?>/?utm_source=ig_embed&amp;utm_campaign=loading"
                            target="_blank"
                            
                            style="
                                color:#c9c8cd;
                                font-family:Arial,sans-serif;
                                font-size:14px;
                                font-style:normal;
                                font-weight:normal;
                                line-height:17px;
                                text-decoration:none;">
    
                            Une publication partagée par <?= $account_label ?> (@<?= $account_codename ?>)
                                
                        </a>
                            
                    </p>
                        
                </div>
    
            </blockquote>
            
            <script async src="//www.instagram.com/embed.js"></script>
    
        <?php HERE("raw_html") ?></html><?php return HSTOP();
    }

    function embed_tiktok_card($id, $account = false, $tags = false, $song_title = false, $song_id = false)
    {
        HSTART(); ?><html><?php HERE() ?>
            
            <blockquote 
    
                class="tiktok-embed card"                                                           <?php if (!!$account) { ?>
                cite="https://www.tiktok.com/@<?= $account ?>/video/<?= $id ?>"                     <?php } ?>
                data-video-id="<?= $id ?>"
                
                style="
                    --clip: 0px;
                    padding: var(--gap);
                    background-color: #FFF;
                    width: 340px;
                    min-height: 340px;">
    
                <section>                                                                           <?php if (!!$account) { ?>
    
                    <a  target="_blank" 
                        title="@<?= $account ?>"    
                        href="https://www.tiktok.com/@<?= $account ?>">@<?= $account ?></a>         <?php } ?>
                                                                                                    <?php if (is_array($tags)) { ?>
                    <p> <?php foreach ($tags as $tag) { ?>
    
                        <a  title="<?= $tag ?>"
                            target="_blank"
                            href="https://www.tiktok.com/tag/<?= $tag ?>">##<?= $tag ?></a>
                        
                        <?php } ?>
                        
                    </p>                                                                            <?php } ?>
                                                                                                    <?php if (!!$song_title && !!$song_id) { ?>
                    <a  target="_blank"
                        title="<?= $song_title ?>"
                        href="https://www.tiktok.com/music/<?= $song_id ?>"><?= $song_title ?></a>  <?php } ?>
    
                </section>
                
            </blockquote>
            
            <script async src="https://www.tiktok.com/embed.js"></script>
            
        <?php HERE("raw_html") ?></html><?php return HSTOP();
    }

    // BlogPosting microdata attributes
    // TODO Microformats vs Microdata vs using both
    // set("auto-microdata") ? set("auto-microformat") + helpers ?
    // TODO indieweb + microformat + microdata : Mandatory for an article : e-content + p-name + dt-published + u-url

    function attr_card()            { return array("class" => "h-entry",                                                "itemscope" => "", "itemtype" => "https://schema.org/BlogPosting"   ); }
    function attr_article()         { return array("class" => "h-entry",                                                "itemscope" => "", "itemtype" => "https://schema.org/BlogPosting"   ); }
    function attr_author()          { return array("class" => "p-author", "rel" => "author",    "itemprop" => "author", "itemscope" => "", "itemtype" => "https://schema.org/Person"        ); }
    function attr_name()            { return array("class" => "p-name",                         "itemprop" => "name"); }
    function attr_datepublished($t) { return array("class" => "dt-published",                   "itemprop" => "datePublished", "datetime" => date("c", $t)); }
    function attr_articlebody()     { return array("class" => "e-content",                      "itemprop" => "articleBody"); }
    function attr_url()             { return array("class" => "u-url"); }
    function attr_syndication()     { return array("class" => "u-syndication"); }
    function attr_category()        { return array("class" => "p-category"); }
    
    // Components with BlogPosting microdata
    // NOTE. Currently, only cards with title, text, and properties sub-components are almost usable for indieweb content

    function article            ($html = "", $attributes = false)   { return tag('article', $html,        attributes_add(/*attributes_add(*/$attributes/*, attr_article())*/, array("class" => "article"))); }
    
    function a_author           ($html,     $attributes = false)    { return a(             $html, url(), attributes_add($attributes, attr_author()           )); }
    function a_category         ($html,     $attributes = false)    { return a(             $html, url(), attributes_add($attributes, attr_category()         )); }
    function span_name          ($html,     $attributes = false)    { return span(          $html,        attributes_add($attributes, attr_name()             )); }
    function time_datepublished ($date, $t, $attributes = false)    { return tag("time",    $date,        attributes_add($attributes, attr_datepublished($t)  )); }
    
    function div_articlebody    ($html,     $attributes = false)    { return div(           $html,        attributes_add($attributes, attr_articlebody()      )); }
    function section_articlebody($html,     $attributes = false)    { return section(       $html,        attributes_add($attributes, attr_articlebody()      )); }
    function main_articlebody   ($html,     $attributes = false)    { return main(          $html,        attributes_add($attributes, attr_articlebody()      )); }

    // LINKS

    $__includes = array();

    function href($link, $target = false)
    {
        $extended_link = $link;

        if ($target !== external_link
        &&  false === stripos($link, "javascript:"))
        {
            if (!!get("static"))
            {
                if (false === stripos($extended_link, "?")
                &&  false === stripos($extended_link, "&")
                &&  false === stripos($extended_link, "#")
                &&  false === stripos($extended_link, "."))
                {
                    foreach (get("forwarded_flags") as $forward_flag)
                    {
                        if (!!get($forward_flag) && (in_array($forward_flag, array("rss","json","tile","amp"))))
                        {
                            $extended_link = "$extended_link/$forward_flag";
                        }
                    }
                }
            }
            else
            {
                foreach (get("forwarded_flags", array()) as $forward_flag)
                {
                    if (get($forward_flag) !== false
                    &&  false === stripos($extended_link,"?$forward_flag") 
                    &&  false === stripos($extended_link,"&$forward_flag") 
                    &&  0     !== stripos($extended_link,"#"))
                    {
                        $extended_link .= ((false === stripos($extended_link,"?")) ? "?" : "") . ("&$forward_flag=".get($forward_flag));
                    }
                }
            }
        }

        return $extended_link;
    }
  
    function a($html, $url = false, $external_attributes = false, $target = false, $noopener = true, $noreferrer = true)
    {
        if ($url                 === false
        &&  $external_attributes === false
        &&  $target              === false) $url = $html;

        if (($external_attributes === internal_link 
          || $external_attributes === external_link) && $target === false)
        {
            $target = $external_attributes;
            $external_attributes = false;
        }

        if ($target === false)
        {
            $target = ((0 === stripos($url, "http"      ))
                    || (0 === stripos($url, "//"        ))
                    || (0 === stripos($url, "tel:"      ))
                    || (0 === stripos($url, "mailto:"   )) ) ? external_link : internal_link;
        }
        
        $extended_link = href($url, $target);

        $internal_attributes = array();

                                                        $internal_attributes["href"]                = ($url === false) ? url_top() : $extended_link; 
                                                        $internal_attributes["target"]              = $target;
                                                        $internal_attributes["rel"]                 = "";
        if ($target == external_link && !!$noopener)    $internal_attributes["rel"]                .= " noopener";
        if ($target == external_link && !!$noreferrer)  $internal_attributes["rel"]                .= " noreferrer";
        if ($target == external_link && !AMP())         $internal_attributes["crossorigin"]         = "anonymous";
        if (!!get("turbo") && !!get("turbo_links"))     $internal_attributes["data-turbo-action"]   = "replace";

        if ($internal_attributes["rel"] == "") unset($internal_attributes["rel"]);

        $attributes = "";
        
        if (is_array($external_attributes))
        {
            foreach ($external_attributes as $type => $attribute)
            {
                foreach ((is_array($attribute) ? $attribute : array($attribute)) as $a)
                {
                    if (array_key_exists($type, $internal_attributes))
                    {
                        $internal_attributes[$type] .= " ".$a;
                    }
                    else
                    {
                        $internal_attributes[$type] = $a;
                    }
                }
            }

            $attributes = attributes_as_string($internal_attributes);
        }
        else
        {
            $attributes =   attributes_as_string($internal_attributes).
                            attributes_as_string($external_attributes);
        }

        //if (false !== stripos($attributes, "hidden") && false !== stripos($attributes, "rel")) die($attributes);
        //if (false !== stripos($url, "selfie")) die($attributes);

        hook_link($html, $url, $target);
        
        return tag('a', $html, $attributes);
    }

    function a_encrypted($url, $text = false, $attributes = false, $target = external_link)
    {
        $text = ($text === false) ? $url : $text;
        
        if (AMP())
        {
            return a($text, $url, $attributes, $target);
        }
        else
        {
            $script  = "document.getElementById('".md5($text)."').setAttribute('href','".preg_replace("/\"/","\\\"",$url)."'); document.getElementById('".md5($text)."').innerHTML = '".$text."';";
            
            $crypted_script = ""; for ($i=0; $i < strlen($script); $i++) { $crypted_script = $crypted_script.'%'.bin2hex(substr($script, $i, 1)); }

            return a("", "", array(/*"aria-label" => "?",*/ "id" => md5($text)), $target).script("eval(unescape('".$crypted_script."'))");
        }
    }

    function a_email($email, $text = false, $attributes = false, $target = external_link)
    {
        $text = ($text === false) ? $email : $text;
        
        if (AMP())
        {
            return a($text, "mailto:" . $email, $attributes, $target);
        }
        else
        {
            $script  = "document.getElementById('".md5($text)."').setAttribute('href','mailto:".preg_replace("/\"/","\\\"",$email)."'); document.getElementById('".md5($text)."').innerHTML = '".$text."';";
            
            $crypted_script = ""; for ($i=0; $i < strlen($script); $i++) { $crypted_script = $crypted_script.'%'.bin2hex(substr($script, $i, 1)); }

            return a("", "", array("aria-label" => "E-mail", "id" => md5($text)), $target).script("eval(unescape('".$crypted_script."'))");
        }
    }
    
    function char_emoji($c) { return !!get("gemini") ? "$c" : span("$c&#xFE0F;", "emoji");   }
    function char_text($c)  { return !!get("gemini") ? "$c" : span("$c&#xFE0E;", "symbol");  }

    function char_phone()  { return char_text("☎"); }
    function char_email()  { return char_text("✉"); }
    function char_anchor() { return char_text("⚓"); }
    
    function char_glue()   { return !!get("gemini") ? ""  : "&#8288;"; }
    function char_unsec()  { return !!get("gemini") ? " " : " "/*"&nbsp;"*/; }
    function char_amp()    { return !!get("gemini") ? "&" : "&amp;";  }
   
    function nbsp($count_or_text = 1) { return is_string($count_or_text) ? str_replace(" ", nbsp(1), $count_or_text) : ($count_or_text == 1 ? char_unsec() : str_repeat(char_unsec(), $count_or_text)); }
    function glue() { return char_glue(); }
    
    function anchor_name($name, $tolower = auto) { return to_classname($name, $tolower); }

    function anchor($name, $character = false, $tolower = auto)
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
            return a((true === $character) ? char_anchor() : $character, "#".$id, $attributes);
        }
    }
    
    // GRID & FLEX

    function flex ($html, $attributes = false) { return div($html, attributes_add_class($attributes, component_class("div", "flex")     )); }

    function grid ($html, $attributes = false) { return div($html, attributes_add_class($attributes, component_class("div", "grid")     )); }
    function row  ($html, $attributes = false) { return div($html, attributes_add_class($attributes, component_class("div", "grid-row") )); }

    function section_grid ($html, $attributes = false) { return section($html, attributes_add_class($attributes, component_class("section", "grid")     )); }
    function article_grid ($html, $attributes = false) { return article($html, attributes_add_class($attributes, component_class("article", "grid")     )); }

    function cell($html, $s = 4, $m = 4, $l = 4, $classes = false)
    {
        if ($html == "") return '';

        if ($s === false) $s = 12;
        if ($m === false) $m = $s;
        if ($l === false) $l = $m;

        return div($html, component_class("div", 'grid-cell').' '.component_class("div", "grid-cell-$s-$m-$l").((false !== $classes) ? (' ' . $classes) : ''));
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
    
    function video($path, $attributes = false, $alt = false, $lazy = auto)
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
            tag("source", '', attributes_as_string(array("src" => $path, "type" => ("video/".str_replace(".","",$ext)))), false, true),
            attributes_as_string(
                array_merge(
                    AMP() ? array() : array("alt" => $alt), 
                    array("width" => "100%", "controls" => "no")
                    )
                )            
        );
    }
    
    // IMAGES
    
    function picture($html, $attributes = false, $alt = false, $lazy = auto, $lazy_src = false)
    {
        $attributes = to_attributes($attributes);

        if (false === stripos($html, "<img")
        &&  false === stripos($html, "<amp-img")) 
        {
            $html = img($html, at($attributes, "width", at($attributes, "w")), at($attributes, "height", at($attributes, "h")), false, $alt, $lazy, $lazy_src);
        }

        if (AMP())
        {
            $tag_bgn = html_comment_bgn();
            $tag_end = html_comment_end();

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
                return img($prefered_src, false, false, $attributes, $alt, false, false, $html);
            }
            else
            {
                return $html;
            }
        }
        else
        {
            return tag('picture', $html, $attributes);
        }
    }

    function source($path, $attributes = false)
    {
        if (AMP())
        {
            return html_comment($path);
        }
        else
        {
            $src    = explode('?', $path);
            $src    = $src[0];
            $info   = pathinfo($src);
            $ext    = array_key_exists('extension', $info) ? '.'.$info['extension'] : false;
            $type   = substr($ext,1); // ! TODO Find better solution

            $attributes = attributes_add($attributes, array("type" => "image/$type", "srcset" => $path));

            return tag("source", "", $attributes, false, true);
        }
    }

    /**
     * Aspect ratio attribute shortcut
     */
    function AR($w, $h)
    {
        return array("width" => $w, "height" => $h/*, "style" => "aspect-ratio: $w / $h"*/);
    }
    
    function img($path, $w = false, $h = false, $attributes = false, $alt = false, $lazy = auto, $lazy_src = auto, $content = auto, $precompute_size = auto, $src_attribute = auto, $preload_if_among_first_images = true)
    {
        if (!get("script-images-loading") && $lazy === true) $lazy = auto;
        if (!!get("nolazy")) $lazy = false;

        if (auto === $lazy_src)         $lazy_src       = false;
        if (auto === $content)          $content        = '';
        if (auto === $src_attribute)    $src_attribute  = 'src';

        if (is_array($path)) 
        {
            return wrap_each($path, "", "img", true, $w, $h, $attributes, $alt, $lazy, $lazy_src, $content, $precompute_size, $src_attribute, $preload_if_among_first_images);
        }

        if (is_array($w))
        {
            $attributes = $w;
            return img(at($attributes, "src", $path), at($attributes, "width"), at($attributes, "height"), $attributes, $alt, $lazy, $lazy_src, $content, $precompute_size, $src_attribute, $preload_if_among_first_images);
        }

        if (auto === $precompute_size)
        {
            $precompute_size = get("img_precompute_size");
        }

        $valid_path = path($path);
        $path       = !$valid_path ? $path : $valid_path;
        $info       = explode('?', $path);
        $info       = $info[0];
        $info       = pathinfo($info);
        $ext        = array_key_exists('extension', $info) ? '.'.$info['extension'] : false;
        $codename   = urlencode(basename($path, $ext));
        $alt        = ($alt === false || $alt === "") ? $codename : $alt;
            
        $lazy_src   = ($lazy !== false) ? (($lazy_src === false) ? url_img_loading() : $lazy_src) : false;

        if (is_array($attributes) && !array_key_exists("class", $attributes)) $attributes["class"] = "";

        $size0 = ($precompute_size ? cached_getimagesize($path) : array(0,0));
        list($w0, $h0) = (is_array($size0) ? $size0 : array(0, 0));
        if ($w0 == 0 || $h0 == 0) list($w0, $h0) = array(get("default_image_ratio_w", 300), get("default_image_ratio_h", 200));
                        
        $w = /*(is_array($attributes) && array_key_exists("width",  $attributes)) ? $attributes["width"] */!!$w ? $w : $w0;
        $h = /*(is_array($attributes) && array_key_exists("height", $attributes)) ? $attributes["height"]*/!!$h ? $h : $h0;

        if (!!get("no_js") && $lazy === true) $lazy = auto;

        $img_nth = get("img_nth", 1);

        $preload = false;
            
        if ($preload_if_among_first_images && $img_nth <= get("img_lazy_loading_after"))
        {
            $lazy    = false;
            $preload = true;
        }

        hook_img($path, $alt, $preload);

        // TODO if EXTERNAL LINK add crossorigin="anonymous"

        set("img_nth", $img_nth + 1);

        if (AMP())
        {
            return tag('amp-img'.                   ($content =='' ? (
                ' fallback'.                        '') : '').
                ' layout'.  '='.'"responsive"'.
                ' width'.   '='.$w.
                ' height'.  '='.$h.
                ' style'.   '='.'"--width: '.$w.'; --height: '.$h.'"',
                $content,
                attributes_as_string(array("src" => $path)).
                attributes_add_class($attributes, "img"),
                false,
                false
                );
        }
        else
        {
                 if (auto === $lazy)  $attributes = attributes_add($attributes, array("alt" => $alt, "width" => $w, "height" => $h, "style" => "--width: $w; --height: $h", "loading" => "lazy", "decoding" => "async", $src_attribute =>                          $path ));
            else if (true === $lazy)  $attributes = attributes_add($attributes, array("alt" => $alt, "width" => $w, "height" => $h, "style" => "--width: $w; --height: $h", "loading" => "auto", "decoding" => "async", $src_attribute => $lazy_src, "data-src" => $path ));
            else                      $attributes = attributes_add($attributes, array("alt" => $alt, "width" => $w, "height" => $h, "style" => "--width: $w; --height: $h",                      "decoding" => "async", $src_attribute =>                          $path ));

                 if (auto === $lazy)  $attributes = attributes_add_class($attributes, "img");
            else if (true === $lazy)  $attributes = attributes_add_class($attributes, "img lazy loading");
            else                      $attributes = attributes_add_class($attributes, "img");

            global $hook_need_lazy_loding;
            if ($lazy === true) $hook_need_lazy_loding[] = $path;
    
            return tag('img', $content, $attributes, false, $content == '');
        }
    }
    
    function img_domain_favicon($url, $attributes = false, $alt = false)
    {
        return img(url_img_domain_favicon($url), false, false, $attributes, $alt);
    }

    function img_svg($path, $attributes = false)
    {
        return img($path, false, false, $attributes ? $attributes : array("style" => "width: 100%; height: auto"));
    }

    function svg_wrapper($html, $label, $align) 
    {
        if (!!get("gemini")) return "";

        return tag('span', $html, array('class' => ('span-svg-wrapper icon '.strtolower($label).($align ? ' span-svg-wrapper-aligned' : ''))));
    }

    $__svg_index = -1;

    function svg($label, $x, $y, $w, $h, $align, $svg_body, $add_wrapper = true) 
    {
        if (!!get("gemini")) return "";

        global $__svg_index; ++$__svg_index;

        $class = strtolower($label);
        if (is_numeric($class[0])) $class = "_$class";

        $label = at($label, "label", $label);

        $has_title = false !== stripos($svg_body, "<title");

        if (!$has_title)
        {
            $id         = to_classname($label);
            $id_title   = "$id-title-$__svg_index";
            $id_desc    = "$id-desc-$__svg_index";
            $title      = at($label, "title", "$label");
            $desc       = at($label, "desc",  "$title svg image");
        }

        $html = '<svg'  .' class'           .'="'.  "svg ".$class           .'"'    // + colorful-shadow ?
                        .' role'            .'="'.  "img"                   .'"'            .(($label!="" && $label!=false)                ? (''
                        .' aria-label'      .'="'.  $label                  .'"'    ):'')   .(($label!="" && $label!=false && !$has_title) ? (''
                        .' aria-labelledby' .'="'.  "$id_title $id_desc"    .'"'    ):'')
                        .' viewBox'         .'="'.  "$x $y $w $h"           .'"'
                        .' width'           .'="'.  min(24, $w-$x)          .'"'
                        .' height'          .'="'.  min(24, $h-$y)          .'"'
                        .' style'           .'="'.  "fill: currentColor"    .'"'
                        .'>'                                                                .(($label!="" && $label!=false && !$has_title)?(''
                        .'<title id="'.$id_title.'">'.$title.'</title>'
                        .'<desc id="'.$id_desc.'">'.$desc.'</desc>'                 ):'')

                    .$svg_body.

                '</svg>';

        if ($add_wrapper)
            $html = svg_wrapper($html, $label, $align);

        return $html;
    }

    // https://materialdesignicons.com/

    $used_colors = array();

    function import_color($color)
    {
        if (is_array($color)) { foreach ($color as $c) import_color($c); return; }

        global $used_colors;
        if (!in_array($color, $used_colors)) $used_colors[] = $color;
    }

    function brands()
    {
        global $used_colors;
        return $used_colors;
    }
    
    function css_brands($tab = 0) { return delayed_component("_".__FUNCTION__, $tab, 3); }
    function _css_brands($tab = 0)
    {
        $css = "";

        foreach (brands() as $brand)
        {
            $fn = "dom\color_$brand"; // For php 5.6 compatibility
            if (!is_callable($fn)) $fn = "dom\\$fn";

            $colors   = $fn();
            $colors   = is_array($colors) ? $colors : array($colors);
            $class    = "brand-$brand";
            $var      = "--color-$brand";
            
            $svg_class = strtolower($brand);
            if (is_numeric($svg_class[0])) $svg_class = "_$svg_class";
    
            $css .= eol().tab($tab)."svg.$svg_class {"; 
                                                        $css .= eol()."--fill-color".        ": var($var); ";
                for ($i = 0; $i < count($colors); ++$i) $css .= eol()."--fill-color-".($i+1).": var($var".(($i > 0) ? ("-".($i+1)) : "")."); ";
            
            $css .= eol()."}"; 

            $css .= eol().tab($tab); if (count($colors) > 0) $css .= pan("svg path.$class", 47)." { fill:".       " var(--color, var(--fill-color)); } ";
            $css .= eol().tab($tab); if (count($colors) > 0) $css .= pan("svg stop.$class", 47)." { stop-color:". "              var(--fill-color);  } "; // Fallback currently not working on stop-color

            $css .= eol().tab($tab); for ($i = 0; $i < count($colors); ++$i) $css .= pan("svg path.$class"."-".($i+1), $i == 0 ? 47 : 0)." { fill:".       " var(--color, var(--fill-color"."-".($i+1).")); } ";
            $css .= eol().tab($tab); for ($i = 0; $i < count($colors); ++$i) $css .= pan("svg stop.$class"."-".($i+1), $i == 0 ? 47 : 0)." { stop-color:". "              var(--fill-color"."-".($i+1).");  } "; // Fallback currently not working on stop-color
        }

        return raw_css($css);
    }

    function brand_color_css_property($brand, $fn_color_transform = "self", $pan = 35, $prefix = "")
    {   
        $css = "";

        $color_contrast_target  = strtolower(get("contrast","AA"));
        $color_contrast_target  = (($color_contrast_target == "a"  ) ? DOM_COLOR_CONTRAST_AA_LARGE
                                : (($color_contrast_target == "aa" ) ? DOM_COLOR_CONTRAST_AA_NORMAL
                                : (($color_contrast_target == "aaa") ? DOM_COLOR_CONTRAST_AAA_NORMAL : $color_contrast_target)));

        $fn       = "dom\color_$brand"; // For php 5.6 compatibility

        if (!is_callable($fn)) $fn = "dom\\$fn";
        $colors = array();
        if (is_callable($fn)) $colors = $fn();

        $colors   = is_array($colors) ? $colors : array($colors);
        $class    = "brand-$brand";

        for ($i = 0; $i < count($colors); ++$i)
        {
            $color = $colors[$i];

            if (!is_callable($fn_color_transform)) $fn_color_transform = "dom\\$fn_color_transform";

            if (is_callable($fn_color_transform))
            {  
                $color = $fn_color_transform($color);
            }
            else
            {
                $color_var_name = str_replace("dom\\", "", $fn_color_transform);

                $background_color = get($color_var_name, $color_var_name);
                
                //if (!!get("static") && !get("fast")) // TODO Currently too slow for non static websites
                {           
                    $ratio = 1.0;
                    $debug = false;

                    if (false !== stripos($background_color, "#")
                    &&  false !== stripos($color, "#"))
                    {   
                        $color = correct_auto(
                            $color,
                            $background_color,
                            $color_contrast_target,
                            $ratio,
                            $debug
                            );
                    }
                }
            }

            $basename = ($prefix != "") ? "$prefix-color" : "color";

          //$css .= pan("--$basename-".$brand.(($i > 0) ? ("-".($i+1)) : "").":", $i == 0 ? $pan : 0)." var(--color, ".$color.");";
            $css .= pan("--$basename-".$brand.(($i > 0) ? ("-".($i+1)) : "").":", $i == 0 ? $pan : 0)." $color;";
        }
        
        return raw_css($css);
    }

    function brand_color_css_properties($fn_color_transform = "self", $pan = 35, $prefix = "") { return delayed_component("_".__FUNCTION__, array($fn_color_transform, $pan, $prefix), 3); }
    function _brand_color_css_properties($fn_color_transform = "self", $pan = 35, $prefix = "")
    {   
        $css = "";

        foreach (brands() as $b => $brand)
        {
            $css .= eol();
            $css .= brand_color_css_property($brand, $fn_color_transform, $pan, $prefix);
        }
        
        return raw_css($css);
    }

    // !TOOD DEPRECATE FUNCTION SIGNATURE AND REMOVE COLOR PARAM

    function svg_flickr         ($label = auto, $align = auto, $add_wrapper = auto) { import_color("flickr");        $class = "brand-flickr";          return svg($label === auto ? "Flickr"          : $label,   0,      0,     232.422, 232.422,  $align == auto ? false : !!$align, '<path class="'.$class.'" d="M43,73.211c-23.71,0-43,19.29-43,43s19.29,43,43,43c23.71,0,43-19.29,43-43S66.71,73.211,43,73.211z"/><path class="'.$class.'-2" d="M189.422,73.211c-23.71,0-43,19.29-43,43s19.29,43,43,43c23.71,0,43-19.29,43-43S213.132,73.211,189.422,73.211z"/>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_facebook       ($label = auto, $align = auto, $add_wrapper = auto) { import_color("facebook");      $class = "brand-facebook";        return svg($label === auto ? "Facebook"        : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M5,3H19A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3M18,5H15.5A3.5,3.5 0 0,0 12,8.5V11H10V14H12V21H15V14H18V11H15V9A1,1 0 0,1 16,8H18V5Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_discord        ($label = auto, $align = auto, $add_wrapper = auto) { import_color("discord");       $class = "brand-discord";         return svg($label === auto ? "Discord"         : $label,   0,      0,      71,      55,      $align == auto ? false : !!$align, '<g clip-path="url(#clip0)"><path class="'.$class.'" d="M60.1045 4.8978C55.5792 2.8214 50.7265 1.2916 45.6527 0.41542C45.5603 0.39851 45.468 0.440769 45.4204 0.525289C44.7963 1.6353 44.105 3.0834 43.6209 4.2216C38.1637 3.4046 32.7345 3.4046 27.3892 4.2216C26.905 3.0581 26.1886 1.6353 25.5617 0.525289C25.5141 0.443589 25.4218 0.40133 25.3294 0.41542C20.2584 1.2888 15.4057 2.8186 10.8776 4.8978C10.8384 4.9147 10.8048 4.9429 10.7825 4.9795C1.57795 18.7309 -0.943561 32.1443 0.293408 45.3914C0.299005 45.4562 0.335386 45.5182 0.385761 45.5576C6.45866 50.0174 12.3413 52.7249 18.1147 54.5195C18.2071 54.5477 18.305 54.5139 18.3638 54.4378C19.7295 52.5728 20.9469 50.6063 21.9907 48.5383C22.0523 48.4172 21.9935 48.2735 21.8676 48.2256C19.9366 47.4931 18.0979 46.6 16.3292 45.5858C16.1893 45.5041 16.1781 45.304 16.3068 45.2082C16.679 44.9293 17.0513 44.6391 17.4067 44.3461C17.471 44.2926 17.5606 44.2813 17.6362 44.3151C29.2558 49.6202 41.8354 49.6202 53.3179 44.3151C53.3935 44.2785 53.4831 44.2898 53.5502 44.3433C53.9057 44.6363 54.2779 44.9293 54.6529 45.2082C54.7816 45.304 54.7732 45.5041 54.6333 45.5858C52.8646 46.6197 51.0259 47.4931 49.0921 48.2228C48.9662 48.2707 48.9102 48.4172 48.9718 48.5383C50.038 50.6034 51.2554 52.5699 52.5959 54.435C52.6519 54.5139 52.7526 54.5477 52.845 54.5195C58.6464 52.7249 64.529 50.0174 70.6019 45.5576C70.6551 45.5182 70.6887 45.459 70.6943 45.3942C72.1747 30.0791 68.2147 16.7757 60.1968 4.9823C60.1772 4.9429 60.1437 4.9147 60.1045 4.8978ZM23.7259 37.3253C20.2276 37.3253 17.3451 34.1136 17.3451 30.1693C17.3451 26.225 20.1717 23.0133 23.7259 23.0133C27.308 23.0133 30.1626 26.2532 30.1066 30.1693C30.1066 34.1136 27.28 37.3253 23.7259 37.3253ZM47.3178 37.3253C43.8196 37.3253 40.9371 34.1136 40.9371 30.1693C40.9371 26.225 43.7636 23.0133 47.3178 23.0133C50.9 23.0133 53.7545 26.2532 53.6986 30.1693C53.6986 34.1136 50.9 37.3253 47.3178 37.3253Z"/></g><defs><clipPath id="clip0"><rect width="71" height="55" fill="white"/></clipPath>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_twitter        ($label = auto, $align = auto, $add_wrapper = auto) { import_color("twitter");       $class = "brand-twitter";         return svg($label === auto ? "Twitter"         : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M22.46,6C21.69,6.35 20.86,6.58 20,6.69C20.88,6.16 21.56,5.32 21.88,4.31C21.05,4.81 20.13,5.16 19.16,5.36C18.37,4.5 17.26,4 16,4C13.65,4 11.73,5.92 11.73,8.29C11.73,8.63 11.77,8.96 11.84,9.27C8.28,9.09 5.11,7.38 3,4.79C2.63,5.42 2.42,6.16 2.42,6.94C2.42,8.43 3.17,9.75 4.33,10.5C3.62,10.5 2.96,10.3 2.38,10C2.38,10 2.38,10 2.38,10.03C2.38,12.11 3.86,13.85 5.82,14.24C5.46,14.34 5.08,14.39 4.69,14.39C4.42,14.39 4.15,14.36 3.89,14.31C4.43,16 6,17.26 7.89,17.29C6.43,18.45 4.58,19.13 2.56,19.13C2.22,19.13 1.88,19.11 1.54,19.07C3.44,20.29 5.7,21 8.12,21C16,21 20.33,14.46 20.33,8.79C20.33,8.6 20.33,8.42 20.32,8.23C21.16,7.63 21.88,6.87 22.46,6Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_linkedin       ($label = auto, $align = auto, $add_wrapper = auto) { import_color("linkedin");      $class = "brand-linkedin";        return svg($label === auto ? "Linkedin"        : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3H19M18.5,18.5V13.2A3.26,3.26 0 0,0 15.24,9.94C14.39,9.94 13.4,10.46 12.92,11.24V10.13H10.13V18.5H12.92V13.57C12.92,12.8 13.54,12.17 14.31,12.17A1.4,1.4 0 0,1 15.71,13.57V18.5H18.5M6.88,8.56A1.68,1.68 0 0,0 8.56,6.88C8.56,5.95 7.81,5.19 6.88,5.19A1.69,1.69 0 0,0 5.19,6.88C5.19,7.81 5.95,8.56 6.88,8.56M8.27,18.5V10.13H5.5V18.5H8.27Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_github         ($label = auto, $align = auto, $add_wrapper = auto) { import_color("github");        $class = "brand-github";          return svg($label === auto ? "Github"          : $label,   0,      0,      16,      16,      $align == auto ? false : !!$align, '<path class="'.$class.'" fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_instagram      ($label = auto, $align = auto, $add_wrapper = auto) { import_color("instagram");     $class = "brand-instagram";       return svg($label === auto ? "Instagram"       : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M17.25,5.5A1.25,1.25 0 0,1 18.5,6.75A1.25,1.25 0 0,1 17.25,8A1.25,1.25 0 0,1 16,6.75A1.25,1.25 0 0,1 17.25,5.5M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_pinterest      ($label = auto, $align = auto, $add_wrapper = auto) { import_color("pinterest");     $class = "brand-pinterest";       return svg($label === auto ? "Pinterest"       : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M13,16.2C12.2,16.2 11.43,15.86 10.88,15.28L9.93,18.5L9.86,18.69L9.83,18.67C9.64,19 9.29,19.2 8.9,19.2C8.29,19.2 7.8,18.71 7.8,18.1C7.8,18.05 7.81,18 7.81,17.95H7.8L7.85,17.77L9.7,12.21C9.7,12.21 9.5,11.59 9.5,10.73C9.5,9 10.42,8.5 11.16,8.5C11.91,8.5 12.58,8.76 12.58,9.81C12.58,11.15 11.69,11.84 11.69,12.81C11.69,13.55 12.29,14.16 13.03,14.16C15.37,14.16 16.2,12.4 16.2,10.75C16.2,8.57 14.32,6.8 12,6.8C9.68,6.8 7.8,8.57 7.8,10.75C7.8,11.42 8,12.09 8.34,12.68C8.43,12.84 8.5,13 8.5,13.2A1,1 0 0,1 7.5,14.2C7.13,14.2 6.79,14 6.62,13.7C6.08,12.81 5.8,11.79 5.8,10.75C5.8,7.47 8.58,4.8 12,4.8C15.42,4.8 18.2,7.47 18.2,10.75C18.2,13.37 16.57,16.2 13,16.2M20,2H4C2.89,2 2,2.89 2,4V20A2,2 0 0,0 4,22H20A2,2 0 0,0 22,20V4C22,2.89 21.1,2 20,2Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_tumblr         ($label = auto, $align = auto, $add_wrapper = auto) { import_color("tumblr");        $class = "brand-tumblr";          return svg($label === auto ? "Tumblr"          : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M16,11H13V14.9C13,15.63 13.14,16 14.1,16H16V19C16,19 14.97,19.1 13.9,19.1C11.25,19.1 10,17.5 10,15.7V11H8V8.2C10.41,8 10.62,6.16 10.8,5H13V8H16M20,2H4C2.89,2 2,2.89 2,4V20A2,2 0 0,0 4,22H20A2,2 0 0,0 22,20V4C22,2.89 21.1,2 20,2Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_rss            ($label = auto, $align = auto, $add_wrapper = auto) { import_color("rss");           $class = "brand-rss";             return svg($label === auto ? "RSS"             : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M6.18,15.64A2.18,2.18 0 0,1 8.36,17.82C8.36,19 7.38,20 6.18,20C5,20 4,19 4,17.82A2.18,2.18 0 0,1 6.18,15.64M4,4.44A15.56,15.56 0 0,1 19.56,20H16.73A12.73,12.73 0 0,0 4,7.27V4.44M4,10.1A9.9,9.9 0 0,1 13.9,20H11.07A7.07,7.07 0 0,0 4,12.93V10.1Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_printer        ($label = auto, $align = auto, $add_wrapper = auto) { import_color("printer");       $class = "brand-printer";         return svg($label === auto ? "Printer"         : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M18,3H6V7H18M19,12A1,1 0 0,1 18,11A1,1 0 0,1 19,10A1,1 0 0,1 20,11A1,1 0 0,1 19,12M16,19H8V14H16M19,8H5A3,3 0 0,0 2,11V17H6V21H18V17H22V11A3,3 0 0,0 19,8Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_notifications  ($label = auto, $align = auto, $add_wrapper = auto) { import_color("printer");       $class = "brand-printer";         return svg($label === auto ? "Notifications"   : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M14,20A2,2 0 0,1 12,22A2,2 0 0,1 10,20H14M12,2A1,1 0 0,1 13,3V4.08C15.84,4.56 18,7.03 18,10V16L21,19H3L6,16V10C6,7.03 8.16,4.56 11,4.08V3A1,1 0 0,1 12,2Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_messenger      ($label = auto, $align = auto, $add_wrapper = auto) { import_color("messenger");     $class = "brand-messenger";       return svg($label === auto ? "Messenger"       : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M12,2C6.5,2 2,6.14 2,11.25C2,14.13 3.42,16.7 5.65,18.4L5.71,22L9.16,20.12L9.13,20.11C10.04,20.36 11,20.5 12,20.5C17.5,20.5 22,16.36 22,11.25C22,6.14 17.5,2 12,2M13.03,14.41L10.54,11.78L5.5,14.41L10.88,8.78L13.46,11.25L18.31,8.78L13.03,14.41Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_whatsapp       ($label = auto, $align = auto, $add_wrapper = auto) { import_color("whatsapp");      $class = "brand-whatsapp";        return svg($label === auto ? "Whatsapp"        : $label,   0,      0,     293.5,   293.5,    $align == auto ? false : !!$align, '<g id="background_1_" enable-background="new    "></g><g id="WhatsApp_Logo_Icon_1_"><g id="WA_Logo"><g><path class="'.$class.'" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M223.777,70.979c-19.623-19.646-45.719-30.47-73.522-30.482 c-57.288,0-103.914,46.623-103.937,103.929c-0.007,18.318,4.778,36.198,13.874,51.961l-14.745,53.858l55.098-14.453 c15.181,8.28,32.273,12.645,49.668,12.651h0.043c57.282,0,103.912-46.629,103.936-103.936 C254.202,116.737,243.4,90.624,223.777,70.979z M150.256,230.89h-0.035c-15.501-0.006-30.705-4.171-43.968-12.042l-3.155-1.871 l-32.696,8.576l8.727-31.878l-2.054-3.27c-8.647-13.753-13.215-29.65-13.208-45.974c0.019-47.63,38.772-86.38,86.424-86.38 c23.073,0.008,44.764,9.005,61.074,25.335c16.31,16.329,25.286,38.033,25.277,61.116 C236.623,192.136,197.87,230.89,150.256,230.89z M197.641,166.189c-2.597-1.299-15.364-7.582-17.745-8.449 c-2.38-0.865-4.112-1.299-5.843,1.301c-1.731,2.6-6.709,8.449-8.224,10.183c-1.515,1.732-3.03,1.95-5.626,0.649 c-2.598-1.299-10.965-4.042-20.885-12.89c-7.72-6.886-12.932-15.39-14.447-17.991c-1.515-2.6-0.162-4.005,1.139-5.3 c1.168-1.164,2.597-3.034,3.896-4.55s1.731-2.6,2.597-4.333s0.433-3.25-0.217-4.549c-0.649-1.301-5.843-14.084-8.007-19.284 c-2.108-5.063-4.249-4.378-5.843-4.458c-1.513-0.075-3.246-0.092-4.978-0.092c-1.731,0-4.544,0.65-6.925,3.25 c-2.38,2.6-9.089,8.883-9.089,21.666c0,12.783,9.305,25.131,10.604,26.865c1.298,1.733,18.313,27.964,44.364,39.214 c6.195,2.676,11.033,4.273,14.805,5.471c6.222,1.977,11.883,1.697,16.357,1.029c4.99-0.746,15.365-6.283,17.529-12.349 c2.164-6.067,2.164-11.267,1.515-12.35C201.969,168.14,200.238,167.49,197.641,166.189z"/></g></g></g>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_alert          ($label = auto, $align = auto, $add_wrapper = auto) { import_color("alert");         $class = "brand-alert";           return svg($label === auto ? "Alert"           : $label,   0,      0,      24,      24,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M13,13H11V7H13M13,17H11V15H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_amp            ($label = auto, $align = auto, $add_wrapper = auto) { import_color("amp");           $class = "brand-amp";             return svg($label === auto ? "AMP"             : $label, -22,    -22,     300,     300,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M171.887 116.28l-53.696 89.36h-9.728l9.617-58.227-30.2.047c-2.684 0-4.855-2.172-4.855-4.855 0-1.152 1.07-3.102 1.07-3.102l53.52-89.254 9.9.043-9.86 58.317 30.413-.043c2.684 0 4.855 2.172 4.855 4.855 0 1.088-.427 2.044-1.033 2.854l.004.004zM128 0C57.306 0 0 57.3 0 128s57.306 128 128 128 128-57.306 128-128S198.7 0 128 0z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_loading        ($label = auto, $align = auto, $add_wrapper = auto) { import_color("loading");       $class = "brand-loading";         return svg($label === auto ? "Loading"         : $label,   0,      0,      96,      96,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50"><animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 48 48" to="360 48 48" repeatCount="indefinite" /></path>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_darkandlight   ($label = auto, $align = auto, $add_wrapper = auto) { import_color("darkandlight");  $class = "brand-darkandlight";    return svg($label === auto ? "DarkAndLight"    : $label, -12,    -12,     640,     640,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M289.203,0C129.736,0,0,129.736,0,289.203C0,448.67,129.736,578.405,289.203,578.405 c159.467,0,289.202-129.735,289.202-289.202C578.405,129.736,448.67,0,289.203,0z M28.56,289.202 C28.56,145.48,145.481,28.56,289.203,28.56l0,0v521.286l0,0C145.485,549.846,28.56,432.925,28.56,289.202z"/>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_google         ($label = auto, $align = auto, $add_wrapper = auto) { import_color("google");        $class = "brand-google";          return svg($label === auto ? "Google"          : $label,   0,      0,      48,      48,      $align == auto ? false : !!$align, '<defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><path class="'.$class.'-2" clip-path="url(#b)" d="M0 37V11l17 13z"/><path class="'.$class.'" clip-path="url(#b)" d="M0 11l17 13 7-6.1L48 14V0H0z"/><path class="'.$class.'-3" clip-path="url(#b)" d="M0 37l30-23 7.9 1L48 0v48H0z"/><path class="'.$class.'-4" clip-path="url(#b)" d="M48 48L17 24l-4-3 35-10z"/>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_youtube        ($label = auto, $align = auto, $add_wrapper = auto) { import_color("youtube");       $class = "brand-youtube";         return svg($label === auto ? "YouTube"         : $label,   0,      0,      71,      50,      $align == auto ? false : !!$align, '<defs id="defs31" /><sodipodi:namedview pagecolor="#ffffff" bordercolor="#666666" borderopacity="1" objecttolerance="10" gridtolerance="10" guidetolerance="10" inkscape:pageopacity="0" inkscape:pageshadow="2" inkscape:window-width="1366" inkscape:window-height="715" id="namedview29" showgrid="false" fit-margin-top="0" fit-margin-left="0" fit-margin-right="0" fit-margin-bottom="0" inkscape:zoom="1.3588925" inkscape:cx="-71.668263" inkscape:cy="39.237696" inkscape:window-x="-8" inkscape:window-y="-8" inkscape:window-maximized="1" inkscape:current-layer="Layer_1" /><style type="text/css" id="style3">.st1{fill:#FFFFFF;} </style><g id="g5" transform="scale(0.58823529,0.58823529)"><path class="'.$class.'" d="M 118.9,13.3 C 117.5,8.1 113.4,4 108.2,2.6 98.7,0 60.7,0 60.7,0 60.7,0 22.7,0 13.2,2.5 8.1,3.9 3.9,8.1 2.5,13.3 0,22.8 0,42.5 0,42.5 0,42.5 0,62.3 2.5,71.7 3.9,76.9 8,81 13.2,82.4 22.8,85 60.7,85 60.7,85 c 0,0 38,0 47.5,-2.5 5.2,-1.4 9.3,-5.5 10.7,-10.7 2.5,-9.5 2.5,-29.2 2.5,-29.2 0,0 0.1,-19.8 -2.5,-29.3 z" id="path7" inkscape:connector-curvature="0"/><polygon class="st1" points="80.2,42.5 48.6,24.3 48.6,60.7 " id="polygon9" style="fill:#ffffff" /></g>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_numerama       ($label = auto, $align = auto, $add_wrapper = auto) { import_color("numerama");      $class = "brand-numerama";        return svg($label === auto ? "Numerama"        : $label,   0,      0,      80,      80,      $align == auto ? false : !!$align, '<g transform="translate(0.000000,80.000000) scale(0.100000,-0.100000)">'.'<path class="'.$class.'" d="M0 505 l0 -275 75 0 75 0 0 200 0 200 140 0 140 0 0 -200 0 -200 80 0 80 0 0 275 0 275 -295 0 -295 0 0 -275z"/><path class="'.$class.'-2" d="M210 285 l0 -275 295 0 295 0 0 275 0 275 -75 0 -75 0 0 -200 0 -200 -140 0 -140 0 0 200 0 200 -80 0 -80 0 0 -275z"/></g>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_soundcloud     ($label = auto, $align = auto, $add_wrapper = auto) { import_color("soundcloud");    $class = "brand-soundcloud";      return svg($label === auto ? "Soundcloud"      : $label,   0,      0,     291.319, 291.319,  $align == auto ? false : !!$align, '<g xmlns="http://www.w3.org/2000/svg"><path class="'.$class.'" d="M72.83,218.485h18.207V103.832c-6.828,1.93-12.982,5.435-18.207,10.041   C72.83,113.874,72.83,218.485,72.83,218.485z M36.415,140.921v77.436l1.174,0.127h17.033v-77.682H37.589   C37.589,140.803,36.415,140.921,36.415,140.921z M0,179.63c0,14.102,7.338,26.328,18.207,33.147V146.52   C7.338,153.329,0,165.556,0,179.63z M109.245,218.485h18.207v-109.6c-5.444-3.396-11.607-5.635-18.207-6.5V218.485z    M253.73,140.803h-10.242c0.519-3.168,0.847-6.382,0.847-9.705c0-32.182-25.245-58.264-56.388-58.264   c-16.896,0-31.954,7.775-42.287,19.955v125.695h108.07c20.747,0,37.589-17.388,37.589-38.855   C291.319,158.182,274.477,140.803,253.73,140.803z"/></g>', $add_wrapper == auto ? true : !!$add_wrapper); } 
    function svg_link           ($label = auto, $align = auto, $add_wrapper = auto) { import_color("link");          $class = "brand-link";            return svg($label === auto ? "Link"            : $label,   0,      0,      48,      48,      $align == auto ? false : !!$align, '<path class="'.$class.'" d="M36 24c-1.2 0-2 0.8-2 2v12c0 1.2-0.8 2-2 2h-22c-1.2 0-2-0.8-2-2v-22c0-1.2 0.8-2 2-2h12c1.2 0 2-0.8 2-2s-0.8-2-2-2h-12c-3.4 0-6 2.6-6 6v22c0 3.4 2.6 6 6 6h22c3.4 0 6-2.6 6-6v-12c0-1.2-0.8-2-2-2z"></path><path class="'.$class.'" d="M43.8 5.2c-0.2-0.4-0.6-0.8-1-1-0.2-0.2-0.6-0.2-0.8-0.2h-12c-1.2 0-2 0.8-2 2s0.8 2 2 2h7.2l-18.6 18.6c-0.8 0.8-0.8 2 0 2.8 0.4 0.4 0.8 0.6 1.4 0.6s1-0.2 1.4-0.6l18.6-18.6v7.2c0 1.2 0.8 2 2 2s2-0.8 2-2v-12c0-0.2 0-0.6-0.2-0.8z"></path>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_leboncoin      ($label = auto, $align = auto, $add_wrapper = auto) { import_color("leboncoin");     $class = "brand-leboncoin";       return svg($label === auto ? "Leboncoin"       : $label,   0,      0,     151.0,    151.0,   $align == auto ? false : !!$align, '<g transform="translate(0.000000,151.000000) scale(0.100000,-0.100000)" stroke="none"><path class="'.$class.'" d="M174 1484 c-59 -21 -123 -80 -150 -138 l-24 -51 0 -555 c0 -516 2 -558 19 -595 25 -56 67 -102 112 -125 37 -19 62 -20 624 -20 557 0 588 1 623 19 49 25 86 66 111 121 20 44 21 63 21 600 l0 555 -24 51 c-28 60 -91 117 -154 138 -66 23 -1095 22 -1158 0z m867 -244 c145 -83 270 -158 277 -167 9 -13 12 -95 12 -329 0 -172 -3 -319 -6 -328 -8 -20 -542 -326 -569 -326 -11 0 -142 70 -291 155 -203 116 -273 161 -278 177 -10 38 -7 632 4 648 15 24 532 318 561 319 17 1 123 -54 290 -149z"/><path class="'.$class.'" d="M530 1187 c-118 -67 -213 -126 -213 -132 1 -5 100 -67 220 -137 l218 -126 65 36 c36 20 139 78 228 127 89 50 161 92 162 95 0 8 -439 260 -453 260 -6 -1 -109 -56 -227 -123z"/><path class="'.$class.'" d="M260 721 l0 -269 228 -131 227 -130 3 266 c1 147 -1 270 -5 274 -11 10 -441 259 -447 259 -4 0 -6 -121 -6 -269z"/><path class="'.$class.'" d="M1018 859 l-228 -130 0 -270 c0 -148 3 -269 7 -269 3 0 107 57 230 126 l223 126 0 274 c0 151 -1 274 -2 273 -2 0 -105 -59 -230 -130z"/></g>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_500px          ($label = auto, $align = auto, $add_wrapper = auto) { import_color("500px");         $class = "brand-500px";           return svg($label === auto ? "500px"           : $label,   0,      0,     980,      997,     $align == auto ? false : !!$align, '<path class="'.$class.'" d="M415.7,462.1c-8.1-6.1-16.6-11.1-25.4-15c-8.9-4-17.7-6-26.5-6c-16.3,0-29.1,6.2-38.6,18.4c-9.6,12.4-14.3,26.2-14.3,41.4c0,16.7,4.9,30.4,14.6,41.1c9.7,10.7,23.2,16,40.4,16c8.8,0,17.6-1.8,26.5-5.3c8.8-3.5,17.2-7.9,25.1-13.2c7.9-5.3,15.4-11.3,22.3-18.1c7-6.7,13.2-13.4,18.8-19.9c-5.6-5.9-12.1-12.6-19.5-19.8S423.8,468.1,415.7,462.1L415.7,462.1z M634.1,441.1c-9.3,0-18.3,2-26.8,6c-8.6,3.9-16.7,8.9-24.4,15c-7.7,6-15,12.7-21.9,19.9s-13.3,13.8-18.8,19.9c6,7,12.5,13.9,19.5,20.5c7,6.8,14.3,12.8,22.4,18.1c7.8,5.3,16,9.6,24.7,12.9c8.6,3.3,17.8,4.9,27.5,4.9c17.2,0,30.4-5.6,39.7-16.7c9.3-11.2,13.9-24.8,13.9-41.1c0-16.2-5.1-30.2-15-41.8C664.8,447,651.2,441.1,634.1,441.1L634.1,441.1z M500,10C229.4,10,10,229.4,10,500c0,270.6,219.4,490,490,490c270.6,0,490-219.4,490-490C990,229.4,770.6,10,500,10z M746.8,549.1c-5.5,15.8-13.4,29.6-23.6,41.4c-10.2,11.9-22.9,21.1-37.9,27.9c-15.1,6.7-31.9,10.1-50.5,10.1c-14.4,0-27.9-2.2-40.4-6.6c-12.6-4.4-24.3-10.2-35.2-17.5c-10.9-7.2-21.2-15.5-31-25c-9.7-9.6-19-19.4-27.9-29.6c-9.7,10.2-19.2,20.1-28.5,29.6c-9.3,9.5-19.1,17.9-29.7,25c-10.4,7.2-21.8,13-34.1,17.5c-12.3,4.4-26.1,6.6-41.4,6.6c-19,0-35.9-3.3-50.8-10.1c-14.9-6.7-27.7-15.8-38.3-27.2c-10.7-11.4-18.8-25-24.4-40.7c-5.5-15.8-8.3-32.7-8.3-50.8c0-18.1,2.7-34.9,8-50.5c5.4-15.6,13.2-29,23.3-40.4c10.2-11.4,22.7-20.4,37.6-27.2c14.8-6.7,31.5-10.1,50.1-10.1c15.3,0,29.3,2.3,42.1,7c12.8,4.6,24.6,10.8,35.5,18.4c11,7.6,21.2,16.4,30.7,26.4s18.9,20.5,28.2,31.7c8.9-10.7,18.1-21.1,27.5-31.3c9.6-10.3,19.8-19.2,30.7-26.8c10.9-7.7,22.7-13.8,35.5-18.4c12.8-4.7,26.6-7,41.3-7c18.6,0,35.3,3.2,50.2,9.7c14.9,6.5,27.4,15.4,37.6,26.7c10.2,11.4,18.1,24.7,23.6,40c5.6,15.4,8.4,32,8.4,50.1C755.2,516.4,752.4,533.4,746.8,549.1L746.8,549.1z" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_pixelfed       ($label = auto, $align = auto, $add_wrapper = auto) { import_color("pixelfed");      $class = "brand-pixelfed";        return svg($label === auto ? "PixelFed"        : $label,   0,      0,      50,       50,     $align == auto ? false : !!$align, '<defs class="'.$class.'" id="defs56"><linearGradient id="g1" y2="0.60117739" x2="0" y1="0.55806792" x1="1"><stop id="stop2" offset="0" class="'.$class.'-6" /><stop id="stop4" offset="1" class="'.$class.'" /></linearGradient><linearGradient id="g2" y2="0" x2="0.30560157" y1="1.1191301" x1="0.5"><stop id="stop7" offset="0" class="'.$class.'-4" /><stop id="stop9" offset="1" class="'.$class.'-5" /></linearGradient><filter x="-0.266" y="-0.189" width="1.5319999" height="1.472" filterUnits="objectBoundingBox" id="filter-18-3"><feOffset id="feOffset12" dx="0" dy="1" in="SourceAlpha" result="shadowOffsetOuter1" /><feGaussianBlur id="feGaussianBlur14" stdDeviation="1.5" in="shadowOffsetOuter1" result="shadowBlurOuter1" /><feColorMatrix id="feColorMatrix16" values="0 0 0 0 0   0 0 0 0 0   0 0 0 0 0  0 0 0 0.298686594 0" type="matrix" in="shadowBlurOuter1" /></filter><linearGradient xlink:href="#g4" id="g3" gradientUnits="userSpaceOnUse" gradientTransform="scale(0.85441985,1.1703848)" x1="38.66045" y1="42.313534" x2="29.417906" y2="17.769199" /><linearGradient x1="38.66045" y1="42.313534" x2="29.417906" y2="17.769199" id="g4" gradientTransform="scale(0.85441985,1.1703848)" gradientUnits="userSpaceOnUse"><stop id="stop20" class="'.$class.'-12" offset="0%" /><stop id="stop22" class="'.$class.'-13" offset="100%" /></linearGradient><linearGradient x1="32.778084" y1="31.292349" x2="-5.737164" y2="34.564075" id="g5" gradientTransform="scale(0.85441985,1.1703848)" gradientUnits="userSpaceOnUse"><stop id="stop25" class="'.$class.'-3" offset="0%" /><stop id="stop27" class="'.$class.'-2" offset="100%" /></linearGradient><linearGradient xlink:href="#g1" id="g6" gradientUnits="userSpaceOnUse" gradientTransform="scale(0.85441985,1.1703848)" x1="26.799479" y1="19.639755" x2="6.4907837" y2="20.515251" /><linearGradient xlink:href="#g1" id="g7" x1="26.799479" y1="19.639755" x2="6.4907837" y2="20.515251" gradientTransform="matrix(0.73238181,-0.44005875,0.60279359,1.0032156,-5.4387332,4.178016)" gradientUnits="userSpaceOnUse" /><linearGradient xlink:href="#g2" id="g8" x1="15.185128" y1="33.220253" x2="9.5916662" y2="1.0193164" gradientTransform="matrix(0.87275201,0.73232484,-0.56419841,0.67238452,20.873061,-10.319713)" gradientUnits="userSpaceOnUse" /><linearGradient xlink:href="#g10" id="g9" gradientUnits="userSpaceOnUse" gradientTransform="scale(0.85441984,1.1703848)" x1="16.690788" y1="19.195547" x2="57.873302" y2="21.720842" /><linearGradient x1="16.690788" y1="19.195547" x2="57.873302" y2="21.720842" id="g10" gradientTransform="scale(0.85441984,1.1703848)" gradientUnits="userSpaceOnUse"><stop id="stop34" class="'.$class.'-7" offset="0%" /><stop id="stop36" class="'.$class.'-8" offset="100%" /></linearGradient><linearGradient x1="40.01442" y1="3.0503507" x2="21.610674" y2="22.693472" id="g11" gradientTransform="matrix(0.8028135,0.67363955,-0.61334952,0.73096044,20.873061,-10.319713)" gradientUnits="userSpaceOnUse"><stop id="stop39" class="'.$class.'-9" offset="0" /><stop id="stop41" class="'.$class.'-10" offset="1" /></linearGradient><linearGradient x1="31.906258" y1="22.861416" x2="56.143276" y2="28.198187" id="g12" gradientTransform="matrix(0.67306192,0.5647652,-0.7315899,0.87187364,20.873061,-10.319713)" gradientUnits="userSpaceOnUse"><stop id="stop44" class="'.$class.'-11" offset="0" /><stop id="stop46" class="'.$class.'-14" offset="1" /></linearGradient><linearGradient x1="18.604218" y1="60.088772" x2="29.551889" y2="34.263325" id="g13" gradientTransform="matrix(0.93316856,0.78302028,-0.52767025,0.62885203,20.873061,-10.319713)" gradientUnits="userSpaceOnUse"><stop id="stop49" class="'.$class.'-3" offset="0" /><stop id="stop51" class="'.$class.'-2" offset="1" /></linearGradient><linearGradient xlink:href="#g1" id="g14" x1="30.973358" y1="27.509178" x2="1.1089396" y2="28.796618" gradientTransform="matrix(0.64006516,0.53707767,-0.76930493,0.9168206,20.873061,-10.319713)" gradientUnits="userSpaceOnUse" /><linearGradient xlink:href="#g2" id="g15" gradientUnits="userSpaceOnUse" gradientTransform="matrix(0.87275201,0.73232484,-0.56419841,0.67238452,20.873061,-10.319713)" x1="15.185128" y1="33.220253" x2="9.5916662" y2="1.0193164" /></defs><path id="path58" d="M 24.844501,25.208859 C 20.77843,19.646166 13.002814,18.371306 7.4771766,22.36138 1.9515387,26.351453 0.76832601,34.0955 4.8343958,39.658194 l 0.3076235,0.420851 C -1.4888222,31.406438 -1.8150576,19.240724 4.7952638,10.325752 l 0.1176971,-0.1564 c 4.095389,-5.4421 11.8771861,-6.487086 17.3811281,-2.33404 5.503943,4.153045 6.6458,11.931447 2.550412,17.373547 z" style="fill:url(#g14)" /><path id="path60" d="m 24.844501,25.208859 c -6.472999,2.189353 -9.877881,9.222288 -7.605018,15.708503 2.272862,6.486214 9.362782,9.969509 15.835779,7.780157 L 33.50409,48.552478 C 25.454263,51.432746 16.076124,50.047472 8.9909709,44.102332 7.5339337,42.879734 6.2499668,41.528152 5.1420193,40.079045 L 4.8343958,39.658194 C 0.76832601,34.0955 1.9515387,26.351453 7.4771766,22.36138 13.002814,18.371306 20.77843,19.646166 24.844501,25.208859 Z" style="fill:url(#g13)" /><path id="path62" d="m 24.844501,25.208859 c 0.04163,6.84562 5.636761,12.42884 12.497071,12.470471 6.860311,0.04163 12.387942,-5.47409 12.346311,-12.319709 l -9.53e-4,-0.156747 c 0.07327,5.679151 -1.798009,11.388946 -5.714387,16.056296 -2.883411,3.436311 -6.515801,5.879029 -10.468453,7.293308 l -0.428828,0.145041 C 26.602265,50.886871 19.512345,47.403576 17.239483,40.917362 14.96662,34.431147 18.371502,27.398212 24.844501,25.208859 Z" style="fill:url(#g12)" /><path id="path64" d="M 24.844501,25.208859 C 31.381909,27.363866 38.367988,23.843413 40.448347,17.345706 42.528706,10.848 38.915553,3.83359 32.378144,1.678584 L 31.842952,1.502162 c 3.149862,0.958982 6.167442,2.558035 8.855077,4.813224 5.838829,4.899353 8.898368,11.870026 8.988901,18.887488 l 9.53e-4,0.156747 c 0.04163,6.845619 -5.486,12.361341 -12.346311,12.319709 -6.86031,-0.04163 -12.45544,-5.624851 -12.497071,-12.470471 z" style="fill:url(#g11)" /><path id="path66" style="fill:url(#g15)" d="M 24.844501,25.208859 C 28.939889,19.766759 27.798032,11.988357 22.294089,7.835312 16.790147,3.682266 9.0083499,4.727252 4.9129609,10.169352 L 4.7952628,10.325753 C 5.0886452,9.930085 5.3956916,9.54082 5.7164561,9.158548 12.244579,1.378645 22.61183,-1.308273 31.842952,1.502162 l 0.535192,0.176422 c 6.537409,2.155006 10.150562,9.169416 8.070203,15.667122 -2.080359,6.497707 -9.066438,10.01816 -15.603846,7.863153 z" /><g id="g72" style="opacity:0.54425222;fill:none" transform="matrix(-0.37460713,0.92718385,-0.92718518,-0.37460659,68.842244,2.122857)"><path id="path68" d="m 28.379451,9.2701483 0.186983,-0.07462 c 6.393149,-2.551328 13.669757,0.4995351 16.252757,6.8142937 2.583,6.314759 -0.505736,13.502142 -6.898886,16.05347 -0.05343,-1.007011 -0.227732,-1.979458 -0.50829,-2.904343 3.429966,-1.856689 5.75552,-5.45605 5.75552,-9.591913 0,-6.036745 -4.954499,-10.9304939 -11.066184,-10.9304939 -1.305803,0 -2.558782,0.223396 -3.7219,0.6336062 z" style="fill:url(#g9)" /><path id="path70" d="m 28.379451,9.2701483 0.186983,-0.07462 c 6.393149,-2.551328 13.669757,0.4995351 16.252757,6.8142937 2.583,6.314759 -0.505736,13.502142 -6.898886,16.05347 -0.05343,-1.007011 -0.227732,-1.979458 -0.50829,-2.904343 3.429966,-1.856689 5.75552,-5.45605 5.75552,-9.591913 0,-6.036745 -4.954499,-10.9304939 -11.066184,-10.9304939 -1.305803,0 -2.558782,0.223396 -3.7219,0.6336062 z" style="mix-blend-mode:overlay;fill:#000000;fill-opacity:0.49988679" /></g><path id="path74" style="opacity:0.1;fill:url(#g8)" d="M 24.844501,25.208859 C 28.939889,19.766759 27.798032,11.988357 22.294089,7.835312 16.790147,3.682266 9.0083499,4.727252 4.9129609,10.169352 L 4.7952628,10.325753 C 5.0886452,9.930085 5.3956916,9.54082 5.7164561,9.158548 12.244579,1.378645 22.61183,-1.308273 31.842952,1.502162 l 0.535192,0.176422 c 6.537409,2.155006 10.150562,9.169416 8.070203,15.667122 -2.080359,6.497707 -9.066438,10.01816 -15.603846,7.863153 z" /><path id="path76" style="opacity:0.18013395;fill:url(#g7)" d="M 4.8244748,10.490984 4.946318,10.330719 C 9.112291,4.851089 16.920883,3.718459 22.387296,7.80092 27.853707,11.883381 28.907921,19.634987 24.741947,25.114618 24.177499,24.278959 23.527245,23.535182 22.810409,22.8869 24.7942,19.528843 24.933782,15.24584 22.803609,11.700712 19.69445,6.526212 12.927139,4.883207 7.6883949,8.030957 6.5691013,8.703496 5.6101449,9.540315 4.8244318,10.490984 Z" /><g id="g82" style="opacity:0.18013395;fill:none" transform="matrix(0.85716853,-0.51503807,0.51503881,0.8571673,-5.2722905,4.334214)"><path id="path78" d="m 5.5458544,10.697205 0.1869826,-0.07462 c 6.39315,-2.5513278 13.669757,0.499535 16.252757,6.814294 2.583,6.314758 -0.505736,13.502141 -6.898886,16.053469 -0.05343,-1.007011 -0.227732,-1.979458 -0.50829,-2.904342 3.429966,-1.856689 5.755521,-5.45605 5.755521,-9.591914 0,-6.036745 -4.9545,-10.930493 -11.0661847,-10.930493 -1.3058034,0 -2.5587821,0.223396 -3.7218999,0.633606 z" style="fill:url(#g6)" /><path id="path80" d="m 5.5458544,10.697205 0.1869826,-0.07462 c 6.39315,-2.5513278 13.669757,0.499535 16.252757,6.814294 2.583,6.314758 -0.505736,13.502141 -6.898886,16.053469 -0.05343,-1.007011 -0.227732,-1.979458 -0.50829,-2.904342 3.429966,-1.856689 5.755521,-5.45605 5.755521,-9.591914 0,-6.036745 -4.9545,-10.930493 -11.0661847,-10.930493 -1.3058034,0 -2.5587821,0.223396 -3.7218999,0.633606 z" style="mix-blend-mode:multiply;fill:#000000;fill-opacity:0.77284307" /></g><g id="g88" style="opacity:0.5841518;fill:none" transform="matrix(-0.22495138,-0.97437006,0.97437146,-0.22495105,-15.913458,55.421439)"><path id="path84" d="m 10.654093,23.764822 0.186983,-0.07462 c 6.393149,-2.551328 13.669757,0.499535 16.252757,6.814293 2.583,6.314759 -0.505736,13.502142 -6.898886,16.05347 -0.05343,-1.007011 -0.227732,-1.979458 -0.50829,-2.904343 3.429966,-1.856689 5.75552,-5.45605 5.75552,-9.591913 0,-6.036745 -4.954499,-10.930493 -11.066184,-10.930493 -1.305803,0 -2.558782,0.223396 -3.7219,0.633606 z" style="fill:url(#g5)" /><path id="path86" d="m 10.654093,23.764822 0.186983,-0.07462 c 6.393149,-2.551328 13.669757,0.499535 16.252757,6.814293 2.583,6.314759 -0.505736,13.502142 -6.898886,16.05347 -0.05343,-1.007011 -0.227732,-1.979458 -0.50829,-2.904343 3.429966,-1.856689 5.75552,-5.45605 5.75552,-9.591913 0,-6.036745 -4.954499,-10.930493 -11.066184,-10.930493 -1.305803,0 -2.558782,0.223396 -3.7219,0.633606 z" style="mix-blend-mode:overlay;fill:#000000;fill-opacity:0.50308539" /></g><g id="g94" style="opacity:0.56222097;fill:none" transform="matrix(-0.99863096,-0.05233596,0.05233603,-0.99862953,57.15441,72.548735)"><path id="path90" d="m 25.135241,22.73235 0.186983,-0.07462 c 6.39315,-2.551328 13.669757,0.499535 16.252757,6.814293 2.583001,6.314759 -0.505736,13.502142 -6.898886,16.05347 -0.05343,-1.007011 -0.227731,-1.979458 -0.50829,-2.904343 3.429966,-1.856689 5.755521,-5.45605 5.755521,-9.591913 0,-6.036745 -4.9545,-10.930494 -11.066185,-10.930494 -1.305803,0 -2.558782,0.223396 -3.7219,0.633607 z" style="fill:url(#g3)" /><path id="path92" d="m 25.135241,22.73235 0.186983,-0.07462 c 6.39315,-2.551328 13.669757,0.499535 16.252757,6.814293 2.583001,6.314759 -0.505736,13.502142 -6.898886,16.05347 -0.05343,-1.007011 -0.227731,-1.979458 -0.50829,-2.904343 3.429966,-1.856689 5.755521,-5.45605 5.755521,-9.591913 0,-6.036745 -4.9545,-10.930494 -11.066185,-10.930494 -1.305803,0 -2.558782,0.223396 -3.7219,0.633607 z" style="mix-blend-mode:overlay;fill:#000000" /></g><path id="path96" d="m 32.186954,1.615568 0.191202,0.06303 c 6.537408,2.155006 10.150561,9.169416 8.070202,15.667122 -2.080359,6.497706 -9.066438,10.01816 -15.603846,7.863153 0.606364,-0.805759 1.097919,-1.662736 1.477505,-2.551578 3.820968,0.782433 7.916076,-0.48 10.574561,-3.648255 C 40.776928,14.384625 40.127202,7.451106 35.445374,3.522591 34.445068,2.683237 33.341634,2.048968 32.186954,1.61557 Z" style="mix-blend-mode:overlay;fill:#000000;fill-opacity:0.49617866" /><path id="path98" d="m 24.100846,55.523071 h 4.544831 c 4.281413,0 7.752184,-3.36365 7.752184,-7.512922 0,-4.149273 -3.470771,-7.512923 -7.752184,-7.512923 h -6.55954 c -2.470046,0 -4.472413,1.940568 -4.472413,4.334379 v 16.869977 z" style="fill:#000000;filter:url(#filter-18-3)" transform="matrix(1.0000014,0,0,1,-1.2150017,-25)" /><path id="path100" d="m 22.885879,30.523071 h 4.544837 c 4.281419,0 7.752195,-3.36365 7.752195,-7.512922 0,-4.149273 -3.470776,-7.512923 -7.752195,-7.512923 h -6.559549 c -2.47005,0 -4.47242,1.940568 -4.47242,4.334379 v 16.869977 z" style="fill:#ffffff" />', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_pixelfed_mono  ($label = auto, $align = auto, $add_wrapper = auto) { import_color("pixelfed");      $class = "brand-pixelfed";        return svg($label === auto ? "PixelFed"        : $label, -10, /*-5*/10,  1034,     1034,     $align == auto ? false : !!$align, '<path d="M500 176q-115 0 -215 58q-96 57 -152 153q-58 99 -58 214.5t58 214.5q56 96 152 152q100 58 215 58t215 -58q96 -56 152 -152q58 -99 58 -214.5t-58 -214.5q-56 -96 -152 -153q-100 -58 -215 -58zM432 435h112q36 0 66.5 17.5t48.5 47t18 65t-18 65t-48.5 47t-66.5 17.5 h-78l-111 106v-290q0 -31 22.5 -53t54.5 -22z" />'); };
    function svg_shareopenly    ($label = auto, $align = auto, $add_wrapper = auto) { import_color("shareopenly");   $class = "brand-shareopenly";     return svg($label === auto ? "ShareOpenly"     : $label,   0,      0,      18,       18,     $align == auto ? false : !!$align, '<path fill-rule="evenodd" clip-rule="evenodd" d="M13.5706 1.07915L12.9519 0.460419L12.3332 1.07914L8.6363 4.77601L9.87373 6.01345L12.0754 3.8118C12.0758 4.19678 12.0764 4.58119 12.077 4.96525V4.9653V4.96533C12.0799 6.74156 12.0828 8.51005 12.063 10.291C11.9514 12.51 10.2821 14.5766 8.13549 15.0249L8.12156 15.0278L8.10773 15.0311C6.21947 15.49 4.06987 14.5395 3.24835 12.8164L3.24176 12.8025L3.23468 12.7889C2.46106 11.3026 2.86462 9.29521 4.17623 8.31823L4.18926 8.30852L4.20193 8.29834C5.33152 7.3898 7.12207 7.44889 8.09598 8.45611L8.10921 8.46979L8.12302 8.48289C8.65152 8.9839 8.85928 9.70255 8.85928 10.7436V10.8568H10.6093V10.7436C10.6093 9.51128 10.3691 8.21034 9.34085 7.22607C7.68339 5.5272 4.88287 5.51577 3.11789 6.92446C1.07968 8.45342 0.548175 11.4013 1.67527 13.5832C2.88159 16.0953 5.88263 17.3657 8.50709 16.735C11.4878 16.1053 13.6724 13.3174 13.8118 10.3583L13.8126 10.3426L13.8127 10.3269C13.8328 8.53249 13.8299 6.73532 13.827 4.94338V4.9431V4.94298C13.8264 4.56468 13.8258 4.18661 13.8254 3.80885L16.03 6.01344L17.2674 4.77602L13.5706 1.07915Z" fill="currentColor"/>'); }

    function svg_mastodon       ($label = auto, $align = auto, $add_wrapper = auto) { import_color("mastodon");      $class = "brand-mastodon";        return svg($label === auto ? "Mastodon"        : $label,   0,      0,      32,       32,     $align == auto ? false : !!$align, '<g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g><path class="'.$class.'" d="M 15.9375 4.03125 C 12.917 4.0435 9.9179219 4.4269844 8.3574219 5.1464844 C 8.3574219 5.1464844 5 6.6748594 5 11.880859 C 5 18.077859 4.9955 25.860234 10.5625 27.365234 C 12.6945 27.938234 14.527953 28.061562 16.001953 27.976562 C 18.676953 27.825562 20 27.005859 20 27.005859 L 19.910156 25.029297 C 19.910156 25.029297 18.176297 25.640313 16.029297 25.570312 C 13.902297 25.495313 11.6615 25.335688 11.3125 22.679688 C 11.2805 22.432688 11.264625 22.182594 11.265625 21.933594 C 15.772625 23.052594 19.615828 22.420969 20.673828 22.292969 C 23.627828 21.933969 26.199344 20.081672 26.527344 18.388672 C 27.041344 15.720672 26.998047 11.880859 26.998047 11.880859 C 26.998047 6.6748594 23.646484 5.1464844 23.646484 5.1464844 C 22.000984 4.3779844 18.958 4.019 15.9375 4.03125 z M 12.705078 8.0019531 C 13.739953 8.0297031 14.762578 8.4927031 15.392578 9.4707031 L 16.001953 10.505859 L 16.609375 9.4707031 C 17.874375 7.5037031 20.709594 7.6264375 22.058594 9.1484375 C 23.302594 10.596438 23.025391 11.531 23.025391 18 L 23.025391 18.001953 L 20.578125 18.001953 L 20.578125 12.373047 C 20.578125 9.7380469 17.21875 9.6362812 17.21875 12.738281 L 17.21875 16 L 14.787109 16 L 14.787109 12.738281 C 14.787109 9.6362812 11.429688 9.7360938 11.429688 12.371094 L 11.429688 18 L 8.9765625 18 C 8.9765625 11.526 8.7043594 10.585438 9.9433594 9.1484375 C 10.622859 8.3824375 11.670203 7.9742031 12.705078 8.0019531 z"></path></g>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_seloger        ($label = auto, $align = auto, $add_wrapper = auto) { import_color("seloger");       $class = "brand-seloger";         return svg($label === auto ? "Seloger"         : $label,   0,      0,     152.0,    152.0,   $align == auto ? false : !!$align, '<g transform="translate(0.000000,152.000000) scale(0.100000,-0.100000)" stroke="none"><path class="'.$class.'" d="M0 760 l0 -760 760 0 760 0 0 760 0 760 -760 0 -760 0 0 -760z m1020 387 c0 -7 -22 -139 -50 -293 -27 -153 -50 -291 -50 -306 0 -39 25 -48 135 -48 l97 0 -7 -57 c-4 -31 -9 -62 -12 -70 -8 -21 -50 -28 -173 -28 -92 0 -122 4 -152 19 -54 26 -81 76 -81 145 1 51 98 624 109 643 3 4 45 8 95 8 66 0 89 -3 89 -13z m-364 -58 c91 -17 93 -18 81 -86 -5 -32 -12 -62 -16 -66 -4 -4 -60 -3 -125 3 -85 8 -126 8 -150 0 -33 -10 -50 -38 -40 -63 2 -7 55 -46 117 -87 131 -88 157 -120 157 -195 0 -129 -86 -217 -239 -245 -62 -11 -113 -9 -245 12 l-68 10 7 61 c3 34 9 65 11 69 3 4 69 5 148 2 97 -5 148 -3 163 4 24 13 38 56 25 78 -5 9 -57 48 -117 87 -60 40 -117 84 -128 99 -33 44 -34 125 -4 191 31 69 88 112 172 130 41 9 193 7 251 -4z m664 -28 c44 -23 80 -84 80 -135 0 -52 -40 -119 -84 -140 -26 -12 -64 -16 -157 -16 l-123 0 36 38 c31 32 35 40 26 62 -14 37 -4 113 20 147 43 61 134 81 202 44z"/></g>', $add_wrapper == auto ? true : !!$add_wrapper); }

    function svg_deezer         ($label = auto, $align = auto, $add_wrapper = auto) { import_color("deezer");        $class = "brand-deezer";          return svg($label === auto ? "Deezer"          : $label,   0,      0,     192.1,    192.1,   $align == auto ? false : !!$align, '<style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;fill:#40AB5D;}.st1{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8192_1_);}.st2{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8199_1_);}.st3{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8206_1_);}.st4{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8213_1_);}.st5{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8220_1_);}.st6{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8227_1_);}.st7{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8234_1_);}.st8{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8241_1_);}.st9{fill-rule:evenodd;clip-rule:evenodd;fill:url(#rect8248_1_);}</style><g id="g8252" transform="translate(0,86.843818)"><rect id="rect8185" x="155.5" y="-25.1" class="st0" width="42.9" height="25.1"/><linearGradient id="rect8192_1_" gradientUnits="userSpaceOnUse" x1="-111.7225" y1="241.8037" x2="-111.9427" y2="255.8256" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop offset="0" style="stop-color:#358C7B"/><stop  offset="0.5256" style="stop-color:#33A65E"/></linearGradient><rect id="rect8192" x="155.5" y="9.7" class="st1" width="42.9" height="25.1"/><linearGradient id="rect8199_1_" gradientUnits="userSpaceOnUse" x1="-123.8913" y1="223.6279" x2="-99.7725" y2="235.9171" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#222B90"/><stop  offset="1" style="stop-color:#367B99"/></linearGradient><rect id="rect8199" x="155.5" y="44.5" class="st2" width="42.9" height="25.1"/><linearGradient id="rect8206_1_" gradientUnits="userSpaceOnUse" x1="-208.4319" y1="210.7725" x2="-185.0319" y2="210.7725" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#FF9900"/><stop  offset="1" style="stop-color:#FF8000"/></linearGradient><rect id="rect8206" x="0" y="79.3" class="st3" width="42.9" height="25.1"/><linearGradient id="rect8213_1_" gradientUnits="userSpaceOnUse" x1="-180.1319" y1="210.7725" x2="-156.7319" y2="210.7725" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#FF8000"/><stop  offset="1" style="stop-color:#CC1953"/></linearGradient><rect id="rect8213" x="51.8" y="79.3" class="st4" width="42.9" height="25.1"/><linearGradient id="rect8220_1_" gradientUnits="userSpaceOnUse" x1="-151.8319" y1="210.7725" x2="-128.4319" y2="210.7725" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#CC1953"/><stop  offset="1" style="stop-color:#241284"/></linearGradient><rect id="rect8220" x="103.7" y="79.3" class="st5" width="42.9" height="25.1"/><linearGradient id="rect8227_1_" gradientUnits="userSpaceOnUse" x1="-123.5596" y1="210.7725" x2="-100.1596" y2="210.7725" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#222B90"/><stop  offset="1" style="stop-color:#3559A6"/></linearGradient><rect id="rect8227" x="155.5" y="79.3" class="st6" width="42.9" height="25.1"/><linearGradient id="rect8234_1_" gradientUnits="userSpaceOnUse" x1="-152.7555" y1="226.0811" x2="-127.5083" y2="233.4639" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="0" style="stop-color:#CC1953"/><stop  offset="1" style="stop-color:#241284"/></linearGradient><rect id="rect8234" x="103.7" y="44.5" class="st7" width="42.9" height="25.1"/><linearGradient id="rect8241_1_" gradientUnits="userSpaceOnUse" x1="-180.9648" y1="234.3341" x2="-155.899" y2="225.2108" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="2.669841e-03" style="stop-color:#FFCC00"/><stop  offset="0.9999" style="stop-color:#CE1938"/></linearGradient><rect id="rect8241" x="51.8" y="44.5" class="st8" width="42.9" height="25.1"/><linearGradient id="rect8248_1_" gradientUnits="userSpaceOnUse" x1="-178.1651" y1="257.7539" x2="-158.6987" y2="239.791" gradientTransform="matrix(1.8318 0 0 -1.8318 381.8134 477.9528)"><stop  offset="2.669841e-03" style="stop-color:#FFD100"/><stop  offset="1" style="stop-color:#FD5A22"/></linearGradient><rect id="rect8248" x="51.8" y="9.7" class="st9" width="42.9" height="25.1"/></g>', $add_wrapper == auto ? true : !!$add_wrapper); }
    function svg_spotify        ($label = auto, $align = auto, $add_wrapper = auto) { import_color("spotify");       $class = "brand-spotify";         return svg($label === auto ? "Spotify"         : $label,   0,      0,     192.1,    192.1,   $align == auto ? false : !!$align, '<path class="'.$class.'" fill="<?= $color ?>" d="m83.996 0.277c-46.249 0-83.743 37.493-83.743 83.742 0 46.251 37.494 83.741 83.743 83.741 46.254 0 83.744-37.49 83.744-83.741 0-46.246-37.49-83.738-83.745-83.738l0.001-0.004zm38.404 120.78c-1.5 2.46-4.72 3.24-7.18 1.73-19.662-12.01-44.414-14.73-73.564-8.07-2.809 0.64-5.609-1.12-6.249-3.93-0.643-2.81 1.11-5.61 3.926-6.25 31.9-7.291 59.263-4.15 81.337 9.34 2.46 1.51 3.24 4.72 1.73 7.18zm10.25-22.805c-1.89 3.075-5.91 4.045-8.98 2.155-22.51-13.839-56.823-17.846-83.448-9.764-3.453 1.043-7.1-0.903-8.148-4.35-1.04-3.453 0.907-7.093 4.354-8.143 30.413-9.228 68.222-4.758 94.072 11.127 3.07 1.89 4.04 5.91 2.15 8.976v-0.001zm0.88-23.744c-26.99-16.031-71.52-17.505-97.289-9.684-4.138 1.255-8.514-1.081-9.768-5.219-1.254-4.14 1.08-8.513 5.221-9.771 29.581-8.98 78.756-7.245 109.83 11.202 3.73 2.209 4.95 7.016 2.74 10.733-2.2 3.722-7.02 4.949-10.73 2.739z"/>', $add_wrapper == auto ? true : !!$add_wrapper); }

    function img_instagram      ($short_code = false, $size_code = "m")     { return img(url_img_instagram  ($short_code, $size_code), false, false, "img-instagram" ); }
    function img_pinterest      ($pin        = false, $size_code = false)   { return img(url_img_pinterest  ($pin),                    false, false, "img-pinterest" ); }
    function img_facebook       ($username   = false, $size_code = false)   { return img(url_img_facebook   ($username),               false, false, "img-facebook"  ); }
    function img_tumblr         ($blogname   = false, $size_code = false)   { return img(url_img_tumblr     ($blogname),               false, false, "img-tumblr"    ); }
    
    function img_loading        ($attributes = false, $size_code = false)   { return img(url_img_loading(), false, false, $attributes); }    
  //function img_loading        ($attributes = false, $size_code = false)   { return svg_loading(); }    

    // IMAGES URLs
 
    function url_img_loading () { return path("loading.svg");   }
    function url_img_blank   () { return path("img/blank.gif"); }
 
    function url_img_instagram($short_code, $size_code = "l") { return "https://instagram.com/p/$short_code/media/?size=$size_code";      }
//  function url_img_instagram($username = false, $index = 0) { $content = json_instagram_medias(($username === false) ? get("instagram_user") : $username); $n = count($content["items"]); if ($n == 0) return url_img_blank(); return $content["items"][$index % $n]["images"]["standard_resolution"]["url"]; }

    function unsplash_url()             { return "https://unsplash.com";        }
    function unsplash_url_author($id)   { return "https://unsplash.com/@".$id;  }
    
    function unsplash_url_img_random($search,$w,$h,$random = auto) {

        if ($random === false)
        {
            $seed = 0;
        }
        else if ($random === auto || $random == true)
        {
            $seed = rand(1111,9999);
        }
        else 
        {
            $seed = "$random";
        }

        $url = "https://picsum.photos/seed/$seed/info";

        $info = @json_decode(@file_get_contents($url), true);

        update_dependency_graph($url);

        if (!!$info)
        {
            $id     = false;
            $author = at($info, "author");
            $url    = at($info, "url");

            $copyright  = array("id" => $id, "author" => $author, "url" => $url);
            $copyrights = get("unsplash_copyrights", array());

            if (is_localhost() && !!get("debug")) $copyright["source"] = debug_backtrace();

            if (!in_array($copyright, $copyrights))
                set("unsplash_copyrights", array_merge($copyrights, array($copyright)));
        }

        return "https://picsum.photos/seed/$seed/$w/$h.webp";
        
        
        /*
             if ($random === auto)  $random = "&".rand(1111,9999);
        else if ($random === true)  $random = "&".rand(1111,9999);
        else if ($random === false) $random = "&";
        else                        $random = "&$random";

        return "https://source.unsplash.com/".$w."x".$h."/?".trim(strtolower(str_replace(" ", ",", "$search")))."$random.jpg";*/
    }

    function unsplash_url_img($id, $w = false, $h = false, $author = false)
    {
        if ($w === false) $w = get("default_image_ratio_w");
        if ($h === false) $h = get("default_image_ratio_h");

        if ($w < 100) { $w *= 100; $h *= 100; } // pure ratio to dimensions

                        $id     = trim($id);
        if (!!$author)  $author = trim($author);

        $copyright  = array($id, $author);
        $copyrights = get("unsplash_copyrights", array());

        if (is_localhost() && !!get("debug")) $copyright["source"] = debug_backtrace();

        if (!in_array($copyright, $copyrights))
            set("unsplash_copyrights", array_merge($copyrights, array($copyright)));

        foreach ([ "avif", "webp", "jpg", "png"] as $ext)
        {
            $local_path = path("img/unsplash/$id.$ext");
            if (!!$local_path) return $local_path;
        }

        //! THIS DIRECT API DOES NOT WORK ANYMORE
        return "https://source.unsplash.com/".$id."/".$w."x".$h."?.jpg";
    }

    function unsplash_img($id, $w = false, $h = false, $author = false, $alt = false, $attributes = false, $lazy = auto, $lazy_src = false, $content = '', $precompute_size = auto)
    {
        $alt        = $alt.(!!$author ? " (by $author on Unsplash)" : " (on Unsplash)");
        $attributes = attributes_add($attributes, array("title" => $alt));

        return img(unsplash_url_img($id, $w, $h, $author), $w, $h, $attributes, $alt, $lazy, $lazy_src, $content, $precompute_size);
    }

    function unsplash_picture($id, $w = false, $h = false, $author = false, $alt = false, $attributes = false, $lazy = auto, $lazy_src = false)
    {
        $alt        = $alt.(!!$author ? " (by $author on Unsplash)" : " (on Unsplash)");
        $attributes = attributes_add($attributes, array("title" => $alt));

        return picture(unsplash_url_img($id, $w, $h, $author), $attributes, $alt, $lazy, $lazy_src);
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
            $photosets      = at(at($data,"photosets"),"photoset");
            $photoset       = false;
            $photoset_id    = false;
            $photoset_title = false;
            
            foreach ($photosets as $photoset_index => $photoset_nth)
            { 
                $photoset       =           $photoset_nth;
                $photoset_id    =        at($photoset_nth, "id");
                $photoset_title = at(at($photoset_nth, "title"), "_content");

                if (is_string($photoset_key)) { if ($photoset_title ==       $photoset_key) break; }
                else                          { if ($photoset_index === (int)$photoset_key) break; }
            }
            
            $data           = json_flickr("photosets.getInfo", array("photoset_id" => $photoset_id), $username, $token);
            $photoset_farm  = at(at($data,"photoset"),"farm");
            
            $data           = json_flickr("photosets.getPhotos", array("photoset_id" => $photoset_id, "media" => "photo"), $username, $token);
            $photos         = at(at($data,"photoset"),"photo");
            $photo_farm     = $photoset_farm;
        }
        else
        {
            $data   = json_flickr("people.getPhotos", array(), $username, $token);
            $photos = at(at($data,"photos"),"photo");
        }
        
        foreach ($photos as $photo_index => $photo_nth)
        { 
            $photo          =    $photo_nth;
            $photo_id       = at($photo_nth, "id",      $photo_id);
            $photo_secret   = at($photo_nth, "secret",  $photo_secret);
            $photo_server   = at($photo_nth, "server",  $photo_server);
            $photo_farm     = at($photo_nth, "farm",    $photo_farm);
            $photo_title    = at($photo_nth, "title",   $photo_title);

            if (is_string($photo_key)) { if ($photo_title ==       $photo_key) break; }
            else                       { if ($photo_index === (int)$photo_key) break; }
        }
        
      //$data   = json_flickr("photos.getInfo", array("photo_id" => $photo_id), $username, $token);
      //$url    = at(at(at(at($data,"photo"),"urls"),"url"),"_content");        
        $url    = "https://farm".$photo_farm.".staticflickr.com/".$photo_server."/".$photo_id    ."_".$photo_secret."_".$photo_size.".jpg";
                //"https://farm"."3"        .".staticflickr.com/"."2936"       ."/"."13992107912"."_"."a2c5d9fe3b" ."_"."k"        .".jpg"
        
        return $url;
    }

    function url_img_pinterest ($pin      = false) { return at(at(at(at(                            json_pinterest_pin ( $pin),                                                                        "data"),"image"),"original"),"url",                                  url_img_blank()); }
    function url_img_facebook  ($username = false) { return at(at(                                          json_facebook      (($username === false) ? get("facebook_page") : $username, "cover", false), "cover"),"source",                                                   url_img_blank()); }
    function url_img_tumblr    ($blogname = false) { return at(at(at(at(at(at(at(at(json_tumblr_blog   (($blogname === false) ? get("tumblr_blog")   : $blogname, "posts"),        "response"),"posts"),0),"trail"),0),"blog"),"theme"),"header_image", url_img_blank()); }

    function url_img_domain_favicon($url)
    {
        return "https://icons.duckduckgo.com/ip3/$url.ico";
    }

    // CARDS

    function clean_social_media_text($text)
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

    $__hook_card_context = array();

    function hook_card_set_context($key, $val)
    {
        global $__hook_card_context;
        if (!array_key_exists($key, $__hook_card_context)) $__hook_card_context[$key] = ""; else $__hook_card_context[$key] .= " ";        
        $__hook_card_context[$key] .= $val;
        return $val;
    }

    function hook_card_flush_context($html = "")
    {
        global $__hook_card_context;

        if (count($__hook_card_context) > 0)
        {
            rss_record_item(at($__hook_card_context, "title"), at($__hook_card_context, "text"));
            $__hook_card_context = array();
        }

        return $html;
    }

    #endregion

    function card_title($title = false, $attributes = false)
    {
        if (is_array($title) && count($title) == 2 && is_int(at($title, 1)))
        {
            $title = array("title" => at($title, 0), "level" => at($title, 1));
        }

        $title_main      = at($title, "title",         at($title, 0, $title)           );
        $title_sub       = at($title, "subtitle",      at($title, 1, false)            );
        $title_icon      = at($title, "icon",          at($title, 2, false)            );
        $title_link      = at($title, "link",          at($title, 3, false)            );
        $title_main_link = at($title, "link_main",     at($title, 3, $title_link)      );
        $title_sub_link  = at($title, "link_subtitle", at($title, 4, $title_main_link) );
        $title_icon_link = at($title, "link_icon",     at($title, 5, $title_sub_link)  );

        $h = (int) at($title, "level", at($title, 6, $title_auto_level = get("card_title_level", 2)));

      //hook_heading($title_main);
        
        $title = "";
        
        if ($title_icon !== false && false === stripos($title_icon, "<img")
                                  && false === stripos($title_icon, "<amp-img")) $title  = img($title_icon, false, false, array("class" => component_class("img", 'card-title-icon'), "style" => "border-radius: 50%; max-width: 2.5rem; position: absolute;"), $title_main);
        if ($title_link !== false && false === stripos($title_link, "<a"))       $title  = a($title,       $title_link,                    component_class("a",   'card-title-link'), external_link);
        if ($title_main !== false && false === stripos($title_main, "<h")
                                  && false === stripos($title_main, "#"))        $title .= h($h,           $title_main,   array("class" => component_class("h$h", 'card-title-main')/*, "style" => "margin-left: ".(($title_icon !== false) ? 56 : 0)."px"*//*,  "itemprop" => "headline name"*/));
        if ($title_main !== false &&(false !== stripos($title_main, "<h") ||
                                     false !== stripos($title_main, "#")))       $title .=                 $title_main;
        if ($title_sub  !== false && false === stripos($title_sub,  "<p"))       $title .= p(              $title_sub,    array("class" => component_class("p", 'card-title-sub')/*,  "style" => "margin-left: ".(($title_icon !== false) ? 56 : 0)."px"*/));

        hook_card_set_context("title", $title_main);

        if ($title == "") return "";

        $title_hidden_microdata = span_name(trim(strip_tags($title_main)), [ "hidden" => "hidden" ]);

        return \dom\header($title.$title_hidden_microdata, attributes_add_class($attributes, component_class("header", "card-title")));
    }

    function card_media($media = false, $attributes = false)
    {
        return (($media !== false) ? section($media, attributes_add_class($attributes, component_class("section", "card-media"))) : "");
    }

    function card_text($text = false, $attributes = false, $cleanup = false)
    {
        if ($text !== false && !!$cleanup)
        {
            $text = clean_social_media_text($text);
        }
        
        hook_card_set_context("text", $text);
        
        return (($text !== false) ? section_articlebody($text, attributes_add_class($attributes, component_class("section", "card-text"))) : "");
    }

    function card_actions($button = false, $attributes = false, $attributes_button = false)
    {
        if ($button === false) return "";
        if (false === stripos($button, "button")
        &&  false === stripos($button, "href")) $button = button($button, attributes_add_class($attributes_button, component_class("button", 'card-action-button')));
        return section($button, attributes_add_class($attributes, component_class("section", "card-actions")));
    }

    function card_action($button = false, $attributes = false, $attributes_button = false)
    {
        return card_actions($button, $attributes, $attributes_button);
    }
  
    function card_properties($date = false, $url = false, $category = false, $author = false)
    {
        $author = !!$author ? $author : (!!get("a-author-me-already") ? false : get("author"));

        return div(

            (!$author   ? "" : a_author(get("author"))                      ).
            (!$category ? "" : a_category($category)                        ).
            (!$date     ? "" : time_datepublished($date, strtotime($date))  ).
            (!$url      ? "" : a($url, $url, attr_url())                    ).
            
            "", [ "class" => "card-properties", "hidden" => "hidden" ]);
    }

    function card($html, $attributes = false, $horizontal = false)
    {
        if (!!get("random_cards_rotate"))
        {
            $attributes = attributes_add($attributes, array("class" => "card", "style" => "transform: scale3d(1,1,1) rotate(".rand(-get("random_cards_rotate"),get("random_cards_rotate"))."deg);"));
        }

        $attributes = attributes_add($attributes, attr_card());
        $attributes = attributes_add($attributes, component_class("article", "card").($horizontal ? (" ".component_class("article", "card-horizontal")) : ''));

        hook_card_flush_context();

        return article($html, $attributes);
    }

    function card_from_metadata($metadata, $attributes = false)
    {
        // CARD INFO FROM METADATA

        $source   = at($metadata, "TYPE",     "instagram");
        $lazy     = at($metadata, "LAZY",     auto);
        $userdata = at($metadata, "userdata", false);

        $short_label = extract_start(at($metadata, "post_title"), 8, array("\n","!","?",".",array("#",1),","," "));

        $data = array();

        $data["content"] = has($metadata, "post_embed") ? $metadata["post_embed"] : '';

        if (has($metadata, "post_img_url"))
        {
            if (is_array(at($metadata, "post_img_url")))
            {
                $images = "";

                foreach (at($metadata, "post_img_url") as $post_img_url)
                {
                    $images .= div(img($post_img_url, false, false, false, $short_label, $lazy));
                }

                $data["content"] = div($images, "slider");
            }
            else
            {
                if (false === $metadata["post_img_url"]) $metadata["post_img_url"] = at($metadata, "user_img_url");
                if (false === $metadata["post_img_url"]) $metadata["post_img_url"] = url_img_blank();

                     if (false !== stripos($metadata["post_img_url"], ".mp4"))      $data["content"] = video($metadata["post_img_url"], false, $short_label, false);
                else if (false !== stripos($metadata["post_img_url"], "<iframe"))   $data["content"] = $metadata["post_img_url"];
                else                                                                $data["content"] = img($metadata["post_img_url"], false, false, false, $short_label, $lazy);
            }
        }

        $data["content"]        = (has($metadata, "post_url") && $data["content"] != "")    ?   a($data["content"], $metadata["post_url"], false, external_link)           : $data["content"];
        $data["content"]        =  has($metadata, "post_figcaption")                        ? cat($data["content"], wrap_each($metadata["post_figcaption"], eol(), "div")) : $data["content"];

        $data["title_main"]     = at($metadata, "post_title");
        $data["title_img_src"]  = at($metadata, "user_img_url");
        $data["title_link"]     = at($metadata, "user_url");  

        if ("" === $data["title_main"]) $data["title_main"] = get("title");

        $data["title_sub"]      =  has($metadata, "post_timestamp") ? time_datepublished(date("d/m/y", at($metadata, "post_timestamp")),           at($metadata, "post_timestamp")  ) 
                                : (has($metadata, "post_date")      ? time_datepublished(              at($metadata, "post_date", ''  ), strtotime(at($metadata, "post_date"))      ) : '');

        $data["title_sub"]      = has($metadata, "user_name")       ? cat($data["title_sub"],' ',a_author(span_name($metadata["user_name"]))) : $data["title_sub"];
        $data["title_sub"]      = has($metadata, "user_url")        ?   a($data["title_sub"], $metadata["user_url"], false, external_link)    : $data["title_sub"];

        $data["title_sub"]      = ($data["title_sub"] != "") ? cat((is_callable("svg_$source") ? call_user_func("svg_$source") : ''), $data["title_sub"]) : false;

        $data["desc"]           = has($metadata, "post_text") 
        
            ?   div_articlebody(
                
                    (
                        is_callable("add_hastag_links_$source") 
                        
                        ?   call_user_func("add_hastag_links_$source", at($metadata, "post_text"), $userdata) 
                        :                                              at($metadata, "post_text")
                    )
                ) 
            
            :   false;

        if (false !==                             at($metadata,"post_url", false)
        &&  ""    !=                                 $metadata["post_url"]
        &&  false !== strpos($data["desc"],          $metadata["post_url"])
        &&  false === strpos($data["desc"], 'href="'.$metadata["post_url"])
        &&  false === strpos($data["desc"], "href='".$metadata["post_url"])
        &&  false === strpos($data["desc"],          $metadata["post_url"]."</a>"))
        {
            $data["desc"] = str_replace($metadata["post_url"], a($metadata["post_url"], $metadata["post_url"]), $data["desc"]);
        }

        if (!!get("debug"))
        {
            $data["desc"] .= pre(raw_array_debug(has($metadata, "DEBUG_SOURCE") ? at($metadata, "DEBUG_SOURCE") : $metadata));
        }
        
    //  JSON-LD INFO FROM METADATA
        
        $properties = false;
        
        if (at($metadata, "post_title"))
        {
        //  $anchor_name  =      urlencode(at($metadata, "post_title"));        
            $date_yyymmdd = date("Y-m-d", has($metadata, "post_timestamp") ? at($metadata, "post_timestamp") : strtotime(at($metadata, "post_date", date("Y/m/d", time()))));

            $properties = array
            (
                "@context"      => "https://schema.org", 
                "@type"         => "BlogPosting",

                "url"           => get("canonical")/* . '#'.$anchor_name*/,
                "description"   => get("title", "")     . " $source post",
                "datePublished" => $date_yyymmdd,
                "dateCreated"   => $date_yyymmdd,
                "dateModified"  => $date_yyymmdd
            );

            if (get("genre")                    !== false) $properties["genre"]         = get("genre", "Website");
            if (get("publisher")                !== false) $properties["publisher"]     = array("@type" => "Organization","name" => get("publisher", author), "logo" => array("@type" => "ImageObject", "url"=> get("canonical").'/'.get("image")));
            
            if (at($metadata, "post_text")      !== false) $properties["keywords"]      = implode(' ', array_hashtags(          at($metadata, "post_text")));
            if (at($metadata, "post_text")      !== false) $properties["articleBody"]   =                                       at($metadata, "post_text");
            if (at($metadata, "post_img_url")   !== false) $properties["image"]         =                                       at($metadata, "post_img_url"); // TODO MULTIPLE IMAGES
            if (at($metadata, "post_title")     !== false) $properties["headline"]      =                                substr(at($metadata, "post_title"), 0, 110);
            if (at($metadata, "user_name")      !== false) $properties["author"]        = array("@type" => "Person","name" =>   at($metadata, "user_name")); else $properties["author"] = "unknown";
        }
        
    //  RETURN CARD + JSON-LD

        return card(

            card_title(

                (   at($data, "title_main")     === false 
                &&  at($data, "title_sub")      === false
                &&  at($data, "title_img_src")  === false
                &&  at($data, "title_link")     === false) ? false : array(
                    
                    at($data, "title_main"),        // title
                    at($data, "title_sub"),         // subtitle
                    at($data, "title_img_src"),     // icon
                    at($data, "title_link"),        // link/link_main
                    false,                          // link_subtitle
                    false,                          // link_icon
                    3//get_card_headline()          // level // TODO Correct way for user to specify headline level + decide if used for toolbar nav menu
                    
                    )
                ).
            eol().card_media  (at($data,"content")).
            eol().card_text   (at($data, "desc", false, true)).
            eol().card_actions(false),
            
            $attributes
            ).

            (($properties !== false && get("jsonld",true)) ? script_json_ld($properties) : "");
    }

    function img_from_metadata($metadata, $attributes = false)
    {
    //  IMG INFO FROM METADATA
    
        $lazy        = at($metadata, "LAZY", auto);        
        $short_label = extract_start(at($metadata, "post_title"), 8, array("\n","!","?",".",array("#",1),","," "));
        
        return img($metadata["post_img_url"], false, false, $attributes, $short_label, $lazy);
    }

    function card_      ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self")                          { return wrap_each(array_card   ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), eol(2), $container);                 }        
    function imgs       ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self")                          { return wrap_each(array_imgs   ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), eol(2), $container, true);           }    
    function cards      ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self", $s = 4, $m = 4, $l = 4)  { return wrap_each(array_cards  ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), eol(2), $container, true, $s,$m,$l); }    
    function cells_card ($source, $type, $ids = false, $filter = "", $tags_in = false, $tags_out = false,                      $s = 4, $m = 4, $l = 4)  { return wrap_each(array_cards  ($source, $type, $ids, $filter, $tags_in, $tags_out, $source), eol(2), "cell",     true, $s,$m,$l); }    

    // PROGRESS-BAR
    
    function progressbar($caption = "")
    {
        return figure
        (
            div
            (
                div("", component_class("div", "progressbar-buffer-dots"))
            .   div("", component_class("div", "progressbar-buffer"))

            .   div(span("", component_class("div", "progressbar-bar-inner")), component_class("div", "progressbar-primary-bar"))
            .   div(span("", component_class("div", "progressbar-bar-inner")), component_class("div", "progressbar-secondary-bar"))

            ,   array("role" => "progressbar", "class" => component_class("div", "progressbar"))
            )

        .   figcaption($caption)
        );
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
        $json = "";

        if (get("ajax") == "header-backgrounds"
        &&  has("support_header_backgrounds")
        &&  false !== get("support_header_backgrounds"))
        {
            del("doctype"); // TODO isn't it hacky?
            init("json");

            if (is_string(get("support_header_backgrounds")))
            {
                $json .= json_encode(explode(',', get("support_header_backgrounds")));
            }
            else 
            {
                $json .= json_encode(array_instagram_thumbs(get("instagram_user")));
            }
        }

        return $json;
    }

    /**
     * User async functions Registration System
     */

    $__asyncs = array();

    function register_async($f)      { global $__asyncs; $__asyncs[$f] = true; }
    function registered_asyncs()     { global $__asyncs; return array_keys($__asyncs); }
    
    function async($f)
    {
        $args = func_get_args();
        return async_FUNC_ARGS($f, $args);
    }
    
    function async_FUNC_ARGS($f, $args)
    {
        $async_params = -1;

        if (is_numeric($f) || is_array($f))
        {
            $async_params = $f;
            array_shift($args);
            $f = $args[0];
        }
 
        array_shift($args);

        $get     = true;
        $post    = false;
        $session = false;

        if (is_string($f) && false !== stripos($f, "-NO-ENV"))
        {
            $f = str_replace("-NO-ENV", "", $f);
            $get = false;
        }
    
        register_async($f);

        return ajax_call_with_args($f, $async_params, $args, $get, $post, $session);
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

        foreach (registered_asyncs() as $registered_async)
        {
            $html .= ajax_call($registered_async);
        }
        
        return $html;
    }
    
    /**
     * Asynchronous components
     */
     
    function img_instagram_async                              ($ids = false, $args = "m")                                                            { return ajax_call("dom\img_instagram", $ids, $args); }
    function img_pinterest_async                              ($ids = false, $args = false)                                                          { return ajax_call("dom\img_pinterest", $ids, $args); }
    function img_facebook_async                               ($ids = false, $args = false)                                                          { return ajax_call("dom\img_facebook",  $ids, $args); }
    function img_tumblr_async                                 ($ids = false, $args = false)                                                          { return ajax_call("dom\img_tumblr",    $ids, $args); }
    
    function card_async       ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self", $async_params = false) { return !$async_params ? ajax_call("dom\card_", $source, $type, $ids, $filter, $tags_in, $tags_out, $container) : ajax_call($async_params, "dom\card_", $source, $type, $ids, $filter, $tags_in, $tags_out, $container); }
    function imgs_async       ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self", $async_params = false) { return !$async_params ? ajax_call("dom\imgs",  $source, $type, $ids, $filter, $tags_in, $tags_out, $container) : ajax_call($async_params, "dom\imgs",  $source, $type, $ids, $filter, $tags_in, $tags_out, $container); }
    function cards_async      ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false, $container = "self", $async_params = false) { return !$async_params ? ajax_call("dom\cards", $source, $type, $ids, $filter, $tags_in, $tags_out, $container) : ajax_call($async_params, "dom\cards", $source, $type, $ids, $filter, $tags_in, $tags_out, $container); }
    function cells_img_async  ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false,                      $async_params = false) { return !$async_params ? ajax_call("dom\imgs",  $source, $type, $ids, $filter, $tags_in, $tags_out, "cell")     : ajax_call($async_params, "dom\imgs",  $source, $type, $ids, $filter, $tags_in, $tags_out, "cell");     }
    function cells_card_async ($source = false, $type = false, $ids = false, $filter = "", $tags_in = false, $tags_out = false,                      $async_params = false) { return !$async_params ? ajax_call("dom\cards", $source, $type, $ids, $filter, $tags_in, $tags_out, "cell")     : ajax_call($async_params, "dom\cards", $source, $type, $ids, $filter, $tags_in, $tags_out, "cell");     }
    
    function google_calendar_async                            ($ids = false, $w = false, $h = false)                                                 { return ajax_call("`dom\google_calendar",    $ids, $w, $h); }
    function google_photo_album_async                         ($ids = false, $wrapper = "div", $img_wrapper = "self")                                { return ajax_call("dom\google_photo_album", $ids, $wrapper, $img_wrapper); }

    #endregion
    #region WIP API : DOM : RSS
    ######################################################################################################################################

    function cdata($html) { return "<![CDATA[$html]]>"; }

    function rss_sanitize($html) { if (is_array($html)) { foreach ($html as &$x) { $x = rss_sanitize($x); } return $html; }  return trim(htmlspecialchars(str_replace("  ", " ", strip_tags(str_replace(">", "> ", $html))), ENT_QUOTES, 'utf-8')); }
    
    function rss_item_from_item_info($item_info)
    {
        if (!is_array($item_info)) $item_info = explode(',', $item_info);
        
        if (!is_array(at($item_info,"img_url",false))) $item_info["img_url"] = array(at($item_info,"img_url"));
        
        $rss =  
                    rss_title       (at($item_info,"title",get("title")))
        . eol() .   rss_link        (get("canonical"))
        . eol() .   rss_description (at($item_info,"description",""))
        . eol() .   rss_pubDate     (at($item_info,"timestamp", 0));
        
        foreach ($item_info["img_url"] as $img_url)
        {       
            if (!!$img_url)
            {
                $rss .= eol() . raw('<enclosure url="'     .rawurlencode($img_url)  .'" type="image/'.((false !== stripos($img_url, '.jpg'))?'jpg':'png').'" length="262144" />')
                     .  eol() . raw('<media:content url="' .rawurlencode($img_url)  .'" medium="image" />');
            }
        }
        
        $rss .= eol() . raw('<source url="'.get("canonical")."/?rss".'">RSS</source>')
        //   .  eol() . raw('<guid isPermaLink="true">https://web.cyanide-studio.com/rss/bb2/xml/?&amp;limit_matches=50&amp;limit_leagues=50&amp;days_leagues=7&amp;days_matches=1&amp;id=3518</guid>')
        ;

        return rss_item($rss);
    }
 
    function rss_channel        ($html = "")                        { return tag('channel',                      $html,  false,         true); }
    function rss_image          ($html = "")                        { return tag('image',                        $html,  false,         true); }
    function rss_url            ($html = "")                        { return tag('url',                          $html,  false,         true); }
    function rss_item           ($html = "")                        { return tag('item',                         $html,  false,         true); }
    function rss_link           ($html = "")                        { return tag('link',                         $html,  false,         true); }
    function rss_title          ($html = "")                        { return tag('title',       rss_sanitize($html), false,         true); }
    function rss_description    ($html = "", $attributes = false)   { return tag('description', rss_sanitize($html), $attributes,   true); }

    function rss_lastbuilddate  ($date = false)                     { return tag('lastBuildDate', (false === $date) ? (!!get("rss_date_granularity_daily") ? date("D, d M Y 00:00:00") : date(DATE_RSS)) : date(DATE_RSS, $date), false, true); }
    function rss_pubDate        ($date = false)                     { return tag('pubDate',       (false === $date) ? (!!get("rss_date_granularity_daily") ? date("D, d M Y 00:00:00") : date(DATE_RSS)) : date(DATE_RSS, $date), false, true); }

    function rss_copyright      ($author = false)                   { return tag('copyright', "Copyright " . ((false === $author) ? get("author", author) : $author), false, true); }
    
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
        $tile .= tile_binding($images. eol() . tile_text($item_info["description"]), 'Tile'.'Square'.'150x150'.'PeekImageAndText'.'02');
        $tile .= tile_binding($images. eol() . tile_text($item_info["description"]), 'Tile'.'Wide'.  '310x150'.'PeekImageAndText'.'01');                      
        $tile .= '</visual></tile>';
        
        return $tile;
    }
    
    function tile_binding   ($html, $template)      { return tag('binding', eol().$html.eol(), array("template" => $template), true); }
    function tile_image     ($src,      $id = 1)    { return raw('<image id="'.$id.'" src="'.tile_sanitize($src).'"/>'); }
    function tile_text      ($txt = "", $id = 1)    { return raw('<text id="'.$id.'">'.tile_sanitize($txt).'</text>'); }
    
    #endregion
    #region WIP HELPERS - COLOR
    ######################################################################################################################################
    
    function valid_hex($hex)
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

    function dec_rgb_to_hsl($var_R, $var_G, $var_B)
    {
        $var_Min = min($var_R, $var_G, $var_B);
        $var_Max = max($var_R, $var_G, $var_B);
        $del_Max = $var_Max - $var_Min;

        $H = 0;
        $S = 0;
        $L = ($var_Max + $var_Min)/2;

        if ($del_Max != 0)
        {
            $S = $del_Max / ( 1 - abs($var_Max + $var_Min - 1) );

            $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
            $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
            $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

            if      ($var_R == $var_Max) $H =             $del_B - $del_G;
            else if ($var_G == $var_Max) $H = ( 1 / 3 ) + $del_R - $del_B;
            else if ($var_B == $var_Max) $H = ( 2 / 3 ) + $del_G - $del_R;

            if ($H<0) $H++;
            if ($H>1) $H--;
        }

        return array('H' => ($H*360), 'S' => $S, 'L' => $L);
    }

    function int_rgb_to_hsl($var_R, $var_G, $var_B)
    {
        return dec_rgb_to_hsl($var_R / 255.0, $var_G / 255.0, $var_B / 255.0);
    }

    function hex_to_hsl($color)
    {
        $color = valid_hex($color);

        $R = hexdec($color[0].$color[1]);
        $G = hexdec($color[2].$color[3]);
        $B = hexdec($color[4].$color[5]);

        $var_R = ($R / 255);
        $var_G = ($G / 255);
        $var_B = ($B / 255);

        return dec_rgb_to_hsl($var_R, $var_G, $var_B);
    }
    
    function hue_to_dec($v1, $v2, $vH)
    {
        if( $vH < 0 ) $vH += 1;
        if( $vH > 1 ) $vH -= 1;

        if ((6*$vH) < 1) return ($v1 + ($v2 - $v1) * 6 * $vH);
        if ((2*$vH) < 1) return $v2;
        if ((3*$vH) < 2) return ($v1 + ($v2-$v1) * ( (2/3)-$vH ) * 6);
        
        return $v1;
    }
    
    function hsl_to_hex($hsl = array())
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

        $r = $L * 255;
        $g = $L * 255;
        $b = $L * 255;

        if ($S != 0 )
        {
            $var_2 = ($L < 0.5) ? ($L*(1+$S)) : (($L+$S) - ($S*$L));
            $var_1 = 2 * $L - $var_2;

            $r = round(255 * hue_to_dec($var_1, $var_2, $H + (1/3) ));
            $g = round(255 * hue_to_dec($var_1, $var_2, $H         ));
            $b = round(255 * hue_to_dec($var_1, $var_2, $H - (1/3) ));
        }

        $r = dechex($r);
        $g = dechex($g);
        $b = dechex($b);

        $rr = (strlen("".$r)===1) ? "0".$r:$r;
        $gg = (strlen("".$g)===1) ? "0".$g:$g;
        $bb = (strlen("".$b)===1) ? "0".$b:$b;

        return "#".$rr.$gg.$bb;
   }

    function rotate($color, $rotate = 180)
    {
        $hsl = hex_to_hsl($color);
        
        $hsl['H'] += $rotate;

        while ($hsl['H'] > 360) $hsl['H'] -= 360;
        while ($hsl['H'] < 0)   $hsl['H'] += 360;

        return hsl_to_hex($hsl);
    }

    function rotate_from_dec_rgb($r, $g, $b, $rotate = 180)
    {
        $hsl = dec_rgb_to_hsl($r, $g, $b);
        
        $hsl['H'] += $rotate;

        while ($hsl['H'] > 360) $hsl['H'] -= 360;
        while ($hsl['H'] < 0)   $hsl['H'] += 360;

        return hsl_to_hex($hsl);
    }

    function rotate_from_int_rgb($r, $g, $b, $rotate = 180)
    {
        $hsl = int_rgb_to_hsl($r, $g, $b);
        
        $hsl['H'] += $rotate;

        while ($hsl['H'] > 360) $hsl['H'] -= 360;
        while ($hsl['H'] < 0)   $hsl['H'] += 360;

        return hsl_to_hex($hsl);
    }

    function complementary($color)
    {
        return rotate($color, 180);
    }

    function int_rgb_to_hash_rrggbb($r, $g, $b)
    {
        return "#". str_pad(dechex($r),2,"0",STR_PAD_LEFT).
                    str_pad(dechex($g),2,"0",STR_PAD_LEFT).
                    str_pad(dechex($b),2,"0",STR_PAD_LEFT);
    }

    function dec_rgb_to_hash_rrggbb($r, $g, $b)
    {
        return int_rgb_to_hash_rrggbb(255*$r, 255*$g, 255*$b);
    }

    function hash_rrggbb_to_int_rgb($rrggbb, &$r, &$g, &$b)
    {
        $rrggbb = ltrim($rrggbb, "#");

        $r = hexdec(substr($rrggbb, 0, 2));
        $g = hexdec(substr($rrggbb, 2, 2));
        $b = hexdec(substr($rrggbb, 4, 2));
    }

    function hash_rrggbb_to_dec_rgb($rrggbb, &$r, &$g, &$b)
    {
        hash_rrggbb_to_int_rgb($rrggbb, $r, $g, $b);

        $r /= 255.0;
        $g /= 255.0;
        $b /= 255.0;
    }

    /*function hash_rrggbb_to_int_rgb($rrggbb)
    {        
        $rrggbb = ltrim($rrggbb, "#");

        return "rgb(".hexdec(substr($rrggbb, 0, 2)).",".
                      hexdec(substr($rrggbb, 2, 2)).",".
                      hexdec(substr($rrggbb, 4, 2)).")";
    }*/

    // (c) https://github.com/gdkraus/wcag2-color-contrast

    // calculates the luminosity of an given RGB color
    // the color code must be in the format of RRGGBB
    // the luminosity equations are from the WCAG 2 requirements
    // http://www.w3.org/TR/WCAG20/#relativeluminancedef

    function calculate_luminosity_dec_rgb($r,$g,$b) {

        if ($r <= 0.03928) { $r = $r / 12.92; } else { $r = pow((($r + 0.055) / 1.055), 2.4); }
        if ($g <= 0.03928) { $g = $g / 12.92; } else { $g = pow((($g + 0.055) / 1.055), 2.4); }
        if ($b <= 0.03928) { $b = $b / 12.92; } else { $b = pow((($b + 0.055) / 1.055), 2.4); }

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    function calculate_luminosity_int_rgb($r,$g,$b) {

        return calculate_luminosity_dec_rgb($r / 255.0, $g / 255.0, $b / 255.0);
    }

    function calculate_luminosity_hex_rgb($r,$g,$b) {

        return calculate_luminosity_dec_rgb(
            hexdec($r) / 255,
            hexdec($g) / 255,
            hexdec($b) / 255
            );
    }

    function calculate_luminosity($color, $fallback = 1.0) {

        $color = ltrim($color,"#");

        if (!ctype_xdigit($color)) return $fallback;

        return calculate_luminosity_hex_rgb(

            substr($color, 0, 2),
            substr($color, 2, 2),
            substr($color, 4, 2)
            );
    }

    // calculates the luminosity ratio of two colors
    // the luminosity ratio equations are from the WCAG 2 requirements
    // http://www.w3.org/TR/WCAG20/#contrast-ratiodef

    function calculate_luminosity_ratio($color1, $color2, $fallback1 = 1.0, $fallback2 = 0.0) {

        //$profiler = debug_track_timing();
        
        $l1 = calculate_luminosity($color1, $fallback1);
        $l2 = calculate_luminosity($color2, $fallback2);

        return ($l1 > $l2) ? (($l1 + 0.05) / ($l2 + 0.05)) : (($l2 + 0.05) / ($l1 + 0.05));
    }

    function calculate_luminosity_ratio_dec_rgb($color1_r, $color1_g, $color1_b, $color2_r, $color2_g, $color2_b) {

        $profiler = debug_track_timing();
        
        $l1 = calculate_luminosity_dec_rgb($color1_r, $color1_g, $color1_b);
        $l2 = calculate_luminosity_dec_rgb($color2_r, $color2_g, $color2_b);

        return ($l1 > $l2) ? (($l1 + 0.05) / ($l2 + 0.05)) : (($l2 + 0.05) / ($l1 + 0.05));
    }

    function calculate_luminosity_ratio_int_rgb($color1_r, $color1_g, $color1_b, $color2_r, $color2_g, $color2_b) {

        return calculate_luminosity_ratio_dec_rgb($color1_r / 255.0, $color1_g / 255.0, $color1_b / 255.0, $color2_r / 255.0, $color2_g / 255.0, $color2_b / 255.0);
    }

    function color_modify_lightness($color, $factor, $debug = false)
    {
        $profiler = debug_track_timing();

        $rrggbb = ltrim($color, "#");        
        if (!ctype_xdigit($rrggbb)) return "#".$rrggbb;

        $r = $r0 = hexdec(substr($rrggbb, 0, 2)) / 255;
        $g = $g0 = hexdec(substr($rrggbb, 2, 2)) / 255;
        $b = $b0 = hexdec(substr($rrggbb, 4, 2)) / 255;

        $l0 = calculate_luminosity_dec_rgb($r,$g,$b);
        
        $percent_min = 0;
        $percent_max = 1;
        $percent     = 0;
        $depth       = 10;

        while ($depth-- > 0)
        {
            $r = max(0, min(1, $r0 + (($factor > 1 ? 1 : 0) - $r0) * $percent));
            $g = max(0, min(1, $g0 + (($factor > 1 ? 1 : 0) - $g0) * $percent));
            $b = max(0, min(1, $b0 + (($factor > 1 ? 1 : 0) - $b0) * $percent));
            
            $l1 = calculate_luminosity_dec_rgb($r,$g,$b);    

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

    function color_lerp($a, $b, $x)
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

        $rrggbb = str_pad(dechex(255*$r),2,"0",STR_PAD_LEFT).
                  str_pad(dechex(255*$g),2,"0",STR_PAD_LEFT).
                  str_pad(dechex(255*$b),2,"0",STR_PAD_LEFT);
                    
        return "#".$rrggbb;
    }

    // Try a color correction function

    define("DOM_COLOR_CONTRAST_LINK_FROM_TEXT", 3.0);
    define("DOM_COLOR_CONTRAST_AA_MEDIUMBOLD",  3.0);
    define("DOM_COLOR_CONTRAST_AA_LARGE",       3.0);
    define("DOM_COLOR_CONTRAST_AA_NORMAL",      4.5);
    define("DOM_COLOR_CONTRAST_AAA_MEDIUMBOLD", 4.5);
    define("DOM_COLOR_CONTRAST_AAA_LARGE",      4.5);
    define("DOM_COLOR_CONTRAST_AAA_NORMAL",     7.0);
    define("DOM_COLOR_CONTRAST_DEFAULT",        DOM_COLOR_CONTRAST_AA_NORMAL);
    
    $__dom_corrected_colors_cache = false;
    
    function correct_color(

        $color,
        $background,
        $contrast_ratio_target,
        $delta,
       &$ratio,
        $debug

        )
    {
        $profiler = debug_track_timing();

        global $__dom_corrected_colors_cache;

        if (is_array($__dom_corrected_colors_cache))
        {
            $cache_key = "$color,$background,$contrast_ratio_target,$delta,$debug";

            if (array_key_exists($cache_key, $__dom_corrected_colors_cache))
            {
                if ($ratio !== null) $ratio = $__dom_corrected_colors_cache[$cache_key][1];
                return $__dom_corrected_colors_cache[$cache_key][0];
            }
        }

        if ($delta == 0) $delta = 1;

        $rrggbb      = ltrim($color,      "#"); if (!ctype_xdigit($rrggbb))      return "#$rrggbb";
        $back_rrggbb = ltrim($background, "#"); if (!ctype_xdigit($back_rrggbb)) return "#$rrggbb";

        $contrast_ratio_target += 0.05; // CHROME DEV TOOL DOES NOT GIVE SAME COMPUTATION RESULT !!

        $ratio = calculate_luminosity_ratio($background, $rrggbb);
        if ($ratio >= $contrast_ratio_target) return "#$rrggbb";

        $r0 = hexdec(substr($rrggbb, 0, 2)) / 255.0;
        $g0 = hexdec(substr($rrggbb, 2, 2)) / 255.0;
        $b0 = hexdec(substr($rrggbb, 4, 2)) / 255.0;

        $ratio       = 0;
        $percent_min = 0;
        $percent_max = 1;
        $percent     = 0;
        $depth       = 10;

        $debug_css = "";

        while ($depth-- > 0)
        {
            $r2 = $g2 = $b2 = ($delta > 0 ? 1.0 : 0.0);

            $r1 = max(0, min(1, $r0 + ($r2 - $r0) * $percent));
            $g1 = max(0, min(1, $g0 + ($g2 - $g0) * $percent));
            $b1 = max(0, min(1, $b0 + ($b2 - $b0) * $percent));

            $rrggbb = dec_rgb_to_hash_rrggbb($r1, $g1, $b1);
            $ratio  = calculate_luminosity_ratio($background, $rrggbb);

            if (!!$debug || !!get("debugcsscolors") || !!get("debug"))
            {
                $debug_css .= PHP_EOL."/* $delta # $depth // $percent% : $rrggbb // R=$ratio/$contrast_ratio_target // $background  */";
            }

            if ($ratio < $contrast_ratio_target) $percent_min = $percent; else $percent_max = $percent;
            $percent = 0.5 * ($percent_min + $percent_max);
        }

        if (is_array($__dom_corrected_colors_cache))
        {
            $__dom_corrected_colors_cache[$cache_key] = array($rrggbb.$debug_css, $ratio);
        }

        return $rrggbb.$debug_css;
    }

    function correct_lighter(

        $color,
        $background             = "#ffffff",
        $contrast_ratio_target  = DOM_COLOR_CONTRAST_DEFAULT,
        &$ratio                 = null,
        $debug                  = false)
    {
        return correct_color($color, $background, $contrast_ratio_target, 1, $ratio, $debug);
    }

    function correct_darker(

        $color,
        $background             = "#ffffff",
        $contrast_ratio_target  = DOM_COLOR_CONTRAST_DEFAULT,
        &$ratio                 = null,
        $debug                  = false)
    {
        return correct_color($color, $background, $contrast_ratio_target, -1, $ratio, $debug);
    }

    function correct_auto(

        $color,
        $background             = "#ffffff",
        $contrast_ratio_target  = DOM_COLOR_CONTRAST_DEFAULT,
        &$ratio                 = null,
        $debug                  = false)
    {
        $profiler = debug_track_timing();
        
        if (is_array($color))
        {
            $corrected = $color;

            if (!!get("static") /*&& !get("fast")*/) // TODO Currently too slow for non static websites
            {       
                $corrected = array();

                foreach ($color as $c)
                {
                    $corrected[] = correct_auto($c, $background, $contrast_ratio_target, $debug);
                }
            }

            return $corrected;
        }

        $lc = calculate_luminosity($color,      1.0);
        $lb = calculate_luminosity($background, 0.0);

        $delta = ($lc > $lb) ? 1 : -1;
        
        $ratioA = 0;
        $corrected_colorA = correct_color($color, $background, $contrast_ratio_target, $delta, $ratioA, $debug);
      //if ($ratioA >= $contrast_ratio_target) return $corrected_colorA;
        
        $ratioB = 0;
        $corrected_colorB = correct_color($color, $background, $contrast_ratio_target, -$delta, $ratioB, $debug);
      //if ($ratioB >= $contrast_ratio_target) return $corrected_colorB;

        $ratio = max($ratioA,$ratioB);

        return $ratioA >= $ratioB ? $corrected_colorA : $corrected_colorB;
    }

    #endregion
    #region BONUS SNIPPETS

    // HEREDOC SNIPPET HELPER

    function HSTART($offset = 0, $tab = "    ") { return heredoc_start($offset, $tab); }
    function HSTOP($out = null, $transform_force_minify = false, $transform_trim = true)  { return heredoc_stop($out,  $transform_force_minify, $transform_trim); }
    function HERE($out  = null, $transform_force_minify = false, $transform_trim = true)  { return heredoc_flush($out, $transform_force_minify, $transform_trim); }

    #endregion

    function php_info_css()
    {
        HSTART(-2) ?><style><?php HERE() ?>
                
            .phpinfo                        { word-break: break-all; }
            .phpinfo pre                    { margin: 0; font-family: monospace }
            .phpinfo a:link                 { text-decoration: initial; color: initial; text-decoration: initial; background-color: initial; }
            .phpinfo a:hover                { text-decoration: underline }
            .phpinfo table                  { table-layout: initial; border-collapse: collapse; border: 0; width: auto; width: -webkit-fill-available; box-shadow: none }
            .phpinfo th, .phpinfo td        { font-size: 75%; border: 1px solid #666; vertical-align: baseline; padding: 4px 5px; overflow: hidden; text-overflow: ellipsis; }
            .phpinfo td, .phpinfo td *      { font-size: 75%; text-align: left; }
            .phpinfo td:not(.e,.v,.p,.h)    { background-color: unset !important; }
            .phpinfo h1                     { font-size: 150% }
            .phpinfo h2                     { font-size: 125% }
            .phpinfo .p                     { text-align: left }
            .phpinfo .e                     { background-color: rgba(0,0,0,0.2); width: auto; font-weight: bold }
            .phpinfo .h                     { background-color: rgba(0,0,0,0.1); font-weight: bold }
            .phpinfo .v                     { background-color: rgba(0,0,0,0.1); max-width: 300px; overflow-x: auto; word-wrap: break-word }
            .phpinfo img                    { display: none }
            .phpinfo hr                     { width: 100%; border: 0; height: 1px }
            .phpinfo br                     { display: none }
            .phpinfo img[alt='PHP Logo']    { display: none }

        <?php HERE("raw_css") ?></style><?php return HSTOP();
    }

    function phpinfo($headline_offset = 0, $display_vars = false, $display_extensions = true) {    

        ob_start();
        \phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();
    
        $phpinfo_style = substr($phpinfo, strpos($phpinfo, "<style"), stripos($phpinfo, "</style>") - strpos($phpinfo, "<style") + strlen("</style>"));
    
        $phpinfo = substr($phpinfo,    strpos($phpinfo, "<body>") + strlen("<body>"));
        $phpinfo = substr($phpinfo, 0, strpos($phpinfo, "</body>"));
    
        if (!$display_vars)
        {
            $tag_bgn = "<h2>PHP Variables";
            $tag_end = "<h1>";
            $pos_bgn = stripos($phpinfo, $tag_bgn);             if (false !== $pos_bgn) {
            $pos_end = stripos($phpinfo, $tag_end, $pos_bgn);   if (false !== $pos_end) {
            $phpinfo = substr($phpinfo, 0, $pos_bgn).substr($phpinfo, $pos_end); }}
        }
    
        if ($display_extensions)
        {
            $tag = "<h1>PHP Credits";
            $pos = stripos($phpinfo, $tag);
            
            if (false !== $pos) {
                $extensions = table(tag("tbody", 
                    tr(th("Extension").th("enabled"), "h").
                    wrap_each(get_loaded_extensions(), "", function($e) { return tr(td("$e","e").td("enabled","v")); })
                    ));
                $phpinfo = substr($phpinfo, 0, $pos).$extensions.substr($phpinfo, $pos); 
                }
        }
    
        // Rewrite headline levels
    
        for ($h=(9-$headline_offset); $h>0; --$h) $phpinfo = str_replace_all(array("<h$h", "</h$h>"), array("<h".($h+$headline_offset), "</h".($h+$headline_offset).">"), $phpinfo);
        
        // Add syles
    
        return /*$phpinfo_style.*/style(php_info_css()).
            
            div($phpinfo, "phpinfo");
    }
  
    
    #region Fake face images

    $__img_fakeface_index = 0;

    function img_fakeface($gender = "female" /* male|female */, $age_min = 18, $age_max = 66, $type = "face" /* face|thumb */, $rand = auto)
    {
        global $__img_fakeface_index;
        ++$__img_fakeface_index;

        $rand = ($rand != auto ? $rand : md5("$__img_fakeface_index".microtime().rand(0, PHP_INT_MAX-1))).".jpg";
        $size = $type == "thumb" ? 350 : 731;
      //$url  = "https://fakeface.rest/$type/view/$rand?gender=$gender&minimum_age=$age_min&maximum_age=$age_max";
        $url  = "https://thispersondoesnotexist.com/";

        return img($url, $size, $size, false, "Fake AI generated face");
    }

    #endregion
    #region Footnotes

    function init_footnotes()
    {
        if (has("ajax")) 
        {
          //session_start([ 'read_and_close' => true ]);
        }
        else
        {
          //session_write_close();
          //session_start([ 'read_and_close' => true ]);

            del("footnote_index");
        }
    }
     
    function a_footnote($html, $index = false, $async = false, $title = auto)
    {
        $footnotes = get("footnotes", array());

        if ($index === false)
        {
            // Avoid duplicates
            foreach ($footnotes as $f => $footnote) if ($footnote == $html) $index = $f;
        }

        $footnote_index = false !== $index ? $index : count(get("footnotes", array()));
        $footnotes[$footnote_index] = $html;
        set("footnotes", $footnotes);

        return a("[".($footnote_index + 1)."]", "#footnote-def-".($footnote_index + 1), array(
            
            "id"    => "footnote-".($footnote_index + 1), 
            "class" => "footnote",
            "title" => (auto === $title ? strip_tags($html) : $title)
        
            ));
    }

    function footnotes($attributes = false) { return delayed_component("_".__FUNCTION__, [ $attributes ]); }
    function _footnotes($attributes)
    {
        $html = "";

        $footnotes = get("footnotes", array());

        foreach ($footnotes as $i => $footnote)
        {
            $html .= dterm(a("[".($i+1)."]", "#footnote-".($i + 1)), array("id" => "footnote-def-".($i + 1)));
            $html .= ddef($footnote);
        }

        return dlist($html, attributes_add($attributes, attributes(attr("class", "footnotes"))));
    }
      
    function address($html)     { return tag("address", $html); }
    function author($author)    { return address(a($author, url_top(), array("rel" => "author")), array("class" => "author")); }

    function h_card($photo = auto, $bio = auto, $name = auto, $url = auto, $attributes = false, $me = false)
    {
        if (!!get("gemini")) return "";

        // https://developer.mozilla.org/en-US/docs/Web/HTML/microformats#some_microformats_examples

        $photo  = auto !== $photo ? $photo : "me.png";
        $name   = auto !== $name  ? $name  : get("author", author);
        $bio    = auto !== $bio   ? $bio   : false;
        $url    = auto !== $url   ? $url   : get("canonical");

        $names = is_array($name) ? $name : explode(" ", $name);

        if (count($names) <= 1)
        {
            $name = span($name, "p-name");
        }
        else
        {
            list($given_name, $family_name) = $names;

            foreach (array_reverse(array("given-name", "given_name", "given name", "givenname", "given", "first-name", "first_name", "first name", "firstname", "first")) as $key)
            {
                $given_name = at($name, $key, $given_name);
            }

            foreach (array_reverse(array("family-name", "family_name", "family name", "familyname", "family", "last-name", "last_name", "last name", "lastname", "last")) as $key)
            {
                $family_name = at($name, $key, $family_name);
            }

            $name = span(span($given_name, "p-given-name")." ".span($family_name, "p-family-name"), "p-name");
        }

        $bio = !!$bio ? span(" (".span($bio, "p-note").")") : "";

        $img = "";
        {
            if (is_array($photo))
            {
                // https://css-tricks.com/gifs-and-prefers-reduced-motion/

                $img = picture(
                    source(
                                              at($photo, "no-motion",                at($photo, 0                 )), attributes(attr("media", "(prefers-reduced-motion: reduce)"))).
                    img($path               = at($photo, "animated",                 at($photo, 1                 ) ), 
                        $w                  = at($photo, "width",    at($photo, "w", at($photo, 2, 300            ))), 
                        $h                  = at($photo, "height",   at($photo, "h", at($photo, 3, 400            ))), $attr = false, 
                        $alt                = at($photo, "alt",                      at($photo, 4, "Author photo" ) ), 
                        $lazy               = auto, 
                        $lazy_src           = auto, 
                        $content            = auto, 
                        $precompute_size    = auto, 
                        $src_attribute      = "srcset"
                        )
                    );
            }
            else if (false !== stripos($photo, "<img")
                 ||  false !== stripos($photo, "<picture"))
            {
                $img = $photo;
            }
            else
            {
                $img = img($photo, 300, 400);
            }
        }

        // Microformats

        $attributes = attributes_add($attributes, attributes(

            attr("class",   "h-card"    ), 
            attr("class",   "u-url"     ), 
            attr("class",   "u-uid"     ), 
            attr("rel",     "me"        ), 
            attr("hidden",  "hidden"    )
        ));

        if (!!$me) set("a-author-me-already", true); // Prevents h-entries to embed author each time, by knowing there already is a h-card in here

        /*
        $a_author = !$me ? "" : a_author($name, [ "rel" => "author", "hidden" => "hidden" ]);
        $h_card   = a($img.$name.$bio, $url, $attributes);

        return $a_author.$h_card;
        */

        if (!!$me) $attributes = attributes_add($attributes, attr_author());

        return a($img.$name.$bio, $url, $attributes);
    }
    
    ######################################################################################################################################
    #endregion
    #region die alternative

    function bye()
    {
        @ob_end_clean();

        $args = func_get_args();

        if (count($args) == 0)
        {
            die();
        }

        $boilerplate_prefix = '';
        $boilerplate_suffix = '';

        if (!DOM_CLI)
        {
            $boilerplate_prefix = '<style> :root { color-scheme: light dark; } </style><pre>';
            $boilerplate_suffix = '</pre>';    
        }

        if (count($args) == 1)
        {
            die($boilerplate_prefix.print_r($args[0], true).$boilerplate_suffix);
        }

        die($boilerplate_prefix.$args[0].print_r($args[1], true).$boilerplate_suffix);
    }

    ######################################################################################################################################
    #endregion

?>