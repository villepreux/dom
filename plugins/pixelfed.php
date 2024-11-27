<?php

namespace dom\pixelfed;

require_once(__DIR__."/../dom.php"); 
use function \dom\{at,content};
use const \dom\{auto};

function api($url, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $token = auto === $token ? constant("TOKEN_PIXELFED") : $token;

    return json_decode(content(

        $url, 
        [ "timeout" => $timeout, "header" => [ "Authorization" => TOKEN_PIXELFED ] ], 
        /*auto_fix*/false, 
        $debug_error_output, 
        /*methods_order*/[ "curl" ]

    ), true);
}

function verify_credentials($token = auto, $timeout = 7, &$debug_error_output = null)
{
    return api("https://pixelfed.social/api/v1/accounts/verify_credentials", $token, $timeout, $debug_error_output);
}

function account_id($token = auto, $timeout = 7, &$debug_error_output = null)
{
    return at(verify_credentials($token, $timeout, $debug_error_output), "id");
}

function account($account_id = auto, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $account_id = auto === $account_id ? account_id($token) : $account_id;

    return api("https://pixelfed.social/api/v1/accounts/$account_id", $token, $timeout, $debug_error_output);
}

function status($status_id, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    return api("https://pixelfed.social/api/v1/statuses/$status_id", $token, $timeout, $debug_error_output);
}

function statuses($account_id = auto, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $account_id = auto === $account_id ? account_id($token) : $account_id;

    return api("https://pixelfed.social/api/v1/accounts/$account_id/statuses", $token, $timeout, $debug_error_output);
}

function following($account_id = auto, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $account_id = auto === $account_id ? account_id($token) : $account_id;

    return api("https://pixelfed.social/api/v1/accounts/$account_id/following", $token, $timeout, $debug_error_output);
}
