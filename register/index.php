<?php

namespace Registration;

use \shgysk8zer0\{User, PDO, Headers, HTTPException};

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php');
const METHODS = 'POST, OPTIONS, HEAD';

Headers::set('Access-Control-Allow-Origin', array_key_exists('HTTP_ORIGIN', $_SERVER) ? $_SERVER['HTTP_ORIGIN'] : '*');
Headers::set('Access-Control-Allow-Methods', METHODS);
Headers::set('Allow', METHODS);
Headers::set('Content-Type', 'application/json');
Headers::delete('X-Powered-By');

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'POST':
			if ($_SERVER['HTTP_ACCEPT'] !== 'application/json') {
				throw new HTTPException('Accept header must be "applicaiton/json"', Headers::NOT_ACCEPTABLE);
			} else  if (isset($_POST['username'], $_POST['password'])) {
				$user = new User(PDO::load());

				if ($user->create($_POST['username'], $_POST['password'])) {
					echo json_encode($user);
				} else {
					throw new HTTPException('Error registering user', Headers::UNAUTHORIZED);
				}
			} else {
				throw new HTTPException('Missing username or password fields', Headers::BAD_REQUEST);
			}
			break;
		case 'OPTIONS':
		case 'HEAD':
			break;
		default:
			throw new HTTPException('Allowed Methods: ' . METHODS, Headers::METHOD_NOT_ALLOWED);
	}
} catch (HTTPException $e) {
	$e();
}
