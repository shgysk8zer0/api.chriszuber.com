<?php

namespace Consts;

const TIMEZONE = 'America/Los_Angeles';
const AUTOLOADER = 'spl_autoload';
const EXCEPTION_HANDLER = '\Functions\log_exception';
const AUTOLOAD_EXTS = [
	'.php',
];
const INCLUDE_PATH = [
	__DIR__,
];
const CREDS_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'data'. DIRECTORY_SEPARATOR . 'creds.json';
const DEBUG = true;