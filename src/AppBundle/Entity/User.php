<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="symfony_demo_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",nullable=true)
     * @Assert\NotBlank()
     */
    private $first_name;

    /**
     * @ORM\Column(type="string",nullable=true)
     * @Assert\NotBlank()
     */
    private $last_name;

    public function __toString() {
        return $this->getUsernameCanonical();
    }

    public function getFirstName() {
        return $this->first_name;
    }

    public function setFirstName( $first_name ) {
        $this->first_name = $first_name;
    }

    public function getLastName() {
        return $this->last_name;
    }

    public function setLastName( $last_name ) {
        $this->last_name = $last_name;
    }
}
