<?php
class Site {
    public function __construct() {
        $config = new JsonStore("config.json", true);
        $config->close();
        if ($config->value === null)
            die("Unable to load config.json");
        foreach ($config->value as $key => $value)
            $this->$key = $value;
    }

    public function Auth() {
        return new Auth($this);
    }

    public function Posse() {
        return new Posse($this);
    }

    public function RemoteFeed() {
        return new Microformat\RemoteFeed($this, $this->feedIndex,
            $this->feedRoot, $this->following);
    }

    public function LocalFeed() {
        return new Microformat\LocalFeed($this, $this->localIndex, $this->pathRegex);
    }

    public function Webmentions() {
        return new JsonStore($this->webmentionFile);
    }

    public function renderHeader($subtitle = null) {
        $pageTitle = $this->title;
        if ($subtitle != null)
            $pageTitle = $subtitle . " - " . $pageTitle;
        require("tpl/header.php");
    }

    public function renderFooter() {
        require("tpl/footer.php");
    }

    public function generateSlug($name, $published) {
        $datepart = date("Y/n/j", strtotime($published));
        if ($name != null) {
            $namepart = strtolower($name);
            $namepart = preg_replace("/[^a-z0-9 ]+/", "", $namepart);
            $namepart = preg_replace("/ +/", "-", $namepart);
            $n = "";
            while (file_exists("$datepart/$namepart$n" . $this->postExtension))
                $n = $n == "" ? 1 : $n + 1;
            return "$datepart/$namepart$n";
        } else {
            $n = 1;
            while (file_exists("$datepart/$n" . $this->postExtension))
                $n++;
            return "$datepart/$n";
        }
    }

    public function save($entry) {
        ob_start();
        $this->renderHeader(truncate($entry->name, 45));
        (new Template($entry))->render("tpl/entry.php");
        $this->renderFooter();
        $contents = ob_get_contents();
        ob_end_clean();
        makeDirs($entry->file);
        $fh = fopen($entry->file, "w");
        fwrite($fh, $contents);
        fclose($fh);
    }
}
?>
