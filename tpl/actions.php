        <div class="blog-post-bottom">
            <span class="if-logged-inline blog-post-actions">
                <a href="/post.php?reply-to=<?= urlencode($e->url) ?>"><i class="fa fa-reply"></i></a>
                <a href="/post.php?repost-of=<?= urlencode($e->url) ?>"><i class="fa fa-retweet"></i></a>
                <a href="/post.php?like-of=<?= urlencode($e->url) ?>"><i class="fa fa-star"></i></a>
            </span>
            &nbsp;
            <span class="blog-post-syndication">
<? foreach ($e->syndication as $syndicated) {
    if (stripos($syndicated, "twitter.com") !== false)
        $icon = "fa-twitter";
    else if (stripos($syndicated, "facebook.com") !== false)
        $icon = "fa-facebook";
    else if (stripos($syndicated, "instagram.com") !== false)
        $icon = "fa-instagram";
    else if (stripos($syndicated, "tumblr.com") !== false)
        $icon = "fa-tumblr";
    else
        $icon = "fa-external-link";
?>
                <a class="u-syndication" href="<?= $syndicated ?>"><i class="fa <?= $icon ?>"></i></a>
<? } ?>
            </span>
        </div>

