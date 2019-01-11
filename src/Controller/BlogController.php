<?php

namespace Blog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Authentication\Result;
use Zend\Uri\Uri;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Zend\Form\Form;
use Blog\Entity\Blog;
use Blog\Form\BlogForm;
use Zend\Session\Container;
use UploadImages\Entity\Image;
use UploadImages\Entity\ImageType;

/**
 * This controller is responsible for letting the user to log in and log out.
 */
class BlogController extends AbstractActionController {

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
    private $viewhelpermanager;
    private $cropImageService;
    private $imageService;
    private $twitterOathService;
    private $blogService;
    private $twitterService;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $viewhelpermanager, $cropImageService, $imageService, $twitterOathService, $blogService, $twitterService) {
        $this->entityManager = $entityManager;
        $this->viewhelpermanager = $viewhelpermanager;
        $this->cropImageService = $cropImageService;
        $this->imageService = $imageService;
        $this->twitterOathService = $twitterOathService;
        $this->blogService = $blogService;
        $this->twitterService = $twitterService;
    }

    /**
     * 
     * Action to show all blogs
     */
    public function indexAction() {
        $this->layout('layout/beheer');
        $container = new Container('cropImages');
        $container->getManager()->getStorage()->clear('cropImages');

        $blogs = $this->blogService->getBlogs();

        return new ViewModel([
            'blogs' => $blogs
        ]);
    }

    /**
     * 
     * Action to show all deleted (archived) blogs
     */
    public function archiveAction() {
        $this->layout('layout/beheer');
        $blogs = $this->blogService->getArchivedBlogs();

        return new ViewModel([
            'blogs' => $blogs
        ]);
    }

    /**
     * 
     * Action to add a blog
     */
    public function addAction() {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/custom/editor.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/blogs.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/dateTimePicker/bootstrap-datetimepicker.min.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/dateTimePicker/bootstrap-datetimepicker.css');
        $container = new Container('cropImages');
        $container->getManager()->getStorage()->clear('cropImages');

        $blog = $this->blogService->createBlog();
        $form = $this->blogService->createBlogForm($blog);

        $Image = $this->imageService->createImage();
        $formBlogImage = $this->imageService->createImageForm($Image);


        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            $formBlogImage->setData($this->getRequest()->getPost());
            if ($form->isValid() && $formBlogImage->isValid()) {

                $aImageFile = '';
                $aImageFile = $this->getRequest()->getFiles('image');

                //Upload image file's
                if ($aImageFile['error'] === 0) {
                    //Upload image file's
                    $cropImageService = $this->cropImageService;
                    //Upload original file
                    $imageFiles = $this->cropImageService->uploadImage($aImageFile, NULL, 'original', $Image, 1);
                    if (is_array($imageFiles)) {

                        $folderOriginal = $imageFiles['imageType']->getFolder();
                        $fileName = $imageFiles['imageType']->getFileName();
                        $image = $imageFiles['image'];
                        //Upload thumb 150x150
                        $imageFiles = $cropImageService->resizeAndCropImage('public/' . $folderOriginal . $fileName, 'public/img/userFiles/blog/thumb/', 150, 150, '150x150', $image);
                        //Create 400x200 crop
                        $imageFiles = $this->cropImageService->createCropArray('400x200', $folderOriginal, $fileName, 'public/img/userFiles/blog/400x200/', 400, 200, $image);
                        $image = $imageFiles['image'];
                        $cropImages = $imageFiles['cropImages'];
                        //Create 800x600 crop
                        $imageFiles = $this->cropImageService->createCropArray('800x600', $folderOriginal, $fileName, 'public/img/userFiles/blog/800x600/', 800, 600, $image, $cropImages);
                        $image = $imageFiles['image'];
                        $cropImages = $imageFiles['cropImages'];
                        //Create return URL
                        $returnURL = $this->cropImageService->createReturnURL('beheer/blog', 'index');

                        //Create session container for crop
                        $this->cropImageService->createContainerImages($cropImages, $returnURL);

                        //Save blog image
                        $this->entityManager->persist($image);
                        $this->entityManager->flush();
                        $blog->addBlogImage($image);
                    }
                } else {
                    $this->flashMessenger()->addSuccessMessage($imageFiles);
                }

                //End upload image

                //Save Blog
                $this->blogService->setNewBlog($blog, $this->currentUser());
                $this->flashMessenger()->addSuccessMessage('Blog opgeslagen');
                
                //Check if blog must be placed on Twitter
                if ((int) $this->getRequest()->getPost('twittered') == 1 && (int) $blog->getOnline() == 1) {
                    $blogUrlForTwitter = $this->blogService->createBlogUrl($blog);
                    $twitterText = $this->twitterService->shortenText($blog->getIntroText(), 150, true);
                    $twitterResponse = $this->twitterOathService->postTweetOnTwitter($twitterText . ' ' . $blogUrlForTwitter);
                    if (empty($twitterResponse->errors)) {
                        $tweetid = $twitterResponse->id;
                        $blog->setTweetId($tweetid);
                        $this->blogService->setExistingBlog($blog, $this->currentUser());
                    }
                }
                

                if ($aImageFile['error'] === 0 && is_array($imageFiles)) {
                    return $this->redirect()->toRoute('beheer/images', array('action' => 'crop'));
                } else {
                    return $this->redirect()->toRoute('blogbeheer');
                }
            }
        }

        return new ViewModel([
            'form' => $form,
            'formBlogImage' => $formBlogImage
        ]);
    }

    /**
     * Function to edit a blog
     * 
     * @return view
     * 
     */
    public function editAction() {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/custom/beheerBlog.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/uploadImages.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/blogs.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/you-tube.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/custom/editor.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/dateTimePicker/bootstrap-datetimepicker.min.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/dateTimePicker/bootstrap-datetimepicker.css');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/you-tube.css');

        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('blogbeheer');
        }
        $blog = $this->blogService->getBlogById($id);
        if (empty($blog)) {
            return $this->redirect()->toRoute('blogbeheer');
        }
        $form = $this->blogService->createBlogForm($blog);
        $Image = $this->imageService->createImage();
        $formBlogImage = $this->imageService->createImageForm($Image);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            $formBlogImage->setData($this->getRequest()->getPost());
            if ($form->isValid() && $formBlogImage->isValid()) {

                $aImageFile = '';
                $aImageFile = $this->getRequest()->getFiles('image');

                //Upload image file
                if ($aImageFile['error'] === 0) {
                    //Upload image file's
                    $cropImageService = $this->cropImageService;
                    //Upload original file
                    $imageFiles = $this->cropImageService->uploadImage($aImageFile, NULL, 'original', $Image, 1);
                    if (is_array($imageFiles)) {

                        $folderOriginal = $imageFiles['imageType']->getFolder();
                        $fileName = $imageFiles['imageType']->getFileName();
                        $image = $imageFiles['image'];
                        //Upload thumb 150x150
                        $imageFiles = $cropImageService->resizeAndCropImage('public/' . $folderOriginal . $fileName, 'public/img/userFiles/blog/thumb/', 150, 150, '150x150', $image);
                        //Create 25x25 crop
                        $imageFiles = $this->cropImageService->createCropArray('400x200', $folderOriginal, $fileName, 'public/img/userFiles/blog/400x200/', 400, 200, $image);
                        $image = $imageFiles['image'];
                        $cropImages = $imageFiles['cropImages'];
                        //Create 75x75 crop
                        $imageFiles = $this->cropImageService->createCropArray('800x600', $folderOriginal, $fileName, 'public/img/userFiles/blog/800x600/', 800, 600, $image, $cropImages);
                        $image = $imageFiles['image'];
                        $cropImages = $imageFiles['cropImages'];

                        $returnURL = $this->cropImageService->createReturnURL('beheer/blog', 'edit', $blog->getId());

                        //Create session container for crop
                        $this->cropImageService->createContainerImages($cropImages, $returnURL);

                        //Save blog image
                        $this->entityManager->persist($image);
                        $this->entityManager->flush();
                        $blog->addBlogImage($image);
                    }
                } else {
                    $this->flashMessenger()->addSuccessMessage($imageFiles);
                }
                //End upload image
                
                //Twitter check
                if ((int) $this->getRequest()->getPost('twittered') == 1 && (int) $blog->getOnline() == 1) {
                    $blogUrlForTwitter = $this->blogService->createBlogUrl($blog);
                    $twitterText = $this->twitterService->shortenText($blog->getIntroText(), 150, true);
                    $twitterResponse = $this->twitterOathService->postTweetOnTwitter($twitterText . ' ' . $blogUrlForTwitter);
                    if (empty($twitterResponse->errors)) {
                        $tweetid = $twitterResponse->id;
                        $blog->setTweetId($tweetid);
                    }
                }
                
                //Save Blog
                $this->blogService->setExistingBlog($blog, $this->currentUser());
                $this->flashMessenger()->addSuccessMessage('Blog opgeslagen');

                if ($aImageFile['error'] === 0 && is_array($imageFiles)) {
                    return $this->redirect()->toRoute('beheer/images', array('action' => 'crop'));
                } else {
                    return $this->redirect()->toRoute('blogbeheer');
                }
            }
        }

        $tweet = '';
        if (!empty($blog->getTweetId())) {
            $tweet = $this->twitterOathService->getTweetById($blog->getTweetId());
        }
        
        $returnURL = [];
        $returnURL['id'] = $id;
        $returnURL['route'] = 'beheer/blog';
        $returnURL['action'] = 'edit';
        
        return new ViewModel([
            'blogId' => $id,
            'form' => $form,
            'formBlogImage' => $formBlogImage,
            'images' => $blog->getBlogImages(),
            'youTubeVideos' => $blog->getBlogYouTubes(),
            'tweet' => $tweet,
            'returnURL' => $returnURL
        ]);
    }

    /**
     * 
     * Action to set delete a blog
     */
    public function deleteAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/blog');
        }
        $blog = $this->blogService->getBlogById($id);
        if (empty($blog)) {
            return $this->redirect()->toRoute('beheer/blog');
        }
        //Delete linked images
        $images = $blog->getBlogImages();
        if (count($images) > 0) {
            $this->imageService->deleteImages($images);
        }
        // Remove blog
        $this->blogService->deleteBlog($blog);
        $this->flashMessenger()->addSuccessMessage('Blog verwijderd');
        $this->redirect()->toRoute('beheer/blog', array('action' => 'archive'));
    }

    public function archiefAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/blog');
        }
        $blog = $this->blogService->getBlogById($id);
        if (empty($blog)) {
            return $this->redirect()->toRoute('beheer/blog');
        }
        //Archive blog
        $this->blogService->archiveBlog($blog, $this->currentUser());
        $this->flashMessenger()->addSuccessMessage('Blog gearchiveerd');
        return $this->redirect()->toRoute('beheer/blog');
    }

    public function unArchiefAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/blog');
        }
        $blog = $this->blogService->getBlogById($id);
        if (empty($blog)) {
            return $this->redirect()->toRoute('beheer/blog');
        }
        //Unarchive blog
        $this->blogService->unArchiveBlog($blog, $this->currentUser());
        $this->flashMessenger()->addSuccessMessage('Blog terug gezet');
        return $this->redirect()->toRoute('beheer/blog');
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

}
