<?php
namespace Log;
use \shgysk8zer0\{PDO, User, Headers, HTTPException, API};
use \shgysk8zer0\Abstracts\{HTTPStatusCodes as HTTP};
use const \Consts\{ERROR_LOG};

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php');

try {
	$api = new API('*');
	$api->on('GET', function(API $request): void
	{
		if (! $request->url->searchParams->has('token')) {
			throw new HTTPException('Missing token in request', HTTP::BAD_REQUEST);
		} else {
			$user = User::loadFromToken(PDO::load(), $request->url->searchParams->get('token'));
			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} elseif (! $user->isAdmin()) {
				throw new HTTPException('You do not have permissions for this action', HTTP::FORBIDDEN);
			} elseif (file_exists(ERROR_LOG)) {
				Headers::set('Content-Type', 'application/json');
				echo json_encode(file(ERROR_LOG, FILE_SKIP_EMPTY_LINES));
			} else {
				throw new HTTException('Log File Not Found', HTTP::INTERNAL_SERVER_ERROR);
			}
		}
	});

	$api->on('DELETE', function(API $request): void
	{
		if (! $request->url->searchParams->has('token')) {
			throw new HTTPException('Missing token in request', HTTP::BAD_REQUEST);
		} else {
			$user = User::loadFromToken(PDO::load(), $request->url->searchParams->get('token'));
			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} elseif (! $user->isAdmin()) {
				throw new HTTPException('You do not have permissions for this action', HTTP::FORBIDDEN);
			} elseif (file_exists(ERROR_LOG)) {
				$fhandle = @fopen(ERROR_LOG, 'a+');

				if (is_resource($fhandle) and flock($fhandle, LOCK_EX)) {
					ftruncate($fhandle, 0);
					flock($fhandle, LOCK_UN);
					fclose($fhandle);
				} else {
					if (is_resource($fhandle)) {
						fclose($fhandle);
					}
					throw new HTTPException('Unable to obtain lock on log file', HTTP::Conflict);
				}
			} else {
				throw new HTTException('Log File Not Found', HTTP::INTERNAL_SERVER_ERROR);
			}
		}
	});
	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
}
