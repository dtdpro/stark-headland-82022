<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="symfony_demo_form")
 */
class WebForm {

     /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string",options={"default":"form"})
     * @Assert\NotBlank()
     */
    private $type = 'form';

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     */
    private $slug;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="post.blank_summary")
     */
    private $summary;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $content_before_top;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $content_before_bottom;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $content_results_top;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $content_results_bottom;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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

    /**
     * @ORM\ManyToOne(targetEntity="PostCategory")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ORM\Column(type="boolean",options={"default":true} )
     */
    private $allow_unreg=true;

    /**
     * @ORM\Column(type="boolean",options={"default":true} )
     */
    private $allow_multiple=true;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     * @Assert\DateTime()
     */
    private $form_open;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     * @Assert\DateTime()
     */
    private $form_close;

    /**
     * @ORM\OneToMany(
     *      targetEntity="WebFormQuestion",
     *      mappedBy="form",
     *      orphanRemoval=true
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $questions;


    public function __construct() {
        $this->publishedAt = new \DateTime();
        $this->updatedAt   = new \DateTime();
        $this->questions = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle( $title ) {
        $this->title = $title;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function setSlug( $slug ) {
        $this->slug = $slug;
    }

    /**
     * Is the given User the author of this Post?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor( User $user ) {
        return $user == $this->getUser();
    }

    public function getPublishedAt() {
        return $this->publishedAt;
    }

    public function setPublishedAt( \DateTime $publishedAt ) {
        $this->publishedAt = $publishedAt;
    }

    public function getSummary() {
        return $this->summary;
    }

    public function setSummary( $summary ) {
        $this->summary = $summary;
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
                $status = 'Unpublished';
                break;
            case  1:
                $status = 'Published';
                break;
            case  2:
                $status = 'Archived';
                break;
            case  3:
                $status = 'Review';
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
            case  3:
                $status = '<i class="fa fa-commenting"></i>';
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

    public function getCategory() {
        return $this->category;
    }

    public function setCategory( $category ) {
        $this->category = $category;
    }

    public function getType() {
        return $this->type;
    }

    public function setType( $type ) {
        $this->type = $type;
    }

    public function getParamsArray() {
        return json_decode( $this->params, true );
    }

    public function getContentBeforeBottom() {
        return $this->content_before_bottom;
    }

    public function setContentBeforeBottom( $content_before_bottom ) {
        $this->content_before_bottom = $content_before_bottom;
    }

    public function getContentBeforeTop() {
        return $this->content_before_top;
    }

    public function setContentBeforeTop( $content_before_top ) {
        $this->content_before_top = $content_before_top;
    }

    public function getContentResultsBottom() {
        return $this->content_results_bottom;
    }

    public function setContentResultsBottom( $content_results_bottom ) {
        $this->content_results_bottom = $content_results_bottom;
    }

    public function getContentResultsTop() {
        return $this->content_results_top;
    }

    public function setContentResultsTop( $content_results_top ) {
        $this->content_results_top = $content_results_top;
    }

    public function getFormClose() {
        return $this->form_close;
    }

    public function getFormOpen() {
        return $this->form_open;
    }

    public function setFormClose( $form_close ) {
        $this->form_close = $form_close;
    }

    public function setFormOpen( $form_open ) {
        $this->form_open = $form_open;
    }

    public function getAllowUnreg() {
        return $this->allow_unreg;
    }

    public function setAllowUnreg( $allow_unreg ) {
        $this->allow_unreg = $allow_unreg;
    }

    public function getAllowMultiple() {
        return $this->allow_multiple;
    }

    public function setAllowMultiple( $allow_multiple ) {
        $this->allow_multiple = $allow_multiple;
    }

    public function getQuestions()
    {
        return $this->questions;
    }

    public function addQuestions(WebFormQuestion $question)
    {
        $this->questions->add($question);
        $question->setForm($this);
    }

    public function removeQuestions(WebFormQuestion $question)
    {
        $this->questions->removeElement($question);
    }
}

