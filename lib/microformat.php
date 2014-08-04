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
    public function __construct($site, $indexFile) {
        $this->site = $site;
        $this->index = new \Jsonstore($indexFile);
    }

    protected static function indexDateCmp($a, $b) {
        return $b["date"] - $a["date"];
    }

    protected static function loadIndexEntry($i) {
        $e = new Entry();
        $e->loadFromFile($i["file"], $i["url"]);
        return $e;
    }

    public static function filterByType($types) {
        return function($i) use($types) {
            return in_array($i["type"], $types);
        };
    }

    protected function addIndexEntry($post) {
        $this->index->value[] = array(
            "file" => $post->file,
            "url" => $post->url,
            "date" => strtotime($post->published),
            "type" => $post->getPostType(),
        );
    }

    public function count($filter = null) {
        $elts = $this->index->value;
        if ($filter != null)
            $elts = array_values(array_filter($elts, $filter));
        return count($elts);
    }

    public abstract function poll();

    public function getRange($offset, $limit, $filter = null) {
        $elts = $this->index->value;
        if ($filter != null)
            $elts = array_values(array_filter($elts, $filter));
        return array_map(array($this,"loadIndexEntry"),
            array_slice($elts, $offset, $limit));
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

    public function hasUrl($url) {
        return array_any($this->index->value,
        function($e) use($url) { return $e["url"] == $url; });
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
    public function __construct($site, $indexFile, $cacheRoot, $feedUrls) {
        parent::__construct($site, $indexFile);
        $this->cacheRoot = $cacheRoot;
        $this->feedUrls = $feedUrls;
    }

    private function getNewPosts() {
        $posts = array();
        foreach ($this->feedUrls as $feedUrl) {
            $html = fetchPage($feedUrl);
            $mf = \Mf2\parse($html, $feedUrl);
            $feed = mftype($mf, "h-feed");
            if (count($feed) > 0)
                $entries = mfpath($feed, "children");
            else
                $entries = mftype($mf, "h-entry");
            foreach ($entries as $entry) {
                $postUrl = mfpath(array($entry), "url/1");
                if (!$this->hasUrl($postUrl)) {
                    $html = fetchPage($postUrl);
                    $post = new Entry();
                    $post->loadFromHtml($html, $feedUrl);
                    $posts[] = $post;
                }
            }
        }
        return $posts;
    }

    public function poll() {
        foreach ($this->getNewPosts() as $post) {
            $post->file = $this->cacheRoot . "/" . md5($post->url);
            $this->site->save($post);
            $this->addIndexEntry($post);
        }
        usort($this->index->value, "parent::indexDateCmp");
        $this->index->sync();
    }
}

class LocalFeed extends Feed {
    public function __construct($site, $indexFile, $pathRegex) {
        parent::__construct($site, $indexFile);
        $this->pathRegex = $pathRegex;
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

    protected static function loadIndexEntry($i) {
        $e = new Entry();
        $e->loadFromFile($i["file"]);
        return $e;
    }

    public function poll() {
        $this->index->value = array();
        $pathRegex = $this->pathRegex;
        foreach (array_filter($this->walkDir(), function($e) use($pathRegex) {
            return preg_match($pathRegex, $e); }) as $file) {
            $post = new Entry();
            $post->loadFromFile($file);
            if (!$this->hasUrl($post->url)) {
                $this->addIndexEntry($post);
            }
        }
        usort($this->index->value, "parent::indexDateCmp");
        $this->index->sync();
    }

    public function add($post) {
        $this->addIndexEntry($post);
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
    public $syndication = array();
    public $replyTo = array();
    public $likeOf = array();
    public $repostOf = array();
    public $children = array();
    public $p = array(); // optional properties, eg. p-in-reply-to

    public function __construct($p = array()) {
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
            $cite = new Cite(array("in-reply-to"));
            if (is_array($elt))
                $cite->loadFromMf(array($elt));
            else
                $cite->url = $elt;
            $this->replyTo[] = $cite;
        }
        foreach (mfpath($mf, "like-of") as $elt) {
            $cite = new Cite(array("like-of"));
            if (is_array($elt))
                $cite->loadFromMf(array($elt));
            else
                $cite->url = $elt;
            $this->likeOf[] = $cite;
        }
        foreach (mfpath($mf, "repost-of") as $elt) {
            $cite = new Cite(array("repost-of"));
            if (is_array($elt))
                $cite->loadFromMf(array($elt));
            else
                $cite->url = $elt;
            $this->repostOf[] = $cite;
        }
        foreach (mfpath($mf, "children") as $elt) {
            $cite = new Cite();
            $cite->loadFromMf(array($elt));
            $this->children[] = $cite;
        }
    }

    public function getRootClass() {
        $class = "h-entry";
        foreach ($this->p as $p)
            $class .= " p-$p";
        return $class;
    }

    public function getContentClass() {
        $class = "e-content";
        if (!$this->isArticle())
            $class .= " p-name note-content";
        return $class;
    }

    public function getPostType() {
        if ($this->isReply())
            return "reply";
        if ($this->isRepost())
            return "repost";
        if ($this->isLike())
            return "like";
        if ($this->isArticle())
            return "article";
        if ($this->isPhoto())
            return "photo";
        return "note";
    }

    public function references() {
        return array_map(function($e) {return $e->url;},
            array_merge($this->replyTo, $this->repostOf, $this->likeOf));
    }

    public function isReply() {
        return count($this->replyTo) > 0;
    }

    public function isRepost() {
        return count($this->repostOf) > 0;
    }

    public function isLike() {
        return count($this->likeOf) > 0;
    }

    public function isPhoto() {
        return isset($this->photo);
    }

    public function isReplyTo($url) {
        return in_array($url, $this->references());
    }

    public function isArticle() {
        return isset($this->name) && $this->name != $this->contentValue
            && count($this->references()) == 0;
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

}

class Cite extends Entry {
    public function getRootClass() {
        $class = "h-cite";
        foreach ($this->p as $p)
            $class .= " p-$p";
        return $class;
    }
}

?>
