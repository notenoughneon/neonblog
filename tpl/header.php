<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><? echo $pageTitle ?></title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/font-awesome/css/font-awesome.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/css/blog.css" rel="stylesheet">

    <link href="<? echo $this->url . "/webmention.php" ?>" rel="webmention">
    <link href="https://indieauth.com/auth" rel="authorization_endpoint">
    <link href="<? echo $this->url . "/token.php" ?>" rel="token_endpoint">
    <link href="<? echo $this->url . "/micropub.php" ?>" rel="micropub">
  </head>

  <body>

    <div class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <a class="navbar-brand" href="/"><? echo $this->title ?></a>
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li><a href="/?t=article">Articles</a></li>
                    <li><a href="/?t=note">Notes</a></li>
                    <li><a href="/?t=photo">Photos</a></li>
                </ul>
                <form class="if-logged-out navbar-form navbar-right" role="form" action="http://indieauth.com/auth" method="get">
                <input type="hidden" name="me" value="<? echo $this->url ?>" />
                <input type="hidden" name="client_id" value="<? echo $this->url ?>" />
                <input type="hidden" name="redirect_uri" value="<? echo $this->url . "/authcb.php" ?>" />
                <input type="hidden" name="scope" value="post" />
                    <button type="submit" class="btn">Sign in</button>
                </form>
                <ul class="if-logged-in nav navbar-nav">
                    <li><a href="/inbox.php">Inbox</a></li>
                    <li><a href="/post.php">Post</a></li>
                    <li><a href="/feed.php">Reader</a></li>
                </ul>
                <form class="if-logged-in navbar-form navbar-right" role="form" action="/logout.php" method="post">
                    <button type="submit" class="btn">Sign out</button>
                </form>
                <form class="navbar-form navbar-right" role="search" action="/search.php" method="get">
                    <input type="text" class="form-control" placeholder="Search" name="q">
                </form>
            </div>
        </div>
    </div>

    <div class="container">

      <div class="row">

        <div class="col-sm-8 blog-main">

