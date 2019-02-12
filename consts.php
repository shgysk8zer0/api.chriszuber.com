<?php
namespace Consts;

const DEBUG             = true;
const BASE              = __DIR__ . DIRECTORY_SEPARATOR;
const DATA_DIR          = BASE . 'data' . DIRECTORY_SEPARATOR;
const LOGS_DIR          = BASE . 'logs' . DIRECTORY_SEPARATOR;
const UPLOADS_DIR       = BASE . 'uploads' . DIRECTORY_SEPARATOR;
const CREDS_FILE        = DATA_DIR . 'creds.json';
const HMAC_FILE         = DATA_DIR . 'hmac.key';
const GITHUB_WEBHOOK    = DATA_DIR . 'github.json';
const SQL_FILE          = DATA_DIR . 'db.sql';
const ERROR_LOG         = LOGS_DIR . 'errors.log';
const TIMEZONE          = 'America/Los_Angeles';
const EXCEPTION_HANDLER = '\Functions\exception_handler';
const ERROR_HANDLER     = '\Functions\error_handler';
const AUTOLOADER        = 'spl_autoload';
const AUTOLOAD_EXTS     = [
	'.php',
];
const INCLUDE_PATH      = [
	__DIR__,
];

define(__NAMESPACE__ . '\HOST', sprintf('%s://%s',
	(array_key_exists('HTTPS', $_SERVER) and ! empty($_SERVER['HTTPS'])) ? 'https' : 'http',
	$_SERVER['HTTP_HOST'] ?? 'localhost'
));
