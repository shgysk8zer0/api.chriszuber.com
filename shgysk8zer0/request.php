<?php
namespace shgysk8zer0;
use \shgysk8zer0\Traits\{cURL};
class Request
{
	use cURL;

	public function __construct(string $url, string $method = 'GET', array $headers = [], array $params = [])
	{
		$this->setURL($url);
		$this->setMethod($method);
		$this->setHeaders($headers);
		$this->setParams($params);
	}

	final public function send() {
		return $this->_send();
	}
}