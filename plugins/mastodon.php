<?php 

/**
 * Blog post commenting via Mastodon comments
 * 
 * Inspired by https://cassidyjames.com/blog/fediverse-blog-comments-mastodon/
 * Himselft inspired by https://codeberg.org/jwildeboer/jwildeboersource/src/commit/45f9750bb53b9f0f6f28399ce4d21785a3bb7d22/_includes/fediverse_comments.html
 */

namespace dom\mastodon; 

require_once(__DIR__."/../dom.php"); 
use function \dom\{set,get,at,array_open_url,HSTART,HERE,HSTOP,style,script,noscript,header,main,footer,section,p,a,picture,figure,source,img,span,div,time_datepublished,summary,details,article};

#region Constants

function valid_host($host = false)
{
    return !!$host ? $host : trim(get("mastodon_domain", "mastodon.social"), "@");
}

function valid_username($username = false)
{
    return !!$username ? $username : trim(get("mastodon_author", get("mastodon_user", defined("TOKEN_MASTODON_USER") ? constant("TOKEN_MASTODON_USER") : get("author"))), "@");
}

function valid_userid($host = false, $username = false, $user_id = false)
{
    list($host, $username) = valid_host_username($host, $username);
    return !!$user_id ? $user_id : at(array_open_url("https://$host/api/v1/accounts/lookup?acct=$username"), "id");
}

function valid_host_username($host = false, $username = false)
{
    $host     = !!$host     ? $host     : trim(get("mastodon_domain", "mastodon.social"), "@");
    $username = !!$username ? $username : trim(get("mastodon_author", get("mastodon_user", defined("TOKEN_MASTODON_USER") ? constant("TOKEN_MASTODON_USER") : get("author"))), "@");

    return [ $host, $username ];
}

function valid_host_username_userid($host = false, $username = false, $user_id = false)
{
    list($host, $username) = valid_host_username($host, $username);
    $user_id = !!$user_id ? $user_id : at(array_open_url("https://$host/api/v1/accounts/lookup?acct=$username"), "id");
    return [ $host, $username, $user_id ];
}

#endregion constants
#region URLs

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
    list($host, $username, $user_id) = valid_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;

    return "https://$host/api/v1/accounts/$user_id/statuses";
}

#endregion URLs
#region Content requets

function array_user_statuses($host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;
    return array_open_url(url_user_statuses($host, $username, $user_id), "json", 60/*seconds*/);
}

#endregion Content requets
#region Components

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
    $status_edited_at = false
    
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
                    img($status_account_avatar_static, 64, 64, false, "@$status_account_username@$instance avatar")
                    ),
                $status_account_url, 
                [ 
                    "class"  => (($is_op ? "op " : ($is_verified ? "verified " : ""))."photo"), 
                    "title"  => (($is_op ? "Blog post author; " : "")."View profile at @$status_account_username@$instance".($is_op ? "" : ($is_verified ? " (verified by site owner)" : ""))),
                    "target" => DOM_EXTERNAL_LINK
                ]
                ).

            span(
                $status_account_display_name, 
                [ "class" => "name", "itemprop" => "author", "itemtype" => "http://schema.org/Person" ]
                ).

            a(
                $instance,
                $status_account_url,
                [
                    "class"  => (($is_op ? "op " : ($is_verified ? "verified " : ""))."instance"), 
                    "title"  => (($is_op ? "Blog post author: " : "")."@$status_account_username@$instance".($is_op ? "" : ($is_verified ? " (verified by site owner)" : ""))),
                    "target" => DOM_EXTERNAL_LINK
                ]
                )./*

            time_datepublished(
                a($status_created_at, $status_url, [ "itemprop" => "url", "title" => "View comment at $instance" ]), 
                $status_created_at, 
                !$status_edited_at ? false : [ "title" => "Edited $status_edited_at" ]
                ).*/
                
            "", 
            
            [ "class" => "author", "style" => "display: flex; align-items: center" ]

            ).
        
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
            
            span($status_favourites_count, [ "class" => "faves",  "title" => "Favorites" ]).
            span($status_reblogs_count,    [ "class" => "boosts", "title" => "Boosts" ])
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

            padding: var(--gap);
        }

        .mastodon-comments-wrapper {

            display:        flex;
            flex-direction: column;
            gap:            var(--gap);
            margin-block:   var(--gap);
        }

        .mastodon-comment .faves:before  { content: "♥ "; }
        .mastodon-comment .boosts:before { content: "↑↓ "; }

    <?= HERE("raw_css") ?></style><?php return HSTOP();
}

function js_comments($post_id, $host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_host_username_userid($host, $username, $user_id);
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
                                '$status.edited_at'

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

function section_comments_cards($post_id, $host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_host_username_userid($host, $username, $user_id);
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
            [ "id" => "mastodon-comments", "class" => "mastodon-comments" ]).
        script(js_comments($post_id, $host, $username, $user_id));
}

#endregion Components
