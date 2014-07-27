<?php
require("lib/init.php");

$feed = $site->LocalFeed();

$webmentions = $site->Webmentions();

while (count($webmentions->value) > 0) {
    $mention = array_shift($webmentions->value);
    $sourceUrl = $mention["source"];
    $targetUrl = $mention["target"];
    echo "Processing $sourceUrl -> $targetUrl\n";
    try {
        $html = fetchPage($sourceUrl);
        $sourcePost = new Microformat\Cite();
        $sourcePost->loadFromHtml($html, $sourceUrl);
        if ($sourcePost->isReplyTo($targetUrl) || linksTo($html, $targetUrl)) {
            echo "\tFound reply\n";
            $targetPost = $feed->getByUrl($targetUrl);
            $targetPost->children[] = $sourcePost;
            $site->save($targetPost);
        } else {
            echo "\tNo reply found\n";
        }
    } catch (Exception $e) {
        echo "\tError: " . $e->getMessage() . "\n";
    }
    $webmentions->sync();
}
$webmentions->flush();

?>
