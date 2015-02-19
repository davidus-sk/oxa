<?php
/**
 * Oxa shortener caching class
 * 
 * @package Oxa
 */

/**
 * Key/value caching
 * 
 * @sample
 * <code>
 * <pre>
 * $Cache = new Cache();
 * $Cache->add('key', array('test'=>true), 500);
 * $data = $Cache->get('key');
 * </pre>
 * </code>
 * 
 * @version 1.0
 */
class Cache {
	
	/**
	 * Cache directory
	 * @var string
	 */
	private $cacheDir = CACHE_DIR;
	
	/**
	 * Cachced data
	 * @var mixed
	 */
	private $payload = null;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->cacheDir = rtrim($this->cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		
		if (!is_dir($this->cacheDir)) {
			mkdir($this->cacheDir, 0777, true);
		}
	}

	/**
	 * Cache object
	 * @param string $key
	 * @param mixed $value
	 * @param int $duration
	 */
	public function add($key, $value, $duration = 300) {
		$keyHash = sha1($key);
		$file = $this->cacheDir . $keyHash;
		
		// we don't really care about collisions
		$object = array(
			'expires' => time() + $duration,
			'payload' => $value
		);
		
		$json = json_encode($object);
		
		if ($json) {
			$bytes = file_put_contents($file, $json);
			
			if (!empty($bytes)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve item from cache
	 * @param type $key
	 * @return boolean
	 */
	public function get($key) {
		$keyHash = sha1($key);
		$file = $this->cacheDir . $keyHash;

		if ($this->keyValid($key)) {
			return $this->payload;
		}

		return false;
	}
	
	/**
	 * Delete item from cache
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		$keyHash = sha1($key);
		$file = $this->cacheDir . $keyHash;

		if ($this->keyExists($key)) {
			if (unlink($file)) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Check if key is not expired
	 * @param string $key
	 * @return boolean
	 */
	public function keyValid($key) {
		$keyHash = sha1($key);
		$file = $this->cacheDir . $keyHash;

		if ($this->keyExists($key)) {
			$json = file_get_contents($file);

			if (!empty($json)) {
				$object = json_decode($json, true);

				if (!empty($object)) {
					if ($object['expires'] >= time()) {
						$this->payload = $object['payload'];

						return true;
					} else {
						//cleanup
						$this->delete($key);
					}
				}
			}
		}

		return false;
	}
	
	/**
	 * Check if key is in cache
	 * @param string $key
	 * @return boolean
	 */
	public function keyExists($key) {
		$keyHash = sha1($key);
		$file = $this->cacheDir . $keyHash;
		clearstatcache();

		if (file_exists($file)) {
			return true;
		}

		return false;
	}

	/**
	 * Init class
	 */
	public static function init() {
		$className = __CLASS__;
		return new $className();
	}
}