<?php

class Contribution {

    private $sizediff = 0;
    private $article = null;
    private $is_new = false;
    private $is_top = false;
    private $is_minor = false;
    private $timestamp = "";
    private $ores_score = null;

    function getSizediff() {
        return $this->sizediff;
    }

    function getArticle() {
        return $this->article;
    }

    function setSizediff($sizediff) {
        $this->sizediff = $sizediff;
    }

    function setArticle($article) {
        $this->article = $article;
    }

    function getIsNew() {
        return $this->is_new;
    }

    function getIsTop() {
        return $this->is_top;
    }

    function getIsMinor() {
        return $this->is_minor;
    }

    function setIsNew($is_new) {
        $this->is_new = $is_new;
    }

    function setIsTop($is_top) {
        $this->is_top = $is_top;
    }

    function setIsMinor($is_minor) {
        $this->is_minor = $is_minor;
    }

    function getTimestamp() {
        return $this->timestamp;
    }

    function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    function getOresScore() {
        return $this->ores_score;
    }

    function setOresScore($ores_score) {
        $this->ores_score = $ores_score;
    }

}
