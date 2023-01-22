<?php

namespace Blog\Form;

use Laminas\Form\Form;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\ArrayInput;

/**
 * This form is used to collect user's email, full name, password and status. The form 
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, user
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class BlogForm extends Form {

    /**
     * Scenario ('create' or 'update').
     * @var string 
     */
    private $scenario;

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager = null;

    /**
     * Current user.
     * @var Blog\Entity\Blog 
     */
    private $blog = null;

    /**
     * Constructor.     
     */
    public function __construct($scenario = 'create', $entityManager = null, $blog  = null) {
        // Define form name
        parent::__construct('user-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->blog = $blog ;

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements() {
        // Add "title" field
        $this->add([
            'type' => 'text',
            'name' => 'title',
            'options' => [
                'label' => 'Titel',
            ],
        ]);

        // Add "text" field
        $this->add([
            'type' => 'textarea',
            'name' => 'text',
            'options' => [
                'label' => 'Text',
            ],
        ]);


        // Add the Submit button
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Create'
            ],
        ]);
    }



}
