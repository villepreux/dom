<?php 

/**
 * Blog post excerpt auto-positing on Mastodon
 * +
 * Blog post commenting via Mastodon comments
 * Inspired by https://cassidyjames.com/blog/fediverse-blog-comments-mastodon/
 * Himselft inspired by https://codeberg.org/jwildeboer/jwildeboersource/src/commit/45f9750bb53b9f0f6f28399ce4d21785a3bb7d22/_includes/fediverse_comments.html
 */

namespace dom\mastodon; 

require_once(__DIR__."/../dom.php"); 
use function \dom\{set,get,del,at,array_open_url,array_open_url_content_post_process,HSTART,HERE,HSTOP,style,script,noscript,header,main,footer,section,p,a,picture,source,img,span,div,ul,li,article,multi_fetch,bye};
use const \dom\{auto,external_link,internal_link};

#region Constants

function app_id($token     = auto) { return auto !== $token ? $token : (get("mastodon_app_id",     defined("TOKEN_MASTODON_APPID")  ? constant("TOKEN_MASTODON_APPID")  : false)); }
function app_secret($token = auto) { return auto !== $token ? $token : (get("mastodon_app_secret", defined("TOKEN_MASTODON_SECRET") ? constant("TOKEN_MASTODON_SECRET") : false)); }
function app_token($token  = auto) { return auto !== $token ? $token : (get("mastodon_app_token",  defined("TOKEN_MASTODON")        ? constant("TOKEN_MASTODON")        : false)); }

function valid_host($host = false)
{
    return !!$host ? $host : trim(get("mastodon_domain", "mastodon.social"), "@");
}

function valid_username($username = false)
{
    return !!$username ? $username : trim(get("mastodon_author", get("mastodon_user", defined("TOKEN_MASTODON_USER") ? constant("TOKEN_MASTODON_USER") : get("author"))), "@");
}

function valid_host_username($host = false, $username = false)
{
    $host     = !!$host     ? $host     : trim(get("mastodon_domain", "mastodon.social"), "@");
    $username = !!$username ? $username : trim(get("mastodon_author", get("mastodon_user", defined("TOKEN_MASTODON_USER") ? constant("TOKEN_MASTODON_USER") : get("author"))), "@");

    return [ $host, $username ];
}

#endregion constants
#region constants fetching

function array_user_account($host = false, $username = false)
{
    list($host, $username) = valid_host_username($host, $username);

    $token = app_token();

    return array_open_url(
        
        "https://$host/api/v1/accounts/lookup?acct=$username",
        "json",
        [ "token" => $token, "timeout" => 7 ]
    );
}

function fetch_userid($host = false, $username = false)
{
    list($host, $username) = valid_host_username($host, $username);
    $account = array_user_account($host, $username);
    return at($account, "id");
}

function valid_or_fetch_userid($host = false, $username = false, $user_id = false)
{
    return !!$user_id ? $user_id : fetch_userid($host, $username);
}

function valid_or_fetch_host_username_userid($host = false, $username = false, $user_id = false)
{
    list($host, $username) = valid_host_username($host, $username);
    $user_id = valid_or_fetch_userid($host, $username, $user_id);
    
    return [ $host, $username, $user_id ];
}

#endregion constants fetching
#region URLs

function url_user_following($host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_or_fetch_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;

    return "https://$host/api/v1/accounts/$user_id/following";
}

function url_post($post_id, $host = false, $username = false)
{
    list($host, $username) = valid_host_username($host, $username);
    if (!$host || !$username || !$post_id) return false;
    return "https://$host/@$username/$post_id";
}

function url_post_statuses($post_id, $host = false, $username = false)
{
    list($host, $username) = valid_host_username($host, $username);
    if (!$host || !$post_id) return false;

    return "https://$host/api/v1/statuses/$post_id";
}

function url_post_context($post_id,  $host = false, $username = false)
{
    return url_post_statuses($post_id, $host, $username)."/context";
}

function url_user_statuses($host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_or_fetch_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;

    return "https://$host/api/v1/accounts/$user_id/statuses";
}

function url_user_profile($host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_or_fetch_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;

    return "https://$host/api/v1/accounts/$user_id";
}

#endregion URLs
#region Content requets

