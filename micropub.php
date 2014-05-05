<?php
require("lib/common.php");
require("lib/auth.php");
require("lib/dom.php");

function links($html) {
    $links = array();
    $doc = new DOMDocument();
    if (!@$doc->loadHTML($html))
        return $links;
    foreach ($doc->getElementsByTagName("a") as $a)
        $links[] = $a->getAttribute("href");
    return $links;
}

function discoverWebmention($html) {
    $doc = new DOMDocument();
    if (!@$doc->loadHTML($html))
        return false;
    foreach ($doc->getElementsByTagName("link") as $link) {
        $rels = explode(" ", $link->getAttribute("rel"));
        if (in_array("webmention", $rels) ||
            in_array("http://webmention.org/", $rels)) {
            return $link->getAttribute("href");
        }
    }
    return false;
}

function sendMention($source, $target) {
    //fetch source root
    $sourceparts = parse_url($source);
    $page = fetchPage($sourceparts["host"]);
    //discover webmention endpoint
    $endpoint = discoverWebmention($page);
    if ($endpoint === false)
        throw new Exception("No webmention endpoint found");
    //send webmention
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, formUrlencode(array(
        "source" => $source,
        "target" => $target)));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $page = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!($httpcode == 200 || $httpcode == 202))
        throw new Exception("Bad http code: $httpcode");
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
$photo = getOptionalFile("photo");
if ($content === null && $photo === null)
    do400("Either content or photo must be set");
$slug = generateSlug($name, $published);

try {
    if ($photo !== null)
        $location = createPhoto($config, $slug, $published, $photo, $content);
    else if ($name === null)
        $location = createNote($config, $slug, $replyto, $published, $content);
    else
        $location = createArticle($config, $slug, $replyto, $name, $published, $content);
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
