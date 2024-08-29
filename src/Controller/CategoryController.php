<?php

namespace Blog\Controller;

use Blog\Form\CreateBlogCategoryForm;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Authentication\Result;
use Laminas\Uri\Uri;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Form;
use Blog\Entity\Category;
use function sprintf;

/**
 * This controller is responsible for adding, editing and removing categories.
 */
class CategoryController extends AbstractActionController {

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $viewhelpermanager;
    private $categoryService;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $viewhelpermanager, $categoryService) {
        $this->entityManager = $entityManager;
        $this->viewhelpermanager = $viewhelpermanager;
        $this->categoryService = $categoryService;
    }

    /**
     *
     * Action to show all categories
     */
    public function indexAction() {
        $this->layout('layout/beheer');
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->categoryService->getCategories();
        $categories = $this->categoryService->getItemsForPagination($query, $page, 10);
        return new ViewModel([
            'categories' => $categories
        ]);
    }

    /**
     *
     * Action to add a category
     */
    public function addAction() {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/custom/editor.js');
        $category = $this->categoryService->createCategory();
        // Create the form and inject the EntityManager
        $form = new CreateBlogCategoryForm($this->entityManager);
        // Create a new, empty entity and bind it to the form
        $form->bind($category);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $this->categoryService->setNewCategory($category, $this->currentUser());
                $this->flashMessenger()->addSuccessMessage(sprintf('Categorie %s is opgeslagen', $category->getName()));
                $this->redirect()->toRoute('categorybeheer');
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     *
     * Action to edit a category
     */
    public function editAction() {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/custom/editor.js');
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('categorybeheer');
        }
        $category = $this->categoryService->getCategoryById($id);
        if (empty($category)) {
            return $this->redirect()->toRoute('categorybeheer');
        }
        //$form = $this->categoryService->createCategoryForm($category);

        // Create the form and inject the EntityManager
        $form = new CreateBlogCategoryForm($this->entityManager);
        // Create a new, empty entity and bind it to the form
        $form->bind($category);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $this->categoryService->setExistingCategory($category, $this->currentUser());
                $this->flashMessenger()->addSuccessMessage(sprintf('Categorie %s is gewijzigd', $category->getName()));
                $this->redirect()->toRoute('categorybeheer');
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     *
     * Action to delete a category
     */
    public function deleteAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('categorybeheer');
        }
        $category = $this->categoryService->getCategoryById($id);
        if (empty($category)) {
            return $this->redirect()->toRoute('categorybeheer');
        }
        $this->categoryService->deleteCategory($category);
        $this->flashMessenger()->addSuccessMessage(sprintf('Categorie %s is verwijderd', $category->getName()));
        $this->redirect()->toRoute('categorybeheer');
    }

    public function archiefAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('categorybeheer');
        }
        $category = $this->categoryService->getCategoryById($id);
        if (empty($category)) {
            return $this->redirect()->toRoute('categorybeheer');
        }

        //Set changed date
        $this->categoryService->archiveCategory($category, $this->currentUser());
        $this->flashMessenger()->addSuccessMessage('Categorie gearchiveerd');
        return $this->redirect()->toRoute('categorybeheer');
    }

    public function archiveAction()
    {
        $this->layout('layout/beheer');
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->categoryService->getArchivedCategories();

        $searchString = '';
        if ($this->getRequest()->isPost()) {
            $searchString = $this->getRequest()->getPost('search');
            $query = $this->categoryService->searchCategorie($searchString, 1);
        }

        $categories = $this->categoryService->getItemsForPagination($query, $page, 10);

        return new ViewModel([
            'categories' => $categories
        ]);
    }

    /**
     * @return Response
     */
    public function unArchiefAction(): Response
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/categories');
        }
        $eventCategory = $this->categoryService->getCategoryById($id);
        if (empty($eventCategory)) {
            return $this->redirect()->toRoute('beheer/categories');
        }
        //Save Event
        $this->categoryService->unArchiveCategory($eventCategory, $this->currentUser());
        $this->flashMessenger()->addSuccessMessage('Categorie terug gezet');
        return $this->redirect()->toRoute('beheer/categories');
    }

}
