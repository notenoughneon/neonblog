<div class="h-entry">
    <div class="blog-post">
<? if ($this->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $this->highlight($this->name, $query) ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="e-summary"><? echo $this->highlight($this->contentValue, $query) ?></div>
    </div>
</div>


