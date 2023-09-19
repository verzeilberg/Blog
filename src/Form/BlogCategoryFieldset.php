<?php

namespace Blog\Form;

use Blog\Entity\Category;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class BlogCategoryFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('blog_category');

        $this->setHydrator(new DoctrineHydrator($objectManager))
            ->setObject(new Category());

        $this->add([
            'type'  => Text::class,
            'name' => 'name',
            'attributes' => [
                'id' => 'name',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Name',
            ],
        ]);

        $this->add([
            'type'  => Textarea::class,
            'name' => 'description',
            'attributes' => [
                'id' => 'description',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Description',
            ],
        ]);
    }

    /**
     * @return array|array[]
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'name' => [
                'required' => true,
            ],
            'description' => [
                'required' => false,
            ]
        ];
    }
}