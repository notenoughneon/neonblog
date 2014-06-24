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
$post->name = getOptionalPost("name");
$post->content = getOptionalPost("content");
$post->published = getOptionalPost("published");
if ($post->published === null)
    $post->published = date("c");

$replyto = getOptionalPost("in-reply-to");

$slug = generateSlug($name, $published);
$filebase = $config["postRoot"] . "/" . $slug;
$post->file = $filebase . $config["postExtension"];
$post->url = $config["siteUrl"] . "/" . $post->file;

$photo = getOptionalFile("photo");
if ($content === null && $photo === null)
    do400("Either content or photo must be set");
if ($photo !== null) {
    $photoFile = $filebase . ".jpg";
    if (!move_uploaded_file($photo["tmp_name"], $photoFile))
        throw new Exception("Failed to move upload to $photoFile");
    $post->content = "<img class=\"u-photo\" src=\"" . $photoFile . "\">" . $post->content;
}

try {
    $post->save($config);
    $feed->add($post);
    $location = $post->url;
    do201($location);
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
