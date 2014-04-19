<? 
$title = $config["siteTitle"];
require("header.tpl.php");
?>

          <?foreach ($posts as $post) {
              if ($post["type"] === "article")
                  include("article.tpl.php");
              else
                  include("note.tpl.php");
            } ?>

          <ul class="pager">
            <? if ($prevUrl != null) { ?>
            <li><a href="<? echo $prevUrl ?>">Previous</a></li>
            <? } if ($nextUrl != null) { ?>
            <li><a href="<? echo $nextUrl ?>">Next</a></li>
            <? } ?>
          </ul>

<? require("footer.tpl.php") ?>
