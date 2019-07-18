<?php
namespace Index;

use \shgysk8zer0\PHPAPI\{API, Headers, HTTPException, PDO};
use \shgysk8zer0\PHPAPI\Schema\{Event, Thing};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use \DateTime;
use \Throwable;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
	$api = new API('*');

	$api->on('GET', function(API $req): void
	{
		Event::setPDO(PDO::load());
		Headers::contentType(Event::CONTENT_TYPE);

		try {
			$start = new DateTime($req->get->get('start', false, null));
		} catch (Throwable $e) {
			throw new HTTPException(
				sprintf('"%s" is not a valid date format', $req->get->get('start')),
				HTTP::BAD_REQUEST
			);
		}

		$events = Event::searchDateRange(
			$start,
			$req->get->get('range', false, '1 month'),
			$req->get->get('limit', false, 50),
			$req->get->get('page', false, 1)
		);

		echo json_encode($events, JSON_PRETTY_PRINT);
	});

	$api();
} catch (HTTPException $e) {
	Headers::contentType('application/json');
	Headers::status($e->getCode());
	exit(json_encode($e));
}
