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

    public function LocalFeed() {
        return new Microformat\LocalFeed($this->localIndex);
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

    public function save($entry) {
        ob_start();
        $this->renderHeader(truncate($entry->name, 45));
        (new Template($entry))->render("tpl/entry.php");
        $this->renderFooter();
        $contents = ob_get_contents();
        ob_end_clean();
        $fh = fopen($entry->file, "w");
        fwrite($fh, $contents);
        fclose($fh);
    }
}
?>
