<?php

namespace Functions;

use const \Consts\{DEBUG, ERROR_LOG, UPLOADS_DIR};
use \shgysk8zer0\{PDO, User, JSONFILE, Headers, HTTPException};
use \StdClass;
use \DateTime;
use \Throwable;
use \ErrorException;

function upload_path(): string
{
	$date = new DateTime();
	return UPLOADS_DIR . $date->format(sprintf('Y%sm%s', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR));
}

function is_cli(): bool
{
	return in_array(PHP_SAPI, ['cli']);
}

function error_handler(int $errno, string $errstr, string $errfile, int $errline = 0): bool
{
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	return true;
}

function exception_handler(Throwable $e)
{
	if ($e instanceof HTTPException) {
		$e();
	} else {
		Headers::status(Headers::INTERNAL_SERVER_ERROR);
		Headers::set('Content-Type', 'application/json');
		log_exception($e);
		exit(json_encode([
			'error' => [
				'message' => 'Internal Server Error',
				'status'  => Headers::INTERNAL_SERVER_ERROR,
			],
		]));
	}
}

function log_exception(Throwable $e): bool
{
	$dtime = new DateTime();
	return file_put_contents(ERROR_LOG, sprintf(
		'[%s %d]: %s "%s" on %s:%d' . PHP_EOL,
		get_class($e),
		$e instanceof ErrorException ? $e->getSeverity() : $e->getCode(),
		$dtime->format(DateTime::W3C),
		$e->getMessage(),
		$e->getFile(),
		$e->getLine()
	), FILE_APPEND | LOCK_EX);
}
