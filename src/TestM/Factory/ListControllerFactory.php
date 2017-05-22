<?php
namespace TestM\Factory;

use TestM\Controller\ListController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
/**
 * Description of ListControllerFactory
 *
 * @author pols12
 */
class ListControllerFactory implements FactoryInterface{
    
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null) {
        //$mediaIngesterManager = $serviceLocator->get('Omeka\MediaIngesterManager');
        $doctrine = $serviceLocator->get('Omeka\ApiManager');
        $config = $serviceLocator->get('Config');
        
        return new ListController($config, $doctrine);
    }

}
