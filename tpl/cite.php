<div class="<? echo $e->getRootClass() ?>">
<?
foreach ($e->replyTo as $child) { ?>
    <a class="u-in-reply-to" href="<? echo $child->url ?>"></a>
<? }
foreach ($e->likeOf as $child) { ?>
    <a class="u-like-of" href="<? echo $child->url ?>"></a>
<? }
foreach ($e->repostOf as $child) { ?>
    <a class="u-repost-of" href="<? echo $child->url ?>"></a>
<? } ?>
    <div class="blog-post">
<? if ($e->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $e->name ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="<? echo $e->getContentClass() ?>"><? echo $e->contentValue ?></div>
<? require("actions.php") ?>
    </div>
</div>

