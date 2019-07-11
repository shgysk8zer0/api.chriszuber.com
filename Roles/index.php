<?php
namespace Roles;

use \shgysk8zer0\PHPAPI\{API, PDO, Headers, HTTPException};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use \shgysk8zer0\Role;
use \StdClass;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

$api = new API('*');

$api->on('GET', function(API $req): void
{
	if ($req->get->has('role')) {
		$role = Role::getFromName(PDO::load(), $req->get->get('role'));
		Headers::contentType('application/json');
		echo json_encode($role);
	} else {
		$roles = Role::getAll(PDO::load());
		Headers::contentType('application/json');
		echo json_encode($roles);
	}
});

$api();
