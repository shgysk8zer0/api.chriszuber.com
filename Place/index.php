<?php
namespace Place;
use \shgysk8zer0\PHPAPI\{API, PDO, Headers, HTTPException, User, UUID};
use \shgysk8zer0\PHPAPI\Abstracts\{HTTPStatusCodes as HTTP};
use \DateTime;
use \DateTimeImmutable;
use \DateTimeZone;
use \Throwable;
use \JSONSerializable;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoloader.php';

const SELECT = 'SELECT JSON_OBJECT(
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
) AS `json`
FROM `Place`
LEFT OUTER JOIN `PostalAddress` ON `Place`.`address` = `PostalAddress`.`id`
LEFT OUTER JOIN `GeoCoordinates` ON `Place`.`geo` = `GeoCoordinates`.`id`
LEFT OUTER JOIN `ImageObject` ON `Place`.`image` = `ImageObject`.`id`';

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
	function get_places(
		PDO                $pdo,
		GeoCoordinates     $coords,
		?Distance          $radius     = null,
		int                $page       = 1,
		int                $limit      = 30
	)
	{
		$sql = SELECT . '
		WHERE `GeoCoordinates`.`longitude` BETWEEN :lngmin AND :lngmax
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
		]);

		return array_map(function(object $Place): object
		{
			return json_decode($Place->json);
		}, $stm->fetchAll() ?? []);
	}

	function get_place(PDO $pdo, string $uuid): ?object
	{
		$sql = SELECT . '
		WHERE `Place`.`identifier` = :uuid LIMIT 1';

		$stm = $pdo->prepare($sql);

		$stm->execute([':uuid' => $uuid]);

		if ($Place = $stm->fetchObject()) {
			return json_decode($Place->json);
		} else {
			return null;
		}
	}

	$api = new API('*');

	$api->on('GET', function(API $req): void
	{
		if ($req->get->has('latitude', 'longitude')) {
			Headers::contentType('application/json');
			$results = get_places(
				PDO::load(),
				new GeoCoordinates($req->get->get('latitude'), $req->get->get('longitude')),
				new Distance($req->get->get('radius', false, 0.3), 'm'),
				$req->get->get('page', false, 1),
				$req->get->get('limit', false, 30)
			);
			echo json_encode($results);
		} elseif ($req->get->has('uuid')) {
			$place = get_place(PDO::load(), $req->get->get('uuid'));

			if (isset($place)) {
				Headers::contentType('application/ld+json');
				echo json_encode($place);
			} else {
				throw new HTTPException('Place not found', HTTP::NOT_FOUND);
			}
		} else {
			throw new HTTPException('Missing geolocation for Places', HTTP::BAD_REQUEST);
		}
	});

	$api->on('POST', function(API $req): void
	{
		if ($req->post->has('token', 'name', 'geo', 'address') or $req->post->has('token', 'uuid')) {
			$pdo = PDO::load();
			$user = User::loadFromToken($pdo, $req->post->get('token', false));

			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} elseif (! $user->can('createPlace')) {
				throw new HTTPException('You do not have permission to create Places', HTTP::UNAUTHORIZED);
			} else {
				$stm = $pdo->prepare('INSERT INTO `Place` (
					`identifier`,
					`name`,
					`description`,
					`address`,
					`geo`,
					`image`,
					`publicAccess`
				) VALUES (
					:uuid,
					:name,
					:description,
					:address,
					:geo,
					:image,
					:publicAccess
				) ON DUPLICATE KEY UPDATE
					`name`         = COALESCE(:name, `name`),
					`description`  = COALESCE(:description, `description`),
					`address`      = COALESCE(:address, `address`),
					`geo`          = COALESCE(:geo, `geo`),
					`image`        = COALESCE(:image, `image`),
					`publicAccess` = COALESCE(:publicAccess, `publicAccess`);');

				if ($stm->execute([
					':uuid'           => $req->post->has('uuid') ? $req->post->get('uuid') : new UUID(),
					':name'           => $req->post->get('name'),
					':description'    => $req->post->get('description'),
					':address'        => $req->post->get('address'),
					':geo'            => $req->post->get('geo'),
					':image'          => $req->post->get('image'),
					':publicAccess'   => $req->post->get('publicAccess'),
				]) and (intval($pdo->lastInsertId()) !== 0 or $stm->rowCount() === 1)) {
					Headers::status(HTTP::CREATED);
					exit();
				} else {
					throw new HTTPException('Error creating or updating Place', HTTP::INTERNAL_SERVER_ERROR);
				}
			}
		} else {
			throw new HTTPException('Missing required fields', HTTP::BAD_REQUEST);
		}
	});

	$api->on('DELETE', function(API $req): void
	{
		if ($req->get->has('token', 'uuid')) {
			$user = User::loadFromToken(PDO::load(), $req->get->get('token', false));

			if (! $user->loggedIn) {
				throw new HTTPException('User data expired or invalid', HTTP::UNAUTHORIZED);
			} elseif (! $user->can('deletePlace')) {
				throw new HTTPException('You do not have permission to delete Places', HTTP::UNAUTHORIZED);
			} else {
				$stm = PDO::load()->prepare('DELETE FROM `Place` WHERE `identifier` = :uuid LIMIT 1;');

				if ($stm->execute([':uuid' => $req->get->get('uuid')]) and $stm->rowCount() === 1) {
					Headers::status(HTTP::NO_CONTENT);
				} else {
					throw new HTTPException('Place not fuond', HTTP::NOT_FOUND);
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
