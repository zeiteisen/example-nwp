<?php
/**
 * 		Simple Example which shows how to communicate with VIVA Network Publishing Server
 *
 *		1. Showing an HTML Form to let the user enter some value
 *		2. Prepares some values send the comminication via request and delivers the servers response 
 *
 */
 
 	include_once "class.FastTemplate.php";
	
 	$myt = new FastTemplate("layout");
	$myt->define( Array("main" => "vivaip.tpl") );	
	$myt->parse(MAIN, "main");
	$myt->FastPrint();
	
?>
