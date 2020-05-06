<?php

namespace Blog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;

/**
 * Club
 *
 * @ORM\Entity
 * @ORM\Table(name="blogimagetypes")
 */
class BlogImageType {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=11, name="id");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $fileName;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $folder;
    
        /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $imageTypeName;

    function getId() {
        return $this->id;
    }

    function getFileName() {
        return $this->fileName;
    }

    function getFolder() {
        return $this->folder;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setFileName($fileName) {
        $this->fileName = $fileName;
    }

    function setFolder($folder) {
        $this->folder = $folder;
    }
    
    function getImageTypeName() {
        return $this->imageTypeName;
    }

    function setImageTypeName($imageTypeName) {
        $this->imageTypeName = $imageTypeName;
    }



}
