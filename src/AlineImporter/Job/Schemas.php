<?php

namespace AlineImporter\Job;

/**
 *
 * @author pols12
 */
interface Schemas {
const ARCHIVES=[
	'Adresse' => [
		'resource_class' => 'ahpo:Address', //classes, avec préfixe
		'resource_template' => 'Adresse', //Labels
		'item_set' => 'Adresses des lieux d’archives', //Labels
		'persist_column' => 'addressOId', //mettre à null une valeur s’il n’y a pas besoin de mémoriser l’Id
		'propertySchemas'=> [
			'dcterms:title'=>[ //valeur à générer automatiquement
				'type' => 'literal',
				'defaultValue' => 'Adresse de %s',
				'defaultValueColumns' => ['name']],
			'ahpo:fullAddress'=>[
				'type' => 'literal',
				'valueColumn' => 'address'],
			'ahpo:town' => [
				'type' => 'literal',
				'valueColumn' => 'city'],
			'ahpo:country' => [
				'type' => 'literal',
				'valueColumn' => 'nation'],
		],
	],
	'Lieu d’archives' => [
		'resource_class' => 'ahpo:ArchivePlace',
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
			'ahpo:name'=>[
				'type' => 'literal',
				'valueColumn' => 'name'],
			'ahpo:hasAddress'=>[
				'type' => 'resource',
				//La nom de la colonne contenant les Ids des items est
				// est contenu dans 'foreignTable'['schemaIndex']['persist_column']
				'foreignTable' => 'archives', //Table contenant les items schemaIndex
				'schemaIndex' => 'Adresse', //index du schéma des items
				
				//Si cette table et foreignTable ne sont pas la même table
				'targetIdColumn' => null, //Clé primaire de foreignTable
				'foreignKeyColumn' => null], //Colonne contenant la clé étrangère
			'ahpo:website'=>[
				'type' => 'uri',
				'valueColumn' => 'url'],
		]
	]
];
const CHP_AUTHOR=[
	'Volume'=>[
		'resource_class' => 'ahpo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Volumes de la correspondance',
		'persist_column' => 'volumeOId',
		'uniqueTerms' => ['ahpo:volumeNumber'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'La Correspondance entre Henri Poincaré et... – Volume %s',
				'defaultValueColumns' => ['vol']],
			'ahpo:volumeNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'vol'],
		],
	],
	'Chapitre'=>[ //uniquement si `chapter` != 0
		'resource_class' => 'ahpo:BookChapter',
		'resource_template' => 'Chapitre',
		'item_set' => 'Chapitres de la correspondance',
		'persist_column' => 'chapitreOId',
		'uniqueTerms' => ['ahpo:chapterNumber','ahpo:inBook'], //Terme contenant la colonne de référence pour éviter les doublons
		'dustValues' => [['0'],['0']], //Valeurs des colonnes de uniqueTerms équivalantes à NULL
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'chapterhead'],
			'ahpo:chapterNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'chapter'],
			'ahpo:inBook' => [ //volume
				'type' => 'resource',
				'foreignTable' => 'chp_author',
				'schemaIndex' => 'Volume'],
		],
	],
	'Pays' => [
		'resource_class' => 'ahpo:Address',
		'resource_template' => 'Adresse',
		'item_set' => 'Adresses des correspondants',
		'persist_column' => 'paysOId',
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'Adresse de %s',
				'defaultValueColumns' => ['chapterhead']],
			'ahpo:country' => [
				'type' => 'literal',
				'valueColumn' => 'nation'],
		],
	],
	'Auteur'=>[
		'resource_class' => 'ahpo:Person',
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
					'ahpo:language' => [
						'type' => 'literal',
						'valueColumn' => 'lang'],
					'ahpo:authoredBy' => [ //À reprendre après import pour le créer comme resource
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]], //['caractère de découpe', indices du tableau]
				'valueColumn' => 'name'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'name'],
			'ahpo:viafIdentifier' => [
				'type' => 'uri',
				'defaultValue' => 'http://viaf.org/viaf/%s',
				'defaultValueColumns' => ['viaf']],
			'ahpo:hasDedicatedChapter' => [
				'type' => 'resource',
				'foreignTable' => 'chp_author',
				'schemaIndex' => 'Chapitre'],
			'ahpo:liveAt' => [
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
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'valueColumn' => 'fn'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'ln'],
		],
	],
	'Auteur1'=>[ //À isoler avant !
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'a2_1'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'a2_1'],
		],
	],
	'Auteur2'=>[ //À isoler avant !
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'a2_2'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'a2_2'],
		],
	],
	'Rédacteur'=>[
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'valueColumn' => 'ef'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'el'],
		],
	],
	'Rédacteur1'=>[ //À isoler avant
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_1'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_1'],
		],
	],
	'Rédacteur2'=>[ //À isoler avant !
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_2'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_2'],
		],
	],
	'Rédacteur3'=>[ //À isoler avant !
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_3'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_3'],
		],
	],
	'Rédacteur4'=>[ //À isoler avant !
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_4'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_4'],
		],
	],
	'Rédacteur5'=>[ //À isoler avant !
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_5'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_5'],
		],
	],
	'Rédacteur6'=>[ //À isoler avant !
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_6'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_6'],
		],
	],
	'Rédacteur7'=>[ //À isoler avant !
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
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
			'ahpo:firstName' => [
				'type' => 'literal',
				'split' => [', ', [1]],
				'valueColumn' => 'e2_7'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'split' => [', ', [0]],
				'valueColumn' => 'e2_7'],
		],
	],
	'Ville d’édition'=>[
		'resource_class' => 'ahpo:Address',
		'resource_template' => 'Adresse',
		'item_set' => 'Adresses des éditeurs',
		'persist_column' => 'villeeditionOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'Adresse de %s',
				'defaultValueColumns' => ['pb']],
			'ahpo:town' => [
				'type' => 'literal',
				'valueColumn' => 'city'],
		],
	],
	'Éditeur'=>[
		'resource_class' => 'ahpo:Publisher',
		'resource_template' => 'Organisation',
		'item_set' => 'Éditeurs des publications de Poincaré',
		'persist_column' => 'editeurOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'pb'],
			'ahpo:locatedAt'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Ville d’édition'],
		],
	],
	'Journal'=>[
		'condition' => "bk IS NULL OR bk=''",
		'resource_class' => 'ahpo:Journal',
		'resource_template' => 'Journal',
		'item_set' => 'Revues ayant publié Poincaré',
		'persist_column' => 'journalOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'jo'],
			'ahpo:publishedBy'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Éditeur'],
		],
	],
	'Collection'=>[
		'condition' => "bk IS NOT NULL AND bk!=''",
		'resource_class' => 'ahpo:Collection',
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
		'resource_class' => 'ahpo:Issue',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Numéros des journaux ayant publié Poincaré',
		'persist_column' => 'numeroOId',
		'uniqueTerms' => ['ahpo:issueOf', 'ahpo:issueNumber', 'ahpo:volumeNumber'],
		'dustValues' => [[NULL, ''], [], [NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => '%s – Vol. %s, N ° %s',
				'defaultValueColumns' => ['jo', 'vo', 'no']],
			'ahpo:publicationDate'=>[
				'type' => 'literal',
				'valueColumn' => 'yr',
				'dustValues' => ['0000'],],
			'ahpo:volumeNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
			'ahpo:issueNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'no'],
			'ahpo:issueOf'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Journal'],
		],
	],
	'Ouvrage'=>[ //Ouvrage contenant des articles
		'condition' => "bk IS NOT NULL AND bk!='' AND art IS NOT NULL",
		'resource_class' => 'ahpo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Ouvrages de Poincaré',
		'persist_column' => 'ouvrageOId',
		'uniqueTerms' => ['dcterms:title','ahpo:publicationDate'],
		'dustValues' => [[NULL],[]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'bk'],
			'ahpo:volumeNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
			'ahpo:identifier'=>[
				'type' => 'literal',
				'valueColumn' => 'bibkey'],
			'ahpo:publicationDate'=>[
				'type' => 'literal',
				'valueColumn' => 'yr',
				'dustValues' => ['0000'],],
			'ahpo:inCollection'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Collection'],
			'ahpo:numberOfPages'=>[
				'type' => 'literal',
				'valueColumn' => 'pp'],
			'ahpo:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
			'ahpo:editedBy'=>[
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
			'ahpo:numberOfVolumes'=>[
				'type' => 'literal',
				'valueColumn' => 'vols'],
			'ahpo:editionNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'edn'],
			'ahpo:publishedBy'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Éditeur'],
		],
	],
	'Livre'=>[ // Livre ne contenant pas d’articles renseignés dans la BDD
		'condition' => "bk IS NOT NULL AND bk!='' AND art IS NULL",
		'resource_class' => 'ahpo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Ouvrages de Poincaré',
		'persist_column' => 'ouvrageOId',
		'uniqueTerms' => ['dcterms:title','ahpo:publicationDate'],
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
				'ingest' => 'text',
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
			'ahpo:volumeNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
			'ahpo:identifier'=>[
				'type' => 'literal',
				'valueColumn' => 'bibkey'],
			'ahpo:publicationDate'=>[
				'type' => 'literal',
				'valueColumn' => 'yr',
				'dustValues' => ['0000'],],
			'ahpo:authoredBy'=>[
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
			'ahpo:inCollection'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Collection'],
			'ahpo:numberOfPages'=>[
				'type' => 'literal',
				'valueColumn' => 'pp'],
			'ahpo:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
			'ahpo:editedBy'=>[
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
			'ahpo:fullTextOnlineAt'=>[
				'type' => 'uri',
				'valueColumn' => 'url'],
			'ahpo:numberOfVolumes'=>[
				'type' => 'literal',
				'valueColumn' => 'vols'],
			'ahpo:editionNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'edn'],
			'ahpo:publishedBy'=>[
				'type' => 'resource',
				'foreignTable' => 'hppb',
				'schemaIndex' => 'Éditeur'],
		],
	],
	'Article'=>[
		'condition' => "art IS NOT NULL",
		'resource_class' => 'ahpo:Article',
		'resource_template' => 'Article',
		'item_set' => 'Articles de Poincaré',
		'persist_column' => 'articleOId',
		'uniqueTerms' => ['ahpo:identifier'],
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
				'ingest' => 'text',
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
			'ahpo:identifier'=>[
				'type' => 'literal',
				'valueColumn' => 'bibkey'],
			'ahpo:authoredBy'=>[
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
			'ahpo:publishedIn'=>[
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
			'ahpo:startPage'=>[
				'type' => 'literal',
				'valueColumn' => 'pg'],
			'ahpo:endPage'=>[
				'type' => 'literal',
				'valueColumn' => 'pgend'],
			'ahpo:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
			'ahpo:fullTextOnlineAt'=>[
				'type' => 'uri',
				'valueColumn' => 'url'],
		],
	],
];
const CHPS=[
	'Volume'=>[
		'tryMerge' => true,
		'sameSet' => true, //rechercher les items avec lesquels fusionner uniquement dans le même item_set
		'resource_class' => 'ahpo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Volumes de la correspondance',
		'persist_column' => 'volumeOId',
		'uniqueTerms' => ['ahpo:volumeNumber'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'La Correspondance entre Henri Poincaré et... – Volume %s',
				'defaultValueColumns' => ['vo']],
			'ahpo:volumeNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
		],
	],
	'Chapitre'=>[ //uniquement si `chapter` != 0
		'tryMerge' => true,
		'sameSet' => true,
		'resource_class' => 'ahpo:BookChapter',
		'resource_template' => 'Chapitre',
		'item_set' => 'Chapitres de la correspondance',
		'persist_column' => 'chapitreOId',
		'uniqueTerms' => ['ahpo:chapterNumber','ahpo:inBook'],
		'dustValues' => [['0', NULL],['0']],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'split' => [', ', [1,0]],
				'valueColumn' => 'alpha'],
			'ahpo:chapterNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'chap'],
			'ahpo:inBook' => [ //volume
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Volume'],
		],
	],
	'Copyright'=>[
		'tryMerge' => true,
		'resource_class' => 'ahpo:Institution',
		'resource_template' => 'Organisation',
		'item_set' => 'Propriétaires de droit d’auteur',
		'persist_column' => 'copyrightOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'copyright'],
			'ahpo:name'=>[
				'type' => 'literal',
				'valueColumn' => 'copyright'],
		],
	],
	'Destination' => [
		'tryMerge' => true,
		'addProperties'=>['ahpo:town'], //Liste des propriétés à rajouter à l’ancien item lors d’une fusion
		'resource_class' => 'ahpo:Address',
		'resource_template' => 'Adresse',
		'item_set' => 'Lieux de destination des lettres',
		'persist_column' => 'destinationOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'Adresse de %2$s %1$s',
				'defaultValueColumns' => ['fndst', 'lndst']],
			'ahpo:town' => [
				'type' => 'literal',
				'valueColumn' => 'dstsite'],
		],
	],
	'Lieu' => [
		'resource_class' => 'ahpo:Address',
		'resource_template' => 'Adresse',
		'item_set' => 'Lieux d’expédition des lettres',
		'persist_column' => 'lieuOId',
		'uniqueTerms' => ['dcterms:title'], // peut-être à supprimer...
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => '%s',
				'defaultValueColumns' => ['expsite']],
			'ahpo:town' => [
				'type' => 'literal',
				'valueColumn' => 'expsite'],
		],
	],
	'Destinataire'=>[ //si bioid != 0
		'tryMerge' => true,
		'addProperties' => ['ahpo:liveAt'],
		'resource_class' => 'ahpo:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Correspondants d’Henri Poincaré',
		'persist_column' => 'destinataireOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title' => [
				'type' => 'literal',
				'defaultValue' => '%2$s %1$s',
				'defaultValueColumns' => ['fndst', 'lndst']],
			'ahpo:firstName' => [
				'type' => 'literal',
				'valueColumn' => 'fndst'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'lndst'],
			'ahpo:liveAt' => [
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Destination'],
		],
	],
	'Expéditeur'=>[ //si bioid != 0
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Correspondants d’Henri Poincaré',
		'persist_column' => 'expediteurOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title' => [
				'type' => 'literal',
				'defaultValue' => '%2$s %1$s',
				'defaultValueColumns' => ['fn', 'ln']],
			'ahpo:firstName' => [
				'type' => 'literal',
				'valueColumn' => 'fn'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'ln'],
		],
	],
	'Lettre' => [ //si doc=0
		'condition' => "doc=0",
		'resource_class' => 'ahpo:Letter',
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
				'ingest' => 'text',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => 'Transcription de la lettre',
						'defaultValueColumns' => []],
					'ahpo:language' => [
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
				'valueColumn' => 'ed',
				'ingest' => 'text',
				'transform' => [
					'ac' => 'André Coret',
					'am' => 'Amirouche Moktefi',
					'dr' => 'David Rowe',
					'eb' => 'Étienne Bolmont',
					'fb' => 'Frédéric Bréchenmacher',
					'gh' => 'Gerhard Heinzmann',
					'jg' => 'Jérémy Gray',
					'jm' => 'Jean Mawhin',
					'kr' => "kr\nRalf Krömer ?",
					'kv' => 'Klaus Volkert',
					'lr' => 'Laurent Rollet',
					'ms' => 'Martina Schiavon',
					'ob' => 'Olivier Bruneau',
					'ph' => 'Philippe Henry',
					'pn' => 'Philippe Nabonnand',
					'rk' => 'Ralf Krömer',
					'sw' => 'Scott A. Walter',
				],
				'propertySchemas' => [
					'dcterms:title'=>[
						'type' => 'literal',
						'defaultValue' => 'Méta-rédacteur de la lettre',
						'defaultValueColumns' => []],
				]
			]
		],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => '%s à %s, %s',
				'defaultValueColumns' => ['ln', 'lndst', 'date']],
			'ahpo:incipit'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'ahpo:writingDate'=>[
				[
				'type' => 'literal',
				'valueColumn' => 'date',
				'dustValues' => ['0000-00-00',
					'regex'=>['(\d{4})-00-00', '(\d{4}-\d{2})-00']],],
				[ //à revoir
				'type' => 'literal',
				'defaultValue' => '%s-%s-%s',
				'defaultValueColumns' => ['yr', 'mon', 'day'],
				'dustValues' => ['1950-00-00', '0000-00-00',
					'regex'=>['(\d{4})-00-00', '(\d{4}-\d{2})-00']],
				'duplicates' => ['date', 'datehi']],],
			'ahpo:latestPossibleWritingDate'=>[ //date au plus tard
				'type' => 'literal', //si !=date
				'valueColumn' => 'datehi',
				'dustValues' => ['1950-00-00', '0000-00-00',
					'regex'=>['(\d{4})-00-00', '(\d{4}-\d{2})-00']],
				'duplicates' => ['date']],
			'ahpo:publishedInReference'=>[ //Publication
				'type' => 'literal',
				'valueColumn' => 'pb'],
			'ahpo:publishedIn'=>[
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Chapitre'],
			'ahpo:identifierInLocalArchives'=>[
				'type' => 'literal',
				'defaultValue' => 'CD n° %s',
				'defaultValueColumns' => ['cd']],
			'ahpo:classificationNumber'=>[ //endroit dans les archives
				'type' => 'literal',
				'valueColumn' => 'docid'],
			'ahpo:archivedAt'=>[
				'type' => 'resource',
				'foreignTable' => 'archives',
				'schemaIndex' => 'Lieu d’archives',
				'foreignKeyColumn' => 'scid'],
			'ahpo:numberOfPages'=>[
				'type' => 'literal',
				'valueColumn' => 'pp'],
			'ahpo:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
			'ahpo:sectionNumber'=>[
				'type' => 'literal',
				'dustValues' => [0],
				'valueColumn' => 'sec'],
			'ahpo:references'=>[ //À revoir
				'type' => 'literal',
				'valueColumn' => 'xref'],
			'ahpo:documentType'=>[ //type de lettre
				['type' => 'literal',
				'valueColumn' => 'type',
				'@language' => 'en',
				'transform' => [
					'ADft' => 'Autograph draft',
					'ADftS' => 'Autograph draft signed',
					'AC' => 'Autograph postcard',
					'ACS' => 'Autograph postcard signed',
					'AL' => 'Autograph letter',
					'ALS' => 'Autograph letter signed',
					'ALSX' => 'ALS photocopy',
					'AD' => 'Autograph document',
					'ADS' => 'Autograph document signed',
					'PD' => 'Printed document',
					'PTrL' => 'Printed transcript of a letter',
					'TDS' => 'Typed document signed',
					'TL' => 'Typed letter',
					'TLS' => 'Typed letter signed',
					'TrL' => 'Transcript of a letter',
				]],
				['type' => 'literal',
				'valueColumn' => 'type',
				'@language' => 'fr',
				'transform' => [
					'ADft' => 'Brouillon autographe',
					'ADftS' => 'Brouillon autographe signé',
					'AC' => 'Carte postale',
					'ACS' => 'Carte postale signée',
					'AL' => 'Autographe lettre',
					'ALS' => 'Autographe lettre signée',
					'ALSX' => 'ALS photocopie',
					'AD' => 'Document autographe',
					'ADS' => 'Document autographe signé',
					'PD' => 'Document imprimé',
					'PTrL' => 'Transcription imprimée d’une lettre',
					'TDS' => 'Document dactylographié signé',
					'TL' => 'Lettre dactylographiée',
					'TLS' => 'Lettre dactylographiée signée',
					'TrL' => 'Transcription d’une lettre',
				]],
			],
			'ahpo:rightsHolder'=>[
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Copyright'],
//			'ahpo:repliesTo'=>[ //Voc peu adapté // Récursivité, à traiter à part...
//				'type' => 'resource',
//				'foreignTable' => 'chps',
//				'schemaIndex' => 'Lettre'],
//			'sioc:hasReply'=>[ //Voc peu adapté
//				'type' => 'resource',
//				'foreignTable' => 'chps',
//				'schemaIndex' => 'Lettre'],
			'ahpo:sentBy'=>[
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Expéditeur'],
			'ahpo:sentTo'=>[
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Destinataire'],
			'ahpo:writtenAt'=>[
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Lieu'],
			'ahpo:destinationAddress'=>[ //à améliorer
				'type' => 'resource',
				'foreignTable' => 'chps',
				'schemaIndex' => 'Destination'],
		]
	],
//	'Piege'=>[], // Pour éviter d’importer ce qu’il y a en dessous de cette ligne
//*/
/*	'Varia' => [ //À RENSEIGNER
	] */
];
const HPP_MISC= [
	'Volume'=>[
		'tryMerge' => true,
		'sameSet' => true, //rechercher les items avec lesquels fusionner uniquement dans le même item_set
		'resource_class' => 'ahpo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Volumes de la correspondance',
		'persist_column' => 'volumeOId',
		'uniqueTerms' => ['ahpo:volumeNumber'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'La Correspondance entre Henri Poincaré et... – Volume %s',
				'defaultValueColumns' => ['vo']],
			'ahpo:volumeNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
		],
	],
	'Commentaire'=>[ //Ouvrage contenant des articles
		'resource_class' => 'ahpo:Article',
		'resource_template' => 'Commentaire',
		'item_set' => 'Commentaires de l’édition de la correspondance',
		'persist_column' => 'commentaireOId',
		'uniqueTerms' => ['dcterms:title','ahpo:writingDate'],
		'medias' => [
			'Fichier' => [
				'public' => true,
				'fileNameColumn' => 'texfile',
				'ingest' => 'text',
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
			'ahpo:writingDate'=>[
				'type' => 'literal',
				'valueColumn' => 'date',
				'dustValues' => ['0000-00-00',
					'regex'=>['(\d{4})-00-00', '(\d{4}-\d{2})-00']],],
			'ahpo:inBook'=>[
				'type' => 'resource',
				'foreignTable' => 'hpp_misc',
				'schemaIndex' => 'Volume'],
			'ahpo:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
		],
	],
];
const HPRPTPHD = [
	'Volume'=>[
		'tryMerge' => true,
		'sameSet' => true, //rechercher les items avec lesquels fusionner uniquement dans le même item_set
		'resource_class' => 'ahpo:Book',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Volumes de la correspondance',
		'persist_column' => 'volumeOId',
		'uniqueTerms' => ['ahpo:volumeNumber'],
		'dustValues' => [[NULL]],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'defaultValue' => 'La Correspondance entre Henri Poincaré et... – Volume %s',
				'defaultValueColumns' => ['vo']],
			'ahpo:volumeNumber'=>[
				'type' => 'literal',
				'valueColumn' => 'vo'],
		],
	],
	'Candidat'=>[
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Doctorants',
		'persist_column' => 'doctorantOId',
		'uniqueTerms' => ['dcterms:title'],
		'medias' => [
			'Biographie'=>[
				'public' => true,
				'ingest' => 'text',
				'fileNameColumn' => 'biofile',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => 'Biographie de %2$s %1$s',
						'defaultValueColumns' => ['fncand', 'lncand']],
				],
			],
		],
		'propertySchemas'=> [
			'dcterms:title' => [
				'type' => 'literal',
				'defaultValue' => '%2$s %1$s',
				'defaultValueColumns' => ['fncand', 'lncand']],
			'ahpo:firstName' => [
				'type' => 'literal',
				'valueColumn' => 'fncand'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'lncand'],
		],
	],
	'Auteur'=>[
		'tryMerge' => true,
		'resource_class' => 'ahpo:Person',
		'resource_template' => 'Correspondant',
		'item_set' => 'Auteurs de rapports de thèse',
		'persist_column' => 'auteurOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title' => [
				'type' => 'literal',
				'defaultValue' => '%2$s %1$s',
				'defaultValueColumns' => ['fn', 'ln']],
			'ahpo:firstName' => [
				'type' => 'literal',
				'valueColumn' => 'fn'],
			'ahpo:familyName' => [
				'type' => 'literal',
				'valueColumn' => 'ln'],
		],
	],
	'Thèse' => [
		'resource_class' => 'ahpo:Thesis',
		'resource_template' => 'Ouvrage',
		'item_set' => 'Thèses',
		'persist_column' => 'theseOId',
		'uniqueTerms' => ['dcterms:title'],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'phdtitle'],
			'ahpo:authoredBy'=>[
				'type' => 'resource',
				'foreignTable' => 'hprptphd',
				'schemaIndex' => 'Candidat'],
			'ahpo:vivaDate'=>[
				'type' => 'literal',
				'valueColumn' => 'sdate',
				'dustValues' => ['0000-00-00',
					'regex'=>['(\d{4})-00-00', '(\d{4}-\d{2})-00']],],
			'ahpo:numberOfPages'=>[
				'type' => 'literal',
				'valueColumn' => 'pp',
				'dustValues' => [0]],
		]
	],
	'Rapport de thèse' => [
		'condition' => 'txtpub!=0',
		'resource_class' => 'ahpo:Report',
		'resource_template' => 'Lettre',
		'item_set' => 'Rapports de thèse',
		'persist_column' => 'rapportOId',
		'medias' => [
			'Transcription' => [
				'public' => true,
				'fileNameColumn' => 'texfile',
				'ingest' => 'text',
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => 'Transcription du rapport',
						'defaultValueColumns' => []],
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
			]
		],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'title'],
			'ahpo:incipit'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'ahpo:writingDate'=>[
				'type' => 'literal',
				'valueColumn' => 'date',
				'dustValues' => ['0000-00-00',
					'regex'=>['(\d{4})-00-00', '(\d{4}-\d{2})-00']],],
			'ahpo:publishedInReference'=>[ // Publication
				'type' => 'literal',
				'valueColumn' => 'pb'],
			'ahpo:inBook'=>[
				'type' => 'resource',
				'foreignTable' => 'hprptphd',
				'schemaIndex' => 'Volume'],
			'ahpo:classificationNumber'=>[ //endroit dans les archives
				'type' => 'literal',
				'valueColumn' => 'ident'],
			'ahpo:archivedAt'=>[
				'type' => 'resource',
				'foreignTable' => 'archives',
				'schemaIndex' => 'Lieu d’archives',
				'foreignKeyColumn' => 'scid'],
			'ahpo:language'=>[
				'type' => 'literal',
				'valueColumn' => 'lang'],
			'ahpo:identifier'=>[
				'type' => 'literal',
				'valueColumn' => 'bibkey'],
			'ahpo:documentType'=>[
				'type' => 'literal',
				'valueColumn' => 'type'],
			'ahpo:authoredBy'=>[
				'type' => 'resource',
				'foreignTable' => 'hprptphd',
				'schemaIndex' => 'Auteur'],
			'ahpo:isAbout'=>[
				'type' => 'resource',
				'foreignTable' => 'hprptphd',
				'schemaIndex' => 'Thèse'],
		]
	],
//expsite		='Paris' (ebucore:hasCreationLocation ? pour la thèse ou le rapport ?)
];
}
