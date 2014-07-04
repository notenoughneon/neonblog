        </div><!-- /.blog-main -->

        <div class="col-sm-3 col-sm-offset-1 blog-sidebar">
          <div class="sidebar-module sidebar-module-inset h-card">
            <h4>About</h4>
            <a class="p-name u-url" href="<? echo $config["siteUrl"] ?>"><? echo $config["authorName"] ?></a>
            <p class="p-note"><? echo $config["authorBlurb"] ?></p>
          </div>
          <div class="sidebar-module">
            <h4>Elsewhere</h4>
            <ol class="list-unstyled">
              <? foreach ($config["elsewhere"] as $name => $url) { ?>
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
