<?php
namespace TestM\Job;

use Omeka\Job\AbstractJob;

/**
 * Description of Import
 *
 * @author pols12
 */
class Import extends AbstractJob implements \TestM\Controller\Schemas {
	
	/* @var $pdo \PDO */
    protected $pdo;
    /* @var $api \Omeka\Api\Manager */
	protected $api;
	
	protected $table;
	protected $tableSchema;


	public function perform() {
		//On prépare la connexion à la BDD Aline
		$this->pdo = new \PDO('mysql:dbname=aline;host=localhost','omeka','omeka');
		//On récupère le service API
		$this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
		
		//On récupère le nom de la table à importer depuis les arguments
		$this->table = $this->getArg('table');
		if(! defined('self::'.strtoupper($this->table)) )
			throw new \Exception("La table `$this->table` n’a pas de schéma défini.");
		
		//On récupère le schéma de cette table
		$this->tableSchema=constant('self::'.strtoupper($this->table));
		
		//Et on lance l’importation
		$this->import();
	}
	
	/**
     * Importe depuis Aline dans Omeka.
     * @return ItemRepresentation
     */
    private function import() {
		foreach ($this->tableSchema as $itemSchema) {
			$valueRows = $this->getCleanedRows($itemSchema['propertySchemas']);
			
			//Définit la classe, le modèle et la collection
			$genericData = $this->getGenericData($itemSchema);
			
			//Prepare schema
			$this->setSchemaPropertyIds($itemSchema['propertySchemas']);
			
			//On traite les jointures
			$this->setLinkedItemIds($valueRows, $itemSchema['propertySchemas']);
			
			//On récupères les propriétés
			$allItemProperties=[];
			foreach ($valueRows as $row) {//Pour chaque entrée dans Aline
				//Et on y ajoute les propriétés
				$allItemProperties[] = NULL===$row ? NULL
						: array_merge(
							$this->hydrateProperties($itemSchema['propertySchemas'], $row),
							$this->hydrateMedias($itemSchema, $row)
						);
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
			$this->persistIds($itemReferences,
					$itemSchema['persist_column'], $itemSchema['item_set']);
		}
		
		return count($itemReferences);
    }
	
	/**
	 * Effectue une requête SELECT sur la BDD pour y récupérer toutes les
	 * lignes, puis les retourne.
	 * @param type $orderBy colonne d’ordonnancement des lignes de la BDD.
	 * @return array Tableau de tableaux associatifs, un par ligne (noms de
	 * colonnes associées aux valeurs).
	 * @throws \Exception Problème de connexion à la BDD.
	 */
	private function getValuesFromAline($orderBy='id') {
		$sql="SELECT * FROM {$this->table} ORDER BY $orderBy ASC";
		$statement=$this->pdo->query($sql);
		if($statement)
			$rows=$statement->fetchAll(\PDO::FETCH_ASSOC);
		else
			throw new \Exception(print_r($this->pdo->errorInfo()), true);
		
		return $rows;
	}
	
	/**
	 * Retourne le JSON-LD des propriétés de $schema associées aux valeurs
	 * de $values.
	 * @param array $schemas
	 * @param array $values
	 * @return array Tableau JSON-LD compatible listant les propriétés et leurs
	 * valeurs.
	 */
	private function hydrateProperties(array $schemas, array $values) {
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
	 * Récupère les données d’Aline et passe à NULL les éléments qui, pour 
	 * toutes les colonnes présentes dans $schema, sont vides.
	 * @param array $schema
	 * @return array Lignes d’Aline moins celles sans informations.
	 */
	private function getCleanedRows(array $schema) {
		$valueRows=$this->getValuesFromAline();
		
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
	 * Récupère depuis le schéma la colonne des notes cachées et retourne le
	 * JSON-LD correspondant au média s’il existe et n’est pas vide.
	 * Sinon, retourne un tableau vide.
	 * @param array $itemSchema Schéma des médias de l’item
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
	 * @param string|null $column Colonne de la BDD qui contiendra les ids des items à mémoriser
	 */
	private function persistIds(array $itemReferences, $column, string $label) {
		if( is_null($column) )
			return; //Il n’est pas nécessaire de mémoriser les Ids de ces items
		
		$this->createColumnIfNotExist($column, $label);
		
		foreach ($itemReferences as $key => $item) {
			$sql="UPDATE `$this->table` SET `$column`='{$item->id()}' WHERE id='$key'";
			
			if(false === $this->pdo->exec($sql))
				throw new Exception(print_r($this->pdo->errorInfo()), true);
		}
	}
	
	/**
	 * Vérifie si la colonne donnée existe dans la table donnée et l’ajoute
	 * si ce n’est pas le cas.
	 * @param string $column Nom de la colonne dont on souhaite qu’elle existe.
	 * @param string $label Intitulé de l’item pour lequel la colonne va stocker
	 * les Ids.
	 * @return boolean Vrai si une table a été créé, faux sinon.
	 * @throws \Exception Problème de connexion avec PDO.
	 */
	private function createColumnIfNotExist(string $column, string $label) {
		$sqlTest="SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = 'aline' 
				AND TABLE_NAME = '$this->table'
				AND COLUMN_NAME = '$column'";
		$statementTest=$this->pdo->query($sqlTest);
		
		if(!$statementTest)
			throw new \Exception(print_r($this->pdo->errorInfo()), true);
		
		if(current($statementTest->fetch()) > 0) //Si la colonne existe déjà
			return false;
		
		//La colonne n’existe pas donc on la crée 
		$sqlAdd="ALTER TABLE `$this->table` ADD `$column` INT NULL COMMENT 'id des items $label dans Omeka'";
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
	 * @param string $this->table Nom de la table d’où proviennent $rows.
	 */
	private function setLinkedItemIds(array &$rows, array $propertySchemas) {
		foreach ($propertySchemas as $term => $schema) { //Pour chaque propriété
			if( 'resource' === $schema['type'] //si la valeur est un autre item
					&& ($foreignTable = $schema['foreignTable']) !== $this->table ) //et que l’item ne vient pas de la même table
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
