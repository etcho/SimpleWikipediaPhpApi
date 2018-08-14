<?php

abstract class Editor {

    private $name = "";
    private $number_of_edits = 0;
    private $articles = array();

    public function getName($replace_spaces = false) {
        if ($replace_spaces) {
            return str_replace(" ", "_", $this->name);
        }
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getNumberOfEdits() {
        return $this->number_of_edits;
    }

    public function setNumberOfEdits($number_of_edits) {
        $this->number_of_edits = $number_of_edits;
    }

    public function getArticles() {
        return $this->articles;
    }

    public function setArticles($articles) {
        $this->articles = $articles;
    }

    public function __construct($name) {
        $this->setName($name);
        $this->increaseNumberOfEdits();
    }

    public function increaseNumberOfEdits() {
        $this->setNumberOfEdits($this->getNumberOfEdits() + 1);
    }

    public function searchArticles($article_limit = null) {
        $json = file_get_contents('https://en.wikipedia.org/w/api.php?action=query&format=json&list=usercontribs&uclimit=500&ucuser=' . $this->getName(true));
        $data = json_decode($json, true);
        foreach ($data["query"]["usercontribs"] as $contrib) {
            $articles = $this->getArticles();
            $title = $contrib["title"];
            $exists = false;
            foreach ($articles as $i => &$a) {
                if ($a["article"]->getTitle() == $title) {
                    $a["occurrences"] ++;
                    $this->setArticles($articles);
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $article = new Article($title, array("search_api" => false));
                $articles[] = array("article" => $article, "occurrences" => 1);
                $this->setArticles($articles);
            }
        }
        $this->sortArticles();
        $articles = $this->getArticles();
        if (count($articles) > $article_limit) {
            $articles = array_slice($articles, 0, $article_limit);
            $this->setArticles($articles);
        }
    }

    private function sortArticles() {
        $articles = $this->getArticles();
        array_multisort(array_column($articles, "occurrences"), SORT_DESC, $articles);
        $this->setArticles($articles);
    }

    private function _sortArticles($a, $b) {
        return strcmp($a->getNumberOfEdits(), $b->getNumberOfEdits());
    }

}