function array_user_following($host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_or_fetch_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;

    $next_batch_max_id = false;

    $url = url_user_following($host, $username, $user_id);

    $following = [];
    
    while (true)
    {
        $headers = [];

        $token = app_token();

        $following_batch = array_open_url(
        
            $url, 
            "json", 
            [ "token" => $token, "timeout" => 60, "max_id" => $next_batch_max_id ],
            [ /*"file_get_contents",*/ "curl" ], 
            $headers
        );

        $links = [];
        {
            //"Link" => '<https://indieweb.social/api/v1/accounts/113384237985390849/following?max_id=862671>; rel="next", <https://indieweb.social/api/v1/accounts/113384237985390849/following?since_id=871963>; rel="prev"';

            $links_header = explode(",", at($headers, "link"));

            foreach ($links_header as &$link)
            {
                $link = explode(";", $link);
                if (count($link) < 2) continue;
                $link[0] = trim(trim($link[0]), "><");
                $link[1] = explode("=", $link[1]);
                $link[1] = trim($link[1][1], '"');
                $links[$link[1]] = $link[0];
            }            
        }

        if (count($following_batch) <= 0) break;

        $min_id = PHP_INT_MAX;

        foreach ($following_batch as $followed_account)
        {
            $id = at($followed_account, "id", PHP_INT_MAX);
            if ($id < $min_id) $min_id = $id;

            $following[] = $followed_account;
        }
        
        $url = at($links, "next");
        if (!$url) break;        
    }

    return $following;
}

function array_user_statuses($host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_or_fetch_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;
    
    $token = app_token();

    return array_open_url(
    
        url_user_statuses($host, $username, $user_id), 
        "json", 
        [ "token" => $token, "timeout" => 60 ]
    );
}

function array_user_profile($host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_or_fetch_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;
    
    $token = app_token();

    return array_open_url(
    
        url_user_profile($host, $username, $user_id), 
        "json", 
        [ "token" => $token, "timeout" => 60 ]
    );
}

#endregion Content requets
#region Multi content requets

function multi_array_user_account(&$host_username_list)
{
    $urls = [];

    foreach ($host_username_list as $host_username)
    {
        list($host, $username) = array_values($host_username);
        list($host, $username) = valid_host_username($host, $username);    

        $urls["$username@$host"] = "https://$host/api/v1/accounts/lookup?acct=$username";
    }

    $token = app_token();

    $contents = multi_fetch($urls, null, null, null, null,

        [ "token" => $token, "timeout" => 7 ]
    );

    foreach ($contents as $username_at_host => $content)
    {
        list($account_username, $account_host) = explode("@", $username_at_host);
        $account = array_open_url_content_post_process($content, "json");

        foreach ($host_username_list as &$host_username)
        {
            list($host, $username) = array_values($host_username);
            
            if ($account_host     == $host
            &&  $account_username == $username)
            {
                $host_username["account"]   = $account;
                $host_username["fetch_url"] = "https://$host/api/v1/accounts/lookup?acct=$username";
            }
        }
    }

    return true;
}

function multi_array_user_following(&$host_username_userid_list)
{
    if (!multi_array_user_account($host_username_userid_list))
    {
        return false;
    }

    $urls = [];

    foreach ($host_username_userid_list as $host_username_userid)
    {
        list($host, $username, $user_id) = [ 
            at($host_username_userid, "host",       at($host_username_userid, 0, false)),
            at($host_username_userid, "username",   at($host_username_userid, 1, false)),
            at($host_username_userid, "user_id",    at($host_username_userid, 2, false)),
        ];

        if (!$user_id)
        {
            $user_id = at(at($host_username_userid, "account"), "id");
        }

        list($host, $username, $user_id) = valid_or_fetch_host_username_userid($host, $username, $user_id);    

        $urls["$user_id@$username@$host"] = "https://$host/api/v1/accounts/$user_id/following";
    }

    $token = app_token();

    $contents = multi_fetch($urls, null, null, null, null,

        [ "token" => $token, "timeout" => 7 ]
    );

    foreach ($contents as $userid_at_username_at_host => $content)
    {
        list($account_userid, $account_username, $account_host) = explode("@", $userid_at_username_at_host);
        $following = array_open_url_content_post_process($content, "json");

        foreach ($host_username_userid_list as &$host_username_userid)
        {
            list($host, $username, $user_id) = [ 
                at($host_username_userid, "host",       at($host_username_userid, 0, false)),
                at($host_username_userid, "username",   at($host_username_userid, 1, false)),
                at($host_username_userid, "user_id",    at($host_username_userid, 2, false)),
            ];
            
            if ($account_host     == $host
            &&  $account_username == $username
            &&  $account_userid   == $user_id)
            {
                $host_username_userid["following"]   = $following;
                $host_username_userid["fetch_url"] = "https://$host/api/v1/accounts/$user_id/following";
            }
        }
    }

    return true;
}

