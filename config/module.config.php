<?php
namespace TestM;
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
			Controller\ListController::class => Factory\ListControllerFactory::class,
		]
	],
//// Router ////////
	'router' => [
		// Open configuration for all possible routes
		'routes' => [
			// Define a new route called "maroute"
			'maroute' => [
				// Define the routes type to be "Zend\Mvc\Router\Http\Literal", which is basically just a string
				'type' => Literal::class,
				// Configure the route itself
				'options' => [
					// Listen to "/testm" as uri
					'route' => '/testm',
					// Define default controller and action to be called when this route is matched
					'defaults' => [
						'controller' => Controller\ListController::class,
						'action'     => 'aff',
					]
				]
			]
		]
	]
 ];