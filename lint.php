<?php
namespace Lint;

use \shgysk8zer0\{Linter, Headers};
use \shgysk8zer0\Abstracts\{HTTPSTatusCodes as HTTP};
use function \Functions\{is_cli};

require_once('./autoloader.php');

if (is_cli()) {
	$linter = new Linter();
	$linter->ignoreDirs('./.git', './data', './logs');
	$linter->scanExts('php');

	if (! $linter->scan(__DIR__)) {
		exit(1);
	}
} else {
	Headers::status(HTTP::NOT_FOUND);
}
