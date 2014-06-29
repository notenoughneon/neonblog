<div class="h-entry">
    <div class="blog-post">
<? if ($this->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $this->name ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="e-summary"><? echo $this->highlight($query) ?></div>
    </div>
</div>


