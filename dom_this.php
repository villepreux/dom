<?php require_once(__DIR__."/dom_html.php");

use function dom\{bye,HSTART,HSTOP,HERE,get,card_title,card_text,header,div,pre,style,debug_track_timing,comment,unindent,details,summary,p,css_layer, layered_style};
use const dom\auto;

define("CODE_RE_INDENT", false);

const code_tab_src_size = 4;
const code_tab_dst_size = 2;

function code_sanitize($code)
{
    $profiler = debug_track_timing();

    $lines = [];

    foreach (explode(PHP_EOL, $code) as $line)
    {
        if (0     === stripos(trim($line),                       "//"       )) continue;
        if (false !== stripos(str_replace(["\t",""], "", $line), "!PRIVATE" )) continue;

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

        .ide:not(:is(.card.ide, details .ide, article .ide)) {
            width:          fit-content;
            max-width:      stretch;
            max-width:      -moz-available;
            max-width:      -webkit-fill-available;
            border:         2px dashed var(--theme-color);
            margin-bottom:  var(--gap);
        }

        .ide pre, .ide pre[class*=language-] {
            margin-block:   0;
            margin:         0 !important; /* as 3rd parties like prism.js set margin directly */
            padding:        var(--gap);
        }

        .ide code, .ide code[class*=language-]  {
            display:        block;
            white-space:    pre;
            padding:        0;
            width:          fit-content;
            max-width:      100%;
            border:         none;
        }

        .ide pre, .ide code, .ide code[class*=language-]  {
            background:     unset !important; /* as applying 3rd party highligh lib theme might try to override it most of the time */
        }

        :is(.card, details).ide :is(code, code[class*=language-]) {            
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

function code_section($code, $client_source_url, $title = false, $attributes = false)
{   
    $is_card   = ($attributes == "card");
    $has_title = (!!$title && "" != $title);
   
    $attributes = dom\attributes_add_class($attributes, "ide");

    $view_compile_source = "";
    {
        if (!!$client_source_url && (!get("minify") || !!get("beautify")))
        {
            $source = dom\content($client_source_url.(false === stripos($client_source_url, "?") ? "?" : "")."&no-code=1");
            {
                if (!!$source && "" != $source)
                {
                    $tag_bgn = '<div class="ide">';
                    $tag_end = "</code></pre></div>";

                    $pos_bgn = stripos($source, $tag_bgn);
                    $pos_end = stripos($source, $tag_end, $pos_bgn);

                    if ($pos_bgn && $pos_end) $source = substr($source, 0, $pos_bgn).dom\comment("server-side source code").substr($source, $pos_end + strlen($tag_end));
                }
                else
                {
                    $source = false;
                }
            }

            if (!!$source && "" != $source)
            {
                if ($is_card)
                {
                    $view_compile_source = card_title("Client source-code").card_text(pre(dom\code(htmlentities($source), "language-html"), "language-html line-numbers"));
                }
                else
                {
                    $view_compile_source = header(p("Client source-code")).pre(dom\code(htmlentities($source), "language-html"), "language-html line-numbers");
                }
            }
        }
    }
    
    if ($is_card)
    {
        return layered_style("app.code.ide", code_css()).div(($has_title ? card_title($title) : "").card_text($code).$view_compile_source, $attributes);
    }
    else
    {
        return layered_style("app", code_css()).div(($has_title ? header($title) : "").$code.$view_compile_source, $attributes);
    }
}

function highlight($code, $lang)
{    
    try {

        if (class_exists("\Tempest\Highlight\Highlighter")) 
        {
            $highlighter = @new \Tempest\Highlight\Highlighter();

            if ($highlighter) 
            {
                return $highlighter->parse($code, $lang);
            } 
        } 
    }
    catch (Exception $e) 
    {
    }

    try {

        if (class_exists("\Highlight\Highlighter")) 
        {
            $highlighter = @new \Highlight\Highlighter();

            if ($highlighter) 
            {
                return $highlighter->highlight($lang, $code)->value;
            } 
        } 
    }
    catch (Exception $e) 
    {
    }

    $code = htmlentities($code);
}

function code_highlight($code, $lang = "php")
{
    $profiler = debug_track_timing();

    $embeds = [];

    if ($lang == "php")
    {
        // Extract other languages embeded inside php
        
        foreach ([ "HERE", "dom\HERE", "heredoc_flush", "dom\heredoc_flush"] as $here_func)
        foreach ([

            [ "xml",        '<xml>'                             .'<?= '.$here_func.'() ?>', '<?= '.$here_func.'("raw_xml"'  ],
            [ "html",       '<html>'                            .'<?= '.$here_func.'() ?>', '<?= '.$here_func.'("raw_html"' ],
            [ "javascript", '<script>'                          .'<?= '.$here_func.'() ?>', '<?= '.$here_func.'("raw_js"'   ],
            [ "css",        '<style>'                           .'<?= '.$here_func.'() ?>', '<?= '.$here_func.'("raw_css"'  ],

            [ "xml",        '<xml>'                             .'<?= '.$here_func.'() ?>', '<?= '.$here_func.'_xml('  ],
            [ "html",       '<html>'                            .'<?= '.$here_func.'() ?>', '<?= '.$here_func.'_html(' ],
            [ "javascript", '<script>'                          .'<?= '.$here_func.'() ?>', '<?= '.$here_func.'_js('   ],
            [ "css",        '<style>'                           .'<?= '.$here_func.'() ?>', '<?= '.$here_func.'_css('  ],

            [ "markdown",   '<code class="language-markdown">'  .'<?= '.$here_func.'() ?>', '<?= '.$here_func.'("raw"'      ],

            ] as $language_embed)
        {
            list($embed_lang, $tag_bgn, $tag_end) = $language_embed;

            $pos_end = 0;

            while (true)
            {
                $pos_bgn = stripos($code, $tag_bgn, $pos_end); if ($pos_bgn === false) break;
                $pos_end = stripos($code, $tag_end, $pos_bgn); if ($pos_end === false) break;

                $placeholder = "CODEEMBED".count($embeds)."CODEEMBED";
                $embed = substr($code, $pos_bgn + strlen($tag_bgn), $pos_end - $pos_bgn - strlen($tag_bgn));
                $code = substr($code, 0, $pos_bgn + strlen($tag_bgn)).$placeholder.substr($code, $pos_end);
                $pos_end = $pos_bgn + strlen($tag_bgn) + strlen($tag_end) + strlen($placeholder);

                $indent = 0;
                $embed  = unindent($embed, $indent);

                $embeds[] = [ $embed_lang, $embed, $indent ];
            }
        }
    }

    $code = highlight($code, $lang);
    $code = dom\code($code, [ "class" => "language-$lang", "spellcheck" => false ]);

    if ($lang == "php")
    {
        // Re-inject other languages
        
        foreach ($embeds as $index => $embed)
        {
            list($embed_lang, $embed, $embed_indent) = $embed;

            if (CODE_RE_INDENT)
            {
                $embed_indent *= code_tab_dst_size / code_tab_src_size;
            }
            
          //$embed = htmlentities($embed);
            $embed = highlight($embed, $embed_lang);
            $embed = dom\code($embed, [ "class" => "language-$embed_lang",  "style" => "padding-left: {$embed_indent}ch" ]);

          //$placeholder = comment("CODE-EMBED-$index");
            $placeholder = "CODEEMBED".$index."CODEEMBED";
            $placeholder = htmlentities($placeholder);
            $embed = '</code>'.$embed.'<code class="language-'.$lang.'" spellcheck=false>';

            $code  = str_replace($placeholder, $embed, $code);
        }
    }

    return pre($code, "language-$lang line-numbers");
}

function code($code, $title = false, $attributes = false, $lang = "php", $syntax_highlight = auto, $client_source_url = false, $code_sanitize = auto)
{
    $profiler = debug_track_timing();

    if (auto === $code_sanitize)    $code_sanitize    = true;
    if (auto === $syntax_highlight) $syntax_highlight = !get("gemini");

    if ($code_sanitize)
    {
        $code = code_sanitize($code);
    }

    if ($syntax_highlight)
    {
        $code = code_highlight($code, $lang);
    }
    else
    {
        $code = htmlentities($code);
        $code = pre(dom\code($code, "language-$lang"));
    }

    if (CODE_RE_INDENT)
    {
        $code = code_transform_indent($code);
    }

    return code_section($code, $client_source_url, $title, $attributes);
}

function this($title = "", $attributes = false, $include_client_source = false, $syntax_highlight = auto, $code_sanitize = true)
{
    return \dom\delayed_component("_".__FUNCTION__, [ $title, $attributes, $include_client_source, $syntax_highlight, $code_sanitize ]);
}

function _this($title = "", $attributes = false, $include_client_source = false, $syntax_highlight = auto, $code_sanitize = true)
{
    if (!!get("no-code")) return "";

    $callstack = debug_backtrace(0);
    if (0 == count($callstack)) return "";

    $caller_source_filename = $callstack[count($callstack) - 1]["file"];
    $caller_source_content = @file_get_contents($caller_source_filename);
    if (false == $caller_source_content) $caller_source_content = $caller_source_filename;

    return code($caller_source_content, $title, $attributes, "php", $syntax_highlight, $include_client_source ? dom\live_url() : false, $code_sanitize);
}
