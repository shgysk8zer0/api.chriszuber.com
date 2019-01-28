<?php
use \shgysk8zer0\{PDO, User, Headers};
use const \Consts\{CREDS_FILE};
use function \Functions\{get_pdo};

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php');

Headers::set('Access-Control-Allow-Origin', array_key_exists('HTTP_ORIGIN', $_SERVER) ? $_SERVER['HTTP_ORIGIN'] : '*');
Headers::set('Access-Control-Allow-Methods', 'POST, OPTIONS, HEAD');
Headers::set('Allow', 'POST, OPTIONS, HEAD');
Headers::set('Content-Type', 'application/json');
Headers::delete('X-Powered-By');

switch($_SERVER['REQUEST_METHOD']) {
	case 'POST':
		if ($_SERVER['HTTP_ACCEPT'] !== 'application/json') {
			Headers::status(Headers::NOT_ACCEPTABLE);
		} else  if (isset($_POST['username'], $_POST['password'])) {
			$user = new User(get_pdo(CREDS_FILE));

			if ($user->login($_POST['username'], $_POST['password'])) {
				echo json_encode($user);
			} else {
				Headers::status(Headers::UNAUTHORIZED);
				echo '{}';
			}
		} else {
			Headers::status(Headers::BAD_REQUEST);
			echo '{}';
		}
		break;
	case 'OPTIONS':
	case 'HEAD':
		break;
	default:
		Headers::status(Headers::METHOD_NOT_ALLOWED);
}
