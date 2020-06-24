<?php

namespace shgysk8zer0\RiverFlows;

use \shgysk8zer0\HTTP\{Request, Response, Body, Headers, URL, ContentSecurityPolicy};

use \shgysk8zer0\HTTP\Abstracts\{HTTPStatusCodes as HTTP};

use \shgysk8zer0\PHPAPI\{SAPILogger, ConsoleLogger, FileCache, LoggerSubject, UUID};

use \Throwable;

use \DateTimeImmutable;

use \DateTimeZone;

use \DateInterval;

use \RuntimeException;

use const \Consts\{CACHE_DIR, TIMEZONE};

use function \Functions\{fetch};

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
	$logger = new LoggerSubject();
	$logger->attach(new ConsoleLogger());
	$logger->attach(new SAPILogger());

	$url = new URL('https://www.spk-wc.usace.army.mil/fcgi-bin/hourly.py');
	$url->searchParams->set('report', 'isb');
	$url->searchParams->set('textonly', 'true');

	$req = new Request($url, [
		'method'      => 'GET',
		'cache'       => 'default',
		'credentials' => 'omit',
		'redirect'    => 'follow',
		'referrer'    => 'no-referrer',
		'headers' => new Headers([
			'Accept' => 'text/plain',
		]),
	]);

	$req->setLogger($logger);
	$req->setCache(new FileCache(CACHE_DIR));
	$req->setExpiration(new DateInterval('PT1H'));

	if (! $resp = $req->send()) {
		throw new RuntimeException("Request to {$url->origin} failed", HTTP::BAD_GATEWAY);
	} elseif (! $resp->ok) {
		throw new RuntimeException("{$resp->url} [{$resp->status} {$resp->statusText}]");
	} else {
		$lines   = explode(PHP_EOL, $resp->body->text());
		$tz      = new DateTimeZone(TIMEZONE);

		$entries = array_map(function(string $line) use ($tz, $logger):? array
		{
			[
				$date,        // dMY
				$time,        // Hi
				$elevation,   // ft
				$storage,     // ac-ft
				$outflow,     // cfs
				$inflow,      // cfs
				$lower_stage, // ft
				$lower_flow,  // cfs
				$upper_stage, // ft
				$upper_flow,  // cfs
			] = array_pad(array_values(array_filter(explode(' ', trim($line)))), 10, null);

			if ($elevation !== '-NR-' and $datetime = DateTimeImmutable::createFromFormat('dMY Hi', "{$date} {$time}", $tz)) {
				return [
					'datetime' => $datetime->format(DateTimeImmutable::W3C),
					'elevation' => [
						'value' => floatval($elevation),
						'unit'  => 'ft',
					],
					'storage' => [
						'value' => floatval($storage),
						'unit'  => 'ac-ft',
					],
					'outflow' => [
						'value' => floatval($outflow),
						'unit'  => 'cfs',
					],
					'inflow' => [
						'value' => floatval($inflow),
						'unit'  => 'cfs',
					],
					'upper' => [
						'stage' => [
							'value' => floatval($upper_stage),
							'unit'  => 'ft',
						],
						'flow' => [
							'value' => floatval($upper_flow),
							'unit'  => 'cfs',
						],
					],
					'lower' => [
						'stage' => [
							'value' => floatval($lower_stage),
							'unit'  => 'ft',
						],
						'flow' => [
							'value' => floatval($lower_flow),
							'unit'  => 'cfs',
						],
					]
				];

			} else {
				return null;
			}
		}, array_reverse(array_slice($lines, 9)));

		$entries = array_values(array_filter($entries));

		$resp = new Response(new Body(json_encode($entries)), [
			'headers' => new Headers([
				'Content-Type'                 => 'application/json',
				'Access-Control-Allow-Origin'  => '*',
				'Access-Control-Allow-Methods' => 'OPTIONS, GET',
				'Access-Control-Allow-Headers' => 'Accept, Content-Type, Upgrade-Insecure-Requests',
				'X-REQUEST-UUID'               => new UUID(),
			]),
			'status'  => HTTP::OK,
		]);

		$resp->send();
	}
} catch (Throwable $e) {
	if (isset($logger)) {
		$logger->error('[{class} {code}] "{message}" at {file}:{line}', [
			'class'   => get_class($e),
			'code'    => $e->getCode(),
			'message' => $e->getMessage(),
			'file'    => $e->getFile(),
			'line'    => $e->getLine(),
		]);
	}

	$resp = new Response(new Body(json_encode([
		'error' => [
			'message' => 'An unknown error occured',
			'status'  => HTTP::INTERNAL_SERVER_ERROR,
		]
	])), [
		'status'  => HTTP::INTERNAL_SERVER_ERROR,
		'headers' => new Headers([
			'Content-Type'                => 'application/json',
			'Access-Control-Allow-Origin' => '*',
		]),
	]);

	$resp->send();
}
