<?php

namespace Registration;

use \shgysk8zer0\{User, PDO, Headers, HTTPException, API};

const METHODS = [
	'POST',
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

	$api->on('POST', function(API $api): void
	{
		if ($api->accept !== 'application/json') {
			throw new HTTPException('Accept header must be "application/json"', Headers::NOT_ACCEPTABLE);
		} elseif (isset($_POST['username'], $_POST['password']) and filter_var($_POST['username'], FILTER_VALIDATE_EMAIL)) {
			$api->contentType = 'application/json';
			$user = new User(PDO::load());

			if ($user->create($_POST['username'], $_POST['password'])) {
				echo json_encode($user);
			} else {
				throw new HTTPException('Error registering user', Headers::UNAUTHORIZED);
			}
		} else {
			throw new HTTPException('Missing or invalid username or password fields', Headers::BAD_REQUEST);
		}
	});
	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
}
