<?php
/**
 * Oxa shortener class
 * 
 * @package Oxa
 */

/**
 * Oxa URL shortener
 * 
 * @sample
 * <code>
 * <pre>
 * $Shortener = new Oxa();
 * $Shortener->addUrl('http://www.google.com');
 * $Shortener->addUrl('http://www.yahoo.com');
 * $results = $Shortener->shorten();
 * </pre>
 * </code>
 * 
 * @version 1.0
 */
class Oxa {

	/**
	 * List of (to be) shortened URLs
	 * @var array
	 */
	private $URLs = array();
	
	/**
	 * MySQL object
	 * @var resource
	 */
	private $MySQLi = null;
	
	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->MySQLi = new mysqli("localhost", "my_user", "my_password", "world");

		if ($this->MySQLi->connect_errno) {
			throw new Exception('DB ERROR: ' . $this->MySQLi->connect_errno);
		}
	}

	/**
	 * Class destructor
	 */
	public function __destruct() {
		$this->MySQLi->close();
	}

	/**
	 * Add URL to list
	 * @param string $longURL
	 */
	public function addUrl($longURL) {
		$hash = md5($longURL);

		$this->URLs[$hash] = array(
			'long' => $longURL,
			'short' => null,
			'id' => null,
		);
	}
	
	/**
	 * Process URLs into short format
	 * @return array
	 */
	public function shorten() {
		if (!empty($this->URLs)) {
			// process all requested URLs
			foreach ($this->URLs as $hash=>$url) {
				// check if already shortened
				$result = $this->getShortUrl($url['long']);

				if ($result) {
					$this->URLs[$hash]['short'] = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $result;
					$this->URLs[$hash]['id'] = $result;
				} else {
					// generate new Id
					$id = $this->getNewId();
					
					if ($id) {
						// save in DB
						$longURL = $mysqli->real_escape_string($url['long']);
						$query = 'INSERT INTO tbl_urls (id_c, longURL_c, hash_c, dateAdded_d) VALUES 
							(\'' . $id . '\', \'' . $longURL . '\', \'' . $hash . '\', ' . time() . ')';
						$result = $mysqli->query($query);

						if ($result) {
							$this->URLs[$hash]['short'] = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $id;
							$this->URLs[$hash]['id'] = $id;
						} else {
							$this->URLs[$hash]['error'] = 'DB INSERT ERROR';
						}
					} else {
						$this->URLs[$hash]['error'] = 'TOO MANY KEY COLLISIONS';
					}
				}
			}
		}
		
		return $this->URLs;
	}

	/**
	 * Delete URL
	 * @param string $longURL
	 * @return boolean
	 */
	public function deleteUrl($longURL) {
		// sanitize
		$id = $this->MySQLi->real_escape_string($longURL);

		// make request
		$query = 'DELETE FROM tbl_urls WHERE longURL_c = \'' . $longURL . '\'';
		$result = $this->MySQLi->query($query);

		if ($result) {
			return true;
		}

		return false;
	}

	/**
	 * Return short URL
	 * @param string $longURL
	 * @return mixed
	 */
	private function getShortUrl($longURL) {
		$hash = md5($longURL);

		// check the array
		if (!empty($this->URLs[$hash]['short'])) {
			return $this->URLs[$hash]['short'];
		}
		// check database
		elseif ($result = $this->getFromDBByLongUrl($longURL)) {
			return $result['id_c'];
		}
		
		return false;
	}

	/**
	 * 
	 * @param string $id
	 * @return mixed
	 */
	private function getFromDBById($id) {
		// sanitize
		$id = $this->MySQLi->real_escape_string($id);

		// make request
		$query = 'SELECT * FROM tbl_urls WHERE id_c = \'' . $id . '\'';
		$result = $this->MySQLi->query($query);

		if ($result) {
			$row = $result->fetch_assoc();
			
			if (!empty($row)) {
				return $row;
			}
		}

		return false;
	}

	/**
	 * 
	 * @param string $longURL
	 * @return mixed
	 */
	private function getFromDBByLongUrl($longURL) {
		// sanitize
		$longURL = $this->MySQLi->real_escape_string($longURL);

		// make request
		$query = 'SELECT * FROM tbl_urls WHERE longURL_c = \'' . $longURL . '\'';
		$result = $this->MySQLi->query($query);

		if ($result) {
			$row = $result->fetch_assoc();
			
			if (!empty($row)) {
				return $row;
			}
		}

		return false;
	}
	
	/**
	 * Generate database wide unique key
	 * @return mixed
	 */
	private function getNewId() {
		for ($i = 0; $i < 10; $i++) {
			$id = $this->getRandomString();
			
			if ($this->getFromDBById($id) === false) {
				return $id;
			}
		}
		
		return false;
	}
	
	/**
	 * Generate string of random chars
	 * @param int $length
	 * @return string
	 */
	private function getRandomString($length = 6) {
		$length = intval($length) > 0 ? $length : 16;
		$base = 'ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
		$max = strlen($base)-1;
		$randString = '';

		mt_srand((double)microtime()*1000000);

		while(strlen($randString) < $length) {
			$randString .= $base{mt_rand(0,$max)};
		}

		return $randString;
	}
}
