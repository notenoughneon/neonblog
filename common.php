<?php
ini_set("display_errors", 1);

$config = array(
    "siteUrl" => "http://notenoughneon.com",
    "siteTitle" => "Not Enough Neon",
    "aboutNick" => "Emma",
    "aboutName" => "Emma Kuo",
    "aboutPhoto" => "/m/emma-tw-73.jpeg",
    "aboutBlurb" => "Software developer in Portland, OR.",
    "elsewhere" => array(
        "Twitter" => "https://twitter.com/notenoughneon",
        "Instagram" => "http://instagram.com/emmackuo",
        "SoundCloud" => "https://soundcloud.com/notenoughneon",
        "Tumblr" => "http://notenoughneon.tumblr.com",
        "Facebook" => "https://www.facebook.com/emma.kuo.5209",
        "Github" => "https://github.com/notenoughneon",
        "LinkedIn" => "http://www.linkedin.com/pub/emma-kuo/25/15a/586"),
    "postRoot" => "p",
    "postExtension" => ".html",
    "postsPerPage" => 20,
    "webmentionQueue" => "webmentions.txt",
    "webmentionQueueLength" => 50
);

function startsWith($h, $n) {
    return substr($h, 0, strlen($n)) === $n;
}

function endsWith($h, $n) {
    $nlen = strlen($n);
    return substr($h, -$nlen, $nlen) === $n;
}

function chopPrefix($h, $n) {
    if (startsWith($h, $n))
        return substr($h, strlen($n));
    return $h;
}

function truncate($s, $n) {
    if (strlen($s) > $n) {
        return substr($s, 0, $n) . "...";
    }
    return $s;
}

function mftype($parsed, $type) {
    return array_filter($parsed["items"], function($elt) use ($type) {
        return in_array($type, $elt["type"]);
    });
}

function scrubstrings($arr) {
    return array_map(function($elt) {
        if (gettype($elt) == "string")
            return htmlspecialchars($elt);
        return $elt;
    }, $arr);
}

function mfprop($mfs, $prop) {
    $props = array();
    if ($prop == "1") {
        if (isset($mfs[0])) return $mfs[0];
        return null;
    }
    foreach ($mfs as $mf) {
        if (isset($mf["properties"][$prop]))
            $thisprops = scrubstrings($mf["properties"][$prop]);
        else if ($prop == "children" && isset($mf[$prop]))
            $thisprops = $mf[$prop];
        else if (($prop == "html") && isset($mf[$prop]))
            $thisprops = array($mf[$prop]);
        else if (($prop == "value") && isset($mf[$prop]))
            $thisprops = scrubstrings(array($mf[$prop]));
        else
            continue;
        $props = array_merge($props, $thisprops);
    }
    return $props;
}

function mfpath($mf, $path) {
    $elts = array_filter(explode("/", $path), function($e){return $e!="";});
    return array_reduce($elts, function($result, $elt) {
        return mfprop($result, $elt);
    }, $mf);
}

function urlToLocal($cfg, $target) {
    $targetUrl = parse_url($target);
    $siteUrl = parse_url($cfg["siteUrl"]);
    foreach (array("scheme","host") as $part) {
        if (!isset($targetUrl[$part])
            || $targetUrl[$part] != $siteUrl[$part])
            return null;
    }
    if (!isset($targetUrl["path"]))
        return null;
    return chopPrefix($targetUrl["path"], "/" . $cfg["postRoot"] . "/");
}

function getPost($mf) {
    $e = mftype($mf, "h-entry");
    $post = array(
        "authorName" => mfpath($e, "author/name/1"),
        "authorPhoto" => mfpath($e, "author/photo/1"),
        "authorUrl" => mfpath($e, "author/url/1"),
        "name" => mfpath($e, "name/1"),
        "published" => mfpath($e, "published/1"),
        "contentHtml" => mfpath($e, "content/html/1"),
        "contentValue" => mfpath($e, "content/value/1"),
        "url" => mfpath($e, "url/1"),
        "in-reply-to" => mfpath($e, "in-reply-to/1"),
    );
    $post["type"] = ($post["name"] === $post["contentValue"]) 
        ? "note" : "article";
    return $post;
}

function getReplies($mf) {
    $replies = array();
    foreach (mfpath(mftype($mf, "h-entry"), "children") as $r) {
        $r = array($r);
        $replies[] = array(
            "authorName" => mfpath($r, "author/name/1"),
            "authorPhoto" => mfpath($r, "author/photo/1"),
            "authorUrl" => mfpath($r, "author/url/1"),
            "published" => mfpath($r, "published/1"),
            "contentValue" => mfpath($r, "content/value/1"),
            "url" => mfpath($r, "url/1"),
            "in-reply-to" => mfpath($r, "in-reply-to/1"),
        );
    }
    return $replies;
}

function generatePostIndex($c) {
    $posts = array();
    $extlen = strlen($c["postExtension"]);
    $dh = opendir($c["postRoot"]);
    while (false !== ($entry = readdir($dh))) {
        $fullPath = $c["postRoot"] . '/' . $entry;
        if (!startsWith($entry, '.')
            && endsWith($entry, $c["postExtension"])
            && is_file($fullPath))
            $posts[substr($entry, 0, strlen($entry) - $extlen)] = $fullPath;
    }
    closedir($dh);
    krsort($posts);
    return $posts;
}

function do202() {
    header("HTTP/1.1 202 Accepted");
    echo "<h1>202 Accepted</h1>";
    exit();
}

function do400($msg = "") {
    header("HTTP/1.1 400 Bad Request");
    echo "<h1>400 Bad Request</h1>";
    echo "<p>$msg</p>";
    exit();
}

function do404($path) {
    header("HTTP/1.1 404 Not Found");
    echo "<h1>404 Not Found</h1>";
    echo "<p>$path was not found.</p>";
    exit();
}

function do500($msg = "") {
    header("HTTP/1.1 500 Internal Server Error");
    echo "<h1>500 Internal Server Error</h1>";
    echo "<p>$msg</p>";
    exit();
}


?>
