<?php

namespace Blog\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Laminas\Form\Form;

class CreateBlogForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('create-blog-post-form');

        // The form will hydrate an object of type "Blog"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the Blog fieldset, and set it as the base fieldset
        $blogFieldset = new BlogFieldset($objectManager);
        $blogFieldset->setUseAsBaseFieldset(true);
        $this->add($blogFieldset);

        // … add CSRF and submit elements …

        // Optionally set your validation group here
    }
}