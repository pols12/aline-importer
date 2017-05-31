<?php
$archivePropertySchema=[
	'dcterms:title'=>[
		'type' => 'literal',
		'valueColumn' => 'name'],
	'locn:address'=>[
		'type' => 'resource',
		'valueItem' => 'AddressItem'],
	'foaf:homepage'=>[
		'type' => 'uri',
		'valueColumn' => 'url'],
];
$addressPropertySchema=[
	'dcterms:title'=>[ //valeur à générer automatiquement
		'type' => 'literal',
	],
	'locn:fullAddress'=>[
		'type' => 'literal',
		'valueColumn' => 'address'],
	'locn:postName' => [
		'type' => 'literal',
		'valueColumn' => 'city'],
	'locn:adminUnitL1' => [
		'type' => 'literal',
		'valueColumn' => 'nation'],
];