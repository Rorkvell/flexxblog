<?php

require_once('xmlrpc.class.php');

$postdata = file_get_contents("php://input");
header('Content-type: application/xml');
//print $postdata;

$response = new xmlrpcDocument();
$response->errorResponse(0, 'not implemented (yet)');
print $response->saveXML();

?>
