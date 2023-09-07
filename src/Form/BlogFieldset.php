<?php
namespace Blog\Form;

use Blog\Entity\Blog;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Collection;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Element\Time;
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
            'type'  => Hidden::class,
            'name' => 'online',
            'attributes' => [
            ],
        ]);

        $this->add([
            'type'  => Date::class,
            'name' => 'dateOnline',
            'options' => [
                'label' => 'Date online',
                'format' => 'd-m-Y',
            ],
            'attributes' => [
                'class' => 'form-control dateOnline',
                'readonly' => 'readonly',
            ],
        ]);

        $this->add([
            'type'  => Date::class,
            'name' => 'dateOffline',
            'options' => [
                'label' => 'Date offline',
                'format' => 'd-m-Y',
            ],
            'attributes' => [
                'class' => 'form-control dateOffline',
                'readonly' => 'readonly',
            ],
        ]);

        $this->add([
            'type'  => Text::class,
            'name' => 'timeOnline',
            'attributes' => [
                'id' => 'timeOnline',
                'step' => 'any',
            ],
        ]);

        $this->add([
            'type'  => Text::class,
            'name' => 'timeOffline',
            'attributes' => [
                'id' => 'timeOffline',
                'step' => 'any',
            ],
        ]);

        $this->add([
            'type'  => Text::class,
            'name' => 'title',
            'options' => [
                'label' => 'Titel',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type'  => Textarea::class,
            'name' => 'introText',
            'options' => [
                'label' => 'Intro text',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type'  => Textarea::class,
            'name' => 'text',
            'options' => [
                'label' => 'Text',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'editor',
            ],
        ]);

        $tagFieldset = new CategoryFieldset($objectManager);
        $this->add([
            'type'    => Collection::class,
            'name'    => 'categories',
            'options' => [
                'target_element' => $tagFieldset,
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'categories' => [
                'required' => false,
            ],
        ];
    }
}