<?php


namespace AppBundle\Entity;


use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="symfony_demo_form_question_option")
 */
class WebFormQuestionOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="WebFormQuestion", inversedBy="options")
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

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
     * @Gedmo\Sortable(groups={"question"})
     * @ORM\Column(type="integer")
     */
    private $position;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->status=1;
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

    public function getQuestion() {
        return $this->question;
    }

    public function setQuestion( $question ) {
        $this->question = $question;
    }
}

