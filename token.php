<?php
require("common.php");
require("jsonstore.php");

/**
 * Authenticate user with indieauth.com
 */
function indieAuthenticate($code, $me) {
    $verify_url = "https://indieauth.com/verify";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verify_url . "?token=$code");
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $page = curl_exec($ch);
    $mimetype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($page === false)
        throw new Exception("GET $verify_url failed");
    if (!startsWith($mimetype, "application/json"))
        throw new Exception("Bad mimetype: $mimetype");

    $response = json_decode($page, true);
    if ($response === null)
        throw new Exception("Json decode failed");

    if (isset($response["me"]) && $response["me"] === $me)
        return true;

    return false;
}

function generateToken($cfg, $me, $client_id, $scope) {
    $token = bin2hex(openssl_random_pseudo_bytes(16));
    $tokenstore = new JsonStore($cfg["tokenFile"]);
    $tokenstore->value[] = array(
        "me" => $me,
        "client_id" => $client_id,
        "scope" => $scope,
        "date_issued" => date("c"),
        "token" => $token
    );
    $tokenstore->flush();
    return $token;
}

$code = getRequiredPost("code");
$me = getRequiredPost("me");
$client_id = getRequiredPost("client_id");
$scope = getRequiredPost("scope");

try {
    if ($scope != "post")
        do400("Unsupported scope: '$scope'");
    if (!indieAuthenticate($code, $me) || $me != $config["siteUrl"])
        do400("Authentication failed for $me");
    $token = generateToken($config, $me, $client_id, $scope);
    header("Content-Type: application/x-www-form-urlencoded");
    echo "access_token=$token&scope=post&me=" . urlencode($me);
} catch (Exception $e) {
    do500($e->getMessage());
}

?>
