<?php

namespace Blog\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Event\Form\EventFieldset;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Element\Time;
use Laminas\Form\Form;
use UploadImages\Form\UploadImageFieldset;

class CreateBlogForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('create-blog-form');

        // The form will hydrate an object of type "Blog"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the Blog fieldset, and set it as the base fieldset
        $blogFieldset = new BlogFieldset($objectManager);
        $blogFieldset->setUseAsBaseFieldset(true);
        $this->add($blogFieldset);
        // Add the Upload image fieldset, and set it as the base fieldset
        $uploadImageFieldset = new UploadImageFieldset($objectManager);
        $uploadImageFieldset->setUseAsBaseFieldset(false);
        $this->add($uploadImageFieldset);

        // Add the Submit button
        $this->add([
            'type'  => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Opslaan',
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