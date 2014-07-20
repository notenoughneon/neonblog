<div class="h-entry">
    <div class="blog-post">
<? if ($e->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $e->name ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="<? echo $e->getContentClass() ?>"><? echo $e->contentHtml ?></div>
<?  if (count($e->children) > 0) { ?>
        <a href="<? echo $e->url ?>"><i class="fa fa-comment"></i> <? echo count($e->children) ?></a>
<? } ?>
<? require("syndication.php") ?>
    </div>
</div>

