<?php
require("lib/init.php");

$code = getRequiredPost("code");
$me = getRequiredPost("me");
$redirect_uri = getRequiredPost("redirect_uri");
$client_id = getRequiredPost("client_id");
$state = getOptionalPost("state");
if ($state == null)
    $state = "";

$params = array(
    "code" => $code,
    "me" => $me,
    "redirect_uri" => $redirect_uri,
    "client_id" => $client_id,
    "state" => $state
);

try {
    $auth = $site->Auth();
    $result = $auth->indieAuthenticate($params);
    if (empty($result["me"]) ||
        $result["me"] !== $site->url)
        do400("Authentication failed for $me");
    $token = $auth->generateToken($client_id, $result["scope"]);
    header("Content-Type: application/x-www-form-urlencoded");
    echo formUrlencode(array(
        "access_token" => $token,
        "me" => $me,
        "scope" => $result["scope"]));
} catch (Exception $e) {
    do500($e->getMessage());
}

?>
