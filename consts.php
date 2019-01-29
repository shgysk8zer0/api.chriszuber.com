<?php

namespace Consts;

const TIMEZONE = 'America/Los_Angeles';
const AUTOLOADER = 'spl_autoload';
const EXCEPTION_HANDLER = '\Functions\log_exception';
const BASE = __DIR__ . DIRECTORY_SEPARATOR;
const DATA_DIR = BASE . 'data' . DIRECTORY_SEPARATOR;
const AUTOLOAD_EXTS = [
	'.php',
];
const INCLUDE_PATH = [
	__DIR__,
];
const CREDS_FILE = DATA_DIR . 'creds.json';
const HMAC_FILE  = DATA_DIR . 'hmac.key';
const DEBUG = true;