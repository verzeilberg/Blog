<?php

namespace Blog;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'blog' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/blog[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'blogbeheer' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/blogbeheer[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => 'blogbeheer',
                        'action' => 'index',
                    ],
                ],
            ],
            'blogajaxbeheer' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/blogajaxbeheer[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => 'blogajaxbeheer',
                        'action' => 'index',
                    ],
                ],
            ],
            'categorybeheer' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/beheer/categorie[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => 'categorybeheer',
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\BlogController::class => Controller\Factory\BlogControllerFactory::class,
            Controller\BlogAjaxController::class => Controller\Factory\BlogAjaxControllerFactory::class,
            Controller\CategoryController::class => Controller\Factory\CategoryControllerFactory::class
        ],
        'aliases' => [
            'blogbeheer' => Controller\BlogController::class,
            'blogajaxbeheer' => Controller\BlogAjaxController::class,
            'categorybeheer' => Controller\CategoryController::class,
        ],
    ],
    'service_manager' => [
        'invokables' => [
            Service\blogServiceInterface::class => Service\blogService::class,
            Service\categoryServiceInterface::class => Service\categoryService::class,
            Service\commentServiceInterface::class => Service\commentService::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],
    // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthorized visitors.
    'access_filter' => [
        'controllers' => [
            Controller\IndexController::class => [
                // to anyone.
                ['actions' => '*', 'allow' => '*']
            ],
            'blogbeheer' => [
                // to anyone.
                ['actions' => '*', 'allow' => '+blog.manage']
            ],
            'blogajaxbeheer' => [
                // to anyone.
                ['actions' => '*', 'allow' => '+blog.manage']
            ],
            'categorybeheer' => [
                // to anyone.
                ['actions' => '*', 'allow' => '+blog.manage']
            ]
        ]
    ],
];
