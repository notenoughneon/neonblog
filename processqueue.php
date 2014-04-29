<?php
require("common.php");
require("dom.php");
require("Mf2/Parser.php");

function linksTo($html, $url) {
    $doc = new DOMDocument();
    if (!$doc->loadHTML($html))
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

$fh = fopen($config["webmentionQueue"], "r+");
if ($fh === false) {
    echo "Unable to open queue\n";
    goto finally;
}
if (!flock($fh, LOCK_EX)) {
    echo "Unable to lock queue\n";
    goto finally;
}
$mentions = array();
while (false != ($mention = fgets($fh))) {
    if ($mention != "") {
        $parts = explode(">>", $mention);
        if (count($parts) != 2) {
            echo "Malformed entry: $mention\n";
            goto finally;
        }
        $mentions[] = array_map("trim", $parts);
    }
}
foreach ($mentions as $mention) {
    list($source, $target) = $mention;
    echo "* processing $source\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $source);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    $page = curl_exec($ch);
    $mimetype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    if ($page === false) {
        echo "web request failed\n";
        continue;
    }
    if (!startsWith($mimetype, "text/html")) {
        echo "bad mimetype: $mimetype\n";
        continue;
    }
    if (linksTo($page, $target)) {
        echo "found link to target\n";
        $mf = Mf2\Parse($page, $source);
        $reply = getPost($mf);
        if (isReplyTo($page, $target)) {
            echo "found in-reply-to target\n";
            $reply["in-reply-to"] = $target;
        } else {
            unset($reply["in-reply-to"]);
        }
        insertReply($postIndex[urlToLocal($config, $target)], $reply);
    }
    else {
        echo "no link to target found\n";
    }
}

if (!ftruncate($fh, 0))
    echo "Failed to clear queue\n";

finally:
flock($fh, LOCK_UN);
fclose($fh);

?>
