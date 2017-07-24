<?php
/**
 * 		Simple Example which shows how to communicate with VIVA Network Publishing Server
 *
 *		1. Showing an HTML Form to let the user enter some value
 *		2. Prepares some values send the comminication via request and delivers the servers response 
 *
 */
 
 /*
  * 	We use an external class to handle communication 
 */
 	require_once( 'http.inc' );
 
 /*
  * 	Define of some Variables
  * 
  */
 	
	$URLIPServer  = "/vivanwp-demo/vivanwp.php";
	
	$http_client = new http( HTTP_V11, false );
	$http_client->host = "213.239.208.66";
	$http_client->port = 80;		

/*
 * 	Handle the users input from the html form
 * 
 */

	$cardCompany 	= $_POST[Company];
	$cardFName 		= $_POST[FName];
	$cardLName 		= $_POST[LName];
	$cardFunktion 	= $_POST[Funktion]; 
	$cardTitel 		= $_POST[Titel]; 
	$cardEmail 		= $_POST[Email]; 
	$cardPhone 		= $_POST[Phone]; 
	$cardFax 		= $_POST[Fax]; 
	$cardZip 		= $_POST[Zip]; 
	$cardCity 		= $_POST[City]; 
	$cardWeb 		= $_POST[Web]; 
	$cardAdress		= $_POST[Adress]; 
	$cardCountry	= $_POST[Country]; 
	$cardState		= $_POST[State]; 

/*
 * 	Build a pseudo CSV Datasource
 * 
 */

	$CSV = "Company;Name;Surname;Phone;Fax;E-Mail;Position;Profession;ZIP;City;State;Country;Web;Language;Address\n".
			$cardCompany .";". $cardFName .";". $cardLName .";". $cardPhone .";". $cardFax .";". $cardEmail .";". $cardFunktion .";". $cardTitel .";". $cardZIP .";". $cardCity .";". $cardState .";". $cardCountry .";". $cardWeb .";English;". $cardAdress ;
		
	$map 	=	array( 	'language' => 'eng',
						'template_name' => "Template-Business-Card.vip",
						'outType' => "pdf",
						'cropMarks' => "true",
						'label' => "-",
						'sourceType' => "upload",
	);

/*
 * 	Build the "file" which will be send to vivanwp server
 * 
 */

	$files = array();
	$files[] = 	array(	'name' => 'File',
						'content-type' => 'application/x-gzip',
						'filename' => 'export.csv.gz',
						'data' => gzencode($CSV, 9),						
	);				

/*
 * 	Send the request and handle the response
 */		
	$http_client->multipart_post( $URLIPServer, $map, $files, false);
	$response = $http_client->get_response();
		
	if ($response->get_status() == HTTP_STATUS_OK ) {
		$responseBody = $http_client->get_response_body();
		echo $responseBody;
    }
	else if ($response->get_status() == HTTP_STATUS_MOVED_PERMANENTLY || $response->get_status() == HTTP_STATUS_FOUND || $response->get_status() == HTTP_STATUS_SEE_OTHER ) {
		if ( $response->get_header( 'Location' ) != null )
			relocate($response->get_header( 'Location' ));
		exit;
	}

/*
 * 	In case of something went wrong display an error page
 * 
 */
    else {
	 	include_once "class.FastTemplate.php";		
	 	$myt = new FastTemplate("layout");
		$myt->define( Array("main" => "error.tpl") );	
		$myt->parse(MAIN, "main");
		$myt->FastPrint();
	}
	unset( $http_client );
 	 	
	function relocate ($url) {
		global $SERVER_NAME;
		global $REQUEST_URI;
		
		if (preg_match ("/^\//", $url))							header ("Location: http://".$SERVER_NAME."$url");
		else if (preg_match ("/^[^\/]+:/", $url))				header ("Location: $url");
		else if (preg_match ("/\/$/", dirname($REQUEST_URI)))	header ("Location: http://".$SERVER_NAME.dirname($REQUEST_URI)."$url");
		else													header ("Location: http://".$SERVER_NAME.dirname($REQUEST_URI)."/$url");
	} 	
?>
