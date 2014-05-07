            <? foreach ($post["replies"] as $reply) {
                if (isset($reply["in-reply-to"]))
                    include("reply.php");
                else
                    include("mention.php");
               } ?>

