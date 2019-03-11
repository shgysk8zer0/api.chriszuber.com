<?php
namespace Weather;

use \shgysk8zer0\PHPAPI\{Headers, API, HTTPException};
use \shgysk8zer0\{OpenWeatherMap};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use const \Consts\{OPEN_WEATHER_MAP};

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
	$api = new API('*');
	$api->on('GET', function(API $request): void
	{
		if (file_exists(OPEN_WEATHER_MAP)) {
			define(__NAMESPACE__ . '\KEY', file_get_contents(OPEN_WEATHER_MAP));
		} else {
			throw new HTTPException('Missing OpenWeatherMap APPID', HTTP::INTERNAL_SERVER_ERROR);
		}

		$weather = new OpenWeatherMap(KEY);

		if ($request->get->has('lon', 'lat')) {
			Headers::contentType('application/json');
			$weather = new OpenWeatherMap(KEY);
			$resp = $weather->getFromCoords($request->get->get('lat', false), $request->get->get('lon', false));
			echo json_encode($resp);
		} elseif ($request->get->has('zip')) {
			Headers::contentType('application/json');
			$weather = new OpenWeatherMap(KEY);
			$resp = $weather->getFromZip($request->get->get('zip', false));
			echo json_encode($resp);
		} else {
			throw new HTTPException('Missing location info (lat/lon or zip)', HTTP::BAD_REQUEST);
		}
	});
	$api();
} catch (HTTPException $e) {
	Headers::contentType('application/json');
	Headers::status($e->getCode());
	echo json_encode($e);
}
