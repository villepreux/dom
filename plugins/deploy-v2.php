<?php

namespace build;

require_once(__DIR__."/../dom.php");

function arg_state($flag, $on = 1, $off = 0) { global $argv; return (DOM_CLI ? in_array("--$flag",  $argv) : (array_key_exists($flag, $_GET) && !!$_GET[$flag] )) ? $on : $off; }
function arg_array($flag)                    { global $argv; $values = array(); if (DOM_CLI) { foreach ($argv as $arg) { $tag = "--$flag="; $pos = stripos($arg, $tag); if (false === $pos) continue; $val = substr($arg, $pos + strlen($tag)); $values = array_merge($values, explode(",", $val)); } } else { $values = array_key_exists($flag, $_GET) ? explode(",", $_GET[$flag]) : array(); } return $values; }
function arg_value($flag, $fallback)         { global $argv; $values = arg_array($flag); if (0 == count($values)) return $fallback; return $values[0]; }

function log($text)
{
    echo $text.PHP_EOL;
}

function exec($cmd, $die_on_error = true, $output = false)
{
    $result  = false;
    $outputs = array();

    \exec($cmd, $outputs, $result);

    if ($result != 0)
    {
        log("");
        log("CWD: ".getcwd());
        log("CMD: $cmd");
        log("RES: $result");
        log("");
        foreach ($outputs as $output) log($output);
        log("");
        if ($die_on_error) die();
    }
    else if ($output)
    {
        foreach ($outputs as $output) log($output);
    }

    return implode(PHP_EOL, $outputs);
}

function die_on_compile_error($html, $src)
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
                if (strlen($html) > 200*80) 
                {
                    $html = substr($html, 0, 200*80/2).PHP_EOL.PHP_EOL."[...]".PHP_EOL.PHP_EOL.substr($html, strlen($html) - (200*80/2));
                }

                $html_lines = explode(PHP_EOL, $html);

                log("");
                log("-----------------------");
                log("Error while compiling $src");
                log("-----------------------");
                foreach ($html_lines as $html) log($html);
                log("-----------------------");
                log("");
            }
            
            die();
        }
    }
}

function parse($path, $name = null, $depth = 0, $something_changed = false)
{
    log($path); 

    global $main_src, $main_dst;

    $name = null === $name ? $name : $name;
    $excluded_dirs = [ "static", "gemini", "netlify", "netflix", "node_modules", "vendor", "openmoji", "dom" ];

    $index_php = false;
    $files = [];
    $dirs  = [];
    {
        $items = [];
        {
            foreach (scandir("$main_src/$path") as $item)
            {
                if ($item[0] == ".") continue;
                if (in_array($item, $excluded_dirs)) continue;
                    
                $items[] = $item;
            }
        }
    
        foreach ($items as $item)
        {
            if (!is_dir("$path/$item")) continue;
            $dirs[] = $item;
        }

        foreach ($items as $item)
        {
            if (is_dir("$path/$item")) continue;

            if ($item == "index.php")
            {
                $index_php = $item;
            }
            else if (substr($item, strripos($item, ".")) != ".php")
            {
                $content = file_get_contents("$path/$item");

                if (!!$content)
                {
                    if (false === stripos($content, "<?php")
                    &&  false === stripos($content, "<?="))
                    {
                        $files[] = $item;
                    }
                }
            }
        }
    }

    if ($index_php !== false || count($files) > 0 || count($dirs) > 0)
    {
        if (!is_dir("$main_dst/$path"))
        {
            mkdir("$main_dst/$path");

            $something_changed = true;

            if (!is_dir("$main_dst/$path"))
            {       
                log("COULD NOT CREATE FOLDER $main_dst/$path !"); 
                die;
            }
        }
    }

    foreach ($dirs as $dir)
    {
        global $cmdline_option_include, $cmdline_option_exclude;
        if (count($cmdline_option_include) > 0 && !in_array($dir, $cmdline_option_include)) continue;
        if (count($cmdline_option_exclude) > 0 &&  in_array($dir, $cmdline_option_exclude)) continue;

        $something_changed_under = parse("$path/$dir", $dir, $depth + 1, $something_changed);
        $something_changed = $something_changed || $something_changed_under;
    }

    foreach ($files as $file)
    {
        if (is_file("$main_dst/$path/$file"))
        {
            if (filemtime("$main_src/$path/$file") >= filemtime("$main_dst/$path/$file"))
            {
                unlink("$main_dst/$path/$file");
                
                log("$path/$file");

                copy("$main_src/$path/$file", "$main_dst/$path/$file");

                $something_changed = true;
        
                if (!is_file("$main_dst/$path/$file"))
                {       
                    log("COULD NOT COPY FILE $main_dst/$path/$file !"); 
                    die;
                }
            }
        }
        else
        {
            copy("$main_src/$path/$file", "$main_dst/$path/$file");
        }
    }

    if (!!$index_php)
    {
        $index_html = str_replace(".php", ".html", $index_php);

        if ($something_changed 
        || !is_file("$main_dst/$path/$index_html") 
        || (filemtime("$main_src/$path/$index_php") >= filemtime("$main_dst/$path/$index_html")))
        {
            log("$path/$index_html");

            global $cmdline_option_generate;

            if ($cmdline_option_generate)
            {         
                $cwd = getcwd();
                chdir("$main_src/$path");
                {
                    global $php_args_common;
                    $html = exec("php -f $index_php -- $php_args_common generate=1 REQUEST_URI=".str_replace("//", "/", str_replace($main_src, "/", $path)));
                }
                chdir($cwd);

                die_on_compile_error($html, "$main_src/$path/$index_php");
            }
            else
            {            
                $cwd = getcwd();
                chdir("$main_src/$path");
                {
                    global $php_args_common;
                    $html = exec("php -f $index_php -- $php_args_common REQUEST_URI=".str_replace("//", "/", str_replace($main_src, "/", $path)));
                }
                chdir($cwd);

                die_on_compile_error($html, "$main_src/$path/$index_php");

                file_put_contents("$main_dst/$path/$index_html", $html);

                $something_changed = true;

                if (!is_file("$main_dst/$path/$index_html"))
                {       
                    log("COULD NOT WRITE FILE $main_dst/$path/$index_html !"); 
                    die;
                }
            }
        }
    }

    return $something_changed;
}

