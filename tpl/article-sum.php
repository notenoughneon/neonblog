          <div class="h-entry">
            <div class="blog-post">
              <h2 class="blog-post-title p-name"><? echo $post["name"] ?></h2>
              <? require("meta.php") ?>
              <div class="e-content"><? echo $post["contentHtml"] ?></div>
              <? require("replycounts.php") ?>
            </div><!-- /.blog-post -->
          </div>
