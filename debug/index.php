<?php
namespace Upload;

use \shgysk8zer0\{PDO, User, API, Headers, URL, HTTPException};
use \shgysk8zer0\Abstracts\{HTTPStatusCodes as HTTP};
use const \Consts\{HOST};

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

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
			} else {
				Headers::set('Content-Type', 'application/json');
				echo json_encode([
					'$request' => $request,
					'$_SERVER' => $_SERVER,
					'includedFiles' => get_included_files(),
					'consts' => get_defined_constants(true)['user'],
				]);
			}
		}
	});
	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
}
