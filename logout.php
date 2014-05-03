<?php
require("lib/common.php");
require("lib/auth.php");

if (isset($_COOKIE["bearer_token"])) {
    removeToken($config, $_COOKIE["bearer_token"]);
    setcookie("bearer_token", "", time() - 3600);
}
do302("/");
?>
