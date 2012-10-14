<?php 
	include_once("Zend/Dom/Query.php");
	include_once("lib/ELClass.php");

	$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

	$el = new EL();
	$el->getAllData($format);

