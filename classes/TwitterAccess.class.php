<?php

require_once 'DAL.class.php';

/**
 * TwitterAccess wrapper class
 * ---------------------------
 * 
 * Makes use of the Twitter API v 1.1 to retrieve data from their servers.
 * A note on searching timelines: Twitter does not keep indexes on tweets from
 * the beginning of time and since we are using the search method from the API
 * as opposed to signing in with Twitter, only the most recent tweets will display
 * 
 * Written by: Francisco Preller <francisco.preller@gmail.com>
 *
 * Usage:
 * ------
 * $twitter  = new TwitterAccess($consumerKey, $consumerSecret);
 * $statuses = $twitter->getStatusesFromTimeline($handle);
 * 
 */
class TwitterAccess {

	// ==================================================================
	//
	// PROPERTIES
	//
	// ------------------------------------------------------------------
	
	private $consumerKey;
	private $consumerSecret;
	private $bearerToken;

	/**
	 * Gets the consumer key
	 * @return string The twitter api oAuth v1.0 consumer key
	 */
	public function getConsumerKey() {
	    return $this->consumerKey;
	}
	
	/**
	 * Sets the consumer key and encodes it
	 * @param String $newconsumerKey The twitter api oAuth v1.0 consumer key
	 */
	public function setConsumerKey($consumerKey) {
	    $this->consumerKey = rawurlencode($consumerKey);
	}

	/**
	 * Gets the consumer secret
	 * @return string The twitter api oAuth 1.0 consumer secret
	 */
	public function getConsumerSecret() {
	    return $this->consumerSecret;
	}
	
	/**
	 * Sets the consumer secret and encodes it
	 * @param String $newconsumerSecret The twitter api oAuth 1.0 consumer secret
	 */
	public function setConsumerSecret($consumerSecret) {
	    $this->consumerSecret = rawurlencode($consumerSecret);
	}


	/**
	 * Gets the bearer token returned from twitter API
	 * @return string The twitter API bearer token for the application
	 */
	public function getBearerToken() {
	    return $this->bearerToken;
	}
	
	/**
	 * Sets the bearer token returned from twitter API
	 * @param String $newbearerToken The twitter API bearer token for the application
	 */
	public function setBearerToken($bearerToken) {
	    $this->bearerToken = $bearerToken;
	}

	// ==================================================================
	//
	// METHODS
	//
	// ------------------------------------------------------------------
	
	/**
	 * Constructor must initialize using consumer key & secret provided by Twitter API
	 * @param string $consumerKey    The consumer key
	 * @param string $consumerSecret The consumer secret
	 */
	public function __construct($consumerKey, $consumerSecret) {

		$this->setConsumerKey($consumerKey);
		$this->setConsumerSecret($consumerSecret);

		// Get & set the bearer token
		$this->setBearerToken($this->requestBearerToken());

	}

	/**
	 * Gets the last $count number of tweets from $handle's twitter timeline
	 * @param  string  $handle Twitter handle to get results from
	 * @param  integer $count  Number of tweets to return
	 * @return object          Object containing the response from the twitter API
	 */
	public function getStatusesFromTimeline($handle, $count = 10) {

		// Set REST endpoint url
		$restEndpointUrl = "https://api.twitter.com/1.1/search/tweets.json";

		// Create simple search query
		// More on twitter searching: https://dev.twitter.com/docs/api/1.1/get/search/tweets
		$queryString = "?q=from%3A{$handle}&count={$count}";

		$context = stream_context_create(array(
		 	"http"   => array(
		 		"method"  => "GET",
		    	"header"  => "GET /1.1/search/tweets.json{$queryString} HTTP/1.1\r\n" .
		    				 "Host: api.twitter.com\r\n" .
		    			  	 "User-Agent: Rockwell Test App\r\n" .
		    			 	 "Authorization: Bearer " . $this->getBearerToken() . "\r\n" .
		    				 "Content-Type: application/x-www-form-urlencoded;charset=UTF-8\r\n"
		    )
		));

		// Execute the API call using the created headers
		// Supress error here since a 403 error results ina  warning and cannot be caught with tryCatch
		$endpointUrl = $restEndpointUrl . $queryString;
		$response    = @file_get_contents($endpointUrl, false, $context);

		// Return JSON response
		return json_decode($response);
	}

