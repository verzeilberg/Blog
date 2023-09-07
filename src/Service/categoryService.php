<?php

namespace Blog\Service;

use Doctrine\ORM\EntityManager;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\ServiceManager\ServiceLocatorInterface;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
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
     * @return Category
     */
    public function createCategory(): Category
    {
        return new Category();
    }

    /**
     * @return object
     */
    public function getCategories(): object
    {
        $qb = $this->entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC');
        return $qb->getQuery();
    }

    public function getItemsForPagination($query, $currentPage = 1, $itemsPerPage = 10): Paginator
    {
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage($itemsPerPage);
        $paginator->setCurrentPageNumber($currentPage);
        return $paginator;
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
