<?php

class Helper {

    static function loadClasses() {
        $classes = array("Editor", "User", "Bot", "Article", "Category");
        foreach ($classes as $class) {
            require(__DIR__ . "/" . $class . ".php");
        }
    }

}
