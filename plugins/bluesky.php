<?php

namespace dom\bluesky;

require_once(__DIR__."/../dom.php");
use function dom\{get,set,at,array_open_url};
use const dom\{auto};

function public_api() { return "https://public.api.bsky.app/xrpc/"; }
function social_api() { return         "https://bsky.social/xrpc/"; }

function post($post_url) 
{
	$post_at_uri = post_at_uri($post_url);
	if (!$post_at_uri) return false;

	return at(cache_or_fetch(public_api()."app.bsky.feed.getPosts?uris=$post_at_uri"), 0);
}

function likes($post_url, $limit = false) 
{
	$post_at_uri = post_at_uri($post_url);
	if (!$post_at_uri) return false;

	return at(cache_or_fetch(public_api()."app.bsky.feed.getLikes?uri=$post_at_uri".(!$limit ? "" : "&limit=$limit")), "likes");
}

function posts($user_handle = auto, $limit = false) 
{
    if (auto === $user_handle) $user_handle = get("bluesky-handle");
    if (!$user_handle) return false;

    return at(cache_or_fetch(public_api()."app.bsky.feed.getAuthorFeed?actor=$user_handle".(!$limit ? "" : "&limit=$limit")), "feed");
}

function profile($user_handle = auto) 
{
    if (auto === $user_handle) $user_handle = get("bluesky-handle");
    if (!$user_handle) return false;

    return cache_or_fetch(public_api()."app.bsky.actor.getProfile?actor=$user_handle");
}

function user_did($user_handle = auto)
{	
    if (auto === $user_handle) $user_handle = get("bluesky-handle");
    if (!$user_handle) return false;

    if (0 === stripos($user_handle, "did:")) return $user_handle;
	$did = at(profile($user_handle), "did");

    return valid_did($did);
}

function blob($cid, $user_handle = auto) 
{	
	$cid = valid_cid($cid);
	if (!$cid) return false;

    if (auto === $user_handle) $user_handle = get("bluesky-handle");
    if (!$user_handle) return false;

    $did = user_did($user_handle);
	if (!$did) return false;

    $url = social_api()."com.atproto.sync.getBlob?did=$did&cid=$cid";

    return [ "url" => $url, "blob" => cache_or_fetch($url) ];
}

function blobs($user_handle = auto) 
{	
    if (auto === $user_handle) $user_handle = get("bluesky-handle");
    if (!$user_handle) return false;

    $did = user_did($user_handle);
	if (!$did) return false;

    $url = social_api()."com.atproto.sync.listBlobs?did=$did";

    return [ "url" => $url, "blobs" => cache_or_fetch($url) ];
}


function post_at_uri($post_url) 
{
    list($user_handle, $post_id) = explode("@", str_replace("/posts/", "@", str_replace("https://bsky.app/profile/", "", $post_url)));
	if (!$user_handle || !$post_id) return false;

	$did = user_did($user_handle);
	if (!$did) return false;

	return "at://$did/app.bsky.feed.post/$post_id";
}

function cache_or_fetch($url)
{
    $json = get("bsky-$url", false, false, false, false, false, /*DOM*/true);
    
    if (!$json) 
    {
        $json = array_open_url($url);
        set("bsky-$url", $json, "DOM");
    }

    return $json;
}

function valid_did($did)
{	
    if (!$did) return false;
    if (0 === stripos($did, "did:")) return $did;
    return "did:$did";
}

function valid_cid($cid)
{	
  //if (!$cid) return false;
  //if (0 === stripos($cid, "cid:")) return $cid;
  //return "cid:$cid";
    return $cid;
}
