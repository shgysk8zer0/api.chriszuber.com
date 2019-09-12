<?php
namespace UUID;
use \shgysk8zer0\PHPAPI\{UUID, API, Headers, HTTPException};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
	$api = new API('*');

	$api->on('GET', function(): void
	{
		Headers::contentType('text/plain');
		echo new UUID();
	});

	$api();
} catch(HTTPException $e) {
	Headers::status($e->getCode());
	Headers::contentType('application/json');
	echo json_encode($e);
}
