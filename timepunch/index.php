<?php
namespace TimePunch;

use \shgysk8zer0\PHPAPI\{Headers, API, PDO, HTTPException, User};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use \shgysk8zer0\PHPAPI\Schema\{Person};
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
		} elseif (true) {
			$user = User::loadFromToken(PDO::load(), $request->get->get('token', false));
			$pdo = PDO::load();
			$stm = $pdo->prepare(
				'SELECT UNIX_TIMESTAMP(`datetime`) AS `datetime`,
					`clock` FROM `punches`
				WHERE `person` = :id
				ORDER BY `datetime` DESC
				LIMIT 1;'
			);

			if ($stm->execute([':id' => $user->id])) {
				$result = $stm->fetchObject() ?? new StdClass();
				Headers::contentType('application/json');
				$result->datetime = date(DateTime::W3C, $result->datetime);
				$result->projects = [[
					'id'   => 1,
					'name' => 'EZPest',
				], [
					'id'   => 2,
					'name' => 'Sentinel',
				], [
					'id'   => 3,
					'name' => 'Kern River Courier',
				], [
					'id'   => 4,
					'name' => 'KernValley.us',
				]];
				echo json_encode($result);
			} else {
				throw new HTTPException('Missing date range', HTTP::BAD_REQUEST);
			}
		} else {
			$pdo = PDO::load();
			$user = User::loadFromToken(PDO::load(), $request->get->get('token', false));

			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} else {
				$stm = $pdo->prepare(
					'SELECT UNIX_TIMESTAMP(`datetime`) AS `datetime`,
						`person`,
						`clock`,
						`notes`,
						UNIX_TIMESTAMP(`created`) AS `created`,
						UNIX_TIMESTAMP(`updated`) AS `updated,
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
					$result->datetime = date(DateTime::W3C, $result->datetime);
					$result->created = date(DateTime::W3C, $result->created);
					$result->updated = date(DateTime::W3C, $result->updated);
					$result->person = new Person($result->person);
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
		} elseif (! $request->post->has('project')) {
			throw new HTTPException('Missing required data (project)', HTTP::BAD_REQUEST);
		} else {
			$user = User::loadFromToken(PDO::load(), $request->post->get('token', false));

			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} else {
				$pdo = PDO::load();
				$datetime = new DateTime($request->post->get('datetime', false, 'now'));
				$prev = $pdo->prepare(
					'SELECT `clock`
					FROM `punches`
					WHERE `person` = :person
					ORDER BY `datetime` DESC
					LIMIT 1;'
				);

				$stm = $pdo->prepare(
					'INSERT INTO `punches` (
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
					);'
				);

				if ($request->post->isAllowedValue('clock', 'in', 'out')) {
					$clock = $request->post->get('clock', false);
				} elseif ($prev->execute([':person' => $user->id]) and $last = $prev->fetchObject()) {
					$clock = $last->clock === 'in' ? 'out' : 'in';
				} else {
					$clock = 'in';
				}

				if (! $stm->execute([
					':person'   => $user->id,
					':project'  => $request->post->get('project'),
					':clock'    => $clock,
					':datetime' => $datetime->getTimestamp(),
					':notes'    => $request->post->get('notes', true, null),
				])) {
					throw new HTTPException('Error saving timepunch', HTTP::INTERNAL_SERVER_ERROR);
				} else {
					Headers::status(HTTP::CREATED);
					Headers::contentType('application/json');
					echo json_encode(['clocked' => $clock]);
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
