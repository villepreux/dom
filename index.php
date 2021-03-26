<?php

require_once dirname(__FILE__)."/dom.php";

set("generate", false);

dom_init();

dom_output(head().body(content(dom_markdown(dom_include_file(dom_path("README.md"))))));
