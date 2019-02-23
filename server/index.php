<?php
namespace Server;
use \shgysk8zer0\{API, Headers, HTTPException, PDO, User};
use \shgysk8zer0\Abstracts\{HTTPStatusCodes as HTTP};

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
	$api = new API('*');
	$api->on('GET', function(API $request): void
	{
		if ($request->url->searchParams->has('token')) {
			$user = User::loadFromToken(PDO::load(), $request->url->searchParams->get('token'));
			if ($user->isAdmin()) {
				Headers::set('Content-Type', 'application/json');
				echo json_encode($_SERVER);
			} else {
				throw new HTTPException('Access denied', HTTP::FORBIDDEN);
			}
		} else {
			throw new HTTPException('Missing token in request', HTTP::UNAUTHORIZED);
		}
	});
	$api();
} catch(HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
} catch (\Throwable $e) {
	Headers::status(500);
	Headers::set('Content-Type', 'application/json');
	echo json_encode([
		'message' => $e->getMessage(),
		'file'    => $e->getFile(),
		'line'    => $e->getLine(),
		'trace'   => $e->getTrace(),
	]);
}