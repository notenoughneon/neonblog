<?php
require("lib/common.php");
require("lib/dom.php");
require("lib/jsonstore.php");
require("Mf2/Parser.php");

function linksTo($html, $url) {
    $doc = new DOMDocument();
    if (!@$doc->loadHTML($html))
        return false;
    foreach ($doc->getElementsByTagName("a") as $a) {
        if ($a->getAttribute("href") == $url)
            return true;
    }
    return false;
}

function isReplyTo($html, $url) {
    $mf = Mf2\Parse($html);
    if (gettype($mf["rels"]) == "array"
        && isset($mf["rels"]["in-reply-to"])
        && in_array($url, $mf["rels"]["in-reply-to"]))
        return true;
    if (in_array($url, mfpath(mftype($mf, "h-entry"), "in-reply-to")))
        return true;
    if (in_array($url, mfpath(mftype($mf, "h-entry"), "in-reply-to/url")))
        return true;
    return false;
}

$postIndex = generatePostIndex($config);

$mentionstore = new JsonStore($config["webmentionFile"]);

while (count($mentionstore->value) > 0) {
    $mention = array_shift($mentionstore->value);
    $source = $mention["source"];
    $target = $mention["target"];
    echo "Processing $source -> $target\n";
    try {
        $page = fetchPage($source);
        if (!linksTo($page, $target)) {
            echo "\tNo link to $target found\n";
        } else {
            echo "\tFound link to $target\n";
            $mf = Mf2\Parse($page, $source);
            $reply = getPost($mf);
            if (isReplyTo($page, $target)) {
                echo "\tFound reply to $target\n";
                $reply["in-reply-to"] = $target;
            } else {
                unset($reply["in-reply-to"]);
            }
            insertReply($postIndex[urlToLocal($config, $target)], $reply);
        }
    } catch (Exception $e) {
        echo "\tError: " . $e->getMessage() . "\n";
    }
    $mentionstore->sync();
}
$mentionstore->flush();

?>
