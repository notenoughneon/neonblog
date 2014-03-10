<?

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

    $doc = new DOMDocument();
    $doc->loadHTML(file_get_contents("p/1-first-post.html"));
    $xpath = new DOMXPath($doc);
    $hentry = $xpath->query("//*[@class='h-entry']")->item(0);

    $hcite = appendElement($hentry, "div");
    $hcite->setAttribute("class", "h-cite");

    //authorName, authorUrl
    $hcard = appendElement($hcite, "a");
    $hcard->setAttribute("class", "p-author h-card");
    $hcard->setAttribute("href", "http://foo.bar");
    appendText($hcard, "Foo Bar");

    //authorPhoto
    $img = appendElement($hcard, "img");
    $img->setAttribute("src", "http://foo.bar/foo.jpg");

    $doc->normalizeDocument();
    $doc->saveHTMLFile("test.html");
?>
