<?php

namespace TestM\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Api\Representation\ResourceReference;
//use TestM\Job\Insert;

class ListController extends AbstractActionController implements Schemas {
    
	/* @var $pdo \PDO */
    protected $pdo;
    /* @var $api \Omeka\Api\Manager */
	protected $api;
	/* @var $adapterManager \Omeka\Api\Adapter\Manager */
    protected $adapterManager;
	
    
    public function __construct(\PDO $pdo, $api, $adapterManager) {
		$this->pdo = $pdo;
		$this->api = $api;
		$this->adapterManager = $adapterManager;
    }
	
	private function postFile() {
		$data = [];
		$data['monFichier'] = new \CURLFile(__DIR__ . '/test.txt','text/plain','nomDuFichierSurLeServeur');
		
		//adding keys
		$url="http://localhost/omeka-s/testm";
		
		$options=[
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data']
		];
		
		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		$result=curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	/**
     * Add a text as media to an existing item.
     * @param string $text Contenu du media.
	 * @param int $itemId Id of item which will have the media attached.
     */
    private function addHtmlMediaTo(string $text, string $titre, int $itemId) {

		$data=[
			"o:ingester" => "html",
			"o:is_public" => false,
			"o:item" => ["o:id" => $itemId],
			"html" => htmlspecialchars($text),
			"titre" => [[
				"type" => "literal",
				'property_id' => 1,
				'@value' => $titre,
			]]
		];
		return $this->api->create('media', $data)->getContent();
	}
	
	/**
     * Add a media to an existing item uploading a file using ApiManager.
     * @param int $itemId Id of item which will have the media attached.
     */
    private function attachMedia($itemId) {
		$fileIndex=0;

		$data=[
			"o:ingester" => "upload",
			"file_index" => $fileIndex,
			"o:item" => ["o:id" => $itemId]
		];
		$fileData = [
			'file'=>[
				$fileIndex => $_FILES['monFichier'],
			],
		];
		return $this->api->create('media', $data, $fileData)->getContent();
	}
	
    /**
     * Importe la table `archives` d’Aline dans Omeka.
     * @return ItemRepresentation
     */
    private function import(string $table) {
		if(! defined('self::'.strtoupper($table)) )
			throw new \Exception("La table `$table` n’a pas de schéma défini.");
		
		$tableSchema=constant('self::'.strtoupper($table));
		
		foreach ($tableSchema as $itemSchema) {
			$valueRows = $this->getCleanedRows($table, $itemSchema['propertySchemas']);
			
			//Définit la classe, le modèle et la collection
			$genericData = $this->getGenericData($itemSchema);
			
			//Prepare schema
			$this->setSchemaPropertyIds($itemSchema['propertySchemas']);
			
			//On traite les jointures
			$this->setLinkedItemIds($valueRows, $itemSchema['propertySchemas'], $table);
			
			//On récupères les propriétés
			$allItemProperties=[];
			foreach ($valueRows as $row) {//Pour chaque entrée dans Aline
				//Nouvel élément pour contenir toutes les propriétés de l’entrée Aline
//				$allItemProperties[]=[];
				
				//On récupère la référence de ce nouvel élément
//				$itemProperties=&$allItemProperties[count($allItemProperties)-1];
				
				//Et on y ajoute les propriétés
				$allItemProperties[] = NULL===$row ? NULL
						: array_merge(
							$this->hydrateProperties($itemSchema['propertySchemas'], $row),
							$this->hydrateMedias($itemSchema, $row)
						);
				
				//Enfin, on y ajoute les notes privées comme média
//				$this->hydrateMedias($itemProperties, $itemSchema['medias'], $row);
			}
			
			//Et on fusionne le tout pour remplir $itemDataList
			$itemDataList = []; //Tableau indexant les tableaux JSON-LD des items
			foreach ($valueRows as $rowNumber => $row){
				$properties = $allItemProperties[$rowNumber];
				if( !is_null($properties) )
					$itemDataList[$row['id']] = array_merge($genericData, $properties);
			}
			
			//Tableau associant aux clés de $itemDataList les ResourceReference des items créés
			$itemReferences=$this->api->batchCreate('items', $itemDataList)->getContent();
			
			//On stocke dans Aline les Ids des items que l’on vient de créer.
			$this->persistIds($itemReferences, $table,
					$itemSchema['persist_column'], $itemSchema['item_set']);
		}
		
		return count($itemReferences);
    }
	
    /**
     * Importe la table `archives` d’Aline dans Omeka.
     * @return ItemRepresentation
     */
    private function importArchives() {
		include 'itemSchemas.php';
		$alineValueRows=$this->getValuesFromAline('archive','tout');
		$this->getCleanedRows($alineValueRows, $addressPropertySchema);
		$this->getCleanedRows($alineValueRows, $archivePropertySchema);
		
		//On s’occupe d’abord des adresses
		
		$addressDataList = []; //Tableau indexant les tableaux JSON-LD des items
		
		//Set ResourceClass and RessourceTemplate
		$genericAddressData=[];
		$genericAddressData['o:resource_class'] = $this->getResourceClassSchema('locn:Address');
		$genericAddressData['o:resource_template'] = $this->getResourceTemplateSchema('Adresse');
		
		//Prepare schema
		$this->setSchemaPropertyIds($addressPropertySchema);
		
		$allAddressItemProperties=[];
		
		//Get properties
		foreach ($alineValueRows as $row) {//Pour chaque entrée dans Aline
			$allAddressItemProperties[]=[];
			$addressItemProperties=&$allAddressItemProperties[count($allAddressItemProperties)-1];
			$this->hydrateProperties($addressItemProperties, $addressPropertySchema, $row);
		}
		
		//Et on fusionne le tout pour remplir $itemDataList
		foreach ($alineValueRows as $rowNumber => $row){
			$properties = $allAddressItemProperties[$rowNumber];
			if( !is_null($properties) )
				$addressDataList[$row['id']] = array_merge($genericAddressData, $properties);
		}
		
		//Tableau associant aux clés de $dataArray les ResourceReference des items créés
		/* @var $addressItems array(\Omeka\Api\Representation\ResourceReference) */
		$addressItems=$this->api->batchCreate('items', $addressDataList)->getContent();
		
		
		//Maintenant on importe les lieux d’archives
		
		$archiveDataList = []; //Tableau indexant les tableaux JSON-LD des items
		
		//Set ResourceClass and RessourceTemplate
		$genericArchiveData=[];
		$genericArchiveData['o:resource_class'] = $this->getResourceClassSchema('dcterms:Location');
		$genericArchiveData['o:resource_template'] = $this->getResourceTemplateSchema('Lieu d’archives');
		
		//On prépare le schéma
		$this->setSchemaPropertyIds($archivePropertySchema);
		
		//On récupères les propriétés
		$allArchiveItemProperties=[];
		foreach ($alineValueRows as $row) {//Pour chaque entrée dans Aline
			//On traite les jointures (ajout des Ids dans $row)
			if( isset($addressItems[$row['id']]) )
				$row['AddressItem']=$addressItems[$row['id']]->id();
			
			//Nouvel élément pour contenir toutes les propriétés de l’entrée Aline
			$allArchiveItemProperties[]=[]; 
			
			//On récupère la référence de ce nouvel élément
			$archiveItemProperties=&$allArchiveItemProperties[count($allArchiveItemProperties)-1];
			
			//Et on y ajoute les propriétés
			$this->hydrateProperties($archiveItemProperties, $archivePropertySchema, $row);
			
			//Enfin, on y ajoute les notes cachées comme média
			$this->hydrateMedias($archiveItemProperties, $row['nt']);
		}
		
		//Et on fusionne le tout pour remplir $itemDataList
		foreach ($alineValueRows as $rowNumber => $row){
			$properties = $allArchiveItemProperties[$rowNumber];
			if( !is_null($properties) )
				$archiveDataList[$row['id']] = array_merge($genericArchiveData, $properties);
		}
		
		$fileData=[];
		$archiveItems=$this->api->batchCreate('items', $archiveDataList, $fileData)->getContent();
		
		return count($archiveItems);
		// Old algorithm :
		// $item = new Item(); $item->setOwner() et autres setters...
		//For each vocabulary in the schema
			//get the vocabulary using
			// $this->adapterManager->get('vocabularies')->findEntity(['prefix'=>'bibo']);
			//
			//For each property associated to the voc in the schema
				// Get the property from the vocabulary or maybe we must create one
				// $property = new Property();
				// $property->setVocabulary($vocabulary);
				// OU
				//
				// /* @var $terms \Doctrine\Common\Collections\ArrayCollection */
				// $terms=$vocabulary->getProperties();
				//Voir comment on peut récupérer la propriété voulue, est-ce
				// que les termes sont les clés dans cette ArrayCollection ?
				//
				//Create a value and set value properties
				// $value= new Value();
				// $value->setValue("ma valeur"); //or other setter
				//
				//Link property to the value
				// $value->setProperty($property);
				//
				//Add the property-linked value to the item
				// $item->getProperties()->add($value);
    }
    

	/**
     * Insert an item using ApiManager.
     */
    private function insert() {
		$fileIndex=0;
		$data=[
			"o:resource_class"=>["o:id"=>40],
			"title"=> [[
				"type"=> "literal",
				"property_id"=> 1,
				"@value"=> "Nouvel Insert"
			]],
			"desc"=> [[
				"type"=> "literal",
				"property_id"=> 4,
				"@value"=> "Un super les livre sur les savants et les écrivains"
			]],
			"createur"=> [[
				"type"=> "literal",
				"property_id"=> 2,
				"@value"=> "C’est bien lui, c’est Henri !"
			],[
				"type"=> "resource",
				"property_id"=> 5,
				"value_resource_id"=> 6,
			],[
				"type"=> "resource",
				"property_id"=> 2,
				"value_resource_id"=> 6,
			],[
				"type"=> "uri",
				"property_id"=> 3,
				"@id"=> "https:\/\/google.com",
				"o:label"=> "Moteur de recherche Google"
			]],
		];
		
		if(isset($_FILES['monFichier'])) {
			$mediaData=[
				"o:media" =>[[
					"o:ingester" => "upload",
					"file_index" => $fileIndex,
				]],
			];
			$data = array_merge($data,$mediaData);
			$fileData=[
				'file'=>[
					$fileIndex => $_FILES['monFichier'],
				],
			];
		}
		else $fileData = [];
		
		return $this->api->create('items', $data, $fileData)->getContent();
	}
	/**
     * Get an item using ApiManager.
     * @param int $idItem
     */
    private function display(int $idItem) {
		/* @var $item ItemRepresentation */
		$item = $this->api->read('items', $idItem)->getContent();
		
		return json_encode($item);
    }
	
    public function affAction() {
        //Display an item
//		$content = $this->display(8);
		
		//Insert an item
//		$content=json_encode($this->insert());
		
		//Upload a file as Media
//		if(isset($_FILES['monFichier']))
//			$content = json_encode($this->attachMedia(38));
//		else
//			$content = $this->postFile();
		
		//Create an item and attach a newly uploaded media in the same time
//		if(isset($_FILES['monFichier']))
//			$content = json_encode($this->insert());
//		else
//			$content = $this->postFile();
		
		//Add a text as HTML media to an item
//		$content= json_encode($this->addHtmlMediaTo("Voilà mon texte !", 'mediaPrivé', 342));
		
		//Launch the specific archives table import algorithm
//		$total = $this->importArchives();
//		$content = "$total entrées semblent avoir été importées avec succès.";
		
		//Launch the generic import
		$this->import("archives");
		
		return new ViewModel([
			'content' => $content
		]);
    }

	private function getValuesFromAline($table, $subItem=NULL, $orderBy='id') {
//		switch ($table){
//			case 'archives':
//				$sql="SELECT * FROM archives ORDER BY $orderBy ASC";
//				break;
//		}
		$sql="SELECT * FROM $table ORDER BY $orderBy ASC";
		$statement=$this->pdo->query($sql);
		if($statement)
			$rows=$statement->fetchAll(\PDO::FETCH_ASSOC);
		else
			throw new \Exception(print_r($this->pdo->errorInfo()), true);
		
		return $rows;
	}

	/**
	 * Ajoute dans $properties les propriétés de $schema associées aux valeurs
	 * de $values.
	 * @param array $properties Tableau de propriétés à compléter.
	 * @param array $schemas
	 * @param array $values
	 */
	private function hydrateProperties(array $schemas, array $values) {
//		if( is_null($values) ) {
//			$properties=NULL;
//			return;
//		}
		$properties=[];
		foreach ($schemas as $term => $schema) { //Pour chaque propriété du schéma
			$data=$schema;
			switch ($data['type']){
				case 'uri':
					$data['@id']=$values[$schema['valueColumn']];
					break;
				
				case 'resource':
					$itemIdColumn=constant('self::'.strtoupper($schema['foreignTable']))
						[$schema['schemaIndex']] ['persist_column'];
					$data['value_resource_id']=$values[$itemIdColumn];
					break;
				
				default : //'literal' le + souvent
					$data['@value']= isset($schema['valueColumn'])
						? $values[$schema['valueColumn']]
						: substr($values['address'], 0, 40);
			}
			unset($data['valueColumn']);
			
			$properties[$term]=[$data];
		}
		return $properties;
	}

	/**
	 * Récupère les property_id réelles et les ajoute au schéma donné.
	 * @param array $propertySchemas Tableau associant à chaque terme les
	 * caractéristiques de sa définition.
	 */
	private function setSchemaPropertyIds(array &$propertySchemas) {
		foreach($propertySchemas as $term => &$propertySchema)
			$propertySchema['property_id']= $this->api
				->search('properties', ['term'=>$term])->getContent()[0]
				->id();
	}

	/**
	 * Récupère l’ensemble des tableaux JSON-LD définissant la classe, le modèle
	 * et la collection.
	 * @param array $itemSchema 
	 * @return array Liste de tableaux JSON-LD-compatible définissant
	 * la ResourceClass, le ResourceTemplate et l’ItemSet.
	 */
	private function getGenericData(array $itemSchema) {
		$genericData=[];
		
		//Resource class
		$classId = $this->api->search('resource_classes', ['term'=>$itemSchema['resource_class']])
				->getContent()[0]->id();
		$genericData['o:resource_class'] =[ 'o:id' => $classId ];
		
		//Resource template
		$templateId = $this->api->search('resource_templates', ['label'=>$itemSchema['resource_template']])
				->getContent()[0]->id();
		$genericData['o:resource_template'] = [ 'o:id' => $templateId ];
		
		//Item set
		$setId = $this->api->search( 'item_sets',
					['property'=>
						[[ 'eq' => [$itemSchema['item_set']] ]]
					]
				)->getContent()[0]->id();
		$genericData['o:item_set'] = [[ 'o:id' => $setId ]]; //Double tableau car il peut y avoir plusieurs item set
		
		return $genericData;
	}
	
	/**
	 * Récupère l’Id du modèle demandé et retourne un tableau JSON-LD
	 * compatible avec cet Id.
	 * @param string $label Intitulé du modèle d’item.
	 * @return array Tableau JSON-LD compatible définissant la ResourceClass.
	 */
	private function getResourceTemplateSchema(string $tableSchema, $key) {
		$label=$tableSchema['resource_templates'][$key];
		
		$id = $this->api->search('resource_templates', ['label'=>$label])
				->getContent()[0]->id();
		
		return [
			'o:id' => $id
		];
	}

	/**
	 * Passe à NULL les éléments de $valueRows qui, pour toutes les colonnes
	 * présentes dans $schema, sont vides.
	 * @param array $valueRows
	 * @param array $schema
	 * @return array Lignes en entrées moins celles sans informations.
	 */
	private function getCleanedRows(string $table, array $schema) {
		$valueRows=$this->getValuesFromAline($table);
		
		foreach ($valueRows as &$row){
			foreach($schema as $term => $propertySchema){
				if( isset($propertySchema['valueColumn']) 
						&& isset($row[$propertySchema['valueColumn']]) )
				{
					if( !empty($row[$propertySchema['valueColumn']]) ){
						
						continue 2; //On passe à la ligne suivante
					}
				}
			}
			$row=NULL;
		}
		return $valueRows;
	}

	/**
	 * Récupère depuis le schéma la colonne des notes cachées et l’ajoute au
	 * JSON-LD comme média s’il existe et n’est pas vide.
	 * @param array $properties Tableau de propriétés à compléter.
	 * @param array $mediaSchema Schéma des médias de l’item
	 * @param array $values Valeurs de la ligne de données.
	 */
	private function hydrateMedias(array $itemSchema, array $values) {
		if(!isset($itemSchema['medias'])) return [];
		$mediaSchema=$itemSchema['medias'];
		
		if(!isset($mediaSchema['privateNotesColumn'])) return [];
		
		$text= $values[$mediaSchema['privateNotesColumn']];
		
		if(empty($text)) return [];
		
		return ['o:media'=>
			[[
				"o:ingester" => "html",
				"o:is_public" => false,
				"html" => nl2br(htmlspecialchars($text)),
				"titre" => [[
					"type" => "literal",
					'property_id' => 1,
					'@value' => 'Remarques',
				]]
			]]
		];
	}

	/**
	 * Sauvegarde les Ids des ResourcesReference données.
	 * @param ResourceReference[] $itemReferences
	 * @param string $table
	 * @param string|null $column Colonne de la BDD qui contiendra les ids des items à mémoriser
	 */
	private function persistIds(array $itemReferences, string $table, $column, string $label) {
		if( is_null($column) )
			return; //Il n’est pas nécessaire de mémoriser les Ids de ces items
		
		$this->createColumnIfNotExist($table, $column, $label);
		
		foreach ($itemReferences as $key => $item) {
			$sql="UPDATE `$table` SET `$column`='{$item->id()}' WHERE id='$key'";
			
			if(false === $this->pdo->exec($sql))
				throw new Exception(print_r($this->pdo->errorInfo()), true);
		}
	}

	/**
	 * Vérifie si la colonne donnée existe dans la table donnée et l’ajoute
	 * si ce n’est pas le cas.
	 * @param string $table Table dans laquelle doit être la colonne.
	 * @param string $column Nom de la colonne dont on souhaite qu’elle existe.
	 * @param string $label Intitulé de l’item pour lequel la colonne va stocker
	 * les Ids.
	 * @return boolean Vrai si une table a été créé, faux sinon.
	 * @throws \Exception Problème de connexion avec PDO.
	 */
	private function createColumnIfNotExist(string $table, string $column, string $label) {
		$sqlTest="SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = 'aline' 
				AND TABLE_NAME = '$table'
				AND COLUMN_NAME = '$column'";
		$statementTest=$this->pdo->query($sqlTest);
		
		if(!$statementTest)
			throw new \Exception(print_r($this->pdo->errorInfo()), true);
		
		if(current($statementTest->fetch()) > 0) //Si la colonne existe déjà
			return false;
		
		//La colonne n’existe pas donc on la crée 
		$sqlAdd="ALTER TABLE `$table` ADD `$column` INT NULL COMMENT 'id des items $label dans Omeka'";
		$statementAdd=$this->pdo->query($sqlAdd);
		if(!$statementAdd)
			throw new \Exception(print_r($this->pdo->errorInfo()), true);
		
		return true;
	}

	/**
	 * On ajoute une colonne à $rows qui contiendra les Ids des items pour
	 * chaque propriété qui lie un autre item comme valeur.
	 * Cette fonction ne rajoute pas de colonne si les items proviennent de la
	 * même table car ce n’est pas nécessaire.
	 * @param array $rows Les données auxquelles on doit ajouter des colonnes.
	 * @param array $propertySchemas
	 * @param string $rowsTable Nom de la table d’où proviennent $rows.
	 */
	private function setLinkedItemIds(array &$rows, array $propertySchemas, string $rowsTable) {
		foreach ($propertySchemas as $term => $schema) { //Pour chaque propriété
			if( 'resource' === $schema['type'] //si la valeur est un autre item
					&& ($foreignTable = $schema['foreignTable']) !== $rowsTable ) //et que l’item ne vient pas de la même table
			{
				//Nom de la colonne contenant les Ids.
				$itemIdColumn=constant('self::'.strtoupper($foreignTable))
						[$schema['schemaIndex']] ['persist_column'];

				$sql="SELECT * FROM $foreignTable ORDER BY id ASC";
				$statement = $this->pdo->query($sql);
				$foreignRows = $statement
						->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);


				foreach ($rows as &$row) {
					//On récupère la ligne étrangère correspondant à notre $row
					$foreignRow = $foreignRows[ $row[ $schema['foreignKeyColumn'] ] ];

					//On crée la nouvelle colonne, copie de celle de la table étrangère
					$row[$itemIdColumn] = $foreignRow [$itemIdColumn];
				}
			}
		}
	}

}