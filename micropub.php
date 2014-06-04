<?php
require("lib/common.php");
require("lib/auth.php");
require("lib/dom.php");
require("lib/webmention.php");
require("lib/twitter.php");
require("twitteroauth/twitteroauth.php");

function links($html) {
    $links = array();
    $doc = new DOMDocument();
    if (!@$doc->loadHTML($html))
        return $links;
    foreach ($doc->getElementsByTagName("a") as $a)
        $links[] = $a->getAttribute("href");
    return $links;
}

requireAuthorization($config, "post");

$h = getRequiredPost("h");
if ($h !== "entry")
    do400("Unsupported object type: '$h'");
$name = getOptionalPost("name");
$replyto = getOptionalPost("in-reply-to");
$content = getOptionalPost("content");
$published = getOptionalPost("published");
if ($published === null)
    $published = date("c");
$syndicateto = getOptionalPost("syndicate-to");
$photo = getOptionalFile("photo");
if ($content === null && $photo === null)
    do400("Either content or photo must be set");
$slug = generateSlug($name, $published);

try {
    $syndication = array();
    if ($syndicateto == "twitter.com") {
        $url = $config["siteUrl"] . "/" . $config["postRoot"] . "/" . $slug;
        $tweet = TwitterElide\format($name, $content, $url);
        $keys = $config["twitter"];
        $twitter = new TwitterOAuth($keys["apiKey"],
                                    $keys["apiSecret"],
                                    $keys["accessToken"],
                                    $keys["accessSecret"]);
        echo "Sending tweet...<br>";
        $response = $twitter->post("statuses/update", array("status" => $tweet));
        $tweetUrl = "https://twitter.com/" . $response->user->screen_name . "/status/" . $response->id_str;
        echo "Success: location = $tweetUrl";
        $syndication[] = $tweetUrl;
    }

    if ($photo !== null)
        $location = createPhoto($config, $slug, $published, $photo, $content);
    else if ($name === null)
        $location = createNote($config, $slug, $replyto, $published, $content, $syndication);
    else
        $location = createArticle($config, $slug, $replyto, $name, $published, $content, $syndication);
    do201($location);
    $links = array();
    if ($replyto !== null)
        $links[] = $replyto;
    if ($content !== null)
        $links = array_merge($links, links($content));
    foreach ($links as $link) {
        try {
            echo "Sending webmention: $location -&gt; $link<br>";
            sendmention($location, $link);
            echo "Success<br>";
        } catch (Exception $e) {
            echo "Failed: " . $e->getMessage() . "<br>";
        }
    }
} catch (Exception $e) {
    do500($e->getMessage());
}

?>
