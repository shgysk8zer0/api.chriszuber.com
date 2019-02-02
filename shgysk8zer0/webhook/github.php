<?php

namespace shgysk8zer0\WebHook;

use \shgysk8zer0\{HTTPException};
use \shgysk8zer0\Abstracts\{HTTPStatusCodes as HTTP};

final class GitHub implements \JSONSerializable
{
	const HOOKSHOT = '/^GitHub-Hookshot/';
	private $_config = null;
	private $_data = [];
	private $_payload = null;
	private $_event = null;
	private $_headers = null;

	public function __construct(string $config)
	{
		$this->_config = json_decode(file_get_contents($config));
		$this->_getHeaders();
		$this->_parse();
	}

	public function __debugInfo(): array
	{
		return [
			'config'   => $this->_config,
			'data'     => $this->_data,
			'payload'  => $this->_payload,
			'$_SERVER' => $_SERVER,
			'headers'  => $this->_headers,
			'event'    => $this->_event,
		];
	}

	public function __get(string $key)
	{
		return $this->_data[$key];
	}

	public function __isset(string $key): bool
	{
		return array_key_exists($key, $this->_data);
	}

	public function jsonSerialize(): array
	{
		return $this->__debugInfo();
		// return [
		// 	'data' => $this->_data,
		// ];
	}

	private function  _getHeaders(): bool
	{
		if (is_null($this->_headers)) {
			$headers = getallheaders();
			$keys    = array_map('strtolower', array_keys($headers));
			$values  = array_values($headers);

			$this->_headers = array_combine($keys, $values);
		}
		return is_array($this->_headers);
	}

	private function _parse(): void
	{
		switch(strtolower($this->_headers['content-type'])) {
		case 'application/json':
			if (array_key_exists('content-length', $this->_headers)) {
				$payload = file_get_contents('php://input');
				$length = strlen($payload);
				$data = json_decode($payload);

				if ($length !== intval($this->_headers['content-length'])) {
					throw new HTTPException('Content-Length does not match payload size', HTTP::BAD_REQUEST);
				} elseif ($this->_verifySecret($this->_headers['x-hub-signature'], $payload, $this->_config->secret)) {
					$this->_payload = $payload;
					$this->_data = $data;
				} else {
					throw new HTTPException('Missing or invalid Signature', HTTP::BAD_REQUEST);
				}
			} else {
				throw new HTTPException('Content-Length header required', HTTP::LENGTH_REQUIRED);
			}
		}
	}

	private function _verifySecret(string $sig, string $payload, string $secret): bool
	{
		list($algo, $hmac) = explode('=', $sig, 2) + ['', ''];
		// header(sprintf('X-Sig: %s', $sig));
		// header(sprintf('X-Algo: %s', $algo));
		// header(sprintf('X-HMAC: %s', $hmac));

		if (in_array($algo, hash_algos(), true)) {
			$hash_hmac = hash_hmac($algo, file_get_contents('php://input'), $secret);
			// header(sprintf('X-Expected-HMAC: %s', $hash_hmac));
			return hash_equals($hash_hmac, $hmac);
		} else {
			throw new HTTPException(sprintf('Unsupported algo: %s', $algo), HTTP::INTERNAL_SERVER_ERROR);
		}
	}
}