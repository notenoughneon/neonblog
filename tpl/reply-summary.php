<div class="h-entry">
<? foreach ($e->replyTo as $child) { ?>
    <i class="fa fa-reply"></i>
    In reply to <a href="<?= $child->url ?>" class="p-in-reply-to"><?= truncate($child->name, 90) ?></a>
<? } ?>
    <div class="blog-post">
<? if ($e->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $e->name ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="<? echo $e->getContentClass() ?>"><? echo $e->contentHtml ?></div>
<? require("actions.php") ?>
    </div>
</div>

<div class="blog-post-spacer"></div>

