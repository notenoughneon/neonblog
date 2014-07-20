<?php
require("lib/init.php");

$source = getRequiredPost("source");
$target = getRequiredPost("target");

try {
    $feed = $site->LocalFeed();
    $feed->getByUrl($target);
} catch (Exception $e) {
    do400("$target isn't a valid target");
}

try {
    $webmentions = $site->Webmentions();
    if (count($webmentions->value) >= $site->webmentionLength) {
        throw new Exception("Webmention queue is full");
    }
    $webmentions->value[] = array(
        "source" => $source,
        "target" => $target);
    $webmentions->flush();
    do202();
} catch (Exception $e) {
    do500($e->getMessage());
}

?>
