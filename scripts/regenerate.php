<?
require("lib/init.php");

$feed = $site->LocalFeed();
foreach ($feed->getAll() as $post) {
    echo "$post->file\n";
    $site->save($post);
}

?>
