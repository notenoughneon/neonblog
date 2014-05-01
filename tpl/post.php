<?
$title = truncate($post["name"],45) . " - " . $config["siteTitle"];
require("header.php");
?>

          <?  if ($post["type"] === "article")
                  include("article.php");
              else
                  include("note.php"); ?>

<? require("footer.php") ?>
