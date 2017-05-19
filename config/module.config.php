<?php
namespace TestM;
use Zend\Router\Http\Literal;

return array(
//// View //////
	'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
//// Controller //////
	'controllers' => array(
		'factories' => array(
		Controller\ListController::class => Factory\ListControllerFactory::class,
		)
	),
//// Router ////////
	'router' => array(
		// Open configuration for all possible routes
		'routes' => array(
			// Define a new route called "maroute"
			'maroute' => array(
				// Define the routes type to be "Zend\Mvc\Router\Http\Literal", which is basically just a string
				'type' => Literal::class,
				// Configure the route itself
				'options' => array(
					// Listen to "/testm" as uri
					'route' => '/testm',
					// Define default controller and action to be called when this route is matched
					'defaults' => array(
						'controller' => Controller\ListController::class,
						'action'     => 'aff',
					)
				)
			)
		)
	)
 );

