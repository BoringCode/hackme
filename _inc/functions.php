<?php

/* A simple wrapper for the PHP password functions */
class Password {
	const COST = 14;
	const HASH = PASSWORD_DEFAULT;

	private $dictionary = "/usr/share/dict/words";

	private $hash;

	private $password;

	/* 
	 * Constructor
	 * Creates and hashes a given password
	 * $password string
	 */
	public function __construct($password) {
		$this->password = trim($password);
		$this->hash($this->password);
	}

	/*
	 * hash()
	 * Generates a salted and hashed password
	 * $password string, password to hash
	 */
	public function hash($password) {
		$this->hash = password_hash($password, self::HASH, ['cost' => self::COST]);

		return $this->hash;
	}

	/*
	 * getHash()
	 * Returns the hashed password
	 */
	public function getHash() {
		return $this->hash;
	}

	/*
	 * verify()
	 * Matches password to a given hash
	 * @return bool
	 */
	public function verify($hash) {
		return password_verify($this->password, $hash);
	}


	/* 
	 * check()
	 * Checks if password matches password requirements
	 */
	public function check() {
		$length = strlen($this->password);

		//Password is too short
		if ($length < 8) return false;

		// count how many lowercase, uppercase, and digits are in the password 
	    $uc = 0; $lc = 0; $num = 0; $other = 0;
	    for ($i = 0; $i < $length; $i++) {
	        $c = substr($this->password, $i, 1);
	        if (preg_match('/^[[:upper:]]$/',$c)) {
	            $uc++;
	        } elseif (preg_match('/^[[:lower:]]$/',$c)) {
	            $lc++;
	        } elseif (preg_match('/^[[:digit:]]$/',$c)) {
	            $num++;
	        } else {
	            $other++;
	        }
	    }

	    //Enforce at least two types of characters
	    $max = $length - 2;

	    //Too many uppercase letters
	    if ($uc > $max) return false;

	    //Too many lowercase letters
	    if ($lc > $max) return false;

	    //Too many numbers
	    if ($num > $max) return false;

	    //Too many special characters
	    if ($other > $max) return false;

	    //Check that password is not a dictionary word
		if (is_readable($this->dictionary)) {
		    if ($fh = fopen($this->dictionary, 'r')) {
		        while (!(feof($fh))) {
		            $word = preg_quote(trim(strtolower(fgets($fh, 1024))), '/');
		            if ($word === strtolower($this->password)) {
		            	echo $word;
		        		fclose($fh);
		            	return false;
		            }
		        }
		    }
		}

		return true;
	}
}

/* Handle site authentication and database operations */
class SiteAuthentication {
	//Only allow a certain number of attempts per minute
	const MAX_ATTEMPTS = 5;
	const TIME_RANGE = 60;

	//Database information
	const DBUser = "security-project";
	const DBPassword = "69BgYftvzpEH";
	const DBName = "hackme";

	//Site database
	private $DB;

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

		$this->DB = new mysqli("localhost", self::DBUser, self::DBPassword, self::DBName);

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

		$result = $this->query("SELECT pass, last_attempt, attempts FROM users WHERE username = '%s'", array($username));

		if (count($result) === 0) return false;

		$password = new Password($password);

		//Rate limit password guesses per minute
		//Once the user has guessed 5 times, they have to wait a minute before trying again
		$attempt_time = time();
		$attempts = (int) $result[0]->attempts;
		//Reset attempt counter if user hasn't attempted in last minute
		if ($attempt_time > ($result[0]->last_attempt + self::TIME_RANGE)) {
			$attempts = 0;
		}
		$attempts++;

		//User has exceeded max number of attempts in last minute
		if ($attempts > self::MAX_ATTEMPTS) return false;

		//Update user with attempt count and current time
		$this->query("UPDATE users SET last_attempt = '%d', attempts = '%d' WHERE username = '%s'", array(
			$attempt_time,
			$attempts,
			$username
		));

		if (!$password->verify($result[0]->pass)) return false;

		$_SESSION["username"] = $username;
		$_SESSION["login_time"] = $attempt_time;

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

		$password = new Password($password);

		//Bad password
		if (!$password->check()) return false;

		$users = $this->query("SELECT username FROM users WHERE username = '%s'", array($username));

		//User already exists
		if (count($users) > 0) return false;

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