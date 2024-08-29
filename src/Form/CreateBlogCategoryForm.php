<?php

namespace Blog\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;

class CreateBlogCategoryForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('create-category-blog-form');

        // The form will hydrate an object of type "Category"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the Blog category fieldset, and set it as the base fieldset
        $blogCategoryFieldset = new BlogCategoryFieldset($objectManager);
        $blogCategoryFieldset->setUseAsBaseFieldset(true);
        $this->add($blogCategoryFieldset);

        // Add the Submit button
        $this->add([
            'type'  => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Toevoegen',
                'id' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);

        // Add the CSRF field
        $this->add([
            'type' => Csrf::class,
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ],
        ]);

    }
}