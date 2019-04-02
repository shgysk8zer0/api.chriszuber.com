<?php
namespace User;
use \shgysk8zer0\PHPAPI\{PDO, User, Headers, HTTPException, API};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use function \Functions\{is_pwned};

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php');

try {
	$api = new API('*');

	$api->on('GET', function(API $api): void
	{
		if (! $api->get->has('token')) {
			throw new HTTPException('Missing token in request', HTTP::BAD_REQUEST);
		} else {
			Headers::contentType('application/json');
			$user = User::loadFromToken(PDO::load(), $api->get->get('token', false));

			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} else {
				echo json_encode($user);
			}
		}
	});

	$api->on('POST', function(API $api): void
	{
		if ($api->accept !== 'application/json') {
			throw new HTTPException('Accept header must be "application/json"', Headers::NOT_ACCEPTABLE);
		} elseif ($api->post->has('username', 'password') and API::isEmail($api->post('username', false))) {
			$api->contentType = 'application/json';
			if (is_pwned($api->post->get('password', false))) {
				throw new HTTPException('Password has previously been found in data breach', HTTP::FORBIDDEN);
			}
			$user = new User(PDO::load());

			if ($user->create($api->post('username', false), $api->post('password', false))) {
				Headers::status(HTTP::CREATED);
				echo json_encode($user);
			} else {
				throw new HTTPException('Error registering user', HTTP::UNAUTHORIZED);
			}
		} else {
			throw new HTTPException('Missing or invalid username or password fields', HTTP::BAD_REQUEST);
		}
	});

	$api->on('DELETE', function(API $api): void
	{
		if (! $api->get->has('token')) {
			throw new HTTPException('Missing token in request', HTTP::BAD_REQUEST);
		} else {
			Headers::contentType('application/json');
			$user = User::loadFromToken(PDO::load(), $api->get('token', false));
			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} elseif ($user->delete()) {
				echo json_encode(['status' => 'success']);
			} else {
				throw new HTTPException('Error deleting user', HTTP::INTERNAL_SERVER_ERROR);
			}
		}
	});

	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::contentType('application/json');
	echo json_encode($e);
}
