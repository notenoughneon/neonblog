<?php
require("lib/common.php");

function isTargetValid($cfg, $target) {
    return array_key_exists(urlToLocal($cfg, $target), generatePostIndex($cfg));
}

$source = getRequiredPost("source");
$target = getRequiredPost("target");

if (!isTargetValid($config, $target))
    do400("$target isn't a valid target");

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
