<?php
require_once("jsonstore.php");

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
    $tokenstore->value[$token] = array(
        "client_id" => $client_id,
        "scope" => $scope,
        "date_issued" => date("c")
    );
    $tokenstore->flush();
    return $token;
}

function removeToken($cfg, $token) {
    $tokenstore = new JsonStore($cfg["tokenFile"]);
    unset($tokenstore->value[$token]);
    $tokenstore->flush();
}

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
    return isset($tokenstore->value[$token])
        && $tokenstore->value[$token]["scope"] === $scope;
}

function requireAuthorization($cfg, $scope) {
    if (!isAuthorized($cfg, $scope)) {
        header("WWW-Authenticate: Bearer realm=\"$scope\"");
        do401();
    }
}

?>
