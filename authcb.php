<?php
require("lib/init.php");

$code = $_GET["code"];
$me = $_GET["me"];
$client_id = $site->url;
$redirect_uri = $site->url . "/authcb.php";
$params = array(
    "code" => $code,
    "client_id" => $client_id,
    "redirect_uri" => $redirect_uri
);
$auth = $site->Auth();
$result = $auth->indieAuthenticate($params);
if (isset($result["me"]) && $result["me"] === $site->url) {
    $token = $auth->generateToken($client_id, $result["scope"]);
    setcookie("bearer_token", $token, time() + 60*60*24*365);
    do302("post.php");
}

?>
