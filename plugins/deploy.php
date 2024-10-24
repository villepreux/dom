<?php

define("DEPLOY_CLI", isset($argv) || php_sapi_name() == "cli");

#region command-line

function arg_state($flag, $on = 1, $off = 0) { global $argv; return (in_array("--$flag",  $argv) || (array_key_exists($flag, $_GET) && !!$_GET[$flag] )) ? $on : $off; }
function arg_array($flag)                    { global $argv; $cli_values = []; { foreach ($argv as $arg) { $tag = "--$flag="; $pos = stripos($arg, $tag); if (false === $pos) continue; $val = substr($arg, $pos + strlen($tag)); $cli_values = array_merge($cli_values, explode(",", $val)); } } $get_values = []; { $get_values = array_key_exists($flag, $_GET) ? explode(",", $_GET[$flag]) : array(); } return array_merge($get_values, $cli_values); }
function arg_value($flag, $fallback)         { $values = arg_array($flag); if (0 == count($values)) return $fallback; return $values[0]; }

$cmdline_option_static                  = 1;
$cmdline_option_cls                     = arg_state("cls");
$cmdline_option_compare_dates           = arg_state("compare-dates", 1, arg_state("horo"));
$cmdline_option_gemini                  = arg_state("gemini");
$cmdline_option_gemini_local_bin        = arg_state("gemini-local-bin");
$cmdline_option_output                  = arg_value("output", arg_state("gemini") ? ".gemini" : ".static");
$cmdline_option_fast                    = arg_state("fast");
$cmdline_option_profiling               = arg_state("profiling");
$cmdline_option_debug                   = arg_state("debug", 1, arg_state("profiling"));
$cmdline_option_verbose                 = arg_state("verbose");
$cmdline_option_test                    = arg_state("test");
$cmdline_option_github_action           = arg_state("github-action");
$cmdline_option_scrap                   = arg_state("scrap");
$cmdline_option_beautify                = arg_state("beautify", 1, arg_state("debug"));
$cmdline_option_os                      = arg_state("unix", "unix", "win");
$cmdline_option_copy                    = arg_state("copy");
$cmdline_option_compile                 = arg_state("compile");/*
$cmdline_option_mt                      = arg_state("mt");*/
$cmdline_option_compile_one             = arg_value("compile-one", false);
$cmdline_option_generate                = arg_state("generate");
$cmdline_option_generate_index_files    = arg_state("generate-index-files");
$cmdline_option_netlify                 = arg_state("netlify");
$cmdline_option_spa                     = arg_state("spa");
$cmdline_option_lunr                    = arg_state("lunr");
$cmdline_option_blogroll                = arg_state("blogroll");
$cmdline_option_minify                  = arg_state("minify", 1, !arg_state("beautify"));
$cmdline_option_include                 = arg_array("include");
$cmdline_option_exclude                 = array_merge(arg_array("exclude"), array(".netlify", ".static", ".gemini"));
$domain_src                             = arg_value("domain-src",       substr(trim(trim(getcwd()), "/\\"), max(strripos(trim(trim(getcwd()), "/\\"), "/"), strripos(trim(trim(getcwd()), "/\\"), "\\")) + 1));
$domain_dst                             = arg_value("domain-dst",      "$domain_src/$cmdline_option_output");
$main_src                               = arg_value("main-src",     "../$domain_src");
$main_dst                               = arg_value("main-dst",     "../$domain_dst");
$server_name                            = arg_value("server-name",      $domain_dst);
$server_http_host                       = arg_value("server-http-host", $domain_dst);
$cmdline_option_process                 = arg_value("process",          "1/1");

list($cmdline_option_process_id, $cmdline_option_process_count) = explode("/", $cmdline_option_process);

#endregion
#region Utilities

/** 
 * @param mixed $a
 * @param mixed $k
 * @param mixed $d
 * @return mixed
*/
function deploy_at($a, $k, $d = false)
{
    if (is_array($k))
    {
        foreach ($k as $k0) 
        {
            if (!is_array($a) || !array_key_exists($k0,$a)) return $d; 
            $a = deploy_at($a, $k0, $d);
        }
        
        return $a; 
    }

    return (is_array($a) && array_key_exists($k,$a)) ? $a[$k] : $d;
}

/**
 * @return bool
 */
function deploy_is_localhost()
{ 
    $server_http_host = deploy_at(array_merge($_GET, $_SERVER), 'HTTP_HOST', "127.0.0.1"); 

    return (false !== stripos($server_http_host, "localhost"))
        || (false !== stripos($server_http_host, "127.0.0.1"));
}

$__deploy_log_line = 0;
$__deploy_log_dimensions = [ 96, 52, 24 ]; // 4 more formatting characters will be used, if u need max line length
$__deploy_log_progressbar_size = 0;

function deploy_log()
{
    global $cmdline_option_compile_one;
    if (!!$cmdline_option_compile_one) return;

    $args = func_get_args();
    if (0 == count($args)) return deploy_log(0.0, "");
    $progress_percent = 0;
    if (count($args) >= 2 && is_int($args[0]) && is_int($args[1])) { $progress_percent = (($args[0] + 1) / $args[1]); array_shift($args); array_shift($args); }
    if (count($args) >= 1 && is_numeric($args[0])) { $progress_percent = (float)array_shift($args); }
    if (0 == count($args)) return deploy_log($progress_percent, "");

    global $__deploy_log_line, $__deploy_log_dimensions, $__deploy_log_progressbar_size;

    $new_line = !!deploy_at($args, 0);

    if ($new_line)
    {
        $len = "mb_strlen"; // strlen vs iconv_strlen vs mb_strlen
        $sub = "mb_substr"; // substr vs mb_substr

        $str  = deploy_at($args, 0, ""); $str  = $sub($str,  0, $__deploy_log_dimensions[0]); $len_str  = @$len($str);  if ($len_str  > $__deploy_log_dimensions[0]) $str  = $sub($str,  0, $__deploy_log_dimensions[0] - 3)."[…]"; $str  .= str_repeat(" ", $__deploy_log_dimensions[0] - $len_str);
        $info = deploy_at($args, 1, ""); $info = $sub($info, 0, $__deploy_log_dimensions[1]); $len_info = @$len($info); if ($len_info > $__deploy_log_dimensions[1]) $info = $sub($info, 0, $__deploy_log_dimensions[1] - 3)."[…]"; $info .= str_repeat(" ", $__deploy_log_dimensions[1] - $len_info);
    
        if ($__deploy_log_line > 0 && $__deploy_log_progressbar_size > 0) // Complete progressbar dots to vizualize its dimensions
        {
            echo str_repeat("-", $__deploy_log_dimensions[2] - $__deploy_log_progressbar_size)."]";
        }

        echo (DEPLOY_CLI ? "" : str_repeat("&#8203;", 4*1024)).PHP_EOL."$str $info ";
        if (!DEPLOY_CLI) flush();

        $__deploy_log_progressbar_size = 0;
        $__deploy_log_line++;
    }

    $progressbar_size_target = (int)max($__deploy_log_progressbar_size, min($__deploy_log_dimensions[2], $progress_percent * $__deploy_log_dimensions[2]));
    if ($__deploy_log_progressbar_size == 0 && $progressbar_size_target > 0) echo "[";
    echo str_repeat("|", max(0, min($__deploy_log_dimensions[2], $progressbar_size_target - $__deploy_log_progressbar_size)));
    $__deploy_log_progressbar_size = $progressbar_size_target;

    flush();
}

$__deploy_is_compiled = [];

