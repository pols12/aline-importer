<?php
namespace AlineImporter\Job;

use Omeka\Job\AbstractJob;
use Omeka\Api\Representation\ResourceReference;

/**
 * Description of Import
 *
 * @author pols12
 */
class Import extends AbstractJob implements \AlineImporter\Controller\Schemas {
	
	/* @var $pdo \PDO */
    protected $pdo;
    /* @var $api \Omeka\Api\Manager */
	protected $api;
	/* @var $logger \Zend\Log\Logger */
	protected $logger;
	
	protected $table;
	protected $tableSchema;


	public function perform() {
		//On prépare la connexion à la BDD Aline
		$this->pdo = new \PDO('mysql:dbname=aline;host=localhost','omeka','omeka');
		//On récupère le service API
		$this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
		//On récupère le service de journalisation
		$this->logger = $this->getServiceLocator()->get('Omeka\Logger');

		$this->logger-> log(0, "Services initialisés. Récupération de la table...");
		
		//On récupère le nom de la table à importer depuis les arguments
		$this->table = $this->getArg('table');
		if(! defined('self::'.strtoupper($this->table)) )
			throw new \Exception("La table `$this->table` n’a pas de schéma défini.");
		
		//On récupère le schéma de cette table
		$this->tableSchema=constant('self::'.strtoupper($this->table));
		
		$this->logger->log(0, "Schéma de la table {$this->table} récupéré.");
		
		$this->prepareTable();
		
		//Et on lance l’importation
		$this->import();
	}
	
	/**
     * Importe depuis Aline dans Omeka.
     * @return int Nombre d’items du dernier type insérés.
     */
    private function import() {
		foreach ($this->tableSchema as $itemSchema) {
			//Si la configuration n’est pas défini, on passe à l’item suivant
			if(!isset($itemSchema['item_set'])) continue;
			
			$this->logger->log(0,"Début de l’import des {$itemSchema['item_set']}...");
			
			//si le schéma définit une colonne unique, et des valeurs poubelle, on les précise
			$uniqueColumns = $this->getUniqueColumns($itemSchema);
			$dustValues = isset($itemSchema['dustValues']) ? $itemSchema['dustValues'] : [];
			
			//On récupère les données à importer (nettoyées)
			$valueRows = $this->getCleanedRows($itemSchema['propertySchemas'],
					$uniqueColumns, $dustValues);
			
			$this->logger->log(0, "Requête SELECT sur Aline finalisée : "
					.count($valueRows)." lignes récupérées.");
			
			//Définit la classe, le modèle et la collection
			$genericData = $this->getGenericData($itemSchema);
			
			//On prépare le schéma
			$this->setSchemaPropertyIds($itemSchema['propertySchemas']);
			
			//On traite les jointures
			$this->setLinkedItemIds($valueRows, $itemSchema['propertySchemas']);
			
			//On récupères les propriétés
			$allItemProperties=[];
			foreach ($valueRows as $row) {//Pour chaque entrée dans Aline
				$allItemProperties[] = NULL===$row ? NULL
						: array_merge(
							$this->getPropertiesArray($itemSchema['propertySchemas'], $row),
							$this->getMediasArray($itemSchema, $row)
						);
			}
			
			//Et on fusionne le tout pour remplir $itemDataList
			$itemDataList = []; //Tableau indexant les tableaux JSON-LD des items
			foreach ($valueRows as $row){
				$properties = current($allItemProperties);
				
				if( NULL !== $properties )
					$itemDataList[$row['unq']] = array_merge($genericData, $properties);
				
				next($allItemProperties);
			}
			
			//Tableau associant aux clés de $itemDataList les ResourceReference des items créés
			$itemReferences=$this->api->batchCreate('items', $itemDataList)->getContent();
			
			$this->logger->log(0,count($itemReferences)." items ont été créés.");
			
			//On stocke dans Aline les Ids des items que l’on vient de créer.
			$this->persistIds($itemReferences, $itemSchema['persist_column'],
					$itemSchema['item_set'], $uniqueColumns);
		}
		
		return count($itemReferences);
    }
	
