<?php

namespace shgysk8zer0;

use \shgysk8zer0\Traits\{CORS};
use \shgysk8zer0\Abstracts\{HTTPStatusCodes as HTTP};
use \shgysk8zer0\{HTTPException, Headers};
use \Exception;

class API
{
	use CORS;

	const DEFAULT_METHODS = [
		'HEAD',
		'OPTIONS',
		'GET',
		'POST',
		'DELETE',
	];

	private $_callbacks = [];

	final public function __construct(string $origin = '*')
	{
		static::allowOrigin($origin);

		if ($origin !== '*' and $this->origin !== $origin) {
			throw new HTTPException('Origin not allowed', HTTP::FORBIDDEN);
		}

		$this->on('OPTIONS', function(): void
		{
			Headers::set('Allow', join(', ', $this->options));
		});

		$this->on('HEAD', function(): void
		{
			Headers::set('Allow', join(', ', $this->options));
		});
	}

	final public function __get(string $prop)
	{
		switch(strtolower($prop)) {
			case 'accept': return $_SERVER['HTTP_ACCEPT'] ?? '*/*';
			case 'contentlength': return $_SERVER['CONTENT_LENGTH'] ?? 0;
			case 'contenttype': return $_SERVER['CONTENT_TYPE'] ?? null;
			case 'https': return array_key_exists('HTTPS', $_SERVER) and $_SERVER['HTTPS'] !== 'off';
			case 'method': return $_SERVER['REQUEST_METHOD'] ?? null;
			case 'options': return array_keys($this->_callbacks);
			case 'origin': return $_SERVER['HTTP_ORIGIN'] ?? null;
			case 'remoteaddr':
			case 'remoteaddress': return $_SERVER['REMOTE_ADDR'] ?? null;
			case 'remotehost': return $_SERVER['REMOTE_HOST'] ?? null;
			case 'referer':
			case 'referrer': return $_SERVER['HTTP_REFERER'] ?? null;
			case 'requesturi':
			case 'requesturl': return $_SERVER['REQUEST_URI'] ?? null;
			case 'serveraddress': return $_SERVER['SERVER_ADDR'];
			case 'servername': return $_SERVER['SERVER_NAME'];
			case 'useragent': return $_SERVER['HTTP_USER_AGENT'] ?? null;
			case 'files': return $_FILES;
			default: throw new \Exception(sprintf('Unknown property: %s', $prop));
		}
	}

	final public function __set(string $prop, $value): void
	{
		switch(strtolower($prop)) {
			case 'contenttype':
				Headers::set('Content-Type', $value);
				break;
			case 'status':
				Headers::status($value);
				break;
			default: throw new \Exception(sprintf('Unknown property: %s', $prop));
		}
	}

	final public function __call(string $method, array $args = []): void
	{
		array_unshift($args, $this);
		$method = strtoupper($method);

		if (array_key_exists($method, $this->_callbacks)) {
			foreach ($this->_callbacks[$method] as $callback) {
				call_user_func_array($callback, $args);
			}
		}
	}

	final public function __invoke(): void
	{
		$method = $this->method;
		static::allowMethods(...$this->options);

		$args = func_get_args();
		array_unshift($args, $this);

		if (array_key_exists($method, $this->_callbacks)) {
			foreach ($this->_callbacks[$method] as $callback) {
				call_user_func_array($callback, $args);
			}
		} else {
			static::set('Allow', join(', ', $this->options));

			throw new HTTPException("Unsupported Method: {$method}", HTTP::METHOD_NOT_ALLOWED);
		}
	}

	final public function __debugInfo(): array
	{
		return [
			'callbacks' => $this->_callbacks,
			'method'    => $this->method,
		];
	}

	final public function on(string $method, callable $callback): void
	{
		$method = strtoupper($method);

		if (! array_key_exists($method, $this->_callbacks)) {
			$this->_callbacks[$method] = [];
		}
		$this->_callbacks[$method][] = $callback;
	}
}
