<?php

namespace dom\pixelfed;

require_once(__DIR__."/../dom.php"); 
use function \dom\{content};
use const \dom\{auto};

function api($url, $token = auto, $timeout = 7)
{
    $token = auto === $token ? constant("TOKEN_PIXELFED") : $token;

    content(
        $url, 
        [ "timeout" => $timeout, "header" => [ "Authorization" => TOKEN_PIXELFED ] ], 
        /*auto_fix*/false, 
        /*debug_error_output*/false, 
        /*methods_order*/[ "curl" ], 
        /*profiling_annotation*/false
    );
}

function verify_credentials()
{
    return api("https://pixelfed.social/api/v1/accounts/verify_credentials");
}

function status($id)
{
    return api("https://pixelfed.social/api/v1/statuses/$id");
}
