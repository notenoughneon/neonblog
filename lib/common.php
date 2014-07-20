<?php

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

function array_any($array, $callback) {
    foreach ($array as $elt)
        if ($callback($elt))
            return true;
    return false;
}

function getRequiredPost($name) {
    if (empty($_POST[$name]))
        do400("Missing required parameter: '$name'");
    return $_POST[$name];
}

function getOptionalPost($name) {
    if (empty($_POST[$name]))
        return null;
    return $_POST[$name];
}

function getOptionalFile($name) {
    if (empty($_FILES[$name])
        || $_FILES[$name]["error"] !== UPLOAD_ERR_OK)
        return null;
    return $_FILES[$name];
}

function formUrlencode($params) {
    $pairs = array();
    foreach ($params as $key => $val) {
        $pairs[] = urlencode($key) . "=" . urlencode($val);
    }
    return implode("&", $pairs);
}

function formUrldecode($coded) {
    $decoded = array();
    foreach (explode("&", $coded) as $pair) {
        list($key, $val) = explode("=", $pair);
        $decoded[urldecode($key)] = urldecode($val);
    }
    return $decoded;
}

function do201($location = null) {
    header("HTTP/1.1 201 Created");
    if ($location !== null)
        header("Location: $location");
    echo "<h1>201 Created</h1>";
    if ($location !== null)
        echo "<p><a href=\"$location\">$location</a></p>";
}

function do202() {
    header("HTTP/1.1 202 Accepted");
    echo "<h1>202 Accepted</h1>";
    exit();
}

function do302($location) {
    header("HTTP/1.1 302 Found");
    header("Location: $location");
    echo "<h1>302 Found</h1>";
    echo "<p>Redirecting to <a href=\"$location\">$location</a></p>";
}

function do400($msg = "") {
    header("HTTP/1.1 400 Bad Request");
    echo "<h1>400 Bad Request</h1>";
    echo "<p>$msg</p>";
    exit();
}

function do401($msg = "") {
    header("HTTP/1.1 401 Unauthorized");
    echo "<h1>401 Unauthorized</h1>";
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

function fetchPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    $page = curl_exec($ch);
    $mimetype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($page === false) {
        $error = curl_error($ch);
        throw new Exception("Web request failed: $error");
    } else if ($httpcode != 200) {
        throw new Exception("Bad http code: $httpcode");
    } else if (!startsWith($mimetype, "text/html")) {
        throw new Exception("Bad mimetype: $mimetype");
    }
    curl_close($ch);
    return $page;
}

function highlight($content, $query) {
    $len = 128;
    $i = stripos($content, $query);
    if ($i !== false) {
        $elided = substr($content, 0, $i)
            . "<mark>"
            . substr($content, $i, strlen($query))
            . "</mark>"
            . substr($content, $i + strlen($query));
    } else {
        $i = 0;
        $elided = $content;
    }
    $start = max($i - $len + strlen($query)/2, 0);
    $end = $start + 2*$len;
    $elided = substr($elided, $start, 2*$len);
    if ($start > 0)
        $elided = "..." . $elided;
    if ($end < strlen($content))
        $elided = $elided . "...";
    return $elided;
}

?>
