<?php
namespace AlineImporter\Factory;

use AlineImporter\Controller\ImportController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
/**
 * Description of ListControllerFactory
 *
 * @author pols12
 */
class ImportControllerFactory implements FactoryInterface{
    
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null) {
        //$mediaIngesterManager = $serviceLocator->get('Omeka\MediaIngesterManager');
        $apiManager = $serviceLocator->get('Omeka\ApiManager');
        
		$pdo=new \PDO('mysql:dbname=aline;host=localhost','omeka','omeka');
        
		$adapterManager= $serviceLocator->get('Omeka\ApiAdapterManager');
		
		//We must allow client using cURL to upload file to ListController
		$acl=$serviceLocator->get('Omeka\Acl');
		$acl->allow(); //allow anybody to do anything anywhere
		
        return new ImportController($pdo, $apiManager, $adapterManager);
    }

}
