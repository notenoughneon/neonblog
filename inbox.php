<?
require("lib/init.php");

$auth = $site->Auth();
$token = isset($_COOKIE["bearer_token"]) ? $_COOKIE["bearer_token"] : null;
$auth->requireAuthorization("post", $token);

$webmentions = $site->Webmentions();
$feed = $site->LocalFeed();

$sourceUrl = getOptionalPost("source");
$targetUrl = getOptionalPost("target");
$verb = getOptionalPost("verb");

if ($sourceUrl !== null && $targetUrl !== null) {
    $auth->requireAuthorization("post");

    $html = fetchPage($sourceUrl);
    $sourcePost = new Microformat\Cite();
    $sourcePost->loadFromHtml($html, $sourceUrl);
    if ($verb != "reject") {
        if ($sourcePost->isReplyTo($targetUrl) || linksTo($html, $targetUrl)) {
            $targetPost = $feed->getByUrl($targetUrl);
            $targetPost->children[] = $sourcePost;
            $site->save($targetPost);
        } else {
            throw new Exception("No reply found");
        }
    }
    $webmentions->value = array_values(
        array_filter($webmentions->value,
            function($m) use($sourceUrl, $targetUrl) {
                return $m["source"] !== $sourceUrl
                    || $m["target"] !== $targetUrl;
        }));
    $webmentions->sync();

}

$site->renderHeader("Inbox");
?>
<h1>Inbox</h1>
<? foreach ($webmentions->value as $mention) {
    $html = fetchPage($mention["source"]);
    $source = new Microformat\Cite();
    $source->loadFromHtml($html);
    $target = $feed->getByUrl($mention["target"]);
?>
        <div class="row">
            <div>
<? (new Template($target))->render("tpl/cite-short.php") ?>
            </div>
            <div>
            <i>Source: <a href="<?= $mention["source"] ?>"><?= truncate($mention["source"], 45) ?></a></i>
<? (new Template($source))->render("tpl/cite.php") ?>
            </div>
            <div>
                <form action="inbox.php" method="post">
                <input type="hidden" name="source" value="<?= $mention["source"] ?>">
                <input type="hidden" name="target" value="<?= $mention["target"] ?>">
                <input type="hidden" name="access_token" value="<?= $token ?>">
                <button name="verb" value="accept" type="submit" class="btn btn-sm btn-primary">Accept</button>
                <button name="verb" value="reject" type="submit" class="btn btn-sm">Reject</button>
                </form>
            </div>
        </div>
<? } ?>

<? $site->renderFooter(); ?>
