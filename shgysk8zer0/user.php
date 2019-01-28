<?php

namespace shgysk8zer0;

use \DateTime;
use \PDO;

final class User implements \JsonSerializable
{
	const PASSWORD_ALGO = PASSWORD_DEFAULT;

	const PASSWORD_OPTS = [
		'cost' => 10,
	];

	const HASH_ALGO = 'sha3-256';

	private $_id       = null;

	private $_username = null;

	private $_created  = null;

	private $_updated  = null;

	private $_loggedIn = false;

	private $_hash     = null;

	private $_pdo      = null;

	final public function __construct(PDO $pdo)
	{
		$this->_pdo = $pdo;
	}

	final public function __get(string $key)
	{
		switch($key) {
			case 'username':
				return $this->_username;
			case 'loggedIn':
				return $this->_loggedIn;
			case 'created':
				return $this->_created;
			case 'updated':
				return $this->_updated;
			default:
				throw new \InvalidArgumentError(sprintf('Undefined or invalid property: "%s"', $key));
		}
	}

	final public function __debugInfo(): array
	{
		return [
			'id'       => $this->_id,
			'username' => $this->_username,
			'created'  => $this->_created,
			'updated'  => $this->_updated,
			'loggedIn' => $this->_loggedIn,
			'hash'     => $this->_hash,
		];
	}

	public function __toString(): string
	{
		return $this->_username;
	}

	public function jsonSerialize(): array
	{
		return $this->_loggedIn ? [
			'id'       => $this->_id,
			'username' => $this->_username,
			'created'  => $this->_created->format(DateTime::W3C),
			'updated'  => $this->_updated->format(DateTime::W3C),
			'loggedIn' => $this->_loggedIn,
		] : [
			'loggedIn' => false,
		];
	}

	final public function login(string $username, string $password): bool
	{
		$stm = $this->_pdo->prepare(
			'SELECT `id`,
				`password` AS `hash`,
				`created`,
				`updated`
			FROM `users`
			WHERE `username` = :username
			LIMIT 1;'
		);

		$stm->bindValue(':username', $username);

		if ($stm->execute()) {
			$user = $stm->fetchObject();
			if (isset($user->hash) and password_verify($password, $user->hash)) {

				$this->_username = $username;
				$this->_created = new DateTime($user->created);
				$this->_updated = new DateTime($user->updated);
				$this->_loggedIn = true;
				$this->_id = intval($user->id);
				$this->_hash = $user->hash;

				if ($this->passwordNeedsUpdate()) {
					$this->changePassword($password);
				}
				return true;
			} else {
				$this->logout();
				return false;
			}
		} else {
			return false;
		}
	}

	final public function logout(): bool
	{
		if ($this->_loggedIn) {
			$this->_id = null;
			$this->_username = null;
			$this->_hash = null;
			$this->_created = null;
			$this->_updated = null;
			$this->_loggedIn = false;
			return true;
		} else {
			return false;
		}
	}

	final public function isLoggedIn(): bool
	{
		return $this->_loggedIn;
	}

	final public function create(string $username, string $password): bool
	{
		$stm = $this->_pdo->prepare(
			'INSERT INTO `users` (
				`username`,
				`password`
			) VALUES (
				:username,
				:password
			);'
		);

		$hash = password_hash($password, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
		$datetime = new DateTime('now');
		$stm->bindValue(':username', $username);
		$stm->bindValue(':password', $hash);

		try {
			$stm->execute();
			$id = intval($this->_pdo->lastInsertId());

			if ($id !== 0) {
				$this->_id = $id;
				$this->_username = $username;
				$this->_created = new DateTime();
				$this->_loggedIn = true;
				$this->_updated = $this->_created;
				$this->_hash = $hash;
				return true;
			} else {
				return false;
			}
		} catch (\Throwable $e) {
			return false;
		}
	}

	final public function passwordNeedsUpdate(): bool
	{
		return $this->_loggedIn
			&& password_needs_rehash($this->_hash, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
	}

	final public function changePassword(string $password): bool
	{
		if ($this->_loggedIn) {
			$hash = password_hash($password, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
			$stm = $this->_pdo->prepare(
				'UPDATE `users`
				SET `password` = :hash
				WHERE `id` = :id
				LIMIT 1;'
			);
			$stm->bindValue(':id', $this->_id);
			$stm->bindValue(':hash', $hash);
			$stm->execute();

			if ($stm->rowCount() === 1) {
				$this->_hash = $hash;
				$this->_updated = new DateTime();
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	final public function delete(): bool
	{
		if ($this->_loggedIn) {
			$stm = $this->_pdo->prepare(
				'DELETE FROM `users`
				WHERE `id` = :id
				LIMIT 1;'
			);
			$stm->bindValue(':id', $this->_id);
			$stm->execute();
			return $this->_pdo->rowCount() === 1;
		} else {
			return false;
		}
	}

	static public function getUser(PDO $pdo, Int $id): self
	{
		$user = new self($pdo);
		$stm = $pdo->prepare(
			'SELECT `id`, `username`, `password` AS `hash`, `created`, `updated`
			FROM `users`
			WHERE `id` = :id
			LIMIT 1;'
		);
		$stm->bindValue(':id', $id);

		if ($stm->execute()) {
			$data = $stm->fetchObject();
			$user->_id = intval($data->id);
			$user->_username = $data->username;
			$user->_created = new DateTime($data->created);
			$user->_updated = new DateTime($data->updated);
			$user->_hash = $data->hash;
			$user->_loggedIn = true;
		}
		return $user;
	}
}
