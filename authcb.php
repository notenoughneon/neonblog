<?php
require("lib/common.php");
require("lib/auth.php");

$code = $_GET["code"];
$me = $_GET["me"];
$client_id = $config["siteUrl"];
$redirect_uri = $config["siteUrl"] . "/authcb.php";
$params = array(
    "code" => $code,
    "client_id" => $client_id,
    "redirect_uri" => $redirect_uri
);
$auth = indieAuthenticate($params);
if (isset($auth["me"]) && $auth["me"] === $config["siteUrl"]) {
    $token = generateToken($config, $me, $client_id, $auth["scope"]);
    setcookie("bearer_token", $token, time() + 60*60*24*365);
    do302("post.php");
}

?>
