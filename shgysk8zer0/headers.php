<?php

namespace shgysk8zer0;

final class Headers extends Abstracts\HTTPStatusCodes
{
	static $_headers = [];

	public static function set(string $key, string $value, bool $replace = true): void
	{
		header("{$key}: {$value}", $replace);
	}

	public static function append(string $key, string $value): void
	{
		static::set($key, $value, false);
	}

	public static function get(string $key): string
	{
		static::_getHeaders();
		return static::$_headers[strtolower($key)];
	}

	public static function has(string $key): bool
	{
		static::_getHeaders();
		return array_key_exists(strtolower($key), static::$_headers);
	}

	public static function delete(string $key): void
	{
		header_remove($key);
	}

	public static function sent(): bool
	{
		return headers_sent();
	}

	public static function status(int $code = self::OK): void
	{
		http_response_code($code);
	}

	private static function _getHeaders(): void
	{
		if (! empty(static::$_headers)) {
			$headers = get_all_headers();
			$keys = array_map('strtolower', array_keys($headers));
			$values = array_values($headers);
			static::$_headers = array_combine($keys, $values);
		}
	}
}
