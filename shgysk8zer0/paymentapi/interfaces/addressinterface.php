<?php
namespace shgysk8zer0\PaymentAPI\Interfaces;
use \shgysk8zer0\PHPAPI\Interfaces\{InputData};

interface AddressInterface
{
	public function __construct(?InputData $input = null);

	public function getRecipient():? string;

	public function setRecipient(?string $name): void;

	public function getAddressLine(): array;

	public function setAddressLine(string ...$lines): void;

	public function getCity(): string;

	public function setCity(string $city): void;

	public function getRegion(): string;

	public function setRegion(string $region): void;

	public function getCountry(): string;

	public function setCountry(string $country): void;

	public function getPostalCode(): string;

	public function setPostalCode(string $postal_code): void;

	public function getOrganization():? string;

	public function setOrganization(?string $org): void;
}
