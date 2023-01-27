<?php

namespace Blog\Form;

use Blog\Entity\Category;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class CategoryFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('category');

        $this->setHydrator(new DoctrineHydrator($objectManager))
            ->setObject(new Category());

        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'name',
            'options' => [
                'label' => 'Name',
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'id' => [
                'required' => false,
            ],
            'name' => [
                'required' => true,
            ],
        ];
    }
}