#endregion Multi content requets
#region Components: Comments

function comment_card(
    
    $instance, 
    
    $status_account_avatar, 
    $status_account_avatar_static, 
    $status_account_username, 
    $status_account_display_name,
    $status_account_url, 
    
    $is_op,
    $is_verified,
    
    $status_id,
    $status_url,
    $status_content,
    $status_sensitive, 
    $status_spoiler_text,
    $status_favourites_count, 
    $status_reblogs_count,
    $status_created_at,
    $status_edited_at = false,

    $filled_with_placeholders = false
    
    )
{
    $attachments    = ""; /*
    let attachments = status.media_attachments;
    
    if (attachments && Array.isArray(attachments) && attachments.length > 0)
    {
        attachments.forEach((attachment) => {
        if( SUPPORTED_MEDIA.includes(attachment.type) ){
            let media = document.createElement("a");
            media.className = "comment-media";
            media.setAttribute("target", "_blank");
            media.setAttribute("href", attachment.url);
            media.setAttribute("rel", "external nofollow");

            let mediaElement;
            switch(attachment.type){
            case "image":
                mediaElement = document.createElement("img");
                mediaElement.setAttribute("src", attachment.preview_url);

                if(attachment.description != null) {
                mediaElement.setAttribute("alt", attachment.description);
                mediaElement.setAttribute("title", attachment.description);
                }

                media.appendChild(mediaElement);
                break;

            case "gifv":
                mediaElement = document.createElement("video");
                mediaElement.setAttribute("src", attachment.url);
                mediaElement.setAttribute("autoplay", "");
                mediaElement.setAttribute("playsinline", "");
                mediaElement.setAttribute("loop", "");

                if(attachment.description != null) {
                mediaElement.setAttribute("aria-title", attachment.description);
                mediaElement.setAttribute("title", attachment.description);
                }

                media.appendChild(mediaElement);
                break;
            }

        }
        });


    } 
    else if (status.card != null && status.card.image != null && !status.card.url.startsWith("<?= \dom\url() ?>"))
    {
        let cardImg = document.createElement("img");
        cardImg.setAttribute("src", status.card.image);

        let cardTitle = document.createElement("h5");
        cardTitle.innerHTML = status.card.title;

        let cardDescription = document.createElement("p");
        cardDescription.innerHTML = status.card.description;

        let cardCaption = document.createElement("figcaption");
        cardCaption.appendChild(cardTitle);
        cardCaption.appendChild(cardDescription);

        let cardFigure = document.createElement("figure");
        cardFigure.appendChild(cardImg);
        cardFigure.appendChild(cardCaption);

        let card = document.createElement("a");
        card.className = "card";
        card.setAttribute("target", "_blank");
        card.setAttribute("href", status.card.url);
        card.setAttribute("rel", "external nofollow");
        card.appendChild(cardFigure);

        $attachments .= $card;

    } */

    $minify = get("minify");
    set("minify", true);

    $html = article(

        header(
            a(  
                picture(
                    source($status_account_avatar).
                    img(
                        $status_account_avatar_static, 
                        64, 64, 
                        false, 
                        "@$status_account_username@$instance avatar", 
                        $lazy                           = auto, 
                        $lazy_src                       = auto, 
                        $content                        = auto, 
                        $precompute_size                = auto, 
                        $src_attribute                  = auto, 
                        $preload_if_among_first_images  = !$filled_with_placeholders)
                    ),
                $status_account_url, 
                [ 
                    "class"  => (($is_op ? "op " : ($is_verified ? "verified " : ""))."photo"), 
                    "title"  => (($is_op ? "Blog post author; " : "")."View profile at @$status_account_username@$instance".($is_op ? "" : ($is_verified ? " (verified by site owner)" : ""))),
                    "target" => external_link
                ]
                ).

            ul(
                li(span(
                    $status_account_display_name, 
                    [ "class" => "name", "itemprop" => "author", "itemtype" => "http://schema.org/Person" ]
                    )).

                li(a(
                    $instance,
                    $status_account_url,
                    [
                        "class"  => (($is_op ? "op " : ($is_verified ? "verified " : ""))."instance"), 
                        "title"  => (($is_op ? "Blog post author: " : "")."@$status_account_username@$instance".($is_op ? "" : ($is_verified ? " (verified by site owner)" : ""))),
                        "target" => external_link
                    ]
                    )).  
                /*
                li(time_datepublished(
                    a($status_created_at, $status_url, [ "itemprop" => "url", "title" => "View comment at $instance" ]), 
                    $status_created_at, 
                    !$status_edited_at ? false : [ "title" => "Edited $status_edited_at" ]
                    )).*/

                "", "meta").
                
            "", "author").
        
        main(
            //details(summary($status_spoiler_text != "" ? $status_spoiler_text : "Sensitive").$status_content). 
            $status_content.
            "",
            [ "itemprop" => "text" ]
            ).
    
        section(
            $attachments
            ).

        footer(
            
            ul( li(span("Faves: ",  "hidden").span($status_favourites_count, [ "class" => "faves",  "title" => "Favorites" ])).
                li(span("boosts: ", "hidden").span($status_reblogs_count,    [ "class" => "boosts", "title" => "Boosts"    ])),
                "metrics")
            ), 
        
        [ 
            "id"        => "comment-$status_id",
            "class"     => ("mastodon-comment comment card".($is_op ? " op" : "").($is_verified ? " verified" : "")), 
            "itemprop"  => "comment",
            "itemtype"  => "http://schema.org/Comment" 
        ]);
        
    set("minify", $minify);
    return $html;
}

