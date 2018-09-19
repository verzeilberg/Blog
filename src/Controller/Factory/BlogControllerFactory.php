<?php

namespace Blog\Controller\Factory;

use Interop\Container\ContainerInterface;
use Blog\Controller\BlogController;
use Zend\ServiceManager\Factory\FactoryInterface;
use UploadImages\Service\cropImageService;
use UploadImages\Service\imageService;
use Twitter\Service\twitterOathService;
use Twitter\Service\twitterService;
use Blog\Service\blogService;

/**
 * This is the factory for AuthController. Its purpose is to instantiate the controller
 * and inject dependencies into its constructor.
 */
class BlogControllerFactory implements FactoryInterface {

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $viewhelpermanager = $container->get('ViewHelperManager');
        $config = $container->get('config');
        $cropImageService = new cropImageService($entityManager, $config);
        $imageService = new imageService($entityManager, $config);
        $twitterService = new twitterService();
        $twitterOathService = new twitterOathService($config, $twitterService);
        $blogService = new blogService($entityManager);
        return new BlogController($entityManager, $viewhelpermanager, $cropImageService, $imageService, $twitterOathService, $blogService, $twitterService);
    }

}
