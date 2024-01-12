<?php

require_once(__DIR__."/../../dom_html.php"); // DOM html markup

use function dom\{set,get,pre,style}; // Import what I need here

set("fonts", get("fonts")."|Fira Code");
    
function code($code) // Custom component
{
    $syntax_highlight = true;

    if ($syntax_highlight)
    {
        // Workaround crappy native php syntax hightlight function ------>
        $functions = array("default", "html", "keyword", "string", "comment");
        foreach ($functions as $value) ini_set("highlight.$value", "ide-highlight-$value;");
        $code = highlight_string($code, true);
        foreach ($functions as $value) $code = preg_replace("/style=\"color: highlight-$value;\"/", "class=\"ide-highlight-$value\"", $code);
        $code = str_replace('style="color: ', 'class="', $code);
        $code = str_replace(';"', '"', $code);
        $code = str_replace(array(PHP_EOL, "\n", "\r", "\r", "\r\n", "\n\r"), "", $code);
        //$code = str_replace(array("&nbsp;"), "", $code);
        $code = str_replace(array("<br />", "<br/>", "<br>"), PHP_EOL, $code);
        $code = str_replace(array("<code>", "</code>", '<span class="ide-highlight-html">'), "", $code);
        $code = substr($code, 0, strripos($code, "</span>"));
        // Workaround crappy native php syntax hightlight function ------>
    }

    $i = 0;

    return 

        pre(implode(PHP_EOL, array_map(function ($line) use (&$i, $syntax_highlight) { 
                
                return  '<div class="ide-line">'.
                
                            '<div class="ide-line-number">'.str_pad(++$i, 3, "0", STR_PAD_LEFT).'</div>'.
                            '<div class="ide-line-code">'.($syntax_highlight ? $line : htmlentities($line)).'</div>'.
                            
                        '</div>';
            
            }, 

            explode(PHP_EOL, $code)
                
            )), "ide").
        
        style("

            @layer ide;
        
            .ide {
                width:          fit-content;
                border:         1px dashed var(--border-color);
                padding:        var( --gap);
                font-size:      .66em;
                line-height:    1.3em;
            }
            .ide-line { 
                display:        inline-flex; 
                align-items:    flex-start;
                gap:            .5em; 
                font-family:    'Fira Code', monospace; 
                font-size:      smaller
            }
            .ide-line-number {
                white-space:    nowrap;
                flex-shrink:    0;
                color:          var(--theme-color, var(--text-on-background-lighter-color));
                pointer-events: none;
                user-select:    none;
            }

            .ide-highlight-html     { color: #000000 }
            .ide-highlight-default  { color: #0000bb }
            .ide-highlight-keyword  { color: #007700 }
            .ide-highlight-string   { color: #dd0000 }
            .ide-highlight-comment  { color: #ff8000 }
            
            @media (prefers-color-scheme: dark) {
                    
                .ide-highlight-html     { color: #FFFFFF }
                .ide-highlight-default  { color: #6ACDFF }
                .ide-highlight-keyword  { color: #84FF84 }
                .ide-highlight-string   { color: #FFAE85 }
                .ide-highlight-comment  { color: #BDBDBD }
                
            }

            ");
}

?>