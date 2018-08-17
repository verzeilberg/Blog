<?php

namespace Blog\Controller\Factory;

use Interop\Container\ContainerInterface;
use Blog\Controller\CategoryController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Blog\Service\categoryService;

/**
 * This is the factory for AuthController. Its purpose is to instantiate the controller
 * and inject dependencies into its constructor.
 */
class CategoryControllerFactory implements FactoryInterface {

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $viewhelpermanager = $container->get('ViewHelperManager');
        $categoryService = new categoryService($entityManager);
        return new CategoryController($entityManager, $viewhelpermanager, $categoryService);
    }

}
