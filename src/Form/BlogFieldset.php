<?php
namespace Blog\Form;

use Blog\Entity\Blog;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Laminas\Form\Element\Collection;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class BlogFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('blog');

        $this->setHydrator(new DoctrineHydrator($objectManager))
            ->setObject(new Blog());

        $this->add([
            'type' => Text::class,
            'name' => 'title',
        ]);

        $tagFieldset = new CategoryFieldset($objectManager);
        $this->add([
            'type'    => Collection::class,
            'name'    => 'categories',
            'options' => [
                'count'          => 2,
                'target_element' => $tagFieldset,
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'title' => [
                'required' => true,
            ],
        ];
    }
}