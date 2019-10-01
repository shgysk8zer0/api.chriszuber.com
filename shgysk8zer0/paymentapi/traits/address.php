<?php
namespace shgysk8zer0\PaymentAPI\Traits;
use \shgysk8zer0\PHPAPI\Interfaces\{InputData};

trait Address
{
	private $_recipient    = null;

	private $_addressLine  = [];

	private $_city         = '';

	private $_region       = '';

	private $_postalCode   = '';

	private $_country      = '';

	private $_organization = '';

	final public function getRecipient():? string
	{
		return $this->_recipient;
	}

	final public function setRecipient(?string $name): void
	{
		$this->_recipient = $name;
	}

	final public function getAddressLine(): array
	{
		return $this->_addressLine;
	}

	final public function setAddressLine(string ...$lines): void
	{
		$this->_addressLine = $lines;
	}

	final public function getCity(): string
	{
		return $this->_city;
	}

	final public function setCity(string $city): void
	{
		$this->_city = $city;
	}

	final public function getRegion(): string
	{
		return $this->_region;
	}

	final public function setRegion(string $region): void
	{
		$this->_region = $region;
	}

	final public function getCountry(): string
	{
		return $this->_country;
	}

	final public function setCountry(string $country): void
	{
		$this->_country = $country;
	}

	final public function getPostalCode(): string
	{
		return $this->_postalCode;
	}

	final public function setPostalCode(string $postal_code): void
	{
		$this->_postalCode = $postal_code;
	}

	final public function getOrganization():? string
	{
		return $this->_organization;
	}

	final public function setOrganization(?string $org): void
	{
		$this->_organization = $org;
	}

	final protected function _setData(InputData $input): bool
	{
		if ($input->has('recipient', 'addressLine', 'city', 'region', 'country', 'postalCode')) {
			$this->setRecipient($input->get('recipient'));
			$this->setAddressLine(...$input->get('addressLine'));
			$this->setCity($input->get('city'));
			$this->setRegion($input->get('region'));
			$this->setCountry($input->get('country'));
			$this->setPostalCode($input->get('postalCode'));
			return true;
		} else {
			return false;
		}
	}
}
