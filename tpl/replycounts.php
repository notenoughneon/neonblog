              <?
                $replycount = count($post["replies"]);
                if ($replycount > 0) {
              ?>
                  <a href="<? echo $post["url"] ?>"><span class="glyphicon glyphicon-comment"></span> <? echo $replycount ?></a>
              <? } ?>

