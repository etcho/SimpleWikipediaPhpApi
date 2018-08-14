<?php

class Category {

    private $title = "";

    function getTitle() {
        return $this->title;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    public function __construct($title) {
        if (substr($title, 0, 9) == "Category:") {
            $title = substr($title, 9, strlen($title));
        }
        $this->setTitle($title);
    }

}
