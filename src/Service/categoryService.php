<?php

namespace Blog\Service;

use Laminas\ServiceManager\ServiceLocatorInterface;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Form;

/*
 * Entities
 */
use Blog\Entity\Category;

class categoryService implements categoryServiceInterface {

    /**
     * Constructor.
     */
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     *
     * Create new Category object
     *
     * @return      object
     *
     */
    public function createCategory(){
        return new Category();
    }

    /**
     *
     * Get array of categories
     *
     * @return      array
     *
     */
    public function getCategories() {
        $categories = $this->entityManager->getRepository(Category::class)
                ->findAll();

        return $categories;
    }

    /**
     *
     * Get category object based on id
     *
     * @param       id  $id The id to fetch the category from the database
     * @return      object
     *
     */
    public function getCategoryById($id) {
        $category = $this->entityManager->getRepository(Category::class)
                ->findOneBy(['id' => $id], []);

        return $category;
    }
    
        /**
     *
     * Create form of an object
     *
     * @param       category $category
     * @return      form
     *
     */
    public function createCategoryForm($category) {
        $builder = new AnnotationBuilder($this->entityManager);
        $form = $builder->createForm($category);
        $form->setHydrator(new DoctrineHydrator($this->entityManager, 'Blog\Entity\Category'));
        $form->bind($category);

        return $form;
    }
    
        /**
     *
     * Save category to database
     *
     * @param       category object
     * @return      void
     *
     */
    public function storeCategory($category) {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }
    
        /**
     *
     * Delete a category object from database
     * @param       category $category object
     * @return      void
     *
     */
    public function deleteCategory($category) {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

}
