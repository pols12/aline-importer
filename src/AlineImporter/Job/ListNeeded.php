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
				
				foreach($schema['propertySchemas'] as $term => $schema){
					$voc=explode(":",$term)[0];
					if(!in_array($voc, $vocs))
						$vocs[]=$voc;
				}
			}
		}
		echo '<pre>';
		echo 'Tables : '.strtolower(implode(", ",$this->tables)).PHP_EOL;
		echo 'Vocabulaires : '.implode(", ",$vocs).PHP_EOL;
		echo 'Item sets : '.implode(", ",$sets).PHP_EOL;
		echo 'Resource templates : '.implode(", ",$templates).PHP_EOL;
	}
}
$rien=new ListNeeded();
