<?php
namespace WebHook\GitHub;

use const \Consts\{GITHUB_WEBHOOK};
use \shgysk8zer0\{HTTPException, Headers, API};
use \shgysk8zer0\WebHook\{GitHub};
use \Throwable;

const METHODS = ['POST', 'OPTIONS','HEAD'];

function info(): void
{
	Headers::set('Allow', join(', ', METHODS));
}

require_once(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'autoloader.php');

$api = new API('*', METHODS);
$api->on('POST', function(API $api): void
{
	$hook = new GitHub(GITHUB_WEBHOOK);
	switch ($hook->event) {
		case 'ping':
			Headers::set('Content-Type', 'application/json');
			echo json_encode($hook);
			break;
		case 'push':
			Headers::set('Content-Type', 'text/plain');
			if ($hook->isMaster()) {
				echo `git pull`;
				echo `git submodule update --init --recursive`;
				echo `git status`;
			} else {
				echo 'Not updating non-master branch';
			}
			break;
		default:
			throw new HTTPException("Unsupported event: {$hook->event}", HTTP::NOT_IMPLEMENTED);
	}
});
$api->on('OPTIONS', __NAMESPACE__ . '\info');
$api->on('HEAD', __NAMESPACE__ . '\info');
$api();