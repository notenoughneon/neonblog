<?php
require("lib/init.php");
$auth = $site->Auth();

if (isset($_COOKIE["bearer_token"])) {
    $auth->removeToken($config, $_COOKIE["bearer_token"]);
    setcookie("bearer_token", "", time() - 3600);
}
do302("/");
?>
