<?php
$twitterMaxLen = 140;
$twitterUrlLen = 22;

function twitterTokenize($str) {
    $patterns = array(
        "url" => "~^(https?://)?[\w-]*[a-z][\w-]*(\.[\w-]+)+(/[\w\./%+?=&#\~-]+)?$~i",
        "hashtag" => "~^#\w*[a-z]\w*$~i",
        "name" => "~^@\w+$~");
    $punc = "[(),\.:!?]*";
    $tokens = array();
    while (strlen($str) > 0) {
        if (preg_match("/^[\s]+/", $str, $matches)) {
        } else {
            assert(preg_match("/^[\S]+/", $str, $matches));
            assert(preg_match("/^($punc)(\S+?)($punc)$/", $matches[0], $matches));
            array_shift($matches);
        }
        foreach ($matches as $token) {
            if (strlen($token) > 0) {
                $toktype = "text";
                foreach ($patterns as $type => $regex) {
                    if (preg_match($regex, $token))
                        $toktype = $type;
                }
                $last = count($tokens) - 1;
                if ($toktype == "text" && $last >= 0 && $tokens[$last]["type"] == "text") {
                    $tokens[$last]["value"] = $tokens[$last]["value"] . $token;
                } else {
                    $tokens[] = array("type" => $toktype, "value" => $token);
                }
                $str = substr($str, strlen($token));
            }
        }
    }
    return $tokens;
}

function twitterElideTo($str, $len) {
}

function elideTo($str, $len) {
    if (strlen($str) > $len)
        return substr($str, 0, $len - 3) . "...";
    return $str;
}

function twitterize($title, $content, $url) {
    if (isset($title) && $title !== "") {
        //article
        $title = elideTo($title, $twitterMaxLen - 1 - $twitterUrlLen);
        return "$title $url";
    } else {
        //note
        if (twitterLen($content) <= $twitterMaxLen)
            return $content;
        $content = twitterElideTo($content, $twitterMaxLen - 1 - $twitterUrlLen);
        return "$content $url";
    }
}

?>
