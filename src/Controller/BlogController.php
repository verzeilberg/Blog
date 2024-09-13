<?php

namespace Blog\Controller;

use Event\Entity\Event;
use Blog\Form\CreateBlogForm;
use Blog\Form\CreateEventForm;
use Blog\Form\UpdateBlogForm;
use Blog\Service\blogService;
use Event\Entity\EventCategory;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Blog\Entity\Blog;
use Blog\Form\BlogForm;
use Laminas\Session\Container;
use Symfony\Component\VarDumper\VarDumper;
use Twitter\Service\twitterOathService;
use Twitter\Service\twitterService;
use UploadImages\Exception\ImageException;
use UploadImages\Service\cropImageService;
use UploadImages\Service\imageService;

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
     * BlogController constructor.
     * @param $entityManager
     * @param $viewhelpermanager
     * @param $cropImageService
     * @param $imageService
     * @param $twitterOathService
     * @param blogService $blogService
     * @param $twitterService
     */
    public function __construct(
        $entityManager,
        $viewhelpermanager,
        cropImageService $cropImageService,
        imageService $imageService,
        twitterOathService $twitterOathService,
        blogService $blogService,
        twitterService $twitterService
    ) {
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

        $page = $this->params()->fromQuery('page', 1);
        $query = $this->blogService->getBlogs();

        $searchString = '';
        if ($this->getRequest()->isPost()) {
            $searchString = $this->getRequest()->getPost('search');
            $query = $this->blogService->searchBlogs($searchString);
        }

        $blogs = $this->blogService->getItemsForPagination($query, $page, 10);

        return new ViewModel([
            'blogs' => $blogs,
            'searchString' => $searchString
        ]);
    }

    /**
     *
     * Action to show all deleted (archived) blogs
     */
    public function archiveAction() {
        $this->layout('layout/beheer');
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->blogService->getArchivedEvents();
        $blogs = $this->blogService->getItemsForPagination($query, $page, 10);

        return new ViewModel([
            'blogs' => $blogs
        ]);
    }

    /**
     * @return Response|ViewModel
     * @throws ImageException
     */
    public function addAction(): Response|ViewModel
    {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/beheerAssets/js/editor.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/blogs.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/timeshift/timeshift-1.0.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/timeshift/dateshift-1.0.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/timeshift/timeshift-1.0.css');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/timeshift/dateshift-1.0.css');
        $container = new Container('cropImages');
        $container->getManager()->getStorage()->clear('cropImages');

        // Create the form and inject the EntityManager
        $form = new CreateBlogForm($this->entityManager);
        // Create a new, empty entity and bind it to the form
        $blog = new Blog();
        $form->bind($blog);
        $Image = $this->imageService->createImage();
        $formBlogImage = $this->imageService->createImageForm($Image);

        if ($this->request->isPost()) {

            $form->setData($this->request->getPost());
            $formBlogImage->setData($this->getRequest()->getPost());
            if ($form->isValid() && $formBlogImage->isValid()) {

                //Create image array and set it
                $aImageFile = $this->getRequest()->getFiles('upload-image');
                $aImageFile = $aImageFile['image']['0'];

                //Upload image file's
                if ($aImageFile['error'] === 0) {
                    //Upload image file's
                    $cropImageService = $this->cropImageService;
                    //Upload original file
                    $imageFiles = $this->cropImageService->uploadImage($aImageFile, 'blog', 'original', $Image, 1);
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
                        //Create 800x300 crop
                        $imageFiles = $this->cropImageService->createCropArray('800x300', $folderOriginal, $fileName, 'public/img/userFiles/blog/800x300/', 800, 300, $image, $cropImages);
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
     * @return Response|ViewModel
     * @throws ImageException
     */
    public function editAction(): Response|ViewModel
    {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/uploadImages.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/blogs.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/you-tube.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/beheerAssets/js/editor.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/you-tube.css');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/timeshift/timeshift-1.0.css');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/timeshift/dateshift-1.0.css');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/timeshift/timeshift-1.0.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/timeshift/dateshift-1.0.js');

        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('blogbeheer');
        }
        $blog = $this->blogService->getBlogById($id);
        if (empty($blog)) {
            return $this->redirect()->toRoute('blogbeheer');
        }
        // Create the form and inject the EntityManager
        $form = new UpdateBlogForm($this->entityManager);
        // Create a new, empty entity and bind it to the form
        $form->bind($blog);

        $Image = $this->imageService->createImage();
        $formBlogImage = $this->imageService->createImageForm($Image);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            $formBlogImage->setData($this->getRequest()->getPost());

            if ($form->isValid() && $formBlogImage->isValid()) {

                //Create image array and set it
                $aImageFile = $this->getRequest()->getFiles('upload-image');
                $aImageFile = $aImageFile['image']['0'];

                //Upload image file
                if ($aImageFile['error'] === 0) {

                    //Upload image file's
                    $cropImageService = $this->cropImageService;
                    //Upload original file
                    $imageFiles = $this->cropImageService->uploadImage($aImageFile, 'blog', 'original', $Image, 1);

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
                        //Create 800x300 crop
                        $imageFiles = $this->cropImageService->createCropArray('800x300', $folderOriginal, $fileName, 'public/img/userFiles/blog/800x300/', 800, 300, $image, $cropImages);
                        $image = $imageFiles['image'];
                        $cropImages = $imageFiles['cropImages'];
                        //Create return URL
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
                    $twitterText = $this->twitterService->shortenText($blog->getIntroText(), 160, true);
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
     * @return Response|void
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

    /**
     * Archives a blog based on the provided ID from the route parameters.
     *
     * @return Response A redirection to the blog management page.
     */
    public function archiefAction(): Response
    {
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

    /**
     * Action to un archive a blog.
     *
     * @return Response Redirects to the blog archive route.
     */
    public function unArchiefAction(): Response
    {
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
        return $this->redirect()->toRoute('beheer/blog', ['action' => 'archive']);
    }

    /**
     * Action to set a blog's online/offline status.
     *
     * @return JsonModel Response containing success status, error message, blog ID, and online status.
     */
    public function setOnlineAction(): JsonModel
    {
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
