<?php

namespace dom\pixelfed;

require_once(__DIR__."/../dom.php"); 
use function \dom\{set,get,at,content};
use const \dom\{auto};

#region Direct API calls wrappers

// https://docs.joinmastodon.org/methods/
// https://beta-preview.pixelfed.io

function host($host = auto)
{
    if (auto !== $host && true !== $host && is_string($host) && "" != $host) return $host;
    return get("pixelfed_host", "pixelfed.social");
}

function verify_credentials($token = auto, $timeout = 7, &$debug_error_output = null)
{
    return api_get("https://".host()."/api/v1", "accounts/verify_credentials", $token, $timeout, $debug_error_output);
}

function account_id($token = auto, $timeout = 7, &$debug_error_output = null)
{
    return at(verify_credentials($token, $timeout, $debug_error_output), "id");
}

function account($account_id = auto, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $account_id = auto === $account_id ? account_id($token) : $account_id;

    return api_get("https://".host()."/api/v1", "accounts/$account_id", $token, $timeout, $debug_error_output);
}

function status($status_id, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    return api_get("https://".host()."/api/v1", "statuses/$status_id", $token, $timeout, $debug_error_output);
}

function statuses($account_id = auto, $limit = 999, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $account_id = auto === $account_id ? account_id($token) : $account_id;

    return api_get("https://".host()."/api/v1", "accounts/$account_id/statuses?limit=$limit", $token, $timeout, $debug_error_output);
}

function following($account_id = auto, $limit = 999, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $account_id = auto === $account_id ? account_id($token) : $account_id;

    return api_get("https://".host()."/api/v1", "accounts/$account_id/following?limit=$limit", $token, $timeout, $debug_error_output);
}

function send_media($path, $name, $alt, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $idempotency_key = md5(json_encode([ "path" => $path, "name" => $name, "alt" => $alt ]));
  
    return api_post(
        
        "https://".host()."/api/v2", "media", 
        
        [   "file"          => curl_file_create($path == "" ? $name : "$path/$name", 'image/jpeg', $name),
            "description"   => $alt, ], 

        [   "Content-Type"  => "multipart/form-data", 
            "Accept"        => "application/json" ],

        $token, $timeout, $debug_error_output, $idempotency_key, /*force_no_url_params*/true
    );
}

function send_status($message, $medias = [], $token = auto, $timeout = 7, &$debug_error_output = null)
{
    if (!is_array($medias)) $medias = [];

    $idempotency_key = md5(json_encode([ "message" => $message, "medias" => $medias ]));

    // V1 - v1/statuses - Can handle alt texts, but doesn't work yet because of media_ids param format
  
    $params = [];
    {
        if (is_string($message) && "" != $message) 
        {
            $params["status"] = $message;
        }

        if (count($medias) > 0)
        {
            $params["media_ids"] = [];

            foreach ($medias as $media)
            {
                list($path, $name, $alt) = array_values($media);

                $response = send_media($path, $name, $alt, $token, $timeout, $debug_error_output);

                if (at($response, "code", 200) != 200
                &&  at($response, "code", 200) != 201
                &&  at($response, "code", 200) != 202)
                {
                    return $response;
                }
                
                $media_id = at($response, "id");

                if (false !== $media_id) $params["media_ids"][] = $media_id;
            }
        }
    }

    return api_post(
        
        "https://".host()."/api/v1", "statuses", 
        
        $params, 
        
        [   "Content-Type"  => "multipart/form-data", 
            "Accept"        => "application/json" ], 
            
        $token, $timeout, $debug_error_output, $idempotency_key
    );

    // V2 - v1.1/status/create - Works but cannot handle alt texts afaik
    /*
    if (count($medias) == 0)
    {
        return false;
    }
    else if (count($medias) > 1)
    {
        foreach ($medias as $media)
        {
            $response = send_status($message, [ $media ], $token, $timeout, $debug_error_output);
        }

        return $response;
    }

    $params = [];
    {
        if (!is_string($message)) $message = "";
        $params["status"] = $message;

        $media = array_shift($medias);
        list($path, $name, $alt) = array_values($media);
        $params["file"] = curl_file_create($path == "" ? $name : "$path/$name", 'image/jpeg', $name);
    }

    return api_post(
        
        "https://".host()."/api/v1.1", "status/create", 
        $params, 
        [ "Content-Type" => "multipart/form-data", "Accept" => "application/json" ], 
        $token, 
        $timeout, 
        $debug_error_output, 
        $idempotency_key
    );*/
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

function api_get($api, $url, $token = auto, $timeout = 7, &$debug_error_output = null, $idempotency_key = false)
{
    $token = auto === $token ? constant("TOKEN_PIXELFED") : $token;

    $header = [];
    {
        if (false !== $token)           $header["Authorization"]   = $token;
        if (false !== $idempotency_key) $header["Idempotency-Key"] = $idempotency_key;
    }

    return json_decode(content(

        "$api/$url", 
        [ "timeout" => $timeout, "header" => $header ], 
        /*auto_fix*/false, 
        $debug_error_output, 
        /*methods_order*/[ "curl" ]

    ), true);
}

function api_post($api, $url, $params, $header = [], $token = auto, $timeout = 7, &$debug_error_output = null, $idempotency_key = false, $force_no_url_params = false)
{
    $token = auto === $token ? constant("TOKEN_PIXELFED") : $token;

    if (false !== $token)           $header["Authorization"]   = $token;
    if (false !== $idempotency_key) $header["Idempotency-Key"] = $idempotency_key;

    $token          = at($header, "Authorization",   at($header, "token", $token));
    $content_type   = at($header, "Content-Type",    at($header, "content-type" ));
    $charset        = at($header, "Charset",         at($header, "charset"      ));
    $language       = at($header, "Accept-language", at($header, "language"     ));
    $accept         = at($header, "Accept",          at($header, "accept"       ));
    $client_id      = at($header, "Client-ID",       at($header, "client-id"    ));

    $header = [];

    if (!!$token)        $header["Authorization"]   = "Bearer $token";
    if (!!$content_type) $header["Content-Type"]    = $content_type.(!$charset ? "" : "; charset=$charset");
    if (!!$language)     $header["Accept-language"] = $language;
    if (!!$client_id)    $header["Client-ID"]       = $client_id;
    if (!!$accept)       $header["Accept"]          = $accept;
    
    $code = $error = $error_details = null;

    $response = \dom\post(
        $api, $url, 
        $params, 
        [ "timeout" => $timeout, "header" => $header ], 
        "POST", 
        false, 
        false, 
        "villepreux.net", 
        $code, $error, $error_details, 
        $force_no_url_params
    );

    if ($code != 200 && $code != 201 && $code != 202)
    {
        $debug_error_output = $error_details;
        $response = [ "code" => $code, "error" => $error, "details" => $error_details, "response" => htmlentities($response) ];
    }
    else
    {
        $response = json_decode($response, true);
    }

    return $response;
}

#endregion