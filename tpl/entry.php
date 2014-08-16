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
<? require("actions.php") ?>
        <a class="if-logged-inline" href="/more.php?u=<?= urlencode($e->url) ?>">more...</a>
    </div>

    <form class="form-inline webmention-form" method="post" action="<? echo "$site->url/webmention.php" ?>">
        <div class="form-group">
        <p class="help-block">Have you written a reply to this?</p>
        <input type="hidden" name="target" value="<? echo $e->url ?>">
        <input class="form-control" type="text" name="source" placeholder="http://yoursite.com/reply">
        <button type="submit" class="btn btn-default">Send webmention</button>
        </div>
    </form>
    <!-- replies -->
<? foreach ($e->children as $child) {
    (new Template($child))->render("tpl/cite.php");
} ?>
    <!-- /replies -->
</div>

