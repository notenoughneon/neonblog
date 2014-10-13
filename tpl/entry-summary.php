<div class="h-entry">
    <div class="blog-post">
<? if ($e->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $e->name ?></h2>
<? } ?>
<? $hideAuthor = true; require("meta.php") ?>
        <div class="<? echo $e->getContentClass() ?>"><? echo $e->contentHtml ?></div>
<? require("actions.php") ?>
    </div>
</div>

<div class="blog-post-spacer"></div>

