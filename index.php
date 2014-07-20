<?php
require("lib/init.php");

$o = 0;
$l = $site->postsPerPage;
if (isset($_GET["o"])) $o = $_GET["o"];
if (isset($_GET["l"])) $l = $_GET["l"];

$site->renderHeader();

?>

<div class="h-feed">

<?

$feed = $site->LocalFeed();
foreach ($feed->getRange($o, $l) as $post) {
    (new Template($post))->render("tpl/entry-summary.php");
}

if (($o + $l) >= $feed->count()) $prevUrl = null;
else $prevUrl = "?o=" . ($o + $l) . "&l=" . $l;
if ($o <= 0) $nextUrl = null;
else $nextUrl = "?o=" . ($o - $l) . "&l=" . $l;

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
