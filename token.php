<?php
require("common.php");
require("jsonstore.php");

function formUrlencode($params) {
    $pairs = array();
    foreach ($params as $key => $val) {
        $pairs[] = urlencode($key) . "=" . urlencode($val);
    }
    return implode("&", $pairs);
}

function formUrldecode($coded) {
    $decoded = array();
    foreach (explode("&", $coded) as $pair) {
        list($key, $val) = explode("=", $pair);
        $decoded[urldecode($key)] = urldecode($val);
    }
    return $decoded;
}

function indieAuthenticate($params) {
    $verify_url = "https://indieauth.com/auth";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verify_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, formUrlencode($params));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $page = curl_exec($ch);
    $mimetype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($page === false)
        throw new Exception("GET $verify_url failed");
    if ($mimetype !== "application/x-www-form-urlencoded")
        throw new Exception("Bad mimetype: $mimetype");

    return formUrldecode($page);
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
$redirect_uri = getRequiredPost("redirect_uri");
$client_id = getRequiredPost("client_id");
$state = getRequiredPost("state");

$params = array(
    "code" => $code,
    "me" => $me,
    "redirect_uri" => $redirect_uri,
    "client_id" => $client_id,
    "state" => $state
);

try {
    $auth = indieAuthenticate($params);
    if (empty($auth["me"]) ||
        $auth["me"] !== $config["siteUrl"])
        do400("Authentication failed for $me");
    $token = generateToken($config, $me, $client_id, $auth["scope"]);
    header("Content-Type: application/x-www-form-urlencoded");
    echo formUrlencode(array(
        "access_token" => $token,
        "me" => $me,
        "scope" => $auth["scope"]));
} catch (Exception $e) {
    do500($e->getMessage());
}

?>
