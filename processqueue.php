<?php
echo "processqueue";
die();

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

function insertReply($file, $source, $post) {
    $doc = new DOMDocument();
    if (!$doc->loadHTML($file)) {
        echo "Failed to open $file\n";
        return false;
    }
    $xpath = new DOMXPath($doc);
    $hentry = $xpath->query("//*[@class='h-entry']")->item(0);

    $hcite = appendElement($hentry, "div");
    $hcite->setAttribute("class", "h-cite");

    //authorName, authorUrl
    if ($post["authorName"] != null) {
        $hcard = appendElement($hcite, "div");
        $hcard->setAttribute("class", "p-author h-card");
        appendText($hcard, $post["authorName"]);
        //authorUrl
        if ($post["authorUrl"] != null) {
            $authorurl = appendElement($hcard, "a");
            $authorurl->setAttribute("href", $post["authorUrl"]);
        }
        //authorPhoto
        if ($post["authorPhoto"] != null) {
            $img = appendElement($hcard, "img");
            $img->setAttribute("src", $post["authorPhoto"]);
        }
    }

    //published
    if ($post["published"] != null) {
        $time = appendElement($hcite, "time");
        $time->setAttribute("class", "dt-published");
        $time->setAttribute("datetime", $post["published"]);
    }

    //url
    $url = appendElement($hcite, "a");
    $url->setAttribute("class", "u-url");
    $url->setAttribute("href", $source);

    //content
    if ($post["contentValue"] != null) {
        $content = appendElement($hcite, "div");
        $content->setAttribute("class", "e-content");
        appendText($content, $post["contentValue"]);
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
        $mentions[] = $parts;
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
    if (!linksTo($page, $target)) {
        echo "no link to target found\n";
        continue;
    }
    $mf = Mf2\Parse($page, $source);
    $reply = getPost($mf);
    insertReply($postIndex[urlToLocal($config, $target)], $source, $reply);
}

if (!ftruncate($fh, 0))
    echo "Failed to clear queue\n";

finally:
flock($fh, LOCK_UN);
fclose($fh);

?>
