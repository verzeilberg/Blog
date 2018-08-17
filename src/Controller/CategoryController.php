<?php

namespace Blog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\Result;
use Zend\Uri\Uri;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Zend\Form\Form;
use Blog\Entity\Category;

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
        $categories = $this->categoryService->getCategories();
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
        $form = $this->categoryService->createCategoryForm($category);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $this->categoryService->storeCategory($category);
                $this->flashMessenger()->addSuccessMessage('Category opgeslagen');
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
        $form = $this->categoryService->createCategoryForm($category);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $this->categoryService->storeCategory($category);
                $this->flashMessenger()->addSuccessMessage('Category opgeslagen');
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
        $this->flashMessenger()->addSuccessMessage('Category verwijderd');
        $this->redirect()->toRoute('categorybeheer');
    }

}
