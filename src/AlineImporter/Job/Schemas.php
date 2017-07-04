<?php

namespace AlineImporter\Job;

/**
 *
 * @author pols12
 */
interface Schemas {
const ARCHIVES=[
	'Adresse' => [
		'resource_class' => 'locn:Address', //classes, avec préfixe
		'resource_template' => 'Adresse', //Labels
		'item_set' => 'Adresses des lieux d’archives', //Labels
		'persist_column' => 'addressOId', //mettre à null une valeur s’il n’y a pas besoin de mémoriser l’Id
		'propertySchemas'=> [
			'dcterms:title'=>[ //valeur à générer automatiquement
				'type' => 'literal',
				'defaultValue' => 'Adresse de %s',
				'defaultValueColumns' => ['name']],
			'locn:fullAddress'=>[
				'type' => 'literal',
				'valueColumn' => 'address'],
			'locn:postName' => [
				'type' => 'literal',
				'valueColumn' => 'city'],
			'locn:adminUnitL1' => [
				'type' => 'literal',
				'valueColumn' => 'nation'],
		],
	],
	'Lieu d’archives' => [
		'resource_class' => 'dcterms:Location',
		'resource_template' => 'Lieu d’archives',
		'item_set' => 'Lieux d’archives',
		'persist_column' => 'archiveOId',
		'medias' => [
			'Notes' => [
				'public' => false,
				'valueColumn' => 'nt', //Colonne contenant les notes et remarques privées
				'ingest' => 'text',
				'propertySchemas' => [
					'dcterms:title'=>[
						'type' => 'literal',
						'defaultValue' => 'Remarques sur %s',
						'defaultValueColumns' => ['name']],
				]
			]
		],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'name'],
			'locn:address'=>[
				'type' => 'resource',
				//La nom de la colonne contenant les Ids des items est
				// est contenu dans 'foreignTable'['schemaIndex']['persist_column']
				'foreignTable' => 'archives', //Table contenant les items schemaIndex
				'schemaIndex' => 'Adresse', //index du schéma des items
				
				//Si cette table et foreignTable ne sont pas la même table
				'targetIdColumn' => null, //Clé primaire de foreignTable
				'foreignKeyColumn' => null], //Colonne contenant la clé étrangère
			'foaf:homepage'=>[
				'type' => 'uri',
				'valueColumn' => 'url'],
		]
	]
];
const CHP_AUTHOR=[
	'Volume'=>[
		'resource_class' => 'bibo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Volumes de la correspondance',
		'persist_column' => 'volumeOId',
		'uniqueTerms' => ['bibo:volume'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'La Correspondance entre Henri Poincaré et... – Volume %s',
				'defaultValueColumns' => ['vol']],
			'bibo:volume'=>[
				'type' => 'literal',
				'valueColumn' => 'vol'],
		],
	],
	'Chapitre'=>[ //uniquement si `chapter` != 0
		'resource_class' => 'bibo:Chapter',
		'resource_template' => 'Chapitre',
		'item_set' => 'Chapitres de la correspondance',
		'persist_column' => 'chapitreOId',
		'uniqueTerms' => ['bibo:chapter','dcterms:isPartOf'], //Terme contenant la colonne de référence pour éviter les doublons
		'dustValues' => [['0'],['0']], //Valeurs des colonnes de uniqueTerms équivalantes à NULL
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'chapterhead'],
			'bibo:chapter'=>[
				'type' => 'literal',
				'valueColumn' => 'chapter'],
			'dcterms:isPartOf' => [ //volume
				'type' => 'resource',
				'foreignTable' => 'chp_author',
				'schemaIndex' => 'Volume'],
		],
	],
	'Pays' => [
		'resource_class' => 'locn:Address',
		'resource_template' => 'Adresse',
		'item_set' => 'Adresses des correspondants',
		'persist_column' => 'paysOId',
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'Adresse de %s',
				'defaultValueColumns' => ['chapterhead']],
			'locn:adminUnitL1' => [
				'type' => 'literal',
				'valueColumn' => 'nation'],
		],
	],
	'Auteur'=>[
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Correspondants d’Henri Poincaré',
		'persist_column' => 'auteurOId',
		'medias' => [
			'Biographie'=>[
				'public' => true,
				'ingest' => 'text',
				'fileNameColumn' => 'texbio',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => 'Biographie de %s',
						'defaultValueColumns' => ['chapterhead']],
					'dcterms:language' => [
						'type' => 'literal',
						'valueColumn' => 'lang'],
					'dcterms:creator' => [ //À reprendre après import pour le créer comme resource
						'type' => 'literal',
						'defaultValue' => 'Scott Walter',
						'defaultValueColumns' => []],
					],
			],
		],
		'propertySchemas'=> [
			'dcterms:title' => [
				'type' => 'literal',
				'valueColumn' => 'chapterhead'], //Poincaré à créer manuellement !
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]], //['caractère de découpe', indices du tableau]
				'valueColumn' => 'name'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'name'],
			'dcterms:identifier' => [
				'type' => 'uri',
				'defaultValue' => 'http://viaf.org/viaf/%s',
				'defaultValueColumns' => ['viaf']],
			'foaf:isPrimaryTopicOf' => [
				'type' => 'resource',
				'foreignTable' => 'chp_author',
				'schemaIndex' => 'Chapitre'],
			'locn:address' => [
				'type' => 'resource',
				'foreignTable' => 'chp_author',
				'schemaIndex' => 'Pays'],
		],
	],
