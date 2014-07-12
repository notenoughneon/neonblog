<?
require("lib/microformat.php");
require("lib/common.php");

$feed = new Microformat\Localfeed("postindex.json");
$feed->reload("#^p/.*\.html$#");

?>
