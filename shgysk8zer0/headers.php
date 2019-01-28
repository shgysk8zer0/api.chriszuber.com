<?php

namespace shgysk8zer0;

final class Headers
{
	static $_headers = [];

	public static function set(string $key, string $value, bool $replace = true): void
	{
		header("{$key}: {$value}", $replace);
	}

	public static function get(string $key): string
	{
		//
	}

	public static code(int $code = 200): void
	{
		http_response_code($code);
	}

	private static _getHeaders(): void
	{
		if (! empty(static::$_headers)) {
			$headers = get_all_headers();
			static::$_headers =
		}
	}
}