<?php

abstract class Editor {

    private $name = "";
    private $number_of_edits = 0;
    private $articles = array();
    private $contributions = array();

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

    function getContributions() {
        return $this->contributions;
    }

    function setContributions($contributions) {
        $this->contributions = $contributions;
    }

    public function __construct($name, $params = array()) {
        array_key_exists("increase_number_of_edits", $params) ? null : $params["increase_number_of_edits"] = false;

        $this->setName($name);
        if ($params["increase_number_of_edits"]) {
            $this->increaseNumberOfEdits();
        }
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

    public function searchContributions() {
        $json = file_get_contents('https://en.wikipedia.org/w/api.php?action=query&list=usercontribs&uclimit=500&format=json&ucprop=oresscores|title|sizediff|timestamp|size|flags&ucuser=' . $this->getName(true));
        $data = json_decode($json, true);
        $contributions = array();
        if (array_key_exists("query", $data)) {
            foreach ($data["query"]["usercontribs"] as $contrib) {
                $article = new Article($contrib["title"]);
                $article->setNs($contrib["ns"]);
                $article->setSize($contrib["size"]);

                $ores = new OresScore();
                if (array_key_exists("damaging", $contrib["oresscores"])) {
                    $ores->setDamagingProb($contrib["oresscores"]["damaging"]["true"]);
                    $ores->setGoodfaithProb($contrib["oresscores"]["goodfaith"]["true"]);
                    if (array_key_exists("draftquality", $contrib["oresscores"])) {
                        $ores->setDraftquality($contrib["oresscores"]["draftquality"]);
                    }
                    if (array_key_exists("wp10", $contrib["oresscores"])) {
                        $ores->setWp10($contrib["oresscores"]["wp10"]);
                    }
                }

                $contribution = new Contribution();
                $contribution->setSizediff($contrib["sizediff"]);
                $contribution->setTimestamp($contrib["timestamp"]);
                $contribution->setIsMinor(array_key_exists("minor", $contrib));
                $contribution->setIsTop(array_key_exists("top", $contrib));
                $contribution->setIsNew(array_key_exists("new", $contrib));
                $contribution->setArticle($article);
                $contribution->setOresScore($ores);

                $contributions[] = $contribution;
            }
        }
        $this->setContributions($contributions);
    }

}
