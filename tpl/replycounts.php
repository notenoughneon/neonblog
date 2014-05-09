              <?
                $replycount = count($post["replies"]);
                if ($replycount > 0) {
              ?>
                  <a href="<? echo $post["url"] ?>">Comments <span class="badge"><? echo $replycount ?></span></a>
              <? } ?>

