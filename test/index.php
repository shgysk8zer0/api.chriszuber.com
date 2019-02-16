<?php
namespace Test;
use \shgysk8zer0\{API, URL, Headers, HTTPException};
use \shgysk8zer0\Abstracts\{HTTPStatusCodes as HTTP};
use const \Consts\{HOST, BASE_URI};
use \Throwable;
require_once  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
	$api = new API('*');
	$api->on('GET', function(): void
	{
		Headers::set('Content-Type', 'application/json');
		echo json_encode([
			'HOST' => HOST,
			'BASE_URI' => BASE_URI,
			'url' => URL::getRequestUrl(),
		]);
	});
	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
} catch(\Throwable $e) {
	Headers::status(HTTP::INTERNAL_SERVER_ERROR);
	Headers::set('Content-Type', 'text/plain');
	exit('Internal Server Error');
}