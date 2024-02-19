<?php 

namespace dom\mastodon; 

require_once(__DIR__."/../dom.php"); 
use function \dom\{get,at,array_open_url,HSTART,HERE,HSTOP};

function valid_host_username_userid($host = false, $username = false, $user_id = false)
{
    $host     = !!$host     ? $host     : "mastodon.social";
    $username = !!$username ? $username : get("author", @constant("TOKEN_MASTODON_USER"));
    $user_id  = !!$user_id  ? $user_id  : at(array_open_url("https://$host/api/v1/accounts/lookup?acct=$username"), "id");

    return [ $host, $username, $user_id ];
}

function url_post($post_id,  $host = false, $username = false)
{
    list($host, $username, $user_id) = valid_host_username_userid($host, $username, $user_id = false);
    if (!$host || !$username || !$user_id) return false;

    return "https://$host/@$username/$post_id";
}

function url_user_statuses($host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;

    return "https://$host/api/v1/accounts/$user_id/statuses";
}

function array_user_statuses($host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return false;

    $api_user_statuses = url_user_statuses($host, $username, $user_id);

    return array_open_url($api_user_statuses, "json", 60);
}

function section_comments($post_id, $host = false, $username = false, $user_id = false)
{
    list($host, $username, $user_id) = valid_host_username_userid($host, $username, $user_id);
    if (!$host || !$username || !$user_id) return "";

    $api_post_context   = "https://$host/api/v1/statuses/$post_id/context";
    $post_url           = "https://$host/@$username/$post_id";

    HSTART() ?><html><?= HERE() ?>

        <section id="mastodon-comments" class="mastodon-comments">

            <p><button id="mastodon-comments-load-comment">Load comments</button></p>

            <div id="mastodon-comments-comments-wrapper">
                <noscript><p>Loading comments relies on JavaScript.</p></noscript>
            </div>
            
            <noscript>You need JavaScript to view the comments.</noscript>

            <!-- <script src="/assets/js/purify.min.js"></script> //-->

            <script type="text/javascript" async defer>

                function escapeHtml(unsafe) {
                        
                    return unsafe
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                }

                function emojify(input, emojis) {
                        
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

                function loadComments() {

                    let commentsWrapper = document.getElementById("mastodon-comments-comments-wrapper");

                    document.getElementById("mastodon-comments-load-comment").innerHTML = "Loading...";

                    console.log("DOM", "Mastodon", "Loading comments...");

                    fetch('<?= $api_post_context ?>')
                    
                        .then(function(response) {

                            console.log("DOM", "Mastodon", "Loading comments...", "Received response", response);

                            return response.json();
                        })

                        .then(function(data) {

                            console.log("DOM", "Mastodon", "Loading comments...", "Received JSON", data);

                            let descendants = data['descendants'];

                            if (descendants && Array.isArray(descendants))
                            {
                                if (descendants.length > 0)
                                {
                                    commentsWrapper.innerHTML = "";

                                    descendants.forEach(function(status) {

                                        console.log(descendants);
                                    
                                        if (status.account.display_name.length > 0 ) {

                                            status.account.display_name = escapeHtml(status.account.display_name);
                                            status.account.display_name = emojify(status.account.display_name, status.account.emojis);
                                    
                                        } else {

                                            status.account.display_name = status.account.username;
                                        }

                                        let instance = "";

                                        if (status.account.acct.includes("@")) {

                                            instance = status.account.acct.split("@")[1];
                                    
                                        } else {

                                            instance = "<?= $host ?>";
                                        }

                                        const isReply = (status.in_reply_to_id !== "<?= $post_id ?>");

                                        let op = false;

                                        if (status.account.acct == "<?= $username ?>") {
                                        
                                            op = true;
                                        }

                                        status.content = emojify(status.content, status.emojis);

                                        let avatarSource = document.createElement("source");
                                        avatarSource.setAttribute("srcset", escapeHtml(status.account.avatar));
                                        avatarSource.setAttribute("media", "(prefers-reduced-motion: no-preference)");

                                        let avatarImg = document.createElement("img");
                                        avatarImg.className = "avatar";
                                        avatarImg.setAttribute("src", escapeHtml(status.account.avatar_static));
                                        avatarImg.setAttribute("alt", `@${ status.account.username }@${ instance } avatar`);

                                        let avatarPicture = document.createElement("picture");
                                        avatarPicture.appendChild(avatarSource);
                                        avatarPicture.appendChild(avatarImg);

                                        let avatar = document.createElement("a");
                                        avatar.className = "avatar-link";
                                        avatar.setAttribute("href", status.account.url);
                                        avatar.setAttribute("rel", "external nofollow");
                                        avatar.setAttribute("title", `View profile at @${ status.account.username }@${ instance }`);
                                        avatar.appendChild(avatarPicture);

                                        let instanceBadge = document.createElement("a");
                                        instanceBadge.className = "instance";
                                        instanceBadge.setAttribute("href", status.account.url);
                                        instanceBadge.setAttribute("title", `@${ status.account.username }@${ instance }`);
                                        instanceBadge.setAttribute("rel", "external nofollow");
                                        instanceBadge.textContent = instance;

                                        let display = document.createElement("span");
                                        display.className = "display";
                                        display.setAttribute("itemprop", "author");
                                        display.setAttribute("itemtype", "http://schema.org/Person");
                                        display.innerHTML = status.account.display_name;

                                        let header = document.createElement("header");
                                        header.className = "author";
                                        header.appendChild(display);
                                        header.appendChild(instanceBadge);

                                        let permalink = document.createElement("a");
                                        permalink.setAttribute("href", status.url);
                                        permalink.setAttribute("itemprop", "url");
                                        permalink.setAttribute("title", `View comment at ${ instance }`);
                                        permalink.setAttribute("rel", "external nofollow");
                                        permalink.textContent = new Date( status.created_at ).toLocaleString('en-US', {
                                            dateStyle: "long",
                                            timeStyle: "short",
                                        });

                                        let timestamp = document.createElement("time");
                                        timestamp.setAttribute("datetime", status.created_at);
                                        timestamp.appendChild(permalink);

                                        let main = document.createElement("main");
                                        main.setAttribute("itemprop", "text");
                                        main.innerHTML = status.content;

                                        let interactions = document.createElement("footer");
                                        if(status.favourites_count > 0) {
                                            let faves = document.createElement("a");
                                            faves.className = "faves";
                                            faves.setAttribute("href", `${ status.url }/favourites`);
                                            faves.setAttribute("title", `Favorites from ${ instance }`);
                                            faves.textContent = status.favourites_count;

                                            interactions.appendChild(faves);
                                        }

                                        let comment = document.createElement("article");
                                        comment.id = `comment-${ status.id }`;
                                        comment.className = isReply ? "comment comment-reply" : "comment";
                                        comment.setAttribute("itemprop", "comment");
                                        comment.setAttribute("itemtype", "http://schema.org/Comment");
                                        comment.appendChild(avatar);
                                        comment.appendChild(header);
                                        comment.appendChild(timestamp);
                                        comment.appendChild(main);
                                        comment.appendChild(interactions);

                                        if(op === true) {
                                            comment.classList.add("op");

                                            avatar.classList.add("op");
                                            avatar.setAttribute(
                                            "title",
                                            "Blog post author; " + avatar.getAttribute("title")
                                            );

                                            instanceBadge.classList.add("op");
                                            instanceBadge.setAttribute(
                                            "title",
                                            "Blog post author: " + instanceBadge.getAttribute("title")
                                            );
                                        }

                                        commentsWrapper.innerHTML += /*DOMPurify.sanitize*/(comment.outerHTML); /* TODO: Purify */

                                    });
                                }
                                else
                                {
                                    console.log("DOM", "Mastodon", "Loading comments...", "Received JSON", "NO COMMENT YET!");

                                    document.getElementById("mastodon-comments-load-comment").innerHTML = "Load comments";
                                    
                                    commentsWrapper.innerHTML = "<p>No comment yet!</p>";
                                }
                            }
                            else
                            {
                                console.log("DOM", "Mastodon", "Loading comments...", "Received JSON", "INVALID JSON!");

                                document.getElementById("mastodon-comments-load-comment").innerHTML = "Load comments";
                            }
                        });
                }

                document.getElementById("mastodon-comments-load-comment").addEventListener("click", loadComments);

            </script>

        </section>

    <?= HERE("raw_html") ?></html><?php return HSTOP();
}