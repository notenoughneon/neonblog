<?php
require("lib/common.php");
require("lib/microformat.php");

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

$feed = new Microformat\Localfeed("postindex.json");

$mentionstore = new JsonStore($config["webmentionFile"]);

while (count($mentionstore->value) > 0) {
    $mention = array_shift($mentionstore->value);
    $sourceUrl = $mention["source"];
    $targetUrl = $mention["target"];
    echo "Processing $sourceUrl -> $targetUrl\n";
    try {
        $html = fetchPage($sourceUrl);
        $sourcePost = new Microformat\Entry("cite");
        $sourcePost->loadFromHtml($html, $sourceUrl);
        if ($sourcePost->isReplyTo($targetUrl)) {
            echo "\tFound reply\n";
            $targetPost = $feed->getByUrl($targetUrl);
            $targetPost->children[] = $sourcePost;
            $targetPost->save($config);
        } else {
            echo "\tNo reply found\n";
        }
    } catch (Exception $e) {
        echo "\tError: " . $e->getMessage() . "\n";
    }
    $mentionstore->sync();
}
$mentionstore->flush();

?>
