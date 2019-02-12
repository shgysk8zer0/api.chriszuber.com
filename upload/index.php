<?php
namespace Upload;

use \shgysk8zer0\{PDO, User, API, Headers, Uploads, HTTPException};
use \shgysk8zer0\Abstracts\{HTTPStatusCodes as HTTP};
use function \Functions\{upload_path};
use const \Consts\{HOST};

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
	$api = new API('*');
	$api->on('POST', function(): void
	{
		if (! array_key_exists('token', $_POST)) {
			throw new HTTPException('Missing token in request', HTTP::BAD_REQUEST);
		} else {
			$user = User::loadFromToken(PDO::load(), $_POST['token']);
			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			}/* elseif (! $user->isAdmin()) {
				throw new HTTPException('You do not have permissions for this action', HTTP::FORBIDDEN);
			}*/ elseif (empty($_FILES)) {
				throw new HTTPException('No file uploaded', HTTP::BAD_REQUEST);
			} else {
				$files = [];
				$path = upload_path();

				foreach (Uploads::getInstance() as $key => $file) {
					if ($file->moveTo("{$path}{$file->hashFileName()}")) {
						$files[$key] = $file;
					} else {
						$files[$key] = new HTTPException("Error uploading {$file->name}");
					}
				}
				Headers::set('Content-Type', 'application/json');
				echo json_encode($files);
			}
		}
	});

	$api->on('DELETE', function(): void
	{
		if (! array_key_exists('token', $_GET)) {
			throw new HTTPException('Missing token in request', HTTP::BAD_REQUEST);
		} else {
			$user = User::loadFromToken(PDO::load(), $_GET['token']);
			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} elseif (! $user->isAdmin()) {
				throw new HTTPException('You do not have permissions for this action', HTTP::FORBIDDEN);
			} else {
				// Delete the file
			}
		}
	});
	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
}
