<?php
namespace Setup;
require_once('../autoloader.php');

use \shgysk8zer0\{PDO, DSN, Headers, HTTPException};
use const \Consts\{CREDS_FILE, SQL_FILE};
use function \Functions\{log_exception};

const METHODS = 'POST, OPTIONS, HEAD';

if (file_exists(CREDS_FILE)) {
	throw new HTTPException('Already installed', Headers::FORBIDDEN);
} elseif (! file_exists(SQL_FILE)) {
	throw new HTTPException('SQL file is missing', Headers::INTERNAL_SERVER_ERROR);
}

Headers::set('Access-Control-Allow-Origin', array_key_exists('HTTP_ORIGIN', $_SERVER) ? $_SERVER['HTTP_ORIGIN'] : '*');
Headers::set('Access-Control-Allow-Methods', METHODS);
Headers::set('Allow', METHODS);
Headers::set('Content-Type', 'application/json');
Headers::delete('X-Powered-By');

switch($_SERVER['REQUEST_METHOD']) {
	case 'POST':
		if (! array_key_exists('HTTP_ACCEPT', $_SERVER) or $_SERVER['HTTP_ACCEPT'] !== 'application/json') {
			throw new HTTPException('Accept header must be "applicaiton/json"', Headers::NOT_ACCEPTABLE);
		} else  if (isset($_POST['username'], $_POST['password'], $_POST['database'])) {
			$dsn = DSN::loadFromArray($_POST);

			try {
				$pdo = new \PDO($dsn, $dsn->getUsername(), $dsn->getPassword(), PDO::OPTIONS);
			} catch (\Throwable $e) {
				log_exception($e);
				throw new HTTPException('Error connecting to database', Headers::INTERNAL_SERVER_ERROR);
			}

			if (! $dsn->saveAs(CREDS_FILE)) {
				throw new HTTPException('Unable to save credentials to disk', Headers::INTERNAL_SERVER_ERROR);
			}

			try {
				$pdo->exec(file_get_contents(SQL_FILE));
			} catch (\Throwable $e) {
				log_exception($e);
				unlink(CREDS_FILE);
				throw new HTTPException('Error creating database', Headers::INTERNAL_SERVER_ERROR);
			}
			exit(json_encode(['message' => 'Installation complete']));
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
