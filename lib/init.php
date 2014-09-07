<?php
ini_set("display_errors", 1);
require("lib/common.php");
require("lib/jsonstore.php");
require("lib/auth.php");
require("lib/microformat.php");
require("lib/posse.php");
require("lib/oembed.php");
require("lib/template.php");
require("lib/webmention.php");
require("lib/site.php");

$site = new Site();
?>