function deploy_is_compiled($path, $extension = false)
{
    global $__deploy_is_compiled;
    if (isset($__deploy_is_compiled[$path])) return $__deploy_is_compiled[$path];
    $__deploy_is_compiled[$path] = true;

    if (false === $extension)
    {
        $pathinfo  = pathinfo($path);
        $extension = array_key_exists("extension", $pathinfo) ? $pathinfo["extension"] : "";
    }
    
    if ($extension == "php"
    ||  $extension == "css"
    || ($extension == "js" && false === stripos($path, "prism"))) // TODO handle vendor special cases
    {
        return true;
    }

    $__deploy_is_compiled[$path] = false;
    return false;
}

function deploy_exec($cmd, $die_on_error = true, $output = false)
{
    $result  = false;
    $outputs = array();

    exec($cmd, $outputs, $result);

    if ($result != 0)
    {
        deploy_log("");
        deploy_log("CWD: ".getcwd());
        deploy_log("CMD: $cmd");
        deploy_log("RES: $result");
        deploy_log("");
        foreach ($outputs as $output) deploy_log($output);
        deploy_log("");
        if ($die_on_error) die();
    }
    else if ($output)
    {
        foreach ($outputs as $output) deploy_log($output);
    }


    return implode(PHP_EOL, $outputs);
}

function deploy_compare($a,$b)
{
    if ($a < $b) return -1;
    if ($a > $b) return  1;

    return 0;
}

function deploy_compare_first_value($a,$b)
{
    if ($a[0] < $b[0]) return -1;
    if ($a[0] > $b[0]) return  1;

    return 0;
}

function should_be_parsed($path, $parse_output = false)
{
    global $cmdline_option_include, $cmdline_option_exclude, $cmdline_option_static;

    // Specials

    if (!!$cmdline_option_static)
    {
        if (false !== stripos($path, "/vendor/")) return false; // INFO PHP dependencies not needed on a static site
    }

    if (false !== stripos($path, "/node_modules/")) return false; // TODO Handle that

    if (!$parse_output)
    {
        if (false !== stripos($path, "/.static/"))  return false; // INFO Do not parse my output
        if (false !== stripos($path, "/.gemini/"))  return false; // INFO Do not parse my output
    }

    if (false !== stripos($path, "static.php"))                 return false; // INFO Do not parse myself
    if (false !== stripos($path, "/dom/plugins/deploy.php"))    return false; // INFO Do not parse myself
    if (false !== stripos($path, "/dom/plugins/deploy-v2.php")) return false; // INFO Do not parse myself
    if (false !== stripos($path, "static.bat"))                 return false; // INFO Do not parse myself

    // Excluded

    if (count($cmdline_option_exclude) > 0)
    {
        foreach ($cmdline_option_exclude as $e)
        {
            global $cmdline_option_output;
            if ($parse_output && false !== stripos($path, "/$cmdline_option_output/")) continue;

            if (false !== stripos($path, $e)) return false;
        }
    }

    // Included

    $should_be_parsed_deeper = false;
    
    if (is_dir($path)) 
    {
        foreach (scandir($path) as $name)
        {
            if ($name[0] == ".")            continue;
            if ($name    == "vendor")       continue;
            if ($name    == "static.php")   continue;
            if ($name    == "static.bat")   continue;

            if (should_be_parsed("$path/$name"))
            {
                $should_be_parsed_deeper = true;
                break;
            }
        }        
    }

    if (!$should_be_parsed_deeper)
    {
        if (count($cmdline_option_include) > 0)
        {
            $found = false;

            foreach ($cmdline_option_include as $i)
            {
                if (false !== stripos($path, $i)) return true;
            }

            return false;
        }
    }

    return true;
}

function deploy_scan($path, $parse_output = false)
{
    //global $cmdline_option_process, $cmdline_option_process_count;
    //$path_hash = intval(hash("crc32b", $path), 16);
    //if ($depth == 0 && $cmdline_option_process != ($path_hash % $cmdline_option_process_count)) return;

    $scan = array();

    foreach (scandir($path) as $name)
    {
        if ($name[0] == ".")            continue;
        if ($name    == "vendor")       continue;
        if ($name    == "static.php")   continue;
        if ($name    == "static.bat")   continue;

        if (!should_be_parsed("$path/$name", $parse_output)) continue;

        $scan[] = $name;
    }

    return $scan;
}

