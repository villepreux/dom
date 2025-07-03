<?php

namespace dom\bluesky;

require_once(__DIR__."/../dom.php");
use function dom\{get,set,at,array_open_url};
use const dom\{auto};

function url_api() 
{
    return "https://public.api.bsky.app/xrpc/app.bsky"; 
}

function post($post_url) 
{
	$post_at_uri = post_at_uri($post_url);
	if (!$post_at_uri) return false;

	return at(cache_or_fetch(url_api().".feed.getPosts?uris=$post_at_uri"), 0);
}

function likes($post_url, $limit = false) 
{
	$post_at_uri = post_at_uri($post_url);
	if (!$post_at_uri) return false;

	return at(cache_or_fetch(url_api().".feed.getLikes?uri=$post_at_uri".(!$limit ? "" : "&limit=$limit")), "likes");
}

function posts($user_handle = auto, $limit = false) 
{
    if (auto === $user_handle) $user_handle = get("bluesky-handle");
    if (!$user_handle) return false;

	return at(cache_or_fetch(url_api().".feed.getActorFeeds?actor=$user_handle".(!$limit ? "" : "&limit=$limit")), "feeds");
}

function profile($user_handle = auto) 
{
    if (auto === $user_handle) $user_handle = get("bluesky-handle");
    if (!$user_handle) return false;

    return cache_or_fetch(url_api().".actor.getProfile?actor=$user_handle");
}

function user_did($user_handle = auto)
{	
    if (auto === $user_handle) $user_handle = get("bluesky-handle");
    if (!$user_handle) return false;

    if (0 === stripos($user_handle, "did:")) return $user_handle;
	return at(profile($user_handle), "did");
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
