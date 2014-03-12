<?php

// Please see the config file for any variables which may need changing
require 'config.inc.php';
require 'classes/DataStore.class.php';

// Initialize failed response
$response = array('success' => false);

// Some simple checks before we continue...
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

	// Return all the data
	$data = DataStore::getAllRecords();

	// Set response
	$response['success'] = true;
	$response['data']    = $data;

}

// Send json response to front end
header('Content-Type: application/json');
echo json_encode(DataStore::utf8_encode_all($response));

?>