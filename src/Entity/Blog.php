<?php

namespace Blog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\UnityOfWork;

/**
 * This class represents a blog item.
 * @ORM\Entity()
 * @ORM\Table(name="blog")
 */
class Blog extends UnityOfWork {

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="online", type="integer", length=1, nullable=false, options={"default"=0})
     * @Annotation\Options({
     * "label": "Online",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":""})
     */
    protected $online = 0;
    
    

    /**
     * @ORM\Column(name="twittered", type="integer", length=11, nullable=true, options={"default"=0})
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({
     * "label": "Tweet",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":""})
     */
    protected $twittered = 0;
    
   /**
     * @ORM\Column(name="tweet_id", type="string", length=255, nullable=true)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({
     * "label": "Tweet",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":""})
     */
    protected $tweetId = 0;

    /**
     * @ORM\Column(name="date_online", type="datetime", nullable=true)
     * @Annotation\Options({
     * "label": "Blog online",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "readonly":"readonly"})
     */
    protected $dateOnline;

    /**
     * @ORM\Column(name="date_offline", type="datetime", nullable=true)
     * @Annotation\Options({
     * "label": "Blog offline",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "readonly":"readonly"})
     */
    protected $dateOffline;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Annotation\Options({
     * "label": "Titel",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "placeholder":"Titel"})
     */
    protected $title;

    /**
     * @ORM\Column(name="introtext", type="string", length=2500, nullable=false)
     * @Annotation\Options({
     * "label": "Intro",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control"})
     */
    protected $introText;

    /**
     * @ORM\Column(name="text", type="text", nullable=false)
     * @Annotation\Options({
     * "label": "Text",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "id":"editor"})
     */
    protected $text;

    /**
     * Many Blogs have Many Categories.
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="blogs")
     * @ORM\JoinTable(name="blog_category")
     * @Annotation\Type("DoctrineModule\Form\Element\ObjectMultiCheckbox")
     * @Annotation\Options({
     * "target_class":"Blog\Entity\Category",
     * "property": "name",
     * "label": "Categorien",
     * "label_attributes": {"class": "col-sm-12 col-md-12 col-lg-12 control-label"}
     * })
     * @Annotation\Attributes({"class":""})
     */
    private $categories;

    /**
     * Many blogs have Many Images.
     * @ORM\ManyToMany(targetEntity="UploadImages\Entity\Image")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     * @ORM\JoinTable(name="blog_images",
     *      joinColumns={@ORM\JoinColumn(name="blogId", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="imageId", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     *      )
     */
    private $blogImages;

    /**
     * Many blogs have Many Youtube video's.
     * @ORM\ManyToMany(targetEntity="YouTube\Entity\YouTube")
     * @ORM\JoinTable(name="blog_youtubes",
     *      joinColumns={@ORM\JoinColumn(name="blogId", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="youTubeId", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     *      )
     */
    private $blogYouTubes;

    /**
     * One Blog has Many Comments.
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="blog")
     */
    private $comments;

    public function __construct() {
        $this->categories = new ArrayCollection();
        $this->blogImages = new ArrayCollection();
        $this->blogYouTubes = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function addCategories($categories) {
        foreach ($categories as $category) {
            $this->categories->add($category);
        }
    }

    public function removeCategories($categories) {
        foreach ($categories as $category) {
            $this->categories->removeElement($category);
        }
    }

    public function addComments($comments) {
        foreach ($comments as $comment) {
            $this->comments->add($comment);
        }
    }

    public function removeComments($comments) {
        foreach ($comments as $comment) {
            $this->comments->removeElement($comment);
        }
    }

    function getId() {
        return $this->id;
    }

    function setId($id) {
        $this->id = $id;
    }

    function getTitle() {
        return $this->title;
    }

    function getText() {
        return $this->text;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function setText($text) {
        $this->text = $text;
    }

    function getCategories() {
        return $this->categories;
    }

    function setCategories($categories) {
        $this->categories = $categories;
    }

    function getBlogImage() {
        return $this->productImage;
    }

    function setBlogImage($blogImages) {
        $this->productImage = $blogImages;
    }

    function getBlogImages($imageType = NULL) {
        return $this->blogImages;
    }

    function setBlogImages($blogImages) {
        $this->blogImages = $blogImages;
    }

    public function addBlogImage($blogImages) {
        if (!$this->blogImages->contains($blogImages)) {
            $this->blogImages->add($blogImages);
        }
        return $this;
    }

    public function removeBlogImage($blogImages) {
        if ($this->blogImages->contains($blogImages)) {
            $this->blogImages->removeElement($blogImages);
        }
        return $this;
    }

    public function addBlogYouTubes($blogYouTubes) {
        if (!$this->blogYouTubes->contains($blogYouTubes)) {
            $this->blogYouTubes->add($blogYouTubes);
        }
        return $this;
    }

    public function removeBlogYouTubes($blogYouTubes) {
        if ($this->blogYouTubes->contains($blogYouTubes)) {
            $this->blogYouTubes->removeElement($blogYouTubes);
        }
        return $this;
    }

    function getOnline() {
        return $this->online;
    }

    function setOnline($online) {
        $this->online = $online;
    }

    function getIntroText() {
        return $this->introText;
    }

    function setIntroText($introText) {
        $this->introText = $introText;
    }

    function getBlogYouTubes() {
        return $this->blogYouTubes;
    }

    function setBlogYouTubes($blogYouTubes) {
        $this->blogYouTubes = $blogYouTubes;
    }

    function getDateOnline() {
        if (!empty($this->dateOnline)) {
            return $this->dateOnline->format('Y-m-d H:i');
        }
    }

    function getDateOffline() {
        if (!empty($this->dateOffline)) {
            return $this->dateOffline->format('Y-m-d H:i');
        }
    }

    function setDateOnline($dateOnline) {
        $this->dateOnline = $dateOnline;
    }

    function setDateOffline($dateOffline) {
        $this->dateOffline = $dateOffline;
    }

    function getTwittered() {
        return $this->twittered;
    }

    function setTwittered($twittered) {
        $this->twittered = $twittered;
    }
    
    function getComments() {
        return $this->comments;
    }

    function setComments($comments) {
        $this->comments = $comments;
    }

    function getTweetId() {
        return $this->tweetId;
    }

    function setTweetId($tweetId) {
        $this->tweetId = $tweetId;
    }



}