//	'Piege' => [], //Le piège à importation !
];
const HPPB=[
	'Auteur'=>[
		'tryMerge' => true,
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Auteurs de publications',
		'persist_column' => 'auteurOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[], [NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'defaultValue' => '%2$s %1$s',
				'defaultValueColumns' => ['fn', 'ln']],
			'foaf:givenName' => [
				'type' => 'literal',
				'valueColumn' => 'fn'],
			'foaf:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'ln'],
		],
	],
	'Auteur1'=>[ //À isoler avant !
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Auteurs de publications',
		'persist_column' => 'auteur1OId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'defaultValue' => '%s',
				'split' => [', ', [1,0]],
				'defaultValueColumns' => ['a2_1']],
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'a2_1'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'a2_1'],
		],
	],
	'Auteur2'=>[ //À isoler avant !
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Auteurs de publications',
		'persist_column' => 'auteur2OId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'defaultValue' => '%s',
				'split' => [', ', [1,0]],
				'defaultValueColumns' => ['a2_2']],
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'a2_2'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'a2_2'],
		],
	],
	'Rédacteur'=>[
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Rédacteurs de publications',
		'persist_column' => 'rédacteurOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[], [NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'defaultValue' => '%2$s %1$s',
				'defaultValueColumns' => ['ef', 'el']],
			'foaf:givenName' => [
				'type' => 'literal',
				'valueColumn' => 'ef'],
			'foaf:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'el'],
		],
	],
	'Rédacteur1'=>[ //À isoler avant ! à dupliquer en 7 fois !!!
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Rédacteurs de publications',
		'persist_column' => 'rédacteur1OId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'split' => [', ', [1,0]],
				'valueColumn' => 'e2_1'],
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_1'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_1'],
		],
	],
	'Rédacteur2'=>[ //À isoler avant !
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Rédacteurs de publications',
		'persist_column' => 'rédacteur2OId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'split' => [', ', [1,0]],
				'valueColumn' => 'e2_2'],
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_2'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_2'],
		],
	],
	'Rédacteur3'=>[ //À isoler avant !
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Rédacteurs de publications',
		'persist_column' => 'rédacteur3OId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'split' => [', ', [1,0]],
				'valueColumn' => 'e2_3'],
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_3'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_3'],
		],
	],
	'Rédacteur4'=>[ //À isoler avant !
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Rédacteurs de publications',
		'persist_column' => 'rédacteur4OId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'split' => [', ', [1,0]],
				'valueColumn' => 'e2_4'],
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_4'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_4'],
		],
	],
	'Rédacteur5'=>[ //À isoler avant !
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Rédacteurs de publications',
		'persist_column' => 'rédacteur5OId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'split' => [', ', [1,0]],
				'valueColumn' => 'e2_5'],
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_5'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_5'],
		],
	],
	'Rédacteur6'=>[ //À isoler avant !
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Rédacteurs de publications',
		'persist_column' => 'rédacteur6OId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'split' => [', ', [1,0]],
				'valueColumn' => 'e2_6'],
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_6'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_6'],
		],
	],
	'Rédacteur7'=>[ //À isoler avant !
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Rédacteurs de publications',
		'persist_column' => 'rédacteur7OId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'split' => [', ', [1,0]],
				'valueColumn' => 'e2_7'],
			'foaf:givenName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_7'],
			'foaf:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_7'],
		],
	],
	'Ville d’édition'=>[
		'resource_class' => 'locn:Address',
		'resource_template' => 'Adresse',
		'item_set' => 'Adresses des éditeurs',
		'persist_column' => 'villeeditionOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'Adresse de %s',
				'defaultValueColumns' => ['pb']],
			'locn:postName' => [
				'type' => 'literal',
				'valueColumn' => 'city'],
		],
	],
	'Éditeur'=>[
		'resource_class' => 'foaf:Organization', //Peut faire mieux
		'resource_template' => 'Organisation',
		'item_set' => 'Éditeurs des publications de Poincaré',
		'persist_column' => 'editeurOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'pb'],
			'locn:address'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Ville d’édition'],
		],
	],
	'Journal'=>[
		'condition' => "bk IS NULL OR bk=''",
		'resource_class' => 'bibo:Journal',
		'resource_template' => 'Journal',
		'item_set' => 'Journaux ayant publié Poincaré',
		'persist_column' => 'journalOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'jo'],
			'dcterms:publisher'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Éditeur'],
		],
	],
	'Collection'=>[
		'condition' => "bk IS NOT NULL AND bk!=''",
		'resource_class' => 'bibo:Collection',
		'resource_template' => 'Collection',
		'item_set' => 'Collections des ouvrages de Poincaré',
		'persist_column' => 'collectionOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'jo'],
		],
	],
	'Numéro'=>[
		'condition' => "bk IS NULL OR bk=''",
		'resource_class' => 'bibo:Issue',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Numéros des journaux ayant publié Poincaré',
		'persist_column' => 'numeroOId',
		'uniqueTerms' => ['dcterms:isPartOf', 'bibo:issue', 'bibo:volume'],
		'dustValues' => [[NULL, ''], [], [NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => '%s – Vol. %s, N° %s',
				'defaultValueColumns' => ['jo', 'vo', 'no']],
			'dcterms:issued'=>[
				'type' => 'literal',
				'valueColumn' => 'yr'],
			'bibo:volume'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
			'bibo:issue'=>[
				'type' => 'literal',
				'valueColumn' => 'no'],
			'dcterms:isPartOf'=>[ //à revoir
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Journal'],
		],
	],
	'Ouvrage'=>[ //Ouvrage contenant des articles
		'condition' => "bk IS NOT NULL AND bk!='' AND art IS NOT NULL",
		'resource_class' => 'bibo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Ouvrages de Poincaré',
		'persist_column' => 'ouvrageOId',
		'uniqueTerms' => ['dcterms:title','dcterms:issued'],
		'dustValues' => [[NULL],[]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'bk'],
			'bibo:volume'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
			'dcterms:identifier'=>[
				'type' => 'literal',
				'valueColumn' => 'bibkey'],
			'dcterms:issued'=>[
				'type' => 'literal',
				'valueColumn' => 'yr'],
			'dcterms:isPartOf'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Collection'],
			'bibo:numPages'=>[
				'type' => 'literal',
				'valueColumn' => 'pp'],
			'dcterms:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
			'bibo:editor'=>[
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur1'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur2'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur3'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur4'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur5'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur6'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur7']],
			'bibo:numVolumes'=>[
				'type' => 'literal',
				'valueColumn' => 'vols'],
			'bibo:edition'=>[
				'type' => 'literal',
				'valueColumn' => 'edn'],
			'dcterms:publisher'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Éditeur'],
		],
	],
	'Livre'=>[ // Livre ne contenant pas d’articles renseignés dans la BDD
		'condition' => "bk IS NOT NULL AND bk!='' AND art IS NULL",
		'resource_class' => 'bibo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Ouvrages de Poincaré',
		'persist_column' => 'ouvrageOId',
		'uniqueTerms' => ['dcterms:title','dcterms:issued'],
		'dustValues' => [[NULL],[]],
		'medias' => [
			'PDF'=>[
				'public' => true,
				'fileNameColumn' => 'myrl',
				'ingest' => 'PDF',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => '%s (PDF)',
						'defaultValueColumns' => ['bk']],
				],
			],
			'HTML' => [
				'public' => true,
				'fileNameColumn' => 'myxml',
				'ingest' => 'HTML',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => '%s (HTML)',
						'defaultValueColumns' => ['bk']],
				],
			],
			'Notes' => [
				'public' => false,
				'valueColumn' => 'nt',
				'ingest' => 'text',
				'propertySchemas' => [
					'dcterms:title'=>[
						'type' => 'literal',
						'defaultValue' => 'Remarques',
						'defaultValueColumns' => []],
				]
			]
		],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'bk'],
			'bibo:volume'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
			'dcterms:identifier'=>[
				'type' => 'literal',
				'valueColumn' => 'bibkey'],
			'dcterms:issued'=>[
				'type' => 'literal',
				'valueColumn' => 'yr'],
			'dcterms:creator'=>[
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Auteur'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Auteur1'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Auteur2'],],
			'dcterms:isPartOf'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Collection'],
			'bibo:numPages'=>[
				'type' => 'literal',
				'valueColumn' => 'pp'],
			'dcterms:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
			'bibo:editor'=>[
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur1'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur2'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur3'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur4'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur5'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur6'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Rédacteur7']],
			'lv:fulltextOnline'=>[
				'type' => 'uri',
				'valueColumn' => 'url'],
			'bibo:numVolumes'=>[
				'type' => 'literal',
				'valueColumn' => 'vols'],
			'bibo:edition'=>[
				'type' => 'literal',
				'valueColumn' => 'edn'],
			'dcterms:publisher'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Éditeur'],
		],
	],
	'Article'=>[
		'condition' => "art IS NOT NULL",
		'resource_class' => 'bibo:Article',
		'resource_template' => 'Article',
		'item_set' => 'Articles de Poincaré',
		'persist_column' => 'articleOId',
		'uniqueTerms' => ['dcterms:identifier'],
		'dustValues' => [[NULL]],
		'medias' => [
			'PDF'=>[
				'public' => true,
				'fileNameColumn' => 'myrl',
				'ingest' => 'PDF',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => '%s (PDF)',
						'defaultValueColumns' => ['art']],
				],
			],
			'HTML' => [
				'public' => true,
				'fileNameColumn' => 'myxml',
				'ingest' => 'HTML',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => '%s (HTML)',
						'defaultValueColumns' => ['art']],
				],
			],
			'Notes' => [
				'public' => false,
				'valueColumn' => 'nt',
				'ingest' => 'text',
				'propertySchemas' => [
					'dcterms:title'=>[
						'type' => 'literal',
						'defaultValue' => 'Remarques',
						'defaultValueColumns' => []],
				]
			]
		],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'art'],
			'dcterms:identifier'=>[
				'type' => 'literal',
				'valueColumn' => 'bibkey'],
			'dcterms:creator'=>[
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Auteur'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Auteur1'],
				[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Auteur2'],],
			'dcterms:isPartOf'=>[
				[//soit c’est un article de journal
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Numéro'], //on a créé un item pour ce numéro de journal
				[
				'nullPropertyRequired' => 0, //cette propriété est ajoutée uniquement si la propriété d’index 0 est vide
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Journal'],
				[//soit c’est un article dans un ouvrage
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Ouvrage']], 
			'bibo:pageStart'=>[
				'type' => 'literal',
				'valueColumn' => 'pg'],
			'bibo:pageEnd'=>[
				'type' => 'literal',
				'valueColumn' => 'pgend'],
			'dcterms:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
			'lv:fulltextOnline'=>[
				'type' => 'uri',
				'valueColumn' => 'url'],
		],
	],
];
const CHPS=[
	'Volume'=>[
		'tryMerge' => true,
		'sameSet' => true, //rechercher les items avec lesquels fusionner uniquement dans le même item_set
		'resource_class' => 'bibo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Volumes de la correspondance',
		'persist_column' => 'volumeOId',
		'uniqueTerms' => ['bibo:volume'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'La Correspondance entre Henri Poincaré et... – Volume %s',
				'defaultValueColumns' => ['vo']],
			'bibo:volume'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
		],
	],
	'Chapitre'=>[ //uniquement si `chapter` != 0
		'tryMerge' => true,
		'resource_class' => 'bibo:Chapter',
		'resource_template' => 'Chapitre',
		'item_set' => 'Chapitres de la correspondance',
		'persist_column' => 'chapitreOId',
		'uniqueTerms' => ['bibo:chapter','dcterms:isPartOf'],
		'dustValues' => [['0', NULL],['0']],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'split' => [', ', [1,0]],
				'valueColumn' => 'alpha'],
			'bibo:chapter'=>[
				'type' => 'literal',
				'valueColumn' => 'chap'],
			'dcterms:isPartOf' => [ //volume
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Volume'],
		],
	],
	'Copyright'=>[
		'tryMerge' => true,
		'resource_class' => 'foaf:Organization',
		'resource_template' => 'Organisation',
		'item_set' => 'Propriétaires de droit d’auteur',
		'persist_column' => 'copyrightOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'copyright'],
		],
	],
	'Destination' => [
		'tryMerge' => true,
		'resource_class' => 'locn:Address',
		'resource_template' => 'Adresse',
		'item_set' => 'Lieux de destination des lettres',
		'persist_column' => 'destinationOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'Adresse de %s %s',
				'defaultValueColumns' => ['fndst', 'lndst']],
			'locn:postName' => [
				'type' => 'literal',
				'valueColumn' => 'dstsite'],
		],
	],
	'Lieu' => [
		'resource_class' => 'locn:Address',
		'resource_template' => 'Adresse',
		'item_set' => 'Lieux d’expédition des lettres',
		'persist_column' => 'lieuOId',
		'uniqueTerms' => ['dcterms:title'], // peut-être à supprimer...
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'Commune de %s',
				'defaultValueColumns' => ['dstsite']],
			'locn:postName' => [
				'type' => 'literal',
				'valueColumn' => 'dstsite'],
		],
	],
	'Destinataire'=>[ //si bioid != 0
		'tryMerge' => true,
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Correspondants d’Henri Poincaré',
		'persist_column' => 'destinataireOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title' => [
				'type' => 'literal',
				'defaultValue' => '%2$s %1$s',
				'defaultValueColumns' => ['fndst', 'lndst']],
			'foaf:givenName' => [
				'type' => 'literal',
				'valueColumn' => 'fndst'],
			'foaf:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'lndst'],
			'locn:address' => [
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Destination'],
		],
	],
	'Expéditeur'=>[ //si bioid != 0
		'tryMerge' => true,
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Correspondants d’Henri Poincaré',
		'persist_column' => 'expediteurOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title' => [
				'type' => 'literal',
				'defaultValue' => '%2$s %1$s',
				'defaultValueColumns' => ['fn', 'ln']],
			'foaf:givenName' => [
				'type' => 'literal',
				'valueColumn' => 'fn'],
			'foaf:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'ln'],
		],
	],
	'Lettre' => [ //si doc=0
		'resource_class' => 'bibo:Letter',
		'resource_template' => 'Lettre',
		'item_set' => 'Correspondance',
		'persist_column' => 'lettreOId',
		'uniqueColumn' => 'rec',
		'medias' => [
			'Scan'=>[
				'public' => true,
				'fileNameColumn' => 'texfile',
				'ingest' => 'images',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => 'Lettre %s (?1/?t)',
						'defaultValueColumns' => ['title']],
					'exif:resolution'=>[
						'type' => 'literal',
						'valueColumn' => 'imgsrcdpi'],
				],
			],
			'Transcription' => [
				'public' => true,
				'fileNameColumn' => 'texfile',
				'ingest' => 'HTML',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => 'Transcription de la lettre',
						'defaultValueColumns' => []],
					'dcterms:language' => [
						'type' => 'literal',
						'valueColumn' => 'lang'],
				],
			],
			'Notes' => [
				'public' => false,
				'valueColumn' => 'nt',
				'ingest' => 'text',
				'propertySchemas' => [
					'dcterms:title'=>[
						'type' => 'literal',
						'defaultValue' => 'Remarques sur le document',
						'defaultValueColumns' => []],
				]
			],
			'Éditeur' => [
				'public' => false,
				'valueColumn' => 'ed', //Valeur à transformer
				'ingest' => 'text',
				'propertySchemas' => [
					'dcterms:title'=>[
						'type' => 'literal',
						'defaultValue' => 'Éditeur de la lettre',
						'defaultValueColumns' => []],
				]
			]
		],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'title'],
			'dm2e:incipit'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'dcterms:created'=>[
				[
				'type' => 'literal',
				'valueColumn' => 'date'],
				[ //date au plus tard, à revoir
				'type' => 'literal', //si !=date
				'valueColumn' => 'datehi',
				'dustValues' => ['1950-00-00'],
				'duplicates' => ['date']],
				[ //à revoir
				'type' => 'literal',
				'defaultValue' => '%s-%s-%s',
				'defaultValueColumns' => ['yr', 'mon', 'day'],
				'duplicates' => ['date', 'datehi']],],
			'dcterms:isPartOf'=>[
				[ //Publication, à revoir
				'type' => 'literal',
				'valueColumn' => 'pb'],
				[
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Chapitre'],
				[
				'type' => 'literal',
				'defaultValue' => 'CD n° %s',
				'defaultValueColumns' => ['cd']],
				[ //endroit dans les archives, à revoir
				'type' => 'literal',
				'valueColumn' => 'docid'],
				[ //à revoir, curate=conserver
				'type' => 'resource',
				'foreignTable' => 'archives',
				'schemaIndex' => 'Lieu d’archives',
				'foreignKeyColumn' => 'scid'],],
			'bibo:numPages'=>[
				'type' => 'literal',
				'valueColumn' => 'pp'],
			'dcterms:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
			'bibo:section'=>[
				'type' => 'literal',
				'dustValues' => [0],
				'valueColumn' => 'sec'],
			'dcterms:references'=>[ //À revoir
				'type' => 'literal',
				'valueColumn' => 'xref'],
			'dcterms:type'=>[ //type de lettre, à revoir
				'type' => 'literal',
				'valueColumn' => 'type'],
			'dcterms:rightsHolder'=>[
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Copyright'],
//			'sioc:reply_of'=>[ //Voc peu adapté // Récursivité, à traiter à part...
//				'type' => 'resource',
//				'foreignTable' => 'chps',
//				'schemaIndex' => 'Lettre'],
//			'sioc:has_reply'=>[ //Voc peu adapté
//				'type' => 'resource',
//				'foreignTable' => 'chps',
//				'schemaIndex' => 'Lettre'],
			'dm2e:writer'=>[
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Expéditeur'],
			'gndo:addressee'=>[
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Destinataire'],
			'ebucore:hasCreationLocation'=>[ //à améliorer
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Lieu'],
			'locn:address'=>[ //à améliorer
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Destination'],
		]
	],
//	'Piege'=>[], // Pour éviter d’importer ce qu’il y a en dessous de cette ligne
//*/
/*	'Varia' => [ //À RENSEIGNER
		'resource_class' => 'dcterms:Location',
		'resource_template' => 'Lieu d’archives',
		'item_set' => 'Lieux d’archives',
		'persist_column' => 'archiveOId',
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'name'],
			'locn:address'=>[
				'type' => 'resource',
				'itemIdColumn' => 'AddressItem',
				'targetTable' => 'archives',
				'foreignKeyColumn' => null,
				'schemaIndex' => 0],
			'foaf:homepage'=>[
				'type' => 'uri',
				'valueColumn' => 'url'],
		]
	] */
];
const HPP_MISC= [
	'Volume'=>[
		'tryMerge' => true,
		'sameSet' => true, //rechercher les items avec lesquels fusionner uniquement dans le même item_set
		'resource_class' => 'bibo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Volumes de la correspondance',
		'persist_column' => 'volumeOId',
		'uniqueTerms' => ['bibo:volume'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'La Correspondance entre Henri Poincaré et... – Volume %s',
				'defaultValueColumns' => ['vo']],
			'bibo:volume'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
		],
	],
	'Commentaire'=>[ //Ouvrage contenant des articles
		'resource_class' => 'bibo:Article',
		'resource_template' => 'Commentaire',
		'item_set' => 'Commentaires de l’édition de la correspondance',
		'persist_column' => 'commentaireOId',
		'uniqueTerms' => ['dcterms:title','dcterms:created'],
		'medias' => [
			'Fichier' => [
				'public' => true,
				'fileNameColumn' => 'texfile',
				'ingest' => 'HTML',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => '%s (HTML)',
						'defaultValueColumns' => ['title']],
				],
			],
			'Auteur' => [
				'public' => false,
				'valueColumn' => 'author',
				'ingest' => 'text',
				'propertySchemas' => [
					'dcterms:title'=>[
						'type' => 'literal',
						'defaultValue' => 'Auteur du fichier',
						'defaultValueColumns' => []],
				]
			]
		],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'title'],
			'dcterms:created'=>[
				'type' => 'literal',
				'valueColumn' => 'date'],
			'dcterms:isPartOf'=>[
				'type' => 'resource',
				'foreignTable' => 'hpp_misc',
				'schemaIndex' => 'Volume'],
			'dcterms:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
		],
	],
];
}
