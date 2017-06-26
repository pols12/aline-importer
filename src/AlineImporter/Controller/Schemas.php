<?php

namespace AlineImporter\Controller;

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
				'valueColumn' => null,
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
			'dbo:viafId' => [
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
	'Auteur'=>[ //À isoler avant !
		'resource_class' => 'foaf:Person',
		'resource_template' => 'Auteur',
		'item_set' => 'Auteurs de publications',
		'persist_column' => 'auteurOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'defaultValue' => '%s %s',
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
		'resource_template' => 'Auteur',
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
		'resource_template' => 'Auteur',
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
		'resource_template' => 'Auteur',
		'item_set' => 'Rédacteurs de publications',
		'persist_column' => 'rédacteurOId',
		'uniqueTerms' => ['dcterms:title'],
		'dustValues' => [[NULL, '']],
		'propertySchemas'=> [
			'dcterms:title'=>[ //Prénom Nom
				'type' => 'literal',
				'defaultValue' => '%s %s',
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
		'resource_template' => 'Auteur',
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
		'resource_template' => 'Auteur',
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
		'resource_template' => 'Auteur',
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
		'resource_template' => 'Auteur',
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
		'resource_template' => 'Auteur',
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
		'resource_template' => 'Auteur',
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
		'resource_template' => 'Auteur',
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
	'Éditeur'=>[
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
	'Journal ou Collection'=>[
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
	'Auteur'=>[
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
	'Publication'=>[
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
];
const CHPS=[
	'Volume'=>[
		'tryMerge' => true,
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
				'ingestUrl' => true,
				'isImage' => true,
				'propertySchemas'=> [
					'dcterms:title' => [
						'type' => 'literal',
						'defaultValue' => 'Page ? sur %s de la lettre %s',
						'defaultValueColumns' => ['pp', 'title']],
					'exif:resolution'=>[
						'type' => 'literal',
						'valueColumn' => 'imgsrcdpi'],
				],
			],
			'Transcription' => [
				'public' => true,
				'fileNameColumn' => 'texfile',
				'ingestUrl' => true,
				'isImage' => false,
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
			'dcterms:date'=>[
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
}
