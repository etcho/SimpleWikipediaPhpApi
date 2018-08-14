<?php

class Article {

    private $users = array();
    private $bots = array();
    private $categories = array();
    private $title = "";
    private $ns = 0;
    private $size = 0;

    public function getUsers() {
        return $this->users;
    }

    public function setUsers($users) {
        $this->users = $users;
    }

    public function getBots() {
        return $this->bots;
    }

    public function setBots($bots) {
        $this->bots = $bots;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getTitleNormalized() {
        return str_replace(" ", "_", $this->title);
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    function getCategories() {
        return $this->categories;
    }

    function setCategories($categories) {
        $this->categories = $categories;
    }

    function getNs() {
        return $this->ns;
    }

    function setNs($ns) {
        $this->ns = $ns;
    }

    function getSize() {
        return $this->size;
    }

    function setSize($size) {
        $this->size = $size;
    }

    public function __construct($title, $params = array()) {
        array_key_exists("search_api", $params) ? null : $params["search_api"] = false;
        array_key_exists("search_users", $params) ? null : $params["search_users"] = false;
        array_key_exists("user_limit", $params) ? null : $params["user_limit"] = null;
        array_key_exists("only_logged_users", $params) ? null : $params["only_logged_users"] = false;
        array_key_exists("search_categories", $params) ? null : $params["search_categories"] = false;
        array_key_exists("category_limit", $params) ? null : $params["category_limit"] = null;

        $this->setTitle($title);
        if ($params["search_api"] || $params["search_users"]) {
            $this->searchUsers($params["user_limit"], $params["only_logged_users"]);
        }
        if ($params["search_api"] || $params["search_categories"]) {
            $this->searchCategories($params["category_limit"]);
        }
    }

    public function searchCategories($category_limit = null) {
        $json = @file_get_contents('https://en.wikipedia.org/w/api.php?action=query&titles=' . $this->getTitleNormalized() . '&prop=categories&format=json&cllimit=500&clshow=!hidden');
        $data = json_decode($json, true);
        $categories = array();
        foreach ($data["query"]["pages"] as $page_id => $page_data) {
            if (array_key_exists("categories", $page_data)) {
                foreach ($page_data["categories"] as $category) {
                    $categories[] = new Category($category["title"]);
                }
            }
        }
        $this->setCategories($categories);
    }

    public function searchUsers($user_limit = null, $only_logged_users = false) {
        $json = @file_get_contents('https://en.wikipedia.org/w/api.php?action=query&prop=revisions&titles=' . $this->getTitleNormalized() . '&rvprop=user&rvlimit=500&format=json');
        $data = json_decode($json, true);
        $users = array();
        $bots = array();
        if ($data !== null) {
            foreach ($data["query"]["pages"] as $page_id => $page_data) {
                foreach ($page_data["revisions"] as $rev) {
                    if (array_key_exists("user", $rev)) {
                        $user = $rev["user"];
                        if (!$only_logged_users || !filter_var($user, FILTER_VALIDATE_IP)) {
                            $this->addEditor($user);
                        }
                    }
                }
            }
            $this->sortEditors();
            if ($user_limit !== null) {
                $users = $this->getUsers();
                if (count($users) > $user_limit) {
                    $users = array_slice($users, 0, $user_limit);
                    $this->setUsers($users);
                }
            }
            $this->setTitle($page_data["title"]);
        }
    }

    private function addEditor($name) {
        $classname = "User";
        if (strtolower(substr($name, count($name) - 4, 3)) === "bot" ||
                strtolower(substr($name, count($name) - 5, 4)) === "bot2" ||
                strpos($name, "Bot") !== false) {
            $classname = "Bot";
        }
        $editors = $classname == "User" ? $this->getUsers() : $this->getBots();
        $exists = false;
        foreach ($editors as $editor) {
            if ($editor->getName() == $name) {
                $editor->increaseNumberOfEdits();
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $editors[] = new $classname($name);
            if ($classname == "User") {
                $this->setUsers($editors);
            } else {
                $this->setBots($editors);
            }
        }
    }

    private function sortEditors() {
        $users = $this->getUsers();
        usort($users, array($this, "_sortEditors"));
        $users = array_reverse($users);
        $this->setUsers($users);
        $bots = $this->getBots();
        usort($bots, array($this, "_sortEditors"));
        $bots = array_reverse($bots);
        $this->setBots($bots);
    }

    private function _sortEditors($a, $b) {
        return strcmp($a->getNumberOfEdits(), $b->getNumberOfEdits());
    }

}
