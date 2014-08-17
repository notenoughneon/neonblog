<div class="h-entry">
    <i class="fa fa-retweet"></i>
    <a href="<?= $e->author->url ?>" class="p-author h-card"><?= $e->author->name ?></a>
    reposted this.

<? (new Template($e->repostOf[0]))->render("tpl/cite.php") ?>
</div>

<div class="blog-post-spacer"></div>

