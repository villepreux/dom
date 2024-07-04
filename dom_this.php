<?php require_once(__DIR__."/dom_html.php");

use function dom\{HSTART,HSTOP,HERE,get,card_title,card_text,header,div,pre,style,debug_track_timing,comment,unindent};
use const dom\auto;

const code_tab_src_size = 4;
const code_tab_dst_size = 2;

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

function code_transform_indent($code, $tab_src_size = code_tab_src_size, $tab_dst_size = code_tab_dst_size, $tab_space = "&nbsp;")
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

function code_css()
{
    HSTART() ?><style><?= HERE() ?>

        /* TODO: CLEANUP CSS */

        @layer ide;
        
        .ide:not(:is(.card.ide, details .ide)) {
            width:          fit-content;
            max-width:      stretch;
            max-width:      -moz-available;
            max-width:      -webkit-fill-available;
            border:         2px dashed var(--theme-color);
            margin-bottom:  var(--gap);
        }

        .ide pre {
            margin-block:   0;
            padding:        var(--gap);
        }

        .ide code {
            display:        block;
            white-space:    pre;
            padding:        0;
            width:          fit-content;
            max-width:      100%;
            border:         none;
            background:     unset;
        }

        :is(.card, details).ide code {            
            border: none;
        }

        /* Horizontal Scroll */

        .card.ide {
            overflow: hidden;
        }        
        .ide:not(:is(.card, details)), .card.ide > :is(.card-text, pre) {

            overflow:   hidden;
            overflow-x: auto;
        }

        /* Do not display source code when inside iframe */

        .in-iframe .ide { 
            display: none 
        }

    <?= HERE("raw_css") ?></style><?php return HSTOP();
}

function code_section($code, $title, $attributes = false)
{   
    $card = ($attributes == "card");
   
    $attributes = dom\attributes_add_class($attributes, "ide");
    
    if ($card)
    {
        return style(code_css()).div(((!!$title && "" != $title) ? card_title($title) : "").card_text($code), $attributes);
    }
    else
    {
        return style(code_css()).div(((!!$title && "" != $title) ? header($title) : "").$code, $attributes);
    }
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

                    $indent = 0;
                    $embed  = unindent($embed, $indent);

                    $embeds[] = [ $embed_lang, $embed, $indent ];
                }
            }
        }

        $code = htmlentities($code);
        $code = dom\code($code, [ "class" => "language-$lang", "spellcheck" => false ]);
    
        if ($lang == "php")
        {
            // Re-inject other languages
            
            foreach ($embeds as $index => $embed)
            {
                list($embed_lang, $embed, $embed_indent) = $embed;

                $embed_indent *= code_tab_dst_size / code_tab_src_size;
                
                $embed = htmlentities($embed);
                $embed = dom\code($embed, [ "class" => "language-$embed_lang",  "style" => "padding-left: {$embed_indent}ch" ]);

                $placeholder = htmlentities(comment("CODE-EMBED-$index"));
                $embed = '</code>'.$embed.'<code class="language-'.$lang.'" spellcheck=false>';
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
