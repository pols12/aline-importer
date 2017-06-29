<?php
namespace AlineImporter\Job;

/**
 * Description of Import
 *
 * @author pols12
 */
class ImportTask implements \AlineImporter\Controller\Schemas {
	use ImportTrait;
	
	const SEPARATOR = '€€'; //Séparateur arbitraire qui ne risque pas d’être dans les colonnes
	const PREFIX = 'aline__'; //Préfixe des tables d’Aline dans la BDD.
	
	public function __construct($table, $api, $logger, $pdo) {
		//On récupère le service API
		$this->api = $api;
		//On récupère le service de journalisation
		$this->logger = $logger;
		$this->logger->addWriter(new \Zend\Log\Writer\Stream('php://output'));
		\Zend\Log\Logger::registerErrorHandler($this->logger);
		
		//On récupère la connexion à la BDD Aline
		$this->pdo = $pdo;
		
		//On récupère le nom de la table à importer depuis les arguments
		$this->table = self::PREFIX.$table;
		if(! defined('self::'.strtoupper($table)) )
			throw new \Exception("La table `$this->table` n’a pas de schéma défini.");
		
		//On récupère le schéma de cette table
		$this->tableSchema=constant('self::'.strtoupper($table));
	}
	
	public function perform() {
		$this->logger->info("Début de l’import de la table `{$this->table}`.");
		
		$this->prepareTable();
		
		//Et on lance l’importation
		$this->import();
		
		$this->logger->info('Importation terminée.');
	}
	
    private function import() {
		foreach ($this->tableSchema as $itemSchema) {
			$count = $this->importItemType($itemSchema);
		}
		
		return $count;
    }
}