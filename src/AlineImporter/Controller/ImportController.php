<?php

namespace AlineImporter\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use AlineImporter\Job\Import;
use AlineImporter\Job\ImportTask;

class ImportController extends AbstractActionController implements Schemas {
    
	/* @var $pdo \PDO */
    protected $pdo;
    /* @var $api \Omeka\Api\Manager */
	protected $api;
	/* @var $adapterManager \Omeka\Api\Adapter\Manager */
    protected $adapterManager;


	public function __construct(\PDO $pdo, $api, $adapterManager, $logger) {
		$this->pdo = $pdo;
		$this->api = $api;
		$this->adapterManager = $adapterManager;
		/* @var $logger \Zend\Log\Logger */
		$this->logger = $logger;
    }
	
    public function importAction() {
		//Lancement de l’importation
		/* @var $job \Omeka\Entity\Job */
		$job = $this->jobDispatcher()->dispatch(Import::class, ['table'=>'archives']);
//		$job = $this->jobDispatcher()->dispatch(Import::class, ['table'=>'chp_author']);
//		$job = $this->jobDispatcher()->dispatch(Import::class, ['table'=>'chps']);
		
		//Lancement de l’import sans utiliser de Job (lorsque php-cli n’est pas dispo)
//		$job = new ImportTask('archives',$this->api,$this->logger, $this->pdo);
//		$job->perform();
		
		$content = 'Import lancé' ;
//		$content='Aucune action n’a été spécifiée.';
		return new ViewModel([
			'content' => $content
		]);
    }
}