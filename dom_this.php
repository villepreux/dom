<?php require_once(__DIR__."/dom_html.php");

use function dom\{set,get,eol,card,card_title,card_text,header,div,span,pre,style,p,debug_track_timing,nbsp,comment};
use const dom\auto;

function code_sanitize($code)
{
    $lines = [];

    foreach (explode(PHP_EOL, $code) as $line)
    {
        if (0     === stripos(trim($line),                       "//"            )) continue;
        if (false !== stripos(str_replace(["\t",""], "", $line), "/"."/!PRIVATE" )) continue;

        $lines[] = $line;
    }

    return implode(PHP_EOL, $lines); 
}

function code_transform_indent($code, $tab_src_size = 4, $tab_dst_size = 2, $tab_space = "&nbsp;")
{
    $lines = explode(PHP_EOL, $code);

    for ($l = 0; $l < count($lines); ++$l)
    {
        $line = $lines[$l];

        $line_indent_size = 0;
        $line_code = "";
     
        $c = 0;

        while ($c < strlen($line))
        {
            if ($line[$c] == "\t")
            {
                $line_indent_size += $tab_src_size;
                ++$c;
            }
            else if ($line[$c] == " ")
            {
                $line_indent_size += 1;
                ++$c;
            }
            else if (substr($line, $c, 6) == "&nbsp;")
            {
                $line_indent_size += 1;
                $c += 6;
            }
            else 
            {
                $line_code = substr($line, $c);
                break;
            }
        }

        $new_line_indent_size  = $tab_dst_size * (int)($line_indent_size / $tab_src_size);
        $new_line_indent_size += $line_indent_size - $tab_src_size * (int)($line_indent_size / $tab_src_size);

        $lines[$l] = str_repeat($tab_space, $new_line_indent_size).trim($line_code);
    }

    $code = implode(PHP_EOL, $lines);

    return $code;
}

function code_section($code, $title, $attributes = false)
{
    $html_header = "";

    if (!!$title && "" != $title)
    {
        if ($attributes == "card")
        {
            $html_header = card_title($title);
        }
        else
        {
            $html_header = header($title);
        }
    }

    $html_code = ($attributes == "card") ? card_text($code) : $code;
    
    $attributes = dom\attributes_add_class($attributes, "ide");

    return 

        style("

            @layer ide;
        
            .ide {
                overflow:       hidden;
                margin-block:   var(--gap);
                width:          fit-content;
                border:         2px dashed var(--border-color, var(--theme-color));
            }
            .ide code {
                border:         unset;
                background:     unset;
                color:          unset;
                display:        block;
            }
            .ide pre {
                white-space:    pre;
                display:        block;
                margin-bottom:  0;
            }

            .in-iframe .ide { 
                display:        none 
            }

            /* IDE within a card */
                        
            .card.ide {
                width:          fit-content;
                max-width:      calc(100vw - 2 * var(--gap));
                margin-inline:  auto;
                overflow:       hidden;
                border:         none;
            }

            .card.ide .card-text {

                padding:        calc(0.5 * var(--gap));
            }

            ").

        div($html_header.$html_code, $attributes).
        
        "";
}

function code($code, $title, $attributes = false, $lang = "php", $syntax_highlight = auto)
{
    $profiler = debug_track_timing();

    if (auto === $syntax_highlight) $syntax_highlight = !get("gemini");

    $code = code_sanitize($code);

    if ($syntax_highlight)
    {
        $embeds = [];

        if ($lang == "php")
        {
            // Extract other languages embeded inside php

            foreach ([

                [ "html",         '<html>'                          .'<?= HERE() ?>', '<?= HERE("raw_html"' ],
                [ "javascript", '<script>'                          .'<?= HERE() ?>', '<?= HERE("raw_js"'   ],
                [ "css",         '<style>'                          .'<?= HERE() ?>', '<?= HERE("raw_css"'  ],
                [ "markdown",    '<code class="language-markdown">' .'<?= HERE() ?>', '<?= HERE("raw"'      ],

                ] as $language_embed)
            {
                list($embed_lang, $tag_bgn, $tag_end) = $language_embed;

                $pos_end = 0;

                while (true)
                {
                    $pos_bgn = stripos($code, $tag_bgn, $pos_end); if ($pos_bgn === false) break;
                    $pos_end = stripos($code, $tag_end, $pos_bgn); if ($pos_end === false) break;

                    $placeholder = comment("CODE-EMBED-".count($embeds));
                    $embed = substr($code, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));
                    $code = substr($code, 0, $pos_bgn + strlen($tag_bgn)).$placeholder.substr($code, $pos_end);
                    $pos_end = $pos_bgn + strlen($tag_bgn) + strlen($tag_end) + strlen($placeholder);

                    $embeds[] = [ $embed_lang, $embed ];
                }
            }
        }

        $code = htmlentities($code);
        $code = dom\code($code, [ "class" => "language-$lang", "contenteditable" => "contenteditable", "spellcheck" => false ]);
    
        if ($lang == "php")
        {
            // Re-inject other languages
            
            foreach ($embeds as $index => $embed)
            {
                list($embed_lang, $embed) = $embed;
                
                $embed = htmlentities($embed);
                $embed = dom\code($embed, "language-$embed_lang");

                $placeholder = htmlentities(comment("CODE-EMBED-$index"));
                $embed = '</code>'.$embed.'<code class="language-'.$lang.'" contenteditable spellcheck=false>';
                $code  = str_replace($placeholder, $embed, $code);
            }
        }
            
        $code = pre($code, "language-$lang line-numbers");
    }
    
    $code = code_transform_indent($code);

    return code_section($code, $title, $attributes);
}

function this($title = "", $attributes = false)
{
    $callstack = debug_backtrace(0);
    if (0 == count($callstack)) return "";

    $caller_source_filename = $callstack[count($callstack) - 1]["file"];
    $caller_source_content = @file_get_contents($caller_source_filename);
    if (false == $caller_source_content) $caller_source_content = $caller_source_filename;

    return code($caller_source_content, $title, $attributes, "php");
}

?>