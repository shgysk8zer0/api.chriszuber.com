<?php
namespace Login;
use \shgysk8zer0\{PDO, User, Headers, HTTPException, API};

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
			throw new HTTPException('Accept header must be "applicaiton/json"', Headers::NOT_ACCEPTABLE);
		} else  if (isset($_POST['username'], $_POST['password'])) {
			$user = new User(PDO::load());

			if ($user->login($_POST['username'], $_POST['password']) and filter_var($_POST['username'], FILTER_VALIDATE_EMAIL)) {
				echo json_encode($user);
			} else {
				throw new HTTPException('Invalid username or password', Headers::UNAUTHORIZED);
			}
		} else {
			throw new HTTPException('Missing username or password fields', Headers::BAD_REQUEST);
		}
	});

	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
}
