<?php

namespace Blog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Club
 *
 * @ORM\Entity
 * @ORM\Table(name="categories")
 */
class Category {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=11, name="id");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * * @Annotation\Options({
     * "label": "Titel",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "placeholder":"titel"})
     */
    protected $name;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     * * @Annotation\Options({
     * "label": "Omschrijving",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "placeholder":"Omschrijving", "id":"editor"})
     */
    protected $description;

    /**
     * Many Categories have Many Blogs.
     * @ORM\ManyToMany(targetEntity="Blog", mappedBy="categories")
     */
    private $blogs;

    public function __construct() {
        $this->blogs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function addBlogs(Collection $blogs) {
        foreach ($blogs as $blog) {
            $this->blogs->add($blog);
        }
    }

    public function removeBlogs(Collection $blogs) {
        foreach ($blogs as $blog) {
            $this->blogs->removeElement($blog);
        }
    }

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setDescription($description) {
        $this->description = $description;
    }
    
    function getBlogs() {
        return $this->blogs;
    }

    function setBlogs($blogs) {
        $this->blogs = $blogs;
        return $this;
    }





}
