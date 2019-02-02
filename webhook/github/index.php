<?php
namespace WebHook\GitHub;

use \shgysk8zer0\{HTTPException, Headers};
use \shgysk8zer0\WebHook\{GitHub};
use const \Consts\{GITHUB_WEBHOOK};
use \Throwable;
use \DateTime;

require_once(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'autoloader.php');

try {
	$hook = new GitHub(GITHUB_WEBHOOK);
	$date = new DateTime();
	Headers::set('Content-Type', 'application/json');
	file_put_contents("{$date->format(\DateTime::W3C)}.json", json_encode($hook, JSON_PRETTY_PRINT));
} catch(HTTPException $e) {
	Headers::status($e->getCode());
	Headers::set('Content-Type', 'application/json');
	echo json_encode($e);
} catch (Throwable $e) {
	Headers::status(Headers::INTERNAL_SERVER_ERROR);
	Headers::set('Content-Type', 'text/plain');
	echo 'Internal Server Error' . PHP_EOL;
	print_r($e);
}
