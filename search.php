<?php
require("lib/init.php");

$q = $_GET["q"];

$site->renderHeader();

$feed = $site->LocalFeed();
foreach ($feed->search($q) as $post) {
    (new Template($post, array("query" => $q)))->render("tpl/entry-search.php");
}

$site->renderFooter();
?>
