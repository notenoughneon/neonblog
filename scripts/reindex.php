<?
require("lib/init.php");

$feed = $site->LocalFeed();
$feed->poll();

?>
