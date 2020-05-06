<?php

namespace Blog\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\Authentication\Result;
use Laminas\Uri\Uri;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Form;
use Blog\Entity\Blog;
use YouTube\Entity\YouTube;
use Blog\Form\BlogForm;
use Laminas\Session\Container;
use UploadImages\Entity\Image;
use UploadImages\Entity\ImageType;

/**
 * This controller is responsible for letting the user to log in and log out.
 */
class BlogAjaxController extends AbstractActionController {

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
    private $viewhelpermanager;
    private $cropImageService;
    private $imageService;
    private $youTubeService;
    private $blogService;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $viewhelpermanager, $cropImageService, $imageService, $youTubeService, $blogService) {
        $this->entityManager = $entityManager;
        $this->viewhelpermanager = $viewhelpermanager;
        $this->cropImageService = $cropImageService;
        $this->imageService = $imageService;
        $this->youTubeService = $youTubeService;
        $this->blogService = $blogService;
    }

    public function setOnlineAction() {
        $errorMessage = '';
        $succes = true;
        $blogId = (int) $this->params()->fromPost('blogId', 0);
        $online = (int) $this->params()->fromPost('online', 0);

        if ($blogId === NULL || $online === NULL) {
            $errorMessage = 'Geen blog id of online waarde meegegeven!';
            $succes = false;
        }
        $blog = $this->blogService->getBlogById($blogId);
        if (empty($blog)) {
            $errorMessage = 'Geen blog met id ' . $blogId . ' gevonden!';
            $succes = false;
        } else {
            $this->blogService->setBlogOnlineOffline($blog, $online, $this->currentUser());
            $succes = true;
        }

        return new JsonModel([
            'errorMessage' => $errorMessage,
            'succes' => $succes,
            'blogId' => $blogId,
            'online' => $online
        ]);
    }

    public function addYouTubeVideoAction() {
        $errorMessage = '';
        $succes = true;
        $YouTubeData = [];
        $blogId = $this->params()->fromPost('blogId');

        if ($blogId === NULL) {
            $errorMessage = 'Geen blog id waarde meegegeven!';
            $succes = false;
        } else {
            $blog = $this->blogService->getBlogById($blogId);
            if (!empty($blog)) {
                $youTubeLink = $this->params()->fromPost('youTubeLink');
                if (!empty($youTubeLink)) {
                    $YouTube = $this->youTubeService->getYouTubeDataFromVideo($youTubeLink);
                    if (is_object($YouTube)) {
                        //Add YouTube video to Blog
                        $this->blogService->addYouTubeToBlog($blog, $YouTube, $this->currentUser());

                        //Create data array to send back with json
                        $YouTubeData['youTubeId'] = $YouTube->getId();
                        $YouTubeData['title'] = $YouTube->getTitle();
                        $YouTubeData['imageurl'] = (is_object($YouTube->getYouTubeImageByType('maxres')) ? $YouTube->getYouTubeImageByType('maxres')->getUrl() : '');
                        //Set succes true
                        $succes = true;
                    } else {
                        $errorMessage = 'Geen video gevonden';
                        $succes = false;
                    }
                } else {
                    $errorMessage = 'Geen url gegevens';
                    $succes = false;
                }
            }
        }
        return new JsonModel([
            'errorMessage' => $errorMessage,
            'succes' => $succes,
            'YouTubeData' => $YouTubeData
        ]);
    }

}
