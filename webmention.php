<?php
require("lib/common.php");
require("lib/microformat.php");

$source = getRequiredPost("source");
$target = getRequiredPost("target");

try {
    $feed = new Microformat\Localfeed("postindex.json");
    $feed->getByUrl($target);
} catch (Exception $e) {
    do400("$target isn't a valid target");
}

try {
    $mentionstore = new JsonStore($config["webmentionFile"]);
    if (count($mentionstore->value) >= $config["webmentionLength"]) {
        throw new Exception("Webmention queue is full");
    }
    $mentionstore->value[] = array(
        "source" => $source,
        "target" => $target);
    $mentionstore->flush();
    do202();
} catch (Exception $e) {
    do500($e->getMessage());
}

?>
