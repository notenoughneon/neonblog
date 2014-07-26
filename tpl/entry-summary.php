<div class="h-entry">
    <div class="blog-post">
<? if ($e->isArticle()) { ?>
        <h2 class="blog-post-title p-name"><? echo $e->name ?></h2>
<? } ?>
<? require("meta.php") ?>
        <div class="<? echo $e->getContentClass() ?>"><? echo $e->contentHtml ?></div>
<? $count = count($e->children); if ($count > 0) { ?>
        <a href="<? echo $e->url ?>"><? echo $count . " repl" . ($count > 1 ? "ies" : "y") ?></a>
<? } ?>
<? require("syndication.php") ?>
    </div>
</div>

<div class="blog-post-spacer"></div>

