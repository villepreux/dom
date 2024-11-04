<?php

namespace dom;
require_once(__DIR__."/../dom.php");

set("minify");
del("beautify");

init();

output(main(h1("DOM config").table(

    style("td:first-child { white-space: nowrap }").
    
    tr(td("server_server_name()"    ).td(server_server_name()   )).
    tr(td("live_domain()"           ).td(live_domain()          )).
    tr(td("live_url()"              ).td(live_url()             )).
    tr(td("host_url()"              ).td(host_url()             )).
    tr(td("url()"                   ).td(url()                  )).
    tr(td("url_branch()"            ).td(url_branch()           )).
    tr(td("url_leaf()"              ).td(url_leaf()             )).

    tr(td("get_all()"               ).td(pre(raw_array_debug(get_all())))).

    "")));
