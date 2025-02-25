<?php

namespace dom\pixelfed;

require_once(__DIR__."/../dom.php"); 
use function \dom\{at,content};
use const \dom\{auto};

#region Direct API calls wrappers

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

#endregion
#region Extensions to the API

function neighborhood($account_id, $limit)
{
    $neighbors = [];

    $accounts_parse_queue = following($account_id);
    if (!is_array($accounts_parse_queue)) return $neighbors;

    $parsed_set = [ $account_id => true ];

    $known_set = [ $account_id => true ];
    foreach ($accounts_parse_queue as $followed) $known_set[at($followed, "id")] = true;
    
    while (count($accounts_parse_queue) > 0)
    {
        $target_account_id = at(array_shift($accounts_parse_queue), "id");
        if (isset($parsed_set[$target_account_id])) continue;        
        $parsed_set[$target_account_id] = true;
        $new_neighbors = following($target_account_id);
        if (!is_array($new_neighbors)) continue;

        $accounts_parse_queue = array_merge($accounts_parse_queue, $new_neighbors);

        foreach ($new_neighbors as $new_neighbor)
        {
            if (count($neighbors) >= $limit) break;            
            if (isset($known_set[at($new_neighbor, "id")])) continue;
            $known_set[at($new_neighbor, "id")] = true;
            $neighbors[] = $new_neighbor;
            
          /*$account  = account(  at($new_neighbor, "id"));
            $statuses = statuses( at($new_neighbor, "id"));
            if (false !== stripos(json_encode($account), "belajkorpoj.art")) \dom\bye([ "statuses" => statuses(at($new_neighbor, "id")), "account" => $account ]);*/

        }

        if (count($neighbors) >= $limit) break;
    }

    return $neighbors;
}

#endregion
#region API Wrapper

function api($url, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $token = auto === $token ? constant("TOKEN_PIXELFED") : $token;

    return json_decode(content(

        $url, 
        [ "timeout" => $timeout, "header" => [ "Authorization" => $token ] ], 
        /*auto_fix*/false, 
        $debug_error_output, 
        /*methods_order*/[ "curl" ]

    ), true);
}

#endregion