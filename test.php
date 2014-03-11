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

    $file = "test.html";

    $doc = new DOMDocument();
    if (!$doc->loadHTMLFile($file)) {
        echo "Failed to open $file\n";
        return false;
    }
    $xpath = new DOMXPath($doc);
    $hentry = $xpath->query("//*[@class='h-entry']")->item(0);

    $hcite = appendElement($hentry, "div");
    $hcite->setAttribute("class", "h-cite");

    if (!$doc->saveHTMLFile($file))
        echo "Failed to save data to $file\n";

?>
