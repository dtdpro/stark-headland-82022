<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostRepository")
 * @ORM\Table(name="symfony_demo_post")
 * @Vich\Uploadable
 *
 * Defines the properties of the Post entity to represent the blog posts.
 * See http://symfony.com/doc/current/book/doctrine.html#creating-an-entity-class
 *
 * Tip: if you have an existing database, you can generate these entity class automatically.
 * See http://symfony.com/doc/current/cookbook/doctrine/reverse_engineering.html
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class Post
{
    /**
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See http://symfony.com/doc/current/best_practices/configuration.html#constants-vs-configuration-options
     */
    const NUM_ITEMS = 10;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string",options={"default":"blog"})
     * @Assert\NotBlank()
     */
    private $type='blog';

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
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="post.blank_content")
     * @Assert\Length(min = "10", minMessage = "post.too_short_content")
     */
    private $content;

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
    private $status=1;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Comment",
     *      mappedBy="post",
     *      orphanRemoval=true
     * )
     * @ORM\OrderBy({"publishedAt" = "DESC"})
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="PostCategory", inversedBy="posts")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $post_image;

    /**
     * @Assert\File(
     *     maxSize="25M",
     *     mimeTypes={"image/png", "image/jpeg", "image/pjpeg"}
     * )
     * @Vich\UploadableField(mapping="post_image", fileNameProperty="post_image")
     *
     * @var File $logo_virtual
     *
     * This is the virtual field that will populate logo with the resulting file.
     */
    protected $post_image_file;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $params;



    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->comments = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Is the given User the author of this Post?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user)
    {
        return $user == $this->getUser();
    }

    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addComment(Comment $comment)
    {
        $this->comments->add($comment);
        $comment->setPost($this);
    }

    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getStatusText() {
        switch ($this->status) {
            case -1: $status = 'Trashed'; break;
            case  0: $status = 'Unpublished'; break;
            case  1: $status = 'Published'; break;
            case  2: $status = 'Archived'; break;
            case  3: $status = 'Review'; break;
        }
        return $status;
    }

    public function getStatusIcon() {
        switch ($this->status) {
            case -1: $status = '<i class="fa fa-trash"></i>'; break;
            case  0: $status = '<i class="fa fa-close"></i>'; break;
            case  1: $status = '<i class="fa fa-check"></i>'; break;
            case  2: $status = '<i class="fa fa-archive"></i>'; break;
            case  3: $status = '<i class="fa fa-commenting"></i>'; break;
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

    public function getPostImage() {
        return $this->post_image;
    }

    public function setPostImage( $post_image ) {
        $this->post_image = $post_image;
    }

    public function getPostImageFile() {
        return $this->post_image_file;
    }

    public function setPostImageFile( File $post_image_file = null ) {
        $this->post_image_file = $post_image_file;
        if ($post_image_file) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    public function getParams() {
        return $this->params;
    }

    public function setParams( $params ) {
        $this->params = $params;
    }

    public function getParamsArray() {
        return json_decode($this->params,true);
    }
}
