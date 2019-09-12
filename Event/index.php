<?php
namespace Event;
use \shgysk8zer0\PHPAPI\{API, PDO, Headers, HTTPException, User};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use \DateTime;
use \DateTimeImmutable;
use \DateTimeZone;
use \Throwable;
use \JSONSerializable;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

const SELECT = 'SELECT JSON_OBJECT(
	"@context", "https://schema.org",
	"@type", "Event",
	"identifier", `Event`.`identifier`,
	"name", `Event`.`name`,
	"description", `Event`.`description`,
	"startDate", DATE_FORMAT(`Event`.`startDate`, "%Y-%m-%dT%T"),
	"endDate", DATE_FORMAT(`Event`.`endDate`, "%Y-%m-%dT%T"),
	"location", JSON_OBJECT(
		"@context", "https://schema.org",
		"@type", "Place",
		"identifier", `Place`.`identifier`,
		"name", `Place`.`name`,
		"publicAccess", `Place`.`publicAccess` IS TRUE,
		"address", JSON_OBJECT(
			"@type", "PostalAddress",
			"streetAddress", `PostalAddress`.`streetAddress`,
			"postOfficeBoxNumber", `PostalAddress`.`postOfficeBoxNumber`,
			"addressLocality", `PostalAddress`.`addressLocality`,
			"addressRegion", `PostalAddress`.`addressRegion`,
			"postalCode", `PostalAddress`.`postalCode`,
			"addressCountry", `PostalAddress`.`addressCountry`
		),
		"geo", JSON_OBJECT(
			"@type", "GeoCoordinates",
			"identifier", `GeoCoordinates`.`identifier`,
			"name", `GeoCoordinates`.`name`,
			"longitude", `GeoCoordinates`.`longitude`,
			"latitude", `GeoCoordinates`.`latitude`,
			"elevation", `GeoCoordinates`.`elevation`
		)
	),
	"image", JSON_OBJECT(
		"@type", "ImageObject",
		"identifier", `ImageObject`.`identifier`,
		"url", `ImageObject`.`url`,
		"height", `ImageObject`.`height`,
		"width", `ImageObject`.`width`,
		"encodingFormat", `ImageObject`.`encodingFormat`
	),
	"organizer", JSON_OBJECT(
		"@type", "Person",
		"identifier", `Person`.`identifier`,
		"honorificPrefix", `Person`.`honorificPrefix`,
		"givenName", `Person`.`givenName`,
		"additionalName", `Person`.`additionalName`,
		"familyName", `Person`.`familyName`,
		"honorificSuffix", `Person`.`honorificSuffix`,
		"gender", `Person`.`gender`,
		"birthDate", DATE(`Person`.`birthDate`),
		"email", `Person`.`email`,
		"telephone", `Person`.`telephone`,
		"jobTitle", `Person`.`jobTitle`,
		"worksFor", JSON_OBJECT(
			"@type", "Organization",
			"identifier", `Organization`.`identifier`,
			"name", `Organization`.`name`,
			"telephone", `Organization`.`telephone`,
			"email", `Organization`.`email`,
			"url", `Organization`.`url`
		)
	)
) AS `json`
FROM `Event`
LEFT OUTER JOIN `Place` ON `Event`.`location` = `Place`.`id`
LEFT OUTER JOIN `PostalAddress` ON `Place`.`address` = `PostalAddress`.`id`
LEFT OUTER JOIN `GeoCoordinates` ON `Place`.`geo` = `GeoCoordinates`.`id`
LEFT OUTER JOIN `ImageObject` ON `Event`.`image` = `ImageObject`.`id`
LEFT OUTER JOIN `Person` ON `Event`.`organizer` = `Person`.`id`
LEFT OUTER JOIN `Organization` ON `Person`.`worksFor` = `Organization`.`id`';

final class Distance
{
	private $_value = 0;
	private $_units = 'm';

	final public function __construct(float $value, string $units = 'm')
	{
		$this->_value = $value;
		$this->_units = $units;
	}

	final public function getValue(): float
	{
		return $this->_value;
	}

	final public function getUnits(): string
	{
		return $this->_units;
	}
}

final class Duration
{
	private $_value = 1;
	private $_units = 'days';

	final public function __construct(float $value, string $units = 'days')
	{
		$this->_value = $value;
		$this->_units = $units;
	}

	final public function getValue(): float
	{
		return $this->_value;
	}

	final public function getUnits(): string
	{
		return $this->_units;
	}

	final public function __toString(): string
	{
		return "{$this->getValue()} {$this->getUnits()}";
	}
}

final class GeoCoordinates implements JSONSerializable
{
	private $_lat  = 0;
	private $_lng  = 0;
	private $_elev = null;

	final public function __construct(float $lat, float $lng, ?float $elev = null)
	{
		$this->_lat = $lat;
		$this->_lng = $lng;
		$this->_elev = $elev;
	}

