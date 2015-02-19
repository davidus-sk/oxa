<?php
/**
 * Main API handler
 * 
 * @package Oxa
 */

// require config
require '../conf/config.php';

// require classes
require '../class/Cache.php';
require '../class/Oxa.php';

// vars
$statusCode = 200;
$result = null;

// determine verb
$httpVerb = strtolower($_SERVER['REQUEST_METHOD']);

// process requests
switch ($httpVerb) {
	// add new URL to shortener
	case 'post': {
		// get raw data
		$data = file_get_contents('php://input');
		
		// decode
		$json = json_decode($data, true);

		if ($json) {
			// run shortener
			try {
				$shortener = new Oxa();
				$shortener->addUrl($json['longURL'], empty($json['secret']) ? null : $json['secret']);
				$result = $shortener->shorten();
				unset($shortener);
			} catch (Exception $e) {
				$statusCode = 500;
			}
		} else {
			$statusCode = 400;
		}
	} break;

	// delete URL from shortener
	case 'delete': {
		// get raw data
		$data = file_get_contents('php://input');
		
		// decode
		$json = json_decode($data, true);

		if ($json) {
			// run shortener
			try {
				$shortener = new Oxa();
				$result = $shortener->deleteUrl($json['longURL'], $json['secret']);
				unset($shortener);
			} catch (Exception $e) {
				$statusCode = 500;
			}
		} else {
			$statusCode = 400;
		}		
	} break;

	// default
	default: {
		$statusCode = 405;
	} break;
}

// output
header('HTTP/1.1 ' . $statusCode);
header('Content-Type: application/json');
echo json_encode(array(
	'statusCode' => $statusCode,
	'method' => $httpVerb,
	'data' => $result,
));