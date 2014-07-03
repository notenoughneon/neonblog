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
    public function __construct($indexFile) {
        $this->index = new \Jsonstore($indexFile);
    }

    public static function indexDateCmp($a, $b) {
        return $b["date"] - $a["date"];
    }

    public function loadIndexEntry($i) {
        $e = new Entry();
        $e->loadFromFile($i["file"], $i["url"]);
        return $e;
    }

    public function count() {
        return count($this->index->value);
    }

    public function getRange($offset, $limit) {
        return array_map(array($this,"loadIndexEntry"),
            array_slice($this->index->value, $offset, $limit));
    }

    public function getAll() {
        return array_map(array($this,"loadIndexEntry"),
            $this->index->value);
    }

    public function search($query) {
        return array_filter($this->getAll(),
            function($e) use($query) {
                return stripos($e->name, $query) !== false
                    || stripos($e->contentValue, $query) !== false;
            }
        );
    }

    public function getByUrl($url) {
        $posts = array_filter($this->index->value,
            function($e) use($url) { return $e["url"] == $url; });
        $count = count($posts);
        if ($count != 1)
            throw new \Exception("Found $count entries matching $url");
        return $this->loadIndexEntry(array_shift($posts));
    }
}

class RemoteFeed extends Feed {
    public function reload($cacheRoot, $url) {
        $html = fetchPage($url);
        $mf = \Mf2\parse($html, $url);
        $feed = mftype($mf, "h-feed");
        if (count($feed) > 0)
            $elts = mfpath($feed, "children");
        else
            $elts = mftype($mf, "h-entry");
        $this->index->value = array();
        foreach ($elts as $elt) {
            $postUrl = mfpath(array($elt), "url/1");
            $postPublished = mfpath(array($elt), "published/1");
            $postHtml = fetchPage($postUrl);
            $file = $cacheRoot . "/" . md5($postUrl);
            file_put_contents($file, $postHtml);
            $this->index->value[] = array(
                "file" => $file,
                "url" => $postUrl,
                "date" => strtotime($postPublished),
            );
        }
        usort($this->index->value, "parent::indexDateCmp");
        $this->index->sync();
    }
}

class LocalFeed extends Feed {
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

    public function reload($regex) {
        $this->index->value = array();
        foreach (array_filter($this->walkDir(), function($e) use($regex) {
            return preg_match($regex, $e); }) as $file) {
            $post = new Entry();
            $post->loadFromFile($file);
            $this->index->value[] = array(
                "file" => $file,
                "url" => $post->url,
                "date" => strtotime($post->published),
            );
        }
        usort($this->index->value, "parent::indexDateCmp");
        $this->index->sync();
    }

    public function add($post) {
        $this->index->value[] = array(
            "file" => $post->file,
            "url" => $post->url,
            "date" => strtotime($post->published),
        );
        usort($this->index->value, "parent::indexDateCmp");
        $this->index->sync();
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

    public function loadFromFile($file, $url = null) {
        $this->file = $file;
        $mf = \Mf2\parse(file_get_contents($file), $url);
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
        if ($this->isNote())
            $class .= " p-name note-content";
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

    public function highlight($query) {
        $content = $this->contentValue;
        $len = 128;
        $i = stripos($content, $query);
        if ($i === false)
            $i = 0;
        $start = max($i - $len + strlen($query)/2, 0);
        $end = $start + 2*$len;
        $elided = substr($content, 0, $i)
            . "<mark>"
            . substr($content, $i, strlen($query))
            . "</mark>"
            . substr($content, $i + strlen($query));
        $elided = substr($elided, $start, 2*$len);
        if ($start > 0)
            $elided = "..." . $elided;
        if ($end < strlen($content))
            $elided = $elided . "...";
        return $elided;
    }

    public function toSearchHit($query) {
        ob_start();
        require("tpl/entry-searchhit.php");
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

    public function references() {
        return array_map(function($e) {return $e->url;},
            array_merge($this->replyTo, $this->repostOf, $this->likeOf));
    }

    public function isReplyTo($url) {
        return in_array($url, $this->references());
    }

    public function isArticle() {
        return isset($this->name) && $this->name != $this->contentValue
            && count($this->references()) == 0;
    }

    public function isNote() {
        return !$this->isArticle() && !$this->isPhoto();
    }

    public function getLinks() {
        $links = $this->references();
        $doc = new \DOMDocument();
        if (!@$doc->loadHTML($this->contentHtml))
            return $links;
        foreach ($doc->getElementsByTagName("a") as $a)
            $links[] = $a->getAttribute("href");
        return $links;
    }

    public function isPhoto() {
        return isset($this->photo);
    }

}

?>