	final public function getLatitude(): float
	{
		return $this->_lat;
	}

	final public function getLongitude(): float
	{
		return $this->_lng;
	}

	final public function getElevation(): ?float
	{
		return $this->_elev;
	}

	final public function JSONSerialize(): array
	{
		return [
			'@type' => 'GeoCoordinates',
			'longitude' => $this->_lng,
			'latitude'  => $this->_lat,
			'elevation' => $this->_elev,
		];
	}
}

try {
	function get_events(
		PDO                $pdo,
		GeoCoordinates     $coords,
		?DateTimeImmutable $start      = null,
		?Distance          $radius     = null,
		?Duration          $date_range = null,
		int                $page       = 1,
		int                $limit      = 30
	)
	{
		$sql = SELECT . '
		WHERE `Event`.`startDate` BETWEEN TIMESTAMP(COALESCE(:start, CURRENT_TIMESTAMP)) AND TIMESTAMP(COALESCE(:end, ADDDATE(CURDATE(), INTERVAL 1 MONTH)))
		AND `GeoCoordinates`.`longitude` BETWEEN :lngmin AND :lngmax
		AND `GeoCoordinates`.`latitude` BETWEEN :latmin AND :latmax
		' . sprintf('LIMIT %d, %d;', ($page - 1) * $limit, $limit);

		if (is_null($start)) {
			$start = new DateTimeImmutable();
		}

		if (is_null($radius)) {
			$radius = new Distance(0.1);
		}

		if (is_null($date_range)) {
			$duration = new Duration(1, 'month');
		}

		$stm = $pdo->prepare($sql);

		$stm->execute([
			':lngmin' => $coords->getLongitude() - $radius->getValue(),
			':lngmax' => $coords->getLongitude() + $radius->getValue(),
			':latmin' => $coords->getLatitude() - $radius->getValue(),
			':latmax' => $coords->getLatitude() + $radius->getValue(),
			':start'  => $start->format(DateTime::W3C),
			':end'    => $start->modify("{$date_range}")->format(DateTime::W3C),
		]);

		return array_map(function(object $event): object
		{
			return json_decode($event->json);
		}, $stm->fetchAll() ?? []);
	}

	function get_event(PDO $pdo, string $uuid): ?object
	{
		$sql = SELECT . '
		WHERE `Event`.`identifier` = :uuid LIMIT 1';

		$stm = $pdo->prepare($sql);

		$stm->execute([':uuid' => $uuid]);

		if ($event = $stm->fetchObject()) {
			return json_decode($event->json);
		} else {
			return null;
		}
	}

	$api = new API('*');

	$api->on('GET', function(API $req): void
	{
		if ($req->get->has('latitude', 'longitude')) {
			Headers::contentType('application/json');
			$results = get_events(
				PDO::load(),
				new GeoCoordinates($req->get->get('latitude'), $req->get->get('longitude')),
				new DateTimeImmutable($req->get->get('date', false, 'now')),
				new Distance($req->get->get('radius', false, 0.3), 'm'),
				new Duration($req->get->get('days', false, 30), 'days'),
				$req->get->get('page', false, 1),
				$req->get->get('results', false, 30)
			);
			echo json_encode($results);
		} elseif ($req->get->has('uuid')) {
			$event = get_event(PDO::load(), $req->get->get('uuid'));

			if (isset($event)) {
				Headers::contentType('application/ld+json');
				echo json_encode($event);
			} else {
				throw new HTTPException('Event not found', HTTP::NOT_FOUND);
			}
		} else {
			throw new HTTPException('Missing geolocation for events', HTTP::BAD_REQUEST);
		}
	});

	$api->on('POST', function(): void
	{
		throw new HTTPException('Not yet implemented', HTTP::NOT_IMPLEMENTED);
	});

	$api->on('DELETE', function(API $req): void
	{
		if ($req->get->has('token', 'uuid')) {
			$user = User::loadFromToken(PDO::load(), $req->get->get('token', false));

			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} elseif (! $user->can('deleteEvent')) {
				throw new HTTPException('You do not have permission to delete events', HTTP::UNAUTHORIZED);
			} else {
				$stm = PDO::load()->prepare('DELETE FROM `Event` WHERE `identifier` = :uuid LIMIT 1;');

				if ($stm->execute([':uuid' => $req->get->get('uuid')]) and $stm->rowCount() === 1) {
					Headers::status(HTTP::NO_CONTENT);
				} else {
					throw new HTTPException('Event not fuond', HTTP::NOT_FOUND);
				}
			}
		} else {
			throw new HTTPException('Request missing UUID or auth token', HTTP::BAD_REQUEST);
		}
	});

	$api();
} catch (HTTPException $e) {
	Headers::status($e->getCode());
	Headers::contentType('application/json');
	echo json_encode($e);
}
