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

class categoryService {

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
            ->where('c.deleted = 0')
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

    public function getAllCategories() {
        $categories = $this->entityManager->getRepository(Category::class)
            ->findAll();

        return $categories;
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
     * @param $category
     * @param $currentUser
     * @return void
     */
    public function setNewCategory($category, $currentUser): void
    {
        $category->setDateCreated(new \DateTime());
        $category->setCreatedBy($currentUser);

        $this->storeCategory($category);
    }

    /**
     * @param $category
     * @param $currentUser
     * @return void
     */
    public function setExistingCategory($category, $currentUser): void
    {
        $category->setDateChanged(new \DateTime());
        $category->setChangedBy($currentUser);
        $this->storeCategory($category);
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

    /**
     * @param $category
     * @param $currentUser
     * @return void
     */
    public function archiveCategory($category, $currentUser): void
    {
        $category->setDateDeleted(new \DateTime());
        $category->setDeleted(1);
        $category->setDeletedBy($currentUser);

        $this->storeCategory($category);
    }

    /**
     * @return mixed
     */
    public function getArchivedCategories(): mixed
    {
        $qb = $this->entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->where('c.deleted = 1')
            ->orderBy('c.name', 'DESC');
        return $qb->getQuery();
    }

    /**
     * @param $searchString
     * @param int $deleted
     * @return mixed
     */
    public function searchCategorie($searchString, int $deleted = 0): mixed
    {
        $qb = $this->entityManager->getRepository(Category::class)->createQueryBuilder('c');
        $orX = $qb->expr()->orX();
        $orX->add($qb->expr()->like('c.name', $qb->expr()->literal("%$searchString%")));
        $orX->add($qb->expr()->like('c.description', $qb->expr()->literal("%$searchString%")));
        $qb->where($orX);
        $qb->andWhere('c.deleted = :deleted');
        $qb->orderBy('c.name', 'DESC');
        $qb->setParameter('deleted', $deleted);
        return $qb->getQuery();
    }

    /**
     * @param $category
     * @param $currentUser
     * @return void
     */
    public function unArchiveCategory($category, $currentUser): void
    {
        $category->setDeletedBy(NULL);
        $category->setChangedBy($currentUser);
        $category->setDeleted(0);
        $category->setDateDeleted(NULL);
        $category->setDateChanged(new \DateTime());

        $this->storeCategory($category);
    }

}
