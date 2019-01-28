<?php
use \shgysk8zer0\{PDO, User, JSONFile};
use const \Consts\{CREDS_FILE};
use function \Functions\{get_pdo};

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php');

header(sprintf(
	'Access-Control-Allow-Origin: %s',
	array_key_exists('HTTP_ORIGIN', $_SERVER) ? $_SERVER['HTTP_ORIGIN'] : '*'
));
header('Access-Control-Allow-Methods: POST, OPTIONS, HEAD');
header('Allow: POST, OPTIONS, HEAD');
header('Content-Type: application/json');

switch($_SERVER['REQUEST_METHOD']) {
	case 'POST':
		if ($_SERVER['HTTP_ACCEPT'] !== 'application/json') {
			http_response_code(406);
		} else  if (isset($_POST['username'], $_POST['password'])) {
			$user = new User(get_pdo(CREDS_FILE));

			if ($user->login($_POST['username'], $_POST['password'])) {
				echo json_encode($user);
			} else {
				http_response_code(401);
				echo '{}';
			}
		} else {
			http_response_code(400);
			echo '{}';
		}
		break;
	case 'OPTIONS':
	case 'HEAD':
		break;
	default:
		http_response_code(405);
}
