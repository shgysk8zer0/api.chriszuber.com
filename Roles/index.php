<?php
namespace Roles;

use \shgysk8zer0\PHPAPI\{API, PDO, Headers, HTTPException};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use \StdClass;
use const \Consts\{PERMISSIONS};

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

$api = new API('*');

$api->on('GET', function(API $req): void
{
	$search = $req->get->has('role');
	$pdo = PDO::load();

	$sql = $search
		? sprintf('SELECT `id`, `name`, %s FROM `roles` WHERE `name` = :name LIMIT 1;', join(',', PERMISSIONS))
		: sprintf('SELECT `id`, `name`, %s FROM `roles`;', join(',', PERMISSIONS));

	$stm = $pdo->prepare($sql);

	if ($search) {
		$stm->bindValue(':name', $req->get->get('role'));
	}

	$stm->execute();
	$roles = $stm->fetchAll();

	$results = array_map(function(StdClass $role): StdClass
	{
		$role->id = intval($role->id);

		foreach (PERMISSIONS as $key) {
			$role->{$key} = $role->{$key} === '1';
		}

		return $role;
	}, $roles);

	Headers::contentType('application/json');

	if (empty($results)) {
		echo $search ? '{}' : '[]';
	} else {
		echo json_encode($search ? $results[0] : $results);
	}
});

$api();
