<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class PostCategoryList
{

    private $numListItems = 25;

    private $searchName = '';

    public function getNumListItems() {
        return $this->numListItems;
    }

    public function setNumListItems( $numListItems ) {
        $this->numListItems = $numListItems;
    }

    public function getSearchName() {
        return $this->searchName;
    }

    public function setSearchName( $searchName ) {
        $this->searchName = $searchName;
    }
}
