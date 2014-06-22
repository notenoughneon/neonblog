<?php
require("lib/common.php");
require("lib/microformat.php");

$o = 0;
$l = $config["postsPerPage"];
if (isset($_GET["o"])) $o = $_GET["o"];
if (isset($_GET["l"])) $l = $_GET["l"];

$title = $config["siteTitle"];

require("tpl/header.php");

$feed = new Microformat\Localfeed("postindex.json");
foreach ($feed->getRange($o, $l) as $post) {
    echo $post->toHtmlSummary();
}

if (($o + $l) >= $feed->count()) $prevUrl = null;
else $prevUrl = "?o=" . ($o + $l) . "&l=" . $l;
if ($o <= 0) $nextUrl = null;
else $nextUrl = "?o=" . ($o - $l) . "&l=" . $l;

?>
          <ul class="pager">
            <? if ($prevUrl != null) { ?>
            <li><a href="<? echo $prevUrl ?>">Previous</a></li>
            <? } if ($nextUrl != null) { ?>
            <li><a href="<? echo $nextUrl ?>">Next</a></li>
            <? } ?>
          </ul>
<?
require("tpl/footer.php");
?>