@set_time_limit(24*60*60);
@ini_set('memory_limit', '-1');

#region cmd-line

$cmdline_option_compare_dates           = arg_state("compare-dates");
$cmdline_option_gemini                  = arg_state("gemini");
$cmdline_option_gemini_local_bin        = arg_state("gemini-local-bin");
$cmdline_option_static                  = 1;
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
$cmdline_option_compile                 = arg_state("compile");
$cmdline_option_mt                      = arg_state("mt");
$cmdline_option_compile_one             = arg_value("compile-one", false);
$cmdline_option_generate                = arg_state("generate");
$cmdline_option_generate_index_files    = arg_state("generate-index-files");
$cmdline_option_netlify                 = arg_state("netlify");
$cmdline_option_spa                     = arg_state("spa");
$cmdline_option_lunr                    = arg_state("lunr");
$cmdline_option_blogroll                = arg_state("blogroll");
$cmdline_option_minify                  = arg_state("minify", 1, !arg_state("beautify"));
$cmdline_option_include                 = arg_array("include");
$cmdline_option_exclude                 = array_merge(arg_array("exclude"), array("netlify", "static", "gemini"));
$domain_src                             = arg_value("domain-src",       substr(trim(trim(getcwd()), "/\\"), max(strripos(trim(trim(getcwd()), "/\\"), "/"), strripos(trim(trim(getcwd()), "/\\"), "\\")) + 1));
$domain_dst                             = arg_value("domain-dst",      "$domain_src/$cmdline_option_output");
$main_src                               = arg_value("main-src",     "../$domain_src");
$main_dst                               = arg_value("main-dst",     "../$domain_dst");
$server_name                            = arg_value("server-name",      $domain_dst);
$server_http_host                       = arg_value("server-http-host", $domain_dst);
$cmdline_option_rebuild                 = arg_state("rebuild");

#endregion cmd-line
#region php compile common args

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
    " "."live_domain"                   ."=".   "$server_name".

    "";

if (!!$cmdline_option_debug)                $php_args_common .= " debug=1";
if (!!$cmdline_option_profiling)            $php_args_common .= " profiling=1";
if (!!$cmdline_option_fast)                 $php_args_common .= " fast=1";
if (!!$cmdline_option_output
&&    $cmdline_option_output != "static")   $php_args_common .= " $cmdline_option_output=1";

#endregion php compile common args

parse(".", null, 0, $cmdline_option_rebuild);