function css_comments()
{
    HSTART() ?><style><?= HERE() ?>

        a.photo picture {
            border-radius: 50%;
            width: 48px;
            height: 48px;
            overflow: hidden;
        }

        a.instance.op:before {
            display: inline-block;
            content: "★";
            margin-right: .5em;
        }

        .mastodon-comment figure img {

            max-height: 120px;
        }

        .mastodon-comment :is(header, footer) {
            
            --gap: min(2vw, 1em);
            padding: var(--gap);
            white-space: nowrap;
            overflow: hidden;
        }

        .mastodon-comments-wrapper {

            display:        flex;
            flex-direction: column;
            gap:            var(--gap);
            margin-block:   var(--gap);
        }

        .mastodon-comment :is(header, footer) > *   { margin: 0 }

        .mastodon-comment .author                   { display: flex; align-items: center; gap: var(--gap) }
        .mastodon-comment .author .meta             { display: flex; align-items: center; gap: var(--gap); list-style: none; padding-inline-start: 0; }

        .mastodon-comment .metrics                  { display: flex; align-items: center; gap: var(--gap); list-style: none; padding-inline-start: 0; }
        .mastodon-comment .metrics .faves:before    { content: "♥ "; }
        .mastodon-comment .metrics .boosts:before   { content: "↑↓ "; }

    <?= HERE("raw_css") ?></style><?php return HSTOP();
}

