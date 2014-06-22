<?php
namespace Microformat;
require_once("Mf2/Parser.php");
require_once("lib/jsonstore.php");

function mftype($parsed, $type) {
    return array_filter($parsed["items"], function($elt) use ($type) {
        return in_array($type, $elt["type"]);
    });
}

function scrubstrings($arr) {
    return array_map(function($elt) {
        if (gettype($elt) == "string")
            return htmlspecialchars($elt);
        return $elt;
    }, $arr);
}

function mfprop($mfs, $prop) {
    $props = array();
    if ($prop == "1") {
        if (isset($mfs[0])) return $mfs[0];
        return null;
    }
    foreach ($mfs as $mf) {
        if (isset($mf["properties"][$prop]))
            $thisprops = scrubstrings($mf["properties"][$prop]);
        else if ($prop == "children" && isset($mf[$prop]))
            $thisprops = $mf[$prop];
        else if (($prop == "html") && isset($mf[$prop]))
            $thisprops = array($mf[$prop]);
        else if (($prop == "value") && isset($mf[$prop]))
            $thisprops = scrubstrings(array($mf[$prop]));
        else
            continue;
        $props = array_merge($props, $thisprops);
    }
    return $props;
}

function mfpath($mf, $path) {
    $elts = array_filter(explode("/", $path), function($e){return $e!="";});
    return array_reduce($elts, function($result, $elt) {
        return mfprop($result, $elt);
    }, $mf);
}

abstract class Feed {
    abstract public function getRange($offset, $limit);
}

class LocalFeed extends Feed {
    public function __construct($indexFile) {
        $this->indexStore = new \Jsonstore($indexFile);
    }

    private static function walkDir($path = ".") {
        $paths = array();
        $dir = opendir($path);
        while (($entry = readdir($dir)) !== FALSE) {
            if ($entry[0] == ".")
                continue;
            if (is_dir("$path/$entry"))
                $paths = array_merge($paths, LocalFeed::walkDir($path == "." ? $entry : "$path/$entry"));
            else
                $paths[] = $path == "." ? $entry : "$path/$entry";
        }
        closedir($dir);
        return $paths;
    }

    private static function indexDateCmp($a, $b) {
        return $b["date"] - $a["date"];
    }

    private static function loadIndexEntry($i) {
        $e = new Entry();
        $e->loadFromFile($i["file"]);
        return $e;
    }

    public function reload($regex) {
        $this->indexStore->value = array();
        foreach (array_filter($this->walkDir(), function($e) use($regex) {
            return preg_match($regex, $e); }) as $file) {
            $post = new Entry();
            $post->loadFromFile($file);
            $this->indexStore->value[] = array(
                "file" => $file,
                "url" => $post->url,
                "date" => strtotime($post->published),
            );
        }
        usort($this->indexStore->value, array($this, "indexDateCmp"));
        $this->indexStore->sync();
    }

    public function add($file) {
        $post = new Entry();
        $post->loadFromFile($file);
        $this->indexStore->value[] = array(
            "file" => $file,
            "url" => $post->url,
            "date" => strtotime($post->published),
        );
        usort($this->indexStore->value, array($this, "indexDateCmp"));
        $this->indexStore->sync();
    }

    public function count() {
        return count($this->indexStore->value);
    }

    public function getRange($offset, $limit) {
        return array_map(array($this, "loadIndexEntry"),
            array_slice($this->indexStore->value, $offset, $limit));
    }

    public function getAll() {
        return array_map(array($this, "loadIndexEntry"),
            $this->indexStore->value);
    }

    public function getByUrl($url) {
        $posts = array_filter($this->indexStore->value,
            function($e) use($url) { return $e["url"] == $url; });
        $count = count($posts);
        if ($count != 1)
            throw new \Exception("Found $count entries matching $url");
        return $this->loadIndexEntry(array_shift($posts));
    }
}


