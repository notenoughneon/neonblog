<?
require("lib/init.php");

$token = $_COOKIE["bearer_token"];
$url = getOptionalRequest("u");
$post = $site->LocalFeed()->getByUrl($url);

$action = getOptionalPost("action");
$auth = $site->Auth();
if ($action == "posse") {
    $auth->requireAuthorization("post");
    $target = getRequiredPost("target");
    $site->Posse()->posseTo($post, array($target));
    exit();
} else if ($action == "manualposse") {
    $auth->requireAuthorization("post");
    $syndication = getRequiredPost("syndication");
    $post->syndication[] = $syndication;
    $site->save($post);
    exit();
}

$site->renderHeader("More options");
?>
            <h4>More options for "<?= truncate($post->name, 90) ?>"</h4>

            <form class="form-horizontal" action="more.php" method="post">
                <input type="hidden" name="access_token" value="<? echo $token ?>" />
                <input type="hidden" name="u" value="<?= $url ?>" />
                <input type="hidden" name="action" value="posse" />
                <legend>POSSE</legend>

                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label"></label>
                    <div class="col-sm-10">
<? foreach ($site->Posse()->getAvailableTargets() as $target) { ?>
                        <button type="submit" class="btn btn-primary" name="target" value="<?= $target ?>"><?= $target ?></button>
<? } ?>
                    </div>
                </div>
            </form>

            <form class="form-horizontal" action="more.php" method="post">
                <input type="hidden" name="access_token" value="<? echo $token ?>" />
                <input type="hidden" name="u" value="<?= $url ?>" />
                <input type="hidden" name="action" value="manualposse" />
                <legend>Manual POSSE</legend>

                <div class="form-group">
                    <label for="syndication" class="col-sm-2 control-label">Url:</label>
                    <div class="col-sm-10">
                        <input id="syndication" name="syndication" type="text" class="form-control" placeholder="http://remote.url/postid" />
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>

            </form>

<? $site->renderFooter(); ?>