function js_comments($post_id, $host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_or_fetch_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return "";

    $token = "";

    $api_post_statuses  = url_post_statuses($post_id, $host, $username);
    $api_post_context   = url_post_context($post_id, $host, $username);

    HSTART() ?><script><?= HERE() ?>

        function escapeHtml(unsafe)
        {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")/*
                .replace(/'/g, "&#039;")*/
                .replace(/'/g, "&apos;");
        }

        function emojify(input, emojis)
        {
            let output = input;

            emojis.forEach(emoji => {

                let picture = document.createElement("picture");

                let source = document.createElement("source");
                source.setAttribute("srcset", escapeHtml(emoji.url));
                source.setAttribute("media", "(prefers-reduced-motion: no-preference)");

                let img = document.createElement("img");
                img.className = "emoji";
                img.setAttribute("src", escapeHtml(emoji.static_url));
                img.setAttribute("alt", `:${ emoji.shortcode }:`);
                img.setAttribute("title", `:${ emoji.shortcode }:`);
                img.setAttribute("width", "20");
                img.setAttribute("height", "20");

                picture.appendChild(source);
                picture.appendChild(img);

                output = output.replace(`:${ emoji.shortcode }:`, picture.outerHTML);
            });

            return output;
        }
        
        function loadComments()
        {
            const HOST            = "<?= $host ?>";
            const DOMAIN          = "<?= $host ?>";
            const USERNAME        = "<?= $username ?>";
            const TOKEN           = "<?= $token ?>";
            const ID              = "<?= $post_id ?>";
            const VERIFIED        = [];
            const SUPPORTED_MEDIA = [ "image", "gifv" ];
            /*
            const STATUS_REQUEST  = "<?= $api_post_statuses ?>";
            const CONTEXT_REQUEST = "<?= $api_post_context  ?>"; */

            const REQUEST_HEADERS = new Headers(); if(TOKEN != "") { REQUEST_HEADERS.append("Authorization", "Bearer " + TOKEN); } 
            const REQUEST_OPTIONS = { method: "GET", headers: REQUEST_HEADERS, mode: "cors", cache: "default" }; 

            console.log("DOM", "MASTODON", "REQUEST_OPTIONS", REQUEST_OPTIONS);

            const STATUS_REQUEST  = new Request("<?= $api_post_statuses ?>", REQUEST_OPTIONS);
            const CONTEXT_REQUEST = new Request("<?= $api_post_context  ?>", REQUEST_OPTIONS); 

            let commentsWrapper = document.getElementById("mastodon-comments-wrapper");

            fetch(STATUS_REQUEST  ).then((response) => { return response.json(); }).then((status) => {
            fetch(CONTEXT_REQUEST ).then((response) => { return response.json(); }).then((data)   => {

                console.log("DOM", "MASTODON COMMENTS", CONTEXT_REQUEST, status, data);

                let descendants = data['descendants'];

                if (descendants && Array.isArray(descendants) && descendants.length > 0)
                {
                    commentsWrapper.innerHTML = "";
                    descendants.unshift(status);

                    descendants.forEach((status) => {

                        let instance = (status.account.acct.includes("@")) ? status.account.acct.split("@")[1] : DOMAIN;

                        if (status.account.display_name.length > 0)
                        {
                            status.account.display_name = emojify(escapeHtml(status.account.display_name), status.account.emojis);
                        }
                        else 
                        {
                            status.account.display_name = status.account.username;
                        }

                        status.content = emojify(status.content, status.emojis);

                        /*let attachments = status.media_attachments;

                        if (attachments && Array.isArray(attachments) && attachments.length > 0) 
                        {
                            attachments.forEach((attachment) => {
                            if( SUPPORTED_MEDIA.includes(attachment.type) ){
                                let media = document.createElement("a");
                                media.className = "comment-media";
                                media.setAttribute("target", "_blank");
                                media.setAttribute("href", attachment.url);
                                media.setAttribute("rel", "external nofollow");

                                let mediaElement;
                                switch(attachment.type){
                                case "image":
                                    mediaElement = document.createElement("img");
                                    mediaElement.setAttribute("src", attachment.preview_url);

                                    if(attachment.description != null) {
                                    mediaElement.setAttribute("alt", attachment.description);
                                    mediaElement.setAttribute("title", attachment.description);
                                    }

                                    media.appendChild(mediaElement);
                                    break;

                                case "gifv":
                                    mediaElement = document.createElement("video");
                                    mediaElement.setAttribute("src", attachment.url);
                                    mediaElement.setAttribute("autoplay", "");
                                    mediaElement.setAttribute("playsinline", "");
                                    mediaElement.setAttribute("loop", "");

                                    if(attachment.description != null) {
                                    mediaElement.setAttribute("aria-title", attachment.description);
                                    mediaElement.setAttribute("title", attachment.description);
                                    }

                                    media.appendChild(mediaElement);
                                    break;
                                }

                                comment.appendChild(media);
                            }
                            });

                        }
                        else if (status.card != null && status.card.image != null && !status.card.url.startsWith("<?= \dom\url() ?>")) 
                        {
                            let cardImg = document.createElement("img");
                            cardImg.setAttribute("src", status.card.image);

                            let cardTitle = document.createElement("h5");
                            cardTitle.innerHTML = status.card.title;

                            let cardDescription = document.createElement("p");
                            cardDescription.innerHTML = status.card.description;

                            let cardCaption = document.createElement("figcaption");
                            cardCaption.appendChild(cardTitle);
                            cardCaption.appendChild(cardDescription);

                            let cardFigure = document.createElement("figure");
                            cardFigure.appendChild(cardImg);
                            cardFigure.appendChild(cardCaption);

                            let card = document.createElement("a");
                            card.className = "card";
                            card.setAttribute("target", "_blank");
                            card.setAttribute("href", status.card.url);
                            card.setAttribute("rel", "external nofollow");
                            card.appendChild(cardFigure);

                            comment.appendChild(card);
                        }*/

                        var is_op       = (status.account.acct == USERNAME);
                        var is_verified = (VERIFIED.includes(status.account.acct));
                        
                        var comment = '';
                        {
                            comment = `<?= comment_card(
                                
                                '$instance', 
                                
                                '$status.account.avatar', 
                                '$status.account.avatar_static', 
                                '$status.account.username', 
                                '$status.account.display_name',
                                '$status.account.url', 
                                
                                '$is_op',
                                '$is_verified',
                                
                                '$status.id',
                                '$status.url',
                                '$status.content',
                                '$status.sensitive', 
                                '$status.spoiler_text',
                                '$status.favourites_count', 
                                '$status.reblogs_count',
                                '$status.created_at',
                                '$status.edited_at',

                                true

                                ) ?>`.trim();

                            comment = comment.replaceAll('$instance',                       instance);

                            comment = comment.replaceAll('$status.account.avatar_static',   status.account.avatar_static);
                            comment = comment.replaceAll('$status.account.avatar',          status.account.avatar);
                            comment = comment.replaceAll('$status.account.username',        status.account.username);
                            comment = comment.replaceAll('$status.account.display_name',    status.account.display_name);
                            comment = comment.replaceAll('$status.account.url',             status.account.url);

                            comment = comment.replaceAll('$is_op',                          is_op);
                            comment = comment.replaceAll('$is_verified',                    is_verified);

                            comment = comment.replaceAll('$status.id',                      status.id);
                            comment = comment.replaceAll('$status.url',                     status.url);
                            comment = comment.replaceAll('$status.content',                 status.content);
                            comment = comment.replaceAll('$status.sensitive',               status.sensitive);
                            comment = comment.replaceAll('$status.spoiler_text',            status.spoiler_text);
                            comment = comment.replaceAll('$status.favourites_count',        status.favourites_count);
                            comment = comment.replaceAll('$status.reblogs_count',           status.reblogs_count);
                            comment = comment.replaceAll('$status.created_at',              status.created_at);
                            comment = comment.replaceAll('$status.edited_at',               status.edited_at);
                        }

                        commentsWrapper.innerHTML += comment;
                    });
                }

            }); });
        }

        loadComments();

    <?= HERE("raw_js") ?></script><?php return HSTOP();
}

function section_mastodon_comments($post_id = auto, $host = false, $username = false, $user_id = false)
{
    if (!!get("no_js"))
    {
        return "";
    }

    if (auto === $post_id)
    {
        $post_id = get("mastodon-post-id", get("mastodon-post"));
    }

    if (is_array($post_id))
    {
        $post_info = $post_id;

        if (count($post_info) > 0) $post_id   = array_shift($post_info);
        if (count($post_info) > 0) $host      = array_shift($post_info);

        $host = trim($host, "@");
        if (false !== stripos($host, "@")) list($username, $host) = explode("@", $host);

        if (count($post_info) > 0) $username  = array_shift($post_info);
    }

    list($host, $username, $user_id) = valid_or_fetch_host_username_userid($host, $username, $user_id);

    if (!$host || !$username || !$user_id) return "";
    
    $post_url = url_post($post_id, $host, $username);

    return
        style(css_comments()).
        section(
            p("Comment on this blog post by publicly replying to ".a("this Mastodon post", $post_url)." using a Mastodon or other ActivityPub/Fediverse account. Known non-private replies are displayed below.").
            div(
                p("No known comments, yet. Reply to ".a("this Mastodon post", $post_url)." to add your own!").
                noscript(p("Loading comments relies on JavaScript. Try enabling JavaScript and reloading, or visit ".a("the original post", $post_url)." on Mastodon.")), 
                [ "id" => "mastodon-comments-wrapper", "class" => "mastodon-comments-wrapper" ]),
            [ "id" => "mastodon-comments", "class" => "mastodon-comments requires-js" ]).
        script(js_comments($post_id, $host, $username, $user_id));
}

#endregion Components: Comments
#region Components: Auto-publish

function live_permalink()
{
    $permalink = get("canonical");

    $permalink = rtrim(str_replace("https://localhost",         "http://localhost", $permalink), "/");
    $permalink = rtrim(str_replace("https://127.0.0.1",         "http://127.0.0.1", $permalink), "/");
    $permalink = rtrim(str_replace("www.".get("local_domain"),  get("live_domain"), $permalink), "/");
    $permalink = rtrim(str_replace(       get("local_domain"),  get("live_domain"), $permalink), "/");
    $permalink = rtrim(str_replace("http://localhost/",         "https://",         $permalink), "/");
    $permalink = rtrim(str_replace("http://localhost",          "https://",         $permalink), "/");
    $permalink = rtrim(str_replace("http://127.0.0.1/",         "https://",         $permalink), "/");
    $permalink = rtrim(str_replace("http://127.0.0.1",          "https://",         $permalink), "/");

    return $permalink;
}

function corresponding_post($permalink)
{
    $permalink_php    = trim(str_replace(get("static_domain"), get("php_domain"), $permalink), "/ \n\r\t\v\0");
    $permalink_static = trim(str_replace(get("php_domain"), get("static_domain"), $permalink), "/ \n\r\t\v\0");

    $contents = [];

    foreach (get("mastodon_recipes", []) as $mastodon_recipe)
    {
        $username = at($mastodon_recipe, "user");
        $host     = at($mastodon_recipe, "domain");

        $statuses = array_user_statuses($host, $username);

        $contents[$host] = [ /*"statuses" => $statuses*/ ];

        foreach ($statuses as $post)
        {
            $reblog = at($post, "reblog", []);
            if (!!$reblog && @count($reblog) > 0) continue;

            $contents[$host][] = htmlentities(strip_tags(at($post, "content")));

          //if (false != stripos(at($post, "content"), html_read_complete_article($permalink_static))) // Better a desambiguating articles but not tolerant to rewording of the footer link + not tolerant to i18n
            if (false != stripos(at($post, "content"), $permalink_static)) // Not good at desambiguating articles
            {
                return $post;
            }
            
          //if (false != stripos(at($post, "content"), html_read_complete_article($permalink_php))) // Better a desambiguating articles but not tolerant to rewording of the footer link + not tolerant to i18n
            if (false != stripos(at($post, "content"), $permalink_php)) // Not good at desambiguating articles
            {
                return $post;
            }
        }
    }

    return false;
}

function excerpt($html)
{
    set("mastodon/excerpt", $html);
    return $html;
}

function href_complete_article($permalink = false)
{
    return !$permalink ? live_permalink() : $permalink;
}

function a_read_complete_article($permalink = false)
{
    return a("complete article here", href_complete_article($permalink), [ "data-article" => "corresponding" ]);
}

function text_read_complete_article($permalink = false)
{
    return "Read complete article here: ".href_complete_article($permalink);
}

function html_read_complete_article($permalink = false)
{
    return "Read ".a_read_complete_article($permalink);
}

function p_read_complete_article($permalink = false)
{
    return p(html_read_complete_article($permalink));
}

function post_excerpt($visibility = auto /* private | public | unlisted | private | direct */, $text_limit = 500)
{
    $visibility = auto === $visibility ? "private" : $visibility;

    $mastodon_villapirorum_app_id       = app_id();
    $mastodon_villapirorum_app_secret   = app_secret();
    $mastodon_villapirorum_app_token    = app_token(); 

    $api_url = "https://".get("mastodon_domain", "mastodon.social");

    $body = "";

    $excerpt = get("mastodon/excerpt"); del("mastodon/excerpt");
    $link    = p_read_complete_article();
    $body    = $excerpt.PHP_EOL.$link;

    if (mb_strlen($body) > $text_limit)
    {    
        $excerpt = strip_tags($excerpt);
        $link    = text_read_complete_article();
        $body    = $excerpt.PHP_EOL.$link;
    }

    if (mb_strlen($body) > $text_limit)
    {    
        $excerpt = mb_substr($excerpt, 0, $text_limit - 3/*...*/ - 2/*PHP_EOL*/ - mb_strlen($link))."...";
        $body    = $excerpt.PHP_EOL.$link;
    }

    if (mb_strlen($body) > $text_limit)
    {
        $link = href_complete_article();
        $body = $excerpt.PHP_EOL.$link;
    }

    $code  = null;
    $error = null;
    $error_details = [];

    $response = \dom\post($api_url, "api/v1/statuses", 

        [
            "status"            => strip_tags($body),
            "media_ids"         => [],
            "visibility"        => $visibility
        ], 

        [            
            'Content-Type'      => "application/x-www-form-urlencoded",
            'Authorization'     => "Bearer $mastodon_villapirorum_app_token",
            "Idempotency-Key"   => live_permalink()
        ],
            
        "POST", false, false, "DOM", $code, $error, $error_details
        );

    if (is_string($response) && strlen($response) > 2 && ($response[0] == '{' || $response[0] == '['))
    {
        $response = json_decode($response, true);
    }

    return [
        
        "status"    => $response,
        "code"      => (int)$code, 
        "error"     => $error, 
        "details"   => \dom\is_localhost() ? $error_details : null
    
        ];
}

function article_excerpt_autopost_and_comments($visibility = auto /* private | public | unlisted | private | direct */) 
{ 
    // TODO. Solve Logic problem
    // When the workflow is compile php -> push compile static version live
    // The it needs to be double-posted in order to work

    // TODO. Go all javascript when static site ?

    $live_permalink     = live_permalink();
    $corresponding_post = corresponding_post($live_permalink);

    $html = "";

    if (!!$corresponding_post)
    {
      //$html =         header(p("Comments")).         section_comments_cards(   at($corresponding_post, "id"));
      //$html =                                        section_mastodon_comments(at($corresponding_post, "id"));
      //$html = article(header(p("Mastodon comments")).section_mastodon_comments(at($corresponding_post, "id")));

        set("mastodon-post", at($corresponding_post, "id"));
    }
    else if (!get("static"))
    {
        if (!get("mastodon/excerpt"))
        {
            $html = p("This article will be automatically posted on the Fediverse once an excerpt has been defined");
        }
        else if (!\dom\url_exists($live_permalink))
        {
            $html = p("This article will be automatically posted on the Fediverse as soon as it goes live at ".a($live_permalink));
        }
        else 
        {
            $html = p("Posted $live_permalink to Fediverse...").

                    ((function($post_response) {

                        $html = "";

                        $post_id    = at(at($post_response, "status"), "id");
                        $created_at = at(at($post_response, "status"), "created_at");

                        if (!!$post_id && !!$created_at)
                        {
                            set("mastodon-post", $post_id);

                            $html .= p("Post #$post_id");
                        }

                        $html .= \dom\pre(htmlentities(json_encode($post_response, JSON_PRETTY_PRINT)));

                        return $html;

                        })(post_excerpt($visibility)))
                ;
        }
    }

    return \dom\article($html);
}

#endregion Components: Autopublish

function url_lookup($webfinger, $host = false, $username = false)
{
    list($host, $username) = valid_host_username($host, $username);
    if (!$host || !$webfinger) return false;

    return "https://$host/api/v1/accounts/lookup/?acct=$webfinger";
}

function lookup($webfinger, $token = auto, $timeout = 7, &$debug_error_output = null, $host = false, $username = false)
{
    return api(url_lookup($webfinger, $host, $username), $token, $timeout, $debug_error_output);
}

function api($url, $token = auto, $timeout = 7, &$debug_error_output = null)
{
    $token = auto === $token ? constant("TOKEN_MASTODON") : $token;

    return json_decode(\dom\content(

        $url, 
        [ "timeout" => $timeout, "header" => [ "Authorization" => $token ] ], 
        /*auto_fix*/false, 
        $debug_error_output, 
        /*methods_order*/[ "curl" ]

    ), true);
}



