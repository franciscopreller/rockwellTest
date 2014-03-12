<?php

// Please see the config file for any variables which may need changing
require 'config.inc.php';
require 'classes/TwitterAccess.class.php';
require 'classes/DataStore.class.php';

// Initialize failed response
$response = array('success' => false);

// Some simple checks before we continue...
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$data = json_decode(file_get_contents("php://input"), true);

	if (isset($data['handle']) && isset($data['count'])) {

		// Use twitter application-only-auth: https://dev.twitter.com/docs/auth/application-only-auth
		$consumerKey    = TWITTER_CONSUMER_KEY;
		$consumerSecret = TWITTER_CONSUMER_SECRET;

		// Sanitize data
		$handle = DataStore::sanitize($data['handle']);
		$count  = DataStore::sanitize($data['count']);

		$twitter  = new TwitterAccess($consumerKey, $consumerSecret);
		$statuses = $twitter->getStatusesFromTimeline($handle);
		$twitter->storeStatusesIntoDatabase($statuses);

		// Set response
		$response['success'] = true;

	}
}

// Send json response to front end
header('Content-Type: application/json');
echo json_encode($response);

?>