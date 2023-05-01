<?php
namespace Blog\Form;

use Blog\Entity\Blog;
use Blog\Entity\Comment;
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

class CommentFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('blog');

        $this->setHydrator(new DoctrineHydrator($objectManager))
            ->setObject(new Comment());


        $this->add([
            'type'  => Textarea::class,
            'name' => 'comment',
            'options' => [
                'label' => 'Comment',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

    }

    public function getInputFilterSpecification()
    {
        return [
            'comment' => [
                'required' => false,
            ],
        ];
    }
}