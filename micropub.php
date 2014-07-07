<?php
require("lib/common.php");
require("lib/microformat.php");
require("lib/auth.php");
require("lib/webmention.php");

function generateSlug($name, $published) {
    $datepart = date("YmdHi", strtotime($published));
    if ($name == null)
        return $datepart;
    $namepart = strtolower($name);
    $namepart = preg_replace("/[^a-z0-9 ]+/", "", $namepart);
    $namepart = preg_replace("/ +/", "-", $namepart);
    return "$datepart-$namepart";
}

function autoLink($content) {
    return preg_replace("~\b((https?://)?[\w-]*[a-z][\w-]*(\.[\w-]+)+(/[\w\./%+?=&#\~-]+)?)\b~i", "<a href=\"$1\">$1</a>", $content);
}

requireAuthorization($config, "post");

$h = getRequiredPost("h");
if ($h !== "entry")
    do400("Unsupported object type: '$h'");

$feed = new Microformat\LocalFeed("postindex.json");
$post = new Microformat\Entry();
$post->authorName = $config["authorName"];
$post->authorPhoto = $config["authorPhoto"];
$post->authorUrl = $config["siteUrl"];
$post->name = getOptionalPost("name");
$content = getOptionalPost("content");
if ($content !== null)
    $content = autoLink($content);
$post->contentHtml = $content;
$post->contentValue = $content;
$post->published = getOptionalPost("published");
if ($post->published === null)
    $post->published = date("c");

$syndication = getOptionalPost("syndication");
if ($syndication !== null) {
    if (is_array($syndication))
        $post->syndication = $syndication;
    else
        $post->syndication = array($syndication);
}

$replyto = getOptionalPost("in-reply-to");
if ($replyto != null) {
    $html = fetchPage($replyto);
    $replyCite = new Microformat\Entry("cite", array("in-reply-to"));
    $replyCite->loadFromHtml($html, $replyto);
    $post->replyTo[] = $replyCite;
}

$slug = $config["postRoot"] . "/" . generateSlug($post->name, $post->published);
$post->file = $slug . $config["postExtension"];
$post->url = $config["siteUrl"] . "/" . $slug;

$photo = getOptionalFile("photo");
if ($post->contentValue === null && $photo === null)
    do400("Either content or photo must be set");
if ($photo !== null) {
    $photoFile = $slug . ".jpg";
    if (!move_uploaded_file($photo["tmp_name"], $photoFile))
        throw new Exception("Failed to move upload to $photoFile");
    $post->contentHtml = "<img class=\"u-photo\" src=\"/" . $photoFile . "\">" . $post->contentHtml;
}

try {
    $post->save($config);
    $feed->add($post);
    $location = $post->url;
    do201($location);
    $syndicateTo = getOptionalPost("syndicate-to");
    if (isset($syndicateTo)) {
        if (in_array("twitter.com", $syndicateTo)) {
            echo "POSSEing to twitter via bridgy<br>";
            $response = json_decode(sendmention($location, "http://brid.gy/publish/twitter"));
            if ($response === null)
                throw new Exception("JSON decode failed");
            $post->syndication[] = $response->url;
            echo "Success: $response->url<br>";
        }
        if (in_array("facebook.com", $syndicateTo)) {
            echo "POSSEing to facebook via bridgy<br>";
            $response = json_decode(sendmention($location, "http://brid.gy/publish/facebook"));
            if ($response === null)
                throw new Exception("JSON decode failed");
            $post->syndication[] = $response->url;
            echo "Success: $response->url<br>";
        }
        $post->save($config);
    }
    foreach ($post->getLinks() as $link) {
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
