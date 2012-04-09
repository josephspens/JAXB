<?php

	// turn the schema into a class file
	require_once('JAXB.class.php');
	$jaxb = new JAXB();
	$jaxb->parse('sample.xsd');
	// turn the xml document into an object
	$object = $jaxb->unmarshal('sample.xml');

	print_r($object);

	// helper function
	function hoth($str){
		echo "$str\n";
	}

?>