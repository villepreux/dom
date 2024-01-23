<?php require_once(__DIR__."/dom_html.php");

use function dom\{set,get,pre,style};

set("fonts", get("fonts")."|Fira Code");

function code($code, $lang = "php", $syntax_highlight = true)
{
    if ($syntax_highlight)
    {
        // Workaround crappy native php syntax hightlight function ------>
        $functions = array("default", "html", "keyword", "string", "comment");
        foreach ($functions as $value) @ini_set("highlight.$value", "ide-highlight-$value;");

        if ($lang == "js")
        {
            $data       = $code;
            $options    = false;
            
            $c_string   = "#DD0000";
            $c_comment  = "#FF8000";
            $c_keyword  = "#007700";
            $c_default  = "#0000BB";
            $c_html     = "#0000BB";

            $flush_on_closing_brace = false;

            if (is_array($options))
            {
                extract($options, EXTR_OVERWRITE); // extract the variables from the array if so
            } 
            else
            {
                $advanced_optimizations = $options; // otherwise carry on as normal
            }

            if ($advanced_optimizations) 
            { 
                // if the function has been allowed to perform potential (although unlikely) code-destroying or erroneous edits
                $data = preg_replace('/([$a-zA-z09]+) = \((.+)\) \? ([^]*)([ ]+)?\:([ ]+)?([^=\;]*)/', 'if ($2) {'."\n".' $1 = $3; }'."\n".'else {'."\n".' $1 = $5; '."\n".'}', $data); // expand all BASIC ternary statements into full if/elses
            }
            
            $data = str_replace(array(') { ', ' }', ";", "\r\n"), array(") {\n", "\n}", ";\n", "\n"), $data); // Newlinefy all braces and change Windows linebreaks to Linux (much nicer!) 
            $data = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $data); // Regex identifies all extra empty lines produced by the str_replace above. It is quicker to do it like this than deal with a more complicated regular expression above.
            $data = str_replace("<?php", "<script>", highlight_string("<?php \n" . $data . "\n?>", true)); 
            $data = explode("\n", str_replace(array("<br />"), array("\n"),$data));
            
            $tab = 0; # experimental tab level highlighting
            $output = '';
            
            foreach ($data as $line)
            { 
                $lineecho = $line;

                if (substr_count($line, "\t") != $tab) 
                {
                    $lineecho = str_replace("\t", "", trim($lineecho));
                    $lineecho = str_repeat("\t", $tab) . $lineecho;
                }
                    
                $tab = $tab + substr_count($line, "{") - substr_count($line, "}");

                if ($flush_on_closing_brace && trim($line) == "}") 
                {
                    $output .= '}';
                } 
                else 
                {
                    $output .= str_replace(array("{}", "[]"), array("<span class='ide-highlight-string'>{}</span>", "<span class='ide-highlight-string'>[]</span>"), $lineecho."\n"); // Main JS specific thing that is not matched in the PHP parser
                }    
            }
            
            $output = str_replace(array('?php', '?&gt;'), array('script type="text/javascript">', '&lt;/script&gt;'), $output); // Add nice and friendly <script> tags around highlighted text
            
            $code = $output;
        }
        else /*if ($lang == "php")*/
        {    
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
    }

    $lines = explode(PHP_EOL, $code);

    $tab_src_size = 4;
    $tab_dst_size = 2;

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

        $line_indent_size = $tab_dst_size * (int)($line_indent_size / $tab_src_size);

        $lines[$l] = str_repeat("&nbsp;", $line_indent_size).$line_code;
    }

    $i = 0;

    return 

        pre(implode(PHP_EOL, array_map(function ($line) use (&$i, $syntax_highlight) { 
                
            return  '<div class="ide-line">'.
            
                        '<div class="ide-line-number">'.str_pad(++$i, 3, "0", STR_PAD_LEFT).'</div>'.
                        '<div class="ide-line-code">'.($syntax_highlight ? $line : htmlentities($line)).'</div>'.
                        
                    '</div>';
            
            }, $lines)), "ide").
        
        style("

            @layer ide;
        
            .ide {

                width:              fit-content;
                border:             1px dashed var(--border-color);
                padding:            var( --gap);

                font-family:        'Fira Code', monospace; 
                font-size:          14px;
                font-weight:        400;
                line-height:        1.3em;
            }
            .ide-line { 
                display:        inline-flex; 
                align-items:    flex-start;
                gap:            .5em; 
            }
            .ide-line-number {
                white-space:    nowrap;
                flex-shrink:    0;
                pointer-events: none;
                user-select:    none;
            }

            .ide {
                    
                --ide-background-color: #FFFFFF;

                --ide-text-color:               #000000;
                --ide-highlight-default-color:  #0000ff;
                --ide-highlight-keyword-color:  #006c00;
                --ide-highlight-string-color:   #b50000;
                --ide-highlight-comment-color:  #7a5700;
            }

            @media (prefers-color-scheme: dark) {

                .ide {
                    
                    --ide-background-color: #000000;
    
                    --ide-text-color:               #FFFFFF;
                    --ide-highlight-default-color:  #6ce8ff;
                    --ide-highlight-keyword-color:  #84FF84;
                    --ide-highlight-string-color:   #ffd700;
                    --ide-highlight-comment-color:  #FFAE85;
                }
                
            }

            [data-colorscheme='light'] .ide {
                    
                --ide-background-color: #FFFFFF;

                --ide-text-color:               #000000;
                --ide-highlight-default-color:  #0000ff;
                --ide-highlight-keyword-color:  #006c00;
                --ide-highlight-string-color:   #b50000;
                --ide-highlight-comment-color:  #7a5700;
            }

            [data-colorscheme='dark'] .ide {
                    
                --ide-background-color: #000000;

                --ide-text-color:               #FFFFFF;
                --ide-highlight-default-color:  #6ce8ff;
                --ide-highlight-keyword-color:  #84FF84;
                --ide-highlight-string-color:   #ffd700;
                --ide-highlight-comment-color:  #FFAE85;
            }
            
            .ide { background-color: var(--ide-background-color) }
            
            .ide                    { color: var(--ide-text-color) }
            .ide-highlight-default  { color: var(--ide-highlight-default-color) }
            .ide-highlight-keyword  { color: var(--ide-highlight-keyword-color) }
            .ide-highlight-string   { color: var(--ide-highlight-string-color) }
            .ide-highlight-comment  { color: var(--ide-highlight-comment-color); font-style: italic }
            

            .in-iframe .ide { display: none }

            ");
}

function this()
{
    //return pre(htmlentities(file_get_contents(__FILE__)));

    $callstack = debug_backtrace(0);
    if (0 == count($callstack)) return "";

    $caller_source_filename = $callstack[count($callstack) - 1]["file"];
    $caller_source_content = @file_get_contents($caller_source_filename);
    if (false == $caller_source_content) $caller_source_content = $caller_source_filename;

    return code($caller_source_content);
}

?>