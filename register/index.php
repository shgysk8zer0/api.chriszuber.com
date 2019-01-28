<?php

namespace Registration;

use \shgysk8zer0\{User};
use function \Functions\{get_pdo};

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php');

header('Access-Control-Allow-Origin: *');

if (isset($_POST['username'], $_POST['password'])) {
	$user = new User(get_pdo());
	$user->create($_POST['username'], $_POST['password']);
	header('Content-Type: application/json');
	echo json_encode($user);
} else {
	http_response_code(400);
}