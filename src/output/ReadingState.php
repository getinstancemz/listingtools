<?php

namespace getinstance\listingtools\output;

class ReadingState
{
    private $readingcount=1;
    private $listingargs = [];

    function __construct() {
    }

    function resetListingArgs($args=[]) {
        $this->listingargs = [];
    }

    function addListingArgs($args=[]) {
        $this->listingargs = array_merge($args, $this->listingargs);
    }

    function getListingArgs($args=[]) {
        return $this->listingargs;
    }

    function incCount() {
        $this->readingcount++;
    }
}
