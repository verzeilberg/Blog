<?php

namespace Blog\Form;

use Blog\Entity\Category;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use DoctrineModule\Form\Element\ObjectMultiCheckbox;
use DoctrineModule\Form\Element\ObjectSelect;
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
            'name' => 'categories',
            'required' => false,
            'type' => ObjectMultiCheckbox::class,
            'options' => [
                'object_manager' => $objectManager,
                'target_class'   => Category::class,
                'property'       => 'id',
                'is_method'      => true,
                'display_empty_item' => false,
                'label' => 'Categorieeen',
                'label_generator' => function ($targetEntity) {
                    return $targetEntity->getName();
                },
            ],
            'attributes' => [
                'class' => 'form-check-input',
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'categories' => [
                'required' => false,
            ]
        ];
    }
}