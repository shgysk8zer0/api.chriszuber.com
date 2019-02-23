<?php
namespace User;
use \shgysk8zer0\{PDO, User, Headers, HTTPException, API};

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php');

try {
	$api = new API('*');

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
