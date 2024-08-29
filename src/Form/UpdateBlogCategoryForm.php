<?php
namespace Blog\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use UploadImages\Form\UploadImageFieldset;

class UpdateBlogCategoryForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('update-category-blog-form');

        // The form will hydrate an object of type "Blog"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the Blog Category fieldset, and set it as the base fieldset
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