<?php
namespace Lint;

use \shgysk8zer0\PHPAPI\{Linter, Headers, SAPILogger};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPSTatusCodes as HTTP};
use \RuntimeException;

const CONFIG = './config.ini';

if (! PHP_SAPI === 'cli') {
	Headers::status(HTTP::FORBIDDEN);
} elseif (! isset($argv) or realpath($argv[0]) !== __FILE__) {
	throw new RuntimeException('Linting must be executed directly');
} elseif (! file_exists(CONFIG)) {
	throw new RuntimeException('Missing linting config file');
} elseif (! $config = parse_ini_file(CONFIG, true, INI_SCANNER_TYPED)) {
	throw new RuntimeException('Error parsing linter config file');
} else {
	$args = getopt('d:', ['dir::']);

	if (array_key_exists('dir', $args)) {
		$dir = $args['dir'];
	} elseif (array_key_exists('d', $args)) {
		$dir = $args['d'];
	} else {
		$dir = __DIR__;
	}

	unset($args);

	date_default_timezone_set($config['init']['timezone']);

	foreach ($config['init']['require'] as $path) {
		require_once $path;
	}

	spl_autoload_register($config['autoload']['function']);
	spl_autoload_extensions(join(',', $config['autoload']['extensions']));
	set_include_path(
		join(PATH_SEPARATOR, $config['autoload']['path'])
		. PATH_SEPARATOR . get_include_path()
	);


	$linter = new Linter(new SAPILogger());
	$linter->ignoreDirs(...$config['lint']['ignore']);
	$linter->scanExts(...$config['lint']['extensions']);

	if ($linter->scan($dir)) {
		exit(0);
	} else {
		exit(1);
	}
}
