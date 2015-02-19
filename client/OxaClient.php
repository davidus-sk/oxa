<?php

/*
 * Oxa URL shortener client library
 *
 * @sample
 * <code>
 * <pre>
 * $Shortener = new OxaClient();
 * $Shortener->addUrl('http://haha.com', 'banana');
 * $Shortener->addUrl('http://hihi.com');
 * $result = $Shortener->shorten();
 * </pre>
 * </code>
 *
 * @version 0.1
 */

class OxaClient {
	/**
	 * Google shortener API
	 *
	 * @var string
	 */
	private $baseUrl = "http://oxa.us/api/";

	/**
	 *
	 * @var object
	 */
	private $curlHandle = null;

	/**
	 * List of URLs to shorten
	 * @var array[string]
	 */
	private $urls = array();

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->curlHandle = curl_init();
		curl_setopt($this->curlHandle, CURLOPT_URL, $this->baseUrl);
	}

	/**
	 * Add URL
	 * @param string $url
	 * @param string $secret
	 */
	public function addUrl($url, $secret = null) {
		$hash = md5($url);

		$this->urls[$hash] = array(
			'long' => $url,
			'short' => null,
			'secret' => $secret,
		);
	}

	/**
	 * Returns a URL from list
	 * @param string $url
	 */
	public function getUrl($url, $type = null){
		$hash = md5($url);
		if (!empty($type)) {
			return isset($this->urls[$hash][$type]) ? $this->urls[$hash][$type] : false;
		} else {
			return isset($this->urls[$hash]) ? $this->urls[$hash] : false;
		}
	}

	/**
	 * Run shortener
	 * @return array
	 */
	public function shorten() {
		foreach ($this->urls as $hash => $value) {
			$json = json_encode(array(
				'longURL' => $value['long'],
				'secret' => $value['secret'],
			));

			curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($this->curlHandle, CURLOPT_POST, 1);
			curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $json);
			curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, 1);
			$result = curl_exec($this->curlHandle);

			if ($result) {
				$data = json_decode($result, true);

				if (($data['statusCode'] == 200) && empty($data['data'][$hash]['error'])) {
					$this->urls[$hash]['short'] = $data['data'][$hash]['short'];
				}
			}
		}

		curl_close($this->curlHandle);
		return $this->urls;
	}
}