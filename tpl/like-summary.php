<div class="h-entry">
    <i class="fa fa-star"></i>
    <a href="<?= $e->author->url ?>" class="p-author h-card"><?= $e->author->name ?></a>
    liked this.

<? (new Template($e->likeOf[0]))->render("tpl/cite.php") ?>
</div>

<div class="blog-post-spacer"></div>

