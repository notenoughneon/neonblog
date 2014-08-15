<div class="h-entry">
    <i class="fa fa-star"></i>
    <a href="<?= $e->authorUrl ?>" class="p-author h-card"><?= $e->authorName ?></a>
    liked this.

<? (new Template($e->likeOf[0]))->render("tpl/cite.php") ?>
</div>

<div class="blog-post-spacer"></div>

