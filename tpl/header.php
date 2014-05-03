<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><? echo $title ?></title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/css/blog.css" rel="stylesheet">

    <link href="<? echo $config["siteUrl"] . "/webmention.php" ?>" rel="webmention">
    <link href="https://indieauth.com/auth" rel="authorization_endpoint">
    <link href="<? echo $config["siteUrl"] . "/token.php" ?>" rel="token_endpoint">
    <link href="<? echo $config["siteUrl"] . "/micropub.php" ?>" rel="micropub">
    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/bootstrap.min.js"></script>

  </head>

  <body>

    <div class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="navbar-collapse collapse">
                <?
                if (empty($_COOKIE["bearer_token"])) {
                ?>
                <form class="navbar-form navbar-right" role="form" action="http://indieauth.com/auth" method="get">
                <input type="hidden" name="me" value="<? echo $config["siteUrl"] ?>" />
                <input type="hidden" name="client_id" value="<? echo $config["siteUrl"] ?>" />
                <input type="hidden" name="redirect_uri" value="<? echo $config["siteUrl"] . "/authcb.php" ?>" />
                <input type="hidden" name="scope" value="post" />
                    <button type="submit" class="btn">Sign in</button>
                </form>
                <? } else { ?>
                <ul class="nav navbar-nav">
                    <li><a href="post.php">Post</a></li>
                </ul>
                <form class="navbar-form navbar-right" role="form" action="logout.php" method="post">
                    <button type="submit" class="btn">Sign out</button>
                </form>
                <? } ?>
            </div>
        </div>
    </div>

    <div class="container">

      <div class="blog-header">
          <h1 class="blog-title"><a href="<? echo $config["siteUrl"] ?>"><? echo $config["siteTitle"] ?></a></h1>
      </div>

      <div class="row">

        <div class="col-sm-8 blog-main">

