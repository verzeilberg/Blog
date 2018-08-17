<?php
namespace Blog\Controller\Factory;

use Interop\Container\ContainerInterface;
use Blog\Controller\IndexController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Search\Service\searchService;
use Blog\Service\blogService;
use Blog\Service\categoryService;
use Blog\Service\commentService;
use User\Service\RbacManager;

/**
 * This is the factory for AuthController. Its purpose is to instantiate the controller
 * and inject dependencies into its constructor.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {   
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $searchService = new searchService($entityManager);
        $rbacManager = $container->get(RbacManager::class);
        $viewhelpermanager = $container->get('ViewHelperManager');
        $blogService = new blogService($entityManager);
        $categoryService = new categoryService($entityManager);
        $commentService = new commentService($entityManager);
        return new IndexController($entityManager, $searchService, $rbacManager, $viewhelpermanager, $blogService, $categoryService, $commentService);
    }
}