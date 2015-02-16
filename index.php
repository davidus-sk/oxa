<?php
/**
 * Main request handler
 * 
 * @package Oxa
 */

// require config
require 'conf/config.php';

// require classes
require 'class/Cache.php';
require 'class/Oxa.php';

// clean request
$request = trim($_SERVER['REQUEST_URI'], '/');

// route request
if (preg_match('/[a-z0-9]{6}/i', $request)) {
	$shortener = new Oxa();
	$result = $shortener->getDataById($request);
	unset($shortener);

	if ($result !== false) {
		header('Location: ' . $result['longURL_c'], true, 301);
	} else {
		header('HTTP/1.1 404 Not Found');
	}
	
	exit(0);
}