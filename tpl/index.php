<? 
$title = $config["siteTitle"];
require("header.php");
?>

          <?foreach ($posts as $post) {
              if ($post["type"] === "article")
                  include("article.php");
              else
                  include("note.php");
            } ?>

          <ul class="pager">
            <? if ($prevUrl != null) { ?>
            <li><a href="<? echo $prevUrl ?>">Previous</a></li>
            <? } if ($nextUrl != null) { ?>
            <li><a href="<? echo $nextUrl ?>">Next</a></li>
            <? } ?>
          </ul>

<? require("footer.php") ?>
