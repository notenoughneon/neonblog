<?php
require("common.php");
require("dom.php");
require("jsonstore.php");

function getBearerToken() {
    if (isset($_SERVER["HTTP_AUTHORIZATION"]))
        if (preg_match(
            "/^Bearer (.+)/",
            $_SERVER["HTTP_AUTHORIZATION"],
            $matches))
            return $matches[1];
    if (isset($_POST["access_token"]))
        return $_POST["access_token"];
    return null;
}

function isAuthorized($cfg, $scope) {
    $token = getBearerToken();
    if ($token === null)
        return false;
    $tokenstore = new JsonStore($cfg["tokenFile"]);
    $tokenstore->close();
    return array_any(
        $tokenstore->value,
        function($e) use($token, $scope) {
            return $e["token"] === $token &&
                $e["scope"] === $scope;
        }
    );
}

function requireAuthorization($cfg, $scope) {
    if (!isAuthorized($cfg, $scope)) {
        header("WWW-Authenticate: Bearer realm=\"$scope\"");
        do401();
    }
}

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
