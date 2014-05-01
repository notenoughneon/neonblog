<?php
require("lib/common.php");
require("Mf2/Parser.php");

$postIndex = generatePostIndex($config);

if (isset($_GET["p"])) {
    $p = $_GET["p"];
    if (!isset($postIndex[$p]))
        do404($p);
    $mf2 = Mf2\parse(file_get_contents($postIndex[$p]), $config["siteUrl"]);
    $post = getPost($mf2);
    $replies = getReplies($mf2);
    require("tpl/post.php");
    exit();
} else {
    $o = 0;
    $l = $config["postsPerPage"];
    if (isset($_GET["o"])) $o = $_GET["o"];
    if (isset($_GET["l"])) $l = $_GET["l"];
    $posts = array();
    $replies = array(); // dummy to keep the template from breaking
    foreach(array_slice($postIndex, $o, $l) as $filename) {
        $posts[] = getPost(Mf2\parse(file_get_contents($filename), $config["siteUrl"]));
    }
    if (($o + $l) >= count($postIndex)) $prevUrl = null;
    else $prevUrl = "?o=" . ($o + $l) . "&l=" . $l;
    if ($o <= 0) $nextUrl = null;
    else $nextUrl = "?o=" . ($o - $l) . "&l=" . $l;
    require("tpl/index.php");
    exit();
}

?>
