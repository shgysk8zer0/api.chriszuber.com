<?php
namespace TimePunch;

use \shgysk8zer0\PHPAPI\{Headers, API, PDO, HTTPException, User, Person};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use \Throwable;
use \DateTime;
use \StdClass;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
	$api = new API();

	$api->on('GET', function(API $request): void
	{
		if (! $request->get->has('token')) {
			throw new HTTPException('No token for request', HTTP::UNAUTHORIZED);
		} elseif (! $request->get->has('from', 'to')) {
			throw new HTTPException('Missing date range', HTTP::BAD_REQUEST);
		} else {
			$pdo = PDO::load();
			$user = User::loadFromToken(PDO::load(), $request->get->get('token', false));

			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} else {
				$stm = $pdo->prepare('SELECT `datetime`,
						`person`,
						`datetime`,
						`clock`,
						`notes`,
						`created`,
						`updated`,
						`editedBy`
					FROM `punches`
					WHERE `datetime` BETWEEN FROM_UNIXTIME(:from) AND FROM_UNIXTIME(:to);'
				);

				$from = new DateTime($request->get->get('from', false));
				$to = new DateTime($request->get->get('to', false));

				$stm->execute([
					':from' => $from->getTimestamp(),
					':to'   => $to->getTimestamp(),
				]);

				$results = array_map(function(StdClass $result): StdClass
				{
					$result->datetime = (new DateTime($result->datetime))->format(DateTime::W3C);
					$result->created = (new DateTime($result->created))->format(DateTime::W3C);
					$result->updated = (new DateTime($result->updated))->format(DateTime::W3C);
					return $result;
				}, $stm->fetchAll());

				Headers::contentType('application/json');
				echo json_encode($results);
			}
		}
	});

	$api->on('POST', function(API $request): void
	{
		if (! $request->post->has('token')) {
			throw new HTTPException('No token for request', HTTP::UNAUTHORIZED);
		} elseif (! $request->post->has('project', 'clock')) {
			throw new HTTPException('Missing required data', HTTP::BAD_REQUEST);
		} else {
			$user = User::loadFromToken(PDO::load(), $request->post->get('token', false));

			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} else {
				$pdo = PDO::load();
				$datetime = new DateTime($request->post->get('datetime', false, 'now'));

				$stm = $pdo->prepare('INSERT INTO `punches` (
					`person`,
					`project`,
					`clock`,
					`datetime`,
					`notes`
				) VALUES (
					:person,
					:project,
					:clock,
					FROM_UNIXTIME(:datetime),
					:notes
				);');

				if (! $stm->execute([
					':person'   => $user->id,
					':project'  => $request->post->get('project'),
					':clock'    => $request->post->get('clock'),
					':datetime' => $datetime->getTimestamp(),
					':notes'    => $request->post->get('notes', true, null),
				])) {
					throw new HTTPException('Error saving timepunch', HTTP::INTERNAL_SERVER_ERROR);
				} else {
					Headers::status(HTTP::CREATED);
				}
			}
		}
	});

	$api();
} catch (HTTPException $e) {
	Headers::contentType('application/json');
	Headers::status($e->getCode());
	echo json_encode($e);
}
