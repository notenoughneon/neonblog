<?php
class OEmbed {
    public function __construct() {
        $this->handlers = array(
            "~^https?://(www\.)?youtube\.com/~i" =>
                $this->oEmbedHandler("http://www.youtube.com/oembed"),
            "~^https?://(www\.)?soundcloud\.com/~i" =>
                $this->oEmbedHandler("http://soundcloud.com/oembed",
                    array("maxheight" => "166")),
            "~\.(jpg|jpeg|gif|png)$~i" =>
                $this->imageEmbedHandler()
        );

    }

    public function resolve($link) {
        foreach ($this->handlers as $pattern => $handler) {
            if (preg_match($pattern, $link) === 1) {
                return $handler($link);
            }
        }
        return null;
    }

    private static function oEmbedHandler($apiUrl, $extraArgs = array()) {
        return function($link) use($apiUrl, $extraArgs) {
            $args = array_merge(array("url" => $link, "format" => "json"), $extraArgs);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "$apiUrl?" . formUrlencode($args));
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $page = curl_exec($ch);
            $mimetype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($page === false) {
                $error = curl_error($ch);
                throw new Exception("Web request failed: $error");
            } else if ($httpcode != 200) {
                throw new Exception("Bad http code: $httpcode");
            } else if (!startsWith($mimetype, "application/json")) {
                throw new Exception("Bad mimetype: $mimetype");
            }
            curl_close($ch);
            $response = json_decode($page);
            if (empty($response->html))
                throw new Exception("Bad oembed response: $page");
            return $response->html;
        };
    }

    private static function imageEmbedHandler() {
        return function ($link) {
            return "<img src=\"$link\" />";
        };
    }

}
?>
