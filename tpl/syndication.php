      <span class="blog-post-syndication">
<? foreach ($this->syndication as $syndicated) { 
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

