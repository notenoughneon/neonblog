<?php
require("lib/init.php");

$o = 0;
$l = $site->postsPerPage;
$t = "note article photo repost reply";
if (isset($_GET["o"])) $o = $_GET["o"];
if (isset($_GET["l"])) $l = $_GET["l"];
if (isset($_GET["t"])) $t = $_GET["t"];

$site->renderHeader();

?>

<div class="h-feed">

<?

$feed = $site->LocalFeed();
$filter = Microformat\Feed::filterByType(explode(" ", $t));
foreach ($feed->getRange($o, $l, $filter) as $post) {
    if ($post->getPostType() == "like")
        (new Template($post, array("hideAuthor" => true)))->render("tpl/like-summary.php");
    else if ($post->getPostType() == "repost")
        (new Template($post, array("hideAuthor" => true)))->render("tpl/repost-summary.php");
    else if ($post->getPostType() == "reply")
        (new Template($post, array("hideAuthor" => true)))->render("tpl/reply-summary.php");
    else
        (new Template($post, array("hideAuthor" => true)))->render("tpl/entry-summary.php");
}

if (($o + $l) >= $feed->count($filter)) $prevUrl = null;
else $prevUrl = "?o=" . ($o + $l) . "&l=" . $l . "&t=" . urlencode($t);
if ($o <= 0) $nextUrl = null;
else $nextUrl = "?o=" . ($o - $l) . "&l=" . $l . "&t=" . urlencode($t);

?>
          <ul class="pager">
            <? if ($prevUrl != null) { ?>
            <li><a href="<? echo $prevUrl ?>" rel="previous">Previous</a></li>
            <? } if ($nextUrl != null) { ?>
            <li><a href="<? echo $nextUrl ?>" rel="next">Next</a></li>
            <? } ?>
          </ul>

</div> <!-- /h-feed -->

<?
$site->renderFooter();
?>
