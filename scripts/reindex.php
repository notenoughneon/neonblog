<?
require("lib/init.php");

$feed = $site->LocalFeed();
$feed->reload("#^p/.*\.html$#");

?>
