<?php

class Pager
{

    private $itemsPerPage;
    private $currentPage;
    private $totalNumberOfItems;

    function __construct($itemsPerPage,$totalNumberOfItems) {
        $this->itemsPerPage = $itemsPerPage;
        $this->totalNumberOfItems = $totalNumberOfItems;
        $this->currentPage = 1;
    }

    public function getCurrentPage() {
        return $this->currentPage;
    }

    public function setCurrentPage($currentPage) {
        if($currentPage > $this->getNumberOfPages() ) {
            throw new Exception("Page is passed the number of pages expected");
        }
        $this->currentPage = $currentPage;
    }

    public function getStartIndex() {
        return ($this->currentPage-1) * $this->itemsPerPage;
    }

    public function getEndIndex() {
        return $this->getStartIndex() + $this->itemsPerPage;
    }

    public function getNumberOfPages() {
        return ceil($this->totalNumberOfItems/$this->itemsPerPage) ;
    }

    public function hasPrevious() {
        return $this->currentPage > 1 && $this->totalNumberOfItems > 0;
    }

    public function hasNext() {
        return $this->currentPage < $this->getNumberOfPages();
    }
}
