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
use Blog\Entity\Comment;

class commentService implements commentServiceInterface {

    /**
     * Constructor.
     */
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    public function createCommentForm($comment) {
        $builder = new AnnotationBuilder($this->entityManager);
        $commentForm = $builder->createForm($comment);
        $commentForm->setHydrator(new DoctrineHydrator($this->entityManager, 'Blog\Entity\Comment'));
        $commentForm->bind($comment);

        return $commentForm;
    }

    public function createComment() {
        return new Comment();
    }

    public function setNewComment($comment, $currentUser, $blog = null, $parentComment = null) {
        $comment->setDateCreated(new \DateTime());
        $comment->setCreatedBy($currentUser);
        if (!empty($parentComment)) {
            $comment->setParent($parentComment);
        }
        if (!empty($blog)) {
            $comment->setBlog($blog);
        }

        $this->storeComment($comment);
    }

    public function storeComment($comment) {
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }

    public function getCommentById($id) {
        $comment = $this->entityManager->getRepository(Comment::class)
                ->findOneBy(['id' => $id], []);

        return $comment;
    }

}
