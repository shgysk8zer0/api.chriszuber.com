<?php

namespace shgysk8zer0;

class PDO extends \PDO
{
	const OPTIONS = [
		self::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
		self::ATTR_ERRMODE            => self::ERRMODE_EXCEPTION,
		self::ATTR_DEFAULT_FETCH_MODE => self::FETCH_OBJ,
		self::ATTR_STATEMENT_CLASS    => [__NAMESPACE__ . '\\PDOStatement'],
	];

	final public function __construct(
		string $username,
		string $password,
		string $database = null,
		string $host     = 'localhost',
		int    $port     = 3306,
		string $charset  = 'UTF8'
	)
	{
		if (is_null($database)) {
			$database = $username;
		}

		$dsn = sprintf('mysql:dbname=%s;host=%s;charset=%s;port=%d', $database, $host, $charset, $port);
		parent::__construct($dsn, $username, $password, self::OPTIONS);
	}

	public function __invoke(string $sql, array $params = null): array
	{
		$stm = $this->prepare($sql);
		$stm($params);
		return $stm->fetchAll();
	}
}