<?php


namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="symfony_demo_form_question")
 */
class WebFormQuestion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string",options={"default":"radio"})
     * @Assert\NotBlank()
     */
    private $type = 'radio';

    /**
     * @ORM\ManyToOne(targetEntity="WebForm", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $form;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="smallint",nullable=true)
     * @Assert\NotBlank()
     */
    private $status=1;

    /**
     * @Gedmo\Sortable(groups={"form"})
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="boolean",options={"default":false})
     */
    private $is_required=false;

    /**
     * @ORM\OneToMany(
     *      targetEntity="WebFormQuestionOption",
     *      mappedBy="question",
     *      orphanRemoval=true
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $options;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->options = new ArrayCollection();
        $this->status = 1;
    }

    /**
     * @Assert\IsTrue(message = "comment.is_spam")
     */
    public function isLegitComment()
    {
        $containsInvalidCharacters = false !== strpos($this->content, '@');

        return !$containsInvalidCharacters;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getContent()
    {
        return $this->content;
    }
    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getStatusText() {
        switch ($this->status) {
            case -1: $status = 'Trashed'; break;
            case  0: $status = 'Unpublished'; break;
            case  1: $status = 'Published'; break;
        }
        return $status;
    }

    public function getStatusIcon() {
        switch ($this->status) {
            case -1: $status = '<i class="fa fa-trash"></i>'; break;
            case  0: $status = '<i class="fa fa-close"></i>'; break;
            case  1: $status = '<i class="fa fa-check"></i>'; break;
        }
        return $status;
    }

    public function setStatus( $status ) {
        $this->status = $status;
    }

    public function getForm() {
        return $this->form;
    }

    public function setForm( $form ) {
        $this->form = $form;
    }

    public function getPosition() {
        return $this->position;
    }

    public function setPosition( $position ) {
        $this->position = $position;
    }

    public function getType() {
        return $this->type;
    }

    public function getTypeText() {
        switch ( $this->type ) {
            case "radio":
                $type = 'Single, Multiple Chioce';
                break;
            case "multicbox":
                $type = 'Multiple, Multiple Chioce';
                break;
            case "dropdown":
                $type = 'Drop Down';
                break;
            case "cbox":
                $type = 'Checkbox';
                break;
            case "textfield":
                $type = 'Text Field';
                break;
            case "textbox":
                $type = 'Text/Comment Box';
                break;
            case "email":
                $type = 'Email Address';
                break;
        }

        return $type;
    }

    public function setType( $type ) {
        $this->type = $type;
    }

    public function getOptions() {
        return $this->options;
    }

    public function setOptions( $options ) {
        $this->options = $options;
    }

    public function hasOptions() {
        $hasopt = false;
        switch ( $this->type ) {
            case "radio":
            case "multicbox":
            case "dropdown":
                $hasopt = true;
                break;
            case "cbox":
            case "textfield":
            case "textbox":
            case "email":
            default:
                $hasopt = false;
                break;
        }

        return $hasopt;
    }

    public function getIsRequired() {
        return $this->is_required;
    }

    public function setIsRequired( $is_required ) {
        $this->is_required = $is_required;
    }
}
