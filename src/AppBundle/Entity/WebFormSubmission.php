<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="symfony_demo_form_submission")
 */
class WebFormSubmission {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="WebForm", inversedBy="submissions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $form;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $ip_address;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $user_agent;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $form_data;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     * @Assert\DateTime()
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="smallint",options={"default":"1"})
     * @Assert\NotBlank()
     */
    private $status = 1;


    public function __construct() {
        $this->publishedAt = new \DateTime();
        $this->updatedAt   = new \DateTime();
    }

    public function getId() {
        return $this->id;
    }

    public function getPublishedAt() {
        return $this->publishedAt;
    }

    public function setPublishedAt( \DateTime $publishedAt ) {
        $this->publishedAt = $publishedAt;
    }

    public function setUser( \AppBundle\Entity\User $user = null ) {
        $this->user = $user;

        return $this;
    }

    public function getUser() {
        return $this->user;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getStatusText() {
        switch ( $this->status ) {
            case - 1:
                $status = 'Trashed';
                break;
            case  0:
                $status = 'Read';
                break;
            case  1:
                $status = 'New';
                break;
            case  2:
                $status = 'Archived';
                break;
        }

        return $status;
    }

    public function getStatusIcon() {
        switch ( $this->status ) {
            case - 1:
                $status = '<i class="fa fa-trash"></i>';
                break;
            case  0:
                $status = '<i class="fa fa-close"></i>';
                break;
            case  1:
                $status = '<i class="fa fa-check"></i>';
                break;
            case  2:
                $status = '<i class="fa fa-archive"></i>';
                break;
        }

        return $status;
    }

    public function setStatus( $status ) {
        $this->status = $status;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function setUpdatedAt( $updatedAt ) {
        $this->updatedAt = $updatedAt;
    }

    public function getForm() {
        return $this->form;
    }

    public function setForm( $form ) {
        $this->form = $form;
    }

    public function getFormData() {
        return $this->form_data;
    }

    public function getFormDataArray() {
        return json_decode($this->form_data,true);
    }

    public function setFormData( $form_data ) {
        $this->form_data = $form_data;
    }

    public function getIpAddress() {
        return $this->ip_address;
    }

    public function setIpAddress( $ip_address ) {
        $this->ip_address = $ip_address;
    }

    /**
     * @return mixed
     */
    public function getUserAgent() {
        return $this->user_agent;
    }

    /**
     * @param mixed $user_agent
     */
    public function setUserAgent( $user_agent ) {
        $this->user_agent = $user_agent;
    }
}

