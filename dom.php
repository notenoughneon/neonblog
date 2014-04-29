<?php

function appendElement($parent, $tag, $attrs = array(), $text = null) {
    $elt = new DOMElement($tag);
    $parent->appendChild($elt);
    foreach ($attrs as $attr => $val) {
        $elt->setAttribute($attr, $val);
    }
    if ($text !== null)
        $elt->appendChild(new DOMText($text));
    return $elt;
}

function createArticle($cfg, $slug, $name, $published, $content) {
    $relativeUrl = $cfg["postRoot"] . "/" . $slug;
    $filename =  $relativeUrl . $cfg["postExtension"];

    $doc = new DOMDocument();

    $hentry = appendElement($doc, "div", array("class" => "h-entry"));

    $pname = appendElement($hentry, "div", array(
        "class" => "p-name"),
    $name);

    $dtpublished = appendElement($hentry, "time", array(
        "class" => "dt-published",
        "datetime" => $published));

    $hauthor = appendElement($hentry, "a", array(
        "class" => "p-author h-card",
        "href" => $cfg["siteUrl"]),
    $cfg["aboutName"]);

    $econtent = appendElement($hentry, "div", array(
        "class" => "e-content"),
    $content);

    $uurl = appendElement($hentry, "a", array(
        "class" => "u-url",
        "href" => $relativeUrl));

    if (!$doc->saveHTMLFile($filename))
        throw new Exception("Failed to write to $filename");

    return $cfg["siteUrl"] . "/" . $relativeUrl;
}

function createNote($cfg, $slug, $published, $content) {
    $relativeUrl = $cfg["postRoot"] . "/" . $slug;
    $filename =  $relativeUrl . $cfg["postExtension"];

    $doc = new DOMDocument();

    $hentry = appendElement($doc, "div", array("class" => "h-entry"));

    $dtpublished = appendElement($hentry, "time", array(
        "class" => "dt-published",
        "datetime" => $published));

    $hauthor = appendElement($hentry, "a", array(
        "class" => "p-author h-card",
        "href" => $cfg["siteUrl"]),
    $cfg["aboutName"]);

    $econtent = appendElement($hentry, "div", array(
        "class" => "p-name e-content"),
    $content);

    $uurl = appendElement($hentry, "a", array(
        "class" => "u-url",
        "href" => $relativeUrl));

    if (!$doc->saveHTMLFile($filename))
        throw new Exception("Failed to write to $filename");

    return $cfg["siteUrl"] . "/" . $relativeUrl;
}

//TODO: abstract file path from slug
function insertReply($file, $reply) {
    $doc = new DOMDocument();
    if (!$doc->loadHTMLFile($file))
        throw new Exception("Failed to open $file");
    $xpath = new DOMXPath($doc);
    $hentry = $xpath->query("//*[@class='h-entry']")->item(0);

    $hcite = appendElement($hentry, "div", array("class" => "h-cite"));

    //reply-to
    if (isset($reply["in-reply-to"]))
        $replyto = appendElement($hcite, "a", array(
            "class" => "u-in-reply-to",
            "href" => $reply["in-reply-to"]));

    //authorName, authorUrl
    if ($reply["authorName"] != null) {
        $hcard = appendElement($hcite, "div", array(
            "class" => "p-author h-card"),
        $reply["authorName"]);
        //authorUrl
        if ($reply["authorUrl"] != null)
            $authorurl = appendElement($hcard, "a", array(
                "href" => $reply["authorUrl"]));
        //authorPhoto
        if ($reply["authorPhoto"] != null)
            $img = appendElement($hcard, "img", array(
                "class" => "u-photo",
                "src" => $reply["authorPhoto"]));
    }

    //published
    if ($reply["published"] != null)
        $time = appendElement($hcite, "time", array(
            "class" => "dt-published",
            "datetime", $reply["published"]));

    //url
    $url = appendElement($hcite, "a", array(
        "class" => "u-url",
        "href" => $reply["url"]));

    //content
    if ($reply["contentValue"] != null)
        $content = appendElement($hcite, "div", array(
            "class" => "e-content"),
        $reply["contentValue"]);

    if (!$doc->saveHTMLFile($file))
        throw new Exception("Failed to save data to $file");

    return $cfg["siteUrl"] . "/" . $relativeUrl;
}

?>
