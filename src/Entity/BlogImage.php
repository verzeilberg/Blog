<?php

namespace Blog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Club
 *
 * @ORM\Entity
 * @ORM\Table(name="blogimages")
 */
class BlogImage {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=11, name="id");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $blogImageId;

    /**
     * @ORM\Column(name="name_image", type="string", length=255, nullable=true)
     * @Annotation\Required(false)
     * @Annotation\Options({
     * "label": "Afbeelding naam",
     * "label_attributes": {"class": "col-sm-4 col-md-4 col-lg-4 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "placeholder":"Afbeelding naam"})
     */
    protected $nameImage;

    /**
     * @ORM\Column(name="alt", type="string", length=255, nullable=true)
     * @Annotation\Required(false)
     * @Annotation\Options({
     * "label": "Alt tekst",
     * "label_attributes": {"class": "col-sm-4 col-md-4 col-lg-4 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "placeholder":"Alt tekst"})
     */
    protected $alt;

    /**
     * @ORM\Column(name="description_image",type="string", length=255, nullable=true)
     * @Annotation\Required(false)
     * @Annotation\Options({
     * "label": "Omschrijving",
     * "label_attributes": {"class": "col-sm-4 col-md-4 col-lg-4 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "placeholder":"Omschrijving"})
     */
    protected $descriptionImage;

    /**
     * @ORM\Column(name="sort_order", type="integer", length=11, nullable=true);
     * @Annotation\Required(false)
     */
    protected $sortOrder = 0;

    /**
     * Many blogImages have Many blogImageTypes.
     * @ORM\ManyToMany(targetEntity="BlogImageType")
     * @ORM\JoinTable(name="blogimage_blogimagetypes",
     *      joinColumns={@ORM\JoinColumn(name="blogId", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="imageTypeId", referencedColumnName="id", unique=true)}
     *      )
     */
    private $blogImageTypes;

    public function __construct() {
        $this->blogImageTypes = new ArrayCollection();
    }

    function getBlogImageTypes($imageType = NULL) {
        if ($imageType === NULL) {
            return $this->blogImageTypes;
        } else {
            $blogImageTypes = array();
            foreach ($this->blogImageTypes AS $blogImageType) {
                if ($blogImageType->getImageTypeName() == $imageType) {
                    $blogImageTypes[] = $blogImageType;
                }
            }
            return $blogImageTypes;
        }
    }

    function setBlogImageTypes($blogImages) {
        $this->blogImageTypes = $blogImages;
    }

    public function addBlogImageType(BlogImageType $blogImageType) {
        if (!$this->blogImageTypes->contains($blogImageType)) {
            $this->blogImageTypes->add($blogImageType);
        }
        return $this;
    }

    public function removeBlogImageType(BlogImageType $blogImageType) {
        if ($this->blogImageTypes->contains($blogImageType)) {
            $this->blogImageTypes->removeElement($blogImageType);
        }
        return $this;
    }

    function getAlt() {
        return $this->alt;
    }

    function setAlt($alt) {
        $this->alt = $alt;
    }

    function getBlogImageId() {
        return $this->blogImageId;
    }

    function setBlogImageId($blogImageId) {
        $this->blogImageId = $blogImageId;
    }
    function getSortOrder() {
        return $this->sortOrder;
    }

    function setSortOrder($sortOrder) {
        $this->sortOrder = $sortOrder;
    }

    function getNameImage() {
        return $this->nameImage;
    }

    function getDescriptionImage() {
        return $this->descriptionImage;
    }

    function setNameImage($nameImage) {
        $this->nameImage = $nameImage;
    }

    function setDescriptionImage($descriptionImage) {
        $this->descriptionImage = $descriptionImage;
    }


}
