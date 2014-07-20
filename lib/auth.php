<?php
class Auth {
    public function __construct($site) {
        $this->site = $site;
    }

    public function indieAuthenticate($params) {
        $verify_url = "https://indieauth.com/auth";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verify_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, formUrlencode($params));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $page = curl_exec($ch);
        $mimetype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($page === false)
            throw new Exception("GET $verify_url failed");
        if ($mimetype !== "application/x-www-form-urlencoded")
            throw new Exception("Bad mimetype: $mimetype");

        return formUrldecode($page);
    }

    public function generateToken($client_id, $scope) {
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $tokenstore = new JsonStore($this->site->tokenFile);
        $tokenstore->value[$token] = array(
            "client_id" => $client_id,
            "scope" => $scope,
            "date_issued" => date("c")
        );
        $tokenstore->flush();
        return $token;
    }

    public function removeToken($token) {
        $tokenstore = new JsonStore($this->site->tokenFile);
        unset($tokenstore->value[$token]);
        $tokenstore->flush();
    }

    public function getBearerToken() {
        if (isset($_SERVER["HTTP_AUTHORIZATION"]))
            if (preg_match(
                "/^Bearer (.+)/",
                $_SERVER["HTTP_AUTHORIZATION"],
                $matches))
                return $matches[1];
        if (isset($_POST["access_token"]))
            return $_POST["access_token"];
        return null;
    }

    public function isAuthorized($token, $scope) {
        if ($token === null)
            return false;
        $tokenstore = new JsonStore($this->site->tokenFile);
        $tokenstore->close();
        return isset($tokenstore->value[$token])
            && $tokenstore->value[$token]["scope"] === $scope;
    }

    public function requireAuthorization($scope, $token = null) {
        if ($token == null)
            $token = $this->getBearerToken();
        if (!$this->isAuthorized($token, $scope)) {
            header("WWW-Authenticate: Bearer realm=\"$scope\"");
            do401();
        }
    }
}
?>
