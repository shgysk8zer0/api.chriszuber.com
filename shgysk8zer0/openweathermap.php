<?php
namespace shgysk8zer0;
use \shgysk8zer0\PHPAPI\{URL, Request, HTTPException};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use \StdClass;

final class OpenWeatherMap
{
	const API_VERSION = 2.5;
	const ORIGIN = 'http://api.openweathermap.org/';

	private $_url = null;
	private $_units = 'imperial';

	public function __construct(string $key)
	{
		$this->_url = new URL(self::ORIGIN);
		$this->_url->searchParams->set('APPID', $key);
	}

	public function getFromCoords(float $lat, float $lon): StdClass
	{
		$this->_url->pathname = sprintf('data/%s/weather', self::API_VERSION);
		$this->_url->searchParams->set('lat', $lat);
		$this->_url->searchParams->set('lon', $lon);
		$this->_url->searchParams->set('units', $this->_units);
		$req = new Request($this->_url);
		$resp = $req->send();
		$this->_reset();
		$this->_parseResponse($resp);
		if ($resp->ok) {
			return $resp->body;
		} elseif (isset($resp->body->message)) {
			throw new HTTPException($resp->body->message, $resp->status);
		} else {
			throw new HTTPException('Bad Gateway', HTTP::BAD_GATEWAY);
		}
	}

	public function getFromZip(int $zip): StdClass
	{
		$this->_url->pathname = sprintf('data/%s/weather', self::API_VERSION);
		$this->_url->searchParams->set('zip', $zip);
		$this->_url->searchParams->set('units', $this->_units);
		$req = new Request($this->_url);
		$resp = $req->send();
		$this->_parseResponse($resp);
		$this->_reset();

		if ($resp->ok) {
			return $resp->body;
		} elseif (isset($resp->body->message)) {
			throw new HTTPException($resp->body->message, $resp->status);
		} else {
			exit(json_encode($resp));
			throw new HTTPException('Bad Gateway', HTTP::BAD_GATEWAY);
		}
	}

	private function _reset(): void
	{
		$url = new URL(self::ORIGIN);
		$url->searchParams->set('APPID', $this->_url->searchParams->get('APPID'));
		$this->_url = $url;
	}

	private function _parseResponse(StdClass &$resp): void
	{
		if (is_string($resp->body)) {
			$resp->body = json_decode($resp->body);
		}
		if (isset($resp->body->cod)) {
			$resp->body->cod = intval($resp->body->cod);
		}
	}
}
