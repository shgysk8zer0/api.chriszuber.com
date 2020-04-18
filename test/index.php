<?php
namespace Test;
use \shgysk8zer0\PHPAPI\{API, Headers, HTTPException, ConsoleLogger};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use const \Consts\{ENV, DEBUG};
use \Throwable;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

$api = new API();

if (DEBUG) {
	$api->setLogger(new ConsoleLogger());
}

$api->on('GET', function(API $req): void
{
	Headers::contentType('application/json');
	echo json_encode($req);
});

$api->on('POST', function(API $request): void
{
	Headers::contentType('application/json');
	echo json_encode($request);
});

$api->on('DELETE', function(API $request): void
{
	Headers::contentType('application/json');
	echo json_encode($request);
});

$api();
