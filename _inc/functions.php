<?php

/* A simple wrapper for the PHP password functions */
class Password {
	const COST = 14;
	const HASH = PASSWORD_DEFAULT;

	private $hash;

	private $password;

	public function __construct($password) {
		$this->password = $password;
		$this->hash($this->password);
	}

	public function hash($password) {
		$this->hash = password_hash($password, self::HASH, ['cost' => self::COST]);

		return $this->hash;
	}

	public function getHash() {
		return $this->hash;
	}

	public function verify($hash) {
		return password_verify($this->password, $hash);
	}
}

/* Handle site authentication and database operations */
class SiteAuthentication {
	//Site database
	private $DB;

	private $DBUser = "security-project";
	private $DBPassword = "69BgYftvzpEH";
	private $DBName = "hackme";

	public $logged_in = false;

	//Start the PHP session and connect to the database
	function __construct() {
		session_start();
		if (isset($_SESSION["username"])) $this->logged_in = true;
		
		//Connect to database
		$this->connect();
	}

	//Cleanup functions
	function __destruct() {
		//Close DB connection
		if (isset($this->DB)) {
			$this->DB->close();
		}
	} 

	//Create database object
	private function connect() {
		//Don't allow making the DB again
		if (isset($this->DB)) return $this->DB;

		$this->DB = new mysqli("localhost", $this->DBUser, $this->DBPassword, $this->DBName);

		if ($this->DB->connect_errno) {
			die($this->DB->connect_error);
		}

		return $this->DB;
	}

	/*
	 * login()
	 * Logs an user into the site
	 * $username string, Username to check against database
	 * $password string, Password for user
	 * @return bool
	 */
	public function login($username, $password) {
		if ($this->logged_in) return true;
		//Make sure username and password are provided
		if (!isset($username) || !isset($password)) return false;

		$username = $this->escape(trim($username));
		$password = $this->escape(trim($password));

		$hash = $this->query("SELECT pass FROM users WHERE username = '%s'", array($username));

		if (count($hash) === 0) return false;

		$password = new Password($password);

		if (!$password->verify($hash[0]->pass)) return false;

		$_SESSION["username"] = $username;
		$_SESSION["login_time"] = time();

		$this->logged_in = true;

		return true;
	}

	/* 
	 * logout()
	 * Logs the user out and destroys the current session (invalidating the session both client side and server side)
	 * @return bool
	 */
	public function logout() {
		// Unset all of the session variables.
		$_SESSION = array();

		//Get rid of all user session cookies
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}

		// Finally, destroy the session.
		session_destroy();

		$this->logged_in = false;

		return true;
	}

	/*
	 * createUser()
	 * $username string, Username for new user
	 * $password string, Password to be hashed and stored in DB
	 * $firstName string, First name of user
	 * $lastName string, Last name of user
	 */
	public function createUser($username, $password, $firstName, $lastName) {
		if (!$this->DB) return false;
		if (!$username || !$password || !$firstName || !$lastName) return false;

		$users = $this->query("SELECT username FROM users WHERE username = '%s'", array($username));

		//User already exists
		if (count($users) > 0) return false;

		$password = new Password($password);

		$createUser = $this->query("INSERT INTO users (username, pass, fname, lname) VALUES ('%s', '%s', '%s', '%s')", array(
			htmlspecialchars($username), 
			$password->getHash(),
			htmlspecialchars($firstName),
			htmlspecialchars($lastName)
		));

		return $createUser;
	}

	/*
	 * query()
	 * $query string, MySQL query string with parametrized values
	 * $values array, Array of values to insert into query string
	 * $require_login bool, Whether this query should require the user to be logged in
	 */
	public function query($query, $values = array(), $require_login = false) {
		if (!$this->DB) return array();
		if ($require_login && !$this->logged_in) return array();
		if (!isset($query)) return array();

		//Make sure all values are properly escaped
		$values = array_map(array($this, "escape"), $values);
		//Create statement
		$statement = vsprintf($query, $values);

		//Get the results of the query
		if ($result = $this->DB->query($statement)) {
			$rows = true;
			if (isset($result->num_rows)) {
				$rows = array();
				while ($row = $result->fetch_object()) {
					$rows[] = $row;
				}				
				$result->close();
			}
			return $rows;
		}

		return false;
	}

	public function escape($value) {
		if (!$this->DB) return false;
		return $this->DB->real_escape_string($value);
	}

}

$auth = new SiteAuthentication();