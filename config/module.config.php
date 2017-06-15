<?php
namespace AlineImporter;
use Zend\Router\Http\Literal;

return [
//// View //////
	'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
//// Controller //////
	'controllers' => [
		'factories' => [
			Controller\ImportController::class => Factory\ImportControllerFactory::class,
		]
	],
//// Router ////////
	'router' => [
		// Open configuration for all possible routes
		'routes' => [
			// Define a new route called "importroute"
			'importroute' => [
				// Define the routes type to be "Zend\Mvc\Router\Http\Literal", which is basically just a string
				'type' => Literal::class,
				// Configure the route itself
				'options' => [
					// URL de la page
					'route' => '/aline-importer',
					// Define default controller and action to be called when this route is matched
					'defaults' => [
						'controller' => Controller\ImportController::class,
						'action'     => 'import',
					]
				]
			]
		]
	]
 ];