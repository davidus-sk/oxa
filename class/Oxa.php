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
	 * Use file caching
	 * @var bool
	 */
	public $cacheEnabled = true;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->cacheEnabled = CACHE_ENABLED;
		$this->MySQLi = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

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
	 * @param string $secret
	 * @return bool
	 */
	public function addUrl($longURL, $secret = null) {
		// check the URL for format
		if (preg_match('/^[a-z0-9]+:\/\/.*$/i', $longURL)) {
			$hash = md5($longURL);

			$this->URLs[$hash] = array(
				'long' => $longURL,
				'short' => null,
				'id' => null,
				'secret' => $secret,
			);

			return true;
		}
		
		return false;
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
						$longURL = $this->MySQLi->real_escape_string($url['long']);
						$query = 'INSERT INTO tbl_urls (id_c, longURL_c, hash_c, dateAdded_d, secret_c) VALUES 
							(\'' . $id . '\', \'' . $longURL . '\', \'' . $hash . '\', ' . time() . ', ' . (empty($url['secret']) ? 'NULL' : '\'' . sha1($url['secret']) . '\'') . ')';
						$result = $this->MySQLi->query($query);

						if ($result) {
							$this->URLs[$hash]['short'] = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $id;
							$this->URLs[$hash]['id'] = $id;

							if ($this->cacheEnabled) {
								$cache = new Cache();
								$cache->add($longURL, $id, 900);
							}
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
	public function deleteUrl($longURL, $secret) {
		// sanitize
		$longURL = $this->MySQLi->real_escape_string($longURL);
		$secret = sha1($secret);

		// make request
		$query = 'DELETE FROM tbl_urls WHERE longURL_c = \'' . $longURL . '\' AND secret_c = \'' . $secret . '\'';
		$result = $this->MySQLi->query($query);

		if ($result) {
			return $this->MySQLi->affected_rows;
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
		$cache = new Cache();

		// check the array
		if (!empty($this->URLs[$hash]['short'])) {
			return $this->URLs[$hash]['short'];
		}
		// check cache
		elseif ($this->cacheEnabled && $cache->keyValid($longURL)) {
			$data = $cache->get($longURL);

			if ($data) {
				return $data;
			}
		}
		// check database
		elseif ($result = $this->getDataByLongUrl($longURL)) {
			return $result['id_c'];
		}

		return false;
	}

	/**
	 * 
	 * @param string $id
	 * @return mixed
	 */
	public function getDataById($id) {
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
	private function getDataByLongUrl($longURL) {
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
			
			if ($this->getDataById($id) === false) {
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
		$base = 'ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz1234567890';
		$max = strlen($base)-1;
		$randString = '';

		mt_srand((double)microtime()*1000000);

		while(strlen($randString) < $length) {
			$randString .= $base{mt_rand(0,$max)};
		}

		return $randString;
	}
}
