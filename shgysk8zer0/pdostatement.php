<?php

namespace shgysk8zer0;

final class PDOStatement extends \PDOStatement
{
	final public function __set(string $param, $value): void
	{
		$this->bindValue(":{$param}", $value);
	}

	final public function __invoke(array $params = null): bool
	{
		if (isset($params)) {
			$keys = array_keys($params);
			$values = array_values($params);
			array_walk($keys, function(string $key): string
			{
				return sprintf(':%s', $key);
			});
			return $this->execute(array_combine($keys, $values));
		} else {
			return $this->execute();
		}
	}
}