<?php
class Template {
    public function __construct($e, $context = array()) {
        $this->e = $e;
        $this->context = $context;
    }

    public function render($tpl) {
        $e = $this->e;
        foreach ($this->context as $key => $val) {
            $$key = $val;
        }
        require($tpl);
    }
}

?>
