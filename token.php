<?php
require("common.php");

/**
 * Authenticate user with indieauth.com
 */
function indieAuthenticate($code, $me) {
    $verify_url = "http://indieauth.com/verify";

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

function generateToken($cfg) {
    //TODO: how secure is this entropy source?
    $token = bin2hex(openssl_random_pseudo_bytes(16));

    $fh = fopen($cfg["tokenFile"], "a+");
    if ($fh === false)
        throw new Exception("Unable to open token file");
    if (!flock($fh, LOCK_EX))
        throw new Exception("Unable to lock token file");
    if (!fwrite($fh, "$token\n"))
        throw new Exception("Unable to write to token file");
    //TODO: are locks automatically released when scripts exit?
    flock($fh, LOCK_UN);
    fclose($fh);

    return $token;
}

if (empty($_POST["code"]))
    do400("'code' not set");
$code = $_POST["code"];
if (empty($_POST["me"]))
    do400("'me' not set");
$me = $_POST["me"];

try {
    if (!indieAuthenticate($code, $me))
        do400("Authentication failed for $me");
    $token = generateToken($config);
    header("Content-Type: application/x-www-form-urlencoded");
    echo "access_token=$token&scope=post&me=" . urlencode($me);
} catch (Exception $e) {
    do500($e->getMessage());
}

?>
