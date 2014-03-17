<!DOCTYPE html>
<? require("common.php") ?>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><? echo "Post - " . $config["siteTitle"] ?></title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/css/blog.css" rel="stylesheet">

  </head>

  <body>

    <div class="container">

      <div class="blog-header">
          <h1 class="blog-title"><a href="<? echo $config["siteUrl"] ?>"><? echo $config["siteTitle"] ?></a></h1>
      </div>

      <div class="row">

        <div class="col-sm-8 blog-main">

            <form class="form-horizontal">
                <legend>Post</legend>

                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Title:</label>
                    <div class="col-sm-10">
                        <input id="name" type="text" class="form-control" placeholder="Leave blank for note" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="reply-to" class="col-sm-2 control-label">Reply to:</label>
                    <div class="col-sm-10">
                        <input id="reply-to" type="text" class="form-control" placeholder="http://example.com/post-id" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="content" class="col-sm-2 control-label">Content:</label>
                    <div class="col-sm-10">
                        <textarea id="content" class="form-control" rows="8"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Submit</input>
                    </div>
                </div>

            </form>

        </div><!-- /.blog-main -->

        <div class="col-sm-3 col-sm-offset-1 blog-sidebar">
          <div class="sidebar-module sidebar-module-inset h-card">
            <h4>About</h4>
            <a class="p-name u-url" href="<? echo $config["siteUrl"] ?>"><? echo $config["aboutName"] ?></a>
            <p class="p-note"><? echo $config["aboutBlurb"] ?></p>
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
    <!--
    <script src="/js/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    -->
  

</body></html>
