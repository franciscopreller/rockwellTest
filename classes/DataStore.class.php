<?php

require_once 'DAL.class.php';

/**
 * Simple static wrapper class to access data in the database
 * Written by: Francisco Preller
 */
class DataStore {

	/**
	 * Gets all records from the database for display
	 * @return array All data from the data store
	 */
	public static function getAllRecords() {

		$dal = new DAL();

		try {
			$query  = "
				SELECT	user.handle,
						user.name,
						status.text,
						status.createdAt
				  FROM  status
				  		JOIN user ON status.userId = user.id
			";

			// Execute the query
			$result = $dal->executeQuery($query);
			
			return $result;
		}
		catch(Exception $ex) {

			$dal->LogException($e, 'Could not get all stored data', 'error');
			return false;

		}

	}

	/**
	 * Sanitizes all inputs
	 * @param  string $input The input to sanitize
	 * @return string        The sanitized string
	 */
	public static function sanitize($input) {

		// If it's an array, iterate and clean everything
		if (is_array($input)) {

	        foreach($input as $var=>$val) {
	            $output[$var] = sanitize($val);
	        }

    	} else {

	        if (get_magic_quotes_gpc())
	            $input = stripslashes($input);

	        $output = DataStore::cleanInput($input);
	    }

	    return $output;
	}

	/**
	 * Cleans inputs
	 * @return string The clean input
	 */
	private static function cleanInput($input) {
		$search = array(
		    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
		    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
		    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
		    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
		);
		 
		$output = preg_replace($search, '', $input);

		return $output;
	}

	/**
	 * Helper function, encodes an entire array passed into utf8
	 * @param  array $dat The array to encode
	 * @return array      The encoded array
	 */
	public static function utf8_encode_all($dat) { 

		if (is_string($dat))
			return utf8_encode($dat); 

		if (!is_array($dat)) 
			return $dat; 

		$ret = array(); 

		foreach($dat as $i=>$d) 
			$ret[$i] = DataStore::utf8_encode_all($d); 

		return $ret; 
	}

}