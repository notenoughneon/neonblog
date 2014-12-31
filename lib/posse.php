<?php
class Posse {
    public function __construct($site) {
        $this->site = $site;
        $this->handlers = array(
            "twitter.com" => $this->bridgyPosseTo("http://brid.gy/publish/twitter"),
            "facebook.com" => $this->bridgyPosseTo("http://brid.gy/publish/facebook")
        );

    }

    public function getAvailableTargets() {
        return array_keys($this->handlers);
    }

    public function dummyPosseTo($posseUrl) {
        return function($post) use ($posseUrl) {
            return $posseUrl;
        };
    }

    public function bridgyPosseTo($posseUrl) {
        return function($post) use ($posseUrl) {
                $response = json_decode(Webmention::send($post->url, $posseUrl));
                if ($response === null)
                    throw new Exception("Bridgy JSON decode failed");
                return $response->url;
        };
    }

    public function posseTo($post, $syndicateTos) {
        if ($syndicateTos === null) return;
        // if not already an array, assume comma delimited list
        if (!is_array($syndicateTos))
            $syndicateTos = explode(",", $syndicateTos);
        foreach ($syndicateTos as $syndicateTo) {
            if (array_key_exists($syndicateTo, $this->handlers)) {
                try {
                    echo "POSSEing to $syndicateTo<br>";
                    $url = $this->handlers[$syndicateTo]($post);
                    $post->syndication[] = $url;
                    $this->site->save($post);
                } catch (Exception $e) {
                    echo "Exception: " . $e->getMessage() . "<br>";
                }
            } else {
                echo "No handler for $syndicateTo.<br>";
            }
        }
    }

}
?>
