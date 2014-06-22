        <p class="blog-post-meta">
            <a class="p-author h-card" href="<? echo $this->authorUrl ?>">
                <img src="<? echo $this->authorPhoto ?>">
                <? echo $this->authorName ?> 
            </a>
            -
            <a class="u-url" href="<? echo $this->url ?>">
<? if (isset($this->published)) { ?>
                <time class="dt-published" datetime="<? echo $this->published ?>"><? echo date("M j, Y g:i a", strtotime($this->published)) ?></time>
<? } ?>
            </a>
        </p>

