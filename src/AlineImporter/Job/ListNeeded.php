<?php

namespace AlineImporter\Job;
//include 'Schemas_withoutAHPO.php'; //old config file which don’t use AHP ontology
include 'Schemas.php';

class ItemType {
	public $name, $properties;
	
	public function __construct($name) {
		$this->name=$name;
		$this->properties=[];
		return $this;
	}

	public function __toString() {
		$str = "$this->name \n";
		foreach($this->properties as $voc => $props_arr) {
			$props_str = implode(', ', $props_arr);
			$str .= "   $voc : $props_str \n";
		}
		return $str;
	}

	public function addTerm(string $term) {
		$term_array = explode(":",$term);
		$voc = $term_array[0];
		$prop = $term_array[1];
		if( !array_key_exists($voc, $this->properties)
				|| !in_array($prop, $this-> properties[$voc]) ) {
			$this->properties[$voc][] = $prop;
			return true;
		}
		else return false;
	}
}

class SchemaAnalyser implements Schemas {
	public $tables=['ARCHIVES', 'CHPS', 'CHP_AUTHOR', 'HPPB', 'HPP_MISC', 'HPRPTPHD'];
	public function listNeeded(){
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
		echo 'Vocabulaires : '.print_r($vocs,true); // .implode(", ",$vocs).PHP_EOL;
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
			$term_array = explode(":",$term);
			$voc = $term_array[0];
			$prop = $term_array[1];
			if( !array_key_exists($voc, $vocs) || !in_array($prop, $vocs[$voc]) ) // if(!in_array($voc, $vocs))
				$vocs[$voc][] = $prop; // $vocs[]=$voc;
		}
	}

	public function listItems() {
		$items=[];
		foreach($this->tables as $tableName){
			$table=constant("self::$tableName");
			foreach($table as $itemName => $item){
				if(!isset($items[$itemName])) 
					$items[$itemName] = new ItemType($itemName);
				foreach($item['propertySchemas'] as $term => $schema)
					$items[$itemName]->addTerm($term);
			}
		}
		return $items;
	}
	public function displayItems(array $items = []) {
		if(empty($items)) $items = $this->listItems();

		foreach($items as $item) echo "$item\n\n";
	}
}
$analyser=new SchemaAnalyser();
echo '<pre>';
$analyser->displayItems();
$analyser->listNeeded();
