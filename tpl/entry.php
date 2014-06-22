<div class="<? echo $this->getRootClass() ?>">
<?
foreach ($this->replyTo as $child) {
    echo $child->toHtml();
}
foreach ($this->likeOf as $child) {
    echo $child->toHtml();
}
foreach ($this->repostOf as $child) {
    echo $child->toHtml();
}
?>
    <div class="blog-post">
<? if ($this->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $this->name ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="<? echo $this->getContentClass() ?>"><? echo $this->contentHtml ?></div>
    </div>

    <!-- replies -->
<? foreach ($this->children as $child) { 
    echo $child->toHtml();
} ?>
    <!-- /replies -->
</div>

