<?
require("lib/microformat.php");
require("lib/common.php");


$feed = new Microformat\Localfeed("postindex.json");
foreach ($feed->getAll() as $post) {
    echo "$post->file\n";
    $post->save($config);
}

?>
