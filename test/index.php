<?php
namespace Test;
use \shgysk8zer0\PHPAPI\{API, Headers, HTTPException, ConsoleLogger};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use const \Consts\{DEBUG};
use \Throwable;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

$api = new API();

if (DEBUG) {
	$api->setLogger(ConsoleLogger::getInstance());
}

$api->on('GET', function(API $req): void
{
	Headers::contentType('application/json');
	echo json_encode($req);
});

$api->on('POST', function(API $req): void
{
	Headers::contentType('application/json');
	echo json_encode($req);
});

$api->on('DELETE', function(API $req): void
{
	Headers::contentType('application/json');
	echo json_encode($req);
});

$api();
