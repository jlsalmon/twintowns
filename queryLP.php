<?php
	/* Gets a lonely planet API request and returns resulting XML */

	// Only execute if correct params are passed
	require_once 'lib.php';
	
	if (isset($_GET['serviceurl'], $_GET['userpw'])) {
	
		// URL and username-password from _GET
		$url = $_GET['serviceurl'];
		$userpw = $_GET['userpw'];
		
		// Password base64 encoded
		$encodedpw = base64_encode($userpw);
		
		// HTTP header for http basic auth
		$cred = sprintf('Authorization: Basic %s', $encodedpw);
		
		// Creates the request
		$opts = array(
			'http' => array(
			'method' => 'GET',
			'proxy' => 'tcp://proxysg.uwe.ac.uk:8080',
			'request_fulluri' => true,
			'header' => $cred)
		);
		
		// Generates context for request
		$ctx = stream_context_create($opts);
		
		// Open and get response
		$handle = fopen ( $url, 'r', false,$ctx);
		echo stream_get_contents($handle);
	}
?>