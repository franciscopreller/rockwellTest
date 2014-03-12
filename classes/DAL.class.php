<?php

class DAL {

	// ==================================================================
	//
	// PROPERTIES
	//
	// ------------------------------------------------------------------

	private $Server;
	private $Database;
	private $Username;
	private $Password;
	private $PDO;

	// ==================================================================
	//
	// METHODS
	//
	// ------------------------------------------------------------------

	public function __construct() {

		// Set up database connection info
		$server   = DATABASE_HOST;
		$database = DATABASE_NAME;
		$username = DATABASE_USER;
		$password = DATABASE_PASSWORD;

		// Try to connect
		try {
			$this->PDO = new PDO("mysql:host={$server};dbname={$database}", $username, $password);
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e) {
			// Log error
			$this->LogException($e, 'Problem connecting to the database server', 'error');
		}

	}

	/**
	 * Gets the last inserted ID from the PDO
	 * @return integer The last inserted ID.
	 */
	public function getLastInsertID() {
		return $this->PDO->lastInsertId();
	}

	/**
	 * Execute a query and return all results.
	 * 
	 * @param  string $sql
	 * @param  array  $parameters
	 * @return array | boolean
	 */
	public function executeQuery($sql, $parameters = array()) {

		$return = false;

		// Try to execute query
		try {
			//Create a statement object using the supplied query
			$statement = $this->PDO->prepare($sql);

			//Assign the parameters to the statement
			$this->assignParameters($statement, $parameters);

			//Execute query and get result
			$statement->execute();
			$return = $statement->fetchAll();
		}
		catch (PDOException $e) {
			//Log error
			$this->LogException($e, 'Problem executing the query', 'error');
		}

		return $return;

	}

	public function executeScalar($sql, $parameters = array()) {

		$return = false;

		// Try to execute query
		try {
			//Create a statement object using the supplied query
			$statement = $this->PDO->prepare($sql);

			//Assign the parameters to the statement
			$this->assignParameters($statement, $parameters);

			//Execute query and get result
			$statement->execute();
			$return = $statement->fetchColumn();
		}
		catch (PDOException $e) {
			//Log error
			$this->LogException($e, 'Problem executing the scalar query', 'error');
		}

		return $return;

	}

	public function executeNonQuery($sql, $parameters = array()) {

		$return = false;

		// Try to execute query
		try {
			// Create a statement object using the supplied query
			$statement = $this->PDO->prepare($sql);

			// Assign the parameters to the statement
			$this->assignParameters($statement, $parameters);

			// Execute query and get result
			$return = $statement->execute();
		}
		catch (PDOException $e) {
			// Log error
			$this->LogException($e, 'Problem executing the non-query', 'error');
		}

		return $return;

	}

	private function assignParameters($statement, $parameters) {

		/*
		 * Structure of parameters array:

		 	$parameters = array(
		 		array('name'=>':parameter1', 'value'=>'some value', 'type'=>PDO::PARAM_STR),
		 		array('name'=>':parameter2', 'value'=>123, 'type'=>PDO::PARAM_INT)
		 	)

		 */

		//Loop through each item in the parameters array
		foreach ($parameters as $parameter) {

			//Get information for current parameter and bind it to the statement
			$statement->bindValue(
				$parameter['name'],
				$parameter['value'],
				$parameter['type']
			);

		}

	}

	public function LogException(
		$pException,
		$pUserMsg 	 = '<no error message>',
		$pErrorLevel = 'error'
	) {

		$this->LogError(
			$pUserMsg,
			$pException->getFile() . ' on line ' . $pException->getLine(),
			$pException->getCode(),
			$pException->getMessage(),
			$pErrorLevel
		);

	}

	public function LogError(
		$pUserMsg 	 = '<no error message>',
		$pFile 		 = '<no file>',
		$pErrorNo 	 = '<xx>',
		$pErrorMsg 	 = '<no error message>',
		$pErrorLevel = 'error'
	) {

		//Write error to the error log

		//Get current time
		date_default_timezone_set('Australia/Sydney');
		$errorTime = date('Y-m-d H:i:s');

		//Assemble log entry
		$logEntry = "$errorTime :: ($pErrorNo) $pErrorMsg :: $pUserMsg :: $pFile\r\n";

		//Add the log entry to the log file
		file_put_contents(
			dirname(__FILE__) . '/../log/error.log',
			$logEntry,
			FILE_APPEND
		);

		return false;
	}

}

?>