	/**
	 * Effectue une requête SELECT sur la BDD pour y récupérer toutes les
	 * lignes, puis les retourne.
	 * @param array $unqColumns colonnes de regroupement des lignes de la BDD,
	 * empêchant les doublons sur cette colonne.
	 * @param string $orderBy colonne d’ordonnancement des lignes de la BDD.
	 * @return array Tableau de tableaux associatifs, un par ligne (noms de
	 * colonnes associées aux valeurs).
	 * @throws \Exception Problème de connexion à la BDD.
	 */
	private function getValuesFromAline(array $unqColumns=['id'], string $orderBy='id') {
		$sql="SELECT *, CONCAT(".implode(",',',", $unqColumns).") unq FROM {$this->table}";
		
		//Groupement pour empêcher les doublons
		$sql.=" GROUP BY unq";
		
		//Classement pour avoir toujours le même ordre
		$sql.=" ORDER BY $orderBy ASC";
		
		$statement=$this->pdo->query($sql);
		if($statement)
			$rows=$statement->fetchAll(\PDO::FETCH_ASSOC);
		else
			throw new \Exception(print_r($this->pdo->errorInfo()), true);
		
		//On indexe les lignes par la concaténation des colonnes uniques
		$unqIndexedRows=[];
		$map = function ($values) use(&$unqIndexedRows) {
			$unqIndexedRows[$values['unq']] = $values;
		};
		array_map($map, $rows);
		
		return $unqIndexedRows;
	}
	
