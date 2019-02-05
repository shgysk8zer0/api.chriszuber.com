<?php
namespace User;
use \shgysk8zer0\{PDO, User, Headers, HTTPException, API};

const METHODS = [
	'GET',
	'DELETE',
	'OPTIONS',
	'HEAD',
];

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php');

try {
	$api = new API('*', METHODS);
	$api->on('OPTIONS', function(): void
	{
		Headers::set('Allow', join(', ', OPTIONS));
	});

	$api->on('HEAD', function(): void
	{
		Headers::set('Allow', join(', ', OPTIONS));
	});

	$api->on('GET', function(API $api): void
	{
		if (! array_key_exists('token', $_GET)) {
			throw new HTTPException('Missing token in request', Headers::BAD_REQUEST);
		} else {
			Headers::set('Content-Type', 'application/json');
			$user = User::loadFromToken(PDO::load(), $_GET['token']);

			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', Headers::UNAUTHORIZED);
			} else {
				echo json_encode($user);
			}
		}
	});

	$api->on('DELETE', function(API $api): void
	{
		if (! array_key_exists('token', $_GET)) {
			throw new HTTPException('Missing token in request', Headers::BAD_REQUEST);
		} else {
			Headers::set('Content-Type', 'application/json');
			$user = User::loadFromToken(PDO::load(), $_GET['token']);
			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', Headers::UNAUTHORIZED);
			} elseif ($user->delete()) {
				echo json_encode(['status' => 'success']);
			} else {
				throw new HTTPException('Error deleting user', Headers::INTERNAL_SERVER_ERROR);
			}
		}
	});
	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
}
