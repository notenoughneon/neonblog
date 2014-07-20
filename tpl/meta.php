        <p class="blog-post-meta">
            <a class="p-author h-card" href="<? echo $e->authorUrl ?>">
                <img src="<? echo $e->authorPhoto ?>">
                <? echo $e->authorName ?> 
            </a>
<? if (isset($e->published)) { ?>
            -
            <a class="u-url" href="<? echo $e->url ?>">
                <time class="dt-published" datetime="<? echo $e->published ?>" title="<? echo date("j M Y g:i a", strtotime($e->published)) ?>"><? echo date("j M Y g:i a", strtotime($e->published)) ?></time>
            </a>
<? } ?>
        </p>

