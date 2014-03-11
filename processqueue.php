<?php
require("common.php");
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
    if (isset($mf["rels"]["in-reply-to"])
        && in_array($url, $mf["rels"]["in-reply-to"]))
        return true;
    if (in_array($url, mfpath(mftype($mf, "h-entry"), "in-reply-to/url")))
        return true;
    return false;
}

function appendElement($parent, $tag) {
    $elt = new DOMElement($tag);
    $parent->appendChild($elt);
    return $elt;
}

function appendText($parent, $text) {
    $elt = new DOMText($text);
    $parent->appendChild($elt);
    return $elt;
}

function insertReply($file, $reply) {
    $doc = new DOMDocument();
    if (!$doc->loadHTMLFile($file)) {
        echo "Failed to open $file\n";
        return false;
    }
    $xpath = new DOMXPath($doc);
    $hentry = $xpath->query("//*[@class='h-entry']")->item(0);

    $hcite = appendElement($hentry, "div");
    $hcite->setAttribute("class", "h-cite");

    //reply-to
    if (isset($reply["in-reply-to"])) {
        $replyto = appendElement($hcite, "a");
        $replyto->setAttribute("class", "u-in-reply-to");
        $replyto->setAttribute("href", $reply["in-reply-to"]);
    }

    //authorName, authorUrl
    if ($reply["authorName"] != null) {
        $hcard = appendElement($hcite, "div");
        $hcard->setAttribute("class", "p-author h-card");
        appendText($hcard, $reply["authorName"]);
        //authorUrl
        if ($reply["authorUrl"] != null) {
            $authorurl = appendElement($hcard, "a");
            $authorurl->setAttribute("href", $reply["authorUrl"]);
        }
        //authorPhoto
        if ($reply["authorPhoto"] != null) {
            $img = appendElement($hcard, "img");
            $img->setAttribute("src", $reply["authorPhoto"]);
        }
    }

    //published
    if ($reply["published"] != null) {
        $time = appendElement($hcite, "time");
        $time->setAttribute("class", "dt-published");
        $time->setAttribute("datetime", $reply["published"]);
    }

    //url
    $url = appendElement($hcite, "a");
    $url->setAttribute("class", "u-url");
    $url->setAttribute("href", $reply["url"]);

    //content
    if ($reply["contentValue"] != null) {
        $content = appendElement($hcite, "div");
        $content->setAttribute("class", "e-content");
        appendText($content, $reply["contentValue"]);
    }

    if (!$doc->saveHTMLFile($file))
        echo "Failed to save data to $file\n";
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

//if (!ftruncate($fh, 0))
//    echo "Failed to clear queue\n";

finally:
flock($fh, LOCK_UN);
fclose($fh);

?>
