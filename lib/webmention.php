<?php

function discoverWebmention($target) {
    $urlparts = parse_url($target);
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $urlparts["host"],
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3));
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($response === false) {
        throw new Exception("Web request failed: " . curl_error($ch));
    } else if ($httpcode != 200) {
        throw new Exception("Bad http code: $httpcode");
    }
    curl_close($ch);
    //TODO: check Link header
    //list($headers, $body) = explode("\r\n\r\n", $response, 2);
    //array_filter(explode("\r\n", $headers), 
    //    function($e) { return startsWith($e, "Link: "); });

    //check <link> and <a> tags
    $doc = new DOMDocument();
    if (!@$doc->loadHTML($body))
        throw new Exception("Failed to parse HTML");
    foreach (array("link", "a") as $type) {
        foreach ($doc->getElementsByTagName($type) as $elt) {
            if (array_any(explode(" ", $elt->getAttribute("rel")),
                function($rel) {
                    return $rel === "webmention" 
                        || $rel === "http://webmention.org/"; }))
                return $elt->getAttribute("href");
        }
    }
    return false;
}

function sendMention($source, $target) {
    //discover webmention endpoint
    $endpoint = discoverWebmention($target);
    if ($endpoint === false)
        throw new Exception("No webmention endpoint found");
    //send webmention
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, formUrlencode(array(
        "source" => $source,
        "target" => $target)));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $page = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!($httpcode == 200 || $httpcode == 202))
        throw new Exception("Bad http code: $httpcode");
}

?>
