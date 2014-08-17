<?php
require("lib/init.php");

function autoLink($content) {
    return preg_replace("~\b((https?://)?[\w-]*[a-z][\w-]*(\.[\w-]+)+(/[\w\./%+?=&#\~-]+)?)\b~i", "<a href=\"$1\">$1</a>", $content);
}

$auth = $site->Auth();

$auth->requireAuthorization("post");

$h = getRequiredPost("h");
if ($h !== "entry")
    do400("Unsupported object type: '$h'");

$feed = $site->LocalFeed();
$post = new Microformat\Entry();
$post->author = new Microformat\Card();
$post->author->name = $site->authorName;
$post->author->photo = $site->authorPhoto;
$post->author->url = $site->url;
$post->name = getOptionalPost("name");
$content = getOptionalPost("content");
$post->contentHtml = $content;
if ($content !== null && $post->name == null)
    $post->contentHtml = autolink(htmlspecialchars($post->contentHtml));
$post->published = getOptionalPost("published");
if ($post->published === null)
    $post->published = date("c");

$syndication = getOptionalPost("syndication");
if ($syndication !== null) {
    if (is_array($syndication))
        $post->syndication = $syndication;
    else
        $post->syndication = array($syndication);
}

$replyto = getOptionalPost("in-reply-to");
if ($replyto != null) {
    $html = fetchPage($replyto);
    $replyCite = new Microformat\Cite(array("in-reply-to"));
    $replyCite->loadFromHtml($html, $replyto);
    $post->replyTo[] = $replyCite;
}

$repostof = getOptionalPost("repost-of");
if ($repostof != null) {
    $html = fetchPage($repostof);
    $repostCite = new Microformat\Cite(array("repost-of"));
    $repostCite->loadFromHtml($html, $repostof);
    $post->repostOf[] = $repostCite;
}

$likeof = getOptionalPost("like-of");
if ($likeof != null) {
    $html = fetchPage($likeof);
    $likeCite = new Microformat\Cite(array("like-of"));
    $likeCite->loadFromHtml($html, $likeof);
    $post->likeOf[] = $likeCite;
}

$slug = $site->generateSlug($post->name, $post->published);
$post->file = $slug . $site->postExtension;
$post->url = $site->url . "/" . $slug;

$photo = getOptionalFile("photo");
if ($content === null && $photo === null)
    do400("Either content or photo must be set");
if ($photo !== null) {
    $extension = strtolower(pathinfo($photo["name"], PATHINFO_EXTENSION));
    // if unknown filetype, assume jpg
    if (!in_array($extension, array("jpg", "gif", "png")))
        $extension = "jpg";
    $photoFile = "$slug.$extension";
    makeDirs($photoFile);
    if (!move_uploaded_file($photo["tmp_name"], $photoFile))
        throw new Exception("Failed to move upload to $photoFile");
    // resize photo
    $img = new Imagick($photoFile);
    if ($img->getImageWidth() > $site->maxPhotoWidth ||
        $img->getImageHeight() > $site->maxPhotoHeight) {
        $img->resizeImage($site->maxPhotoWidth, $site->maxPhotoHeight,
            imagick::FILTER_LANCZOS, 1, true);
        $photoFile = "$slug.jpg";
        $img->writeImage($photoFile);
    }
    $post->photo = $photoFile;
    $post->contentHtml = "<img class=\"u-photo\" src=\"/" . $photoFile . "\">" . $post->contentHtml;
}

try {
    $site->save($post);
    $feed->add($post);
    $location = $post->url;
    do201($location);

    $site->Posse()->posseTo($post, getOptionalPost("syndicate-to"));

    foreach ($post->getLinks() as $link) {
        try {
            echo "Sending webmention: $location -&gt; $link<br>";
            Webmention::send($location, $link);
            echo "Success<br>";
        } catch (Exception $e) {
            echo "Failed: " . $e->getMessage() . "<br>";
        }
    }
} catch (Exception $e) {
    do500($e->getMessage());
}

?>
