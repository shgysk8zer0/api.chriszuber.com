<?php

namespace Functions;
use const \Consts\{DEBUG, CREDS_FILE, HMAC_FILE};
use \shgysk8zer0\{PDO, User, JSONFILE};
use \StdClass;
use \Throwable;

function log_exception(Throwable $e)
{
	file_put_contents('error.log', sprintf(
		'Error: "%s" @ %s:%d',
		$e->getMessage(),
		$e->getFile(),
		$e->getLine()
	) . PHP_EOL);
}

function get_pdo(string $filename = CREDS_FILE): PDO
{
	static $instances = [];
	if (! in_array($filename, $instances)) {
		$creds = new JSONFile($filename);
		$instances[$filename] = new PDO($creds->username, $creds->password, $creds->database);
	}
	return $instances[$filename];
}

function get_user(string $creds_file = CREDS_FILE, string $hmac_file = HMAC_FILE): User
{
	$pdo = get_pdo($creds_file);
	if (array_key_exists('token', $_REQUEST)) {
		$token  = $_REQUEST['token'];
		User::setKey(file_get_contents($hmac_file));
		return User::loadFromToken(get_pdo($creds_file), $_REQUEST['token']);
	} else {
		return new User($pdo);
	}
}