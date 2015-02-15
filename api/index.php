<?php
/**
 * Main API handler
 * 
 * @package Oxa
 */

// require config
require '../conf/config.php';

// require classes
require '../class/Oxa.php';

// vars
$statusCode = 200;
$result = null;

// determine verb
$httpVerb = strtolower($_SERVER['REQUEST_METHOD']);

// process requests
switch ($httpVerb) {
	case 'post': {
		// get raw data
		$data = file_get_contents('php://input');
		
		// decode
		$json = json_decode($data, true);

		if ($json) {
			// run shortener
			try {
				$shortener = new Oxa();
				$shortener->addUrl($json['longURL']);
				$result = $shortener->shorten();
			} catch (Exception $e) {
				$statusCode = 500;
			}
		} else {
			$statusCode = 400;
		}
	} break;

	default: {
		$statusCode = 405;
	} break;
}

// output
header('Content-Type: application/json');
echo json_encode(array(
	'statusCode' => $statusCode,
	'data' => $result,
));