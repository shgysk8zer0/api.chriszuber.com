<?php
namespace WebHook\GitHub;

use const \Consts\{GITHUB_WEBHOOK};
use \shgysk8zer0\{HTTPException, Headers};
use \shgysk8zer0\WebHook\{GitHub};
use \Throwable;
use \DateTime;


const METHODS = 'POST, OPTIONS, HEAD';

require_once(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'autoloader.php');

try {
	switch($_SERVER['REQUEST_METHOD']) {
	case 'POST':
		$hook = new GitHub(GITHUB_WEBHOOK);

		Headers::set('Content-Type', 'application/json');
		echo(json_encode([
			'pull' => exec('git pull'),
			'submodule update' => exec('git submodule update --init --recursive'),
		]));
		break;
	case 'OPTIONS':
	case 'HEAD':
		Headers::set('Allow', METHODS);
		break;
	default:
		Headers::set('Allow', METHODS);
		throw new HTTPException('Allowed Methods: ' . METHODS, Headers::METHOD_NOT_ALLOWED);
	}
} catch(HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
} catch (Throwable $e) {
	Headers::status(Headers::INTERNAL_SERVER_ERROR);
	Headers::set('Content-Type', 'text/plain');
	echo 'Internal Server Error' . PHP_EOL;
}