	/**
	 * Stores any new users or status into the database
	 * @param  object $statuses Twitter API's statuses object
	 * @return void
	 */
	public function storeStatusesIntoDatabase($statuses) {

		// Iterate over each status
		foreach ($statuses->statuses as $status) {

			// First update (or insert) the user's followers/friends/statuses counts
			if (!$this->checkIfUserExists($status->user->id_str))
				$this->insertTwitterUser($status->user);
			else
				$this->updateTwitterUser($status->user);

			// Insert tweet details as long as we've not inserted those into tweet yet
			if (!$this->checkIfStatusExists($status->id_str))
				$this->insertTwitterStatus($status);

		}

	}

	/**
	 * Inserts a new twitter status into the database
	 * @param  object $status Twitter API's status object
	 * @return boolean        True if successfully inserted, otherwise false
	 */
	private function insertTwitterStatus($status) {

		$dal = new DAL();

		try {
			$query  = "
				INSERT INTO status (id, userId, text, source, createdAt)
				     VALUES (:id, :userId, :text, :source, :createdAt)
			";
			$params = array(
				array('name' => 'id', 'value' => $status->id_str, 'type' => PDO::PARAM_INT),
				array('name' => 'userId', 'value' => $status->user->id_str, 'type' => PDO::PARAM_INT),
				array('name' => 'text', 'value' => $status->text, 'type' => PDO::PARAM_STR),
				array('name' => 'source', 'value' => $status->source, 'type' => PDO::PARAM_STR),
				array('name' => 'createdAt', 'value' => date('Y-m-d H:i:s', strtotime($status->created_at)), 'type' => PDO::PARAM_STR),
			);

			// Execute the query
			$result = $dal->executeNonQuery($query, $params);

			return $result;
		}
		catch(Exception $ex) {

			$dal->LogException($e, 'Could not insert new status', 'error');
			return false;

		}
	}

	/**
	 * Updates an existing twitter user in the database
	 * @param  object $user Twitter API user object
	 * @return boolean      True is successfully updated, false otherwise
	 */
	private function updateTwitterUser($user) {

		$dal = new DAL();

		try {
			$query  = "
				UPDATE 	user
				   SET 	followersCount = :followersCount,
				   		friendsCount   = :friendsCount,
				   		statusesCount  = :statusesCount
				 WHERE  id = :id
			";
			$params = array(
				array('name' => 'id', 'value' => $user->id_str, 'type' => PDO::PARAM_INT),
				array('name' => 'followersCount', 'value' => $user->followers_count, 'type' => PDO::PARAM_INT),
				array('name' => 'friendsCount', 'value' => $user->friends_count, 'type' => PDO::PARAM_INT),
				array('name' => 'statusesCount', 'value' => $user->statuses_count, 'type' => PDO::PARAM_INT)
			);

			// Execute the query
			$result = $dal->executeNonQuery($query, $params);

			return $result;
		}
		catch(Exception $ex) {

			$dal->LogException($e, 'Could not update user', 'error');
			return false;

		}
	}