class Entry {
    public $file = null;
    public $name = null;
    public $published = null;
    public $contentHtml = null;
    public $contentValue = null;
    public $photo = null;
    public $url = null;
    public $authorName = null;
    public $authorPhoto = null;
    public $authorUrl = null;
    public $syndication = null;
    public $replyTo = array();
    public $likeOf = array();
    public $repostOf = array();
    public $children = array();
    public $h = "entry";
    public $p = array(); // optional properties, eg. p-in-reply-to

    public function __construct($h = "entry", $p = array()) {
        $this->h = $h;
        $this->p = $p;
    }

    public function loadFromHtml($html, $url = null) {
        $mf = \Mf2\parse($html, $url);
        return $this->loadFromMf(mftype($mf, "h-entry"));
    }

    public function loadFromFile($file) {
        $this->file = $file;
        $mf = \Mf2\parse(file_get_contents($file));
        return $this->loadFromMf(mftype($mf, "h-entry"));
    }

    public function save($config) {
        ob_start();
        $title = truncate($this->name, 45) . " - " . $config["siteTitle"];
        require("tpl/header.php");
        echo $this->toHtml();
        require("tpl/footer.php");
        $contents = ob_get_contents();
        ob_end_clean();
        $fh = fopen($this->file, "w");
        fwrite($fh, $contents);
        fclose($fh);
    }

    public function loadFromMf($mf) {
        $this->name = mfpath($mf, "name/1");
        $this->published = mfpath($mf, "published/1");
        $this->contentHtml = mfpath($mf, "content/html/1");
        $this->contentValue = mfpath($mf, "content/value/1");
        $this->photo = mfpath($mf, "photo/1");
        $this->url = mfpath($mf, "url/1");
        $this->authorName = mfpath($mf, "author/name/1");
        $this->authorPhoto = mfpath($mf, "author/photo/1");
        $this->authorUrl = mfpath($mf, "author/url/1");
        $this->syndication = mfpath($mf, "syndication");
        foreach (mfpath($mf, "in-reply-to") as $elt) {
            $cite = new Entry("cite", array("in-reply-to"));
            if (is_array($elt))
                $cite->loadFromMf(array($elt));
            else
                $cite->url = $elt;
            $this->replyTo[] = $cite;
        }
        foreach (mfpath($mf, "like-of") as $elt) {
            $cite = new Entry("cite", array("like-of"));
            if (is_array($elt))
                $cite->loadFromMf(array($elt));
            else
                $cite->url = $elt;
            $this->likeOf[] = $cite;
        }
        foreach (mfpath($mf, "repost-of") as $elt) {
            $cite = new Entry("cite", array("repost-of"));
            if (is_array($elt))
                $cite->loadFromMf(array($elt));
            else
                $cite->url = $elt;
            $this->repostOf[] = $cite;
        }
        foreach (mfpath($mf, "children") as $elt) {
            $cite = new Entry("cite");
            $cite->loadFromMf(array($elt));
            $this->children[] = $cite;
        }
    }

    public function getRootClass() {
        $class = "h-" . $this->h;
        foreach ($this->p as $p)
            $class .= " p-$p";
        return $class;
    }

    public function getContentClass() {
        $class = "e-content";
        if (!$this->isArticle())
            $class .= " p-name";
        return $class;
    }

    public function toHtml() {
        ob_start();
        if ($this->h == "cite") {
            require("tpl/cite.php");
        } else {
            require("tpl/entry.php");
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function toHtmlSummary() {
        ob_start();
        if ($this->h == "cite") {
            require("tpl/cite-summary.php");
        } else {
            require("tpl/entry-summary.php");
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function isReplyTo($url) {
        return in_array($url, array_map(
            function($e) { return $e->url; },
            array_merge($this->replyTo, $this->repostOf, $this->likeOf)));
    }

    public function isArticle() {
        return isset($this->name) && $this->name != $this->contentValue;
    }

    public function isPhoto() {
        return isset($this->photo);
    }

}

?>
