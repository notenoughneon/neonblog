<? $showAuthor = empty($hideAuthor) || !$hideAuthor; ?>
        <p class="blog-post-meta">
<? if ($showAuthor) { ?>
            <a class="p-author h-card" href="<? echo $e->author->url ?>">
                <img src="<? echo $e->author->photo ?>">
                <? echo $e->author->name ?>
            </a>
<? } ?>
<? if ($showAuthor && isset($e->published)) { ?>
            -
<? } ?>
            <a class="u-url" href="<? echo $e->url ?>">
<? if (isset($e->published)) { ?>
                <time class="dt-published" datetime="<? echo $e->published ?>" title="<? echo date("j M Y g:i a", strtotime($e->published)) ?>"><? echo date("j M Y g:i a", strtotime($e->published)) ?></time>
<? } else { echo $e->url; } ?>
            </a>
        </p>

