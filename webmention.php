<?php
require("common.php");

function isTargetValid($cfg, $target) {
    return array_key_exists(urlToLocal($target), generatePostIndex($cfg));
}

if (!isset($_REQUEST["source"]))
    do400("Source not set");
if (!isset($_REQUEST["target"]))
    do400("Target not set");
$source = $_REQUEST["source"];
$target = $_REQUEST["target"];
if (!isTargetValid($config, $target))
    do400("$target isn't a valid target");


$errorMsg = "";

$fh = fopen($config["webmentionQueue"], "a+");
if ($fh === false) {
    $errorMsg = "Unable to open queue";
    goto finally;
}
if (!flock($fh, LOCK_EX)) {
    $errorMsg = "Unable to lock queue";
    goto finally;
}
$mentions = array();
while (false != ($mention = fgets($fh))) {
    if ($mention != "")
        $mentions[] = $mention;
}
if (count($mentions) >= $config["webmentionQueueLength"]) {
    $errorMsg = "Message queue is full";
    goto finally;
}
if (!fwrite($fh, $source . ">>" . $target . "\n")) {
    $errorMsg = "Unable to write to queue";
    goto finally;
}
finally:
flock($fh, LOCK_UN);
fclose($fh);


if ($errorMsg != "")
    do500($errorMsg);
else
    do202();
?>
