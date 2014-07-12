<?php
namespace Posse;

function bridgyPosseTo($posseUrl) {
    return function($post) use ($posseUrl) {
            $response = json_decode(sendmention($post->url, $posseUrl));
            if ($response === null)
                throw new Exception("Bridgy JSON decode failed");
            return $response->url;
    };
}

function posse($config, $post, $syndicateTos) {

    $handlers = array(
        "twitter.com" => bridgyPosseTo("http://brid.gy/publish/twitter"),
        "facebook.com" => bridgyPosseTo("http://brid.gy/publish/facebook")
    );

    if ($syndicateTos === null) return;

    foreach ($syndicateTos as $syndicateTo) {
        if (array_key_exists($syndicateTo, $handlers)) {
            try {
                echo "POSSEing to $syndicateTo<br>";
                $url = $handlers[$syndicateTo]($post);
                $post->syndication[] = $url;
                $post->save($config);
            } catch (Exception $e) {
                echo "Exception: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "No handler for $syndicateTo.<br>";
        }
    }
}

?>
