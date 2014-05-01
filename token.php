<?php
require("lib/common.php");
require("lib/auth.php");

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
