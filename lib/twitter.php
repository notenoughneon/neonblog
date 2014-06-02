<?php
namespace TwitterElide;

const URL_LEN = 22;
const MAX_LEN = 140;

abstract class Token {
    public $next = null;

    public function __construct($value) {
        $this->value = $value;
    }

    public function totalLength() {
        $length = $this->length();
        if ($this->next != null)
            $length += $this->next->totalLength();
        return $length;
    }

    protected function length() {
        return strlen($this->value);
    }

    protected function shorten($delta) {
        return $delta;
    }

    public function elideTo($len) {
        if ($len > 0 && $len <= $this->length())
            $len -= 4;
        $delta = $len - $this->length();
        if ($this->next != null)
            $delta = $this->next->elideTo($delta);
        return $this->shorten($delta);
    }

    public function append($token) {
        if ($this->next === null)
            $this->next = $token;
        else $this->next->append($token);
    }

    public function toString() {
        if ($this->next == null)
            return $this->value;
        return $this->value . $this->next->toString();
    }
}

class TextToken extends Token {
    protected function shorten($delta) {
        $len = $this->length();
        if ($delta < 0) {
            if ($len + $delta < 1) {
                $this->value = " ";
                return $delta + $len - 1;
            } else {
                $this->value = substr($this->value, 0, $len + $delta) . "... ";
                return 0;
            }
        }
        return $delta;
    }

    public function append($token) {
        if ($this->next === null) {
            if ($token instanceof TextToken)
                $this->value .= $token->value;
            else
                $this->next = $token;
        }
        else $this->next->append($token);
    }
}

class UrlToken extends Token {
    protected function length() { return URL_LEN; }
}

class NameToken extends Token {}

class HashToken extends Token {}

function tokenize($str) {
    $punc = "[(),\.:!?]*";
    $tokens = null;
    while (strlen($str) > 0) {
        if (preg_match("/^[\s]+/", $str, $matches)) {
        } else {
            assert(preg_match("/^[\S]+/", $str, $matches));
            assert(preg_match("/^($punc)(\S+?)($punc)$/", $matches[0], $matches));
            array_shift($matches);
        }
        foreach ($matches as $lex) {
            if (strlen($lex) > 0) {
                if (preg_match("~^(https?://)?[\w-]*[a-z][\w-]*(\.[\w-]+)+(/[\w\./%+?=&#\~-]+)?$~i", $lex))
                    $tok = new UrlToken($lex);
                else if (preg_match("~^#\w*[a-z]\w*$~i", $lex))
                    $tok = new HashToken($lex);
                else if (preg_match("~^@\w+$~", $lex))
                    $tok = new NameToken($lex);
                else
                    $tok = new TextToken($lex);
                if ($tokens == null)
                    $tokens = $tok;
                else
                    $tokens->append($tok);
                $str = substr($str, strlen($lex));
            }
        }
    }
    return $tokens;
}

function elideTo($str, $len) {
    if (strlen($str) > $len)
        return substr($str, 0, $len - 3) . "...";
    return $str;
}

function format($title, $content, $url) {
    if (isset($title) && $title !== "") {
        //article
        $title = elideTo($title, MAX_LEN - 1 - URL_LEN);
        return "$title $url";
    } else {
        //note
        $tokens = tokenize($content);
        if ($tokens->totalLength() <= MAX_LEN)
            return $content;
        $status = $tokens->elideTo(MAX_LEN - 1 - URL_LEN);
        return $tokens->toString() . " $url";
    }
}

?>
