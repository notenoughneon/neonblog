<?php
require("lib/init.php");
$token = $_COOKIE["bearer_token"];

$action = getOptionalPost("action");
if ($action == "poll") {
    $site->Auth()->requireAuthorization("post");
    header("Content-type: text/plain");
    $site->RemoteFeed()->poll();
    exit();
}

$o = 0;
$l = $site->postsPerPage;
if (isset($_GET["o"])) $o = $_GET["o"];
if (isset($_GET["l"])) $l = $_GET["l"];

$site->renderHeader();
?>
<form action="feed.php" method="post">
    <input type="hidden" name="access_token" value="<? echo $token ?>" />
    <button type="submit" class="btn" name="action" value="poll">Poll</button>
</form>
<?

$feed = $site->RemoteFeed();
foreach ($feed->getRange($o, $l) as $post) {
    if ($post->getPostType() == "like")
        (new Template($post))->render("tpl/like-summary.php");
    else if ($post->getPostType() == "repost")
        (new Template($post))->render("tpl/repost-summary.php");
    else
        (new Template($post))->render("tpl/entry-summary.php");
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
$site->renderFooter();
?>
