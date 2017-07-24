<?php

namespace AlineImporter\Job;
include 'Schemas.php';

class ListNeeded implements Schemas {
	public $tables=['ARCHIVES', 'CHPS', 'CHP_AUTHOR', 'HPPB', 'HPP_MISC', 'HPRPTPHD'];
	public function __construct(){
		$vocs=[];
		$sets=[];
		$templates=[];
		foreach($this->tables as $table){

			$t=constant("self::$table");
			foreach($t as $schema){
				
				$set=$schema['item_set'];
				if(!in_array($set, $sets))
					$sets[]=$set;
				
				$template=$schema['resource_template'];
				if(!in_array($template, $templates))
					$templates[]=$template;
				
				$this->getVocFromProperties($vocs, $schema['propertySchemas']);
				
				if(isset($schema['medias']))
					foreach ($schema['medias'] as $mediaSchema)
						$this->getVocFromProperties($vocs, $mediaSchema['propertySchemas']);
			}
		}
		echo '<pre>';
		echo 'Tables : '.strtolower(implode(", ",$this->tables)).PHP_EOL;
		echo 'Vocabulaires : '.implode(", ",$vocs).PHP_EOL;
		echo 'Item sets : '.implode(", ",$sets).PHP_EOL;
		echo 'Resource templates : '.implode(", ",$templates).PHP_EOL;
	}
	
	/**
	 * Complète la liste de vocabulaire donnée par ceux utilisée dans le schéma
	 * de proprités donné.
	 * @param array $vocs Liste de vocabulaire.
	 * @param array $propertySchemas $itemSchema['propertySchemas']
	 */
	private function getVocFromProperties(array &$vocs, array $propertySchemas) {
		foreach($propertySchemas as $term => $schema){
			$voc=explode(":",$term)[0];
			if(!in_array($voc, $vocs))
				$vocs[]=$voc;
		}
	}
}
$rien=new ListNeeded();
