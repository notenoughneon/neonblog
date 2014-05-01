<?php
require("lib/common.php");
require("lib/auth.php");
require("lib/dom.php");

requireAuthorization($config, "post");

$h = getRequiredPost("h");
if ($h !== "entry")
    do400("Unsupported object type: '$h'");
$name = getOptionalPost("name");
$content = getOptionalPost("content");
$published = getOptionalPost("published");
if ($published === null)
    $published = date("c");
$photo = getOptionalFile("photo");
if ($content === null && $photo === null)
    do400("Either content or photo must be set");
$slug = generateSlug($name, $published);

try {
    if ($photo !== null)
        $location = createPhoto($config, $slug, $published, $photo, $content);
    else if ($name === null)
        $location = createNote($config, $slug, $published, $content);
    else
        $location = createArticle($config, $slug, $name, $published, $content);
    do201($location);
} catch (Exception $e) {
    do500($e->getMessage());
}

?>
