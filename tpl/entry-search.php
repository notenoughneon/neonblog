<div class="h-entry">
    <div class="blog-post">
<? if ($e->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo highlight($e->name, $query) ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="e-summary"><? echo highlight($e->contentValue, $query) ?></div>
    </div>
</div>


