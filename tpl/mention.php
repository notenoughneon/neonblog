            <div class="blog-reply h-cite">
              <p class="blog-post-meta">
                <a class="p-author h-card" href="<? echo $reply["authorUrl"] ?>">
                  <img src="<? echo $reply["authorPhoto"] ?>">
                  <? echo $reply["authorName"] ?></a> -
                <a class="u-url" href="<? echo $reply["url"] ?>"><time class="dt-published" datetime="<? echo $reply["published"] ?>"><? echo date("M j, Y g:i a", strtotime($reply["published"])) ?></time></a>
              </p>
              <a href="<? echo $reply["url"] ?>"><? echo $reply["url"] ?></a>
            </div>

