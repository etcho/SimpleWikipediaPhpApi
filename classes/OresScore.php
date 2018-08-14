<?php

class OresScore {

    private $damaging_prob = 0;
    private $goodfaith_prob = 0;
    private $draftquality = array();
    private $wp10 = array();

    function getDamagingProb() {
        return $this->damaging_prob;
    }

    function getGoodfaithProb() {
        return $this->goodfaith_prob;
    }

    function getWp10() {
        return $this->wp10;
    }

    function setDamagingProb($damaging_prob) {
        $this->damaging_prob = $damaging_prob;
    }

    function setGoodfaithProb($goodfaith_prob) {
        $this->goodfaith_prob = $goodfaith_prob;
    }

    function setWp10($wp10) {
        $this->wp10 = $wp10;
    }

    function getDraftquality() {
        return $this->draftquality;
    }

    function setDraftquality($draftquality) {
        $this->draftquality = $draftquality;
    }

}
