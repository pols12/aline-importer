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
	const PREFIX = ''; //Préfixe des tables d’Aline dans la BDD.

	public function perform() {
		//On prépare la connexion à la BDD Aline
		$this->pdo = new \PDO('mysql:dbname=aline;host=localhost','omeka','omeka');
		//On récupère le service API
		$this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
		//On récupère le service de journalisation
		$this->logger = $this->getServiceLocator()->get('Omeka\Logger');
		\Zend\Log\Logger::registerErrorHandler($this->logger);

		$this->logger->info("Services initialisés. Récupération de la table...");
		
		//On récupère le nom de la table à importer depuis les arguments
		$this->table = $this->getArg('table');
		if(! defined('self::'.strtoupper($this->table)) )
			throw new \Exception("La table `$this->table` n’a pas de schéma défini.");
		
		//On récupère le schéma de cette table
		$this->tableSchema=constant('self::'.strtoupper($this->table));
		
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