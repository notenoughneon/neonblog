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
    $namepart = preg_replace("/ +", "-", $namepart);
    return "$datepart-$namepart";
}

requireAuthorization($config, "post");

$h = getRequiredPost("h");
if ($h !== "entry")
    do400("Unsupported object type: '$h'");

$feed = new Microformat\LocalFeed("postindex.json");
$post = new Microformat\Entry();
$post->authorName = $config["aboutName"];
$post->authorPhoto = $config["aboutPhoto"];
$post->authorUrl = $config["siteUrl"];
$post->name = getOptionalPost("name");
$content = getOptionalPost("content");
$post->contentHtml = htmlspecialchars($content);
$post->contentValue = $content;
$post->published = getOptionalPost("published");
if ($post->published === null)
    $post->published = date("c");

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
    $links = $post->getLinks();
    $syndications = getOptionalPost("syndicate-to");
    if (isset($syndications)) {
        if (in_array("twitter.com", $syndications))
            $links[] = "http://brid.gy/publish/twitter";
        if (in_array("facebook.com", $syndications))
            $links[] = "http://brid.gy/publish/facebook";
    }
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
