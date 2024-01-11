<?php

require_once(__DIR__."/../../dom_html.php"); // DOM html markup
require_once(__DIR__."/../../dom_toolbar.php"); // DOM toolbar plugin

use function dom\{set,get,pre,style}; // Import what I need here

set("fonts", get("fonts")."|Fira Code");
    
function code($code) // Custom component
{
    $i = 0;

    return 

        pre(implode(PHP_EOL, array_map(function ($line) use (&$i) { 
                
                return  '<div class="ide-line">'.
                
                            '<span class="ide-line-number">'.str_pad(++$i, 3, "0", STR_PAD_LEFT).'</span>'.
                            '<span class="ide-line-code">'.htmlentities($line).'</span>'.
                            
                        '</div>';
            
            }, 

            explode(PHP_EOL, $code)
                
            )), "ide").
        
        style("

            @layer ide;
        
            .ide {
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
            
            ");
}

?>