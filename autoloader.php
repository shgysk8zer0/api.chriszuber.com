<?php
namespace autoloader;

use const \Consts\{
	AUTOLOADER,
	AUTOLOAD_EXTS,
	TIMEZONE,
	INCLUDE_PATH,
	EXCEPTION_HANDLER,
	HMAC_FILE,
	CREDS_FILE
};

use \shgysk8zer0\{User, PDO};

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'consts.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'functions.php');

set_include_path(join(INCLUDE_PATH, PATH_SEPARATOR) . PATH_SEPARATOR . get_include_path());
spl_autoload_register(AUTOLOADER);
spl_autoload_extensions(join(AUTOLOAD_EXTS, ','));
set_exception_handler(EXCEPTION_HANDLER);
date_default_timezone_set(TIMEZONE);
User::setKey(file_get_contents(HMAC_FILE));
PDO::setCredsFile(CREDS_FILE);