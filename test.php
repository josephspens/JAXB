<?php

	// turn the schema into a class file
	require_once('Schema.class.php');
	$schema = new Schema();
	$schema->parse('HW3.xsd');

	// turn the xml document into an object
	require_once('XML.class.php');
	$xml = new XML();
	$object = $xml->instantiate('hw3.xml');

	print_r($object);

	function hoth($str){
		echo "$str\n";
	}

?>