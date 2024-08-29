<?php

namespace Blog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\UnityOfWork;

/**
 * Club
 *
 * @ORM\Entity
 * @ORM\Table(name="comments")
 */
class Comment extends UnityOfWork {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=11, name="id");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * * @Annotation\Options({
     * "label": "Bericht",
     * "label_attributes": {"class": "col-sm-12 col-md-12 col-lg-12 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "id":"editor"})
     */
    protected $comment;

    /**
     * Many comments have One Blog.
     * @ORM\ManyToOne(targetEntity="Blog", inversedBy="comments")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id")
     */
    private $blog;

    /**
     * One Comment has Many comments.
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parent")
     */
    private $children;

    /**
     * Many Comments have One Comment.
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     *
     */
    private $parent;

    public function __construct() {
        $this->children = new ArrayCollection();
    }

    function getId() {
        return $this->id;
    }

    function getComment() {
        return $this->comment;
    }

    function getBlog() {
        return $this->blog;
    }

    function setId($id) {
        $this->id = $id;
    }


    function setComment($comment) {
        $this->comment = $comment;
    }

    function setBlog($blog) {
        $this->blog = $blog;
    }

    function getChildren() {
        return $this->children;
    }

    function getParent() {
        return $this->parent;
    }

    function setChildren($children) {
        $this->children = $children;
    }

    function setParent($parent) {
        $this->parent = $parent;
    }

}
