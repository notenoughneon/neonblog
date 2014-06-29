<?php
require("lib/common.php");
require("lib/microformat.php");

$q = $_GET["q"];

$title = $config["siteTitle"];

require("tpl/header.php");

$feed = new Microformat\Localfeed("postindex.json");
foreach ($feed->search($q) as $post) {
    echo $post->toSearchHit($q);
}

require("tpl/footer.php");
?>
