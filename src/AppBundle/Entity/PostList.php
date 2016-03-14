<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class PostList
{

    private $numListItems = 25;

    private $orderBy = 'p.publishedAt';

    private $orderDir = 'DESC';

    private $status = -99;

    private $searchTitle = '';

    private $user = 0;

    private $category;

    public function getNumListItems() {
        return $this->numListItems;
    }

    public function setNumListItems( $numListItems ) {
        $this->numListItems = $numListItems;
    }

    public function getOrderBy() {
        return $this->orderBy;
    }

    public function setOrderBy( $orderBy ) {
        $this->orderBy = $orderBy;
    }

    public function getOrderDir() {
        return $this->orderDir;
    }

    public function setOrderDir( $orderDir ) {
        $this->orderDir = $orderDir;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus( $status ) {
        $this->status = $status;
    }

    public function getSearchTitle() {
        return $this->searchTitle;
    }

    public function setSearchTitle( $searchTitle ) {
        $this->searchTitle = $searchTitle;
    }

    public function getCategory() {
        return $this->category;
    }

    public function setCategory( $category ) {
        $this->category = $category;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser( $user ) {
        $this->user = $user;
    }
}
