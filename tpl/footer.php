        </div><!-- /.blog-main -->

        <div class="col-sm-3 col-sm-offset-1 blog-sidebar">
          <div class="sidebar-module sidebar-module-inset">
            <h4>About</h4>
            <div class="h-card">
                <img class="u-photo" src="<? echo $this->authorPhoto ?>">
                <a class="p-name u-url" rel="me" href="<? echo $this->url ?>">
                    <? echo $this->authorName ?>
                </a>
                <p class="p-note"><? echo $this->authorBlurb ?></p>
            </div>
          </div>
          <div class="sidebar-module">
            <h4>Elsewhere</h4>
            <ol class="list-unstyled">
              <? foreach ($this->elsewhere as $name => $url) { ?>
              <li><a href="<? echo $url ?>" rel="me"><? echo $name ?></a></li>
              <? } ?>
            </ol>
          </div>
        </div><!-- /.blog-sidebar -->

      </div><!-- /.row -->

    </div><!-- /.container -->

    <div class="blog-footer">
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/js/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/blog.js"></script>
  

</body></html>
