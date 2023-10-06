<?php

namespace Blog\Controller;

use Blog\Form\CreateBlogCategoryForm;
use Blog\Form\CreateCommentForm;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Authentication\Result;
use Laminas\Uri\Uri;
use Symfony\Component\VarDumper\VarDumper;

/**
 * This controller is responsible for showing blogs
 */
class IndexController extends AbstractActionController {

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
    private $searchService;
    private $rbacManager;
    private $viewhelpermanager;
    private $blogService;
    private $categoryService;
    private $commentService;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $searchService, $rbacManager, $viewhelpermanager, $blogService, $categoryService, $commentService) {
        $this->entityManager = $entityManager;
        $this->searchService = $searchService;
        $this->rbacManager = $rbacManager;
        $this->viewhelpermanager = $viewhelpermanager;
        $this->blogService = $blogService;
        $this->categoryService = $categoryService;
        $this->commentService = $commentService;
    }

    /**
     * Index page overview of blogs    
     */
    public function indexAction() {
        $page = $this->params()->fromQuery('page', 1);
        $blogs = $this->blogService->getOnlineBlogsForPaginator($page);
        $categories = $this->categoryService->getAllCategories();

        return new ViewModel([
            'blogs' => $blogs,
            'categories' => $categories
        ]);
    }

    public function detailAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('blog');
        }
        $blog = $this->blogService->getBlogById($id);
        if (empty($blog)) {
            return $this->redirect()->toRoute('blog');
        }
        if ($blog->getOnline() == 0) {
            return $this->getResponse()->setStatusCode(404);
        }

        $categories = $this->categoryService->getCategories();

        // Create the form and inject the EntityManager
        $commentForm = new CreateCommentForm($this->entityManager);
        // Create a new, empty entity and bind it to the form
        $comment = $this->commentService->createComment();
        $commentForm->bind($comment);

        if ($this->getRequest()->isPost()) {
            $commentForm->setData($this->getRequest()->getPost());
            if ($commentForm->isValid()) {
                if(!empty($this->getRequest()->getPost('parentID'))) {
                    $commentID = $this->getRequest()->getPost('parentID');
                    $parentComment = $this->commentService->getCommentById($commentID);
                    $this->commentService->setNewComment($comment, $this->currentUser(), null, $parentComment);
                } else {
                    $this->commentService->setNewComment($comment, $this->currentUser(), $blog);
                }
            } 
        }

        return new ViewModel([
            'blog' => $blog,
            'categories' => $categories,
            'commentForm' => $commentForm,
            'comments' => $blog->getComments(),
            'rbacManager' => $this->rbacManager
        ]);
    }

    public function searchAction() {
        if ($this->getRequest()->isPost()) {
            $searchTerm = $this->params()->fromPost('searchString');
            $this->searchService->findSearchPhrase($searchTerm);
            $page = $this->params()->fromQuery('page', 1);
            $blogs = $this->blogService->getBlogsBySearchPhrase($searchTerm, $page);
        }

        $categories = $this->categoryService->getCategories();

        return new ViewModel(
                array(
            'blogs' => $blogs,
            'categories' => $categories,
            'searchTerm' => $searchTerm
                )
        );
    }

    public function categoryAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('blog');
        }
        $page               = $this->params()->fromQuery('page', 1);
        $blogs              = $this->blogService->getBlogsByCategoryIdForPaginator($id, $page);
        $categories         = $this->categoryService->getAllCategories();
        $currentCategory    = $this->categoryService->getCategoryById($id);

        return new ViewModel(
                array(
            'blogs' => $blogs,
            'categories' => $categories,
            'currentCategory' => $currentCategory
                )
        );
    }

}
