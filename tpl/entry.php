<div class="<? echo $e->getRootClass() ?>">
<?
foreach (array_merge($e->replyTo, $e->likeOf, $e->repostOf) as $child) {
    (new Template($child))->render("tpl/cite.php");
}
?>
    <div class="blog-post">
<? if ($e->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $e->name ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="<? echo $e->getContentClass() ?>"><? echo $e->contentHtml ?></div>
<? require("syndication.php") ?>
    </div>

    <!-- replies -->
<? foreach ($e->children as $child) {
    (new Template($child))->render("tpl/cite.php");
} ?>
    <!-- /replies -->
</div>

