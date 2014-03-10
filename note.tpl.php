          <div class="h-entry">
            <div class="blog-post">
              <p class="blog-post-meta">
                <a class="p-author h-card" href="<? echo $config["siteUrl"] ?>">
                  <img src="<? echo $config["aboutPhoto"] ?>">
                  <? echo $config["aboutName"] ?></a> - 
                <a class="u-url" href="<? echo $post["url"] ?>"><time class="dt-published" datetime="<? echo $post["published"] ?>"><? echo date("M j, Y g:i a", strtotime($post["published"])) ?></time></a>
              </p>
              <div class="p-name e-content"><? echo $post["contentValue"] ?></div>
            </div><!-- /.blog-post -->

            <? foreach ($replies as $reply) {
                if ($reply["type"] == "reply")
                    include("reply.tpl.php");
                else
                    include("mention.tpl.php");
               } ?>
          </div>
