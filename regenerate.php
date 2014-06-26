<?
require("lib/microformat.php");
require("lib/common.php");


$feed = new Microformat\Localfeed("postindex.json");
$feed->reload("#^p/.*\.html$#");
foreach ($feed->getAll() as $post) {
    echo "$post->file...<br>";
    $post->save($config);
}

?>
