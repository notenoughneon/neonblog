          <div class="h-entry">
            <div class="blog-post">
              <h2 class="blog-post-title p-name"><? echo $post["name"] ?></h2>
              <p class="blog-post-meta">
                <a class="p-author h-card" href="<? echo $config["siteUrl"] ?>">
                  <img src="<? echo $config["aboutPhoto"] ?>">
                  <? echo $config["aboutName"] ?></a> - 
                <a class="u-url" href="<? echo $post["url"] ?>"><time class="dt-published" datetime="<? echo $post["published"] ?>"><? echo date("M j, Y g:i a", strtotime($post["published"])) ?></time></a>
              </p>
              <div class="e-content"><? echo $post["contentHtml"] ?></div>
              <?
                $replycount = count($post["replies"]);
                if ($replycount > 0) {
              ?>
                  <a href="<? echo $post["url"] ?>"><span class="glyphicon glyphicon-comment"></span> <? echo $replycount ?></a>
              <? } ?>
            </div><!-- /.blog-post -->
          </div>
