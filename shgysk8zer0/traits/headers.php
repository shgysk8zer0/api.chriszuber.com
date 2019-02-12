<?php
namespace shgysk8zer0\Traits;

use \shgysk8zer0\Abstracts\{HTTPStatusCodes as HTTP};

trait Headers
{
	static private $_headers = [];

	final public static function set(string $key, string $value, bool $replace = true): void
	{
		header("{$key}: {$value}", $replace);
	}

	final public static function append(string $key, string $value): void
	{
		static::set($key, $value, false);
	}

	final public static function get(string $key): string
	{
		static::_getHeaders();
		return static::$_headers[strtolower($key)];
	}

	final public static function has(string $key): bool
	{
		static::_getHeaders();
		return array_key_exists(strtolower($key), static::$_headers);
	}

	final public static function delete(string $key): void
	{
		header_remove($key);
	}

	final public static function sent(): bool
	{
		return headers_sent();
	}

	final public static function status(int $code = HTTP::OK): void
	{
		http_response_code($code);
	}

	final protected static function _getHeaders(): void
	{
		if (! empty(static::$_headers)) {
			$headers = get_all_headers();
			$keys = array_map('strtolower', array_keys($headers));
			$values = array_values($headers);
			static::$_headers = array_combine($keys, $values);
		}
	}
}
