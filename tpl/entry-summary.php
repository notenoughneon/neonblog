<div class="h-entry">
    <div class="blog-post">
<? if ($this->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $this->name ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="<? echo $this->getContentClass() ?>"><? echo $this->contentHtml ?></div>
<?  if (count($this->children) > 0) { ?>
        <a href="<? echo $this->url ?>"><i class="fa fa-comment"></i> <? echo count($this->children) ?></a>
<? } ?>
<? require("syndication.php") ?>
    </div>
</div>

