<?php
require_once("php-mf2/Mf2/Parser.php");

class Webmention {
    public static function discoverEndpoint($target) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $target,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3));
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            throw new Exception("Web request failed: " . curl_error($ch));
        } else if ($httpcode != 200) {
            throw new Exception("Bad http code: $httpcode");
        }
        curl_close($ch);
        //check Link header
        list($headers, $body) = explode("\r\n\r\n", $response, 2);
        if (preg_match("/^Link: <(.*)>; rel=webmention$/m", $headers, $matches) === 1) {
            return $matches[1];
        }
        //check <link> and <a> tags
        $mf = Mf2\Parse($body, $target);
        if (isset($mf["rels"])) {
            if (isset($mf["rels"]["webmention"]))
                return $mf["rels"]["webmention"][0];
            if (isset($mf["rels"]["http://webmention.org/"]))
                return $mf["rels"]["http://webmention.org/"][0];
        }
        return false;
    }

    public static function send($source, $target) {
        //discover webmention endpoint
        $endpoint = Webmention::discoverEndpoint($target);
        if ($endpoint === false)
            throw new Exception("No webmention endpoint found");
        //send webmention
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, formUrlencode(array(
            "source" => $source,
            "target" => $target)));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $page = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!($httpcode == 200 || $httpcode == 202))
            throw new Exception("Bad http code: $httpcode\n" . $page);
        return $page;
    }

}
?>
