<?php

class Helper {

    static function loadClasses() {
        $classes = array("Editor", "User", "Bot", "Article", "Category", "Contribution", "OresScore");
        foreach ($classes as $class) {
            require(__DIR__ . "/" . $class . ".php");
        }
    }

    static function convertNs($ns) {
        $list = array(
            -2 => "Media",
            -1 => "Special",
            0 => "Main",
            2 => "User",
            4 => "Wikipedia",
            6 => "File",
            8 => "MediaWiki",
            10 => "Template",
            12 => "Help",
            14 => "Category",
            100 => "Portal",
            108 => "Book",
            118 => "Draft",
            446 => "Education Program",
            710 => "TimedText",
            828 => "Module",
            2300 => "Gadget",
            2302 => "Gadget Definition"
        );
        if (array_key_exists($ns, $list)) {
            return $list[$ns];
        }
        if ($ns > 0 && array_key_exists($ns + 1, $list)) {
            return $list[$ns + 1] . " Talk";
        }
        return $ns;
    }

}
