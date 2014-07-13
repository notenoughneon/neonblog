<?
require("lib/common.php");
require("lib/microformat.php");
require("lib/auth.php");

$token = isset($_COOKIE["bearer_token"]) ? $_COOKIE["bearer_token"] : null;
requireAuthorization($config, "post", $token);

$webmentions = new JsonStore($config["webmentionFile"]);
$feed = new Microformat\Localfeed("postindex.json");

$sourceUrl = getOptionalPost("source");
$targetUrl = getOptionalPost("target");
$verb = getOptionalPost("verb");

if ($sourceUrl !== null && $targetUrl !== null) {
    requireAuthorization($config, "post");

    $html = fetchPage($sourceUrl);
    $sourcePost = new Microformat\Cite();
    $sourcePost->loadFromHtml($html, $sourceUrl);
    if ($verb != "reject") {
        if ($sourcePost->isReplyTo($targetUrl)) {
            $targetPost = $feed->getByUrl($targetUrl);
            $targetPost->children[] = $sourcePost;
            $targetPost->save($config);
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

$title = "Inbox - " . $config["siteTitle"];
require("tpl/header.php");
?>
<h1>Inbox</h1>
<? foreach ($webmentions->value as $mention) {
    $html = fetchPage($mention["source"]);
    $source = new Microformat\Cite();
    $source->loadFromHtml($html);
    $target = $feed->getByUrl($mention["target"]);
?>
        <div class="row">
            <div><?= $target->toSearchHit("") ?></div>
            <div>
            <i>Source: <a href="<?= $mention["source"] ?>"><?= truncate($mention["source"], 45) ?></a></i>
                <?= $source->toHtmlSummary() ?>
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

<? require("tpl/footer.php") ?>
