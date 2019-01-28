<?php

namespace Functions;
use const \Consts\{DEBUG, CREDS_FILE};
use \shgysk8zer0\{PDO, JSONFILE};
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