	/**
	 * Inserts a new twitter user into the database
	 * @param  object $user Twitter API user object
	 * @return boolean      True if successfully inserted, false otherwise
	 */
	private function insertTwitterUser($user) {

		$dal = new DAL();

		try {
			$query  = "
				INSERT INTO user (id, name, handle, followersCount, friendsCount, statusesCount, createdAt)
				     VALUES (:id, :name, :handle, :followersCount, :friendsCount, :statusesCount, :createdAt)
			";
			$params = array(
				array('name' => 'id', 'value' => $user->id_str, 'type' => PDO::PARAM_INT),
				array('name' => 'name', 'value' => $user->name, 'type' => PDO::PARAM_STR),
				array('name' => 'handle', 'value' => $user->screen_name, 'type' => PDO::PARAM_STR),
				array('name' => 'followersCount', 'value' => $user->followers_count, 'type' => PDO::PARAM_INT),
				array('name' => 'friendsCount', 'value' => $user->friends_count, 'type' => PDO::PARAM_INT),
				array('name' => 'statusesCount', 'value' => $user->statuses_count, 'type' => PDO::PARAM_INT),
				array('name' => 'createdAt', 'value' => date('Y-m-d H:i:s', strtotime($user->created_at)), 'type' => PDO::PARAM_STR),
			);

			// Execute the query
			$result = $dal->executeNonQuery($query, $params);

			return $result;
		}
		catch(Exception $ex) {

			$dal->LogException($e, 'Could not insert new user', 'error');
			return false;

		}
	}

	/**
	 * Checks if the twitter status exists in our database yet
	 * @param  string $id Twitter API's $status->id_str
	 * @return boolean    True if status exists, false otherwise.
	 */
	private function checkIfStatusExists($id) {

		$dal = new DAL();

		try {
			$query  = "SELECT id FROM status WHERE id = :id";
			$params = array(
				array('name' => 'id', 'value' => $id, 'type' => PDO::PARAM_INT)
			);

			// Execute the query
			$result = $dal->executeScalar($query, $params);
			return (!!$result);
		}
		catch(Exception $ex) {

			$dal->LogException($e, 'Could not check if status exists', 'error');
			return false;

		}
	}

	/**
	 * Checks if the twitter user exists in our database yet
	 * @param  string $id Twitter API's $status->user->id_str
	 * @return boolean    True if user exists, false otherwise.
	 */
	private function checkIfUserExists($id) {

		$dal = new DAL();

		try {
			$query  = "SELECT id FROM user WHERE id = :id";
			$params = array(
				array('name' => 'id', 'value' => $id, 'type' => PDO::PARAM_INT)
			);

			// Execute the query
			$result = $dal->executeScalar($query, $params);
			return (!!$result);
		}
		catch(Exception $ex) {

			$dal->LogException($e, 'Could not check if user exists', 'error');
			return false;

		}
	}

	/**
	 * Sends HTTPS request to twitter API with user credentials end returns oAuth bearer token
	 * @return string The twitter API access token for the passed consumer key and consumer secret
	 */
	private function requestBearerToken() {

		// Set token endpoint url
		$tokenEndpointUrl = "https://api.twitter.com/oauth2/token";

		// Bearer credential is a bse64 encoded string of <consumerKey>:<consumerSecret>
		$bearerCredentials = base64_encode($this->getConsumerKey() . ':' . $this->getConsumerSecret());

		// Create an options context that contains the headers used to make a call to the URL
		// including the app name and the access token
		$context = stream_context_create(array(
		 	"http"   => array(
		 		"method"  => "POST",
		    	"header"  => "POST /oauth2/token HTTP/1.1\r\n" .
		    				 "Host: api.twitter.com\r\n" .
		    			  	 "User-Agent: Rockwell Test App\r\n" .
		    			 	 "Authorization: Basic " . $bearerCredentials . "\r\n" .
		    				 "Content-Type: application/x-www-form-urlencoded;charset=UTF-8\r\n" .
	    					 "Content-Length: 29\r\n",
		    	"content" => "grant_type=client_credentials"
		    )
		));
		 
		// Execute the API call using the created headers
		// Supress error here since a 403 error results ina  warning and cannot be caught with tryCatch
		$response = @file_get_contents($tokenEndpointUrl, false, $context);

		// Decode the returned JSON response
		$json = json_decode($response);

		return $json->access_token;
	}

}