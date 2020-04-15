<?php
namespace autoloader;

use \RuntimeException;
use \Exception;
use \Throwable;

chdir(__DIR__);

if (! file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'config.ini')) {
	throw new Exception('Missing config file');
}

define(
	__NAMESPACE__ . '\CONFIG',
	parse_ini_file(__DIR__ . DIRECTORY_SEPARATOR . 'config.ini', true, INI_SCANNER_TYPED)
);

try {
	if (! is_array(CONFIG) or count(CONFIG) === 0) {
		throw new Exception('Config file not found or could not be parsed');
	} elseif (version_compare(PHP_VERSION, CONFIG['php']['min_version'], '<')) {
		throw new Exception(sprintf('PHP version %s or higher required', CONFIG['php']['min_version']));
	} elseif (is_array(CONFIG['php']['extensions'])) {
		foreach (CONFIG['php']['extensions'] as $ext) {
			if (! extension_loaded($ext)) {
				throw new RuntimeException(sprintf('Missing required PHP extension: "%s"', $ext));
			}
		}
		unset($ext);
	}

	if (is_array(CONFIG['env'])) {
		foreach (CONFIG['env'] as $key => $val) {
			putenv(sprintf('%s=%s', $key, $val));
		}
		unset($key, $val);
	}

	set_include_path(
		join(array_map('realpath', CONFIG['init']['path']), PATH_SEPARATOR)
		. PATH_SEPARATOR . get_include_path()
	);

	if (is_array(CONFIG['init']['require'])) {
		foreach (CONFIG['init']['require'] as $file) {
			require_once($file);
		}
		unset($file);
	}


	spl_autoload_register(CONFIG['autoload']['function']);
	spl_autoload_extensions(join(CONFIG['autoload']['extensions'], ','));

	set_error_handler(CONFIG['init']['handler']['error']);
	set_exception_handler(CONFIG['init']['handler']['exception']);
	date_default_timezone_set(CONFIG['init']['timezone']);

	if (! file_exists(\Consts\HMAC_FILE)) {
		(new \shgysk8zer0\RandomString(30, true, true, true, true))->saveAs(\Consts\HMAC_FILE);
	}

	\shgysk8zer0\PHPAPI\User::setKey(file_get_contents(\Consts\HMAC_FILE));
	\shgysk8zer0\PHPAPI\User::setExpires(CONFIG['token']['value'], CONFIG['token']['units']);
	\shgysk8zer0\PHPAPI\UploadFile::setHost(\Consts\HOST);

	if (FILE_EXISTS(\Consts\CREDS_FILE)) {
		\shgysk8zer0\PHPAPI\PDO::setCredsFile(\Consts\CREDS_FILE);
		\shgysk8zer0\PHPAPI\Schema\Thing::setPDO(\shgysk8zer0\PHPAPI\PDO::load());
	}

	\shgysk8zer0\PHPAPI\API::allowHeaders(...CONFIG['cors']['allowed_headers']);

} catch (\Throwable $e) {
	http_response_code(500);
	header('Content-Type: application/json');

	exit(json_encode(['error' => [
		'message' => $e->getMessage(),
		'code'    => 500,
	]]));
}
