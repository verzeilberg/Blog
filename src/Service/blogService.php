<?php

namespace Blog\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

/*
 * Entities
 */
use Blog\Entity\Blog;

class blogService implements blogServiceInterface {

    /**
     * Constructor.
     */
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     *
     * Create an url from a blog object
     *
     * @param       blog  $blog The object to create Blog url from
     * @return      string
     *
     */
    public function createBlogUrl(Blog $blog = null) {
        if (!empty($blog)) {
            $request = new \Zend\Http\PhpEnvironment\Request();
            $host = $request->getServer('HTTP_HOST');
            return 'http://' . $host . '/blog/detail/' . $blog->getId();
        } else {
            return null;
        }
    }

    /**
     *
     * Create new Blog object
     *
     * @return      object
     *
     */
    public function createBlog() {
        return new Blog();
    }

    /**
     *
     * Get blog object based on id
     *
     * @param       id  $id The id to fetch the blog from the database
     * @return      object
     *
     */
    public function getBlogById($id) {
        $blog = $this->entityManager->getRepository(Blog::class)
                ->findOneBy(['id' => $id], []);

        return $blog;
    }

    /**
     *
     * Get array of blogs
     *
     * @return      array
     *
     */
    public function getBlogs() {
        $blogs = $this->entityManager->getRepository(Blog::class)
                ->findBy(['deleted' => 0], ['dateOnline' => 'DESC']);

        return $blogs;
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
                ->findBy(['deleted' => 0, 'online' => 1], ['dateCreated' => 'DESC'], $end, $start);

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
        $form->setHydrator(new DoctrineHydrator($this->entityManager, 'Blog\Entity\Blog'));
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
