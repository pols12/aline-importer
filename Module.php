<?php
namespace AlineImporter;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Omeka\Module\AbstractModule;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractController;
use Zend\View\Renderer\PhpRenderer;

class Module extends AbstractModule implements
		AutoloaderProviderInterface,
		ConfigProviderInterface {

	/**
	 * Get this module's configuration form.
	 *
	 * @param ViewModel $view
	 * @return string
	 */
	public function getConfigForm(PhpRenderer $view)
	{
		return '<input name="foo">';
	}

	/**
	 * Handle this module's configuration form.
	 *
	 * @param AbstractController $controller
	 * @return bool False if there was an error during handling
	 */
	public function handleConfigForm(AbstractController $controller)
	{
		return true;
	}

	/**
	 * Return an array for passing to Zend\Loader\AutoloaderFactory.
	 *
	 * @return array
	 */
	 public function getAutoloaderConfig()
	 {
		 return array(
			 'Zend\Loader\StandardAutoloader' => array(
				 'namespaces' => array(
					 // Autoload all classes of this namespace from '/module/Blog/src/Blog'
					 __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				 )
			 )
		 );
	 }

	/**
	 * Returns configuration to merge with application configuration
	 *
	 * @return array|\Traversable
	 */
	public function getConfig(){
		return include __DIR__ . '/config/module.config.php';
	}
	
}

