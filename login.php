<?
require("lib/common.php");
$title = "Login - " . $config["siteTitle"];
require("tpl/header.php");
?>

        <form action="http://indieauth.com/auth" method="get">
        <input type="hidden" name="me" value="<? echo $config["siteUrl"] ?>" />
            <input type="hidden" name="client_id" value="<? echo $config["siteUrl"] ?>" />
            <input type="hidden" name="redirect_uri" value="<? echo $config["siteUrl"] . "/authcb.php" ?>" />
            <input type="hidden" name="scope" value="post" />
            <button type="submit">Sign in</button>
        </form>

<? require("tpl/footer.php") ?>
