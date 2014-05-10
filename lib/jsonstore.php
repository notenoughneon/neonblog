<?php
class JsonStore
{
    public $value;
    private $fh;
    private $filename;

    public function __construct($filename, $readonly = false, $default = array()) {
        $this->filename = $filename;
        $this->fh = fopen($this->filename, $readonly ? "r" : "a+");
        if ($this->fh === false)
            throw new Exception("Unable to open $this->filename");
        if (!flock($this->fh, $readonly ? LOCK_SH : LOCK_EX))
            throw new Exception("Unable to lock $this->filename");
        $size = filesize($this->filename);
        if ($size > 0) {
            $contents = fread($this->fh, filesize($this->filename));
            if ($contents === false)
                throw new Exception("Unable to read $this->filename");
            $this->value = json_decode($contents, true);
            if ($this->value === null)
                throw new Exception("JSON error: " . json_last_error());
        } else {
            $this->value = $default;
        }
    }

    public function close() {
        if (!flock($this->fh, LOCK_UN))
            throw new Exception("Unable to unlock $this->filename");
        if (!fclose($this->fh))
            throw new Exception("Unable to close $this->filename");
    }

    public function sync() {
        if (!ftruncate($this->fh, 0))
            throw new Exception("Unable to truncate $this->filename");
        if (!fwrite($this->fh, json_encode($this->value)))
            throw new Exception("Unable to write to $this->filename");
    }

    public function flush() {
        $this->sync();
        $this->close();
    }
}
?>
