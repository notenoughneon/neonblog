          <div class="h-entry">
            <div class="blog-post">
              <p class="blog-post-meta">
                <a class="p-author h-card" href="<? echo $config["siteUrl"] ?>">
                  <img src="<? echo $config["aboutPhoto"] ?>">
                  <? echo $config["aboutName"] ?></a> - 
                <a class="u-url" href="<? echo $post["url"] ?>"><time class="dt-published" datetime="<? echo $post["published"] ?>"><? echo date("M j, Y g:i a", strtotime($post["published"])) ?></time></a>
                <? if (isset($post["in-reply-to"])) { ?>
                <br>In reply to: <a class="u-in-reply-to" href="<? echo $post["in-reply-to"] ?>"><? echo $post["in-reply-to"] ?></a>
                <? } ?>
              </p>
              <div class="p-name e-content"><? echo $post["contentHtml"] ?></div>
            </div><!-- /.blog-post -->

            <? foreach ($post["replies"] as $reply) {
                if (isset($reply["in-reply-to"]))
                    include("reply.php");
                else
                    include("mention.php");
               } ?>
          </div>
