<?php

namespace Blog\Service;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\ServiceManager\ServiceLocatorInterface;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;

/*
 * Entities
 */
use Blog\Entity\Blog;

class blogService {

    protected $entityManager;

    /**
     * blogService constructor.
     * @param $entityManager
     */
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Blog|null $blog
     * @return string|null
     */
    public function createBlogUrl(Blog $blog = null) {
        if (!empty($blog)) {
            $request = new \Laminas\Http\PhpEnvironment\Request();
            $host = $request->getServer('HTTP_HOST');
            return 'http://' . $host . '/blog/detail/' . $blog->getId();
        } else {
            return null;
        }
    }

    /**
     * @return Blog|object
     */
    public function createBlog() {
        return new Blog();
    }

    /**
     * @param id $id
     * @return object
     */
    public function getBlogById($id) {
        $blog = $this->entityManager->getRepository(Blog::class)
                ->findOneBy(['id' => $id], []);

        return $blog;
    }

    /**
     * Get blogs for pagination
     * @return array
     */
    public function getBlogs() {
        $qb = $this->entityManager->getRepository(Blog::class)->createQueryBuilder('b')
            ->where('b.deleted = 0')
            ->orderBy('b.dateOnline', 'DESC');
        return $qb->getQuery();
    }

    /**
     * Search blogs for pagination
     * @param $searchString
     * @return mixed
     */
    public function searchBlogs($searchString)
    {
        $qb = $this->entityManager->getRepository(Blog::class)->createQueryBuilder('b');
        $orX = $qb->expr()->orX();
        $orX->add($qb->expr()->like('b.title', $qb->expr()->literal("%$searchString%")));
        $orX->add($qb->expr()->like('b.text', $qb->expr()->literal("%$searchString%")));
        $qb->where($orX);
        $qb->andWhere('b.deleted = 0');
        $qb->andWhere('b.online = 1');
        $qb->orderBy('b.dateOnline', 'DESC');
        return $qb->getQuery();
    }

    /**
     * @return mixed
     */
    public function getArchivedEvents()
    {
        $qb = $this->entityManager->getRepository(Blog::class)->createQueryBuilder('b')
            ->where('b.deleted = 1')
            ->orderBy('b.dateOnline', 'DESC');
        return $qb->getQuery();
    }

    /**
     * @param $query
     * @param int $currentPage
     * @param int $itemsPerPage
     * @return Paginator
     */
    public function getItemsForPagination($query, $currentPage = 1, $itemsPerPage = 10)
    {
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage($itemsPerPage);
        $paginator->setCurrentPageNumber($currentPage);
        return $paginator;
    }

    /**
     *
     * Get array of blogs
     *
     * @param       page  $page The page offset
     * @return      array
     *
     */
    public function getOnlineBlogsForPaginator($page = 1) {
        $qb = $this->entityManager->getRepository('Blog\Entity\Blog')->createQueryBuilder('b');
        $qb->select('b');
        $qb->where('b.deleted = 0');
        $qb->andWhere('b.online = 1');
        $qb->orderBy('b.dateOnline', 'DESC');
        
        $paginator = new Paginator(new DoctrinePaginator(new ORMPaginator($qb)));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(5);

        return $paginator;
    }

    /**
     *
     * Get array of blogs by search phrase
     *
     * @param       searchterm  $searchTerm to search for in database
     * @param       page  $page The page offset
     * @return      array
     *
     */
    public function getBlogsBySearchPhrase($searchTerm, $page = 1) {
        $qb = $this->entityManager->getRepository('Blog\Entity\Blog')->createQueryBuilder('b');
        $orX = $qb->expr()->orX();
        $orX->add($qb->expr()->like('b.title', $qb->expr()->literal("%$searchTerm%")));
        $orX->add($qb->expr()->like('b.text', $qb->expr()->literal("%$searchTerm%")));
        $qb->where($orX);
        $qb->andWhere('b.deleted = 0');
        $qb->andWhere('b.online = 1');
        $qb->orderBy('b.dateOnline', 'DESC');

        $paginator = new Paginator(new DoctrinePaginator(new ORMPaginator($qb)));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(5);

        return $paginator;
    }