function deploy_mime_from_filename($filename) 
{   
    $pathinfo  = is_array($filename) ? $filename : pathinfo($filename);
    $extension = deploy_at($pathinfo, "extension", "");

    $mimes = array( 

        // Text
        'txt'   => 'text/plain',
        'htm'   => 'text/html',
        'html'  => 'text/html',
        'gmi'   => 'text/gemini',
        'php'   => 'text/html',
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'xml'   => 'application/xml',
        //Flash
        'swf'   => 'application/x-shockwave-flash',
        'flv'   => 'video/x-flv',
        // images
        'png'   => 'image/png',        
        'jpe'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'jxr'   => 'image/jxr',
        'webp'  => 'image/webp',
        'jpg'   => 'image/jpeg',
        'gif'   => 'image/gif',
        'bmp'   => 'image/bmp',
        'ico'   => 'image/vnd.microsoft.icon',
        'tiff'  => 'image/tiff',
        'tif'   => 'image/tiff',
        'svg'   => 'image/svg+xml',
        'svgz'  => 'image/svg+xml',
        // Archives
        'zip'   => 'application/zip',        
        'rar'   => 'application/x-rar-compressed',
        'exe'   => 'application/x-msdownload',
        'msi'   => 'application/x-msdownload',
        'cab'   => 'application/vnd.ms-cab-compressed',
        // Audio/video
        'mp3'   => 'audio/mpeg',        
        'qt'    => 'video/quicktime',
        'mov'   => 'video/quicktime',
        // Adobe
        'pdf'   => 'application/pdf',        
        'psd'   => 'image/vnd.adobe.photoshop',
        'ai'    => 'application/postscript',
        'eps'   => 'application/postscript',
        'ps'    => 'application/postscript',
        // MS-Office
        'doc'   => 'application/msword',        
        'rtf'   => 'application/rtf',
        'xls'   => 'application/vnd.ms-excel',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'docx'  => 'application/msword',
        'xlsx'  => 'application/vnd.ms-excel',
        'pptx'  => 'application/vnd.ms-powerpoint',
        // Open Office
        'odt'   => 'application/vnd.oasis.opendocument.text',        
        'ods'   => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    return deploy_at($mimes, $extension, 'application/octet-stream');
}

function deploy_is_text_file($path)
{
    $mime = deploy_mime_from_filename($path);
    
    return 0     === stripos($mime, "text/")
        || 0     === stripos($mime, "application/")
        || false !== stripos($mime, "+xml");
}

function deploy_is_image_file($path)
{
    return 0 === stripos(deploy_mime_from_filename($path), "image/");
}

function deploy_subdirs($path)                  { return array_filter(deploy_scan($path), function($name) use ($path) { return  is_dir("$path/$name"); }); }
function deploy_subfiles($path)                 { return array_filter(deploy_scan($path), function($name) use ($path) { return !is_dir("$path/$name"); }); }

function deploy_content($str, $encode = false)  { return $encode ? base64_encode(str_replace("\r\n","\n",trim($str))) : str_replace("\r\n","\n",trim($str)); }
function deploy_content_file($path)             { return deploy_content(@file_get_contents($path), !deploy_is_text_file($path)); }

function deploy_substr($str, $pos, $length)
{
    $sub = "mb_substr"; // substr
    $pad = "str_pad";   // str_pad vs mb_str_pad (php >=8)

    return $pad($sub($str, $pos, $length), $length);
}

function deploy_diff($str1, $str2, $diff_chunk_length = 16, $prefix = "")
{
    $str1 = str_replace("\n", "\\n", str_replace("\r", "\\r", $str1));
    $str2 = str_replace("\n", "\\n", str_replace("\r", "\\r", $str2));

    $len  = "mb_strlen"; // strlen vs iconv_strlen vs mb_strlen
    $len1 = $len($str1);
    $len2 = $len($str2);

    for ($c = 0; $c < min($len1, $len2); ++$c)
    if ($str1[$c] != $str2[$c]) return $prefix.(str_pad(number_format($c), 7, " ", STR_PAD_LEFT).": [".deploy_substr($str1, $c,    $diff_chunk_length)."] VS [".deploy_substr($str2, $c,    $diff_chunk_length)."]");
    if ($len1     <  $len2)     return $prefix.(str_pad(            $len1, 7, " ", STR_PAD_LEFT).": [".   str_repeat(" ",          $diff_chunk_length)."] VS [".deploy_substr($str2, $len1, $diff_chunk_length)."]");
    if ($len2     <  $len1)     return $prefix.(str_pad(            $len2, 7, " ", STR_PAD_LEFT).": [".deploy_substr($str1, $len2, $diff_chunk_length)."] VS [".   str_repeat(" ",          $diff_chunk_length)."]");

    return "";
}

function deploy_init_terminal($clear_screen = true)
{ 
    global $cmdline_option_github_action, $cmdline_option_compile_one;

    if ($clear_screen && !$cmdline_option_github_action && DEPLOY_CLI)
    {
        DIRECTORY_SEPARATOR === '\\' ? popen('cls', 'w') : exec('clear');
    }

    if (!$cmdline_option_github_action && !DEPLOY_CLI && !$cmdline_option_compile_one)
    {
        echo "<html><head><title>DOM CLI</title><style> html { background-color: #0E0E0E; color: #F2FFF2; font-size: 15px; font-family: monospace; } </style><script>const resizeObserver = new ResizeObserver(function(entries) { window.scrollTo(0,document.body.scrollHeight); }); resizeObserver.observe(document.body);</script><body><pre>";
    }
}

function deploy_compile_error_check($html, $src)
{
    if (false === stripos($html, "PRAGMA STATIC NO DIE ON ERROR"))
    {
        if ((false !== stripos($html, "127.0.0.1"))
        ||  (false !== stripos($html, "Notice:"  ) && false !== stripos($html, "Call Stack:"))
        ||  (false !== stripos($html, "Warning:" ) && false !== stripos($html, "Call Stack:"))
        ||  (false !== stripos($html, "Error:"   ) && false !== stripos($html, "Call Stack:"))
        ||  (false !== stripos($html, "[COMPILE ERROR]"  )))
        { 
            global $cmdline_option_compile_one;

            if (!$cmdline_option_compile_one)
            {
                $msg = "Unknown error";
                if (false !== stripos($html, "127.0.0.1")) $msg = "Localhost detected 127.0.0.1";
                if (false !== stripos($html, "Notice:"  ) && false !== stripos($html, "Call Stack:")) $msg = "PHP Notice";
                if (false !== stripos($html, "Warning:" ) && false !== stripos($html, "Call Stack:")) $msg = "PHP Warning";
                if (false !== stripos($html, "Error:"   ) && false !== stripos($html, "Call Stack:")) $msg = "PHP Error";
                if (false !== stripos($html, "[COMPILE ERROR]"  )) $msg = "INTERNAL [COMPILE ERROR]";
        
                error_log("-----------------------");
                error_log(PHP_EOL.$html);
                error_log("-----------------------");
                error_log("Error while compiling $src ($msg)");
                error_log("-----------------------");
                
                if (strlen($html) > 200*80) 
                {
                    $html = substr($html, 0, 200*80/2).PHP_EOL.PHP_EOL."[...]".PHP_EOL.PHP_EOL.substr($html, strlen($html) - (200*80/2));
                }

                $html_lines = explode(PHP_EOL, $html);

                echo(PHP_EOL."-----------------------");
                foreach ($html_lines as $html) echo(PHP_EOL.$html);
                echo(PHP_EOL."-----------------------");
                echo(PHP_EOL."Error while compiling $src ($msg)");
                echo(PHP_EOL."-----------------------");
            }
            
            die();
        }
    }
}

#endregion

if (!$cmdline_option_github_action)
{
    require_once(__DIR__."/../dom.php");
}
else
{
    require_once("dom/dom.php"); // TODO verify, now that this code has moved from ROOT/static.php to ROOT/dom/plugin/deploy.php
}

if (!deploy_is_localhost()) { die("Can only by run locally"); }

deploy_init_terminal($cmdline_option_cls);

@set_time_limit(24*60*60);
@ini_set('memory_limit', '-1');

$root_sources = array("$main_src");
{
    foreach (array("portfolio/web", "dom/examples") as $path)
        $root_sources = array_merge($root_sources, array_map(
            function($name) use ($main_src, $path) { return "$main_src/$path/$name"; }, 
            deploy_subdirs("$main_src/$path")
            ));
}

$php_args_common = "";
{
    $php_args_common = 

            "beautify"                      ."=".   "$cmdline_option_beautify".
        " "."minify"                        ."=".   "$cmdline_option_minify".
        " "."noajax"                        ."=".   "1".                        // (SLOWER!)
    //" "."masonry"                       ."=".   "0".                        // (Slower final website)
        " "."static"                        ."=".   "1".                        // Hint to inform the site that it is a static version
        " "."scrap"                         ."=".   "$cmdline_option_scrap".    // Hint to inform it can scrap if needed, as it's a precompiled site (MUCH SLOWER!)
        " "."spa"                           ."=".   "$cmdline_option_spa".      // Hint to inform we want a Single Page Application (EXPERIMENTAL / WIP)
        " "."HTTP_ACCEPT_LANGUAGE"          ."=".   "fr".                       // Make assumptions on Netlify server
        " "."SERVER_NAME"                   ."=".   "$server_name".
        " "."SERVER_PORT"                   ."=".   "80".
        " "."HTTPS"                         ."=".   "on".
        " "."HTTP_HOST"                     ."=".   "$server_http_host".
        " "."rand_seed"                     ."=".   "666".
        " "."path_max_depth"                ."=".   "32".
        " "."rss_date_granularity_daily"    ."=".   "1".
        " "."rss_date_granularity_file"     ."=".   "1".
        " "."live_domain"                   ."=".   "$server_name".

        "";

    if (!!$cmdline_option_debug)                $php_args_common .= " debug=1";
    if (!!$cmdline_option_profiling)            $php_args_common .= " profiling=1";
    if (!!$cmdline_option_fast)                 $php_args_common .= " fast=1";
    if (!!$cmdline_option_output
    &&    $cmdline_option_output != "static")   $php_args_common .= " $cmdline_option_output=1";
}

$cmdline_values = [

    "cmdline_option_github_action" ,
    "cmdline_option_scrap"         ,
    "cmdline_option_beautify"      ,
    "cmdline_option_os"            ,
    "cmdline_option_copy"          ,
    "cmdline_option_compile"       ,
    "cmdline_option_generate"      ,
    "cmdline_option_netlify"       ,
    "cmdline_option_spa"           ,
    "cmdline_option_lunr"          ,
    "cmdline_option_blogroll"      ,
    "cmdline_option_minify"        ,
    "cmdline_option_include"       ,
    "cmdline_option_exclude"       ,
    "domain_src"                   ,
    "domain_dst"                   ,
    "main_src"                     ,
    "main_dst"                     ,
];

foreach ($cmdline_values as $cmdline_value)      deploy_log("[i] Generate static site from cmd CLI option $cmdline_value=".json_encode(${$cmdline_value}));
foreach (explode(" ", $php_args_common) as $arg) deploy_log("[i] Generate static site php cmd-line option $arg");

$target_ext = ($cmdline_option_output == "gemini") ? "gmi" : "html";

if (!!$cmdline_option_test)
{
    deploy_log("[i] Testing");

    /*$text = "Bienvenu à la scène musicale!";
    $l0 = 29;
    $l1 = strlen($text);
    $l2 = iconv_strlen($text);
    $l3 = mb_strlen($text);
    
    deploy_log("[i] Lengths testing: $l0 vs $l1 vs $l2 vs $l3");

    $i = 0;
    $t = 0;

    while (++$i < 100)
    {
        if (rand(1,10) <= 1 || $i == 1 || $i == 100) 
        { 
            sleep(rand(1,2) == 1 ? 1 : 0);
            deploy_log($i, 100, "[+] Testing $i", "Test $i");
        }
        else
        {
            deploy_log($i, 100);
        }        
    }*/

    /*
    $nb_files = 0;

    $roots = array(array($main_src, $main_dst));

    while (count($roots) > 0)
    {
        $dir = array_shift($roots);

        $src = $dir[0];
        $dst = $dir[1];

        foreach (deploy_scan($src) as $name)
        {
            if (is_dir("$src/$name"))
            {
                $roots[] = array("$src/$name", "$dst/$name");
                continue;
            }
            
            ++$nb_files;
        }
    }

    $dependency_graph = [];

    $file_index = 0;

    $roots = array(array($main_src, $main_dst));

    while (count($roots) > 0)
    {
        $dir = array_shift($roots);

        $src = $dir[0];
        $dst = $dir[1];

        foreach (deploy_scan($src) as $name)
        {
            if (is_dir("$src/$name"))
            {
                $roots[] = array("$src/$name", "$dst/$name");
                continue;
            }

            $pathinfo  = pathinfo("$src/$name");
            $extension = array_key_exists("extension", $pathinfo) ? $pathinfo["extension"] : "";
            
            if (deploy_is_compiled("$src/$name", $extension))
            {
                $cwd = getcwd();
                chdir($src);
                {
                    $php_args = "$php_args_common REQUEST_URI=".str_replace("//","/", str_replace($main_src,"/",$src));

                    dom\del("dependency-graph");
    
                    deploy_log("$src/$name");
                    //$json = deploy_exec("php -f $name -- $php_args doctype=dependency-graph static=0");
                    deploy_exec("php -f $name -- $php_args doctype=dependency-graph static=0");
                    //$dependencies = json_decode($json, true);
                    $dependencies = dom\del("dependency-graph");
                    
                    if (is_array($dependencies) && count($dependencies) > 0) 
                    {
                        $dependency_graph[] = [ "file" => "$src/$name", "dependencies" => $dependencies ];
                    }
                }
                chdir($cwd);
            }    
            
            deploy_log($file_index, $nb_files);
            ++$file_index;
        }
    }

    $impact_graph = [];

    foreach ($dependency_graph as $d)
    {
        list($file, $file_dependency_graph) = array_values($d);

        if (is_array($file_dependency_graph))
        {
            $file = array_shift($file_dependency_graph);

            foreach ($file_dependency_graph as $dependency_file)
            {
                if (!dom\at($impact_graph, $dependency_file)) $impact_graph[$dependency_file] = [];
                $impact_graph[$dependency_file][$file] = true;
            }
        }
    }

    $impact_graph_tmp = $impact_graph;
    $impact_graph = [];

    foreach ($impact_graph_tmp as $file => $impacts)
    {
        if (false !== stripos($file, "\\vendor\\")) continue;
        $impact_graph[$file] = $impacts;        
    }

    $stable = false;

    while (!$stable)
    {
        $stable = true;

        foreach ($impact_graph as $file => $impacts)
        {
            $n = count($impact_graph[$file]);

            foreach ($impacts as $impact => $_)
            {
                foreach (dom\at($impact_graph, $impact, []) as $sub_impact => $_)
                {
                    $impact_graph[$file][$sub_impact] = true;
                }
            }

            if ($n != count($impact_graph[$file]))
            {
                $stable = false;
            }
        }
    }

    foreach ($impact_graph as $file => &$impacts)
    {
        $impacts = array_keys($impacts);
    }

    dom\bye($impact_graph);

    deploy_log("[i] Testing OK");

    die;
    */
}

if (!!$cmdline_option_generate)
{
    deploy_log("[i] Generating files...");

    $cwd = getcwd();
    chdir($main_src);
    {
        $php_args = "$php_args_common REQUEST_URI=/";
        deploy_exec("php -f index.php -- $php_args generate=1");
    }    
    chdir($cwd);

    deploy_log("[i] Generating files... OK");
}
else
{
    deploy_log("[i] Auto-Generating files if needed...");

    $cwd = getcwd();
    chdir($main_src);
    {
        $php_args = "$php_args_common REQUEST_URI=/";
        deploy_exec("php -f index.php -- $php_args");
    }    
    chdir($cwd);

    deploy_log("[i] Auto-Generating files if needed... OK");
}

// Pre-parsing to detect any possible optimization based on actual sources
{
    deploy_log("[i] Preparing...");

    $max_path_level = 1;
    {
        $roots = array(array($main_src, $main_dst, 0));

        while (count($roots) > 0)
        {
            list($src, $dst, $lvl) = array_shift($roots);

            $max_path_level = max($max_path_level, $lvl);

            foreach (deploy_scan($src) as $name)
            {
                if (is_dir("$src/$name"))
                {
                    $roots[] = array("$src/$name", "$dst/$name", $lvl + 1);
                }
            }
        }
    }

    deploy_log("[i] Preparing... Path max depth : $max_path_level");
    $php_args_common .= " path_max_depth=$max_path_level";
}

if (!!$cmdline_option_copy)
{
    deploy_log("[i] Mirroring...");

    $nb_files = 0;

    $roots = array(array($main_src, $main_dst));

    while (count($roots) > 0)
    {
        $dir = array_shift($roots);

        $src = $dir[0];
        $dst = $dir[1];

        foreach (deploy_scan($src) as $name)
        {
            if (is_dir("$src/$name"))
            {
                $roots[] = array("$src/$name", "$dst/$name");
            }
            
            ++$nb_files;
        }
    }

    $file_index = 0;

    $roots = array(array($main_src, $main_dst));

    while (count($roots) > 0)
    {
        $dir = array_shift($roots);

        $src = $dir[0];
        $dst = $dir[1];

        foreach (deploy_scan($src) as $name)
        {
            if (is_dir("$src/$name"))
            {
                $roots[] = array("$src/$name", "$dst/$name");

                if (!is_dir("$dst/$name"))
                {
                    $os_path = ($cmdline_option_os == "win") ? str_replace("/","\\","$dst/$name") : "$dst/$name";
                    deploy_log($file_index, $nb_files, "[+] $dst/$name");
                    deploy_exec("mkdir \"$os_path\"");

                    if (is_dir('D:\wamp\www\villepreux.net\json'))      die(print_r($roots, true));

                    if (is_dir('D:\wamp\www\villepreux.net\json'))      die("a.1 JSON folder created! ($src/$name -> $dst/$name / $os_path)");
                    if (is_dir('D:\wamp\www\villepreux.net\$os_path'))  die("a.2 os_path folder created!");
                    if (is_dir('D:\wamp\www\villepreux.net\5'))         die("a.3 5 folder created!");
                }
            }
            else
            {
                $pathinfo  = pathinfo("$src/$name");
                $extension = array_key_exists("extension", $pathinfo) ? $pathinfo["extension"] : "";
                
                if (!deploy_is_compiled("$src/$name", $extension))
                {
                    if (!!$cmdline_option_gemini && !$cmdline_option_gemini_local_bin && !deploy_is_text_file($pathinfo))
                    {   
                        if (is_file("$dst/$name"))
                        {
                            $os_path_dst = ($cmdline_option_os == "win") ? str_replace("/","\\","$dst/$name") : "$dst/$name";

                            deploy_log($file_index, $nb_files, "[-] $dst/$name", "REMOVE");
                            deploy_exec("del" ." \"$os_path_dst\"");
                        }
                    }
                    else
                    {
                        if (!is_file("$dst/$name") || (deploy_content_file("$src/$name") != deploy_content_file("$dst/$name"))) 
                        {
                            $os_path_src = ($cmdline_option_os == "win") ? str_replace("/","\\","$src/$name") : "$src/$name";
                            $os_path_dst = ($cmdline_option_os == "win") ? str_replace("/","\\","$dst/$name") : "$dst/$name";

                            deploy_log($file_index, $nb_files, "[+] $dst/$name",deploy_diff(deploy_content_file("$src/$name"), deploy_content_file("$dst/$name")));

                            if ($cmdline_option_os == "win")  deploy_exec("copy" ." \"$os_path_src\" \"$os_path_dst\"");
                            if ($cmdline_option_os == "unix") deploy_exec("cp"   ." \"$os_path_src\" \"$os_path_dst\"");
                        }
                    }
                }
            }    
            
            deploy_log($file_index, $nb_files);
            ++$file_index;
        }
    }
    
    deploy_log("[i] Mirroring... OK");
}

if (!!$cmdline_option_compile_one)
{
    $exec = array_values(json_decode(base64_decode($cmdline_option_compile_one), true));

    list($cwd, $loc, $cmd, $prev_loc) = $exec;

    deploy_log("[i] Pre-compiling: $cmd");

    if (!!$loc) chdir($loc);
    if (!!$cmd) $html = deploy_exec($cmd);
    if (!!$prev_loc) chdir($prev_loc);

    if (!is_dir("$main_src/.cache")) 
    {
        deploy_log("[i] Pre-compiling: Creating .cache folder $main_src/.cache");

        $cwd = getcwd();
        chdir($main_src);
        deploy_exec("mkdir .cache");
        chdir($cwd);
    }

    if (!is_dir("$main_src/.cache")) 
    {
        deploy_log("[i] Pre-compiling: COULD NOT create .cache folder $main_src/.cache");
        return; die;
    }

    file_put_contents("$main_src/.cache/".md5($cmdline_option_compile_one).".html", $html);

    deploy_log("[i] Pre-compiling: DONE!");
    return; die;
}

if (!!$cmdline_option_compile)
{
    deploy_log("[i] Compiling...");

    $derivatives = !$cmdline_option_gemini ? array("rss", "json", "tile") : array();

    // PASS #0 - Compute amount of files to process. So we can track progression

    deploy_log("[i] Compiling... Compute amount of files to process...");

    $nb_files = 0;
    $roots = array(array($main_src, $main_dst));

    while (count($roots) > 0)
    {
        $dir = array_shift($roots);

        $src = $dir[0];
        $dst = $dir[1];

        foreach (deploy_scan($src) as $name)
        {
            if (is_dir("$src/$name")) { $roots[] = array("$src/$name", "$dst/$name"); continue; }

            $pathinfo  = pathinfo("$src/$name");
            $extension = array_key_exists("extension", $pathinfo) ? $pathinfo["extension"] : "";
            
            if (deploy_is_compiled("$src/$name", $extension))
            {
                $nb_files += 1 + count($derivatives);
            }        
        }
    }

    // PASS #1 - Date optimization / Check dependencies

    $dependencies_debug_checked_file = false;

    if ($cmdline_option_compare_dates)
    {
        deploy_log("[i] Compiling... Check dependencies...");
    
        $dependencies_could_have_been_modified = false;
        
        $roots = array(array($main_src, $main_dst));

        while (count($roots) > 0)
        {
            $dir = array_shift($roots);

            $src = $dir[0];
            $dst = $dir[1];

            foreach (deploy_scan($src) as $name)
            {
                if (is_dir("$src/$name")) { $roots[] = array("$src/$name", "$dst/$name"); continue; }

                $pathinfo  = pathinfo("$src/$name");
                $extension = array_key_exists("extension", $pathinfo) ? $pathinfo["extension"] : "";
                
                if (deploy_is_compiled("$src/$name", $extension))
                {
                    if ($cmdline_option_compare_dates && !$dependencies_could_have_been_modified)
                    {
                        $deploy_name = str_replace(".php", ".$target_ext", $name);  
                        
                        $t_from = filemtime("$src/$name");
                        $t_to   = is_file("$dst/$deploy_name") ? filemtime("$dst/$deploy_name") : 0;
                        
                        if ($t_to < $t_from) 
                        {
                            /* On check des fichiers qu'on considère dépendances probables de tout le monde
                             *  - tous les fichiers non index.php
                             *  - tous les fichiers racine
                             */
                            if ($name != "index.php" || in_array($src, $root_sources))
                            {   
                                deploy_log($file_index, $nb_files, "[i] $deploy_name ".date ("Y-m-d H:i:s.", $t_to)." < $src/$name ".date ("Y-m-d H:i:s.", $t_from)."");

                                $dependencies_debug_checked_file = "$src/$name";

                                //file_put_contents("__DIFF_A.txt", deploy_content_file("http://localhost/$domain_src/$src/$name"));
                                //file_put_contents("__DIFF_B.txt", deploy_content_file("$dst/$name"));

                                $dependencies_could_have_been_modified = true;
                                break;
                            }
                        }
                    }

                    $file_index += 1 + count($derivatives);                
                }        
            }
        }

        if ($dependencies_could_have_been_modified)
        {
            deploy_log($nb_files, $nb_files, "[i] Core dependencies could have been modified. Disable date optimization.");
        }
    }

    // PASS #2 - Run all php commands / idealy in parallel
    
    deploy_log("[i] Compiling... Pre-Process files...");
    
    /*
    $execs = [];*/

    $file_index = 0;
    $roots = array(array($main_src, $main_dst));

    while (count($roots) > 0)
    {
        $dir = array_shift($roots);

        $src = $dir[0];
        $dst = $dir[1];

        foreach (deploy_scan($src) as $name)
        {
            if (is_dir("$src/$name")) { $roots[] = array("$src/$name", "$dst/$name"); continue; }

            $pathinfo  = pathinfo("$src/$name");
            $extension = array_key_exists("extension", $pathinfo) ? $pathinfo["extension"] : "";
            
            if (deploy_is_compiled("$src/$name", $extension))
            {
                $deploy_name = str_replace(".php", ".$target_ext", $name);  

                if (!!$cmdline_option_verbose)
                {
                    //deploy_log($file_index, $nb_files, "[i] $dst/$deploy_name", "");
                }

                if ($cmdline_option_compare_dates && !$dependencies_could_have_been_modified)
                {
                    $t_from = filemtime("$src/$name");
                    $t_to   = is_file("$dst/$deploy_name") ? filemtime("$dst/$deploy_name") : 0;
                    
                    if ($t_to >= $t_from) 
                    {
                        //deploy_log($file_index, $nb_files);
                        ++$file_index;
                        continue;
                    }
                }
    
                $php_args = "$php_args_common REQUEST_URI=".str_replace("//","/", str_replace($main_src,"/",$src));

                if (!!$cmdline_option_verbose)
                {
                    $deploy_name = str_replace(".php", ".$target_ext", $name);            
                  //deploy_log($file_index, $nb_files, "[i] $dst/$deploy_name", "$file_index / $nb_files: $name -> COMPILE");
                }
                
                $html = false;
                $derivative_outputs = array();/*

                if ($name == "index.php")
                {
                    $execs[] = [ getcwd(), $src, "php -f $name -- $php_args", getcwd() ];

                    foreach ($derivatives as $type)
                    {
                        $type_arg = "rss=$type";
                        $execs[] = [ getcwd(), $src, "php -f $name -- $php_args $type_arg rss_date_granularity_daily=1 rss_date_granularity_file=1", getcwd() ];
                    }
                }
                else
                {
                    $execs[] = [ getcwd(), false, "php -f $src/$name -- $php_args", false ];
                }*/
            }        
        }
    }
    /*
    function async_exec($cmd) 
    {
        if (DIRECTORY_SEPARATOR === '\\')
      //if (substr(php_uname(), 0, 7) == "Windows")
        {
          //pclose(popen("start /b $cmd >nul", "r")); 
            popen("start /b $cmd >nul", "r"); 
            //deploy_exec($cmd);
        }
        else 
        {
            @exec($cmd . " > /dev/null &");  
        }
    }*/

    $sync_async = /*$cmdline_option_mt ? "ASync" : */"Sync";

    if (is_dir("$main_src/.cache")) 
    {   
        deploy_log("[i] $sync_async compile: Wipe .cache folder $main_src/.cache");
    
        $cwd = getcwd();
        chdir($main_src);
        deploy_exec("rmdir /s /q .cache");
        chdir($cwd);    
    }
    /*
    if ($cmdline_option_mt)
    {    
        deploy_log("[i] $sync_async compile: Creating .cache folder $main_src/.cache");

        $cwd = getcwd();
        chdir($main_src);
        deploy_exec("mkdir .cache");
        chdir($cwd);

        foreach ($execs as $exec)
        {
            list($cwd, $loc, $cmd, $prev_loc) = $exec;
            $exec_base64 = base64_encode(json_encode($exec));

            deploy_log("[i] $sync_async compile: ".(!!$loc ? $loc : $cwd)."> $cmd");

            if ($cmdline_option_mt)
            {
                async_exec("php static.php --compile-one=$exec_base64");
            }
            else
            {
                $html = "";
                if (!!$loc) chdir($loc);
                if (!!$cmd) $html = deploy_exec($cmd);
                if (!!$prev_loc) chdir($prev_loc);
                $cache_filename = "$main_src/.cache/".md5($exec_base64).".html";
                file_put_contents($cache_filename, $html);
            }
        }

        deploy_log("[i] $sync_async compile: Wait for all processes to be completed");

        $compiled = 0;
        $nb_compilations = count($execs);
        
        while ($compiled < $nb_compilations)
        {
            $new_compiled = 0;
            $current_cmd  = "";

            foreach ($execs as $exec)
            {
                list($cwd, $loc, $cmd, $prev_loc) = $exec;
                $exec_base64 = base64_encode(json_encode($exec));
                $cache_filename = "$main_src/.cache/".md5($exec_base64).".html";

                if (is_file($cache_filename)) ++$new_compiled;
                else $current_cmd = "".(!!$loc ? $loc : $cwd)."> $cmd";
            }

            if ($new_compiled > $compiled)
            {
                $compiled = $new_compiled;
                deploy_log($compiled, $nb_compilations, "[i] $sync_async compile: $current_cmd");
            }
        }

        sleep(1);
        deploy_log("[i] $sync_async compile: DONE!");
    }
    */
    // PASS #3 - Do all our logic

    deploy_log("[i] Compiling... Process files...");
    
    $file_index = 0;
    $roots = array(array($main_src, $main_dst));

    while (count($roots) > 0)
    {
        $dir = array_shift($roots);

        $src = $dir[0];
        $dst = $dir[1];

        foreach (deploy_scan($src) as $name)
        {
            if (is_dir("$src/$name")) { $roots[] = array("$src/$name", "$dst/$name"); continue; }

            $pathinfo  = pathinfo("$src/$name");
            $extension = array_key_exists("extension", $pathinfo) ? $pathinfo["extension"] : "";
            
            if (deploy_is_compiled("$src/$name", $extension))
            {
                $deploy_name = str_replace(".php", ".$target_ext", $name);  
    
                if (!!$cmdline_option_verbose)
                {
                    deploy_log($file_index, $nb_files, "[i] $dst/$deploy_name", "");
                }

                if ($cmdline_option_compare_dates && !$dependencies_could_have_been_modified)
                {
                    $t_from = filemtime("$src/$name");
                    $t_to   = is_file("$dst/$deploy_name") ? filemtime("$dst/$deploy_name") : 0;
                    
                    if ($t_to >= $t_from) 
                    {
                        /*if (!!$cmdline_option_verbose)
                        {
                            deploy_log($file_index, $nb_files, "[i] $dst/$deploy_name", "OLD");
                        }
                        else*/
                        {
                            deploy_log($file_index, $nb_files);
                        }

                        $file_index += 1 + count($derivatives);
                        continue;
                    }

                    if (!!$cmdline_option_verbose)
                    {
                        deploy_log($file_index, $nb_files, "[i] $dst/$deploy_name", "NEW ($t_to < $t_from)");
                    }
                }
                
                $php_args = "$php_args_common REQUEST_URI=".str_replace("//","/", str_replace($main_src,"/",$src));

                /*if (!!$cmdline_option_verbose)
                {
                    $deploy_name = str_replace(".php", ".$target_ext", $name);
                    deploy_log($file_index, $nb_files, "[i] $dst/$deploy_name", "$file_index / $nb_files: $name -> COMPILE");
                }*/
                
                $html = false;
                $derivative_outputs = array();

                if ("$src/$name" == $dependencies_debug_checked_file) {
                    deploy_log($file_index, $nb_files, "[i] $dependencies_debug_checked_file FOUND!");
                }

                if ($name == "index.php")
                {
                    // Assumes index.php are implicitely included from their directory
                    /*
                    if ($cmdline_option_mt)
                    {
                        $html = @file_get_contents("$main_src/.cache/".md5(base64_encode(json_encode([ getcwd(), $src, "php -f $name -- $php_args", getcwd() ]))).".html");
                    }*/

                    if (false === $html)
                    {   
                        /*if (!!$cmdline_option_verbose)
                        {
                            deploy_log($file_index, $nb_files, "[i] $dst/$name", "$file_index / $nb_files: $name -> COMPILE");
                        }*/
        
                        $cwd = getcwd();
                        chdir($src);
                        {
                            $html = "";
                            /*
                            if (!!$cmdline_option_test)
                            {
                                $getdata = []; foreach (explode(" ", $php_args) as $arg) { list($var,$val) = explode("=", $arg); $getdata[$var] = $val; } $getdata = http_build_query($getdata);
                                $html = file_get_contents("http://localhost/villepreux.net/$src/$name?$getdata");
                            }
                            else*/
                            {
                                $html = deploy_exec("php -f $name -- $php_args");
                            }
    
                            deploy_compile_error_check($html, "$src/$name");
                        }
                        chdir($cwd);
                    }

                    $has_rss_content = (false !== stripos($html, "application/rss+xml"));

                    if ($has_rss_content) 
                    {
                        foreach ($derivatives as $type)
                        {
                            if (!!$cmdline_option_verbose)
                            {
                                $deploy_name = str_replace(".php", ".$target_ext", $name);
                                deploy_log($file_index, $nb_files, "[i] $dst/$deploy_name -> $type", "$file_index / $nb_files: $name -> $type COMPILE");
                            }
                
                            $type_arg = "rss=$type";

                            $derivative_outputs[$type] = false;
                            /*
                            if ($cmdline_option_mt)
                            {
                                $derivative_outputs[$type] = @file_get_contents("$main_src/.cache/".md5(base64_encode(json_encode([ getcwd(), $src, "php -f $name -- $php_args $type_arg rss_date_granularity_daily=1 rss_date_granularity_file=1", getcwd() ]))).".html");
                            }*/

                            if (false === $derivative_outputs[$type])
                            {
                                $cwd = getcwd();
                                chdir($src);
                                {
                                    $derivative_outputs[$type] = "";/*

                                    if (!!$cmdline_option_test)
                                    {
                                        $getdata = []; foreach (explode(" ", "$php_args $type_arg rss_date_granularity_daily=1 rss_date_granularity_file=1") as $arg) { list($var,$val) = explode("=", $arg); $getdata[$var] = $val; } $getdata = http_build_query($getdata);
                                        $derivative_outputs[$type] = file_get_contents("http://localhost/villepreux.net/$src/$name?$getdata");
                                    }
                                    else*/
                                    {
                                        $derivative_outputs[$type] = deploy_exec("php -f $name -- $php_args $type_arg rss_date_granularity_daily=1 rss_date_granularity_file=1");
                                    }

                                    deploy_compile_error_check($derivative_outputs[$type], "$src/$name $type");
                                }
                                chdir($cwd);
                            }

                            if (false === $derivative_outputs[$type])
                            {
                                unset($derivative_outputs[$type]);
                            }
                        }
                    }
                }
                else
                {
                    /*if (!!$cmdline_option_verbose)
                    {
                        deploy_log($file_index, $nb_files, "[i] $dst/$name", "$file_index / $nb_files: $name -> COMPILE");
                    }*/
    
                    // Assumes other php files have to be able to be included from anywhere
                    /*                     
                    if ($cmdline_option_mt)
                    {
                        $html = @file_get_contents("$main_src/.cache/".md5(base64_encode(json_encode([ getcwd(), false, "php -f $src/$name -- $php_args", false ]))).".html");
                    }*/

                    if (false === $html)
                    {
                        $html = "";/*

                        if (!!$cmdline_option_test)
                        {
                            $getdata = []; foreach (explode(" ", $php_args) as $arg) { list($var,$val) = explode("=", $arg); $getdata[$var] = $val; } $getdata = http_build_query($getdata);
                            $html = file_get_contents("http://localhost/villepreux.net/$src/$name?$getdata");
                        }
                        else */
                        {
                            if ("$src/$name" == $dependencies_debug_checked_file) {
                                deploy_log($file_index, $nb_files, "[i] $dependencies_debug_checked_file FOUND! php -f $src/$name -- $php_args");
                            }
        
                            $html = deploy_exec("php -f $src/$name -- $php_args");
                            
                            if ("$src/$name" == $dependencies_debug_checked_file) {
                                deploy_log($file_index, $nb_files, "---");
                                echo $html;
                                deploy_log($file_index, $nb_files, "---");
                            }        
                        }
                    }

                    if (!$html || $html == "")
                    {
                             if ($extension == "php") { $html = '<!-- '  . 'empty' . ' //-->'; }
                        else if ($extension == "js" ) { $html = '/'.'* ' . 'empty' . ' *'.'/'; }
                        else if ($extension == "css") { $html = '/'.'* ' . 'empty' . ' *'.'/'; }
                        else                          { $html = '/'.'* ' . 'empty' . ' *'.'/'; }
                    }
                    
                    deploy_compile_error_check($html, "$src/$name html");
                }

                if (false !== $html)
                {
                    $deploy_name = str_replace(".php", ".$target_ext", $name);

                    $md5_prev = -1;

                    if (is_file("$dst/$deploy_name"))
                    {
                        $md5_prev = deploy_content_file("$dst/$deploy_name");
                    }

                    if ($md5_prev != deploy_content($html))
                    {   /*
                        if (false !== stripos($dst, "/a11y/"))
                        {
                            deploy_log($file_index, $nb_files, "[i] ----------------- ");
                            echo $md5_prev;
                            file_put_contents("__DIFF_A.txt", $md5_prev);
                            deploy_log($file_index, $nb_files, "[i] ----------------- ");
                            echo $html;
                            file_put_contents("__DIFF_B.txt", $html);
                            deploy_log($file_index, $nb_files, "[i] ----------------- ");
                            die;
                        }*/

                        deploy_log($file_index, $nb_files, "[C] $dst/$deploy_name", deploy_diff(deploy_content($html), $md5_prev));
    
                        file_put_contents("$dst/$deploy_name", $html);
                    }
                    else
                    {
                        if ("$src/$name" == $dependencies_debug_checked_file) {
                            deploy_log($file_index, $nb_files, "[i] $dependencies_debug_checked_file: TOUCH $dst/$deploy_name!");
                        }
                        touch("$dst/$deploy_name");
                    }
                }
                else
                {
                    if ("$src/$name" == $dependencies_debug_checked_file) {
                        deploy_log($file_index, $nb_files, "[i] $dependencies_debug_checked_file: !html");
                    }
                }

                deploy_log($file_index, $nb_files);
                ++$file_index;
                
                foreach ($derivatives as $type)
                {
                    $output = array_key_exists($type, $derivative_outputs) ? $derivative_outputs[$type] : false;

                    if (!!$cmdline_option_verbose)
                    {
                        $deploy_name = str_replace(".php", ".$target_ext", $name);            
                        deploy_log($file_index, $nb_files, "[i] $dst/$deploy_name -> $type", "$file_index / $nb_files: $name ".(false === $output ? "N/A" : "-> STRUCT $type"));
                    }
                    else
                    {    
                        deploy_log($file_index, $nb_files);
                    }

                    ++$file_index;
                    
                    if (!$output) continue;

                    // Derivative folder

                    if (!is_dir("$dst/$type"))
                    {
                        $os_path = ($cmdline_option_os == "win") ? str_replace("/","\\","$dst/$type") : "$dst/$type";
                        deploy_log($file_index, $nb_files, "[+] $dst/$type");
                        deploy_exec("mkdir \"$os_path\"");
                    }

                    $deploy_name =  ($type == "json") ? "rss.json"  : (
                                    ($type == "tile") ? "tile.xml"  : (                                                                      
                                                        "rss.xml"     ));

                    // Redirection file

                    $html_redirect = dom\html_refresh_page("../$deploy_name");

                    $md5_prev = -1;

                    if (is_file("$dst/$type/index.$target_ext"))
                    {
                        $md5_prev = deploy_content_file("$dst/$type/index.$target_ext");
                    }

                    if ($md5_prev != deploy_content($html_redirect))
                    {
                        deploy_log($file_index, $nb_files, "[C] $dst/$type/index.$target_ext", deploy_diff(deploy_content($html_redirect), $md5_prev));
                        file_put_contents("$dst/$type/index.$target_ext", $html_redirect);
                    } 

                    // Derivative file
                    
                    $md5_prev = -1;

                    if (is_file("$dst/$deploy_name"))
                    {
                        $md5_prev = deploy_content_file("$dst/$deploy_name");
                    }

                    if ($md5_prev != deploy_content($output))
                    {
                        deploy_log($file_index, $nb_files, "[C] $dst/$deploy_name", deploy_diff(deploy_content($output), $md5_prev));
                        file_put_contents("$dst/$deploy_name", $output);
                    }  
                }
            }        
        }
    }

    // DONE
    
    deploy_log($nb_files, $nb_files);
    deploy_log("[i] Compiling... OK");
}

if (!!$cmdline_option_generate)
{
    deploy_log("[i] generating files...");

    $nb_files = 0;

    $roots = array(array($main_src, $main_dst));

    while (count($roots) > 0)
    {
        $dir = array_shift($roots);

        $src = $dir[0];
        $dst = $dir[1];

        foreach (deploy_scan($src) as $name)
        {
            if (is_dir("$src/$name")) { $roots[] = array("$src/$name", "$dst/$name"); continue; }

            ++$nb_files;
        }
    }

    $file_index = 0;

    $roots = array(array($main_src, $main_dst));

    while (count($roots) > 0)
    {
        $dir = array_shift($roots);

        $src = $dir[0];
        $dst = $dir[1];

        foreach (deploy_scan($src) as $name)
        {
            if (is_dir("$src/$name")) { $roots[] = array("$src/$name", "$dst/$name"); continue; }

            if (false !== stripos($name, "portfolio")) continue;

            if ($name == "index.php" && in_array($src, $root_sources))
            {
                $cwd = getcwd();
                chdir($src);
                {
                    $path_src_to_dst = rtrim(str_replace("//", "/", (substr_count($src, "/") <= 0 ? "" : str_repeat("../", substr_count($src, "/") - 1))."$dst/"), "/");

                    //die(PHP_EOL.PHP_EOL."src=$src / dst=$dst / path_src_to_dst=$path_src_to_dst");
                    
                    $dst_files_before = array();

                    $parse_dir = $path_src_to_dst;

                    foreach (deploy_subfiles($parse_dir) as $parsed_name)
                    {
                        $dst_files_before[] = array($parsed_name, deploy_content_file("$parse_dir/$parsed_name"));
                    }
                    
                    $php_args = "$php_args_common REQUEST_URI=".str_replace("//","/",str_replace($main_dst,"/",$dst))." generate=1 generate_dst=$path_src_to_dst";

                    deploy_exec("php -f $name -- $php_args", /*false*/true); 

                    //deploy_compile_error_check($html, "$src/$name html");

                    $dst_files_after = array();
                    
                    foreach (deploy_subfiles($parse_dir) as $parsed_name)
                    {
                        $dst_files_after[] = array($parsed_name, deploy_content_file("$parse_dir/$parsed_name"));
                    }

                    usort($dst_files_before, "deploy_compare_first_value");
                    usort($dst_files_after,  "deploy_compare_first_value");

                    $i = 0;

                    for ($j = 0; $j < count($dst_files_after); ++$j)
                    {
                        if ($i >= count($dst_files_before) || $dst_files_after[$j][0] != $dst_files_before[$i][0])
                        {
                            deploy_log($file_index, $nb_files, "[G] $dst/".$dst_files_after[$j][0], deploy_diff($dst_files_after[$i][0], $dst_files_before[$j][0]));
                            continue;
                        }
                        
                        if ($dst_files_after[$j][1] != $dst_files_before[$i][1])
                        {
                            deploy_log($file_index, $nb_files, "[U] $dst/".$dst_files_after[$j][0], deploy_diff($dst_files_after[$i][1], $dst_files_before[$j][1]));
                        }

                        ++$i;
                    }
                }
                chdir($cwd);
            }  
            
            deploy_log($file_index, $nb_files);
            ++$file_index;
        }
    }

    deploy_log("[i] generating files...OK");
}

if (!!$cmdline_option_generate || !!$cmdline_option_generate_index_files)
{
    deploy_log("[i] generating index files...");

    $nb_dirs = 0;

    $roots = array($main_dst);
    $dirs  = array();

    while (count($roots) > 0)
    {
        $dst = array_shift($roots);
        ++$nb_dirs;

        foreach (deploy_scan($dst, true) as $name) 
        {
            if (is_dir("$dst/$name")) $roots[] = "$dst/$name"; 
        }
    }

    $dir_index = 0;

    $roots = array($main_dst);

    while (count($roots) > 0)
    {
        $dst = array_shift($roots);

        if (!is_file("$dst/index.$target_ext"))
        {
            deploy_log("[+] Generate $dst/index.$target_ext");
            file_put_contents("$dst/index.$target_ext", "");
        }  

        deploy_log($dir_index, $nb_dirs);
        ++$dir_index;

        foreach (deploy_scan($dst, true) as $name)
        {
            if (is_dir("$dst/$name")) $roots[] = "$dst/$name";
        }
    }

    deploy_log("[i] generating index files...OK");
}

if (!!$cmdline_option_lunr)
{
    deploy_log("[i] generating files - LUNR index...");

    $src = $main_src;
    $dst = $main_dst;

    $cwd = getcwd();
    chdir($src);
    {
        $php_args = "$php_args_common REQUEST_URI=".str_replace("//","/",str_replace($main_dst,"/",$dst));

        $json = deploy_exec("php -f ./index.php -- $php_args lunr=doc", false);
        file_put_contents("$src/lunr-doc.json", $json);
        deploy_log("[+] $src/lunr-doc.json");
    }
    chdir($cwd);

    deploy_log("[i] generating files - LUNR index... OK");
}

if (!!$cmdline_option_blogroll)
{
    deploy_log("[i] generating files - blogroll opml...");

    $src = $main_src;
    $dst = $main_dst;

    $cwd = getcwd();
    chdir($src);
    {
        $php_args = "$php_args_common REQUEST_URI=".str_replace("//","/",str_replace($main_dst,"/",$dst));

        $xml = deploy_exec("php -f ./blogroll/opml/index.php -- $php_args static=0", false);
        file_put_contents("$src/blogroll.opml", $xml);
        deploy_log("[+] $src/blogroll.opml");
    }
    chdir($cwd);

    deploy_log("[i] generating files - blogroll opml... OK");
}

if (!!$cmdline_option_netlify)
{
    deploy_log("[i] Deploying website with Netlify");

    $cwd = getcwd();
    chdir($main_dst);

    deploy_exec("Set-ExecutionPolicy -ExecutionPolicy Bypass", false, true);
    
    //deploy_log("[i] Show netlify CLI available parameters");
    //deploy_exec("netlify help", false, true);

    deploy_log("[i] Disable netlify telemetry");
  //deploy_exec("netlify --telemetry-disable --dir=.", true, true);
    deploy_exec("netlify --telemetry-disable", true, true);

    // TODO: Add github secrets support
    deploy_log("[i] Push to netlify production env");
    deploy_exec("netlify deploy --dir=. --prod --open --site=5a21869e-2608-47a8-8399-2645d687b675", true, true);

    chdir($cwd);

    deploy_log("[i] WEBSITE IS LIVE!");
}

deploy_log("[i] DONE!");
