<div class="h-entry">
    <i class="fa fa-retweet"></i>
    <a href="<?= $e->authorUrl ?>" class="p-author h-card"><?= $e->authorName ?></a>
    reposted this.

<? (new Template($e->repostOf[0]))->render("tpl/cite.php") ?>
</div>

<div class="blog-post-spacer"></div>

