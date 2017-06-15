<?php

namespace TestM\Controller;

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
				'defaultValueColumn' => 'address'],
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
			'privateNotesColumn' => 'nt', //Colonne contenant les notes et remarques privées
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
const CHPS=[
	'Lettre' => [ //À RENSEIGNER
		'resource_class' => 'bibo:Letter',
		'resource_template' => 'Lettre',
		'item_set' => 'Correspondance',
		'persist_column' => '',
		'medias' => [
			'images' =>[],
			'privateNotesColumn' => 'nt', //
		],
		'propertySchemas'=> [
			'dcterms:title'=>[
				'type' => 'literal',
				'valueColumn' => 'name'],
			'incipit'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'résolution de l’image'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'publication'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'nombre de pages'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'langue'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'section'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'chapitre'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'? xref'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'? ed'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'cd'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'type de lettre'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'? aip'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'endroit d’archive'=>[
				'type' => 'literal',
				'valueColumn' => 'opng'],
			'Lieu d’archive'=>[
				'type' => 'resource',
				'itemIdColumn' => '', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'archives', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'copyright'=>[
				'type' => 'resource',
				'itemIdColumn' => '', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chps', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'répond à '=>[
				'type' => 'resource',
				'itemIdColumn' => '', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chps', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'est la réponse de '=>[
				'type' => 'resource',
				'itemIdColumn' => '', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chps', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'volume'=>[
				'type' => 'resource',
				'itemIdColumn' => '', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chp_author', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'expediteur'=>[
				'type' => 'resource',
				'itemIdColumn' => '', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chp_author', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'destinataire'=>[
				'type' => 'resource',
				'itemIdColumn' => '', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chp_author', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'lieu d’expédition'=>[
				'type' => 'resource',
				'itemIdColumn' => 'Address', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chps', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'lieu de destination'=>[
				'type' => 'resource',
				'itemIdColumn' => 'Address', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chps', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'Date d’écriture'=>[
				'type' => 'resource',
				'itemIdColumn' => 'Address', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chps', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'Date2'=>[
				'type' => 'resource',
				'itemIdColumn' => 'Address', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chps', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'Date3'=>[
				'type' => 'resource',
				'itemIdColumn' => 'Address', //Colonne à dupliquer par jointure, contenant l’Id de l’item cible
				'targetTable' => 'chps', //Table contenant la colonne définie dans valueItem
				'foreignKeyColumn' => null, //Colonne contenant la clé étrangère
				'schemaIndex' => 0], //index du schéma de l’item cible
			'foaf:homepage'=>[
				'type' => 'uri',
				'valueColumn' => 'url'],
		]
	],
	'Varia' => [ //À RENSEIGNER
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
	]
];
}
