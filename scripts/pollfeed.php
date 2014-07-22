<?php
require("lib/init.php");
$remotefeed = $site->RemoteFeed();
$remotefeed->poll();
?>
