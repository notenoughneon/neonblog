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

  </head>

  <body>

    <div class="container">

      <div class="blog-header">
          <h1 class="blog-title"><a href="<? echo $config["siteUrl"] ?>"><? echo $config["siteTitle"] ?></a></h1>
      </div>

      <div class="row">

        <div class="col-sm-8 blog-main">