    /**
     *
     * Get array of blogs
     *
     * @param       id  $id of the category
     * @param       page  $page The page offset
     * @return      array
     *
     */
    public function getBlogsByCategoryIdForPaginator($id, $page = 1) {
        $qb = $this->entityManager->getRepository('Blog\Entity\Blog')->createQueryBuilder('b');
        $qb->select('b');
        $qb->join('b.categories', 'c');
        $qb->where('b.deleted = 0');
        $qb->andWhere('b.online = 1');
        $qb->andWhere('c.id = ' . $id);
        $qb->orderBy('b.dateOnline', 'DESC');

        $paginator = new Paginator(new DoctrinePaginator(new ORMPaginator($qb)));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage(5);

        return $paginator;
    }

    /**
     *
     * Get array of blogs
     *
     * @param       start  $start of the blogs
     * @param       end  $end The blogs offset
     * @return      array
     *
     */
    public function getOnlineBlogsBasedOnStartAndOffSet($start = 0, $end = 3) {
        $blogs = $this->entityManager->getRepository(Blog::class)
                ->findBy(['deleted' => 0, 'online' => 1], ['dateOnline' => 'DESC'], $end, $start);

        return $blogs;
    }

    /**
     *
     * Get array of blogs
     *
     * @return      array
     *
     */
    public function getArchivedBlogs() {
        $blogs = $this->entityManager->getRepository(Blog::class)
                ->findBy(['deleted' => 1], ['id' => 'ASC']);

        return $blogs;
    }

    /**
     *
     * Create form of an object
     *
     * @param       blog $event $blog
     * @return      form
     *
     */
    public function createBlogForm($blog) {
        $builder = new AnnotationBuilder($this->entityManager);
        $form = $builder->createForm($blog);
        $form->setHydrator(new DoctrineHydrator($this->entityManager, Blog::class));
        $form->bind($blog);

        return $form;
    }

    /**
     *
     * Set data to new blog
     *
     * @param       blog $blog object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setNewBlog($blog, $currentUser) {
        $blog->setDateCreated(new \DateTime());
        $blog->setCreatedBy($currentUser);

        $this->storeBlog($blog);
    }

    /**
     *
     * Set data to existing blog
     *
     * @param       blog $blog object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setExistingBlog($blog, $currentUser) {
        $blog->setDateChanged(new \DateTime());
        $blog->setChangedBy($currentUser);
        $this->storeBlog($blog);
    }

    /**
     *
     * Archive blog
     *
     * @param       blog $blog object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function archiveBlog($blog, $currentUser) {
        $blog->setDateDeleted(new \DateTime());
        $blog->setDeleted(1);
        $blog->setDeletedBy($currentUser);

        $this->storeBlog($blog);
    }

    /**
     *
     * UnArchive blog
     *
     * @param       blog $blog object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function unArchiveBlog($blog, $currentUser) {
        $blog->setDeletedBy(NULL);
        $blog->setChangedBy($currentUser);
        $blog->setDeleted(0);
        $blog->setDateDeleted(NULL);
        $blog->setDateChanged(new \DateTime());

        $this->storeBlog($blog);
    }

    /**
     *
     * Set blog online or offline
     *
     * @param       blog $blog object
     * @param       status $status integer
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setBlogOnlineOffline($blog, $status, $currentUser) {
        $blog->setOnline($status);
        $blog->setDateChanged(new \DateTime());
        $blog->setChangedBy($currentUser);

        $this->storeBlog($blog);
    }

    /**
     *
     * Set youtube video to blog
     *
     * @param       blog $blog object
     * @param       youtube $youtube object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function addYouTubeToBlog($blog, $youtube, $currentUser) {
        $blog->addBlogYouTubes($youtube);
        $blog->setDateChanged(new \DateTime());
        $blog->setChangedBy($currentUser);

        $this->storeBlog($blog);
    }

    /**
     *
     * Save blog to database
     *
     * @param       blog object
     * @return      void
     *
     */
    public function storeBlog($blog) {
        $this->entityManager->persist($blog);
        $this->entityManager->flush();
    }

    /**
     *
     * Delete a Blog object from database
     * @param       blog $blog object
     * @return      void
     *
     */
    public function deleteBlog($blog) {
        $this->entityManager->remove($blog);
        $this->entityManager->flush();
    }

}
