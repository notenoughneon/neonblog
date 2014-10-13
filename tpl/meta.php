        <p class="blog-post-meta">
            <a class="p-author h-card" href="<? echo $e->author->url ?>">
<? if (empty($hideAuthor)) { ?>
                <img src="<? echo $e->author->photo ?>">
                <? echo $e->author->name ?>
<? } ?>
            </a>
<? if (isset($e->published)) { ?>
<? if (empty($hideAuthor)) { ?>
            -
<? } ?>
            <a class="u-url" href="<? echo $e->url ?>">
                <time class="dt-published" datetime="<? echo $e->published ?>" title="<? echo date("j M Y g:i a", strtotime($e->published)) ?>"><? echo date("j M Y g:i a", strtotime($e->published)) ?></time>
            </a>
<? } ?>
        </p>

