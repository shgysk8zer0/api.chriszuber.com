<?php
namespace Fortune;
use \shgysk8zer0\PHPAPI\{API, Headers, HTTPException};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
	$api = new API();

	$api->on('GET', function(): void
	{
		if (`command -v 'fortune'` === null) {
			throw new HTTPException('`fortune` not installed', HTTP::INTERNAL_SERVER_ERROR);
		} else {
			Headers::contentType('text/plain');
			exit(`fortune`);
		}
	});

	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::contentType('application/json');
	echo json_encode($e);
}
