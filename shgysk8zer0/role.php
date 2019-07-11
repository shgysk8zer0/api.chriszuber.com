<?php
namespace shgysk8zer0;

use \shgysk8zer0\PHPAPI\{PDO};
use \StdClass;

final class Role implements \JSONSerializable
{
	const PERMISSIONS = [
		'debug',
		'createUpload',
		'deleteUpload',
		'createEvent',
		'updateEvent',
		'deleteEvent',
		'createUser',
		'updateUser',
		'deleteUser',
		'deleteUser',
		'createComment',
		'deleteComment',
		'createPost',
		'updatePost',
		'deletePost',
	];


	private $_perms = null;
	private $_name  = '';
	private static $_stm = null;

	final public function __construct(PDO $pdo, int $id)
	{
		if (is_null(static::$_stm)) {
			$sql = sprintf('SELECT `name`, %s FROM `roles` WHERE `id` = :id LIMIT 1;', join(',', self::PERMISSIONS));
			static::$_stm = $pdo->prepare($sql);

		}
		$this->_id = $id;
		static::$_stm->execute([':id' => $id]);

		if ($role = static::$_stm->fetchObject()) {
			$this->_name = $role->name;
			unset($role->name);

			foreach (self::PERMISSIONS as $key) {
				$role->{$key} = $role->{$key} === '1';
			}

			$this->_perms = $role;
		} else {
			$this->_perms = new StdClass;
		}
	}

	final public function __toString(): string
	{
		return $this->_name;
	}

	final public function jsonSerialize(): array
	{
		$arr = [
			'id'   => $this->_id,
			'name' => $this->_name,
		];

		foreach(self::PERMISSIONS as $key) {
			$arr[$key] = $this->__get($key);
		}

		return $arr;
	}

	final public function __isset(string $perm): bool
	{
		return isset($this->_perms->{$perm});
	}

	final public function __get(string $perm): bool
	{
		return isset($this->{$perm}) and $this->_perms->{$perm};
	}

	final public static function getFromName(PDO $pdo, string $name): self
	{
		$stm = $pdo->prepare('SELECT `id` FROM `roles` WHERE `name` = :name LIMIT 1;');
		$stm->execute([':name' => $name]);
		$role = $stm->fetchObject();
		return new self($pdo, $role->id);
	}

	final public static function getAll(PDO $pdo): array
	{
		$stm = $pdo->query('SELECT `id` FROM `roles`;');
		$stm->execute();

		return array_map(function(StdClass $role) use ($pdo): self
		{
			return new self($pdo, $role->id);
		}, $stm->fetchAll());
	}
}
