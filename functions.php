<?php

namespace Functions;

use const \Consts\{DEBUG, ERROR_LOG};
use \shgysk8zer0\{PDO, User, JSONFILE, Headers, HTTPException};
use \StdClass;
use \DateTime;
use \Throwable;
use \ErrorException;

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
		$dtime = new DateTime();

		file_put_contents(ERROR_LOG, sprintf(
			'[%s %d]: %s "%s" on %s:%d' . PHP_EOL,
			get_class($e),
			$e instanceof ErrorException ? $e->getSeverity() : $e->getCode(),
			$dtime->format(DateTime::W3C),
			$e->getMessage(),
			$e->getFile(),
			$e->getLine()
		), FILE_APPEND | LOCK_EX);

		exit(json_encode([
			'error' => [
				'message' => 'Internal Server Error',
				'status'  => Headers::INTERNAL_SERVER_ERROR,
			],
		]));
	}
}
