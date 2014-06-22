<div class="<? echo $this->getRootClass() ?>">
<?
foreach ($this->replyTo as $child) { ?>
    <a class="u-in-reply-to" href="<? echo $child->url ?>"></a>
<? }
foreach ($this->likeOf as $child) { ?>
    <a class="u-in-reply-to" href="<? echo $child->url ?>"></a>
<? }
foreach ($this->repostOf as $child) { ?>
    <a class="u-in-reply-to" href="<? echo $child->url ?>"></a>
<? } ?>
    <div class="blog-post">
<? if ($this->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $this->name ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="<? echo $this->getContentClass() ?>"><? echo $this->contentValue ?></div>
    </div>
</div>

