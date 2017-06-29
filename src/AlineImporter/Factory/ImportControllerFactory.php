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
        
		require __DIR__ .'/../../../config/db.config.php';
		$pdo=new \PDO("mysql:dbname=$dbname;host=$host;charset=utf8",$user,$password);
        
		$adapterManager= $serviceLocator->get('Omeka\ApiAdapterManager');
		
		//We must allow client using cURL to upload file to ListController
		$acl=$serviceLocator->get('Omeka\Acl');
		$acl->allow(); //allow anybody to do anything anywhere
		
		$logger=$serviceLocator->get('Omeka\Logger');
		
        return new ImportController($pdo, $apiManager, $adapterManager, $logger);
    }

}
