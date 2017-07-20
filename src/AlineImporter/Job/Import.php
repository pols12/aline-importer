<?php
namespace AlineImporter\Job;

use Omeka\Job\AbstractJob;
use Omeka\Api\Representation\ResourceReference;

/**
 * Description of Import
 *
 * @author pols12
 */
class Import extends AbstractJob implements Schemas {
	use ImportTrait;

	const SEPARATOR = '€€'; //Séparateur arbitraire qui ne risque pas d’être dans les colonnes
	const PREFIX = 'aline__'; //Préfixe des tables d’Aline dans la BDD.

	public function perform() {
		//On prépare la connexion à la BDD Aline
//		$this->pdo = $this->getArg('pdo');//new \PDO('mysql:dbname=aline;host=localhost','omeka','omeka');
		require __DIR__ .'/../../../config/db.config.php';
		$this->pdo =new \PDO("mysql:dbname=$dbname;host=$host;charset=utf8",$user,$password);
		
		//On récupère le service API
		$this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
		//On récupère le service de journalisation
		$this->logger = $this->getServiceLocator()->get('Omeka\Logger');
		\Zend\Log\Logger::registerErrorHandler($this->logger);
		
		$this->logger->info("Augmentation de la mémoire. Valeur précédente : "
				.ini_set('memory_limit', '256M'));
		$this->logger->info("Augmentation du temps limite à 120 :"
				.(set_time_limit(120) ? "OK" : "échec"));
		
		//On récupère le nom de la table à importer depuis les arguments
		$this->table = self::PREFIX.$this->getArg('table');
		if(! defined('self::'.strtoupper($this->getArg('table'))) )
			throw new \Exception("La table `$this->table` n’a pas de schéma défini.");
		
		//On récupère le schéma de cette table
		$this->tableSchema=constant('self::'.strtoupper($this->getArg('table')));
		
		$this->logger->info("Schéma de la table {$this->table} récupéré.");
		
		$this->prepareTable();
		
		//Et on lance l’importation
		$this->import();
		
		$this->logger->info('Importation terminée.');
	}
	
    private function import() {
		foreach ($this->tableSchema as $itemSchema) {
			$count = $this->importItemType($itemSchema);
			
			if($this->shouldStop()) {
				$this->logger->warn("Le Job a dû s’arrêter avant de se terminer.");
				break;
			}
		}
		
		return $count;
	}
	
}