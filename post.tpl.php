<?
$title = truncate($post["name"],45) . " - " . $config["siteTitle"];
require("header.tpl.php");
?>

          <?  if ($post["type"] === "article")
                  include("article.tpl.php");
              else
                  include("note.tpl.php"); ?>

<? require("footer.tpl.php") ?>
