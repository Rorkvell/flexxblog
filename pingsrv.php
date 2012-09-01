<?php

require_once('xmlrpc.class.php');

$rc = false;
$requestDoc = new xmlrpcDocument();
$rc = $requestDoc->load();
if ($rc === false) {
	header('Content-type: text/plain');
	print "Error loading xmlrpc request";
} else {
	header('Content-type: application/xml');
	$func = $requestDoc->documentElement->nodeName;
	if (method_exists($requestDoc, $func)) {
		$responseDocument = $requestDoc->$func();
	} else {
		$responseDocument = new xmlrpcDocument();
		$responseDocument->errorResponse(0, 'not implemented');
	}
	print $responseDocument->saveXML();
}


?>