	/**
	 * Retourne le JSON-LD des propriétés de $schema associées aux valeurs
	 * de $values.
	 * @param array $schemas
	 * @param array $values
	 * @return array Tableau JSON-LD compatible listant les propriétés et leurs
	 * valeurs.
	 */
	private function getPropertiesArray(array $schemas, array $values) {
		$properties=[];
		foreach ($schemas as $term => $schema) { //Pour chaque propriété du schéma
			$data=$schema;
			switch ($data['type']){
				case 'uri':
					$value=$this->getPropertyValue($schema, $values);
					//l’URI doit commencer par http
					$data['@id']='http' !== substr($value, 0, 4) && !empty($value)
							? "http://$value" : $value;
					break;
				
				case 'resource':
					$itemIdColumn=constant('self::'.strtoupper($schema['foreignTable']))
						[$schema['schemaIndex']] ['persist_column'];
					$data['value_resource_id']=$values[$itemIdColumn];
					break;
				
				default : //'literal' le + souvent
					$data['@value']= $this->getPropertyValue($schema, $values);
			}
			unset($data['valueColumn'], $data['defaultValue'], $data['defaultValueColumns']);
			
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
		$setId = $this->api->search( 'item_sets', ['property'=> [[
					'property' => 1, //dcterms:title
					'type' => 'eq',
					'text' => $itemSchema['item_set']
				]]
			])->getContent() [0]->id();
		$genericData['o:item_set'] = [[ 'o:id' => $setId ]]; //Double tableau car il peut y avoir plusieurs item set
		
		return $genericData;
	}
	
	/**
	 * Récupère les données d’Aline et passe à NULL les éléments qui, pour 
	 * toutes les colonnes présentes dans $schema, sont vides, ou bien dont la
	 * clé appartient à $dustValues.
	 * @param array $schema Schéma des propriétés
	 * @param array $uniqueColumns Colonnes où il ne doit pas y avoir de doublon.
	 * @param array $dustValues Liste des valeurs poubelles pour $uniqueColumn.
	 * @return array Lignes d’Aline moins celles sans informations.
	 */
	private function getCleanedRows(array $schema, array $uniqueColumns=['id'], array $dustValues=[]) {
		$valueRows=$this->getValuesFromAline($uniqueColumns);
		
		foreach ($valueRows as &$row){ //Pour chaque ligne
			
			//Si la valeur d’une colonne unique est dans $dustValues, l’entrée
			// devient NULL et on passe à la ligne suivante.
			if([]!==$dustValues) {
				foreach ($uniqueColumns as $i => $uniqueColumn) {
					if(in_array($row[$uniqueColumn], $dustValues[$i], true)){
						$row=NULL; 
						continue 2;
					}
				}
			}
			
			foreach($schema as $term => $propertySchema){ //Pour chaque propriété
				if( isset($propertySchema['valueColumn']) //Si une colonne est défini
						&& isset($row[$propertySchema['valueColumn']]) ) //et que cette colonne existe
				{
					if( !empty($row[$propertySchema['valueColumn']]) ){ //si la valeur n’est pas vide
						continue 2; //On passe à la ligne suivante
					}
				}
			}
			//Toutes les valeurs utiles sont vides
			$row=NULL; //Donc on remplace la ligne par NULL
		}
		return $valueRows;
	}
	
	/**
	 * Récupère depuis le schéma la colonne des notes cachées et retourne le
	 * JSON-LD correspondant au média s’il existe et n’est pas vide.
	 * Sinon, retourne un tableau vide.
	 * @param array $itemSchema Schéma des médias de l’item
	 * @param array $values Valeurs de la ligne de données.
	 * @return array Tableau JSON-LD-compatible spécifiant les médias liés à
	 * l’item.
	 */
	private function getMediasArray(array $itemSchema, array $values) {
		if(!isset($itemSchema['medias'])) return [];
		$mediaSchemas=$itemSchema['medias'];
		
		if(!is_array($mediaSchemas)) return [];
		
		$mediaData=[];
		foreach ($mediaSchemas as $schema) {
			$text = isset($schema['valueColumn']) //Si c’est du texte,
					? nl2br(htmlspecialchars($values[$schema['valueColumn']])) //on l’assainit.
					//Sinon c’est un nom de fichier,
					: $this->getFileContent($values[$schema['fileNameColumn']]); //on l’importe.
			
			if(empty($text)) continue;
			
			//On prépare le schéma des propriétés
			$this->setSchemaPropertyIds($schema['propertySchemas']);
			
			$mediaData[] = array_merge( [
						"o:ingester" => "html",
						"o:is_public" => $schema['public'],
						"html" => $text,
					],
					$this->getPropertiesArray($schema['propertySchemas'], $values) );
		}
		return ['o:media'=> $mediaData];
	}
	
	/**
	 * Sauvegarde les Ids des ResourcesReference données.
	 * @param ResourceReference[] $itemReferences
	 * @param string|null $column Colonne de la BDD qui contiendra les ids des items à mémoriser
	 */
	private function persistIds(array $itemReferences, $column, string $label,
			array $uniqueColumns=['id']) {
		if( is_null($column) )
			return; //Il n’est pas nécessaire de mémoriser les Ids de ces items
		
		$this->createColumnIfNotExist($column, $label);
		
		foreach ($itemReferences as $uniqueValuesStr => $item) {
			$uniqueValues = explode(",", $uniqueValuesStr);
			
			$sql="UPDATE `$this->table` SET `$column`='{$item->id()}' WHERE $uniqueColumns[0]='$uniqueValues[0]'";
			for($i=1; $i<count($uniqueValues); $i++)
				$sql.="AND $uniqueColumns[$i]='$uniqueValues[$i]'";
			
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
	private function createColumnIfNotExist(string $column, string $label,
			bool $fullLabel=false, string $type='INT') {
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
		$comment = $fullLabel ? $label : "id des items $label dans Omeka";
		$sqlAdd="ALTER TABLE `$this->table` ADD `$column` $type NULL COMMENT '$comment'";
		$statementAdd=$this->pdo->query($sqlAdd);
		if(!$statementAdd)
			throw new \Exception(print_r($this->pdo->errorInfo(), true));
		
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

	/**
	 * Donne le contenu du fichier référencé dans la table xmlfile d’Aline sous
	 * la clé $fileId.
	 * @param string|null $fileId Clé dans `xmlfile` (nom du fichier sans l’extension).
	 * @return string Contenu du fichier
	 * @throws \Exception Erreur de connexion PDO.
	 */
	private function getFileContent($fileId) {
		if(empty($fileId)) return;
		$sql="SELECT * FROM xmlfile WHERE file='$fileId'";
		$statement=$this->pdo->query($sql);
		if($statement)
			$row=$statement->fetch(\PDO::FETCH_ASSOC);
		else
			throw new \Exception(print_r($this->pdo->errorInfo()), true);
		
		$fileName="http://henripoincarepapers.univ-nantes.fr/{$row['url']}";
		
		return file_get_contents($fileName);
	}

	/**
	 * Donne la valeur de la propriété à partir de son schéma et des données de
	 * la BDD.
	 * @param array $schema Schéma de la propriété.
	 * @param array $values Ligne de la BDD.
	 * @return string Valeur de la propriété.
	 */
	private function getPropertyValue(array $schema, array $values) {
		$value = isset($schema['valueColumn']) //Si une colonne de la BDD contient l’info
			? $values[$schema['valueColumn']] //on l’assigne
			: vsprintf( $schema['defaultValue'], //Sinon on génère une valeur
					array_intersect_key($values,array_flip($schema['defaultValueColumns'])) );
		
		if( isset($schema['split']) ){ //Si la valeur contient d’autres infos non voulues
			$splittedValue = explode( $schema['split'][0], $value );
			$gluedValue = implode(" ", array_intersect_key($splittedValue, array_flip($schema['split'][1])));
			return trim($gluedValue);
		}
		
		return $value;
	}

	/**
	 * Donne les colonnes pour l’ensemble desquels il ne doit pas y avoir de
	 * doublon.
	 * @param array $itemSchema
	 * @return array Liste de colonnes formant une clé unique dans la BDD.
	 */
	private function getUniqueColumns(array $itemSchema) {
		//Si aucune colonne unique n’est définie, on utilise `id`
		if( !isset($itemSchema['uniqueTerms']) )
			return ['id'];
		
		$uniqueColumns=[];
		foreach ($itemSchema['uniqueTerms'] as $term) {
			$propertySchema=$itemSchema['propertySchemas'][$term];
			$uniqueColumns[] = isset($propertySchema['valueColumn'])
					? $propertySchema['valueColumn']
					: constant('self::'.strtoupper($propertySchema['foreignTable']))
						[$propertySchema['schemaIndex']] ['persist_column'] ;
		}
		return $uniqueColumns;
	}

	/**
	 * Effectue des opérations sur la table dans Aline, nécessaires avant
	 * son importation dans Omeka S.
	 */
	private function prepareTable() {
		switch ($this->table) {
		case 'hppb':
			//scinder la colonne a2 en a2_1 et a2_2
			if(! ($this->createColumnIfNotExist('a2_1', 'Coauteur1', true, 'VARCHAR(250)')
					&& $this->createColumnIfNotExist('a2_2', 'Coauteur2', true, 'VARCHAR(250)')) ) {
				
				$sqlSlct="SELECT id,a2 FROM `hppb`";
				$statementSlct = $this->pdo->query($sqlSlct);
				if(!$statementSlct) throw new \Exception(print_r($pdo->errorInfo(), true));
				
				$rows = $statementSlct->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE| \PDO::FETCH_ASSOC) ;
				
				$sqlUpd="UPDATE hppb SET a2_1=:name1, a2_2=:name2 WHERE id=:id";
				$statementUpd=$this->pdo->prepare($sqlUpd);
				
				foreach ($rows as $id => $row) {
					if(empty($row['a2'])) continue;
					
					$names=explode(' and ',$row['a2']);
					$name1=$names[0];
					$name2=isset($names[1]) ? $names[1] : NULL;
					
					$sqlVals=[':name1' => $name1,
						':name2' => $name2,
						'id' => $id];
					$this->logger->debug("id ${sqlVals['id']} prend la valeur {$sqlVals[':name1']} ");
					$statementUpd->execute($sqlVals);
					if(!$statementUpd) throw new \Exception(print_r($pdo->errorInfo(), true));
				}
			}
		}
	}
}
